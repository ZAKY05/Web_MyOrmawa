<?php
// API/form_builder_api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include '../Config/ConnectDB.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/* =========================
   GET ALL FORMS
   ========================= */
if ($method === 'GET' && $action === 'get_forms') {
    $jenis_form = isset($_GET['jenis_form']) ? $_GET['jenis_form'] : 'anggota';
    
    $query = "SELECT fi.id, fi.judul, fi.deskripsi, fi.gambar, fi.status, 
                     fi.created_at, o.nama_ormawa, o.id as ormawa_id,
                     (SELECT COUNT(DISTINCT user_id) FROM submit s 
                      JOIN form f ON s.form_id = f.id 
                      WHERE f.form_info_id = fi.id) as total_submissions
              FROM form_info fi
              JOIN user u ON fi.user_id = u.id
              JOIN ormawa o ON u.id_ormawa = o.id
              WHERE fi.jenis_form = ? AND fi.status = 'published'
              ORDER BY fi.created_at DESC";
    
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("s", $jenis_form);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $forms = [];
    while ($row = $result->fetch_assoc()) {
        $row['gambar_url'] = $row['gambar'] 
            ? 'https://basiliscine-ricky-nebuly.ngrok-free.dev/uploads/form/' . $row['gambar'] 
            : null;

        $forms[] = $row;
    }
    
    sendResponse(true, 'Forms retrieved successfully', $forms);
}

/* =========================
   GET FORM DETAIL
   ========================= */
if ($method === 'GET' && $action === 'get_form_detail') {
    $form_id = isset($_GET['form_id']) ? (int)$_GET['form_id'] : 0;

    if ($form_id <= 0) {
        sendResponse(false, 'Invalid form ID', null);
    }

    $query = "SELECT fi.id, fi.judul, fi.deskripsi, fi.gambar, fi.status, 
                     fi.jenis_form, fi.created_at, o.nama_ormawa, o.id as ormawa_id
              FROM form_info fi
              JOIN user u ON fi.user_id = u.id
              JOIN ormawa o ON u.id_ormawa = o.id
              WHERE fi.id = ? AND fi.status = 'published'";
    
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $form_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $form_info = $result->fetch_assoc();
    $stmt->close();
    
    if (!$form_info) {
        sendResponse(false, 'Form not found', null);
    }
    
    $form_info['gambar_url'] = $form_info['gambar']
        ? 'https://basiliscine-ricky-nebuly.ngrok-free.dev/uploads/form/' . $form_info['gambar']
        : null;

    // Get form fields
    $query_fields = "SELECT id, nama, tipe, label, opsi 
                     FROM form 
                     WHERE form_info_id = ? ORDER BY id ASC";
    $stmt = $koneksi->prepare($query_fields);
    $stmt->bind_param("i", $form_id);
    $stmt->execute();
    $result_fields = $stmt->get_result();

    $fields = [];
    while ($row = $result_fields->fetch_assoc()) {
        $row['options'] = $row['opsi'] ? json_decode($row['opsi'], true) : [];
        unset($row['opsi']);
        $fields[] = $row;
    }

    $form_info['fields'] = $fields;

    sendResponse(true, 'Form detail retrieved successfully', $form_info);
}

/* =========================
   SUBMIT FORM (FIX: Hanya simpan nama file)
   ========================= */
if ($method === 'POST' && $action === 'submit_form') {

    $input = json_decode(file_get_contents('php://input'), true);

    $form_info_id = (int)($input['form_info_id'] ?? 0);
    $user_id      = (int)($input['user_id'] ?? 0);
    $submissions  = $input['submissions'] ?? [];

    if ($form_info_id <= 0 || $user_id <= 0 || empty($submissions)) {
        sendResponse(false, 'Invalid submission data', null);
    }

    // Check form exists
    $check_query = "SELECT id FROM form_info WHERE id = ? AND status = 'published'";
    $stmt = $koneksi->prepare($check_query);
    $stmt->bind_param("i", $form_info_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result->fetch_assoc()) {
        sendResponse(false, 'Form not available', null);
    }

    // Check if user already submitted
    $check_submit = "SELECT COUNT(*) AS count 
                     FROM submit s
                     JOIN form f ON s.form_id = f.id
                     WHERE f.form_info_id = ? AND s.user_id = ?";
    $stmt = $koneksi->prepare($check_submit);
    $stmt->bind_param("ii", $form_info_id, $user_id);
    $stmt->execute();
    $check = $stmt->get_result()->fetch_assoc();

    if ($check['count'] > 0) {
        sendResponse(false, 'You have already submitted this form', null);
    }

    $koneksi->begin_transaction();

    try {
        $insert_query = "INSERT INTO submit 
            (form_id, user_id, field_name, field_value, created_at, submitted_at, status)
            VALUES (?, ?, ?, ?, NOW(), NOW(), 'pending')";
        $stmt = $koneksi->prepare($insert_query);

        foreach ($submissions as $submission) {
            $field_id   = (int)$submission['field_id'];
            $field_name = $submission['field_name'];
            $value      = $submission['value'];

            // FIX: jika value adalah URL, ambil hanya nama file
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                $value = basename(parse_url($value, PHP_URL_PATH));
            }

            // FIX: jika value mengandung path folder → ambil basename
            $value = basename($value);

            $stmt->bind_param("iiss", $field_id, $user_id, $field_name, $value);
            $stmt->execute();
        }

        $koneksi->commit();
        sendResponse(true, 'Form submitted successfully', [
            'form_info_id' => $form_info_id,
            'user_id' => $user_id
        ]);

    } catch (Exception $e) {
        $koneksi->rollback();
        sendResponse(false, 'Failed to submit form: ' . $e->getMessage(), null);
    }
}

/* =========================
   UPLOAD FILE (OK)
   ========================= */
if ($method === 'POST' && $action === 'upload_file') {

    if (!isset($_FILES['file'])) {
        sendResponse(false, 'No file uploaded', null);
    }

    $file = $_FILES['file'];

    $allowed = ['jpg','jpeg','png','pdf','docx'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        sendResponse(false, 'Invalid file type', null);
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        sendResponse(false, 'File too large (max 5MB)', null);
    }

    $uploadDir = "../uploads/submissions";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newName = time() . "_" . rand(1000,9999) . "." . $ext;
    $filePath = $uploadDir . "/" . $newName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        sendResponse(true, 'File uploaded successfully', [
            "file_name" => $newName,
            "file_url"  => "https://basiliscine-ricky-nebuly.ngrok-free.dev/uploads/submissions/" . $newName
        ]);
    } else {
        sendResponse(false, "Failed to upload file", null);
    }
}

/* =========================
   GET USER SUBMISSIONS
   ========================= */
if ($method === 'GET' && $action === 'get_user_submissions') {
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    $jenis_form = $_GET['jenis_form'] ?? 'anggota';

    if ($user_id <= 0) {
        sendResponse(false, 'Invalid user ID', null);
    }

    $query = "SELECT DISTINCT fi.id, fi.judul, fi.deskripsi, fi.gambar, 
                     o.nama_ormawa, s.created_at AS submitted_at
              FROM submit s
              JOIN form f ON s.form_id = f.id
              JOIN form_info fi ON f.form_info_id = fi.id
              JOIN user u ON fi.user_id = u.id
              JOIN ormawa o ON u.id_ormawa = o.id
              WHERE s.user_id = ? AND fi.jenis_form = ?
              ORDER BY s.created_at DESC";

    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("is", $user_id, $jenis_form);
    $stmt->execute();
    $result = $stmt->get_result();

    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        $row['gambar_url'] = $row['gambar']
            ? 'https://basiliscine-ricky-nebuly.ngrok-free.dev/uploads/form/' . $row['gambar']
            : null;

        $submissions[] = $row;
    }

    sendResponse(true, 'User submissions retrieved successfully', $submissions);
}

sendResponse(false, 'Invalid action', null);
?>
