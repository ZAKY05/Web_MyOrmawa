<?php
require_once '../Config/ConnectDB.php';
require_once '../includes/functions.php';
require_once '../includes/email_sender.php';

// Set header untuk respons JSON
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Pastikan metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', 'Method not allowed', null, 405);
}

// Mengambil data JSON yang dikirim
$data = json_decode(file_get_contents("php://input"), true);

// Memeriksa apakah parameter 'action' ada
if (!isset($data['action'])) {
    sendResponse('error', 'Parameter \'action\' tidak ditemukan.', null, 400);
}

// Tentukan tindakan berdasarkan nilai 'action'
$action = $data['action'];
$conn = $koneksi;

if ($conn === null) {
    sendResponse('error', 'Database connection failed', null, 500);
}

try {
    switch ($action) {
        case 'login':
            if (!isset($data['email']) || empty(trim($data['email']))) {
                sendResponse('error', 'Email is required', null, 400);
            }
            if (!isset($data['password']) || empty(trim($data['password']))) {
                sendResponse('error', 'Password is required', null, 400);
            }
            $email = trim($data['email']);
            $password = trim($data['password']);
            if (!isValidEmail($email)) {
                sendResponse('error', 'Invalid email format', null, 400);
            }
            $user = getUserByEmail($conn, $email);
            if (!$user) {
                sendResponse('error', 'Invalid email or password', null, 401);
            }
            if (!verifyPassword($password, $user['password'])) {
                sendResponse('error', 'Invalid email or password', null, 401);
            }
            if ($user['is_verified'] == 0) {
                sendResponse('error', 'Account not verified. Please check your email for verification code.', null, 403);
            }
            $token = generateToken();
            $query = "INSERT INTO login_sessions (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $user['id'], $token);
            $stmt->execute();
            sendResponse('success', 'Login successful', [
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'nim' => $user['nim'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'program_studi' => $user['program_studi'],
                    'angkatan' => $user['angkatan'],
                    'level' => (int)$user['level'],
                    'id_ormawa' => isset($user['id_ormawa']) ? (int)$user['id_ormawa'] : null
                ]
            ]);
            break;

        case 'register':
            // Logic from register.php
            $required_fields = ['nim', 'full_name', 'email', 'program_studi', 'password'];
            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || empty(trim($data[$field]))) {
                    sendResponse('error', ucfirst(str_replace('_', ' ', $field)) . ' is required', null, 400);
                }
            }
            $nim = trim($data['nim']);
            $full_name = trim($data['full_name']);
            $email = trim($data['email']);
            $program_studi = trim($data['program_studi']);
            $password = trim($data['password']);
            if (!isValidEmail($email)) {
                sendResponse('error', 'Invalid email format', null, 400);
            }
            if (strlen($nim) < 8) {
                sendResponse('error', 'NIM must be at least 8 characters', null, 400);
            }
            if (strlen($password) < 8) {
                sendResponse('error', 'Password must be at least 8 characters', null, 400);
            }
            $angkatan = calculateAngkatan($nim);
            $existing_user = getUserByNIM($conn, $nim);
            if ($existing_user) {
                sendResponse('error', 'NIM already registered', null, 409);
            }
            $existing_email = getUserByEmail($conn, $email);
            if ($existing_email) {
                sendResponse('error', 'Email already registered', null, 409);
            }
            $otp_code = generateOTP();
            if (!saveOTP($conn, $email, $otp_code, 'register')) {
                sendResponse('error', 'Failed to generate verification code', null, 500);
            }
            sendOTPEmail($email, $otp_code, 'register');
            $hashed_password = hashPassword($password);
            $level = '3';
            $query = "INSERT INTO user (nim, full_name, email, program_studi, angkatan, password, is_verified, level) VALUES (?, ?, ?, ?, ?, ?, 0, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sssssss", $nim, $full_name, $email, $program_studi, $angkatan, $hashed_password, $level);
            if (mysqli_stmt_execute($stmt)) {
                sendResponse('success', 'Registration successful. Verification code sent to your email.', ['email' => $email], 201);
            } else {
                sendResponse('error', 'Registration failed', null, 500);
            }
            mysqli_stmt_close($stmt);
            break;

        case 'verify_otp':
            // Logic from verify_otp.php
            if (!isset($data['email']) || !isset($data['otp_code']) || !isset($data['otp_type'])) {
                sendResponse('error', 'Email, OTP code, and OTP type are required', null, 400);
            }
            $email = trim($data['email']);
            $otp_code = trim($data['otp_code']);
            $otp_type = trim($data['otp_type']);
            $valid_types = ['register', 'forgot_password', 'change_email'];
            if (!in_array($otp_type, $valid_types)) {
                sendResponse('error', 'Invalid OTP type', null, 400);
            }
            $otp_record = verifyOTP($conn, $email, $otp_code, $otp_type);
            if (!$otp_record) {
                sendResponse('error', 'Invalid or expired OTP code', null, 400);
            }
            switch($otp_type) {
                case 'register':
                    $query = "UPDATE user SET is_verified = 1 WHERE email = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $email);
                    if ($stmt->execute()) {
                        $user = getUserByEmail($conn, $email);
                        sendResponse('success', 'Account verified successfully', ['email' => $email, 'user' => $user]);
                    } else {
                        sendResponse('error', 'Failed to verify account', null, 500);
                    }
                    break;
                case 'forgot_password':
                    sendResponse('success', 'OTP verified successfully. You can now reset your password.', ['email' => $email]);
                    break;
                case 'change_email':
                    $old_email = $email;
                    $new_email = $otp_record['new_email'];
                    $existing = getUserByEmail($conn, $new_email);
                    if ($existing) {
                        sendResponse('error', 'New email is already registered', null, 409);
                    }
                    $query = "UPDATE user SET email = ? WHERE email = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ss", $new_email, $old_email);
                    if ($stmt->execute() && $stmt->affected_rows > 0) {
                        sendResponse('success', 'Email changed successfully', ['new_email' => $new_email]);
                    } else {
                        sendResponse('error', 'Failed to change email', null, 500);
                    }
                    break;
            }
            break;

        case 'forgot_password':
            // Logic from forgot_password.php
            if (!isset($data['email']) || empty(trim($data['email']))) {
                sendResponse('error', 'Email is required', null, 400);
            }
            $email = trim($data['email']);
            if (!isValidEmail($email)) {
                sendResponse('error', 'Invalid email format', null, 400);
            }
            $user = getUserByEmail($conn, $email);
            if ($user) {
                $otp_code = generateOTP();
                if (saveOTP($conn, $email, $otp_code, 'forgot_password')) {
                    sendOTPEmail($email, $otp_code, 'forgot_password');
                }
            }
            sendResponse('success', 'If this email is registered, you will receive a password reset code.', ['email' => $email]);
            break;

        case 'reset_password':
            // Logic from reset_password.php
            if (!isset($data['email']) || !isset($data['password'])) {
                sendResponse('error', 'Email and new password are required', null, 400);
            }
            $email = trim($data['email']);
            $password = trim($data['password']);
            if (strlen($password) < 8) {
                sendResponse('error', 'Password must be at least 8 characters', null, 400);
            }
            $user = getUserByEmail($conn, $email);
            if (!$user) {
                sendResponse('error', 'User not found', null, 404);
            }
            $hashed_password = hashPassword($password);
            $query = "UPDATE user SET password = ? WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $hashed_password, $email);
            if ($stmt->execute()) {
                $delete_sessions = "DELETE FROM login_sessions WHERE user_id = ?";
                $delete_stmt = $conn->prepare($delete_sessions);
                $delete_stmt->bind_param("i", $user['id']);
                $delete_stmt->execute();
                sendResponse('success', 'Password reset successful. Please login with your new password.', ['email' => $email]);
            } else {
                sendResponse('error', 'Failed to reset password', null, 500);
            }
            break;

        case 'change_password':
            // Logic from change_password.php
            if (!isset($data['email']) || !isset($data['old_password']) || !isset($data['new_password'])) {
                sendResponse('error', 'Email, current password, and new password are required', null, 400);
            }
            $email = trim($data['email']);
            $old_password = trim($data['old_password']);
            $new_password = trim($data['new_password']);
            if (!isValidEmail($email)) {
                sendResponse('error', 'Invalid email format', null, 400);
            }
            if (strlen($new_password) < 8) {
                sendResponse('error', 'New password must be at least 8 characters', null, 400);
            }
            if ($old_password === $new_password) {
                sendResponse('error', 'New password cannot be the same as current password', null, 400);
            }
            $user = getUserByEmail($conn, $email);
            if (!$user) {
                sendResponse('error', 'User not found', null, 404);
            }
            if ($user['is_verified'] != 1) {
                sendResponse('error', 'Account not verified', null, 403);
            }
            if (!password_verify($old_password, $user['password'])) {
                sendResponse('error', 'Current password is incorrect', null, 401);
            }
            $hashed_password = hashPassword($new_password);
            $query = "UPDATE user SET password = ?, updated_at = NOW() WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $hashed_password, $email);
            if ($stmt->execute()) {
                sendResponse('success', 'Password changed successfully', ['email' => $email]);
            } else {
                sendResponse('error', 'Failed to change password', null, 500);
            }
            break;

        case 'change_email':
            // Logic from change_email.php
            if (!isset($data['current_email']) || !isset($data['new_email'])) {
                sendResponse('error', 'Current email and new email are required', null, 400);
            }
            $current_email = trim($data['current_email']);
            $new_email = trim($data['new_email']);
            if (!isValidEmail($current_email) || !isValidEmail($new_email)) {
                sendResponse('error', 'Invalid email format', null, 400);
            }
            if ($current_email === $new_email) {
                sendResponse('error', 'New email must be different from current email', null, 400);
            }
            $user = getUserByEmail($conn, $current_email);
            if (!$user) {
                sendResponse('error', 'Current email not found', null, 404);
            }
            $existing_email = getUserByEmail($conn, $new_email);
            if ($existing_email) {
                sendResponse('error', 'New email is already registered', null, 409);
            }
            $otp_code = generateOTP();
            if (!saveOTP($conn, $current_email, $otp_code, 'change_email', $new_email)) {
                sendResponse('error', 'Failed to generate verification code', null, 500);
            }
            sendOTPEmail($new_email, $otp_code, 'change_email');
            sendResponse('success', 'Verification code sent to your new email address', ['current_email' => $current_email, 'new_email' => $new_email]);
            break;

        case 'resend_otp':
            // Logic from resend_otp.php
            if (!isset($data['email']) || !isset($data['otp_type'])) {
                sendResponse('error', 'Email and OTP type are required', null, 400);
            }
            $email = trim($data['email']);
            $otp_type = trim($data['otp_type']);
            $new_email = isset($data['new_email']) ? trim($data['new_email']) : null;
            $valid_types = ['register', 'forgot_password', 'change_email'];
            if (!in_array($otp_type, $valid_types)) {
                sendResponse('error', 'Invalid OTP type', null, 400);
            }
            if (!isValidEmail($email)) {
                sendResponse('error', 'Invalid email format', null, 400);
            }
            $user = getUserByEmail($conn, $email);
            if (!$user && $otp_type !== 'forgot_password') {
                sendResponse('error', 'User not found', null, 404);
            }
            $otp_code = generateOTP();
            if (!saveOTP($conn, $email, $otp_code, $otp_type, $new_email)) {
                sendResponse('error', 'Failed to generate verification code', null, 500);
            }
            $send_to_email = ($otp_type === 'change_email' && $new_email) ? $new_email : $email;
            sendOTPEmail($send_to_email, $otp_code, $otp_type);
            $response_data = ['email' => $email];
            if ($new_email) {
                $response_data['new_email'] = $new_email;
            }
            sendResponse('success', 'Verification code resent successfully', $response_data);
            break;

        default:
            sendResponse('error', 'Aksi tidak valid: ' . htmlspecialchars($action), null, 400);
            break;
    }
} catch (Exception $e) {
    error_log("API Error in action '{$action}': " . $e->getMessage());
    sendResponse('error', 'An error occurred while processing your request', null, 500);
}
?>
