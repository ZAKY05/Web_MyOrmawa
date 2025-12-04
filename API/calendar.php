<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../Config/ConnectDB.php';

if (!$koneksi) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ));
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    handleGetCalendarEvents();
} else {
    response(405, false, 'Method not allowed');
}

function handleGetCalendarEvents() {
    global $koneksi;
    
    // Query: ambil event upcoming (tgl_selesai >= today)
    $query = "SELECT e.id, e.nama_event, e.lokasi, e.tgl_mulai, e.tgl_selesai, 
                     e.waktu_mulai, e.waktu_selesai, o.nama_ormawa 
              FROM event e 
              JOIN ormawa o ON e.ormawa_id = o.id 
              WHERE e.tgl_selesai >= CURDATE() 
              ORDER BY e.tgl_mulai ASC";
    
    $result = mysqli_query($koneksi, $query);
    
    if (!$result) {
        response(500, false, 'Query failed: ' . mysqli_error($koneksi));
        exit;
    }
    
    $events = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = formatCalendarEvent($row);
    }
    
    response(200, true, 'Calendar events retrieved successfully', $events);
}

function formatCalendarEvent($row) {
    // Format waktu: 07:00 WIB
    $waktuMulai = !empty($row['waktu_mulai']) ? substr($row['waktu_mulai'], 0, 5) . ' WIB' : '-';
    $waktuSelesai = !empty($row['waktu_selesai']) ? substr($row['waktu_selesai'], 0, 5) . ' WIB' : '-';
    
    return array(
        'id' => $row['id'],
        'title' => $row['nama_event'],
        'organizer' => $row['nama_ormawa'],
        'location' => $row['lokasi'],
        'tgl_mulai' => $row['tgl_mulai'],
        'tgl_selesai' => $row['tgl_selesai'],
        'waktu_mulai' => $waktuMulai,
        'waktu_selesai' => $waktuSelesai,
        'time_display' => $waktuMulai . ' - ' . $waktuSelesai, // "07:00 WIB - 17:00 WIB"
        'color' => getRandomColor() // Random color untuk calendar indicator
    );
}

function getRandomColor() {
    $colors = ['#5800FF', '#2C4EEF', '#00D7FF', '#FF6B00', '#FF0080'];
    return $colors[array_rand($colors)];
}

function response($status, $success, $message, $data = null) {
    http_response_code($status);
    $response = array(
        'success' => $success,
        'message' => $message
    );
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

mysqli_close($koneksi);
?>