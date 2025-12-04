<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection dari Config
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

function handleGet() {
    global $koneksi;
    
    // Check if requesting single organization
    if(isset($_GET['id'])) {
        $id = mysqli_real_escape_string($koneksi, $_GET['id']);
        $query = "SELECT * FROM ormawa WHERE id = '$id'";
        
        $result = mysqli_query($koneksi, $query);
        
        if(!$result) {
            response(500, false, 'Query failed: ' . mysqli_error($koneksi));
            exit;
        }
        
        if(mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            // Add full logo URL
            if(!empty($data['logo'])) {
                $data['logo_url'] = getBaseUrl() . '/uploads/ormawa/' . $data['logo'];
            } else {
                $data['logo_url'] = '';
            }
            response(200, true, 'Organization retrieved successfully', $data);
        } else {
            response(404, false, 'Organization not found');
        }
    } else {
        // Get all organizations
        $query = "SELECT * FROM ormawa ORDER BY created_at DESC";
        
        $result = mysqli_query($koneksi, $query);
        
        if(!$result) {
            response(500, false, 'Query failed: ' . mysqli_error($koneksi));
            exit;
        }
        
        $organizations = array();
        while($row = mysqli_fetch_assoc($result)) {
            // Add full logo URL
            if(!empty($row['logo'])) {
                $row['logo_url'] = getBaseUrl() . '/uploads/ormawa/' . $row['logo'];
            } else {
                $row['logo_url'] = '';
            }
            $organizations[] = $row;
        }
        
        response(200, true, 'Organizations retrieved successfully', $organizations);
    }
}

function handleDelete($input) {
    global $koneksi;
    
    if(!isset($input['id'])) {
        response(400, false, 'Organization ID is required');
    }
    
    $id = mysqli_real_escape_string($koneksi, $input['id']);
    
    // Get logo filename before deleting
    $query = "SELECT logo FROM ormawa WHERE id = '$id'";
    $result = mysqli_query($koneksi, $query);
    
    if(!$result) {
        response(500, false, 'Query failed: ' . mysqli_error($koneksi));
        exit;
    }
    
    if(mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $logo = $data['logo'];
        
        // Delete from database
        $deleteQuery = "DELETE FROM ormawa WHERE id = '$id'";
        if(mysqli_query($koneksi, $deleteQuery)) {
            // Delete logo file if exists
            if(!empty($logo)) {
                $logoPath = '../uploads/ormawa/' . $logo;
                if(file_exists($logoPath)) {
                    unlink($logoPath);
                }
            }
            response(200, true, 'Organization deleted successfully');
        } else {
            response(500, false, 'Failed to delete organization: ' . mysqli_error($koneksi));
        }
    } else {
        response(404, false, 'Organization not found');
    }
}

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

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $baseDir = dirname(dirname($_SERVER['SCRIPT_NAME']));
    return $protocol . "://" . $host . $baseDir;
}
?>