<?php
// ✅ 1. Start session & pastikan output hanya JSON
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ 2. Matikan semua output HTML/error
ini_set('display_errors', 0);
error_reporting(E_ALL);

// ✅ 3. Include koneksi
include(__DIR__ . '/../Config/ConnectDB.php');

// ✅ 4. Cek akses
$user_id = $_SESSION['user_id'] ?? null;
$user_level = $_SESSION['user_level'] ?? 0;

if (!$user_id || $user_level !== 2) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Session tidak valid']);
    exit();
}

$action = $_GET['action'] ?? '';

// === GET PESERTA ===
if ($action === 'get_peserta') {
    $kehadiran_id = (int)($_GET['kehadiran_id'] ?? 0);
    if (!$kehadiran_id) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        exit();
    }

    $stmt = $koneksi->prepare("
        SELECT a.nama, 
               DATE_FORMAT(al.waktu_absen, '%d %b %Y %H:%i') as waktu_absen,
               al.tipe_absen
        FROM absensi_log al
        JOIN anggota a ON al.anggota_id = a.id
        WHERE al.kehadiran_id = ?
        ORDER BY al.waktu_absen ASC
    ");
    $stmt->bind_param("i", $kehadiran_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $peserta = [];
    while ($row = $result->fetch_assoc()) {
        $peserta[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'peserta' => $peserta]);
    exit();
}

// === GET COUNT ===
if ($action === 'get_count') {
    $kehadiran_id = (int)($_GET['kehadiran_id'] ?? 0);
    $stmt = $koneksi->prepare("SELECT COUNT(*) as c FROM absensi_log WHERE kehadiran_id = ?");
    $stmt->bind_param("i", $kehadiran_id);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();
    echo json_encode(['success' => true, 'count' => (int)$count]);
    exit();
}

// === SELESAI SESI ===
if ($action === 'selesai' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $koneksi->prepare("UPDATE kehadiran SET status = 'selesai' WHERE id = ? AND ormawa_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['ormawa_id']);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $success]);
    exit();
}

// === DEFAULT ERROR ===
echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenali']);
?>