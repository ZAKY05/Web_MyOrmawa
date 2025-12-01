<?php
// Fungsi untuk mendapatkan data event, dengan filter berdasarkan user_id jika admin
function getEventData($koneksi) {
    if (!isset($_SESSION['user_id'], $_SESSION['user_level'])) {
        return [];
    }

    $user_level = $_SESSION['user_level'];
    $base_query = "SELECT e.*, o.nama_ormawa 
                   FROM event e 
                   JOIN ormawa o ON e.ormawa_id = o.id";

    if ($user_level == 2) {
        // Admin Ormawa: hanya lihat event dari ormawanya
        $ormawa_id = $_SESSION['ormawa_id'] ?? 0;
        if (!$ormawa_id) {
            // Jika session tidak punya ormawa_id, ambil dari user table
            $user_id = (int)$_SESSION['user_id'];
            $stmt_user = mysqli_prepare($koneksi, "SELECT u.ormawa_id FROM user u WHERE u.id = ?");
            mysqli_stmt_bind_param($stmt_user, "i", $user_id);
            mysqli_stmt_execute($stmt_user);
            $res_user = mysqli_stmt_get_result($stmt_user);
            $user_data = mysqli_fetch_assoc($res_user);
            mysqli_stmt_close($stmt_user);

            if (!$user_data || !$user_data['ormawa_id']) {
                return []; // Tidak bisa lanjut tanpa ormawa
            }
            $ormawa_id = (int)$user_data['ormawa_id'];
            $_SESSION['ormawa_id'] = $ormawa_id;

            // Ambil juga nama ormawa untuk session
            $stmt_orm = mysqli_prepare($koneksi, "SELECT nama_ormawa FROM ormawa WHERE id = ?");
            mysqli_stmt_bind_param($stmt_orm, "i", $ormawa_id);
            mysqli_stmt_execute($stmt_orm);
            $res_orm = mysqli_stmt_get_result($stmt_orm);
            $orm_data = mysqli_fetch_assoc($res_orm);
            mysqli_stmt_close($stmt_orm);

            if ($orm_data) {
                $_SESSION['ormawa_nama'] = $orm_data['nama_ormawa'];
            }
        }

        $query = $base_query . " WHERE e.ormawa_id = ? ORDER BY e.tgl_mulai DESC";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $ormawa_id);
    } else if ($user_level == 1) {
        // SuperAdmin: lihat semua event
        $query = $base_query . " ORDER BY e.tgl_mulai DESC";
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

// Fungsi untuk mendapatkan daftar ormawa
function getOrmawaList($koneksi) {
    $query = "SELECT id, nama_ormawa FROM ormawa ORDER BY nama_ormawa ASC";
    $result = mysqli_query($koneksi, $query);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

// ✅ DIPERBAIKI: Ambil info ormawa admin dari SESSION atau DATABASE sebagai fallback
function getAdminOrmawaInfo($koneksi) {
    $ormawa_id = $_SESSION['ormawa_id'] ?? null;
    $ormawa_nama = $_SESSION['ormawa_nama'] ?? null;

    // Jika session tidak lengkap, ambil dari database
    if (!$ormawa_id || !$ormawa_nama) {
        if (!isset($_SESSION['user_id'])) return null;

        $user_id = (int)$_SESSION['user_id'];
        $stmt = mysqli_prepare($koneksi, "
            SELECT u.ormawa_id, o.nama_ormawa 
            FROM user u 
            LEFT JOIN ormawa o ON u.ormawa_id = o.id 
            WHERE u.id = ? AND u.user_level = 2
        ");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($data && $data['ormawa_id']) {
            $_SESSION['ormawa_id'] = $data['ormawa_id'];
            $_SESSION['ormawa_nama'] = $data['nama_ormawa'];
            return [
                'id' => $data['ormawa_id'],
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

// Fungsi upload gambar (tidak diubah)
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

// Fungsi handle operasi event
function handleEventOperations($koneksi) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    if (!isset($_SESSION['user_id'], $_SESSION['user_level'])) {
        $_SESSION['message'] = 'Harap login terlebih dahulu.';
        $_SESSION['msg_type'] = 'danger';
        return;
    }

    $user_level = $_SESSION['user_level'];
    $action = $_POST['action'] ?? '';

    // ✅ Ambil ormawa_id dari session, jika tidak ada → ambil dari DB (lebih aman)
    $ormawa_id_session = $_SESSION['ormawa_id'] ?? 0;
    if ($user_level === 2 && !$ormawa_id_session) {
        $user_id = (int)$_SESSION['user_id'];
        $stmt = mysqli_prepare($koneksi, "SELECT ormawa_id FROM user WHERE id = ? AND user_level = 2");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user_data = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if ($user_data && $user_data['ormawa_id']) {
            $ormawa_id_session = (int)$user_data['ormawa_id'];
            $_SESSION['ormawa_id'] = $ormawa_id_session;
        } else {
            $_SESSION['message'] = 'Akun admin tidak terhubung ke organisasi. Hubungi superadmin.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }
    }

    if ($action === 'tambah') {
        if ($user_level === 1) {
            // SuperAdmin: bisa pilih ormawa
            $ormawa_id = (int)($_POST['ormawa_id'] ?? 0);
            if (!$ormawa_id) {
                $_SESSION['message'] = 'Ormawa tidak valid.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        } elseif ($user_level === 2) {
            // ✅ Admin Ormawa: **harus** pakai ormawa_id dari session/DB (tidak boleh override via POST)
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
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $tgl_mulai = $_POST['tgl_mulai'] ?? '';
        $tgl_selesai = $_POST['tgl_selesai'] ?? '';
        $lokasi = trim($_POST['lokasi'] ?? '');

        if (!$nama_event || !$deskripsi || !$tgl_mulai || !$tgl_selesai || !$lokasi) {
            $_SESSION['message'] = 'Semua field wajib diisi.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        $gambar_nama = '';
        if (!empty($_FILES['gambar']['name'])) {
            $gambar_nama = uploadGambar($_FILES['gambar'], 'event');
            if ($gambar_nama === false) {
                $_SESSION['message'] = 'Gagal mengupload gambar. Pastikan format JPG/PNG dan ukuran ≤2MB.';
                $_SESSION['msg_type'] = 'danger';
                return;
            }
        }

        $stmt = mysqli_prepare($koneksi, "INSERT INTO event (ormawa_id, nama_event, deskripsi, tgl_mulai, tgl_selesai, lokasi, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issssss", $ormawa_id, $nama_event, $deskripsi, $tgl_mulai, $tgl_selesai, $lokasi, $gambar_nama);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Event berhasil ditambahkan.';
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Gagal menambahkan event: ' . mysqli_error($koneksi);
            $_SESSION['msg_type'] = 'danger';
        }
        mysqli_stmt_close($stmt);

    } elseif ($action === 'edit') {
        $event_id = (int)($_POST['event_id'] ?? 0);
        if (!$event_id) {
            $_SESSION['message'] = 'ID event tidak valid.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        // Ambil data event untuk validasi kepemilikan
        $stmt_check = mysqli_prepare($koneksi, "SELECT ormawa_id FROM event WHERE id = ?");
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

        // Validasi akses: Admin hanya boleh edit event dari ormawanya
        if ($user_level === 2 && $event_data['ormawa_id'] != $ormawa_id_session) {
            $_SESSION['message'] = 'Akses ditolak. Anda hanya dapat mengedit event dari organisasi Anda.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        if ($user_level === 1) {
            $ormawa_id = (int)($_POST['ormawa_id'] ?? $event_data['ormawa_id']);
        } else {
            $ormawa_id = $ormawa_id_session; // 🔒 Admin tidak bisa ganti ormawa
        }

        $nama_event = trim($_POST['nama_event'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $tgl_mulai = $_POST['tgl_mulai'] ?? '';
        $tgl_selesai = $_POST['tgl_selesai'] ?? '';
        $lokasi = trim($_POST['lokasi'] ?? '');
        $gambar_lama = $_POST['gambar_lama'] ?? '';

        if (!$nama_event || !$deskripsi || !$tgl_mulai || !$tgl_selesai || !$lokasi) {
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

        $stmt = mysqli_prepare($koneksi, "UPDATE event SET ormawa_id = ?, nama_event = ?, deskripsi = ?, tgl_mulai = ?, tgl_selesai = ?, lokasi = ?, gambar = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "issssssi", $ormawa_id, $nama_event, $deskripsi, $tgl_mulai, $tgl_selesai, $lokasi, $gambar_nama, $event_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Event berhasil diperbarui.';
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Gagal memperbarui event.';
            $_SESSION['msg_type'] = 'danger';
        }
        mysqli_stmt_close($stmt);

    } elseif ($action === 'hapus') {
        $event_id = (int)($_POST['event_id'] ?? 0);
        if (!$event_id) return;

        $stmt_check = mysqli_prepare($koneksi, "SELECT ormawa_id, gambar FROM event WHERE id = ?");
        mysqli_stmt_bind_param($stmt_check, "i", $event_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $event_data = mysqli_fetch_assoc($result_check);
        mysqli_stmt_close($stmt_check);

        if (!$event_data) return;

        // Validasi akses: Admin hanya boleh hapus event dari ormawanya
        if ($user_level === 2 && $event_data['ormawa_id'] != $ormawa_id_session) {
            $_SESSION['message'] = 'Akses ditolak. Anda hanya dapat menghapus event dari organisasi Anda.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        // Hapus gambar
        if ($event_data['gambar'] && file_exists("../../../Uploads/event/" . $event_data['gambar'])) {
            unlink("../../../Uploads/event/" . $event_data['gambar']);
        }

        $stmt = mysqli_prepare($koneksi, "DELETE FROM event WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $event_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Event berhasil dihapus.';
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Gagal menghapus event.';
            $_SESSION['msg_type'] = 'danger';
        }
        mysqli_stmt_close($stmt);
    }
}
?>