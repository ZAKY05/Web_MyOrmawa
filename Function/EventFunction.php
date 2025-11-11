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
        if (!isset($_SESSION['ormawa_id'])) {
            return []; // Admin harus punya ormawa
        }
        $ormawa_id = (int)$_SESSION['ormawa_id'];
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
        // Tambahkan logging error jika perlu
        return [];
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $data;
}


// Fungsi untuk mendapatkan daftar ormawa
function getOrmawaList($koneksi)
{
    $query = "SELECT id, nama_ormawa FROM ormawa ORDER BY nama_ormawa ASC";
    $result = mysqli_query($koneksi, $query);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

// Fungsi untuk mendapatkan info ormawa admin (untuk tampilan)
function getAdminOrmawaInfo($koneksi)
{
    if (!isset($_SESSION['ormawa_id']) || !isset($_SESSION['ormawa_nama'])) {
        return null;
    }
    return [
        'id' => $_SESSION['ormawa_id'],
        'nama_ormawa' => $_SESSION['ormawa_nama']
    ];
}

// Fungsi upload gambar (tidak diubah)
function uploadGambar($file, $folder)
{
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
function handleEventOperations($koneksi)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = 'Harap login terlebih dahulu.';
        $_SESSION['msg_type'] = 'danger';
        return;
    }

    $user_level = $_SESSION['user_level'];
    $ormawa_id_session = $_SESSION['ormawa_id'] ?? 0;
    $action = $_POST['action'] ?? '';

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
            // Admin Ormawa: hanya bisa buat event untuk ormawanya
            $ormawa_id = $ormawa_id_session;
        } else {
            $_SESSION['message'] = 'Akses ditolak.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        $nama_event = trim($_POST['nama_event']);
        $deskripsi = trim($_POST['deskripsi']);
        $tgl_mulai = $_POST['tgl_mulai'];
        $tgl_selesai = $_POST['tgl_selesai'];
        $lokasi = trim($_POST['lokasi']);

        if (!$nama_event || !$deskripsi || !$tgl_mulai || !$tgl_selesai || !$lokasi) {
            $_SESSION['message'] = 'Semua field wajib diisi.';
            $_SESSION['msg_type'] = 'danger';
            return;
        }

        $gambar_nama = '';
        if (!empty($_FILES['gambar']['name'])) {
            $gambar_nama = uploadGambar($_FILES['gambar'], 'event');
            if ($gambar_nama === false) {
                $_SESSION['message'] = 'Gagal mengupload gambar.';
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
            $_SESSION['message'] = 'Gagal menambahkan event.';
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

        // Tentukan ormawa_id yang boleh diupdate
        if ($user_level === 1) {
            $ormawa_id = (int)($_POST['ormawa_id'] ?? $event_data['ormawa_id']);
        } else {
            $ormawa_id = $ormawa_id_session; // Admin tidak boleh ganti ormawa
        }

        $nama_event = trim($_POST['nama_event']);
        $deskripsi = trim($_POST['deskripsi']);
        $tgl_mulai = $_POST['tgl_mulai'];
        $tgl_selesai = $_POST['tgl_selesai'];
        $lokasi = trim($_POST['lokasi']);
        $gambar_lama = $_POST['gambar_lama'];

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
        }
        mysqli_stmt_close($stmt);
    }
}
?>