<?php
session_start();
include('../Config/ConnectDB.php');

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $err = "";

    // Validasi input kosong
    if (empty($email) || empty($password)) {
        $err = "Email dan password wajib diisi.";
    }

    // Proses login jika tidak ada error
    if (empty($err)) {
        $stmt = mysqli_prepare($koneksi, "SELECT id, full_name, nim, email, password, level, id_ormawa FROM user WHERE email = ?");
        if (!$stmt) {
            $err = "Gagal menyiapkan query: " . mysqli_error($koneksi);
        } else {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if (!$user) {
                $err = "Email tidak ditemukan.";
            } else {
                $db_password = $user['password'];
                
                // 🔐 Verifikasi password (support MD5 dan password_hash)
                if (strlen($db_password) === 32 && ctype_xdigit($db_password)) {
                    // Jika password di DB adalah MD5
                    if ($db_password !== md5($password)) {
                        $err = "Password salah.";
                    }
                } else {
                    // Jika password di DB sudah hash (password_hash)
                    if (!password_verify($password, $db_password)) {
                        $err = "Password salah.";
                    }
                }
            }
        }
    }

    // Jika login berhasil
    if (empty($err) && isset($user)) {
        $user_level = (int)($user['level'] ?? 0);
        $ormawa_nama = "";

        // Ambil nama ormawa jika id_ormawa ada
        if (!empty($user['id_ormawa'])) {
            $stmt_ormawa = mysqli_prepare($koneksi, "SELECT nama_ormawa FROM ormawa WHERE id = ?");
            if ($stmt_ormawa) {
                mysqli_stmt_bind_param($stmt_ormawa, "i", $user['id_ormawa']);
                mysqli_stmt_execute($stmt_ormawa);
                $res_ormawa = mysqli_stmt_get_result($stmt_ormawa);
                $row_ormawa = mysqli_fetch_assoc($res_ormawa);
                if ($row_ormawa) {
                    $ormawa_nama = $row_ormawa['nama_ormawa'];
                }
                mysqli_stmt_close($stmt_ormawa);
            }
        }

        // Simpan ke session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_level'] = $user_level;
        $_SESSION['ormawa_id'] = $user['id_ormawa'];
        $_SESSION['user_nama'] = $user['full_name'];
        $_SESSION['ormawa_nama'] = $ormawa_nama;

        // Set login sukses untuk notifikasi SweetAlert
        $_SESSION['login_success'] = "Selamat datang, " . $user['full_name'] . "!";
        
        // Redirect sesuai level
        if ($user_level === 1) {
            header("Location: ../App/View/SuperAdmin/Index.php?page=dashboard");
            exit();
        } elseif ($user_level === 2) {
            header("Location: ../App/View/Admin/Index.php?page=anggota");
            exit();
        } elseif ($user_level === 3) {
            header("Location: ../App/View/User/Index.php?page=dashboard");
            exit();
        } elseif ($user_level === 4) {
            header("Location: ../App/View/Member/Index.php?page=dashboard");
            exit();
        } else {
            $err = "Level pengguna tidak valid.";
        }
    }

    // Jika ada error, redirect kembali dengan pesan error
    if (!empty($err)) {
        $_SESSION['login_error'] = $err;
        
        // ✅ Redirect kembali ke halaman sebelumnya (halaman login)
        $redirect = $_SERVER['HTTP_REFERER'] ?? '../App/View/SuperAdmin/Login.php';
        header("Location: $redirect");
        exit();
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../App/View/SuperAdmin/Login.php");
    exit();
}
?>