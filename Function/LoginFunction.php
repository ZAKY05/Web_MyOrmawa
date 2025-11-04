<?php
session_start();
include('../Config/ConnectDB.php');
include('../App/View/SuperAdmin/Route.php');

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $err = "";

    if (empty($email) || empty($password)) {
        $err = "Email dan password wajib diisi.";
    }

    if (empty($err)) {
        $stmt = mysqli_prepare($koneksi, "SELECT id, nama, email, password, level FROM user WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if (!$user) {
            $err = "Email atau password salah.";
        } else {
            if ($user['password'] !== md5($password)) {
                $err = "Email atau password salah.";
            }
        }
    }

    if (empty($err)) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_level'] = $user['level'];

        // Redirect berdasarkan level
        switch ($user['level']) {
            case '1':
                header("Location: ../App/View/SuperAdmin/Index.php?page=dashboard");
                break;
            default:
                header("Location: /MyOrmawa/App/View/Admin/Index.php?page=dashboard");
        }
        exit();
    } else {
        $_SESSION['login_error'] = $err;
        header("Location: ../App/View/SuperAdmin/Login.php"); 
        exit();
    }
}
if (isset($_GET['logout'])) {
    // Hapus semua variabel session
    $_SESSION = array();

    // Hapus cookie session jika ada
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Hancurkan session
    session_destroy();

    // Redirect ke halaman login
    header("Location: /MyOrmawa/App/View/SuperAdmin/Login.php");
    exit();
}
