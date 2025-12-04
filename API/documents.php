<?php
header('Content-Type: application/json');

// Include koneksi dan fungsi
include('../Config/ConnectDB.php');
include('../Function/DocumentFunction.php');

// Pastikan ada parameter id_ormawa
if (!isset($_GET['id_ormawa']) || empty($_GET['id_ormawa'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parameter id_ormawa diperlukan'
    ]);
    exit;
}

$id_ormawa = intval($_GET['id_ormawa']);

// Ambil data dokumen berdasarkan id_ormawa
try {
    $dokumen_list = getDocumentDataByOrmawa($koneksi, $id_ormawa);

    if (!$dokumen_list) $dokumen_list = [];

    // Tambahkan link download pakai URL ngrok
    foreach ($dokumen_list as &$doc) {

    // Ambil ekstensi file
    $ext = strtolower(pathinfo($doc['file_path'], PATHINFO_EXTENSION));

    // Deteksi jenis dokumen
    switch ($ext) {
        case 'pdf': $doc['jenis_dokumen'] = 'PDF'; break;
        case 'doc':
        case 'docx': $doc['jenis_dokumen'] = 'DOCX'; break;
        case 'xls':
        case 'xlsx': $doc['jenis_dokumen'] = 'XLSX'; break;
        case 'ppt':
        case 'pptx': $doc['jenis_dokumen'] = 'PPTX'; break;
        default: $doc['jenis_dokumen'] = strtoupper($ext); break;
    }

    // Format ukuran file (dari KB ke KB/MB/GB)
    $doc['ukuran_file'] = formatFileSize($doc['ukuran_file']);

    // URL download
    $doc['download_url'] = 'https://basiliscine-ricky-nebuly.ngrok-free.dev/Uploads/dokumen/' . urlencode($doc['file_path']);
}

    echo json_encode([
        'status' => 'success',
        'data' => $dokumen_list
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>