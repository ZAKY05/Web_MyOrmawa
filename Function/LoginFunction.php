<?php
session_start();
include('../Config/ConnectDB.php');

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $err = "";

    if (empty($email) || empty($password)) {
        $err = "Email dan password wajib diisi.";
    }

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
                $db_password = trim($user['password']);
                // 🔔 PERINGATAN: MD5 TIDAK AMAN — ganti ke password_hash() di masa depan
                if ($db_password !== md5($password)) {
                    $err = "Password salah.";
                }
            }
        }
    }

    if (empty($err) && isset($user)) {
        $user_level = (int)($user['level'] ?? 0);
        $ormawa_nama = ""; // default: kosong (bukan "Ormawa")

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
        $_SESSION['user_nama'] = $user['full_name'];   // asli
        $_SESSION['ormawa_nama'] = $ormawa_nama;      // dari tabel ormawa

        // Redirect sesuai level
        if ($user_level === 1) {
            header("Location: ../App/View/SuperAdmin/Index.php?page=dashboard");
            exit();
        } elseif ($user_level === 2) {
            header("Location: ../App/View/Admin/Index.php?page=anggota");
            exit();
        } else {
            $err = "Level pengguna tidak valid.";
        }
    }

    if (!empty($err)) {
        $_SESSION['login_error'] = $err;
        header("Location: ../App/View/SuperAdmin/Login.php");
        exit();
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /MyOrmawa/App/View/SuperAdmin/Login.php");
    exit();
}
?>