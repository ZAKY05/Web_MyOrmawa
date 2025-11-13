<?php
session_start(); // Diperlukan karena diakses langsung (bukan via Index.php)

include('../Config/ConnectDB.php');

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../App/View/SuperAdmin/Login.php");
    exit();
}

$user_level = $_SESSION['user_level'];
$ormawa_id_session = $_SESSION['ormawa_id'] ?? 0;
$user_id_session = $_SESSION['user_id'];

// Direktori upload
$target_dir = "../uploads/dokumen/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// --- FUNGSI: TAMBAH ---
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama_dokumen = trim($_POST['nama_dokumen'] ?? '');
    $jenis_dokumen = trim($_POST['jenis_dokumen'] ?? '');
    $id_ormawa = (int)($_POST['id_ormawa'] ?? 0);
    $id_user = (int)($_POST['id_user'] ?? 0);

    // Validasi session & izin
    if ($user_level === 2 && $id_ormawa !== $ormawa_id_session) {
        header("Location: ../App/View/Admin/Index.php?page=document&error=akses_ditolak");
        exit;
    }
    if ($user_level === 2 && $id_user !== $user_id_session) {
        // Opsional: batasi hanya bisa upload dokumen atas nama diri sendiri
        $id_user = $user_id_session;
    }

    // Validasi dasar
    if (empty($nama_dokumen) || empty($jenis_dokumen) || $id_ormawa <= 0) {
        header("Location: ../App/View/Admin/Index.php?page=document&error=data_kosong");
        exit;
    }

    // Upload file
    $file_path = '';
    $uploadOk = 1;

    if (!empty($_FILES['file_dokumen']['name'])) {
        $file = $_FILES['file_dokumen'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];
        
        if (!in_array($ext, $allowed) || $file['size'] > 10 * 1024 * 1024) {
            $uploadOk = 0;
        } else {
            $file_path = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file['name']);
            if (!move_uploaded_file($file['tmp_name'], $target_dir . $file_path)) {
                $uploadOk = 0;
            }
        }
    }

    if ($uploadOk == 0) {
        header("Location: ../App/View/Admin/Index.php?page=document&error=upload_gagal");
        exit;
    }

    // Simpan ke DB
    $tanggal = date('Y-m-d');
    $stmt = mysqli_prepare($koneksi, "INSERT INTO dokumen (nama_dokumen, jenis_dokumen, tanggal_upload, id_ormawa, id_user, file_path) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssiis", $nama_dokumen, $jenis_dokumen, $tanggal, $id_ormawa, $id_user, $file_path);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: /App/View/Admin/Index.php?page=document&success=ditambah");
    } else {
        header("Location: /App/View/Admin/Index.php?page=document&error=query_gagal");
    }
    mysqli_stmt_close($stmt);
    exit;
}

// --- FUNGSI: EDIT ---
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        header("Location: /App/View/Admin/Index.php?page=document&error=id_invalid");
        exit;
    }

    // Ambil data dokumen untuk validasi kepemilikan
    $stmt_check = mysqli_prepare($koneksi, "SELECT id_ormawa FROM dokumen WHERE id = ?");
    mysqli_stmt_bind_param($stmt_check, "i", $id);
    mysqli_stmt_execute($stmt_check);
    $result = mysqli_stmt_get_result($stmt_check);
    $doc = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt_check);

    if (!$doc) {
        header("Location: /App/View/Admin/Index.php?page=document&error=tidak_ditemukan");
        exit;
    }

    // Validasi akses
    if ($user_level === 2 && $doc['id_ormawa'] !== $ormawa_id_session) {
        header("Location: /App/View/Admin/Index.php?page=document&error=akses_ditolak");
        exit;
    }

    $nama_dokumen = trim($_POST['nama_dokumen'] ?? '');
    $jenis_dokumen = trim($_POST['jenis_dokumen'] ?? '');
    $id_ormawa = ($user_level === 1) ? (int)($_POST['id_ormawa'] ?? $doc['id_ormawa']) : $ormawa_id_session;
    $id_user = $user_id_session; // Admin hanya bisa edit atas nama diri sendiri

    if (empty($nama_dokumen) || empty($jenis_dokumen)) {
        header("Location: /App/View/Admin/Index.php?page=document&error=data_kosong");
        exit;
    }

    // Ambil file lama
    $stmt_get = mysqli_prepare($koneksi, "SELECT file_path FROM dokumen WHERE id = ?");
    mysqli_stmt_bind_param($stmt_get, "i", $id);
    mysqli_stmt_execute($stmt_get);
    $old_file = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_get))['file_path'] ?? '';
    mysqli_stmt_close($stmt_get);

    $file_path = $old_file;

    // Proses upload file baru (jika ada)
    if (!empty($_FILES['file_dokumen']['name'])) {
        if ($old_file && file_exists($target_dir . $old_file)) {
            unlink($target_dir . $old_file);
        }

        $file = $_FILES['file_dokumen'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];
        
        if (in_array($ext, $allowed) && $file['size'] <= 10 * 1024 * 1024) {
            $file_path = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file['name']);
            move_uploaded_file($file['tmp_name'], $target_dir . $file_path);
        }
    }

    // Update DB
    $stmt = mysqli_prepare($koneksi, "UPDATE dokumen SET nama_dokumen = ?, jenis_dokumen = ?, id_ormawa = ?, id_user = ?, file_path = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssiiis", $nama_dokumen, $jenis_dokumen, $id_ormawa, $id_user, $file_path, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: /App/View/Admin/Index.php?page=document&success=diedit");
    } else {
        header("Location: /App/View/Admin/Index.php?page=document&error=query_gagal");
    }
    mysqli_stmt_close($stmt);
    exit;
}

// --- FUNGSI: HAPUS ---
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) exit;

    // Ambil info dokumen
    $stmt_check = mysqli_prepare($koneksi, "SELECT id_ormawa, file_path FROM dokumen WHERE id = ?");
    mysqli_stmt_bind_param($stmt_check, "i", $id);
    mysqli_stmt_execute($stmt_check);
    $doc = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_check));
    mysqli_stmt_close($stmt_check);

    if (!$doc) exit;

    // Validasi akses
    if ($user_level === 2 && $doc['id_ormawa'] !== $ormawa_id_session) {
        header("Location: /App/View/Admin/Index.php?page=document&error=akses_ditolak");
        exit;
    }

    // Hapus file
    if ($doc['file_path'] && file_exists($target_dir . $doc['file_path'])) {
        unlink($target_dir . $doc['file_path']);
    }

    // Hapus dari DB
    $stmt = mysqli_prepare($koneksi, "DELETE FROM dokumen WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: /App/View/Admin/Index.php?page=document&success=dihapus");
    exit;
}

header("Location: /App/View/Admin/Index.php?page=document");
exit;
?>