<?php
session_start();
include('../Config/ConnectDB.php');
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit();
}

$action = $_GET['action'] ?? '';

// === TAMBAH ===
if ($action === 'add') {
    $nama = trim($_POST['nama_kompetisi'] ?? '');
    $penyelenggara = trim($_POST['penyelenggara'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    if (!$nama || !$penyelenggara || !$deskripsi) {
        echo json_encode(['success' => false, 'message' => 'Nama, penyelenggara, dan deskripsi wajib diisi']);
        exit();
    }

    // Upload gambar (opsional)
    $gambar = '';
    if (!empty($_FILES['gambar']['name'])) {
        $file = $_FILES['gambar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png']) && $file['size'] <= 2 * 1024 * 1024) {
            $gambar = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file['name']);
            if (!is_dir("../../../uploads/kompetisi/")) mkdir("../../../uploads/kompetisi/", 0777, true);
            move_uploaded_file($file['tmp_name'], "../../../uploads/kompetisi/" . $gambar);
        }
    }

    // Upload panduan (opsional)
    $file_panduan = '';
    if (!empty($_FILES['file_panduan']['name'])) {
        $file = $_FILES['file_panduan'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'doc', 'docx']) && $file['size'] <= 10 * 1024 * 1024) {
            $file_panduan = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file['name']);
            if (!is_dir("../../../uploads/kompetisi/")) mkdir("../../../uploads/kompetisi/", 0777, true);
            move_uploaded_file($file['tmp_name'], "../../../uploads/kompetisi/" . $file_panduan);
        }
    }

    $stmt = mysqli_prepare($koneksi, "
        INSERT INTO kompetisi (
            nama_kompetisi, penyelenggara, periode, deadline, deskripsi, gambar, file_panduan
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $periode = trim($_POST['periode'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    mysqli_stmt_bind_param($stmt, "sssssss", 
        $nama, $penyelenggara, $periode, $deadline, $deskripsi, $gambar, $file_panduan
    );

    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Berhasil!' : 'Gagal menyimpan'
    ]);
    exit();
}

// === EDIT ===
if ($action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = mysqli_prepare($koneksi, "SELECT gambar, file_panduan FROM kompetisi WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $old = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    $nama = trim($_POST['nama_kompetisi'] ?? '');
    $penyelenggara = trim($_POST['penyelenggara'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    if (!$nama || !$penyelenggara || !$deskripsi || !$id) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        exit();
    }

    // Proses gambar
    $gambar = $old['gambar'];
    if (!empty($_FILES['gambar']['name'])) {
        if ($gambar && file_exists("../../../uploads/kompetisi/" . $gambar)) unlink("../../../uploads/kompetisi/" . $gambar);
        $file = $_FILES['gambar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png']) && $file['size'] <= 2 * 1024 * 1024) {
            $gambar = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file['name']);
            move_uploaded_file($file['tmp_name'], "../../../uploads/kompetisi/" . $gambar);
        }
    }

    // Proses panduan
    $file_panduan = $old['file_panduan'];
    if (!empty($_FILES['file_panduan']['name'])) {
        if ($file_panduan && file_exists("../../../uploads/kompetisi/" . $file_panduan)) unlink("../../../uploads/kompetisi/" . $file_panduan);
        $file = $_FILES['file_panduan'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'doc', 'docx']) && $file['size'] <= 10 * 1024 * 1024) {
            $file_panduan = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file['name']);
            move_uploaded_file($file['tmp_name'], "../../../uploads/kompetisi/" . $file_panduan);
        }
    }

    $stmt = mysqli_prepare($koneksi, "
        UPDATE kompetisi SET 
            nama_kompetisi = ?, 
            penyelenggara = ?, 
            periode = ?, 
            deadline = ?, 
            deskripsi = ?, 
            gambar = ?, 
            file_panduan = ?
        WHERE id = ?
    ");
    $periode = trim($_POST['periode'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    mysqli_stmt_bind_param($stmt, "sssssssi", 
        $nama, $penyelenggara, $periode, $deadline, $deskripsi, $gambar, $file_panduan, $id
    );

    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Berhasil!' : 'Gagal memperbarui'
    ]);
    exit();
}

// === HAPUS ===
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($koneksi, "SELECT gambar, file_panduan FROM kompetisi WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($koneksi, "DELETE FROM kompetisi WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($success && $row) {
        if ($row['gambar'] && file_exists("../../../uploads/kompetisi/" . $row['gambar'])) 
            unlink("../../../uploads/kompetisi/" . $row['gambar']);
        if ($row['file_panduan'] && file_exists("../../../uploads/kompetisi/" . $row['file_panduan'])) 
            unlink("../../../uploads/kompetisi/" . $row['file_panduan']);
    }

    echo json_encode(['success' => $success]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenali']);
?>