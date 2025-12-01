<?php
/**
 * ========================================
 * ORMAWA FUNCTION - BACKEND CRUD
 * ========================================
 * File ini menangani semua operasi CRUD untuk tabel ormawa
 * - CREATE (Tambah Ormawa)
 * - UPDATE (Edit Ormawa)
 * - DELETE (Hapus Ormawa)
 * * Author: System
 * Last Update: 2025
 * ========================================
 */

include '../Config/ConnectDB.php';

// ========================================
// FUNGSI: TAMBAH ORMAWA (CREATE)
// ========================================
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    
    // Ambil data dari form dan sanitasi
    $nama = trim($_POST['nama_ormawa'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $visi = trim($_POST['visi'] ?? '');
    $misi = trim($_POST['misi'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');

    // Validasi data wajib
    if (empty($nama)) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=nama_kosong");
        exit;
    }

    if (empty($kategori)) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=kategori_kosong");
        exit;
    }

    // ========================================
    // HANDLE UPLOAD LOGO
    // ========================================
    $logo_nama = '';
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/logos/";
        
        // Cek dan buat folder jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $gambar_nama_asli = basename($_FILES["logo"]["name"]);
        $imageFileType = strtolower(pathinfo($gambar_nama_asli, PATHINFO_EXTENSION));
        $uploadOk = 1;
        $error_message = '';

        // Validasi 1: Cek apakah file adalah gambar
        $check = getimagesize($_FILES["logo"]["tmp_name"]);
        if($check === false) {
            $uploadOk = 0;
            $error_message = "File bukan gambar.";
        }

        // Validasi 2: Cek ukuran file (maksimal 5MB)
        if ($_FILES["logo"]["size"] > 5000000) {
            $uploadOk = 0;
            $error_message = "File logo terlalu besar (maksimal 5MB).";
        }

        // Validasi 3: Batasi format file yang diperbolehkan
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if(!in_array($imageFileType, $allowed_extensions)) {
            $uploadOk = 0;
            $error_message = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
        }

        // Proses upload jika validasi lolos
        if ($uploadOk == 1) {
            // Generate nama unik untuk mencegah konflik nama file
            $logo_nama = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $gambar_nama_asli);
            $target_file = $target_dir . $logo_nama;

            // Upload file
            if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
                error_log("Upload Error: Gagal memindahkan file ke " . $target_file);
                $error_message = "Terjadi kesalahan saat mengupload logo.";
                $logo_nama = '';
            }
        } else {
            // Log error jika upload gagal
            error_log("Upload Error: " . $error_message);
        }
    }

    // ========================================
    // INSERT DATA KE DATABASE
    // ========================================
    $stmt = $koneksi->prepare("INSERT INTO ormawa (nama_ormawa, deskripsi, kategori, visi, misi, email, contact_person, logo, created_at, update_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    
    if ($stmt) {
        $stmt->bind_param("ssssssss", $nama, $deskripsi, $kategori, $visi, $misi, $email, $contact_person, $logo_nama);
        
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&success=form");
            exit;
        } else {
            error_log("Database Error (Insert): " . $stmt->error);
            $stmt->close();
            
            // Hapus file logo yang sudah diupload jika insert gagal
            if ($logo_nama && file_exists($target_dir . $logo_nama)) {
                unlink($target_dir . $logo_nama);
            }
            
            header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=query_gagal");
            exit;
        }
    } else {
        error_log("Database Error (Prepare Insert): " . $koneksi->error);
        
        // Hapus file logo yang sudah diupload jika prepare gagal
        if ($logo_nama && file_exists($target_dir . $logo_nama)) {
            unlink($target_dir . $logo_nama);
        }
        
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=query_gagal");
        exit;
    }
}

// ========================================
// FUNGSI: EDIT ORMAWA (UPDATE)
// ========================================
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    
    // Ambil data dari form dan sanitasi
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama_ormawa'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $visi = trim($_POST['visi'] ?? '');
    $misi = trim($_POST['misi'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');

    // Validasi data
    if (!$id || empty($nama)) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=invalid_data");
        exit;
    }

    if (empty($kategori)) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=kategori_kosong");
        exit;
    }

    // ========================================
    // AMBIL DATA LAMA DARI DATABASE
    // ========================================
    $stmt = $koneksi->prepare("SELECT logo FROM ormawa WHERE id = ?");
    
    if (!$stmt) {
        error_log("Database Error (Prepare Select): " . $koneksi->error);
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=query_gagal");
        exit;
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt->close();
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=data_not_found");
        exit;
    }
    
    $row = $result->fetch_assoc();
    $logo_lama = $row['logo'] ?? '';
    $stmt->close();

    // ========================================
    // HANDLE UPLOAD LOGO BARU (OPSIONAL)
    // ========================================
    $logo_nama = $logo_lama; // Default: gunakan logo lama
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK && $_FILES['logo']['size'] > 0) {
        $target_dir = "../uploads/logos/";
        
        // Cek dan buat folder jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $gambar_nama_asli_baru = basename($_FILES["logo"]["name"]);
        $imageFileType = strtolower(pathinfo($gambar_nama_asli_baru, PATHINFO_EXTENSION));
        $uploadOk = 1;
        $error_message = '';

        // Validasi 1: Cek apakah file adalah gambar
        $check = getimagesize($_FILES["logo"]["tmp_name"]);
        if($check === false) {
            $uploadOk = 0;
            $error_message = "File bukan gambar.";
        }

        // Validasi 2: Cek ukuran file (maksimal 5MB)
        if ($_FILES["logo"]["size"] > 5000000) {
            $uploadOk = 0;
            $error_message = "File logo terlalu besar (maksimal 5MB).";
        }

        // Validasi 3: Batasi format file yang diperbolehkan
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if(!in_array($imageFileType, $allowed_extensions)) {
            $uploadOk = 0;
            $error_message = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
        }

        // Proses upload jika validasi lolos
        if ($uploadOk == 1) {
            // Hapus logo lama jika ada dan file-nya ada di server
            if ($logo_lama && file_exists($target_dir . $logo_lama)) {
                if (!unlink($target_dir . $logo_lama)) {
                    error_log("Warning: Gagal menghapus logo lama: " . $target_dir . $logo_lama);
                }
            }

            // Generate nama unik untuk file baru
            $logo_nama = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $gambar_nama_asli_baru);
            $target_file_baru = $target_dir . $logo_nama;

            // Upload file baru
            if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file_baru)) {
                error_log("Upload Error: Gagal memindahkan file baru ke " . $target_file_baru);
                $error_message = "Terjadi kesalahan saat mengupload logo baru.";
                $logo_nama = $logo_lama; // Kembali ke logo lama jika upload gagal
            }
        } else {
            // Log error jika validasi gagal
            error_log("Upload Error: " . $error_message);
            $logo_nama = $logo_lama; // Kembali ke logo lama jika validasi gagal
        }
    }

    // ========================================
    // UPDATE DATA KE DATABASE
    // ========================================
    $stmt = $koneksi->prepare("UPDATE ormawa SET nama_ormawa = ?, deskripsi = ?, kategori = ?, visi = ?, misi = ?, email = ?, contact_person = ?, logo = ?, update_at = NOW() WHERE id = ?");
    
    if ($stmt) {
        $stmt->bind_param("ssssssssi", $nama, $deskripsi, $kategori, $visi, $misi, $email, $contact_person, $logo_nama, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            // PERUBAHAN: Menggunakan success=updated untuk pesan spesifik
            header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&success=updated"); 
            exit;
        } else {
            error_log("Database Error (Update): " . $stmt->error);
            $stmt->close();
            header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=query_gagal");
            exit;
        }
    } else {
        error_log("Database Error (Prepare Update): " . $koneksi->error);
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=query_gagal");
        exit;
    }
}

// ========================================
// FUNGSI: HAPUS ORMAWA (DELETE)
// ========================================
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    
    $id = (int)($_GET['id'] ?? 0);
    
    // Validasi ID
    if ($id <= 0) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=invalid_id");
        exit;
    }

    // ========================================
    // AMBIL DATA UNTUK MENDAPATKAN NAMA LOGO
    // ========================================
    $stmt = $koneksi->prepare("SELECT logo FROM ormawa WHERE id = ?");
    
    if (!$stmt) {
        error_log("Database Error (Prepare Select for Delete): " . $koneksi->error);
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=query_gagal");
        exit;
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt->close();
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=data_not_found");
        exit;
    }
    
    $row = $result->fetch_assoc();
    $logo_nama = $row['logo'] ?? '';
    $stmt->close();

    // ========================================
    // HAPUS DATA DARI DATABASE
    // ========================================
    $stmt = $koneksi->prepare("DELETE FROM ormawa WHERE id = ?");
    
    if (!$stmt) {
        error_log("Database Error (Prepare Delete): " . $koneksi->error);
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=query_gagal");
        exit;
    }
    
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        // ========================================
        // HAPUS FILE LOGO DARI SERVER
        // ========================================
        if ($logo_nama && file_exists("../uploads/logos/" . $logo_nama)) {
            if (!unlink("../uploads/logos/" . $logo_nama)) {
                error_log("Warning: Gagal menghapus file logo: ../uploads/logos/" . $logo_nama);
            }
        }

        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&deleted=ormawa");
        exit;
    } else {
        error_log("Database Error (Execute Delete): " . $stmt->error);
        $stmt->close();
        header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=query_gagal");
        exit;
    }
}

// ========================================
// HANDLE: ACTION TIDAK DIKENALI
// ========================================
if (isset($_POST['action']) || isset($_GET['action'])) {
    header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa&error=unknown_action");
    exit;
}

// Redirect default jika file diakses langsung tanpa parameter
header("Location: ../App/View/SuperAdmin/Index.php?page=ormawa");
exit;
?>