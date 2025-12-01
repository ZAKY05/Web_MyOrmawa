<?php
session_start();
include('../Config/ConnectDB.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu.";
    header("Location: ../App/View/SuperAdmin/Login.php");
    exit();
}

$user_level = $_SESSION['user_level'];
$ormawa_id_session = $_SESSION['ormawa_id'] ?? 0;
$user_id_session = $_SESSION['user_id'];

$target_dir = "../uploads/dokumen/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// Helper
function redirectDocument() {
    header("Location: ../App/View/Admin/Index.php?page=doc");
    exit;
}

// --- TAMBAH ---
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama_dokumen = trim($_POST['nama_dokumen'] ?? '');
    $jenis_dokumen = trim($_POST['jenis_dokumen'] ?? '');
    $id_ormawa = (int)($_POST['id_ormawa'] ?? 0);

    if ($user_level === 2) {
        $id_ormawa = $ormawa_id_session;
    }

    if (empty($nama_dokumen) || empty($jenis_dokumen) || $id_ormawa <= 0) {
        $_SESSION['error'] = "Semua field wajib diisi!";
        redirectDocument();
    }

    // Upload file
    if (empty($_FILES['file_dokumen']['name'])) {
        $_SESSION['error'] = "File dokumen wajib diunggah!";
        redirectDocument();
    }

    $file = $_FILES['file_dokumen'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];
    $maxSize = 10 * 1024 * 1024; // 10MB

    if (!in_array($ext, $allowed) || $file['size'] > $maxSize) {
        $_SESSION['error'] = "File tidak didukung atau ukuran > 10MB.";
        redirectDocument();
    }

    $file_path = uniqid('doc_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
    $full_path = $target_dir . $file_path;

    if (!move_uploaded_file($file['tmp_name'], $full_path)) {
        $_SESSION['error'] = "Gagal mengunggah file.";
        redirectDocument();
    }

    // Hitung ukuran (KB)
    $ukuran_kb = round(filesize($full_path) / 1024, 2);

    // Simpan ke DB
    $tanggal = date('Y-m-d H:i:s');
    $stmt = $koneksi->prepare(
        "INSERT INTO dokumen (nama_dokumen, jenis_dokumen, tanggal_upload, id_ormawa, id_user, file_path, ukuran_file) 
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param("sssiisd", $nama_dokumen, $jenis_dokumen, $tanggal, $id_ormawa, $user_id_session, $file_path, $ukuran_kb);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Dokumen berhasil ditambahkan!";
    } else {
        unlink($full_path); // hapus file jika gagal insert
        $_SESSION['error'] = "Gagal menyimpan data dokumen.";
    }
    $stmt->close();
    redirectDocument();
}

// --- EDIT ---
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        $_SESSION['error'] = "ID dokumen tidak valid.";
        redirectDocument();
    }

    // Validasi kepemilikan
    $stmt_check = $koneksi->prepare("SELECT id_ormawa, file_path FROM dokumen WHERE id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $doc = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if (!$doc) {
        $_SESSION['error'] = "Dokumen tidak ditemukan.";
        redirectDocument();
    }

    if ($user_level === 2 && $doc['id_ormawa'] !== $ormawa_id_session) {
        $_SESSION['error'] = "Anda tidak berhak mengedit dokumen ini.";
        redirectDocument();
    }

    $nama_dokumen = trim($_POST['nama_dokumen'] ?? '');
    $jenis_dokumen = trim($_POST['jenis_dokumen'] ?? '');
    if (empty($nama_dokumen) || empty($jenis_dokumen)) {
        $_SESSION['error'] = "Nama dan jenis dokumen wajib diisi!";
        redirectDocument();
    }

    $old_file = $doc['file_path'];
    $new_file_path = $old_file;

    // Upload file baru (opsional)
    if (!empty($_FILES['file_dokumen']['name'])) {
        $file = $_FILES['file_dokumen'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];
        if (in_array($ext, $allowed) && $file['size'] <= 10 * 1024 * 1024) {
            // Hapus file lama
            if ($old_file && file_exists($target_dir . $old_file)) {
                unlink($target_dir . $old_file);
            }
            // Simpan baru
            $new_file_path = uniqid('doc_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
            move_uploaded_file($file['tmp_name'], $target_dir . $new_file_path);
            $ukuran_kb = round(filesize($target_dir . $new_file_path) / 1024, 2);
        } else {
            $_SESSION['error'] = "File tidak valid. Gunakan PDF/DOC/JPG/PNG ≤10MB.";
            redirectDocument();
        }
    } else {
        // Jika tidak ganti file, ambil ukuran lama
        $stmt_size = $koneksi->prepare("SELECT ukuran_file FROM dokumen WHERE id = ?");
        $stmt_size->bind_param("i", $id);
        $stmt_size->execute();
        $ukuran_kb = $stmt_size->get_result()->fetch_assoc()['ukuran_file'] ?? 0;
        $stmt_size->close();
    }

    // Update DB
    $stmt = $koneksi->prepare(
        "UPDATE dokumen SET nama_dokumen = ?, jenis_dokumen = ?, file_path = ?, ukuran_file = ? WHERE id = ?"
    );
    $stmt->bind_param("sssdi", $nama_dokumen, $jenis_dokumen, $new_file_path, $ukuran_kb, $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Dokumen berhasil ditambahkan!";
        $stmt->close(); // ← pastikan close dulu
        redirectDocument(); // ← baru redirect
    } else {
        $stmt->close();
        $_SESSION['error'] = "Gagal menyimpan.";
        redirectDocument();
    }
}

// --- HAPUS ---
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        $_SESSION['error'] = "ID tidak valid.";
        redirectDocument();
    }

    $stmt_check = $koneksi->prepare("SELECT id_ormawa, file_path FROM dokumen WHERE id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $doc = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if (!$doc) {
        $_SESSION['error'] = "Dokumen tidak ditemukan.";
        redirectDocument();
    }

    if ($user_level === 2 && $doc['id_ormawa'] !== $ormawa_id_session) {
        $_SESSION['error'] = "Akses ditolak.";
        redirectDocument();
    }

    // Hapus file fisik
    $file_path = $target_dir . $doc['file_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Hapus dari DB
    $stmt = $koneksi->prepare("DELETE FROM dokumen WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['success'] = "Dokumen berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus dokumen.";
    }
    $stmt->close();
    redirectDocument();
}

redirectDocument();
?>