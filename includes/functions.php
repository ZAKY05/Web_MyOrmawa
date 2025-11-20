<?php
// Generate 6 digit OTP
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate authentication token
function generateToken() {
    return bin2hex(random_bytes(32));
}

// Send JSON response
function sendResponse($status, $message, $data = null, $http_code = 200) {
    http_response_code($http_code);
    $response = [
        'status' => $status,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit();
}

// Save OTP to database
function saveOTP($koneksi, $email, $otp_code, $otp_type, $new_email = null) {
    error_log("[DEBUG] saveOTP: Received parameters - Email: $email, OTP: $otp_code, Type: $otp_type, Data(new/old email): $new_email");
    try {
        // Delete old OTPs for this email and type
        $delete_query = "DELETE FROM otp_codes WHERE email = ? AND otp_type = ?";
        $delete_stmt = mysqli_prepare($koneksi, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "ss", $email, $otp_type);
        mysqli_stmt_execute($delete_stmt);

        // Insert new OTP (expires in 10 minutes)
        $query = "INSERT INTO otp_codes (email, otp_code, otp_type, new_email, expires_at) 
                  VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))";
        
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $email, $otp_code, $otp_type, $new_email);
        
        $success = mysqli_stmt_execute($stmt);
        
        if (!$success) {
            error_log("Save OTP failed: " . mysqli_stmt_error($stmt));
        }
        
        return $success;
    } catch(Exception $e) {
        error_log("Save OTP error: " . $e->getMessage());
        return false;
    }
}

// Verify OTP
function verifyOTP($koneksi, $email, $otp_code, $otp_type) {
    try {
        $query = "SELECT * FROM otp_codes 
                  WHERE email = ? 
                  AND otp_code = ? 
                  AND otp_type = ? 
                  AND expires_at > NOW() 
                  AND is_used = 0 
                  ORDER BY created_at DESC 
                  LIMIT 1";
        
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sss", $email, $otp_code, $otp_type);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            // Mark OTP as used
            $update_query = "UPDATE otp_codes SET is_used = 1 WHERE id = ?";
            $update_stmt = mysqli_prepare($koneksi, $update_query);
            mysqli_stmt_bind_param($update_stmt, "i", $row['id']);
            mysqli_stmt_execute($update_stmt);
            
            return $row;
        }
        
        return false;
    } catch(Exception $e) {
        error_log("Verify OTP error: " . $e->getMessage());
        return false;
    }
}

// Get user by email
function getUserByEmail($koneksi, $email) {
    try {
        $query = "SELECT * FROM user WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    } catch(Exception $e) {
        error_log("Get user error: " . $e->getMessage());
        return false;
    }
}

// Get user by NIM
function getUserByNIM($koneksi, $nim) {
    try {
        $query = "SELECT * FROM user WHERE nim = ? LIMIT 1";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "s", $nim);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    } catch(Exception $e) {
        error_log("Get user by NIM error: " . $e->getMessage());
        return false;
    }
}

// Calculate angkatan from NIM
function calculateAngkatan($nim) {
    // Assuming NIM format: E41242025 where 24 is the year (2024)
    if (preg_match('/[A-Z]?\d{2}(\d{2})\d+/', $nim, $matches)) {
        $year = $matches[1];
        return "20" . $year;
    }
    return date('Y'); // Default to current year
}
?>