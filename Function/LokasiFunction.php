<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function jsonResponse($success, $message, $data = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

try {
    $config_path = __DIR__ . '/../Config/ConnectDB.php';
    if (!file_exists($config_path)) {
        throw new Exception("File koneksi tidak ditemukan.");
    }
    $koneksi = include($config_path);
    if (!$koneksi) throw new Exception("Koneksi database gagal.");

    $user_id = $_SESSION['user_id'] ?? null;
    $user_level = (int)($_SESSION['user_level'] ?? 0);
    $session_ormawa_id = (int)($_SESSION['ormawa_id'] ?? 0);

    if (!$user_id || ($user_level != 1 && $user_level != 2)) {
        throw new Exception("Akses ditolak. Hanya SuperAdmin atau Admin ORMAWA.");
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    // 🔁 Tentukan ORMawa ID target
    $ormawa_id = null;
    if ($user_level === 2) {
        // Admin: wajib ormawa_id dari session
        $ormawa_id = $session_ormawa_id;
        if ($ormawa_id <= 0) {
            throw new Exception("Admin tidak terdaftar di ORMAWA manapun.");
        }
    } elseif ($user_level === 1) {
        // SuperAdmin: ambil dari POST/GET
        $ormawa_id = (int)($_POST['ormawa_id'] ?? $_GET['ormawa_id'] ?? 0);
        if ($ormawa_id <= 0) {
            throw new Exception("Pilih ORMAWA terlebih dahulu.");
        }
    }

    // Validasi bahwa ORMawa ID valid (ada di database)
    $check_ormawa = mysqli_prepare($koneksi, "SELECT id FROM ormawa WHERE id = ?");
    mysqli_stmt_bind_param($check_ormawa, "i", $ormawa_id);
    mysqli_stmt_execute($check_ormawa);
    $ormawa_exists = mysqli_stmt_get_result($check_ormawa)->fetch_assoc();
    mysqli_stmt_close($check_ormawa);
    if (!$ormawa_exists) {
        throw new Exception("ORMAWA tidak ditemukan.");
    }

    // --- TAMBAH ---
    if ($action === 'add') {
        $nama = trim($_POST['nama_lokasi'] ?? '');
        $lat = $_POST['lat'] ?? null;
        $lng = $_POST['lng'] ?? null;
        $radius = (int)($_POST['radius_default'] ?? 100);

        if (empty($nama) || !is_numeric($lat) || !is_numeric($lng)) {
            throw new Exception("Nama, Latitude, dan Longitude wajib diisi dengan format angka.");
        }

        $lat = (float)$lat;
        $lng = (float)$lng;
        if ($lat < -90 || $lat > 90) throw new Exception("Latitude harus antara -90 sampai 90");
        if ($lng < -180 || $lng > 180) throw new Exception("Longitude harus antara -180 sampai 180");
        if ($radius < 10 || $radius > 500) throw new Exception("Radius harus 10–500 meter.");

        $query = "INSERT INTO lokasi_absen (ormawa_id, nama_lokasi, lat, lng, radius_default) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        if (!$stmt) throw new Exception("Query gagal: " . mysqli_error($koneksi));

        mysqli_stmt_bind_param($stmt, "isddi", $ormawa_id, $nama, $lat, $lng, $radius);

        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, 'Lokasi berhasil ditambahkan!');
        } else {
            throw new Exception("Gagal menyimpan: " . mysqli_stmt_error($stmt));
        }
    }

    // --- EDIT ---
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $nama = trim($_POST['nama_lokasi'] ?? '');
        $lat = $_POST['lat'] ?? null;
        $lng = $_POST['lng'] ?? null;
        $radius = (int)($_POST['radius_default'] ?? 100);

        if ($id <= 0 || empty($nama) || !is_numeric($lat) || !is_numeric($lng)) {
            throw new Exception("Data tidak valid.");
        }

        $lat = (float)$lat;
        $lng = (float)$lng;
        if ($lat < -90 || $lat > 90) throw new Exception("Latitude harus antara -90 sampai 90");
        if ($lng < -180 || $lng > 180) throw new Exception("Longitude harus antara -180 sampai 180");
        if ($radius < 10 || $radius > 500) throw new Exception("Radius harus 10–500 meter.");

        // Pastikan lokasi milik ORMawa ini
        $check = mysqli_prepare($koneksi, "SELECT id FROM lokasi_absen WHERE id = ? AND ormawa_id = ?");
        mysqli_stmt_bind_param($check, "ii", $id, $ormawa_id);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_num_rows(mysqli_stmt_get_result($check)) == 0) {
            throw new Exception("Lokasi tidak ditemukan atau bukan milik ORMAWA ini.");
        }
        mysqli_stmt_close($check);

        $query = "UPDATE lokasi_absen SET nama_lokasi = ?, lat = ?, lng = ?, radius_default = ? 
                  WHERE id = ? AND ormawa_id = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        if (!$stmt) throw new Exception("Query edit gagal.");

        mysqli_stmt_bind_param($stmt, "sddiii", $nama, $lat, $lng, $radius, $id, $ormawa_id);

        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, 'Lokasi berhasil diperbarui!');
        } else {
            throw new Exception("Gagal memperbarui: " . mysqli_stmt_error($stmt));
        }
    }

    // --- HAPUS ---
    if ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            throw new Exception("ID tidak valid.");
        }

        $query = "DELETE FROM lokasi_absen WHERE id = ? AND ormawa_id = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ii", $id, $ormawa_id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            jsonResponse(true, 'Lokasi berhasil dihapus!');
        } else {
            throw new Exception("Lokasi tidak ditemukan atau bukan milik ORMAWA ini.");
        }
    }

    throw new Exception("Aksi tidak dikenali.");

} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(200);
    jsonResponse(false, $e->getMessage());
}
?>