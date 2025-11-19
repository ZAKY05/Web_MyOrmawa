<?php
session_start();
include('../Config/ConnectDB.php'); // Sesuaikan path jika perlu

// Pastikan user sudah login dan level 2 (ORMawa)
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 2) {
    $_SESSION['error'] = "Akses ditolak!";
    header("Location: ../App/View/SuperAdmin/Login.php");
    exit();
}

$ormawa_id = (int)$_SESSION['ormawa_id']; // Ambil ID ormawa yang login

// --- AKSI: TAMBAH atau EDIT (via POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Ambil & amankan data
    $nama_kegiatan = mysqli_real_escape_string($koneksi, trim($_POST['nama_kegiatan']));
    $agenda        = mysqli_real_escape_string($koneksi, trim($_POST['agenda']));
    $tanggal       = mysqli_real_escape_string($koneksi, trim($_POST['tanggal']));
    $jam_mulai     = mysqli_real_escape_string($koneksi, trim($_POST['jam_mulai']));
    $jam_selesai   = mysqli_real_escape_string($koneksi, trim($_POST['jam_selesai']));
    $lokasi        = mysqli_real_escape_string($koneksi, trim($_POST['lokasi']));

    // Validasi
    if (empty($nama_kegiatan) || empty($agenda) || empty($tanggal) || 
        empty($jam_mulai) || empty($jam_selesai) || empty($lokasi)) {
        $_SESSION['error'] = "Semua field wajib diisi!";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    if ($action === 'add') {
        // Format jam_mulai & jam_selesai → 'H:i:s'
        $jam_mulai_full   = $jam_mulai . ':00';
        $jam_selesai_full = $jam_selesai . ':00';

        $query = "INSERT INTO kegiatan 
                  (nama_kegiatan, agenda, tanggal, jam_mulai, jam_selesai, lokasi, id_ormawa) 
                  VALUES ('$nama_kegiatan', '$agenda', '$tanggal', '$jam_mulai_full', '$jam_selesai_full', '$lokasi', '$ormawa_id')";

    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        if ($id <= 0) {
            $_SESSION['error'] = "ID tidak valid.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $jam_mulai_full   = $jam_mulai . ':00';
        $jam_selesai_full = $jam_selesai . ':00';

        $query = "UPDATE kegiatan SET 
                    nama_kegiatan = '$nama_kegiatan',
                    agenda = '$agenda',
                    tanggal = '$tanggal',
                    jam_mulai = '$jam_mulai_full',
                    jam_selesai = '$jam_selesai_full',
                    lokasi = '$lokasi'
                  WHERE id = '$id' AND id_ormawa = '$ormawa_id'"; // ✅ Filter by ORMawa

    } else {
        $_SESSION['error'] = "Aksi tidak dikenal.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    if (mysqli_query($koneksi, $query)) {
        $_SESSION['success'] = $action === 'add' ? "Kegiatan berhasil ditambahkan!" : "Kegiatan berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Gagal memproses data: " . mysqli_error($koneksi);
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Index.php?page=kegiatan'));
    exit();
}

// --- AKSI: HAPUS (via GET) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        $query = "DELETE FROM kegiatan WHERE id = $id AND id_ormawa = $ormawa_id"; // ✅ Filter by ORMawa
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['success'] = "Kegiatan berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus kegiatan.";
        }
    } else {
        $_SESSION['error'] = "ID tidak valid.";
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Index.php?page=kegiatan'));
    exit();
}

// Jika diakses langsung tanpa aksi
header("Location: ../App/View/Admin/Index.php?page=kegiatan");
exit();
?>