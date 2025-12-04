<?php
// ========================================
// DOCUMENT FUNCTION - COMPLETE & FIXED
// ========================================

function getDocumentDataByOrmawa($koneksi, $id_ormawa) {
    $stmt = $koneksi->prepare("SELECT d.id, d.nama_dokumen, d.jenis_dokumen, d.file_path, d.tanggal_upload, d.ukuran_file, o.nama_ormawa
                               FROM dokumen d
                               JOIN ormawa o ON d.id_ormawa = o.id
                               WHERE d.id_ormawa = ?
                               ORDER BY d.tanggal_upload DESC");
    $stmt->bind_param("i", $id_ormawa);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function formatFileSize($kb) {
    $size = floatval($kb);

    if ($size >= 1048576) { 
        // 1 GB = 1.048.576 KB
        return round($size / 1048576, 2) . ' GB';
    } elseif ($size >= 1024) { 
        // 1 MB = 1024 KB
        return round($size / 1024, 2) . ' MB';
    } else {
        return round($size, 2) . ' KB';
    }
}

// Fungsi untuk mendapatkan data dokumen
function getDocumentData($koneksi) {
    if (!isset($_SESSION['user_id'], $_SESSION['user_level'])) {
        return [];
    }

    $user_level = $_SESSION['user_level'];
    $base_query = "SELECT d.*, o.nama_ormawa 
                   FROM dokumen d 
                   JOIN ormawa o ON d.id_ormawa = o.id";

    if ($user_level == 2) {
        // Admin Ormawa: hanya lihat dokumen dari ormawanya
        $ormawa_id = $_SESSION['ormawa_id'] ?? 0;
        if (!$ormawa_id) {
            $user_id = (int)$_SESSION['user_id'];
            $stmt_user = mysqli_prepare($koneksi, "SELECT id_ormawa FROM user WHERE id = ?");
            mysqli_stmt_bind_param($stmt_user, "i", $user_id);
            mysqli_stmt_execute($stmt_user);
            $res_user = mysqli_stmt_get_result($stmt_user);
            $user_data = mysqli_fetch_assoc($res_user);
            mysqli_stmt_close($stmt_user);

            if (!$user_data || !$user_data['id_ormawa']) {
                return [];
            }
            $ormawa_id = (int)$user_data['id_ormawa'];
            $_SESSION['ormawa_id'] = $ormawa_id;
        }

        $query = $base_query . " WHERE d.id_ormawa = ? ORDER BY d.tanggal_upload DESC";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $ormawa_id);
    } else if ($user_level == 1) {
        // SuperAdmin: lihat semua dokumen
        $query = $base_query . " ORDER BY d.tanggal_upload DESC";
        $stmt = mysqli_prepare($koneksi, $query);
    } else {
        return [];
    }

    if (!$stmt) {
        return [];
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $data;
}

function getOrmawaList($koneksi) {
    $query = "SELECT id, nama_ormawa FROM ormawa ORDER BY nama_ormawa ASC";
    $result = mysqli_query($koneksi, $query);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function getAdminOrmawaInfo($koneksi) {
    if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] != 2) {
        return null;
    }

    $ormawa_id = $_SESSION['ormawa_id'] ?? null;
    $ormawa_nama = $_SESSION['ormawa_nama'] ?? null;

    if (!$ormawa_id || !$ormawa_nama) {
        if (!isset($_SESSION['user_id'])) return null;

        $user_id = (int)$_SESSION['user_id'];
        
        $stmt = mysqli_prepare($koneksi, "
            SELECT u.id_ormawa, o.nama_ormawa 
            FROM user u 
            LEFT JOIN ormawa o ON u.id_ormawa = o.id 
            WHERE u.id = ? AND u.level = '2'
        ");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($data && $data['id_ormawa']) {
            $_SESSION['ormawa_id'] = $data['id_ormawa'];
            $_SESSION['ormawa_nama'] = $data['nama_ormawa'];
            return [
                'id' => $data['id_ormawa'],
                'nama_ormawa' => $data['nama_ormawa']
            ];
        }
        return null;
    }

    return [
        'id' => $ormawa_id,
        'nama_ormawa' => $ormawa_nama
    ];
}

function uploadDokumen($file, $folder = 'dokumen') {
    $target_dir = "../../../Uploads/" . $folder . "/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $fileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
    
    // Validasi tipe file
    if (!in_array($fileType, $allowedTypes)) {
        return false;
    }
    
    // Validasi ukuran file (max 10MB)
    if ($file["size"] > 10000000) {
        return false;
    }
    
    // Generate nama file unik
    $new_filename = uniqid('doc_') . '_' . time() . '.' . $fileType;
    $new_target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $new_target_file)) {
        return $new_filename;
    }
    
    return false;
}

function handleDocumentOperations($koneksi) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    if (!isset($_SESSION['user_id'], $_SESSION['user_level'])) {
        $_SESSION['message'] = 'Harap login terlebih dahulu.';
        $_SESSION['msg_type'] = 'danger';
        return;
    }

    $user_level = $_SESSION['user_level'];
    $user_id = (int)$_SESSION['user_id'];
    $action = $_POST['action'] ?? '';

    $ormawa_id_session = $_SESSION['ormawa_id'] ?? 0;
    if ($user_level == 2 && !$ormawa_id_session) {
        $stmt = mysqli_prepare($koneksi, "SELECT id_ormawa FROM user WHERE id = ? AND level = '2'");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user_data = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if ($user_data && $user_data['id_ormawa']) {
            $ormawa_id_session = (int)$user_data['id_ormawa'];
            $_SESSION['ormawa_id'] = $ormawa_id_session;
        } else {
            $_SESSION['message'] = 'Akun admin tidak terhubung ke organisasi. Hubungi superadmin.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }
    }

    // TAMBAH DOKUMEN
    if ($action === 'tambah') {
        if ($user_level == 1) {
            $id_ormawa = (int)($_POST['id_ormawa'] ?? 0);
            if (!$id_ormawa) {
                $_SESSION['message'] = 'Ormawa tidak valid.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        } elseif ($user_level == 2) {
            $id_ormawa = $ormawa_id_session;
            if (!$id_ormawa) {
                $_SESSION['message'] = 'Anda tidak terdaftar di organisasi mana pun.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        } else {
            $_SESSION['message'] = 'Akses ditolak.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        $nama_dokumen = trim($_POST['nama_dokumen'] ?? '');
        $jenis_dokumen = trim($_POST['jenis_dokumen'] ?? '');

        if (!$nama_dokumen || !$jenis_dokumen) {
            $_SESSION['message'] = 'Semua field wajib diisi.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        // Upload file
        if (empty($_FILES['file_dokumen']['name'])) {
            $_SESSION['message'] = 'File dokumen wajib diupload.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        $file_nama = uploadDokumen($_FILES['file_dokumen']);
        if ($file_nama === false) {
            $_SESSION['message'] = 'Gagal mengupload file. Format: PDF/DOC/DOCX/XLS/XLSX, Max: 10MB.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        // Hitung ukuran file dalam KB
        $file_path_full = "../../../Uploads/dokumen/" . $file_nama;
        $ukuran_file = filesize($file_path_full) / 1024; // Convert to KB

        $stmt = mysqli_prepare($koneksi, "INSERT INTO dokumen (nama_dokumen, jenis_dokumen, id_ormawa, id_user, file_path, ukuran_file) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssiiss", $nama_dokumen, $jenis_dokumen, $id_ormawa, $user_id, $file_nama, $ukuran_file);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Dokumen berhasil ditambahkan.';
            $_SESSION['msg_type'] = 'success';
            $_SESSION['redirect'] = true;
            mysqli_stmt_close($stmt);
        } else {
            // Hapus file jika gagal insert
            if (file_exists($file_path_full)) {
                unlink($file_path_full);
            }
            $_SESSION['message'] = 'Gagal menambahkan dokumen: ' . mysqli_error($koneksi);
            $_SESSION['msg_type'] = 'danger';
            mysqli_stmt_close($stmt);
        }

    } 
    // EDIT DOKUMEN
    elseif ($action === 'edit') {
        $dokumen_id = (int)($_POST['dokumen_id'] ?? 0);
        if (!$dokumen_id) {
            $_SESSION['message'] = 'ID dokumen tidak valid.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        // Cek kepemilikan dokumen
        $stmt_check = mysqli_prepare($koneksi, "SELECT id_ormawa, file_path FROM dokumen WHERE id = ?");
        mysqli_stmt_bind_param($stmt_check, "i", $dokumen_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $doc_data = mysqli_fetch_assoc($result_check);
        mysqli_stmt_close($stmt_check);

        if (!$doc_data) {
            $_SESSION['message'] = 'Dokumen tidak ditemukan.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        if ($user_level == 2 && $doc_data['id_ormawa'] != $ormawa_id_session) {
            $_SESSION['message'] = 'Akses ditolak. Anda hanya dapat mengedit dokumen dari organisasi Anda.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        if ($user_level == 1) {
            $id_ormawa = (int)($_POST['id_ormawa'] ?? $doc_data['id_ormawa']);
        } else {
            $id_ormawa = $ormawa_id_session;
        }

        $nama_dokumen = trim($_POST['nama_dokumen'] ?? '');
        $jenis_dokumen = trim($_POST['jenis_dokumen'] ?? '');
        $file_path_lama = $_POST['file_path_lama'] ?? '';

        if (!$nama_dokumen || !$jenis_dokumen) {
            $_SESSION['message'] = 'Semua field wajib diisi.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        $file_nama = $file_path_lama;
        $ukuran_file = 0;

        // Jika ada file baru
        if (!empty($_FILES['file_dokumen']['name'])) {
            // Hapus file lama
            if ($file_path_lama && file_exists("../../../Uploads/dokumen/" . $file_path_lama)) {
                unlink("../../../Uploads/dokumen/" . $file_path_lama);
            }
            
            // Upload file baru
            $file_nama = uploadDokumen($_FILES['file_dokumen']);
            if ($file_nama === false) {
                $_SESSION['message'] = 'Gagal mengupload file baru.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
            
            // Hitung ukuran file baru
            $file_path_full = "../../../Uploads/dokumen/" . $file_nama;
            $ukuran_file = filesize($file_path_full) / 1024;
        } else {
            // Ambil ukuran file lama
            if ($file_path_lama && file_exists("../../../Uploads/dokumen/" . $file_path_lama)) {
                $ukuran_file = filesize("../../../Uploads/dokumen/" . $file_path_lama) / 1024;
            }
        }

        $stmt = mysqli_prepare($koneksi, "UPDATE dokumen SET nama_dokumen = ?, jenis_dokumen = ?, id_ormawa = ?, file_path = ?, ukuran_file = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssissi", $nama_dokumen, $jenis_dokumen, $id_ormawa, $file_nama, $ukuran_file, $dokumen_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Dokumen berhasil diperbarui.';
            $_SESSION['msg_type'] = 'success';
            $_SESSION['redirect'] = true;
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['message'] = 'Gagal memperbarui dokumen.';
            $_SESSION['msg_type'] = 'danger';
            mysqli_stmt_close($stmt);
        }

    } 
    // HAPUS DOKUMEN
    elseif ($action === 'hapus') {
        $dokumen_id = (int)($_POST['dokumen_id'] ?? 0);
        if (!$dokumen_id) return;

        $stmt_check = mysqli_prepare($koneksi, "SELECT id_ormawa, file_path FROM dokumen WHERE id = ?");
        mysqli_stmt_bind_param($stmt_check, "i", $dokumen_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $doc_data = mysqli_fetch_assoc($result_check);
        mysqli_stmt_close($stmt_check);

        if (!$doc_data) return;

        if ($user_level == 2 && $doc_data['id_ormawa'] != $ormawa_id_session) {
            $_SESSION['message'] = 'Akses ditolak. Anda hanya dapat menghapus dokumen dari organisasi Anda.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        // Hapus file fisik
        if ($doc_data['file_path'] && file_exists("../../../Uploads/dokumen/" . $doc_data['file_path'])) {
            unlink("../../../Uploads/dokumen/" . $doc_data['file_path']);
        }

        $stmt = mysqli_prepare($koneksi, "DELETE FROM dokumen WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $dokumen_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Dokumen berhasil dihapus.';
            $_SESSION['msg_type'] = 'success';
            $_SESSION['redirect'] = true;
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['message'] = 'Gagal menghapus dokumen.';
            $_SESSION['msg_type'] = 'danger';
            mysqli_stmt_close($stmt);
        }
    }

}
?>