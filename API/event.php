<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../Config/ConnectDB.php';

// Cek koneksi database
if (!$koneksi) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ));
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch($method) {
        case 'GET':
            handleGet();
            break;
        case 'POST':
            handlePost($input);
            break;
        case 'PUT':
            handlePut($input);
            break;
        case 'DELETE':
            handleDelete($input);
            break;
        default:
            response(405, false, 'Method not allowed');
            break;
    }
} catch (Exception $e) {
    response(500, false, 'Server error: ' . $e->getMessage());
}

// ========================================
// GET - Ambil semua event (upcoming only)
// ========================================
function handleGet() {
    global $koneksi;
    
    // ===========================
    // GET EVENT BY ID
    // ===========================
    if(isset($_GET['id'])) {
        $id = mysqli_real_escape_string($koneksi, $_GET['id']);
        $query = "SELECT e.*, o.nama_ormawa 
                  FROM event e 
                  JOIN ormawa o ON e.ormawa_id = o.id 
                  WHERE e.id = '$id'";
        
        $result = mysqli_query($koneksi, $query);
        
        if(!$result) {
            response(500, false, 'Query failed: ' . mysqli_error($koneksi));
            exit;
        }
        
        if(mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            $data = formatEventData($data);
            response(200, true, 'Event retrieved successfully', $data);
        } else {
            response(404, false, 'Event not found');
        }
    }

    // ===========================================
    // NEW FEATURE: GET EVENT BY ORMAWA ID
    // ===========================================
    if(isset($_GET['ormawa_id'])) {
        $ormawa_id = mysqli_real_escape_string($koneksi, $_GET['ormawa_id']);

        $query = "SELECT e.*, o.nama_ormawa 
                  FROM event e 
                  JOIN ormawa o ON e.ormawa_id = o.id 
                  WHERE e.ormawa_id = '$ormawa_id'
                  AND e.tgl_selesai >= CURDATE()
                  ORDER BY e.tgl_mulai ASC";

        $result = mysqli_query($koneksi, $query);

        if(!$result) {
            response(500, false, 'Query failed: ' . mysqli_error($koneksi));
        }

        $events = array();
        while($row = mysqli_fetch_assoc($result)) {
            $events[] = formatEventData($row);
        }

        response(200, true, 'Events by ORMAWA retrieved successfully', $events);
    }

    // ===========================================
    // GET ALL UPCOMING EVENTS
    // ===========================================
    $query = "SELECT e.*, o.nama_ormawa 
              FROM event e 
              JOIN ormawa o ON e.ormawa_id = o.id 
              WHERE e.tgl_selesai >= CURDATE() 
              ORDER BY e.tgl_mulai ASC";
    
    $result = mysqli_query($koneksi, $query);
    
    if(!$result) {
        response(500, false, 'Query failed: ' . mysqli_error($koneksi));
        exit;
    }
    
    $events = array();
    while($row = mysqli_fetch_assoc($result)) {
        $events[] = formatEventData($row);
    }
    
    response(200, true, 'Events retrieved successfully', $events);
}

// ========================================
// POST - Tambah event baru (untuk testing)
// ========================================
function handlePost($input) {
    global $koneksi;
    
    // Validasi input
    $required = ['ormawa_id', 'nama_event', 'kategori', 'deskripsi', 'tgl_mulai', 'tgl_selesai', 'waktu_mulai', 'waktu_selesai', 'lokasi'];
    foreach($required as $field) {
        if(!isset($input[$field]) || empty($input[$field])) {
            response(400, false, "Field '$field' is required");
        }
    }
    
    $ormawa_id = mysqli_real_escape_string($koneksi, $input['ormawa_id']);
    $nama_event = mysqli_real_escape_string($koneksi, $input['nama_event']);
    $kategori = mysqli_real_escape_string($koneksi, $input['kategori']);
    $deskripsi = mysqli_real_escape_string($koneksi, $input['deskripsi']);
    $tgl_mulai = mysqli_real_escape_string($koneksi, $input['tgl_mulai']);
    $tgl_selesai = mysqli_real_escape_string($koneksi, $input['tgl_selesai']);
    $waktu_mulai = mysqli_real_escape_string($koneksi, $input['waktu_mulai']);
    $waktu_selesai = mysqli_real_escape_string($koneksi, $input['waktu_selesai']);
    $lokasi = mysqli_real_escape_string($koneksi, $input['lokasi']);
    
    $query = "INSERT INTO event (ormawa_id, nama_event, kategori, deskripsi, tgl_mulai, tgl_selesai, waktu_mulai, waktu_selesai, lokasi) 
              VALUES ('$ormawa_id', '$nama_event', '$kategori', '$deskripsi', '$tgl_mulai', '$tgl_selesai', '$waktu_mulai', '$waktu_selesai', '$lokasi')";
    
    if(mysqli_query($koneksi, $query)) {
        $newId = mysqli_insert_id($koneksi);
        response(200, true, 'Event created successfully', array('id' => $newId));
    } else {
        response(500, false, 'Failed to create event: ' . mysqli_error($koneksi));
    }
}

// ========================================
// PUT - Update event
// ========================================
function handlePut($input) {
    global $koneksi;
    
    if(!isset($input['id'])) {
        response(400, false, 'Event ID is required');
    }
    
    $id = mysqli_real_escape_string($koneksi, $input['id']);
    
    // Check if event exists
    $checkQuery = "SELECT id FROM event WHERE id = '$id'";
    $checkResult = mysqli_query($koneksi, $checkQuery);
    
    if(mysqli_num_rows($checkResult) == 0) {
        response(404, false, 'Event not found');
    }
    
    // Build update query dynamically
    $updates = array();
    $allowed_fields = ['nama_event', 'kategori', 'deskripsi', 'tgl_mulai', 'tgl_selesai', 'waktu_mulai', 'waktu_selesai', 'lokasi'];
    
    foreach($allowed_fields as $field) {
        if(isset($input[$field])) {
            $value = mysqli_real_escape_string($koneksi, $input[$field]);
            $updates[] = "$field = '$value'";
        }
    }
    
    if(empty($updates)) {
        response(400, false, 'No fields to update');
    }
    
    $updateString = implode(', ', $updates);
    $query = "UPDATE event SET $updateString WHERE id = '$id'";
    
    if(mysqli_query($koneksi, $query)) {
        response(200, true, 'Event updated successfully');
    } else {
        response(500, false, 'Failed to update event: ' . mysqli_error($koneksi));
    }
}

// ========================================
// DELETE - Hapus event
// ========================================
function handleDelete($input) {
    global $koneksi;
    
    if(!isset($input['id'])) {
        response(400, false, 'Event ID is required');
    }
    
    $id = mysqli_real_escape_string($koneksi, $input['id']);
    
    // Get event data before deleting (untuk hapus file gambar & pdf)
    $query = "SELECT gambar, buku_panduan FROM event WHERE id = '$id'";
    $result = mysqli_query($koneksi, $query);
    
    if(!$result) {
        response(500, false, 'Query failed: ' . mysqli_error($koneksi));
        exit;
    }
    
    if(mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $gambar = $data['gambar'];
        $buku_panduan = $data['buku_panduan'];
        
        // Delete from database
        $deleteQuery = "DELETE FROM event WHERE id = '$id'";
        if(mysqli_query($koneksi, $deleteQuery)) {
            // Delete gambar file if exists
            if(!empty($gambar)) {
                $gambarPath = '../Uploads/event/' . $gambar;
                if(file_exists($gambarPath)) {
                    unlink($gambarPath);
                }
            }
            
            // Delete buku panduan file if exists
            if(!empty($buku_panduan)) {
                $bukuPath = '../Uploads/event_panduan/' . $buku_panduan;
                if(file_exists($bukuPath)) {
                    unlink($bukuPath);
                }
            }
            
            response(200, true, 'Event deleted successfully');
        } else {
            response(500, false, 'Failed to delete event: ' . mysqli_error($koneksi));
        }
    } else {
        response(404, false, 'Event not found');
    }
}

// ========================================
// Helper: Format event data dengan URL lengkap
// ========================================
function formatEventData($row) {
    // Format tanggal Indonesia
    setlocale(LC_TIME, 'id_ID.UTF-8', 'Indonesian');
    $tgl_mulai = date('d M Y', strtotime($row['tgl_mulai']));
    $tgl_selesai = date('d M Y', strtotime($row['tgl_selesai']));
    
    // Build full URL dengan forward slash
    $baseUrl = getBaseUrl();
    
    // FIX: Ganti backslash dengan forward slash dan pastikan tidak ada double slash
    $posterUrl = '';
    if (!empty($row['gambar'])) {
        $posterUrl = $baseUrl . '/Uploads/event/' . $row['gambar'];
        $posterUrl = str_replace('\\', '/', $posterUrl);
        $posterUrl = preg_replace('#/+#', '/', $posterUrl);
        $posterUrl = str_replace(':/', '://', $posterUrl);
    }
    
    $guideBookUrl = '';
    if (!empty($row['buku_panduan'])) {
        $guideBookUrl = $baseUrl . '/Uploads/event_panduan/' . $row['buku_panduan'];
        $guideBookUrl = str_replace('\\', '/', $guideBookUrl);
        $guideBookUrl = preg_replace('#/+#', '/', $guideBookUrl);
        $guideBookUrl = str_replace(':/', '://', $guideBookUrl);
    }
    
    return array(
        'id' => $row['id'],
        'title' => $row['nama_event'],
        'organizer' => $row['nama_ormawa'],
        'category' => $row['kategori'],
        'description' => $row['deskripsi'],
        'date' => $tgl_mulai . ($tgl_mulai != $tgl_selesai ? " - " . $tgl_selesai : ""),
        'tgl_mulai' => $row['tgl_mulai'],
        'tgl_selesai' => $row['tgl_selesai'],
        'waktu_mulai' => !empty($row['waktu_mulai']) ? substr($row['waktu_mulai'], 0, 5) . ' WIB' : '',
        'waktu_selesai' => !empty($row['waktu_selesai']) ? substr($row['waktu_selesai'], 0, 5) . ' WIB' : '',
        'location' => $row['lokasi'],
        'posterUrl' => $posterUrl,
        'guideBookUrl' => $guideBookUrl,
        'guideBookFilename' => !empty($row['buku_panduan']) ? $row['buku_panduan'] : ''
    );
}

// ========================================
// Helper: Response JSON
// ========================================
function response($status, $success, $message, $data = null) {
    http_response_code($status);
    $response = array(
        'success' => $success,
        'message' => $message
    );
    
    if($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

// ========================================
// Helper: Get Base URL (Support Ngrok & Local)
// ========================================
function getBaseUrl() {
    $originalHost = $_SERVER['HTTP_X_ORIGINAL_HOST'] ?? $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '';

    if (strpos($originalHost, 'ngrok-free.app') !== false || strpos($originalHost, 'ngrok.io') !== false) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        return $protocol . "://" . $originalHost;
    }

    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $baseDir = dirname(dirname($scriptName));
    $baseDir = rtrim($baseDir, '/');

    return $protocol . "://" . $originalHost . $baseDir;
}

mysqli_close($koneksi);
?>