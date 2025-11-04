<?php
include '../Config/ConnectDB.php';

// Pastikan sesi dimulai untuk mengakses $_SESSION
session_start(); 

// --- FUNGSI BARU: Membuat formulir baru ---
if (isset($_POST['action']) && $_POST['action'] === 'create_form_info') {
    // Ambil user_id dari sesi
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&error=user_not_logged_in");
        exit;
    }
    $user_id = (int)$_SESSION['user_id'];

    $judul = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!$judul) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&error=judul_kosong");
        exit;
    }

    // Handle upload gambar
    $gambar_nama = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../uploads/form/"; 
        $gambar_nama_asli = basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar_nama_asli;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Cek apakah file adalah gambar
        $check = getimagesize($_FILES["gambar"]["tmp_name"]);
        if($check === false) {
            $uploadOk = 0;
            $error_message = "File bukan gambar.";
        }

        // Cek ukuran file (opsional, misalnya max 5MB)
        if ($_FILES["gambar"]["size"] > 5000000) {
            $uploadOk = 0;
            $error_message = "File gambar terlalu besar.";
        }

        // Batasi format file
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $uploadOk = 0;
            $error_message = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
        }

        // Jika semua cek lolos
        if ($uploadOk == 1) {
            // Generate nama unik untuk mencegah konflik dan karakter khusus
            $gambar_nama = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $gambar_nama_asli);
            $target_file = $target_dir . $gambar_nama;

            if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $error_message = "Terjadi kesalahan saat mengupload gambar.";
                $uploadOk = 0;
                $gambar_nama = '';
            }
        } else {
            if (!isset($error_message)) $error_message = "Upload gambar gagal.";
            $gambar_nama = '';
        }
    }

    // Simpan ke tabel `form_info` - Tambahkan user_id
    $stmt = $koneksi->prepare("INSERT INTO form_info (judul, deskripsi, gambar, user_id) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssi", $judul, $deskripsi, $gambar_nama, $user_id);
        $stmt->execute();
        $new_form_info_id = $stmt->insert_id;
        $stmt->close();

        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&form_id=$new_form_info_id&success=form");
        exit;
    } else {
        error_log("Error inserting form_info: " . $koneksi->error);
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&error=query_gagal");
        exit;
    }
}

// --- FUNGSI BARU: Memperbarui formulir ---
if (isset($_POST['action']) && $_POST['action'] === 'update_form_info') {
    $form_info_id = (int)($_POST['form_info_id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    // Ambil user_id dari sesi
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&form_id=$form_info_id&error=user_not_logged_in");
        exit;
    }
    $current_user_id = (int)$_SESSION['user_id'];

    if (!$form_info_id || !$judul) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&form_id=$form_info_id&error=invalid_data");
        exit;
    }

    // Validasi apakah user yang sedang login adalah pembuat formulir
    $stmt_check = $koneksi->prepare("SELECT user_id FROM form_info WHERE id = ?");
    $stmt_check->bind_param("i", $form_info_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();
    $stmt_check->close();

    if (!$row_check || $row_check['user_id'] != $current_user_id) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&form_id=$form_info_id&error=unauthorized_access");
        exit;
    }

    // Ambil gambar lama dari database
    $stmt = $koneksi->prepare("SELECT gambar FROM form_info WHERE id = ?");
    $stmt->bind_param("i", $form_info_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $gambar_lama = $row['gambar'] ?? '';
    $stmt->close();

    // Handle upload gambar baru (jika ada)
    $gambar_nama = $gambar_lama;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0 && $_FILES['gambar']['size'] > 0) {
        $target_dir = "../uploads/form/"; 
        $gambar_nama_asli_baru = basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar_nama_asli_baru;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Cek apakah file adalah gambar
        $check = getimagesize($_FILES["gambar"]["tmp_name"]);
        if($check === false) {
            $uploadOk = 0;
            $error_message = "File bukan gambar.";
        }

        // Cek ukuran file (opsional, misalnya max 5MB)
        if ($_FILES["gambar"]["size"] > 5000000) {
            $uploadOk = 0;
            $error_message = "File gambar terlalu besar.";
        }

        // Batasi format file
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $uploadOk = 0;
            $error_message = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
        }

        // Jika semua cek lolos
        if ($uploadOk == 1) {
            // Hapus gambar lama jika ada dan file-nya ada di server
            if ($gambar_lama && file_exists($target_dir . $gambar_lama)) {
                unlink($target_dir . $gambar_lama);
            }

            // Generate nama unik untuk mencegah konflik dan karakter khusus
            $gambar_nama = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $gambar_nama_asli_baru);
            $target_file_baru = $target_dir . $gambar_nama;

            if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file_baru)) {
                $error_message = "Terjadi kesalahan saat mengupload gambar baru.";
                $uploadOk = 0;
                $gambar_nama = $gambar_lama;
            }
        } else {
            if (!isset($error_message)) $error_message = "Upload gambar baru gagal.";
            $gambar_nama = $gambar_lama;
        }
    }

    // Update ke tabel `form_info` - JANGAN menyertakan user_id
    $stmt = $koneksi->prepare("UPDATE form_info SET judul = ?, deskripsi = ?, gambar = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("sssi", $judul, $deskripsi, $gambar_nama, $form_info_id);
        $stmt->execute();
        $stmt->close();

        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&form_id=$form_info_id&success=form");
        exit;
    } else {
        error_log("Error updating form_info: " . $koneksi->error);
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&form_id=$form_info_id&error=query_gagal");
        exit;
    }
}

// --- FUNGSI BARU: Menghapus formulir (DIPERBAIKI untuk mendukung GET) ---
// Tambahkan validasi akses di sini juga jika diinginkan, mirip dengan update_form_info
if (isset($_GET['action']) && $_GET['action'] === 'delete_form') {
    $form_info_id = (int)($_GET['id'] ?? 0);
    if ($form_info_id > 0) {
        // Ambil user_id dari sesi
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&error=user_not_logged_in");
            exit;
        }
        $current_user_id = (int)$_SESSION['user_id'];

        // Validasi apakah user yang sedang login adalah pembuat formulir
        $stmt_check = $koneksi->prepare("SELECT user_id FROM form_info WHERE id = ?");
        $stmt_check->bind_param("i", $form_info_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row_check = $result_check->fetch_assoc();
        $stmt_check->close();

        if (!$row_check || $row_check['user_id'] != $current_user_id) {
            header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&error=unauthorized_access");
            exit;
        }

        // Ambil nama gambar untuk dihapus
        $stmt = $koneksi->prepare("SELECT gambar FROM form_info WHERE id = ?");
        $stmt->bind_param("i", $form_info_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $gambar_nama = $row['gambar'] ?? '';
        $stmt->close();

        // Hapus dari tabel `form_info` (CASCADE akan menghapus field di tabel `form`)
        $stmt = $koneksi->prepare("DELETE FROM form_info WHERE id = ?");
        $stmt->bind_param("i", $form_info_id);
        $stmt->execute();
        $stmt->close();

        // Hapus file gambar dari server jika ada
        if ($gambar_nama && file_exists("../uploads/form/" . $gambar_nama)) {
            unlink("../uploads/form/" . $gambar_nama);
        }

        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&deleted=form");
        exit;
    } else {
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&error=invalid_id");
        exit;
    }
}

// --- FUNGSI LAMA: Menambah field (DIPERBARUI) ---
if (isset($_POST['action']) && $_POST['action'] === 'add_field') {
    $form_info_id = (int)($_POST['form_info_id'] ?? 0);
    $label = trim($_POST['label'] ?? '');
    $type = $_POST['type'] ?? '';

    // Validasi akses ke form_info_id (opsional: cek apakah user_id cocok, tapi biasanya tidak diperlukan jika form_id sudah valid)
    // Ambil user_id dari sesi
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&form_id=$form_info_id&error=user_not_logged_in");
        exit;
    }
    // Validasi bisa ditambahkan di sini jika perlu

    if (!$form_info_id || !$label || !$type) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&form_id=$form_info_id&error=invalid_data");
        exit;
    }

    // Validasi tipe
    $allowedTypes = ['text', 'email', 'number', 'textarea', 'file', 'radio', 'select'];
    if (!in_array($type, $allowedTypes)) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&form_id=$form_info_id&error=invalid_type");
        exit;
    }

    // Normalisasi nama field
    $name = preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace(' ', '_', $label)));

    // Handle opsi
    $opsi = '';
    if ($type === 'radio' || $type === 'select') {
        $options = $_POST['options'] ?? [];
        $options = array_filter(array_map('trim', $options));
        $opsi = json_encode(array_values($options));
    }

    // Simpan ke tabel `form` dengan form_info_id
    $stmt = $koneksi->prepare("INSERT INTO form (form_info_id, nama, tipe, label, opsi, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("issss", $form_info_id, $name, $type, $label, $opsi);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&form_id=$form_info_id&success=field");
    exit;
}

// --- FUNGSI LAMA: Hapus field (DIPERBARUI) ---
if (isset($_POST['action']) && $_POST['action'] === 'delete_field') {
    $id = (int)($_POST['delete_id'] ?? 0);
    $form_info_id = (int)($_POST['form_info_id'] ?? 0);
    if ($id > 0) {
        // Validasi akses ke form_info_id (opsional: cek apakah user_id cocok)
        // Ambil user_id dari sesi
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&form_id=$form_info_id&error=user_not_logged_in");
            exit;
        }
        // Validasi bisa ditambahkan di sini jika perlu

        $stmt = $koneksi->prepare("DELETE FROM form WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&form_id=$form_info_id&deleted=field");
    exit;
}

// Tangani error jika tidak ada aksi yang dikenali
header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&error=unknown_action");
exit;
?>