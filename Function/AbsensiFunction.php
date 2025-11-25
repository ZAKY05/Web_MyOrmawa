<?php
// Matikan output error ke layar agar tidak merusak JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Buffer output untuk menangkap echo yang tidak disengaja
ob_start();

header('Content-Type: application/json; charset=utf-8');

// Mulai session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // 1. Definisi Path Koneksi
    $config_path = __DIR__ . '/../Config/ConnectDB.php';

    if (!file_exists($config_path)) {
        throw new Exception("File koneksi tidak ditemukan di: $config_path");
    }

    $koneksi = include($config_path);

    // Validasi koneksi
    if (!$koneksi || !($koneksi instanceof mysqli)) {
        global $conn;
        if ($conn && $conn instanceof mysqli) {
            $koneksi = $conn;
        } else {
            throw new Exception("Gagal koneksi database.");
        }
    }

    // 2. Cek Login
    $user_id = $_SESSION['user_id'] ?? null;
    $user_level = $_SESSION['user_level'] ?? 0;
    $ormawa_id = $_SESSION['ormawa_id'] ?? 0;

    // Asumsi level 2 adalah admin ormawa
    if (!$user_id || $user_level != 2) { 
        throw new Exception("Akses ditolak. Login admin diperlukan.");
    }

    // 3. Ambil Action
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    // --- HANDLER: PING ---
    if ($action === 'ping') {
        echo json_encode(['success' => true, 'message' => 'Server Ready']);
        exit;
    }

    // --- HANDLER: BUAT SESI ---
    if ($action === 'buat') {
        $judul = $_POST['judul_rapat'] ?? '';
        $tgl_mulai = $_POST['tanggal_mulai'] ?? '';
        $jam_mulai = $_POST['jam_mulai'] ?? '';
        $tgl_selesai = $_POST['tanggal_selesai'] ?? '';
        $jam_selesai = $_POST['jam_selesai'] ?? '';

        if (empty($judul) || empty($tgl_mulai)) {
            throw new Exception("Judul dan Tanggal wajib diisi.");
        }

        $waktu_mulai = "$tgl_mulai $jam_mulai:00";
        $waktu_selesai = "$tgl_selesai $jam_selesai:00";
        
        // Generate Kode Unik
        $kode_unik = strtoupper(substr(md5(time() . uniqid()), 0, 6));
        
        // Data Lokasi (Sesuai kolom Database kamu: lokasi_nama, lat, lng, radius)
        // Jika user tidak mencentang lokasi, kita set NULL
        $use_loc = isset($_POST['use_location']); // Boolean
        
        $loc_name = $use_loc ? ($_POST['lokasi_nama'] ?? '') : null;
        $lat      = $use_loc ? ($_POST['lat'] ?? null) : null;
        $lng      = $use_loc ? ($_POST['lng'] ?? null) : null;
        $radius   = $use_loc ? ($_POST['radius'] ?? 100) : null; // Default 100 meter jika null

        // Query INSERT disesuaikan dengan kolom database kamu
        $query = "INSERT INTO kehadiran 
                  (ormawa_id, judul_rapat, waktu_mulai, waktu_selesai, kode_unik, status, lokasi_nama, lat, lng, radius) 
                  VALUES (?, ?, ?, ?, ?, 'aktif', ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($koneksi, $query);
        if (!$stmt) throw new Exception("Query Error: " . mysqli_error($koneksi));
        
        // Tipe data bind: i=int, s=string, d=double
        // urutan: ormawa_id(i), judul(s), mulai(s), selesai(s), kode(s), lokasi_nama(s), lat(d), lng(d), radius(i)
        mysqli_stmt_bind_param($stmt, "isssssddi", $ormawa_id, $judul, $waktu_mulai, $waktu_selesai, $kode_unik, $loc_name, $lat, $lng, $radius);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Sesi Absensi Berhasil Dibuat!']);
        } else {
            throw new Exception("Gagal menyimpan: " . mysqli_stmt_error($stmt));
        }
        exit;
    }

    // --- HANDLER: SELESAI SESI ---
    if ($action === 'selesai') {
        $id = $_GET['id'] ?? 0;
        $stmt = mysqli_prepare($koneksi, "UPDATE kehadiran SET status='selesai' WHERE id=? AND ormawa_id=?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $ormawa_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Sesi telah diakhiri.']);
        } else {
            throw new Exception("Gagal update database.");
        }
        exit;
    }

    // --- HANDLER: GET PESERTA ---
    if ($action === 'get_peserta') {
        $id = $_GET['kehadiran_id'] ?? 0;
        // Pastikan tabel absensi_log ada dan relasinya benar
        $query = "SELECT a.nama, al.waktu_absen, al.tipe_absen 
                  FROM absensi_log al 
                  JOIN anggota a ON al.anggota_id = a.id 
                  WHERE al.kehadiran_id = ? ORDER BY al.waktu_absen DESC";
        
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        
        $data = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $data[] = $row;
        }
        
        echo json_encode(['success' => true, 'peserta' => $data]);
        exit;
    }

    throw new Exception("Action tidak dikenali.");

} catch (Throwable $e) {
    ob_end_clean(); 
    http_response_code(200); 
    echo json_encode([
        'success' => false,
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
    exit;
}
?>