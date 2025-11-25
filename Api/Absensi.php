<?php
// 1. Atur Timezone & Header
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle Preflight Request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 2. Koneksi Database yang Aman (Sama seperti Function sebelumnya)
$path_koneksi = __DIR__ . '/../Config/ConnectDB.php'; // Sesuaikan jika file ini ada di folder 'Api'
if (!file_exists($path_koneksi)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'File koneksi tidak ditemukan']);
    exit;
}
$koneksi = include($path_koneksi);

// Helper Response
function respond($success, $data = [], $message = '') {
    http_response_code($success ? 200 : 400);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Parsing Input
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $_GET['action'] ?? 'default';

switch ($action) {

    // 🔍 1. Validasi QR (Scan QR di HP)
    case 'validate_qr':
        $qrData = $input['qr_data'] ?? '';
        if (!$qrData) respond(false, [], 'QR data kosong');

        $data = json_decode($qrData, true);
        // Validasi format JSON QR
        if (!$data || !isset($data['kode']) || ($data['type'] ?? '') !== 'ABSENSI_ORMAWA') {
            respond(false, [], 'QR Code tidak valid untuk aplikasi ini');
        }

        // ✅ PERBAIKAN: Sesuaikan nama kolom dengan database kamu (lat, lng, radius)
        $stmt = $koneksi->prepare("
            SELECT id, judul_rapat, lat, lng, radius, status 
            FROM kehadiran 
            WHERE kode_unik = ? AND status = 'aktif'
        ");
        $stmt->bind_param("s", $data['kode']);
        $stmt->execute();
        $sesi = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$sesi) {
            respond(false, [], 'Sesi tidak ditemukan atau sudah ditutup');
        }

        // Logic: Jika lat/lng di database tidak null, maka lokasi dibutuhkan
        $is_location_required = (!empty($sesi['lat']) && !empty($sesi['lng']));

        respond(true, [
            'sesi_id' => $sesi['id'],
            'judul'   => $sesi['judul_rapat'],
            'lokasi_dibutuhkan' => $is_location_required,
            'target_lat' => $sesi['lat'],     // Kirim ke HP untuk hitung jarak
            'target_lng' => $sesi['lng'],
            'radius'     => (int)$sesi['radius']
        ]);
        break;

    // 📝 2. Proses Absen
    case 'absen':
        $sesi_id = (int)($input['sesi_id'] ?? 0);
        $nim = trim($input['nim'] ?? '');
        $nama = trim($input['nama'] ?? '');
        $tipe = $input['tipe_absen'] ?? 'hadir'; 
        
        // Ambil koordinat dari HP user
        $user_lat = $input['lat'] ?? null; // Dari HP
        $user_lng = $input['lng'] ?? null; // Dari HP

        if (!$sesi_id || !$nim || !$nama) {
            respond(false, [], 'Data tidak lengkap (NIM/Nama wajib)');
        }

        // Cek Sesi Aktif
        $stmt = $koneksi->prepare("SELECT id FROM kehadiran WHERE id = ? AND status = 'aktif'");
        $stmt->bind_param("i", $sesi_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            respond(false, [], 'Sesi absensi sudah ditutup');
        }
        $stmt->close();

        // Cek / Buat Anggota
        $stmt = $koneksi->prepare("SELECT id FROM anggota WHERE nim = ?");
        $stmt->bind_param("s", $nim);
        $stmt->execute();
        $anggota = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $anggota_id = $anggota['id'] ?? null;

        if (!$anggota_id) {
            // Auto-register anggota baru
            $stmt = $koneksi->prepare("INSERT INTO anggota (nim, nama, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $nim, $nama);
            if ($stmt->execute()) {
                $anggota_id = $stmt->insert_id;
            } else {
                respond(false, [], 'Gagal mendaftarkan anggota');
            }
            $stmt->close();
        }

        // Cek Duplikat Absen
        $stmt = $koneksi->prepare("SELECT id FROM absensi_log WHERE anggota_id = ? AND kehadiran_id = ?");
        $stmt->bind_param("ii", $anggota_id, $sesi_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            respond(false, [], 'Kamu sudah absen sebelumnya.');
        }
        $stmt->close();

        // ✅ PERBAIKAN: Insert ke absensi_log menggunakan lat/lng
        // Pastikan tabel absensi_log punya kolom lat & lng juga!
        $sql_log = "INSERT INTO absensi_log (kehadiran_id, anggota_id, tipe_absen, waktu_absen, lat, lng) VALUES (?, ?, ?, NOW(), ?, ?)";
        
        $stmt = $koneksi->prepare($sql_log);
        
        // Pastikan data float valid
        $save_lat = $user_lat ? (float)$user_lat : null;
        $save_lng = $user_lng ? (float)$user_lng : null;

        $stmt->bind_param("iisdd", $sesi_id, $anggota_id, $tipe, $save_lat, $save_lng);

        if ($stmt->execute()) {
            respond(true, [], 'Absen Berhasil! ✅');
        } else {
            respond(false, [], 'Database Error: ' . $stmt->error);
        }
        break;

    // 📋 3. Get Data Peserta
    case 'get_peserta':
        $id = (int)($input['kehadiran_id'] ?? $_GET['kehadiran_id'] ?? 0);
        
        // ✅ PERBAIKAN: Select lat/lng
        $sql = "SELECT a.nama, a.nim, 
                       DATE_FORMAT(al.waktu_absen, '%d %b %H:%i') as waktu,
                       al.tipe_absen, al.lat, al.lng
                FROM absensi_log al
                JOIN anggota a ON al.anggota_id = a.id
                WHERE al.kehadiran_id = ?
                ORDER BY al.waktu_absen DESC";
        
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        respond(true, $data);
        break;

    default:
        respond(false, [], 'Action tidak dikenal');
}
?>