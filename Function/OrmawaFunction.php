<?php
include '../Config/ConnectDB.php';

// --- FUNGSI: Menambah Ormawa ---
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama = trim($_POST['nama_ormawa'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (empty($nama)) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=nama_kosong");
        exit;
    }

    $logo_nama = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/logos/"; 
        $gambar_nama_asli = basename($_FILES["logo"]["name"]);
        $target_file = $target_dir . $gambar_nama_asli;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Cek apakah file adalah gambar
        $check = getimagesize($_FILES["logo"]["tmp_name"]);
        if($check === false) {
            $uploadOk = 0;
            $error_message = "File bukan gambar.";
        }

        // Cek ukuran file (opsional, misalnya max 5MB)
        if ($_FILES["logo"]["size"] > 5000000) { // 5MB
            $uploadOk = 0;
            $error_message = "File logo terlalu besar.";
        }

        // Batasi format file
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if(!in_array($imageFileType, $allowed_extensions)) {
            $uploadOk = 0;
            $error_message = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
        }

        // Jika semua cek lolos
        if ($uploadOk == 1) {
            // Generate nama unik untuk mencegah konflik dan karakter khusus
            $logo_nama = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $gambar_nama_asli);
            $target_file = $target_dir . $logo_nama;

            if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
                $error_message = "Terjadi kesalahan saat mengupload logo.";
                $uploadOk = 0;
                $logo_nama = '';
            }
        } else {
            if (!isset($error_message)) $error_message = "Upload logo gagal.";
            $logo_nama = '';
        }
    }

    $stmt = $koneksi->prepare("INSERT INTO ormawa (nama_ormawa, deskripsi, logo) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sss", $nama, $deskripsi, $logo_nama);
        $stmt->execute();
        $stmt->close();

        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&success=form");
        exit;
    } else {
        error_log("Error inserting ormawa: " . $koneksi->error);
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=query_gagal");
        exit;
    }
}

// --- FUNGSI: Memperbarui Ormawa ---
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama_ormawa'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!$id || empty($nama)) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=invalid_data");
        exit;
    }

    // Ambil logo lama dari database
    $stmt = $koneksi->prepare("SELECT logo FROM ormawa WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $logo_lama = $row['logo'] ?? '';
    $stmt->close();

    $logo_nama = $logo_lama;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK && $_FILES['logo']['size'] > 0) {
        $target_dir = "../../uploads/logos/"; // Path relatif dari App/Function/
        $gambar_nama_asli_baru = basename($_FILES["logo"]["name"]);
        $target_file = $target_dir . $gambar_nama_asli_baru;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Cek apakah file adalah gambar
        $check = getimagesize($_FILES["logo"]["tmp_name"]);
        if($check === false) {
            $uploadOk = 0;
            $error_message = "File bukan gambar.";
        }

        // Cek ukuran file (opsional, misalnya max 5MB)
        if ($_FILES["logo"]["size"] > 5000000) { // 5MB
            $uploadOk = 0;
            $error_message = "File logo terlalu besar.";
        }

        // Batasi format file
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if(!in_array($imageFileType, $allowed_extensions)) {
            $uploadOk = 0;
            $error_message = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
        }

        // Jika semua cek lolos
        if ($uploadOk == 1) {
            // Hapus logo lama jika ada dan file-nya ada di server
            if ($logo_lama && file_exists($target_dir . $logo_lama)) {
                unlink($target_dir . $logo_lama);
            }

            // Generate nama unik untuk mencegah konflik dan karakter khusus
            $logo_nama = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $gambar_nama_asli_baru);
            $target_file_baru = $target_dir . $logo_nama;

            if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file_baru)) {
                $error_message = "Terjadi kesalahan saat mengupload logo baru.";
                $uploadOk = 0;
                $logo_nama = $logo_lama; // Kembali ke logo lama jika upload baru gagal
            }
        } else {
            if (!isset($error_message)) $error_message = "Upload logo baru gagal.";
            $logo_nama = $logo_lama; // Kembali ke logo lama jika validasi gagal
        }
    }

    $stmt = $koneksi->prepare("UPDATE ormawa SET nama_ormawa = ?, deskripsi = ?, logo = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("sssi", $nama, $deskripsi, $logo_nama, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&success=form");
        exit;
    } else {
        error_log("Error updating ormawa: " . $koneksi->error);
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=query_gagal");
        exit;
    }
}

// --- FUNGSI: Menghapus Ormawa ---
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        // Ambil nama logo untuk dihapus
        $stmt = $koneksi->prepare("SELECT logo FROM ormawa WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $logo_nama = $row['logo'] ?? '';
        $stmt->close();

        // Hapus dari tabel `ormawa`
        $stmt = $koneksi->prepare("DELETE FROM ormawa WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Hapus file logo dari server jika ada
        if ($logo_nama && file_exists("../../uploads/logos/" . $logo_nama)) { // Path relatif dari App/Function/
            unlink("../../uploads/logos/" . $logo_nama);
        }

        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&deleted=ormawa"); // Ganti 'form' menjadi 'ormawa' agar lebih spesifik
        exit;
    } else {
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=invalid_id");
        exit;
    }
}

// Tangani error jika tidak ada aksi yang dikenali
header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=unknown_action");
exit;


?>