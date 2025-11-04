<?php
include '../Config/ConnectDB.php';

// Direktori upload dokumen (relatif terhadap lokasi file ini)
$target_dir = "../uploads/dokumen/";

// Buat folder jika belum ada
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// --- FUNGSI: Menambah Dokumen ---
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama_dokumen = trim($_POST['nama_dokumen'] ?? '');
    $jenis_dokumen = trim($_POST['jenis_dokumen'] ?? '');
    $id_ormawa = (int)($_POST['id_ormawa'] ?? 0);
    $id_user = (int)($_POST['id_user'] ?? 0);

    // Validasi dasar
    if (empty($nama_dokumen) || empty($jenis_dokumen) || $id_ormawa <= 0 || $id_user <= 0) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=document&error=data_kosong");
        exit;
    }

    // Handle upload file
    $file_path = '';
    $ukuran_file = 0;
    $uploadOk = 1;
    $error_message = '';

    if (isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] == UPLOAD_ERR_OK) {
        $file_name = $_FILES['file_dokumen']['name'];
        $file_tmp = $_FILES['file_dokumen']['tmp_name'];
        $ukuran_file = $_FILES['file_dokumen']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];

        // Cek ekstensi
        if (!in_array($file_ext, $allowed_ext)) {
            $uploadOk = 0;
            $error_message = "Format file tidak diizinkan.";
        }

        // Cek ukuran (maks 10MB)
        if ($ukuran_file > 10 * 1024 * 1024) {
            $uploadOk = 0;
            $error_message = "Ukuran file melebihi 10MB.";
        }

        if ($uploadOk == 1) {
            // Generate nama unik
            $file_path = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file_name);
            $target_file = $target_dir . $file_path;

            if (!move_uploaded_file($file_tmp, $target_file)) {
                $uploadOk = 0;
                $error_message = "Gagal mengupload file.";
                $file_path = '';
                $ukuran_file = 0;
            }
        }
    } else {
        $uploadOk = 0;
        $error_message = "File dokumen wajib diunggah.";
    }

    if ($uploadOk == 0) {
        error_log("Upload gagal: " . ($error_message ?? 'Unknown error'));
        header("Location: ../App/View/SuperAdmin/Index.php?page=document&error=upload_gagal");
        exit;
    }

    // Simpan ke database
    $tanggal_upload = date('Y-m-d');
    $stmt = $koneksi->prepare("INSERT INTO dokumen (nama_dokumen, jenis_dokumen, tanggal_upload, id_ormawa, id_user, file_path, ukuran_file) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssiiis", $nama_dokumen, $jenis_dokumen, $tanggal_upload, $id_ormawa, $id_user, $file_path, $ukuran_file);
        if ($stmt->execute()) {
            header("Location: ../App/View/SuperAdmin/Index.php?page=document&success=dokumen_baru");
        } else {
            error_log("Gagal insert dokumen: " . $stmt->error);
            header("Location: ../App/View/SuperAdmin/Index.php?page=document&error=query_gagal");
        }
        $stmt->close();
    } else {
        error_log("Prepare gagal: " . $koneksi->error);
        header("Location: ../App/View/SuperAdmin/Index.php?page=document&error=query_gagal");
    }
    exit;
}

// --- FUNGSI: Mengedit Dokumen ---
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $nama_dokumen = trim($_POST['nama_dokumen'] ?? '');
    $jenis_dokumen = trim($_POST['jenis_dokumen'] ?? '');
    $id_ormawa = (int)($_POST['id_ormawa'] ?? 0);
    $id_user = (int)($_POST['id_user'] ?? 0);

    if ($id <= 0 || empty($nama_dokumen) || empty($jenis_dokumen)) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=document&error=data_invalid");
        exit;
    }

    // Ambil data lama dari database
    $stmt = $koneksi->prepare("SELECT file_path FROM dokumen WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $file_lama = $row['file_path'] ?? '';
    $stmt->close();

    $file_path = $file_lama;
    $ukuran_file = 0;
    $uploadOk = 1;
    $error_message = '';

    // Cek apakah ada file baru diupload
    if (isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] == UPLOAD_ERR_OK && $_FILES['file_dokumen']['size'] > 0) {
        $file_name = $_FILES['file_dokumen']['name'];
        $file_tmp = $_FILES['file_dokumen']['tmp_name'];
        $ukuran_file = $_FILES['file_dokumen']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];

        if (!in_array($file_ext, $allowed_ext)) {
            $uploadOk = 0;
            $error_message = "Format file tidak diizinkan.";
        }

        if ($ukuran_file > 10 * 1024 * 1024) {
            $uploadOk = 0;
            $error_message = "Ukuran file melebihi 10MB.";
        }

        if ($uploadOk == 1) {
            // Hapus file lama jika ada
            if (!empty($file_lama) && file_exists($target_dir . $file_lama)) {
                unlink($target_dir . $file_lama);
            }

            // Simpan file baru
            $file_path = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file_name);
            $target_file = $target_dir . $file_path;

            if (!move_uploaded_file($file_tmp, $target_file)) {
                $uploadOk = 0;
                $error_message = "Gagal mengupload file baru.";
                $file_path = $file_lama; // kembalikan ke file lama
                $ukuran_file = 0;
            }
        } else {
            $file_path = $file_lama;
        }
    } else {
        // Tidak ada file baru, gunakan ukuran lama
        $stmt_size = $koneksi->prepare("SELECT ukuran_file FROM dokumen WHERE id = ?");
        $stmt_size->bind_param("i", $id);
        $stmt_size->execute();
        $size_result = $stmt_size->get_result();
        $size_row = $size_result->fetch_assoc();
        $ukuran_file = $size_row['ukuran_file'] ?? 0;
        $stmt_size->close();
    }

    // Update database
    $stmt = $koneksi->prepare("UPDATE dokumen SET nama_dokumen = ?, jenis_dokumen = ?, id_ormawa = ?, id_user = ?, file_path = ?, ukuran_file = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("ssiiisi", $nama_dokumen, $jenis_dokumen, $id_ormawa, $id_user, $file_path, $ukuran_file, $id);
        if ($stmt->execute()) {
            header("Location: ../App/View/SuperAdmin/Index.php?page=document&success=dokumen_diedit");
        } else {
            error_log("Gagal update dokumen: " . $stmt->error);
            header("Location: ../App/View/SuperAdmin/Index.php?page=document&error=query_gagal");
        }
        $stmt->close();
    } else {
        error_log("Prepare gagal: " . $koneksi->error);
        header("Location: ../App/View/SuperAdmin/Index.php?page=document&error=query_gagal");
    }
    exit;
}

// --- FUNGSI: Menghapus Dokumen ---
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        // Ambil path file
        $stmt = $koneksi->prepare("SELECT file_path FROM dokumen WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $file_path = $row['file_path'] ?? '';
        $stmt->close();

        // Hapus dari database
        $stmt = $koneksi->prepare("DELETE FROM dokumen WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Hapus file fisik
        if (!empty($file_path) && file_exists($target_dir . $file_path)) {
            unlink($target_dir . $file_path);
        }

        header("Location: ../App/View/SuperAdmin/Index.php?page=document&deleted=dokumen");
        exit;
    } else {
        header("Location: ../App/View/SuperAdmin/Index.php?page=document&error=id_invalid");
        exit;
    }
}

// Jika tidak ada aksi yang cocok
header("Location: ../App/View/SuperAdmin/Index.php?page=document&error=aksi_tidak_dikenal");
exit;
?>