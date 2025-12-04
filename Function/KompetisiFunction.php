<?php
// ========================================
// KOMPETISI FUNCTION - WITH TGL_MULAI & TGL_SELESAI
// ========================================

// Fungsi untuk mendapatkan data kompetisi
function getKompetisiData($koneksi) {
    if (!isset($_SESSION['user_id'], $_SESSION['user_level'])) {
        return [];
    }

    $user_level = $_SESSION['user_level'];
    $base_query = "SELECT k.*, o.nama_ormawa 
                   FROM kompetisi k 
                   JOIN ormawa o ON k.id_ormawa = o.id 
                   WHERE k.tgl_selesai >= CURDATE()";

    if ($user_level == 2) {
        // Admin Ormawa: hanya lihat kompetisi dari ormawanya
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

        $query = $base_query . " AND k.id_ormawa = ? ORDER BY k.id DESC";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $ormawa_id);
    } else if ($user_level == 1) {
        // SuperAdmin: lihat semua kompetisi
        $query = $base_query . " ORDER BY k.id DESC";
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

function getOrmawaListKompetisi($koneksi) {
    $query = "SELECT id, nama_ormawa FROM ormawa ORDER BY nama_ormawa ASC";
    $result = mysqli_query($koneksi, $query);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function getAdminOrmawaInfoKompetisi($koneksi) {
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

function uploadGambarKompetisi($file, $folder) {
    $target_dir = "../../../Uploads/" . $folder . "/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $check = getimagesize($file["tmp_name"]);
    if ($check === false || $file["size"] > 2000000 || !in_array($imageFileType, ["jpg", "jpeg", "png"])) {
        return false;
    }
    $new_filename = uniqid() . '.' . $imageFileType;
    $new_target_file = $target_dir . $new_filename;
    return move_uploaded_file($file["tmp_name"], $new_target_file) ? $new_filename : false;
}

function uploadPDFKompetisi($file, $folder) {
    $target_dir = "../../../Uploads/" . $folder . "/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $fileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
    if (!in_array($fileType, ["pdf", "doc", "docx"])) {
        return false;
    }
    
    if ($file["size"] > 10000000) {
        return false;
    }
    
    $new_filename = uniqid() . '.' . $fileType;
    $new_target_file = $target_dir . $new_filename;
    return move_uploaded_file($file["tmp_name"], $new_target_file) ? $new_filename : false;
}

function handleKompetisiOperations($koneksi) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    if (!isset($_SESSION['user_id'], $_SESSION['user_level'])) {
        $_SESSION['message'] = 'Harap login terlebih dahulu.';
        $_SESSION['msg_type'] = 'danger';
        return;
    }

    $user_level = $_SESSION['user_level'];
    $action = $_POST['action'] ?? '';

    $ormawa_id_session = $_SESSION['ormawa_id'] ?? 0;
    if ($user_level == 2 && !$ormawa_id_session) {
        $user_id = (int)$_SESSION['user_id'];
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

    if ($action === 'tambah') {
        if ($user_level == 1) {
            $ormawa_id = (int)($_POST['id_ormawa'] ?? 0);
            if (!$ormawa_id) {
                $_SESSION['message'] = 'Ormawa tidak valid.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        } elseif ($user_level == 2) {
            $ormawa_id = $ormawa_id_session;
            if (!$ormawa_id) {
                $_SESSION['message'] = 'Anda tidak terdaftar di organisasi mana pun.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        } else {
            $_SESSION['message'] = 'Akses ditolak.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        $nama_kompetisi = trim($_POST['nama_kompetisi'] ?? '');
        $penyelenggara = trim($_POST['penyelenggara'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $tgl_mulai = $_POST['tgl_mulai'] ?? '';
        $tgl_selesai = $_POST['tgl_selesai'] ?? '';

        if (!$nama_kompetisi || !$penyelenggara || !$deskripsi || !$tgl_mulai || !$tgl_selesai) {
            $_SESSION['message'] = 'Semua field wajib diisi.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        $gambar_nama = '';
        if (!empty($_FILES['gambar']['name'])) {
            $gambar_nama = uploadGambarKompetisi($_FILES['gambar'], 'kompetisi');
            if ($gambar_nama === false) {
                $_SESSION['message'] = 'Gagal mengupload gambar. Format: JPG/PNG, Max: 2MB.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        }

        $file_panduan_nama = '';
        if (!empty($_FILES['file_panduan']['name'])) {
            $file_panduan_nama = uploadPDFKompetisi($_FILES['file_panduan'], 'kompetisi_panduan');
            if ($file_panduan_nama === false) {
                $_SESSION['message'] = 'Gagal mengupload file panduan. Format: PDF/DOC, Max: 10MB.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        }

        $stmt = mysqli_prepare($koneksi, "INSERT INTO kompetisi (id_ormawa, nama_kompetisi, penyelenggara, deskripsi, tgl_mulai, tgl_selesai, gambar, file_panduan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isssssss", $ormawa_id, $nama_kompetisi, $penyelenggara, $deskripsi, $tgl_mulai, $tgl_selesai, $gambar_nama, $file_panduan_nama);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Kompetisi berhasil ditambahkan.';
            $_SESSION['msg_type'] = 'success';
            $_SESSION['redirect'] = true;
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['message'] = 'Gagal menambahkan kompetisi: ' . mysqli_error($koneksi);
            $_SESSION['msg_type'] = 'danger';
            mysqli_stmt_close($stmt);
        }

    } elseif ($action === 'edit') {
        $kompetisi_id = (int)($_POST['kompetisi_id'] ?? 0);
        if (!$kompetisi_id) {
            $_SESSION['message'] = 'ID kompetisi tidak valid.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        $stmt_check = mysqli_prepare($koneksi, "SELECT id_ormawa, gambar, file_panduan FROM kompetisi WHERE id = ?");
        mysqli_stmt_bind_param($stmt_check, "i", $kompetisi_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $kompetisi_data = mysqli_fetch_assoc($result_check);
        mysqli_stmt_close($stmt_check);

        if (!$kompetisi_data) {
            $_SESSION['message'] = 'Kompetisi tidak ditemukan.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        if ($user_level == 2 && $kompetisi_data['id_ormawa'] != $ormawa_id_session) {
            $_SESSION['message'] = 'Akses ditolak. Anda hanya dapat mengedit kompetisi dari organisasi Anda.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        if ($user_level == 1) {
            $ormawa_id = (int)($_POST['id_ormawa'] ?? $kompetisi_data['id_ormawa']);
        } else {
            $ormawa_id = $ormawa_id_session;
        }

        $nama_kompetisi = trim($_POST['nama_kompetisi'] ?? '');
        $penyelenggara = trim($_POST['penyelenggara'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $tgl_mulai = $_POST['tgl_mulai'] ?? '';
        $tgl_selesai = $_POST['tgl_selesai'] ?? '';
        $gambar_lama = $_POST['gambar_lama'] ?? '';
        $file_panduan_lama = $_POST['file_panduan_lama'] ?? '';

        if (!$nama_kompetisi || !$penyelenggara || !$deskripsi || !$tgl_mulai || !$tgl_selesai) {
            $_SESSION['message'] = 'Semua field wajib diisi.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        $gambar_nama = $gambar_lama;
        if (!empty($_FILES['gambar']['name'])) {
            if ($gambar_lama && file_exists("../../../Uploads/kompetisi/" . $gambar_lama)) {
                unlink("../../../Uploads/kompetisi/" . $gambar_lama);
            }
            $gambar_nama = uploadGambarKompetisi($_FILES['gambar'], 'kompetisi');
            if ($gambar_nama === false) {
                $_SESSION['message'] = 'Gagal mengupload gambar baru.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        }

        $file_panduan_nama = $file_panduan_lama;
        if (!empty($_FILES['file_panduan']['name'])) {
            if ($file_panduan_lama && file_exists("../../../Uploads/kompetisi_panduan/" . $file_panduan_lama)) {
                unlink("../../../Uploads/kompetisi_panduan/" . $file_panduan_lama);
            }
            $file_panduan_nama = uploadPDFKompetisi($_FILES['file_panduan'], 'kompetisi_panduan');
            if ($file_panduan_nama === false) {
                $_SESSION['message'] = 'Gagal mengupload file panduan baru.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        }

        $stmt = mysqli_prepare($koneksi, "UPDATE kompetisi SET id_ormawa = ?, nama_kompetisi = ?, penyelenggara = ?, deskripsi = ?, tgl_mulai = ?, tgl_selesai = ?, gambar = ?, file_panduan = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "isssssssi", $ormawa_id, $nama_kompetisi, $penyelenggara, $deskripsi, $tgl_mulai, $tgl_selesai, $gambar_nama, $file_panduan_nama, $kompetisi_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Kompetisi berhasil diperbarui.';
            $_SESSION['msg_type'] = 'success';
            $_SESSION['redirect'] = true;
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['message'] = 'Gagal memperbarui kompetisi.';
            $_SESSION['msg_type'] = 'danger';
            mysqli_stmt_close($stmt);
        }

    } elseif ($action === 'hapus') {
        $kompetisi_id = (int)($_POST['kompetisi_id'] ?? 0);
        if (!$kompetisi_id) return;

        $stmt_check = mysqli_prepare($koneksi, "SELECT id_ormawa, gambar, file_panduan FROM kompetisi WHERE id = ?");
        mysqli_stmt_bind_param($stmt_check, "i", $kompetisi_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $kompetisi_data = mysqli_fetch_assoc($result_check);
        mysqli_stmt_close($stmt_check);

        if (!$kompetisi_data) return;

        if ($user_level == 2 && $kompetisi_data['id_ormawa'] != $ormawa_id_session) {
            $_SESSION['message'] = 'Akses ditolak. Anda hanya dapat menghapus kompetisi dari organisasi Anda.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        if ($kompetisi_data['gambar'] && file_exists("../../../Uploads/kompetisi/" . $kompetisi_data['gambar'])) {
            unlink("../../../Uploads/kompetisi/" . $kompetisi_data['gambar']);
        }

        if ($kompetisi_data['file_panduan'] && file_exists("../../../Uploads/kompetisi_panduan/" . $kompetisi_data['file_panduan'])) {
            unlink("../../../Uploads/kompetisi_panduan/" . $kompetisi_data['file_panduan']);
        }

        $stmt = mysqli_prepare($koneksi, "DELETE FROM kompetisi WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $kompetisi_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Kompetisi berhasil dihapus.';
            $_SESSION['msg_type'] = 'success';
            $_SESSION['redirect'] = true;
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['message'] = 'Gagal menghapus kompetisi.';
            $_SESSION['msg_type'] = 'danger';
            mysqli_stmt_close($stmt);
        }
    }
}
?>