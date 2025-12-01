<?php
// File: /Function/ScanAbsensiAPI.php
// Endpoint: POST /Function/ScanAbsensiAPI.php

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *"); // Sesuaikan di production (misal: https://yourapp.com)
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Matikan error display untuk keamanan di production
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Helper: response JSON
function json_response($success, $message, $data = [])
{
    http_response_code($success ? 200 : 400);
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $data));
    exit;
}

// Koneksi DB
$config_path = __DIR__ . '/../Config/ConnectDB.php';
if (!file_exists($config_path)) {
    json_response(false, "Internal server error: koneksi DB tidak ditemukan.");
}
$koneksi = include $config_path;
if (!$koneksi) {
    json_response(false, "Gagal terhubung ke database.");
}

// Ambil input JSON (karena mobile app biasanya kirim JSON via POST body)
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    json_response(false, "Invalid JSON input.");
}

try {
    $kode_unik = trim($input['kode_unik'] ?? '');
    $anggota_id = (int)($input['anggota_id'] ?? 0); // Harus dikirim dari mobile app
    $device_id = trim($input['device_id'] ?? '');
    $lat = floatval($input['lat'] ?? 0);
    $lng = floatval($input['lng'] ?? 0);

    // Validasi dasar
    if (empty($kode_unik)) {
        json_response(false, "Kode unik sesi tidak ditemukan dalam QR code.");
    }
    if ($anggota_id <= 0) {
        json_response(false, "ID anggota tidak valid. Harap login di aplikasi.");
    }

    // === 1. Cari sesi berdasarkan kode_unik ===
    $stmt = mysqli_prepare($koneksi, 
        "SELECT k.id, k.status, k.waktu_mulai, k.waktu_selesai, k.id_lokasi_absen,
                l.lat AS lokasi_lat, l.lng AS lokasi_lng, l.radius_default
         FROM kehadiran k
         LEFT JOIN lokasi_absen l ON k.id_lokasi_absen = l.id
         WHERE k.kode_unik = ?"
    );
    mysqli_stmt_bind_param($stmt, "s", $kode_unik);
    mysqli_stmt_execute($stmt);
    $sesi = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);

    if (!$sesi) {
        json_response(false, "Sesi absensi tidak ditemukan. Pastikan QR code masih berlaku.");
    }

    // === 2. Validasi status & waktu ===
    $now = new DateTime();
    $mulai = new DateTime($sesi['waktu_mulai']);
    $selesai = new DateTime($sesi['waktu_selesai']);

    if ($sesi['status'] !== 'aktif') {
        json_response(false, "Sesi absensi telah ditutup.");
    }
    if ($now < $mulai) {
        json_response(false, "Sesi absensi belum dimulai.");
    }
    if ($now > $selesai) {
        json_response(false, "Sesi absensi telah berakhir.");
    }

    // === 3. Validasi lokasi (jika diterapkan) ===
    if ($sesi['id_lokasi_absen']) {
        $lokasi_lat = floatval($sesi['lokasi_lat']);
        $lokasi_lng = floatval($sesi['lokasi_lng']);
        $radius = intval($sesi['radius_default'] ?? 100); // default 100 meter

        // Cek koordinat valid
        if ($lat == 0 && $lng == 0) {
            json_response(false, "Lokasi tidak terdeteksi. Izinkan akses GPS dan coba lagi.");
        }
        if (!validateLatLng($lat, $lng)) {
            json_response(false, "Koordinat GPS tidak valid.");
        }

        // Hitung jarak (Haversine formula)
        $jarak = calculateDistance($lat, $lng, $lokasi_lat, $lokasi_lng);

        if ($jarak > $radius) {
            json_response(false, "Anda berada di luar radius absensi (" . round($jarak) . "m > " . $radius . "m).");
        }
    }

    // === 4. Cek duplikat absen (optional: bisa dihapus jika boleh absen >1x) ===
    $stmt = mysqli_prepare($koneksi, 
        "SELECT id FROM absensi_log WHERE kehadiran_id = ? AND anggota_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $sesi['id'], $anggota_id);
    mysqli_stmt_execute($stmt);
    $duplikat = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);

    if ($duplikat) {
        json_response(false, "Anda sudah melakukan absensi untuk sesi ini.");
    }

    // === 5. Simpan ke absensi_log ===
    $waktu_sekarang = $now->format('Y-m-d H:i:s');
    $tipe_absen = $sesi['id_lokasi_absen'] ? 'lokal' : 'online';

    $stmt = mysqli_prepare($koneksi, 
        "INSERT INTO absensi_log (kehadiran_id, anggota_id, waktu_absen, tipe_absen, device_id) 
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iiss", 
        $sesi['id'], 
        $anggota_id, 
        $waktu_sekarang, 
        $tipe_absen,
        $device_id
    );

    if (mysqli_stmt_execute($stmt)) {
        json_response(true, "Absensi berhasil!", [
            "waktu" => $waktu_sekarang,
            "tipe" => $tipe_absen,
            "sesi" => [
                "judul" => $input['judul'] ?? "Rapat Internal"
            ]
        ]);
    } else {
        throw new Exception("Gagal menyimpan absensi: " . mysqli_stmt_error($stmt));
    }

} catch (Exception $e) {
    error_log("ScanAbsensiAPI Error: " . $e->getMessage());
    json_response(false, "Terjadi kesalahan sistem. Silakan coba lagi nanti.");
}

// ─── Helper Functions ────────────────────────────────────────

function validateLatLng($lat, $lng) {
    return $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180;
}

// Haversine formula (jarak dalam meter)
function calculateDistance($lat1, $lng1, $lat2, $lng2) {
    $R = 6371000; // Earth radius in meters
    $phi1 = deg2rad($lat1);
    $phi2 = deg2rad($lat2);
    $deltaPhi = deg2rad($lat2 - $lat1);
    $deltaLambda = deg2rad($lng2 - $lng1);

    $a = sin($deltaPhi / 2) * sin($deltaPhi / 2) +
         cos($phi1) * cos($phi2) *
         sin($deltaLambda / 2) * sin($deltaLambda / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $R * $c;
}