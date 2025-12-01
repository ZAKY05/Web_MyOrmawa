<?php
session_start();
include('../Config/ConnectDB.php');

// Pastikan user sudah login dan level 2
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 2) {
    $_SESSION['error'] = "Akses ditolak!";
    header("Location: ../App/View/SuperAdmin/Login.php");
    exit();
}

$ormawa_id = $_SESSION['ormawa_id'] ?? 0;

if ($ormawa_id <= 0) {
    $_SESSION['error'] = "Session tidak valid. Silakan login ulang.";
    header("Location: ../App/View/Admin/Index.php?page=anggota");
    exit();
}

// Bersihkan input secara aman
function cleanInput($data) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, trim($data));
}

// --- AKSI: TAMBAH atau EDIT (via POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $nama = cleanInput($_POST['nama'] ?? '');
        $departemen = cleanInput($_POST['departemen'] ?? '');
        $jabatan = cleanInput($_POST['jabatan'] ?? '');
        $no_telpon = cleanInput($_POST['no_telpon'] ?? '');
        $prodi = cleanInput($_POST['prodi'] ?? '');

        if (empty($nama) || empty($departemen) || empty($jabatan) || empty($no_telpon) || empty($prodi)) {
            $_SESSION['error'] = "Semua field wajib diisi!";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Index.php?page=anggota'));
            exit();
        }

        $query = "INSERT INTO anggota (nama, departemen, jabatan, no_telpon, prodi, id_ormawa) 
                  VALUES ('$nama', '$departemen', '$jabatan', '$no_telpon', '$prodi', '$ormawa_id')";

        if (mysqli_query($koneksi, $query)) {
            $_SESSION['success'] = "Data anggota berhasil ditambahkan!";
        } else {
            $_SESSION['error'] = "Gagal menambahkan data: " . mysqli_error($koneksi);
        }

    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = "ID tidak valid.";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Index.php?page=anggota'));
            exit();
        }

        $nama = cleanInput($_POST['nama'] ?? '');
        $departemen = cleanInput($_POST['departemen'] ?? '');
        $jabatan = cleanInput($_POST['jabatan'] ?? '');
        $no_telpon = cleanInput($_POST['no_telpon'] ?? '');
        $prodi = cleanInput($_POST['prodi'] ?? '');

        if (empty($nama) || empty($departemen) || empty($jabatan) || empty($no_telpon) || empty($prodi)) {
            $_SESSION['error'] = "Semua field wajib diisi!";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Index.php?page=anggota'));
            exit();
        }

        // Pastikan hanya bisa edit data milik ormawa ini
        $query = "UPDATE anggota SET 
                    nama = '$nama',
                    departemen = '$departemen',
                    jabatan = '$jabatan',
                    no_telpon = '$no_telpon',
                    prodi = '$prodi'
                  WHERE id = $id AND id_ormawa = $ormawa_id";

        $result = mysqli_query($koneksi, $query);
        $affected = mysqli_affected_rows($koneksi);

        if ($result && $affected > 0) {
            $_SESSION['success'] = "Data anggota berhasil diperbarui!";
        } else {
            $_SESSION['error'] = "Gagal memperbarui data. Pastikan data milik Anda.";
        }
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Index.php?page=anggota'));
    exit();
}

// --- AKSI: HAPUS (via GET) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        $query = "DELETE FROM anggota WHERE id = $id AND id_ormawa = $ormawa_id";
        $result = mysqli_query($koneksi, $query);
        $affected = mysqli_affected_rows($koneksi);

        if ($result && $affected > 0) {
            $_SESSION['success'] = "Data anggota berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus data. Pastikan data milik Anda.";
        }
    } else {
        $_SESSION['error'] = "ID tidak valid.";
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../App/View/Admin/Index.php?page=anggota'));
    exit();
}

// Fallback
header("Location: ../App/View/Admin/Index.php?page=anggota");
exit();
?>