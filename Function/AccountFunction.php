<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../Config/ConnectDB.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu.";
    header("Location: ../App/View/SuperAdmin/Login.php");
    exit();
}

$user_level = (int) ($_SESSION['user_level'] ?? 0);
$ormawa_id_restricted = null;

if ($user_level === 2) {
    $ormawa_id_restricted = (int) ($_SESSION['ormawa_id'] ?? 0);
    if ($ormawa_id_restricted <= 0) {
        $_SESSION['error'] = "Anda tidak terdaftar di ORMawa manapun.";
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../Admin/Account.php'));
        exit();
    }
} elseif ($user_level !== 1) {
    $_SESSION['error'] = "Akses ditolak.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit();
}

function redirectBack($fallback = null)
{
    $fallback = $fallback ?? (
        $_SESSION['user_level'] == 1
        ? '../App/View/SuperAdmin/Account.php'
        : '../App/View/Admin/Account.php'
    );
    $ref = $_SERVER['HTTP_REFERER'] ?? $fallback;
    if (strpos($ref, 'Login.php') !== false || empty($ref)) {
        $ref = $fallback;
    }
    header("Location: $ref");
    exit();
}

function containsForbidden($str)
{
    $terms = ['admin', 'hmj-ti', 'hmj ti', 'superadmin', 'root', 'guest'];
    $low = strtolower(trim($str));
    foreach ($terms as $t) {
        if (strpos($low, $t) !== false)
            return true;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (empty($full_name) || empty($nim) || empty($email)) {
        $_SESSION['error'] = "Nama lengkap, NIM, dan email wajib diisi.";
        redirectBack();
    }
    if (strlen($nim) < 5 || strlen($nim) > 20) {
        $_SESSION['error'] = "NIM harus 5–20 karakter.";
        redirectBack();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format email tidak valid.";
        redirectBack();
    }
    if (containsForbidden($full_name) || containsForbidden($nim)) {
        $_SESSION['error'] = "Nama/NIM mengandung kata terlarang.";
        redirectBack();
    }

    // 🔑 id_ormawa
    $target_ormawa_id = null;
    if ($user_level === 1) {
        $target_ormawa_id = (int) ($_POST['id_ormawa'] ?? 0);
        if ($target_ormawa_id <= 0) {
            $_SESSION['error'] = "Ormawa wajib dipilih.";
            redirectBack();
        }
        $chk = $koneksi->prepare("SELECT id FROM ormawa WHERE id = ?");
        $chk->bind_param("i", $target_ormawa_id);
        $chk->execute();
        if ($chk->get_result()->num_rows === 0) {
            $chk->close();
            $_SESSION['error'] = "Ormawa tidak ditemukan.";
            redirectBack();
        }
        $chk->close();
    } else {
        $target_ormawa_id = $ormawa_id_restricted;
    }

    // Level
    $level = (int) ($_POST['level'] ?? 3);
    // ✅ Batas level untuk Admin
    if ($user_level === 2 && !in_array($level, [3, 4])) {
        $_SESSION['error'] = "Level hanya boleh Pengurus (3) atau Mahasiswa (4).";
        redirectBack();
    }

    if ($action === 'add') {
        if (empty($pass) || strlen($pass) < 6) {
            $_SESSION['error'] = "Password wajib diisi (minimal 6 karakter).";
            redirectBack();
        }

        $check = $koneksi->prepare("SELECT COUNT(*) AS c FROM user WHERE id_ormawa = ? AND (nim = ? OR email = ?)");
        $check->bind_param("iss", $target_ormawa_id, $nim, $email);
        $check->execute();
        $dup = $check->get_result()->fetch_assoc()['c'];
        $check->close();

        if ($dup > 0) {
            $_SESSION['error'] = "NIM/email sudah terdaftar.";
            redirectBack();
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("
            INSERT INTO user (full_name, nim, email, password, level, id_ormawa, is_verified, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
        ");
        $stmt->bind_param("ssssii", $full_name, $nim, $email, $hash, $level, $target_ormawa_id); // ✅ +level

        if ($stmt->execute()) {
            $_SESSION['success'] = "Akun '{$full_name}' berhasil ditambahkan!";
        } else {
            $_SESSION['error'] = "Gagal menyimpan: " . $stmt->error;
        }
        $stmt->close();
        redirectBack();
    } elseif ($action === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = "ID tidak valid.";
            redirectBack();
        }

        $where = $user_level === 1
            ? "id = ? AND level IN (2,3,4)"
            : "id = ? AND id_ormawa = ? AND level IN (3,4)";

        $own = $koneksi->prepare("SELECT id FROM user WHERE $where");
        if ($user_level === 1) {
            $own->bind_param("i", $id);
        } else {
            $own->bind_param("ii", $id, $ormawa_id_restricted);
        }
        $own->execute();
        if ($own->get_result()->num_rows === 0) {
            $own->close();
            $_SESSION['error'] = "Data tidak ditemukan atau bukan milik Anda.";
            redirectBack();
        }
        $own->close();

        $dup = $koneksi->prepare("
            SELECT COUNT(*) AS c FROM user 
            WHERE id_ormawa = ? AND (nim = ? OR email = ?) AND id != ?
        ");
        $dup->bind_param("issi", $target_ormawa_id, $nim, $email, $id);
        $dup->execute();
        $count = $dup->get_result()->fetch_assoc()['c'];
        $dup->close();
        if ($count > 0) {
            $_SESSION['error'] = "NIM/email sudah digunakan.";
            redirectBack();
        }

        // Level
        $level = (int) ($_POST['level'] ?? 3);
        if ($user_level === 2 && !in_array($level, [3, 4])) {
            $_SESSION['error'] = "Level hanya boleh Pengurus (3) atau Mahasiswa (4).";
            redirectBack();
        }

        // ✅ UPDATE: DIPERBAIKI — jumlah bind_param sesuai placeholder
        if (!empty($pass)) {
            if (strlen($pass) < 6) {
                $_SESSION['error'] = "Password minimal 6 karakter.";
                redirectBack();
            }
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $upd = $koneksi->prepare("
                UPDATE user 
                SET full_name = ?, nim = ?, email = ?, password = ?, level = ?, id_ormawa = ?, updated_at = NOW()
                WHERE id = ?
            ");
            // ✅ 7 placeholder → 7 variabel → 'ssssiii' (4 string + 3 integer)
            $upd->bind_param("ssssiii", $full_name, $nim, $email, $hash, $level, $target_ormawa_id, $id);
        } else {
            $upd = $koneksi->prepare("
                UPDATE user 
                SET full_name = ?, nim = ?, email = ?, level = ?, id_ormawa = ?, updated_at = NOW()
                WHERE id = ?
            ");
            // ✅ 6 placeholder → 6 variabel → 'ssiiii' (2 string + 4 integer)
            $upd->bind_param("ssiiii", $full_name, $nim, $email, $level, $target_ormawa_id, $id);
        }

        if ($upd->execute()) {
            $_SESSION['success'] = "Data akun '{$full_name}' berhasil diperbarui!";
        } else {
            $_SESSION['error'] = "Gagal memperbarui: " . $upd->error;
        }
        $upd->close();
        redirectBack();
    }
}

// === HAPUS ===
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        $_SESSION['error'] = "ID tidak valid.";
        redirectBack();
    }

    $where = $user_level === 1
        ? "id = ? AND level IN (2,3,4)"
        : "id = ? AND id_ormawa = ? AND level IN (3,4)";

    $chk = $koneksi->prepare("SELECT full_name FROM user WHERE $where");
    if ($user_level === 1) {
        $chk->bind_param("i", $id);
    } else {
        $chk->bind_param("ii", $id, $ormawa_id_restricted);
    }
    $chk->execute();
    $result = $chk->get_result();
    if ($result->num_rows === 0) {
        $chk->close();
        $_SESSION['error'] = "Data tidak ditemukan.";
        redirectBack();
    }
    $full_name = $result->fetch_assoc()['full_name'];
    $chk->close();

    $del = $koneksi->prepare("DELETE FROM user WHERE id = ?");
    $del->bind_param("i", $id);
    if ($del->execute() && $del->affected_rows > 0) {
        $_SESSION['success'] = "Akun '{$full_name}' berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus.";
    }
    $del->close();
    redirectBack();
}

$_SESSION['error'] = "Aksi tidak dikenali.";
redirectBack();
?>