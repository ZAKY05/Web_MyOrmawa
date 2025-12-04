<?php
/**
 * Attendance API - Member Side (Updated for new DB)
 * Handle: verify_qr, check_in, get_history
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function jsonResponse($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // meter
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return round($earthRadius * $c, 2);
}

try {
   // Jika file ConnectDB.php ada di folder Config satu level di atas
$config_path = '../Config/ConnectDB.php';
require_once $config_path;

// Sekarang $conn atau koneksi DB bisa dipakai
    if (!file_exists($config_path)) throw new Exception("File koneksi tidak ditemukan.");
    
    $koneksi = include($config_path);
    if (!$koneksi) throw new Exception("Koneksi database gagal.");

    // Get request data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $action = $data['action'] ?? $_GET['action'] ?? '';

    // ==============================
// VERIFY QR CODE
// ==============================
if ($action === 'verify_qr') {
    $user_id = $data['user_id'] ?? '';
    $qr_code = $data['qr_code'] ?? '';

    if (empty($user_id) || empty($qr_code)) throw new Exception("Data tidak lengkap");

    // Cari sesi kehadiran
    $stmt = mysqli_prepare($koneksi, 
        "SELECT k.id, k.judul_rapat, k.waktu_mulai, k.waktu_selesai, 
                k.status, k.id_lokasi_absen, k.ormawa_id,
                o.nama_ormawa,
                l.nama_lokasi, l.lat, l.lng, l.radius_default
         FROM kehadiran k
         JOIN ormawa o ON k.ormawa_id = o.id
         LEFT JOIN lokasi_absen l ON k.id_lokasi_absen = l.id
         WHERE k.kode_unik = ?"
    );
    mysqli_stmt_bind_param($stmt, "s", $qr_code);
    mysqli_stmt_execute($stmt);
    $kehadiran = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$kehadiran) jsonResponse(false, "QR Code tidak valid", ['is_valid' => false]);
    if ($kehadiran['status'] !== 'aktif') jsonResponse(false, "Sesi absensi sudah berakhir", ['is_valid' => false]);

    // Cari user dan ambil id_ormawa
    $stmt = mysqli_prepare($koneksi, "SELECT id, full_name, id_ormawa FROM user WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$user) jsonResponse(false, "User tidak ditemukan", ['is_valid' => false]);

    // Cek apakah user punya id_ormawa yang sesuai dengan QR
    if ((int)$kehadiran['ormawa_id'] !== (int)$user['id_ormawa']) {
        jsonResponse(false, "QR hanya berlaku untuk anggota ormawa ini", ['is_valid' => false]);
    }

    // Cek sudah check-in
    $stmt = mysqli_prepare($koneksi, "SELECT id FROM absensi_log WHERE kehadiran_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $kehadiran['id'], $user_id);
    mysqli_stmt_execute($stmt);
    $already_checked = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($already_checked) jsonResponse(false, "Anda sudah melakukan check-in", ['is_valid' => false]);

    // Response data
    $waktu_mulai = new DateTime($kehadiran['waktu_mulai']);
    $waktu_selesai = new DateTime($kehadiran['waktu_selesai']);
    $months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
    $month = $months[(int)$waktu_mulai->format('n')];

    $response_data = [
        'is_valid' => true,
        'event_id' => (string)$kehadiran['id'],
        'event_name' => $kehadiran['judul_rapat'],
        'event_date' => $waktu_mulai->format('d') . ' ' . $month . ' ' . $waktu_mulai->format('Y'),
        'event_time' => $waktu_mulai->format('H:i') . ' - ' . $waktu_selesai->format('H:i'),
        'location' => $kehadiran['nama_lokasi'] ?? 'Tidak ada batasan lokasi',
        'organization_name' => $kehadiran['nama_ormawa'],
        'status' => 'pending',
        'message' => 'QR Code valid. Silakan konfirmasi check-in.',
        'location_required' => !empty($kehadiran['id_lokasi_absen']),
        'location_data' => !empty($kehadiran['id_lokasi_absen']) ? [
            'lat' => (float)$kehadiran['lat'],
            'lng' => (float)$kehadiran['lng'],
            'radius' => (float)$kehadiran['radius_default']
        ] : null
    ];

    jsonResponse(true, "QR Code valid", $response_data);
}

// ==============================
// CHECK IN
// ==============================
if ($action === 'check_in') {
    $user_id = $data['user_id'] ?? '';
    $qr_code = $data['qr_code'] ?? '';
    $latitude = $data['latitude'] ?? null;
    $longitude = $data['longitude'] ?? null;

    if (empty($user_id) || empty($qr_code)) throw new Exception("Data tidak lengkap");

    $stmt = mysqli_prepare($koneksi, 
        "SELECT k.id, k.status, k.id_lokasi_absen, k.ormawa_id, k.waktu_mulai,
                l.lat, l.lng, l.radius_default
         FROM kehadiran k
         LEFT JOIN lokasi_absen l ON k.id_lokasi_absen = l.id
         WHERE k.kode_unik = ?"
    );
    mysqli_stmt_bind_param($stmt, "s", $qr_code);
    mysqli_stmt_execute($stmt);
    $kehadiran = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$kehadiran) throw new Exception("QR Code tidak valid");
    if ($kehadiran['status'] !== 'aktif') throw new Exception("Sesi absensi sudah berakhir");

    $stmt = mysqli_prepare($koneksi, "SELECT full_name, id_ormawa FROM user WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$user) throw new Exception("User tidak ditemukan");

    // Cek ormawa sama
    if ((int)$kehadiran['ormawa_id'] !== (int)$user['id_ormawa']) {
        throw new Exception("QR hanya berlaku untuk anggota ormawa ini");
    }

    // Cek double check-in
    $stmt = mysqli_prepare($koneksi, "SELECT id FROM absensi_log WHERE kehadiran_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $kehadiran['id'], $user_id);
    mysqli_stmt_execute($stmt);
    $already_checked = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($already_checked) throw new Exception("Anda sudah melakukan check-in sebelumnya");

    // Validasi lokasi
    $distance_from_location = null;
    if (!empty($kehadiran['id_lokasi_absen'])) {
        if (empty($latitude) || empty($longitude)) throw new Exception("Lokasi diperlukan untuk check-in");
        $distance_from_location = calculateDistance($kehadiran['lat'], $kehadiran['lng'], $latitude, $longitude);
        if ($distance_from_location > $kehadiran['radius_default']) {
            throw new Exception("Anda berada di luar jangkauan lokasi. Jarak: " . $distance_from_location . "m");
        }
    }

    $waktu_absen = date('Y-m-d H:i:s');
    $tipe_absen = (strtotime($waktu_absen) <= strtotime($kehadiran['waktu_mulai'])) ? 'hadir' : 'terlambat';

    $stmt = mysqli_prepare($koneksi, 
        "INSERT INTO absensi_log (kehadiran_id, user_id, waktu_absen, tipe_absen) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iiss", $kehadiran['id'], $user_id, $waktu_absen, $tipe_absen);
    if (!mysqli_stmt_execute($stmt)) throw new Exception("Gagal menyimpan absensi: " . mysqli_stmt_error($stmt));
    $absensi_id = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt);

    $response_data = [
        'id' => (string)$absensi_id,
        'check_in_time' => date('H:i:s', strtotime($waktu_absen)),
        'status' => 'checked_in',
        'tipe_absen' => $tipe_absen
    ];
    if ($distance_from_location !== null) $response_data['distance'] = $distance_from_location . ' meter';

    jsonResponse(true, "Check-in berhasil", $response_data);
}
    // ==============================
// GET HISTORY
// ==============================
if ($action === 'get_history') {
    $user_id = $_GET['user_id'] ?? '';
    if (empty($user_id)) throw new Exception("User ID tidak valid");

    // Cek user
    $stmt = mysqli_prepare($koneksi, "SELECT full_name FROM user WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$user) throw new Exception("User tidak ditemukan");

    // Ambil history absensi lengkap, termasuk sesi yang sudah selesai
    $stmt = mysqli_prepare($koneksi, 
        "SELECT k.id as event_id, k.judul_rapat as event_name, 
                k.waktu_mulai, k.waktu_selesai,
                o.nama_ormawa as organization_name,
                al.waktu_absen as check_in_time,
                al.tipe_absen
         FROM absensi_log al
         JOIN kehadiran k ON al.kehadiran_id = k.id
         JOIN ormawa o ON k.ormawa_id = o.id
         WHERE al.user_id = ?
         ORDER BY al.waktu_absen DESC
         LIMIT 50"
    );
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $history = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $history[] = [
            'event_id' => (string)$row['event_id'],
            'event_name' => $row['event_name'],
            'event_date' => date('d M Y', strtotime($row['waktu_mulai'])),
            'event_time' => date('H:i', strtotime($row['waktu_mulai'])) . ' - ' . date('H:i', strtotime($row['waktu_selesai'])),
            'organization_name' => $row['organization_name'],
            'check_in_time' => date('H:i', strtotime($row['check_in_time'])),
            'tipe_absen' => $row['tipe_absen']
        ];
    }
    mysqli_stmt_close($stmt);

    jsonResponse(true, "Data riwayat absensi berhasil diambil", $history);
}


    throw new Exception("Aksi tidak dikenali: " . $action);

} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(200);
    jsonResponse(false, $e->getMessage());
}
?>