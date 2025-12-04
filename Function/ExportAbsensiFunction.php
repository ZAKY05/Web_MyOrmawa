<?php
// File: ../../../Function/ExportAbsensiFunction.php

require_once __DIR__ . '/../vendor/autoload.php'; // Sesuaikan path autoload
require_once __DIR__ . '/../Config/ConnectDB.php'; // Sesuaikan path koneksi

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Fungsi untuk mengirim error
function sendError($message) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Ambil ID kehadiran dari parameter GET
$kehadiran_id = (int) ($_GET['kehadiran_id'] ?? 0);
if ($kehadiran_id <= 0) {
    sendError('ID Kehadiran tidak valid.');
}

// Ambil data sesi untuk nama file
$sessionQuery = "SELECT judul_rapat, waktu_mulai, waktu_selesai FROM kehadiran WHERE id = ?";
$sessionStmt = mysqli_prepare($koneksi, $sessionQuery);
mysqli_stmt_bind_param($sessionStmt, "i", $kehadiran_id);
mysqli_stmt_execute($sessionStmt);
$sessionResult = mysqli_stmt_get_result($sessionStmt);
$sessionData = mysqli_fetch_assoc($sessionResult);
mysqli_stmt_close($sessionStmt);

if (!$sessionData) {
    sendError('Sesi tidak ditemukan.');
}

// Ambil data peserta
$waktu_selesai = $sessionData['waktu_selesai'];
$query = "SELECT 
            u.full_name, 
            al.waktu_absen, 
            al.tipe_absen,
            CASE 
                WHEN al.waktu_absen <= ? THEN 'Hadir'
                ELSE 'Terlambat'
            END AS status_kehadiran
         FROM absensi_log al
         JOIN user u ON al.user_id = u.id
         WHERE al.kehadiran_id = ?
         ORDER BY al.waktu_absen ASC";

$stmt = mysqli_prepare($koneksi, $query);
if (!$stmt) {
    sendError('Gagal menyiapkan query peserta: ' . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "si", $waktu_selesai, $kehadiran_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (!$res) {
    mysqli_stmt_close($stmt);
    sendError('Gagal mengambil data peserta.');
}

$peserta = [];
while ($row = mysqli_fetch_assoc($res)) {
    $peserta[] = $row;
}
mysqli_stmt_close($stmt);

// --- MULAI MEMBUAT EXCEL ---
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Data Header Dokumen
$judul_rapat = $sessionData['judul_rapat'];
$waktu_mulai = $sessionData['waktu_mulai'];
$waktu_selesai = $sessionData['waktu_selesai'];

// 1. Set Judul dan Metadata
$sheet->setTitle('Peserta'); 
$sheet->setCellValue('A1', 'LAPORAN KEHADIRAN RAPAT');
$sheet->mergeCells('A1:E1'); // Gabungkan sel judul utama

$sheet->setCellValue('A2', 'Judul Rapat');
$sheet->setCellValue('B2', ': ' . $judul_rapat);
$sheet->setCellValue('A3', 'Waktu Mulai');
$sheet->setCellValue('B3', ': ' . $waktu_mulai);
$sheet->setCellValue('A4', 'Waktu Selesai');
$sheet->setCellValue('B4', ': ' . $waktu_selesai);

// 2. Set Header Tabel (Baris 6)
$sheet->setCellValue('A6', 'No');
$sheet->setCellValue('B6', 'Nama Peserta');
$sheet->setCellValue('C6', 'Waktu Absen');
$sheet->setCellValue('D6', 'Tipe Absen');
$sheet->setCellValue('E6', 'Status Kehadiran');

// Isi data peserta
$rowIndex = 7; // Mulai dari baris ke-7
$no = 1;
foreach ($peserta as $p) {
    $sheet->setCellValue('A' . $rowIndex, $no);
    $sheet->setCellValue('B' . $rowIndex, $p['full_name']);
    $sheet->setCellValue('C' . $rowIndex, $p['waktu_absen']);
    $sheet->setCellValue('D' . $rowIndex, $p['tipe_absen']);
    $sheet->setCellValue('E' . $rowIndex, $p['status_kehadiran']);
    $rowIndex++;
    $no++;
}

// --- MULAI STYLING ---

// Style: Font Judul Utama (A1)
$sheet->getStyle('A1')->applyFromArray([
    'font' => [
        'bold' => true,
        'size' => 14,
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
]);

// Style: Bold Label Metadata (A2:A4)
$sheet->getStyle('A2:A4')->getFont()->setBold(true);

// Style: Header Tabel (Baris 6) - Background Biru, Text Putih, Bold
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'], // Warna Text Putih
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4A90E2'], // Warna Background Biru
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$sheet->getStyle('A6:E6')->applyFromArray($headerStyle);

// Style: Borders & Alignment untuk Data (Baris 7 sampai akhir)
$lastRow = $rowIndex - 1; // Baris terakhir yang berisi data
if ($lastRow >= 7) {
    // Border untuk seluruh tabel data
    $sheet->getStyle('A7:E' . $lastRow)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ]);

    // Rata Tengah untuk kolom No (A), Waktu (C), Tipe (D), Status (E)
    $sheet->getStyle('A7:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('C7:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// Style: Auto Width (Lebar kolom otomatis)
foreach (range('A', 'E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Output File
$filename = 'Rekap_Absensi_' . str_replace([' ', ':'], '_', substr($judul_rapat, 0, 15)) . '_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>