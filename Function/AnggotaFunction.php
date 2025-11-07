<?php
session_start();
include('../Config/ConnectDB.php'); // Sesuaikan path jika perlu

// Pastikan user sudah login dan level 2
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 2) {
    $_SESSION['error'] = "Akses ditolak!";
    header("Location: ../App/View/SuperAdmin/Login.php");
    exit();
}

$ormawa_id = $_SESSION['ormawa_id']; // Ambil ID ormawa yang login

// --- AKSI: TAMBAH atau EDIT (via POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Ambil data dari form
        $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
        $departemen = mysqli_real_escape_string($koneksi, trim($_POST['departemen']));
        $jabatan = mysqli_real_escape_string($koneksi, trim($_POST['jabatan']));
        $no_telpon = mysqli_real_escape_string($koneksi, trim($_POST['no_telpon']));
        $prodi = mysqli_real_escape_string($koneksi, trim($_POST['prodi']));

        // Validasi sederhana
        if (empty($nama) || empty($departemen) || empty($jabatan) || empty($no_telpon) || empty($prodi)) {
            $_SESSION['error'] = "Semua field wajib diisi!";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }

        // Simpan ke database — SERTAKAN id_ormawa
        $query = "INSERT INTO anggota (nama, departemen, jabatan, no_telpon, prodi, id_ormawa) 
                  VALUES ('$nama', '$departemen', '$jabatan', '$no_telpon', '$prodi', '$ormawa_id')";

        if (mysqli_query($koneksi, $query)) {
            $_SESSION['success'] = "Data anggota berhasil ditambahkan!";
        } else {
            $_SESSION['error'] = "Gagal menambahkan data: " . mysqli_error($koneksi);
        }

    } elseif ($action === 'edit') {
        $id = mysqli_real_escape_string($koneksi, $_POST['id']);
        $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
        $departemen = mysqli_real_escape_string($koneksi, trim($_POST['departemen']));
        $jabatan = mysqli_real_escape_string($koneksi, trim($_POST['jabatan'])); 
        $no_telpon = mysqli_real_escape_string($koneksi, trim($_POST['no_telpon']));
        $prodi = mysqli_real_escape_string($koneksi, trim($_POST['prodi']));

        // Update data — pastikan hanya milik ormawa ini
        $query = "UPDATE anggota SET 
                    nama = '$nama',
                    departemen = '$departemen',
                    jabatan = '$jabatan',
                    no_telpon = '$no_telpon',
                    prodi = '$prodi'
                  WHERE id = '$id' AND id_ormawa = '$ormawa_id'"; // <-- FILTER BY ORM AWAL

        if (mysqli_query($koneksi, $query)) {
            $_SESSION['success'] = "Data anggota berhasil diperbarui!";
        } else {
            $_SESSION['error'] = "Gagal memperbarui data: " . mysqli_error($koneksi);
        }
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Index.php?page=anggota'));
    exit();
}

// --- AKSI: HAPUS (via GET) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        // Hapus dari database — pastikan hanya milik ormawa ini
        $query = "DELETE FROM anggota WHERE id = $id AND id_ormawa = $ormawa_id";
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['success'] = "Data anggota berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus data.";
        }
    } else {
        $_SESSION['error'] = "ID tidak valid.";
    }

    // Redirect ke halaman anggota
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Index.php?page=anggota'));
    exit();
}

// Jika diakses langsung tanpa aksi
header("Location: ../App/View/Admin/Index.php?page=anggota");
exit();
?>