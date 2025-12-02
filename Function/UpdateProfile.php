<?php
// UpdateProfile.php - Versi POST Biasa - Untuk MD5

session_start();

// Include file koneksi database
include '../Config/ConnectDB.php';

// Cek apakah user login
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    $_SESSION['profile_message'] = 'Akses ditolak. Silakan login.';
    $_SESSION['profile_message_type'] = 'error';
    $_SESSION['field_error'] = 'general';
    header("Location: " . $_SERVER['HTTP_REFERER'] ?? '../../../Login.php');
    exit();
}

// Cek apakah permintaan adalah POST dan action adalah change_password
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'change_password') {
    $_SESSION['profile_message'] = 'Permintaan tidak valid.';
    $_SESSION['profile_message_type'] = 'error';
    $_SESSION['field_error'] = 'general';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Ambil data dari form
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

// Validasi input
if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
    $_SESSION['profile_message'] = 'Semua field password harus diisi.';
    $_SESSION['profile_message_type'] = 'error';
    if (empty($current_password)) $_SESSION['field_error'] = 'current_password';
    elseif (empty($new_password)) $_SESSION['field_error'] = 'new_password';
    elseif (empty($confirm_new_password)) $_SESSION['field_error'] = 'confirm_new_password';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

if ($new_password !== $confirm_new_password) {
    $_SESSION['profile_message'] = 'Password baru dan konfirmasi tidak cocok.';
    $_SESSION['profile_message_type'] = 'error';
    $_SESSION['field_error'] = 'confirm_new_password';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

if (strlen($new_password) < 6) {
    $_SESSION['profile_message'] = 'Password baru minimal 6 karakter.';
    $_SESSION['profile_message_type'] = 'error';
    $_SESSION['field_error'] = 'new_password';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Ambil password lama dari database
$query = "SELECT password FROM user WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);
    $stored_hash = $row['password']; // Ini adalah hash MD5 dari password lama

    // Hash password lama yang dimasukkan user dengan MD5
    $input_hash = md5($current_password);

    // Bandingkan hash MD5
    if ($input_hash === $stored_hash) {
        // Hash password baru dengan MD5
        $new_hash = md5($new_password); // Ganti ini dengan password_hash($new_password, PASSWORD_DEFAULT) jika ingin beralih ke metode yang lebih aman

        // Update password di database
        $update_query = "UPDATE user SET password = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($koneksi, $update_query);
        mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $user_id);

        if (mysqli_stmt_execute($update_stmt)) {
            $_SESSION['profile_message'] = 'Password berhasil diubah.';
            $_SESSION['profile_message_type'] = 'success';
            unset($_SESSION['field_error']); // Hapus field_error jika sukses
        } else {
            $_SESSION['profile_message'] = 'Gagal mengupdate database.';
            $_SESSION['profile_message_type'] = 'error';
            $_SESSION['field_error'] = 'general';
        }
        mysqli_stmt_close($update_stmt);
    } else {
        $_SESSION['profile_message'] = 'Password lama salah.';
        $_SESSION['profile_message_type'] = 'error';
        $_SESSION['field_error'] = 'current_password'; // Tandai field password lama
    }
} else {
    $_SESSION['profile_message'] = 'Data user tidak ditemukan.';
    $_SESSION['profile_message_type'] = 'error';
    $_SESSION['field_error'] = 'general';
}

mysqli_stmt_close($stmt);

// Redirect kembali ke halaman sebelumnya (yang memicu form ini)
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>