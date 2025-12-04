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
// GET - Ambil semua competition (upcoming only)
// ========================================
function handleGet() {
    global $koneksi;
    
    // GET COMPETITION BY ID
    if(isset($_GET['id'])) {
        $id = mysqli_real_escape_string($koneksi, $_GET['id']);
        $query = "SELECT k.*, o.nama_ormawa 
                  FROM kompetisi k 
                  JOIN ormawa o ON k.id_ormawa = o.id 
                  WHERE k.id = '$id'";
        
        $result = mysqli_query($koneksi, $query);
        
        if(!$result) {
            response(500, false, 'Query failed: ' . mysqli_error($koneksi));
            exit;
        }
        
        if(mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            $data = formatCompetitionData($data);
            response(200, true, 'Competition retrieved successfully', $data);
        } else {
            response(404, false, 'Competition not found');
        }
    }

    // GET COMPETITION BY ORMAWA ID
    if(isset($_GET['ormawa_id'])) {
        $ormawa_id = mysqli_real_escape_string($koneksi, $_GET['ormawa_id']);

        $query = "SELECT k.*, o.nama_ormawa 
                  FROM kompetisi k 
                  JOIN ormawa o ON k.id_ormawa = o.id 
                  WHERE k.id_ormawa = '$ormawa_id'
                  AND k.tgl_selesai >= CURDATE()
                  ORDER BY k.tgl_mulai ASC";

        $result = mysqli_query($koneksi, $query);

        if(!$result) {
            response(500, false, 'Query failed: ' . mysqli_error($koneksi));
        }

        $competitions = array();
        while($row = mysqli_fetch_assoc($result)) {
            $competitions[] = formatCompetitionData($row);
        }

        response(200, true, 'Competitions by ORMAWA retrieved successfully', $competitions);
    }

    // GET ALL UPCOMING COMPETITIONS
    $query = "SELECT k.*, o.nama_ormawa 
              FROM kompetisi k 
              JOIN ormawa o ON k.id_ormawa = o.id 
              WHERE k.tgl_selesai >= CURDATE() 
              ORDER BY k.tgl_mulai ASC";
    
    $result = mysqli_query($koneksi, $query);
    
    if(!$result) {
        response(500, false, 'Query failed: ' . mysqli_error($koneksi));
        exit;
    }
    
    $competitions = array();
    while($row = mysqli_fetch_assoc($result)) {
        $competitions[] = formatCompetitionData($row);
    }
    
    response(200, true, 'Competitions retrieved successfully', $competitions);
}

// ========================================
// POST - Tambah competition baru
// ========================================
function handlePost($input) {
    global $koneksi;
    
    // Validasi input
    $required = ['id_ormawa', 'nama_kompetisi', 'penyelenggara', 'deskripsi', 'tgl_mulai', 'tgl_selesai'];
    foreach($required as $field) {
        if(!isset($input[$field]) || empty($input[$field])) {
            response(400, false, "Field '$field' is required");
        }
    }
    
    $id_ormawa = mysqli_real_escape_string($koneksi, $input['id_ormawa']);
    $nama_kompetisi = mysqli_real_escape_string($koneksi, $input['nama_kompetisi']);
    $penyelenggara = mysqli_real_escape_string($koneksi, $input['penyelenggara']);
    $deskripsi = mysqli_real_escape_string($koneksi, $input['deskripsi']);
    $tgl_mulai = mysqli_real_escape_string($koneksi, $input['tgl_mulai']);
    $tgl_selesai = mysqli_real_escape_string($koneksi, $input['tgl_selesai']);
    
    $query = "INSERT INTO kompetisi (id_ormawa, nama_kompetisi, penyelenggara, deskripsi, tgl_mulai, tgl_selesai) 
              VALUES ('$id_ormawa', '$nama_kompetisi', '$penyelenggara', '$deskripsi', '$tgl_mulai', '$tgl_selesai')";
    
    if(mysqli_query($koneksi, $query)) {
        $newId = mysqli_insert_id($koneksi);
        response(200, true, 'Competition created successfully', array('id' => $newId));
    } else {
        response(500, false, 'Failed to create competition: ' . mysqli_error($koneksi));
    }
}

// ========================================
// PUT - Update competition
// ========================================
function handlePut($input) {
    global $koneksi;
    
    if(!isset($input['id'])) {
        response(400, false, 'Competition ID is required');
    }
    
    $id = mysqli_real_escape_string($koneksi, $input['id']);
    
    // Check if competition exists
    $checkQuery = "SELECT id FROM kompetisi WHERE id = '$id'";
    $checkResult = mysqli_query($koneksi, $checkQuery);
    
    if(mysqli_num_rows($checkResult) == 0) {
        response(404, false, 'Competition not found');
    }
    
    // Build update query dynamically
    $updates = array();
    $allowed_fields = ['nama_kompetisi', 'penyelenggara', 'deskripsi', 'tgl_mulai', 'tgl_selesai'];
    
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
    $query = "UPDATE kompetisi SET $updateString WHERE id = '$id'";
    
    if(mysqli_query($koneksi, $query)) {
        response(200, true, 'Competition updated successfully');
    } else {
        response(500, false, 'Failed to update competition: ' . mysqli_error($koneksi));
    }
}

// ========================================
// DELETE - Hapus competition
// ========================================
function handleDelete($input) {
    global $koneksi;
    
    if(!isset($input['id'])) {
        response(400, false, 'Competition ID is required');
    }
    
    $id = mysqli_real_escape_string($koneksi, $input['id']);
    
    // Get competition data before deleting
    $query = "SELECT gambar, file_panduan FROM kompetisi WHERE id = '$id'";
    $result = mysqli_query($koneksi, $query);
    
    if(!$result) {
        response(500, false, 'Query failed: ' . mysqli_error($koneksi));
        exit;
    }
    
    if(mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $gambar = $data['gambar'];
        $file_panduan = $data['file_panduan'];
        
        // Delete from database
        $deleteQuery = "DELETE FROM kompetisi WHERE id = '$id'";
        if(mysqli_query($koneksi, $deleteQuery)) {
            // Delete gambar file if exists
            if(!empty($gambar)) {
                $gambarPath = '../Uploads/kompetisi/' . $gambar;
                if(file_exists($gambarPath)) {
                    unlink($gambarPath);
                }
            }
            
            // Delete file panduan if exists
            if(!empty($file_panduan)) {
                $panduanPath = '../Uploads/kompetisi_panduan/' . $file_panduan;
                if(file_exists($panduanPath)) {
                    unlink($panduanPath);
                }
            }
            
            response(200, true, 'Competition deleted successfully');
        } else {
            response(500, false, 'Failed to delete competition: ' . mysqli_error($koneksi));
        }
    } else {
        response(404, false, 'Competition not found');
    }
}

// ========================================
// Helper: Format competition data dengan URL lengkap
// ========================================
function formatCompetitionData($row) {
    // Format tanggal Indonesia
    setlocale(LC_TIME, 'id_ID.UTF-8', 'Indonesian');
    $tgl_mulai = date('d M Y', strtotime($row['tgl_mulai']));
    $tgl_selesai = date('d M Y', strtotime($row['tgl_selesai']));
    
    // Build full URL
    $baseUrl = getBaseUrl();
    
    // Fix URL
    $posterUrl = '';
    if (!empty($row['gambar'])) {
        $posterUrl = $baseUrl . '/Uploads/kompetisi/' . $row['gambar'];
        $posterUrl = str_replace('\\', '/', $posterUrl);
        $posterUrl = preg_replace('#/+#', '/', $posterUrl);
        $posterUrl = str_replace(':/', '://', $posterUrl);
    }
    
    $guideBookUrl = '';
    if (!empty($row['file_panduan'])) {
        $guideBookUrl = $baseUrl . '/Uploads/kompetisi_panduan/' . $row['file_panduan'];
        $guideBookUrl = str_replace('\\', '/', $guideBookUrl);
        $guideBookUrl = preg_replace('#/+#', '/', $guideBookUrl);
        $guideBookUrl = str_replace(':/', '://', $guideBookUrl);
    }
    
    return array(
    'id' => $row['id'],
    'title' => $row['nama_kompetisi'],
    'organizer' => $row['penyelenggara'],
    'description' => $row['deskripsi'],
    'registrationPeriod' => $tgl_mulai . ($tgl_mulai != $tgl_selesai ? " - " . $tgl_selesai : ""),
    'tgl_mulai' => $row['tgl_mulai'],
    'tgl_selesai' => $row['tgl_selesai'],
    'posterUrl' => $posterUrl,
    'guideBookUrl' => $guideBookUrl,
    'guideBookFilename' => !empty($row['file_panduan']) ? $row['file_panduan'] : '',
    
    // ❗ INI YG KURANG
    'created_at' => $row['created_at']
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
// Helper: Get Base URL
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