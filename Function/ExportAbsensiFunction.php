<?php
// ./../../Function/ExportAbsensiFunction.php

// --- Import PhpSpreadsheet ---
require_once '../vendor/autoload.php'; // Pastikan path ini benar menuju autoload.php milik PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

// --- Inisialisasi Sesi ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Validasi Session dan Level Pengguna ---
$user_level = (int) ($_SESSION['user_level'] ?? 0);
$session_ormawa_id = (int) ($_SESSION['ormawa_id'] ?? 0);

if ($user_level !== 1 && $user_level !== 2) {
    http_response_code(403);
    die('Akses ditolak. Hanya SuperAdmin atau Admin ORMAWA yang dapat mengakses fungsi ini.');
}

// --- Ambil dan Validasi ID Kehadiran dari Parameter URL ---
$kehadiran_id = (int) ($_GET['kehadiran_id'] ?? 0);
if ($kehadiran_id <= 0) {
    http_response_code(400);
    die('ID Kehadiran tidak valid.');
}

// --- Koneksi Database ---
$path_koneksi = __DIR__ . '/../Config/ConnectDB.php';
if (!file_exists($path_koneksi)) {
    http_response_code(500);
    die("Error: File koneksi database tidak ditemukan di $path_koneksi");
}
$koneksi = include($path_koneksi);
if (!$koneksi) {
    http_response_code(500);
    die('Gagal terhubung ke database.');
}

// --- Validasi Akses ke Sesi dan Ambil Detail Sesi dalam Satu Query ---
$validateQuery = "
    SELECT 
        k.judul_rapat, 
        k.waktu_mulai, 
        k.waktu_selesai, 
        k.ormawa_id,
        o.nama_ormawa
    FROM kehadiran k
    JOIN ormawa o ON k.ormawa_id = o.id
    WHERE k.id = ?
";
$stmt_validate = mysqli_prepare($koneksi, $validateQuery);
if (!$stmt_validate) {
    http_response_code(500);
    die('Gagal menyiapkan query validasi sesi dan pengambilan detail.');
}
mysqli_stmt_bind_param($stmt_validate, "i", $kehadiran_id);
mysqli_stmt_execute($stmt_validate);
$result_validate = mysqli_stmt_get_result($stmt_validate);
$row_validate = mysqli_fetch_assoc($result_validate);

if (!$row_validate) {
    http_response_code(404);
    die('Sesi absensi tidak ditemukan.');
}

$judul_rapat = $row_validate['judul_rapat'];
$waktu_mulai = $row_validate['waktu_mulai'];
$waktu_selesai = $row_validate['waktu_selesai'];
$sesi_ormawa_id = (int) $row_validate['ormawa_id'];
$nama_ormawa = $row_validate['nama_ormawa'];

// Periksa akses: SuperAdmin (1) bisa akses semua, Admin Ormawa (2) hanya bisa akses miliknya sendiri
if ($user_level === 2 && $sesi_ormawa_id !== $session_ormawa_id) {
    http_response_code(403);
    die('Akses ditolak. Anda tidak memiliki izin untuk mengakses sesi ini.');
}

mysqli_stmt_close($stmt_validate);

if (!$waktu_selesai || $waktu_selesai === '0000-00-00 00:00:00') {
    http_response_code(500);
    die('Waktu selesai sesi tidak valid.');
}

// --- Ambil Data Peserta ---
// Kolom 'tipe_absen' dan 'no_hp' dihapus dari SELECT
$query = "
    SELECT 
        u.nim,
        u.full_name, 
        u.email,
        al.waktu_absen, 
        -- al.tipe_absen, -- Dihapus
        -- u.no_hp,       -- Dihapus
        CASE 
            WHEN al.waktu_absen <= ? THEN 'Hadir'
            ELSE 'Terlambat'
        END AS status_kehadiran
     FROM absensi_log al
     JOIN user u ON al.user_id = u.id
     WHERE al.kehadiran_id = ?
     ORDER BY al.waktu_absen ASC
";

$stmt_data = mysqli_prepare($koneksi, $query);
if (!$stmt_data) {
    http_response_code(500);
    die('Gagal menyiapkan query data peserta.');
}
mysqli_stmt_bind_param($stmt_data, "si", $waktu_selesai, $kehadiran_id);
mysqli_stmt_execute($stmt_data);
$result_data = mysqli_stmt_get_result($stmt_data);

$data_peserta = [];
while ($row = mysqli_fetch_assoc($result_data)) {
    $data_peserta[] = $row;
}

mysqli_stmt_close($stmt_data);

// --- Generate Spreadsheet ---
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- Tambahkan Header Informasi Sesi ---
$row_num_header = 1;

$sheet->setCellValue('A' . $row_num_header, 'REKAPITULASI ABSENSI');
$sheet->getStyle('A' . $row_num_header)->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A' . $row_num_header)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

$row_num_header++;
$sheet->setCellValue('A' . $row_num_header, 'Judul Rapat:');
$sheet->setCellValue('B' . $row_num_header, $judul_rapat);
$sheet->getStyle('A' . $row_num_header)->getFont()->setBold(true);
$sheet->getStyle('B' . $row_num_header)->getAlignment()->setWrapText(true);

$row_num_header++;
$sheet->setCellValue('A' . $row_num_header, 'ORMAWA:');
$sheet->setCellValue('B' . $row_num_header, $nama_ormawa);
$sheet->getStyle('A' . $row_num_header)->getFont()->setBold(true);

$row_num_header++;
$sheet->setCellValue('A' . $row_num_header, 'Waktu Mulai:');
$sheet->setCellValue('B' . $row_num_header, (new DateTime($waktu_mulai))->format('d/m/Y H:i:s'));
$sheet->getStyle('A' . $row_num_header)->getFont()->setBold(true);

$row_num_header++;
$sheet->setCellValue('A' . $row_num_header, 'Waktu Selesai:');
$sheet->setCellValue('B' . $row_num_header, (new DateTime($waktu_selesai))->format('d/m/Y H:i:s'));
$sheet->getStyle('A' . $row_num_header)->getFont()->setBold(true);

// Tambahkan baris kosong sebelum tabel data
$row_num_header += 2;

// Atur Header Kolom Tabel Rekap (tanpa 'No HP' dan 'Tipe Absen')
$headers = [
    'No',
    'NIM',
    'Nama',
    'Email',
    'Waktu Absen',
    'Status Kehadiran'
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . $row_num_header, $header);
    $col++;
}

// Styling Header Tabel
$headerStyle = [
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'D3D3D3'], // Abu-abu terang
    ],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
];
$sheet->getStyle('A' . $row_num_header . ':' . chr(ord('A') + count($headers) - 1) . $row_num_header)->applyFromArray($headerStyle);

// Isi Data Rekap Peserta (dimulai dari baris setelah header tabel)
$row_num_data = $row_num_header + 1;
$no_urut = 1;
foreach ($data_peserta as $peserta) {
    $sheet->setCellValueExplicit('A' . $row_num_data, $no_urut++, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC); // No
    $sheet->setCellValueExplicit('B' . $row_num_data, $peserta['nim'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // NIM
    $sheet->setCellValueExplicit('C' . $row_num_data, $peserta['full_name'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // Nama
    $sheet->setCellValueExplicit('D' . $row_num_data, $peserta['email'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // Email
    
    // Waktu Absen
    if ($peserta['waktu_absen']) {
        $waktu_absen_obj = new DateTime($peserta['waktu_absen']);
        $sheet->setCellValue('E' . $row_num_data, $waktu_absen_obj);
        $sheet->getStyle('E' . $row_num_data)->getNumberFormat()->setFormatCode('DD/MM/YYYY HH:MM:SS');
    } else {
        $sheet->setCellValue('E' . $row_num_data, '-');
    }
    
    $sheet->setCellValueExplicit('F' . $row_num_data, $peserta['status_kehadiran'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // Status Kehadiran
    $row_num_data++;
}

// Auto-size columns (opsional, bisa memperlambat proses)
foreach (range('A', chr(ord('A') + count($headers) - 1)) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set Properties Dokumen
$spreadsheet->getProperties()
    ->setCreator("Sistem Absensi ORMAWA")
    ->setTitle("Rekapitulasi Absensi - " . $judul_rapat)
    ->setSubject("Daftar Hadir Sesi " . $judul_rapat)
    ->setDescription("Rekapitulasi kehadiran peserta untuk sesi \"$judul_rapat\" milik $nama_ormawa.")
    ->setKeywords("absensi rapat ormawa excel");

// --- Output File Excel ---
$filename = 'Rekap_Absensi_' . preg_replace('/[^A-Za-z0-9 _.-]/', '_', $judul_rapat) . '_' . date('Y-m-d_H-i-s') . '.xlsx';

// Atur header HTTP untuk download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
// Cache Control tambahan untuk browser
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

exit(); // Sangat penting untuk menghentikan eksekusi setelah file dikirim
?>