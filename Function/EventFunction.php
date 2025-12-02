<?php
// ========================================
// EVENT FUNCTION - FIXED VERSION
// ========================================

// Fungsi untuk mendapatkan data event
function getEventData($koneksi) {
    if (!isset($_SESSION['user_id'], $_SESSION['user_level'])) {
        return [];
    }

    $user_level = $_SESSION['user_level'];
    $base_query = "SELECT e.*, o.nama_ormawa 
                   FROM event e 
                   JOIN ormawa o ON e.ormawa_id = o.id 
                   WHERE e.tgl_selesai >= CURDATE()";

    if ($user_level == 2) {
        // Admin Ormawa: hanya lihat event dari ormawanya
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

        $query = $base_query . " AND e.ormawa_id = ? ORDER BY e.id DESC";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $ormawa_id);
    } else if ($user_level == 1) {
        // SuperAdmin: lihat semua event
        $query = $base_query . " ORDER BY e.id DESC";
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

function uploadGambar($file, $folder) {
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

function uploadPDF($file, $folder) {
    $target_dir = "../../../Uploads/" . $folder . "/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $fileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
    if ($fileType !== "pdf") {
        return false;
    }
    
    if ($file["size"] > 5000000) {
        return false;
    }
    
    $new_filename = uniqid() . '.pdf';
    $new_target_file = $target_dir . $new_filename;
    return move_uploaded_file($file["tmp_name"], $new_target_file) ? $new_filename : false;
}

function handleEventOperations($koneksi) {
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
            $ormawa_id = (int)($_POST['ormawa_id'] ?? 0);
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

        $nama_event = trim($_POST['nama_event'] ?? '');
        $kategori = trim($_POST['kategori'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $tgl_mulai = $_POST['tgl_mulai'] ?? '';
        $tgl_selesai = $_POST['tgl_selesai'] ?? '';
        $lokasi = trim($_POST['lokasi'] ?? '');

        if (!$nama_event || !$kategori || !$deskripsi || !$tgl_mulai || !$tgl_selesai || !$lokasi) {
            $_SESSION['message'] = 'Semua field wajib diisi.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        $gambar_nama = '';
        if (!empty($_FILES['gambar']['name'])) {
            $gambar_nama = uploadGambar($_FILES['gambar'], 'event');
            if ($gambar_nama === false) {
                $_SESSION['message'] = 'Gagal mengupload gambar. Format: JPG/PNG, Max: 2MB.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        }

        $buku_panduan_nama = '';
        if (!empty($_FILES['buku_panduan']['name'])) {
            $buku_panduan_nama = uploadPDF($_FILES['buku_panduan'], 'event_panduan');
            if ($buku_panduan_nama === false) {
                $_SESSION['message'] = 'Gagal mengupload buku panduan. Format: PDF, Max: 5MB.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        }

        $stmt = mysqli_prepare($koneksi, "INSERT INTO event (ormawa_id, nama_event, kategori, deskripsi, tgl_mulai, tgl_selesai, lokasi, gambar, buku_panduan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issssssss", $ormawa_id, $nama_event, $kategori, $deskripsi, $tgl_mulai, $tgl_selesai, $lokasi, $gambar_nama, $buku_panduan_nama);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Event berhasil ditambahkan.';
            $_SESSION['msg_type'] = 'success';
            $_SESSION['redirect'] = true; // Flag untuk redirect via JavaScript
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['message'] = 'Gagal menambahkan event: ' . mysqli_error($koneksi);
            $_SESSION['msg_type'] = 'danger';
            mysqli_stmt_close($stmt);
        }

    } elseif ($action === 'edit') {
        $event_id = (int)($_POST['event_id'] ?? 0);
        if (!$event_id) {
            $_SESSION['message'] = 'ID event tidak valid.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        $stmt_check = mysqli_prepare($koneksi, "SELECT ormawa_id, gambar, buku_panduan FROM event WHERE id = ?");
        mysqli_stmt_bind_param($stmt_check, "i", $event_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $event_data = mysqli_fetch_assoc($result_check);
        mysqli_stmt_close($stmt_check);

        if (!$event_data) {
            $_SESSION['message'] = 'Event tidak ditemukan.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        if ($user_level == 2 && $event_data['ormawa_id'] != $ormawa_id_session) {
            $_SESSION['message'] = 'Akses ditolak. Anda hanya dapat mengedit event dari organisasi Anda.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        if ($user_level == 1) {
            $ormawa_id = (int)($_POST['ormawa_id'] ?? $event_data['ormawa_id']);
        } else {
            $ormawa_id = $ormawa_id_session;
        }

        $nama_event = trim($_POST['nama_event'] ?? '');
        $kategori = trim($_POST['kategori'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $tgl_mulai = $_POST['tgl_mulai'] ?? '';
        $tgl_selesai = $_POST['tgl_selesai'] ?? '';
        $lokasi = trim($_POST['lokasi'] ?? '');
        $gambar_lama = $_POST['gambar_lama'] ?? '';
        $buku_panduan_lama = $_POST['buku_panduan_lama'] ?? '';

        if (!$nama_event || !$kategori || !$deskripsi || !$tgl_mulai || !$tgl_selesai || !$lokasi) {
            $_SESSION['message'] = 'Semua field wajib diisi.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        $gambar_nama = $gambar_lama;
        if (!empty($_FILES['gambar']['name'])) {
            if ($gambar_lama && file_exists("../../../Uploads/event/" . $gambar_lama)) {
                unlink("../../../Uploads/event/" . $gambar_lama);
            }
            $gambar_nama = uploadGambar($_FILES['gambar'], 'event');
            if ($gambar_nama === false) {
                $_SESSION['message'] = 'Gagal mengupload gambar baru.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        }

        $buku_panduan_nama = $buku_panduan_lama;
        if (!empty($_FILES['buku_panduan']['name'])) {
            if ($buku_panduan_lama && file_exists("../../../Uploads/event_panduan/" . $buku_panduan_lama)) {
                unlink("../../../Uploads/event_panduan/" . $buku_panduan_lama);
            }
            $buku_panduan_nama = uploadPDF($_FILES['buku_panduan'], 'event_panduan');
            if ($buku_panduan_nama === false) {
                $_SESSION['message'] = 'Gagal mengupload buku panduan baru.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        }

        $stmt = mysqli_prepare($koneksi, "UPDATE event SET ormawa_id = ?, nama_event = ?, kategori = ?, deskripsi = ?, tgl_mulai = ?, tgl_selesai = ?, lokasi = ?, gambar = ?, buku_panduan = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "issssssssi", $ormawa_id, $nama_event, $kategori, $deskripsi, $tgl_mulai, $tgl_selesai, $lokasi, $gambar_nama, $buku_panduan_nama, $event_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Event berhasil diperbarui.';
            $_SESSION['msg_type'] = 'success';
            $_SESSION['redirect'] = true;
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['message'] = 'Gagal memperbarui event.';
            $_SESSION['msg_type'] = 'danger';
            mysqli_stmt_close($stmt);
        }

    } elseif ($action === 'hapus') {
        $event_id = (int)($_POST['event_id'] ?? 0);
        if (!$event_id) return;

        $stmt_check = mysqli_prepare($koneksi, "SELECT ormawa_id, gambar, buku_panduan FROM event WHERE id = ?");
        mysqli_stmt_bind_param($stmt_check, "i", $event_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $event_data = mysqli_fetch_assoc($result_check);
        mysqli_stmt_close($stmt_check);

        if (!$event_data) return;

        if ($user_level == 2 && $event_data['ormawa_id'] != $ormawa_id_session) {
            $_SESSION['message'] = 'Akses ditolak. Anda hanya dapat menghapus event dari organisasi Anda.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        if ($event_data['gambar'] && file_exists("../../../Uploads/event/" . $event_data['gambar'])) {
            unlink("../../../Uploads/event/" . $event_data['gambar']);
        }

        if ($event_data['buku_panduan'] && file_exists("../../../Uploads/event_panduan/" . $event_data['buku_panduan'])) {
            unlink("../../../Uploads/event_panduan/" . $event_data['buku_panduan']);
        }

        $stmt = mysqli_prepare($koneksi, "DELETE FROM event WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $event_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Event berhasil dihapus.';
            $_SESSION['msg_type'] = 'success';
            $_SESSION['redirect'] = true;
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['message'] = 'Gagal menghapus event.';
            $_SESSION['msg_type'] = 'danger';
            mysqli_stmt_close($stmt);
        }
    }
}
?>