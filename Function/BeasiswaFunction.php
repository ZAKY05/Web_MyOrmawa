<?php
session_start();
// ✅ AMAN: include dengan error handling
if (!file_exists(__DIR__ . '/../Config/ConnectDB.php')) {
    $_SESSION['error'] = "File koneksi tidak ditemukan.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../Admin/Beasiswa.php'));
    exit();
}
require_once __DIR__ . '/../Config/ConnectDB.php';

// ✅ Validasi koneksi & session
if (!$koneksi || mysqli_connect_errno()) {
    $_SESSION['error'] = "Koneksi database gagal.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../Admin/Beasiswa.php'));
    exit();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['ormawa_id'])) {
    $_SESSION['error'] = "Akses ditolak: login sebagai ORMawa diperlukan.";
    header("Location: ../App/View/SuperAdmin/Login.php");
    exit();
}

$ormawa_id = (int)$_SESSION['ormawa_id'];

// === TAMBAH / EDIT ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $nama = trim($_POST['nama_beasiswa'] ?? '');
    $penyelenggara = trim($_POST['penyelenggara'] ?? '');
    $periode = trim($_POST['periode'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!$nama || !$penyelenggara || !$deskripsi) {
        $_SESSION['error'] = "Nama, penyelenggara, dan deskripsi wajib diisi.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Validasi dan konversi deadline
    if (!empty($deadline)) {
        // Validasi format tanggal
        if (!strtotime($deadline)) {
            $_SESSION['error'] = "Format tanggal deadline tidak valid.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
        // Pastikan format tanggal dalam format MySQL (YYYY-MM-DD)
        $deadline = date('Y-m-d', strtotime($deadline));
    } else {
        $deadline = null; // Set ke NULL jika kosong
    }

    $uploadDir = '../uploads/beasiswa/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        $_SESSION['error'] = "Gagal membuat folder upload.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
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
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            }
        } else {
            $_SESSION['error'] = "Format gambar tidak didukung atau ukuran > 2MB.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
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
                $_SESSION['error'] = "Gagal upload file panduan.";
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            }
        } else {
            if ($gambar && file_exists($uploadDir . $gambar)) unlink($uploadDir . $gambar);
            $_SESSION['error'] = "Format panduan tidak didukung atau ukuran > 10MB.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }

    // Edit
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = "ID tidak valid.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $stmt = mysqli_prepare($koneksi, "SELECT gambar, file_panduan FROM beasiswa WHERE id = ? AND id_ormawa = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $ormawa_id);
        mysqli_stmt_execute($stmt);
        $old = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$old) {
            $_SESSION['error'] = "Beasiswa tidak ditemukan.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $gambar = $gambar ?: $old['gambar'];
        $file_panduan = $file_panduan ?: $old['file_panduan'];

        if ($_FILES['gambar']['name'] && $old['gambar'] && $old['gambar'] !== $gambar) {
            if (file_exists($uploadDir . $old['gambar'])) unlink($uploadDir . $old['gambar']);
        }
        if ($_FILES['file_panduan']['name'] && $old['file_panduan'] && $old['file_panduan'] !== $file_panduan) {
            if (file_exists($uploadDir . $old['file_panduan'])) unlink($uploadDir . $old['file_panduan']);
        }

        $stmt = mysqli_prepare($koneksi, "
            UPDATE beasiswa SET 
                nama_beasiswa = ?, penyelenggara = ?, periode = ?, 
                deadline = ?, deskripsi = ?, gambar = ?, file_panduan = ?
            WHERE id = ? AND id_ormawa = ?
        ");
        $bind = mysqli_stmt_bind_param($stmt, "sssssssi", 
            $nama, $penyelenggara, $periode, $deadline, $deskripsi, $gambar, $file_panduan, $id, $ormawa_id
        );
    } else {
        // Tambah
        $stmt = mysqli_prepare($koneksi, "
            INSERT INTO beasiswa (
                id_ormawa, nama_beasiswa, penyelenggara, periode, 
                deadline, deskripsi, gambar, file_panduan
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $bind = mysqli_stmt_bind_param($stmt, "isssssss", 
            $ormawa_id, $nama, $penyelenggara, $periode, $deadline, $deskripsi, $gambar, $file_panduan
        );
    }

    if (!$stmt || !$bind) {
        if ($action !== 'edit' && $gambar && file_exists($uploadDir . $gambar)) unlink($uploadDir . $gambar);
        if ($action !== 'edit' && $file_panduan && file_exists($uploadDir . $file_panduan)) unlink($uploadDir . $file_panduan);
        $_SESSION['error'] = "Error query: " . mysqli_error($koneksi);
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = $action === 'edit' ? "Beasiswa berhasil diperbarui!" : "Beasiswa berhasil ditambahkan!";
    } else {
        if ($action !== 'edit' && $gambar && file_exists($uploadDir . $gambar)) unlink($uploadDir . $gambar);
        if ($action !== 'edit' && $file_panduan && file_exists($uploadDir . $file_panduan)) unlink($uploadDir . $file_panduan);
        $_SESSION['error'] = "Gagal menyimpan: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// === HAPUS ===
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        $_SESSION['error'] = "ID tidak valid.";
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../Admin/Beasiswa.php'));
        exit();
    }

    $stmt = mysqli_prepare($koneksi, "SELECT gambar, file_panduan FROM beasiswa WHERE id = ? AND id_ormawa = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $ormawa_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($row) {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM beasiswa WHERE id = ? AND id_ormawa = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $ormawa_id);
        if (mysqli_stmt_execute($stmt)) {
            if ($row['gambar'] && file_exists($uploadDir . $row['gambar'])) unlink($uploadDir . $row['gambar']);
            if ($row['file_panduan'] && file_exists($uploadDir . $row['file_panduan'])) unlink($uploadDir . $row['file_panduan']);
            $_SESSION['success'] = "Beasiswa berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus dari database.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Beasiswa tidak ditemukan.";
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../Admin/Beasiswa.php'));
    exit();
}

header("Location: ../Admin/Beasiswa.php");
exit();
?>