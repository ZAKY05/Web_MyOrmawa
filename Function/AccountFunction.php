<?php
// Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load koneksi
require_once __DIR__ . '/../Config/ConnectDB.php';

// ✅ Validasi login & level
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu.";
    header("Location: ../App/View/SuperAdmin/Login.php");
    exit();
}

$user_level = (int)($_SESSION['user_level'] ?? 0);
$ormawa_id_restricted = null;

if ($user_level === 2) {
    // Admin Organisasi
    $ormawa_id_restricted = (int)($_SESSION['ormawa_id'] ?? 0);
    if ($ormawa_id_restricted <= 0) {
        $_SESSION['error'] = "Anda tidak terdaftar di ORMawa manapun.";
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Account.php'));
        exit();
    }
} elseif ($user_level !== 1) {
    $_SESSION['error'] = "Akses ditolak.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit();
}

// Helper redirect
function redirectBack($fallback = null) {
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

// Cek kata terlarang
function containsForbidden($str) {
    $terms = ['admin', 'hmj-ti', 'hmj ti', 'superadmin', 'root', 'guest'];
    $low = strtolower(trim($str));
    foreach ($terms as $t) {
        if (strpos($low, $t) !== false) return true;
    }
    return false;
}

// Hash password berdasarkan level
function hashPasswordByLevel($password, $level) {
    if ($level == 2) {
        // Level 2 (Admin): MD5
        return md5($password);
    } else {
        // Level 3 (Mahasiswa) & 4 (Pengurus): bcrypt
        return password_hash($password, PASSWORD_BCRYPT);
    }
}

// ============================================================== //
// TAMBAH / EDIT AKUN USER
// ============================================================== //
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $level = (int)($_POST['level'] ?? 0);
    $program_studi = trim($_POST['program_studi'] ?? '');
    $angkatan = trim($_POST['angkatan'] ?? '');

    // Validasi umum
    if (empty($full_name) || empty($nim) || empty($email)) {
        $_SESSION['error'] = "Nama lengkap, NIM, dan email wajib diisi.";
        redirectBack();
    }

    if (empty($program_studi) || empty($angkatan)) {
        $_SESSION['error'] = "Program Studi dan Angkatan wajib diisi.";
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
        $_SESSION['error'] = "Nama/NIM mengandung kata terlarang (admin, hmj-ti, dll).";
        redirectBack();
    }

    // Validasi level
    if (!in_array($level, [2, 3, 4])) {
        $_SESSION['error'] = "Level tidak valid.";
        redirectBack();
    }

    // 🔑 Tentukan id_ormawa target
    $target_ormawa_id = null;
    
    if ($user_level === 1) {
        // SuperAdmin
        $target_ormawa_id = isset($_POST['id_ormawa']) && $_POST['id_ormawa'] !== '' 
            ? (int)$_POST['id_ormawa'] 
            : null;
        
        // Level 2 (Admin) dan 4 (Pengurus) WAJIB punya Ormawa
        if (($level === 2 || $level === 4) && ($target_ormawa_id === null || $target_ormawa_id <= 0)) {
            $_SESSION['error'] = $level === 2 
                ? "Admin (Ketua Ormawa) wajib memilih Ormawa!" 
                : "Pengurus wajib memilih Ormawa!";
            redirectBack();
        }
        
        // AUTO UPDATE: Jika Mahasiswa (3) pilih Ormawa, ubah jadi Pengurus (4)
        if ($level === 3 && $target_ormawa_id !== null && $target_ormawa_id > 0) {
            $level = 4;
        }
        
        // Validasi keberadaan Ormawa
        if ($target_ormawa_id !== null && $target_ormawa_id > 0) {
            $chk = $koneksi->prepare("SELECT id FROM ormawa WHERE id = ?");
            $chk->bind_param("i", $target_ormawa_id);
            $chk->execute();
            if ($chk->get_result()->num_rows === 0) {
                $chk->close();
                $_SESSION['error'] = "Ormawa tidak ditemukan.";
                redirectBack();
            }
            $chk->close();
        }
    } else {
        // Admin Ormawa
        $target_ormawa_id = $ormawa_id_restricted;
        
        if ($level === 2) {
            $_SESSION['error'] = "Anda tidak dapat membuat akun Admin (Ketua Ormawa).";
            redirectBack();
        }
    }

    // ============================================================== //
    // TAMBAH USER BARU
    // ============================================================== //
    if ($action === 'add') {
        if (empty($pass) || strlen($pass) < 6) {
            $_SESSION['error'] = "Password wajib diisi (minimal 6 karakter).";
            redirectBack();
        }

        // Cek email duplikat (GLOBAL - tidak boleh sama di seluruh tabel user)
        $checkEmail = $koneksi->prepare("SELECT COUNT(*) AS c FROM user WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $emailCount = $checkEmail->get_result()->fetch_assoc()['c'];
        $checkEmail->close();
        
        if ($emailCount > 0) {
            $_SESSION['error'] = "Email sudah terdaftar. Gunakan email lain.";
            redirectBack();
        }

        // Cek NIM duplikat di Ormawa yang sama
        if ($target_ormawa_id !== null) {
            $checkNim = $koneksi->prepare("SELECT COUNT(*) AS c FROM user WHERE id_ormawa = ? AND nim = ?");
            $checkNim->bind_param("is", $target_ormawa_id, $nim);
            $checkNim->execute();
            $nimCount = $checkNim->get_result()->fetch_assoc()['c'];
            $checkNim->close();
            
            if ($nimCount > 0) {
                $_SESSION['error'] = "NIM sudah terdaftar di Ormawa ini.";
                redirectBack();
            }
        } else {
            // Mahasiswa tanpa Ormawa: cek NIM global
            $checkNim = $koneksi->prepare("SELECT COUNT(*) AS c FROM user WHERE nim = ?");
            $checkNim->bind_param("s", $nim);
            $checkNim->execute();
            $nimCount = $checkNim->get_result()->fetch_assoc()['c'];
            $checkNim->close();
            
            if ($nimCount > 0) {
                $_SESSION['error'] = "NIM sudah terdaftar.";
                redirectBack();
            }
        }

        // ✅ Validasi 1 Admin per Ormawa
        if ($level === 2 && $target_ormawa_id !== null) {
            $chkAdmin = $koneksi->prepare("SELECT COUNT(*) AS c FROM user WHERE id_ormawa = ? AND level = 2");
            $chkAdmin->bind_param("i", $target_ormawa_id);
            $chkAdmin->execute();
            $adminCount = $chkAdmin->get_result()->fetch_assoc()['c'];
            $chkAdmin->close();
            
            if ($adminCount > 0) {
                $_SESSION['error'] = "Ormawa ini sudah memiliki 1 Admin (Ketua). Tidak boleh ada lebih dari 1 Admin per Ormawa.";
                redirectBack();
            }
        }

        // Hash password sesuai level
        $hash = hashPasswordByLevel($pass, $level);

        // Insert user baru
        if ($target_ormawa_id !== null) {
            $stmt = $koneksi->prepare("
                INSERT INTO user (full_name, nim, email, password, level, id_ormawa, program_studi, angkatan, is_verified, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
            ");
            $stmt->bind_param("ssssisss", $full_name, $nim, $email, $hash, $level, $target_ormawa_id, $program_studi, $angkatan);
        } else {
            $stmt = $koneksi->prepare("
                INSERT INTO user (full_name, nim, email, password, level, id_ormawa, program_studi, angkatan, is_verified, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NULL, ?, ?, 1, NOW(), NOW())
            ");
            $stmt->bind_param("sssssss", $full_name, $nim, $email, $hash, $level, $program_studi, $angkatan);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = "Akun '{$full_name}' berhasil ditambahkan!";
        } else {
            $_SESSION['error'] = "Gagal menyimpan: " . $stmt->error;
        }
        $stmt->close();
        redirectBack();
    }

    // ============================================================== //
    // EDIT USER
    // ============================================================== //
    elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = "ID tidak valid.";
            redirectBack();
        }

        // Ambil data lama
        $where = $user_level === 1 
            ? "id = ? AND level IN (2,3,4)" 
            : "id = ? AND id_ormawa = ? AND level IN (3,4)";
        
        $own = $koneksi->prepare("SELECT id, level, id_ormawa FROM user WHERE $where");
        if ($user_level === 1) {
            $own->bind_param("i", $id);
        } else {
            $own->bind_param("ii", $id, $ormawa_id_restricted);
        }
        $own->execute();
        $result = $own->get_result();
        if ($result->num_rows === 0) {
            $own->close();
            $_SESSION['error'] = "Data tidak ditemukan atau bukan milik Anda.";
            redirectBack();
        }
        $oldData = $result->fetch_assoc();
        $own->close();

        $oldLevel = (int)$oldData['level'];
        $oldOrmawaId = $oldData['id_ormawa'];

        // AUTO UPDATE: Jika Mahasiswa (3) update dengan Ormawa, ubah jadi Pengurus (4)
        if ($level === 3 && $target_ormawa_id !== null && $target_ormawa_id > 0) {
            $level = 4;
        }

        // Validasi Admin
        if ($level === 2 && $target_ormawa_id !== null) {
            if ($oldLevel !== 2) {
                // Cek apakah Ormawa target sudah punya Admin
                $chkAdmin = $koneksi->prepare("SELECT COUNT(*) AS c FROM user WHERE id_ormawa = ? AND level = 2 AND id != ?");
                $chkAdmin->bind_param("ii", $target_ormawa_id, $id);
                $chkAdmin->execute();
                $adminCount = $chkAdmin->get_result()->fetch_assoc()['c'];
                $chkAdmin->close();
                
                if ($adminCount > 0) {
                    $_SESSION['error'] = "Ormawa ini sudah memiliki 1 Admin (Ketua).";
                    redirectBack();
                }
            } elseif ($oldLevel === 2 && $target_ormawa_id !== $oldOrmawaId) {
                // Admin pindah Ormawa
                $chkAdmin = $koneksi->prepare("SELECT COUNT(*) AS c FROM user WHERE id_ormawa = ? AND level = 2");
                $chkAdmin->bind_param("i", $target_ormawa_id);
                $chkAdmin->execute();
                $adminCount = $chkAdmin->get_result()->fetch_assoc()['c'];
                $chkAdmin->close();
                
                if ($adminCount > 0) {
                    $_SESSION['error'] = "Ormawa tujuan sudah memiliki 1 Admin (Ketua).";
                    redirectBack();
                }
            }
        }

        // Cek email duplikat (GLOBAL, kecuali diri sendiri)
        $checkEmail = $koneksi->prepare("SELECT COUNT(*) AS c FROM user WHERE email = ? AND id != ?");
        $checkEmail->bind_param("si", $email, $id);
        $checkEmail->execute();
        $emailCount = $checkEmail->get_result()->fetch_assoc()['c'];
        $checkEmail->close();
        
        if ($emailCount > 0) {
            $_SESSION['error'] = "Email sudah digunakan oleh akun lain.";
            redirectBack();
        }

        // Cek NIM duplikat
        if ($target_ormawa_id !== null) {
            $checkNim = $koneksi->prepare("SELECT COUNT(*) AS c FROM user WHERE id_ormawa = ? AND nim = ? AND id != ?");
            $checkNim->bind_param("isi", $target_ormawa_id, $nim, $id);
        } else {
            $checkNim = $koneksi->prepare("SELECT COUNT(*) AS c FROM user WHERE nim = ? AND id != ?");
            $checkNim->bind_param("si", $nim, $id);
        }
        $checkNim->execute();
        $nimCount = $checkNim->get_result()->fetch_assoc()['c'];
        $checkNim->close();
        
        if ($nimCount > 0) {
            $_SESSION['error'] = "NIM sudah digunakan oleh akun lain.";
            redirectBack();
        }

        // Update data
        if (!empty($pass)) {
            // Update dengan password baru
            if (strlen($pass) < 6) {
                $_SESSION['error'] = "Password minimal 6 karakter.";
                redirectBack();
            }
            $hash = hashPasswordByLevel($pass, $level);
            
            if ($target_ormawa_id !== null) {
                $upd = $koneksi->prepare("
                    UPDATE user 
                    SET full_name = ?, nim = ?, email = ?, password = ?, level = ?, id_ormawa = ?, program_studi = ?, angkatan = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $upd->bind_param("ssssiissi", $full_name, $nim, $email, $hash, $level, $target_ormawa_id, $program_studi, $angkatan, $id);
            } else {
                $upd = $koneksi->prepare("
                    UPDATE user 
                    SET full_name = ?, nim = ?, email = ?, password = ?, level = ?, id_ormawa = NULL, program_studi = ?, angkatan = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $upd->bind_param("ssssissi", $full_name, $nim, $email, $hash, $level, $program_studi, $angkatan, $id);
            }
        } else {
            // Update tanpa mengubah password (password tetap sama seperti saat create)
            if ($target_ormawa_id !== null) {
                $upd = $koneksi->prepare("
                    UPDATE user 
                    SET full_name = ?, nim = ?, email = ?, level = ?, id_ormawa = ?, program_studi = ?, angkatan = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $upd->bind_param("ssisisssi", $full_name, $nim, $email, $level, $target_ormawa_id, $program_studi, $angkatan, $id);
            } else {
                $upd = $koneksi->prepare("
                    UPDATE user 
                    SET full_name = ?, nim = ?, email = ?, level = ?, id_ormawa = NULL, program_studi = ?, angkatan = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $upd->bind_param("sssissi", $full_name, $nim, $email, $level, $program_studi, $angkatan, $id);
            }
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

// ============================================================== //
// HAPUS USER
// ============================================================== //
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
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
        $_SESSION['error'] = "Data tidak ditemukan atau bukan milik Anda.";
        redirectBack();
    }
    $full_name = $result->fetch_assoc()['full_name'];
    $chk->close();

    $del = $koneksi->prepare("DELETE FROM user WHERE id = ?");
    $del->bind_param("i", $id);
    if ($del->execute() && $del->affected_rows > 0) {
        $_SESSION['success'] = "Akun '{$full_name}' berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus akun.";
    }
    $del->close();
    redirectBack();
}

$_SESSION['error'] = "Aksi tidak dikenali.";
redirectBack();
?>