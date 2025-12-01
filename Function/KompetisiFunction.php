<?php
session_start();
require_once __DIR__ . '/../Config/ConnectDB.php';

// ✅ Validasi login & ORMawa
if (!isset($_SESSION['user_id']) || empty($_SESSION['ormawa_id'])) {
    $_SESSION['error'] = "Akses ditolak: Anda tidak memiliki ORMawa.";
    header("Location: ../App/View/SuperAdmin/Login.php");
    exit();
}

$ormawa_id = (int)$_SESSION['ormawa_id'];

// === AKSI: TAMBAH / EDIT (via POST) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Ambil data
    $nama = trim($_POST['nama_kompetisi'] ?? '');
    $penyelenggara = trim($_POST['penyelenggara'] ?? '');
    $periode = trim($_POST['periode'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    // Validasi wajib
    if (!$nama || !$penyelenggara || !$deskripsi) {
        $_SESSION['error'] = "Nama, penyelenggara, dan deskripsi wajib diisi.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    $uploadDir = '../../uploads/kompetisi/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $gambar = '';
    $file_panduan = '';

    // === UPLOAD GAMBAR ===
    if (!empty($_FILES['gambar']['name'])) {
        $file = $_FILES['gambar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png']) && $file['size'] <= 2 * 1024 * 1024) {
            $gambar = uniqid('img_', true) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $gambar)) {
                $_SESSION['error'] = "Gagal mengunggah gambar.";
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            }
        } else {
            $_SESSION['error'] = "Format gambar tidak didukung atau ukuran > 2MB.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }

    // === UPLOAD PANDUAN ===
    if (!empty($_FILES['file_panduan']['name'])) {
        $file = $_FILES['file_panduan'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'doc', 'docx']) && $file['size'] <= 10 * 1024 * 1024) {
            $file_panduan = uniqid('doc_', true) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $file_panduan)) {
                // Hapus gambar jika panduan gagal
                if ($gambar && file_exists($uploadDir . $gambar)) unlink($uploadDir . $gambar);
                $_SESSION['error'] = "Gagal mengunggah file panduan.";
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            }
        } else {
            if ($gambar && file_exists($uploadDir . $gambar)) unlink($uploadDir . $gambar);
            $_SESSION['error'] = "Format file panduan tidak didukung atau ukuran > 10MB.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }

    // ✅ Tambah — Ambil data lama hanya untuk edit
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = "ID tidak valid.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }

        // Ambil data lama untuk cleanup
        $stmt = mysqli_prepare($koneksi, "SELECT gambar, file_panduan FROM kompetisi WHERE id = ? AND id_ormawa = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $ormawa_id);
        mysqli_stmt_execute($stmt);
        $old = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$old) {
            $_SESSION['error'] = "Kompetisi tidak ditemukan atau bukan milik Anda.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }

        // Gunakan nama file lama jika tidak diupload baru
        $gambar = $gambar ?: $old['gambar'];
        $file_panduan = $file_panduan ?: $old['file_panduan'];

        // ✅ Hapus file lama jika diganti
        if ($_FILES['gambar']['name'] && $old['gambar'] && $old['gambar'] !== $gambar) {
            if (file_exists($uploadDir . $old['gambar'])) unlink($uploadDir . $old['gambar']);
        }
        if ($_FILES['file_panduan']['name'] && $old['file_panduan'] && $old['file_panduan'] !== $file_panduan) {
            if (file_exists($uploadDir . $old['file_panduan'])) unlink($uploadDir . $old['file_panduan']);
        }

        // ✅ UPDATE
        $stmt = mysqli_prepare($koneksi, "
            UPDATE kompetisi SET
                nama_kompetisi = ?, penyelenggara = ?, periode = ?, 
                deadline = ?, deskripsi = ?, gambar = ?, file_panduan = ?
            WHERE id = ? AND id_ormawa = ?
        ");
        mysqli_stmt_bind_param($stmt, "sssssssi", 
            $nama, $penyelenggara, $periode, $deadline, $deskripsi, $gambar, $file_panduan, $id, $ormawa_id
        );

    } else {
        // ✅ INSERT — id_ormawa dari SESSION
        $stmt = mysqli_prepare($koneksi, "
            INSERT INTO kompetisi (
                id_ormawa, nama_kompetisi, penyelenggara, periode, 
                deadline, deskripsi, gambar, file_panduan
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "isssssss", 
            $ormawa_id, $nama, $penyelenggara, $periode, $deadline, $deskripsi, $gambar, $file_panduan
        );
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = $action === 'edit' ? "Kompetisi berhasil diperbarui!" : "Kompetisi berhasil ditambahkan!";
    } else {
        // Cleanup file jika gagal
        if ($action !== 'edit' && $gambar && file_exists($uploadDir . $gambar)) unlink($uploadDir . $gambar);
        if ($action !== 'edit' && $file_panduan && file_exists($uploadDir . $file_panduan)) unlink($uploadDir . $file_panduan);
        $_SESSION['error'] = "Gagal menyimpan: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// === AKSI: HAPUS (via GET) ===
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        // Ambil data untuk hapus file
        $stmt = mysqli_prepare($koneksi, "SELECT gambar, file_panduan FROM kompetisi WHERE id = ? AND id_ormawa = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $ormawa_id);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($row) {
            // Hapus dari DB
            $stmt = mysqli_prepare($koneksi, "DELETE FROM kompetisi WHERE id = ? AND id_ormawa = ?");
            mysqli_stmt_bind_param($stmt, "ii", $id, $ormawa_id);
            if (mysqli_stmt_execute($stmt)) {
                // Hapus file
                $uploadDir = '../../uploads/kompetisi/';
                if ($row['gambar'] && file_exists($uploadDir . $row['gambar'])) unlink($uploadDir . $row['gambar']);
                if ($row['file_panduan'] && file_exists($uploadDir . $row['file_panduan'])) unlink($uploadDir . $row['file_panduan']);
                $_SESSION['success'] = "Kompetisi berhasil dihapus!";
            } else {
                $_SESSION['error'] = "Gagal menghapus dari database.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['error'] = "Kompetisi tidak ditemukan atau bukan milik Anda.";
        }
    } else {
        $_SESSION['error'] = "ID tidak valid.";
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'Kompetisi.php'));
    exit();
}

// Redirect default
header("Location: Kompetisi.php");
exit();
?>