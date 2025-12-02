<?php
session_start();

// ✅ Koneksi
if (!file_exists(__DIR__ . '/../Config/ConnectDB.php')) {
    $_SESSION['error'] = "File koneksi tidak ditemukan.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit();
}
require_once __DIR__ . '/../Config/ConnectDB.php';

// ✅ Validasi login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu.";
    header("Location: ../App/View/SuperAdmin/Login.php");
    exit();
}

// ✅ Tentukan level & batasan ORMawa
$user_level = (int)($_SESSION['user_level'] ?? 0);
$ormawa_id_restricted = null; // null = SuperAdmin (semua), angka = hanya milik Ormawa tsb

if ($user_level === 2) { // Admin Organisasi
    $ormawa_id_restricted = (int)($_SESSION['ormawa_id'] ?? 0);
    if ($ormawa_id_restricted <= 0) {
        $_SESSION['error'] = "Anda tidak terdaftar di ORMawa manapun.";
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../Admin/Beasiswa.php'));
        exit();
    }
} elseif ($user_level !== 1) { // Bukan SuperAdmin (1) atau Admin (2)
    $_SESSION['error'] = "Akses ditolak.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit();
}

$uploadDir = '../uploads/beasiswa/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

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

    // 🔑 Ambil id_ormawa tergantung level
    $submitted_ormawa_id = null;
    if ($user_level === 1) { // SuperAdmin
        $submitted_ormawa_id = (int)($_POST['id_ormawa'] ?? 0);
        if ($submitted_ormawa_id <= 0) {
            $_SESSION['error'] = "Ormawa wajib dipilih.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
    } else { // Admin Organisasi
        $submitted_ormawa_id = $ormawa_id_restricted;
    }

    // Validasi: pastikan ORMawa benar-benar ada
    $check = mysqli_prepare($koneksi, "SELECT id FROM ormawa WHERE id = ?");
    mysqli_stmt_bind_param($check, "i", $submitted_ormawa_id);
    mysqli_stmt_execute($check);
    if (!mysqli_stmt_get_result($check)->num_rows) {
        $_SESSION['error'] = "Ormawa tidak valid.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
    mysqli_stmt_close($check);

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

        // ✅ Cek kepemilikan: SuperAdmin boleh semua, Admin hanya miliknya
        $where = $user_level === 1 ? "id = ?" : "id = ? AND id_ormawa = ?";
        $stmt = mysqli_prepare($koneksi, "SELECT gambar, file_panduan FROM beasiswa WHERE $where");
        if ($user_level === 1) {
            mysqli_stmt_bind_param($stmt, "i", $id);
        } else {
            mysqli_stmt_bind_param($stmt, "ii", $id, $ormawa_id_restricted);
        }
        mysqli_stmt_execute($stmt);
        $old = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$old) {
            $_SESSION['error'] = "Beasiswa tidak ditemukan atau bukan milik Anda.";
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
                id_ormawa = ?, nama_beasiswa = ?, penyelenggara = ?, periode = ?, 
                deadline = ?, deskripsi = ?, gambar = ?, file_panduan = ?
            WHERE id = ?
        ");
        $bind = mysqli_stmt_bind_param($stmt, "isssssssi", 
            $submitted_ormawa_id, $nama, $penyelenggara, $periode, $deadline, $deskripsi, $gambar, $file_panduan, $id
        );

    } else { // Tambah
        $stmt = mysqli_prepare($koneksi, "
            INSERT INTO beasiswa (
                id_ormawa, nama_beasiswa, penyelenggara, periode, 
                deadline, deskripsi, gambar, file_panduan
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $bind = mysqli_stmt_bind_param($stmt, "isssssss", 
            $submitted_ormawa_id, $nama, $penyelenggara, $periode, $deadline, $deskripsi, $gambar, $file_panduan
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
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
        exit();
    }

    // ✅ Cek kepemilikan
    $where = $user_level === 1 ? "id = ?" : "id = ? AND id_ormawa = ?";
    $stmt = mysqli_prepare($koneksi, "SELECT gambar, file_panduan FROM beasiswa WHERE $where");
    if ($user_level === 1) {
        mysqli_stmt_bind_param($stmt, "i", $id);
    } else {
        mysqli_stmt_bind_param($stmt, "ii", $id, $ormawa_id_restricted);
    }
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($row) {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM beasiswa WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            if ($row['gambar'] && file_exists($uploadDir . $row['gambar'])) unlink($uploadDir . $row['gambar']);
            if ($row['file_panduan'] && file_exists($uploadDir . $row['file_panduan'])) unlink($uploadDir . $row['file_panduan']);
            $_SESSION['success'] = "Beasiswa berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus dari database.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Beasiswa tidak ditemukan atau bukan milik Anda.";
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit();
}

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
exit();
?>