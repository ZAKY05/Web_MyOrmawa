<?php
session_start();
require_once __DIR__ . '/../Config/ConnectDB.php';

// ✅ Validasi login & tentukan hak akses
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
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../Admin/Kompetisi.php'));
        exit();
    }
} elseif ($user_level !== 1) {
    $_SESSION['error'] = "Akses ditolak.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit();
}

$uploadDir = '../../uploads/kompetisi/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Helper redirect
function redirectBack($fallback = null) {
    $fallback = $fallback ?? (
        $_SESSION['user_level'] == 1 
            ? '../App/View/SuperAdmin/Kompetisi.php' 
            : '../App/View/Admin/Kompetisi.php'
    );
    $ref = $_SERVER['HTTP_REFERER'] ?? $fallback;
    if (strpos($ref, 'Login.php') !== false || empty($ref)) $ref = $fallback;
    header("Location: $ref");
    exit();
}

// === TAMBAH / EDIT ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $nama = trim($_POST['nama_kompetisi'] ?? '');
    $penyelenggara = trim($_POST['penyelenggara'] ?? '');
    $periode = trim($_POST['periode'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!$nama || !$penyelenggara || !$deskripsi) {
        $_SESSION['error'] = "Nama, penyelenggara, dan deskripsi wajib diisi.";
        redirectBack();
    }

    // 🔑 Tentukan id_ormawa target
    $target_ormawa_id = null;
    if ($user_level === 1) {
        $target_ormawa_id = (int)($_POST['id_ormawa'] ?? 0);
        if ($target_ormawa_id <= 0) {
            $_SESSION['error'] = "Ormawa wajib dipilih.";
            redirectBack();
        }
        $chk = $koneksi->prepare("SELECT id FROM ormawa WHERE id = ?");
        $chk->bind_param("i", $target_ormawa_id);
        $chk->execute();
        if ($chk->get_result()->num_rows === 0) {
            $_SESSION['error'] = "Ormawa tidak ditemukan.";
            redirectBack();
        }
        $chk->close();
    } else {
        $target_ormawa_id = $ormawa_id_restricted;
    }

    $gambar = '';
    $file_panduan = '';

    // Upload gambar
    if (!empty($_FILES['gambar']['name'])) {
        $file = $_FILES['gambar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png']) && $file['size'] <= 2*1024*1024) {
            $gambar = uniqid('img_', true) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $gambar)) {
                $_SESSION['error'] = "Gagal upload gambar.";
                redirectBack();
            }
        } else {
            $_SESSION['error'] = "Format gambar tidak didukung atau ukuran > 2MB.";
            redirectBack();
        }
    }

    // Upload panduan
    if (!empty($_FILES['file_panduan']['name'])) {
        $file = $_FILES['file_panduan'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf','doc','docx']) && $file['size'] <= 10*1024*1024) {
            $file_panduan = uniqid('doc_', true) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $file_panduan)) {
                if ($gambar && file_exists($uploadDir . $gambar)) unlink($uploadDir . $gambar);
                $_SESSION['error'] = "Gagal upload panduan.";
                redirectBack();
            }
        } else {
            if ($gambar && file_exists($uploadDir . $gambar)) unlink($uploadDir . $gambar);
            $_SESSION['error'] = "Format panduan tidak didukung atau ukuran > 10MB.";
            redirectBack();
        }
    }

    // Edit
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = "ID tidak valid.";
            redirectBack();
        }

        // ✅ Cek kepemilikan
        $where = $user_level === 1 ? "id = ?" : "id = ? AND id_ormawa = ?";
        $stmt = $koneksi->prepare("SELECT gambar, file_panduan FROM kompetisi WHERE $where");
        if ($user_level === 1) {
            $stmt->bind_param("i", $id);
        } else {
            $stmt->bind_param("ii", $id, $ormawa_id_restricted);
        }
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$old) {
            $_SESSION['error'] = "Kompetisi tidak ditemukan atau bukan milik Anda.";
            redirectBack();
        }

        $gambar = $gambar ?: $old['gambar'];
        $file_panduan = $file_panduan ?: $old['file_panduan'];

        if ($_FILES['gambar']['name'] && $old['gambar'] && $old['gambar'] !== $gambar) {
            if (file_exists($uploadDir . $old['gambar'])) unlink($uploadDir . $old['gambar']);
        }
        if ($_FILES['file_panduan']['name'] && $old['file_panduan'] && $old['file_panduan'] !== $file_panduan) {
            if (file_exists($uploadDir . $old['file_panduan'])) unlink($uploadDir . $old['file_panduan']);
        }

        $stmt = $koneksi->prepare("
            UPDATE kompetisi SET
                id_ormawa = ?, nama_kompetisi = ?, penyelenggara = ?, periode = ?, 
                deadline = ?, deskripsi = ?, gambar = ?, file_panduan = ?
            WHERE id = ?
        ");
        $stmt->bind_param("isssssssi", 
            $target_ormawa_id, $nama, $penyelenggara, $periode, $deadline, $deskripsi, $gambar, $file_panduan, $id
        );

    } else { // Tambah
        $stmt = $koneksi->prepare("
            INSERT INTO kompetisi (
                id_ormawa, nama_kompetisi, penyelenggara, periode, 
                deadline, deskripsi, gambar, file_panduan
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssssss", 
            $target_ormawa_id, $nama, $penyelenggara, $periode, $deadline, $deskripsi, $gambar, $file_panduan
        );
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = $action === 'edit' ? "Kompetisi berhasil diperbarui!" : "Kompetisi berhasil ditambahkan!";
    } else {
        if ($action !== 'edit' && $gambar && file_exists($uploadDir . $gambar)) unlink($uploadDir . $gambar);
        if ($action !== 'edit' && $file_panduan && file_exists($uploadDir . $file_panduan)) unlink($uploadDir . $file_panduan);
        $_SESSION['error'] = "Gagal menyimpan: " . $stmt->error;
    }
    $stmt->close();
    redirectBack();
}

// === HAPUS ===
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        $_SESSION['error'] = "ID tidak valid.";
        redirectBack();
    }

    // ✅ Cek kepemilikan
    $where = $user_level === 1 ? "id = ?" : "id = ? AND id_ormawa = ?";
    $stmt = $koneksi->prepare("SELECT gambar, file_panduan FROM kompetisi WHERE $where");
    if ($user_level === 1) {
        $stmt->bind_param("i", $id);
    } else {
        $stmt->bind_param("ii", $id, $ormawa_id_restricted);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        $stmt = $koneksi->prepare("DELETE FROM kompetisi WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if ($row['gambar'] && file_exists($uploadDir . $row['gambar'])) unlink($uploadDir . $row['gambar']);
            if ($row['file_panduan'] && file_exists($uploadDir . $row['file_panduan'])) unlink($uploadDir . $row['file_panduan']);
            $_SESSION['success'] = "Kompetisi berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus dari database.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Kompetisi tidak ditemukan atau bukan milik Anda.";
    }
    redirectBack();
}

$_SESSION['error'] = "Aksi tidak dikenali.";
redirectBack();
?>