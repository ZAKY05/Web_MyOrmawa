<?php
session_start();
include('../Config/ConnectDB.php');

// Validasi dan format datetime
function formatDateTime($date, $time) {
    // Gabungkan jadi "Y-m-d H:i:s"
    $datetime = $date . ' ' . $time . ':00';
    // Validasi apakah format benar
    if (DateTime::createFromFormat('Y-m-d H:i:s', $datetime)) {
        return $datetime;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    $nama_kegiatan = trim($_POST['nama_kegiatan'] ?? '');
    $agenda = trim($_POST['agenda'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? '');
    $jam_mulai_input = trim($_POST['jam_mulai'] ?? '');
    $jam_selesai_input = trim($_POST['jam_selesai'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');

    if (empty($nama_kegiatan) || empty($agenda) || empty($tanggal) || empty($jam_mulai_input) || empty($jam_selesai_input) || empty($lokasi)) {
        $_SESSION['error'] = "Semua field wajib diisi!";
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Kegiatan.php'));
        exit();
    }

    // Format jam_mulai dan jam_selesai menjadi TIMESTAMP lengkap
    $jam_mulai_full = formatDateTime($tanggal, $jam_mulai_input);
    $jam_selesai_full = formatDateTime($tanggal, $jam_selesai_input);

    if (!$jam_mulai_full || !$jam_selesai_full) {
        $_SESSION['error'] = "Format tanggal atau jam tidak valid.";
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Kegiatan.php'));
        exit();
    }

    if ($action === 'add') {
        $stmt = $koneksi->prepare("INSERT INTO kegiatan (nama_kegiatan, agenda, tanggal, jam_mulai, jam_selesai, lokasi) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nama_kegiatan, $agenda, $tanggal, $jam_mulai_full, $jam_selesai_full, $lokasi);
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            $_SESSION['error'] = "ID tidak valid.";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Kegiatan.php'));
            exit();
        }
        $stmt = $koneksi->prepare("UPDATE kegiatan SET 
                                   nama_kegiatan = ?, agenda = ?, tanggal = ?, jam_mulai = ?, jam_selesai = ?, lokasi = ? 
                                   WHERE id = ?");
        $stmt->bind_param("ssssssi", $nama_kegiatan, $agenda, $tanggal, $jam_mulai_full, $jam_selesai_full, $lokasi, $id);
    } else {
        $_SESSION['error'] = "Aksi tidak dikenal.";
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Kegiatan.php'));
        exit();
    }

    if ($stmt && $stmt->execute()) {
        $_SESSION['success'] = $action === 'add' ? "Kegiatan berhasil ditambahkan!" : "Kegiatan berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Gagal menyimpan data kegiatan.";
        error_log("KegiatanFunction error: " . ($stmt ? $stmt->error : $koneksi->error));
    }

    if (isset($stmt)) $stmt->close();
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Kegiatan.php'));
    exit();
}

// --- HAPUS ---
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $koneksi->prepare("DELETE FROM kegiatan WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Kegiatan berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus kegiatan.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "ID tidak valid.";
    }
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Kegiatan.php'));
    exit();
}

header("Location: ../App/View/Admin/Kegiatan.php");
exit();
?>