<?php
session_start();
include('../Config/ConnectDB.php'); 
include('../App/View/SuperAdmin/Route.php'); 


if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $err = "";

    // Validasi input
    if (empty($email) || empty($password)) {
        $err = "Email dan password wajib diisi.";
    }

    if (empty($err)) {
        // Gunakan prepared statement untuk keamanan (hindari SQL injection)
        $stmt = mysqli_prepare($koneksi, "SELECT id, nama, email, password, level FROM user WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if (!$user) {
            $err = "Email atau password salah.";
        } else {
            // Verifikasi password (gunakan MD5 hanya jika memang disimpan sebagai MD5)
            // ⚠️ Disarankan ganti ke password_hash() di masa depan!
            if ($user['password'] !== md5($password)) {
                $err = "Email atau password salah.";
            }
        }
    }

    if (empty($err)) {
        // Login sukses — simpan session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_level'] = $user['level'];

        // Redirect berdasarkan level
        switch ($user['level']) {
            case '1':
                header("Location: /MyOrmawa/App/View/SuperAdmin/Index.php?page=dashboard");
                break;
            case '2':
                header("Location: ../App/View/Admin/Dashboard.php");
                break;
            case '3':
                header("Location: ../App/View/Member/Dashboard.php");
                break;
            default:
                header("Location: ../App/View/Member/Dashboard.php");
        }
        exit();
    } else {
        // Simpan error ke session untuk ditampilkan di halaman login
        $_SESSION['login_error'] = $err;
        header("Location: ../App/View/SuperAdmin/Login.php"); // Sesuaikan path ke file view login Anda
        exit();
    }
}
