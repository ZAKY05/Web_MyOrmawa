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

// Helper: validasi koordinat
function validateLatLng($lat, $lng) {
    $lat = floatval($lat);
    $lng = floatval($lng);
    return ($lat >= -90 && $lat <= 90) && ($lng >= -180 && $lng <= 180);
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

    if (!$user_id || ($user_level !== 1 && $user_level !== 2)) {
        throw new Exception("Akses ditolak. Hanya SuperAdmin atau Admin ORMAWA.");
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    // 🔑 Tentukan ormawa_id target (untuk operasi CRUD & get_bank)
    $ormawa_id = null;

    if ($user_level === 2) {
        // Admin: harus dari session
        $ormawa_id = $session_ormawa_id;
        if ($ormawa_id <= 0) {
            throw new Exception("Anda tidak terdaftar di ORMAWA manapun.");
        }
    } elseif ($user_level === 1) {
        // SuperAdmin: ambil dari request (wajib)
        $ormawa_id = (int)($_POST['id_ormawa'] ?? $_GET['id_ormawa'] ?? 0);
        if ($ormawa_id <= 0) {
            throw new Exception("Pilih ORMAWA terlebih dahulu.");
        }
    }

    // ✅ Validasi ORMawa ID eksis di database
    $checkStmt = mysqli_prepare($koneksi, "SELECT id FROM ormawa WHERE id = ?");
    mysqli_stmt_bind_param($checkStmt, "i", $ormawa_id);
    mysqli_stmt_execute($checkStmt);
    $ormawaExists = mysqli_stmt_get_result($checkStmt)->fetch_assoc();
    mysqli_stmt_close($checkStmt);
    if (!$ormawaExists) {
        throw new Exception("ORMAWA tidak ditemukan.");
    }

    // --- PING ---
    if ($action === 'ping') {
        jsonResponse(true, 'OK');
    }

    // --- GET BANK LOKASI (SuperAdmin & Admin) ---
    if ($action === 'get_bank') {
        // ✅ Gunakan $ormawa_id yang sudah ditentukan (bukan session langsung)
        $stmt = mysqli_prepare($koneksi, "SELECT id, nama_lokasi, lat, lng, radius_default FROM lokasi_absen WHERE ormawa_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $ormawa_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        
        $locations = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $locations[] = $row;
        }
        mysqli_stmt_close($stmt);
        
        jsonResponse(true, 'OK', ['locations' => $locations]);
    }

    // --- BUAT SESI ---
    if ($action === 'buat') {
        $judul = trim($_POST['judul_rapat'] ?? '');
        $mulai = $_POST['waktu_mulai'] ?? '';
        $selesai = $_POST['waktu_selesai'] ?? '';

        if (empty($judul)) throw new Exception("Judul wajib diisi.");
        if (empty($mulai) || empty($selesai)) throw new Exception("Waktu mulai & selesai wajib diisi.");

        $dt_mulai = DateTime::createFromFormat('Y-m-d\TH:i', $mulai);
        $dt_selesai = DateTime::createFromFormat('Y-m-d\TH:i', $selesai);
        if (!$dt_mulai || !$dt_selesai) {
            throw new Exception("Format waktu tidak valid. Format: YYYY-MM-DDTHH:MM");
        }
        if ($dt_mulai >= $dt_selesai) {
            throw new Exception("Waktu selesai harus lebih besar dari waktu mulai.");
        }

        $waktu_mulai = $dt_mulai->format('Y-m-d H:i:00');
        $waktu_selesai = $dt_selesai->format('Y-m-d H:i:00');

        $kode_unik = strtoupper(substr(md5(uniqid() . time()), 0, 6));

        $id_lokasi_absen = null;
        if (isset($_POST['use_location']) && !empty($_POST['id_lokasi_absen'])) {
            $id_lokasi_absen = (int)$_POST['id_lokasi_absen'];

            // ✅ Validasi: lokasi harus milik ORMawa target ($ormawa_id)
            $stmt = mysqli_prepare($koneksi, "SELECT id FROM lokasi_absen WHERE id = ? AND ormawa_id = ?");
            mysqli_stmt_bind_param($stmt, "ii", $id_lokasi_absen, $ormawa_id);
            mysqli_stmt_execute($stmt);
            $lokasi_valid = mysqli_stmt_get_result($stmt)->fetch_assoc();
            mysqli_stmt_close($stmt);

            if (!$lokasi_valid) {
                throw new Exception("Lokasi tidak ditemukan atau bukan milik ORMawa yang dipilih.");
            }
        }

        // ✅ Insert kehadiran dengan ormawa_id yang valid
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO kehadiran 
             (ormawa_id, judul_rapat, waktu_mulai, waktu_selesai, kode_unik, status, id_lokasi_absen) 
             VALUES (?, ?, ?, ?, ?, 'aktif', ?)"
        );

        if (!$stmt) throw new Exception("Query gagal: " . mysqli_error($koneksi));

        mysqli_stmt_bind_param(
            $stmt,
            "issssi",
            $ormawa_id,             // ✅ gunakan $ormawa_id yang sudah divalidasi
            $judul,
            $waktu_mulai,
            $waktu_selesai,
            $kode_unik,
            $id_lokasi_absen
        );

        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, 'Sesi absensi berhasil dibuat!');
        } else {
            throw new Exception("Gagal menyimpan sesi: " . mysqli_stmt_error($stmt));
        }
    }

    // --- SELESAI SESI ---
    if ($action === 'selesai') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) throw new Exception("ID tidak valid.");

        // ✅ Pastikan sesi milik ORMawa ini
        $stmt = mysqli_prepare($koneksi, "UPDATE kehadiran SET status = 'selesai' WHERE id = ? AND ormawa_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $ormawa_id);
        
        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
            jsonResponse(true, 'Sesi berhasil diakhiri.');
        } else {
            throw new Exception("Sesi tidak ditemukan atau bukan milik ORMawa ini.");
        }
    }

    // --- GET PESERTA ---
    if ($action === 'get_peserta') {
        $id = (int)($_GET['kehadiran_id'] ?? 0);
        if ($id <= 0) throw new Exception("ID sesi tidak valid.");

        // ✅ Pastikan kehadiran milik ORMawa ini
        $check = mysqli_prepare($koneksi, "SELECT id FROM kehadiran WHERE id = ? AND ormawa_id = ?");
        mysqli_stmt_bind_param($check, "ii", $id, $ormawa_id);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_num_rows(mysqli_stmt_get_result($check)) == 0) {
            throw new Exception("Sesi tidak ditemukan atau bukan milik ORMAWA ini.");
        }
        mysqli_stmt_close($check);

        $query = "SELECT u.full_name, al.waktu_absen, al.tipe_absen 
                  FROM absensi_log al
                  JOIN user u ON al.user_id = u.id
                  WHERE al.kehadiran_id = ?
                  ORDER BY al.waktu_absen DESC";
        
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        
        $peserta = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $peserta[] = $row;
        }
        
        jsonResponse(true, 'OK', ['peserta' => $peserta]);
    }

    throw new Exception("Aksi tidak dikenali.");
    
} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(200);
    jsonResponse(false, $e->getMessage());
}
?>