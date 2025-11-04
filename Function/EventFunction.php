<?php
// ... (koneksi dan fungsi lainnya)

// Fungsi untuk mendapatkan data event, dengan filter berdasarkan ormawa_id jika login sebagai admin organisasi
// Di sini, karena session dihapus, kita asumsikan tidak ada filter ormawa_id kecuali ditentukan dari parameter eksternal.
// Jika fungsi ini sebelumnya mengandalkan session, maka sekarang tidak lagi.
function getEventData($koneksi) {
    // Asumsikan tidak ada session, jadi tidak ada filter berdasarkan role atau ormawa_id
    // Jika sebelumnya ada filter untuk admin organisasi, maka filter itu hilang.
    $query = "SELECT e.*, o.nama_ormawa FROM event e JOIN ormawa o ON e.ormawa_id = o.id";
    // Tidak ada WHERE clause karena session dihapus
    $stmt = $koneksi->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fungsi untuk mendapatkan daftar ormawa untuk dropdown
function getOrmawaList($koneksi) {
    // Karena session dihapus, kita asumsikan selalu seperti super_admin (ambil semua)
    // atau kembalikan kosong jika tidak ada mekanisme lain untuk menentukan akses.
    // Jika sebelumnya ada logika untuk role, maka itu dihapus.
    $query = "SELECT id, nama_ormawa FROM ormawa ORDER BY nama_ormawa ASC";
    $result = $koneksi->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fungsi untuk mendapatkan informasi ormawa milik admin organisasi (digunakan untuk tampilan readonly)
function getAdminOrmawaInfo($koneksi) {
    // Karena session dihapus, fungsi ini tidak bisa mengetahui ormawa milik user.
    // Kembalikan null karena tidak bisa menentukan info dari session.
    return null;
}

function handleEventOperations($koneksi) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'] ?? '';
        // Karena session dihapus, kita tidak bisa mengetahui user_id, role, atau ormawa_id
        // Asumsikan semua operasi diizinkan seolah-olah adalah super_admin
        // atau tambahkan parameter POST untuk role/user jika tidak ada session

        // CONTOH: Kita asumsikan selalu super_admin untuk menjaga fungsi tetap berjalan
        $userRole = 'super_admin'; // Hardcoded karena session dihapus
        $userOrmawaId = null; // Tidak bisa diambil dari session

        if ($action === 'tambah') {
            // Karena session dihapus, kita tidak bisa memvalidasi role di sini kecuali dari tempat lain
            // Jika tidak ada mekanisme lain, maka hanya super_admin hardcoded yang bisa
            if ($userRole !== 'super_admin') { // Hanya ini yang bisa di cek sekarang
                 $_SESSION['message'] = 'Akses ditolak.';
                 $_SESSION['msg_type'] = 'danger';
                 return;
            }

            $ormawa_id = $_POST['ormawa_id'];

            // Karena userOrmawaId tidak bisa didapat dari session, logika ini tidak bisa dijalankan
            // Kita abaikan pengecekan ormawa_id untuk admin_organisasi
            // (Kecuali ormawa_id diambil dari tempat lain)

            $nama_event = $_POST['nama_event'];
            $deskripsi = $_POST['deskripsi'];
            $tgl_mulai = $_POST['tgl_mulai'];
            $tgl_selesai = $_POST['tgl_selesai'];
            $lokasi = $_POST['lokasi'];

            $gambar_nama = '';
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
                $gambar_nama = uploadGambar($_FILES['gambar'], 'event');
                if ($gambar_nama === false) {
                    $_SESSION['message'] = 'Gagal mengupload gambar.';
                    $_SESSION['msg_type'] = 'danger';
                    return;
                }
            }

            $stmt = $koneksi->prepare("INSERT INTO event (ormawa_id, nama_event, deskripsi, tgl_mulai, tgl_selesai, lokasi, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $ormawa_id, $nama_event, $deskripsi, $tgl_mulai, $tgl_selesai, $lokasi, $gambar_nama);

            if ($stmt->execute()) {
                $_SESSION['message'] = 'Event berhasil ditambahkan.';
                $_SESSION['msg_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Gagal menambahkan event.';
                $_SESSION['msg_type'] = 'danger';
            }
            $stmt->close();

        } elseif ($action === 'edit') {
            $event_id = $_POST['event_id'];

            // Ambil ormawa_id dari event yang akan diedit
            $stmt_check = $koneksi->prepare("SELECT ormawa_id FROM event WHERE id = ?");
            $stmt_check->bind_param("i", $event_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows !== 1) {
                 $_SESSION['message'] = 'Event tidak ditemukan.';
                 $_SESSION['msg_type'] = 'danger';
                 $stmt_check->close();
                 return;
            }
            $event_data = $result_check->fetch_assoc();
            $stmt_check->close();

            // $event_ormawa_id = $event_data['ormawa_id'];
            // Karena session dihapus, kita tidak bisa memvalidasi akses user terhadap event ini
            // Jadi, kita asumsikan akses diperbolehkan (seperti super_admin)
            // Kode validasi akses dihapus

            $ormawa_id = $_POST['ormawa_id'];
            // Karena userOrmawaId tidak ada, kita tidak bisa memvalidasi bahwa admin organisasi hanya bisa edit untuk ormawanya sendiri
            // Kita abaikan validasi ini

            $nama_event = $_POST['nama_event'];
            $deskripsi = $_POST['deskripsi'];
            $tgl_mulai = $_POST['tgl_mulai'];
            $tgl_selesai = $_POST['tgl_selesai'];
            $lokasi = $_POST['lokasi'];
            $gambar_lama = $_POST['gambar_lama'];

            $gambar_nama = $gambar_lama;
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
                // Hapus gambar lama jika ada dan bukan default
                if ($gambar_lama && file_exists('../../../Uploads/event/' . $gambar_lama)) {
                    unlink('../../../Uploads/event/' . $gambar_lama);
                }
                $gambar_nama = uploadGambar($_FILES['gambar'], 'event');
                if ($gambar_nama === false) {
                    $_SESSION['message'] = 'Gagal mengupload gambar baru.';
                    $_SESSION['msg_type'] = 'danger';
                    return;
                }
            }

            $stmt = $koneksi->prepare("UPDATE event SET ormawa_id=?, nama_event=?, deskripsi=?, tgl_mulai=?, tgl_selesai=?, lokasi=?, gambar=? WHERE id=?");
            $stmt->bind_param("issssssi", $ormawa_id, $nama_event, $deskripsi, $tgl_mulai, $tgl_selesai, $lokasi, $gambar_nama, $event_id);

            if ($stmt->execute()) {
                $_SESSION['message'] = 'Event berhasil diperbarui.';
                $_SESSION['msg_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Gagal memperbarui event.';
                $_SESSION['msg_type'] = 'danger';
            }
            $stmt->close();

        } elseif ($action === 'hapus') {
            $event_id = $_POST['event_id'];

            // Ambil ormawa_id dari event yang akan dihapus
            $stmt_check = $koneksi->prepare("SELECT ormawa_id FROM event WHERE id = ?");
            $stmt_check->bind_param("i", $event_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows !== 1) {
                 $_SESSION['message'] = 'Event tidak ditemukan.';
                 $_SESSION['msg_type'] = 'danger';
                 $stmt_check->close();
                 return;
            }
            $event_data = $result_check->fetch_assoc();
            $stmt_check->close();

            // $event_ormawa_id = $event_data['ormawa_id'];
            // Karena session dihapus, tidak bisa memvalidasi akses
            // Asumsikan akses diperbolehkan

            // Ambil nama gambar dari database untuk dihapus
            $stmt = $koneksi->prepare("SELECT gambar FROM event WHERE id = ?");
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $event = $result->fetch_assoc();
            $stmt->close();

            if ($event && $event['gambar']) {
                $gambar_path = '../../../Uploads/event/' . $event['gambar'];
                if (file_exists($gambar_path)) {
                    unlink($gambar_path); // Hapus file dari server
                }
            }

            $stmt_hapus = $koneksi->prepare("DELETE FROM event WHERE id = ?");
            $stmt_hapus->bind_param("i", $event_id);

            if ($stmt_hapus->execute()) {
                $_SESSION['message'] = 'Event berhasil dihapus.';
                $_SESSION['msg_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Gagal menghapus event.';
                $_SESSION['msg_type'] = 'danger';
            }
            $stmt_hapus->close();
        }
    }
}

function uploadGambar($file, $folder) {
    $target_dir = "../../../Uploads/" . $folder . "/";
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Cek apakah file adalah gambar sebenarnya
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        $uploadOk = 0;
    }

    // Cek ukuran file (contoh: 2MB)
    if ($file["size"] > 2000000) {
        $uploadOk = 0;
    }

    // Batasi format file
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        $uploadOk = 0;
    }

    // Jika semua cek lolos
    if ($uploadOk == 1) {
        // Buat nama file unik untuk menghindari konflik
        $new_filename = uniqid() . '.' . $imageFileType;
        $new_target_file = $target_dir . $new_filename;

        if (move_uploaded_file($file["tmp_name"], $new_target_file)) {
            return $new_filename; // Kembalikan nama file baru
        } else {
            return false; // Gagal upload
        }
    } else {
        return false; // Gagal karena cek
    }
}

// ... (fungsi lainnya jika ada)
?>