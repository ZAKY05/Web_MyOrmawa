<?php
// Pastikan file ini tidak diakses langsung tanpa aksi
if (!isset($_GET['action']) || $_GET['action'] !== 'export_submissions_xlsx') {
    header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&error=unknown_action");
    exit;
}

require_once '../Config/ConnectDB.php'; // Pastikan path ke ConnectDB.php benar
require_once '../vendor/autoload.php'; // Path ke autoload Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

$form_info_id = (int)($_GET['form_info_id'] ?? 0);

if ($form_info_id <= 0) {
    // Redirect atau tampilkan error jika ID tidak valid
    header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&error=invalid_form_id");
    exit;
}

// Query untuk mendapatkan semua submission untuk form_info_id tertentu
$all_submissions_query = "
    SELECT 
        s.id as submission_id,
        s.user_id, 
        u.full_name as user_nama, 
        u.nim, 
        u.email, 
        s.form_id, 
        s.field_name, 
        s.field_value, 
        f.label as field_label,
        f.tipe as field_type,
        f.id as field_id,
        s.status as submission_status
    FROM submit s
    INNER JOIN user u ON s.user_id = u.id
    INNER JOIN form f ON s.form_id = f.id AND s.field_name = f.nama
    WHERE f.form_info_id = ?
    ORDER BY s.user_id ASC, f.id ASC
";

$stmt = $koneksi->prepare($all_submissions_query);
if (!$stmt) {
    error_log("Error preparing statement for export: " . $koneksi->error);
    header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&form_info_id=$form_info_id&error=query_gagal");
    exit;
}
$stmt->bind_param("i", $form_info_id);
$stmt->execute();
$result_all_submissions = $stmt->get_result();
$all_submissions_raw = [];
while ($row = $result_all_submissions->fetch_assoc()) {
    $all_submissions_raw[] = $row;
}
$stmt->close();

// Organisir jawaban berdasarkan user_id
$organized_submissions = [];
$unique_users = [];
foreach ($all_submissions_raw as $sub) {
    $user_id = $sub['user_id'];

    if (!isset($unique_users[$user_id])) {
        $unique_users[$user_id] = [
            'nama'      => $sub['user_nama'],
            'nim'       => $sub['nim'],
            'email'     => $sub['email'],
            'status'    => $sub['submission_status'] // Ambil status dari satu submission, asumsi konsisten
        ];
        $organized_submissions[$user_id] = [];
    }

    $organized_submissions[$user_id][] = [
        'field_label'   => $sub['field_label'],
        'field_value'   => $sub['field_value'],
    ];
}

// Ambil judul form untuk nama file dan header
$form_detail_query = "SELECT judul FROM form_info WHERE id = ? LIMIT 1";
$stmt = $koneksi->prepare($form_detail_query);
$stmt->bind_param("i", $form_info_id);
$stmt->execute();
$form_detail = $stmt->get_result()->fetch_assoc();
$stmt->close();

$form_title = $form_detail ? $form_detail['judul'] : 'Form_' . $form_info_id;

// Buat Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- Styling Dasar ---
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '34495E'], // Warna gelap untuk header
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

$dataStyle = [
    'alignment' => [
        'vertical' => Alignment::VERTICAL_TOP,
        'wrapText' => true, // Agar teks panjang bisa wrap ke baris berikutnya dalam sel
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'CCCCCC'],
        ],
    ],
];

// --- LOGIKA PEMBUATAN HEADER DAN ISI DATA (Menggunakan Alamat Sel) ---
// Kumpulkan semua label field unik yang muncul di semua submission untuk form ini
$fieldLabels = [];
foreach ($organized_submissions as $submissions) {
    foreach ($submissions as $sub) {
        if (!in_array($sub['field_label'], $fieldLabels)) {
            $fieldLabels[] = $sub['field_label'];
        }
    }
}

// Header Kolom: No, Nama, NIM, Email, Status, lalu semua field label
$headers = ['No', 'Nama', 'NIM', 'Email', 'Status'];
$headers = array_merge($headers, $fieldLabels);

// Tulis header ke baris pertama (Row 1)
$colIndex = 0; // Mulai dari 0 untuk kolom A
foreach ($headers as $header) {
    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1); // A, B, C, ...
    $cellAddress = $columnLetter . '1';
    $sheet->setCellValue($cellAddress, $header);
    $sheet->getStyle($cellAddress)->applyFromArray($headerStyle);
    $colIndex++;
}

// Isi Data Baris (Mulai dari Row 2)
$rowIndex = 2;
$no = 1;
foreach ($unique_users as $user_id => $user_info) {
    $submissions = $organized_submissions[$user_id] ?? [];

    // Mulai kolom dari 0 lagi untuk setiap baris pengguna
    $colIndex = 0; 
    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
    $cellAddress = $columnLetter . $rowIndex;
    $sheet->setCellValue($cellAddress, $no++);
    $sheet->getStyle($cellAddress)->applyFromArray($dataStyle);
    
    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1 + 1); // Kolom berikutnya
    $cellAddress = $columnLetter . $rowIndex;
    $sheet->setCellValue($cellAddress, $user_info['nama']);
    $sheet->getStyle($cellAddress)->applyFromArray($dataStyle);
    
    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1 + 2);
    $cellAddress = $columnLetter . $rowIndex;
    $sheet->setCellValue($cellAddress, $user_info['nim']);
    $sheet->getStyle($cellAddress)->applyFromArray($dataStyle);
    
    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1 + 3);
    $cellAddress = $columnLetter . $rowIndex;
    $sheet->setCellValue($cellAddress, $user_info['email']);
    $sheet->getStyle($cellAddress)->applyFromArray($dataStyle);
    
    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1 + 4);
    $cellAddress = $columnLetter . $rowIndex;
    $sheet->setCellValue($cellAddress, $user_info['status']);
    $sheet->getStyle($cellAddress)->applyFromArray($dataStyle);

    // Iterasi melalui *semua* label field yang telah dikumpulkan
    // untuk mengisi kolom yang sesuai, bahkan jika pengguna tidak menjawabnya
    foreach ($fieldLabels as $label) {
        $found = false;
        // Cari jawaban dari pengguna untuk label field saat ini
        foreach ($submissions as $sub) {
            if ($sub['field_label'] === $label) {
                $value = $sub['field_value'];
                // Jika nilai adalah path file, cukup tampilkan nama filenya
                if (preg_match('/\.(jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt|zip|rar)$/i', $value)) {
                    $value = basename($value);
                }
                // Tulis nilai ke kolom yang benar (berdasarkan urutan $fieldLabels)
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(5 + 1 + array_search($label, $fieldLabels)); // Kolom setelah Email (E) + indeks field
                $cellAddress = $columnLetter . $rowIndex;
                $sheet->setCellValue($cellAddress, $value);
                $sheet->getStyle($cellAddress)->applyFromArray($dataStyle);
                $found = true;
                break; // Hentikan pencarian untuk label ini
            }
        }
        // Jika jawaban tidak ditemukan, isi kolom dengan string kosong
        if (!$found) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(5 + 1 + array_search($label, $fieldLabels));
            $cellAddress = $columnLetter . $rowIndex;
            $sheet->setCellValue($cellAddress, '');
            $sheet->getStyle($cellAddress)->applyFromArray($dataStyle);
        }
        // Pindah ke kolom berikutnya, terlepas dari apakah jawaban ditemukan atau tidak
        // Kode ini sekarang terwakili oleh pencarian kolom berdasarkan array_search di atas
    }

    // Pindah ke baris berikutnya setelah selesai mengisi semua kolom untuk satu pengguna
    $rowIndex++; 
}
// --- SAMPAI SINI ---

// --- Atur Lebar Kolom Otomatis (jika tersedia) dan Styling Tambahan ---
$highestColumn = $sheet->getHighestDataColumn();
$sheet->getStyle("A1:{$highestColumn}1")->getFont()->setBold(true); // Pastikan header tetap bold

// Atur lebar kolom otomatis (mungkin tidak akurat 100%, tapi membantu)
foreach (range('A', $highestColumn) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
    // Batasi lebar maksimum kolom untuk mencegah kolom terlalu lebar karena teks panjang
    $currentWidth = $sheet->getColumnDimension($col)->getWidth();
    if ($currentWidth > 50) { // Atur batas maksimum, misalnya 50
        $sheet->getColumnDimension($col)->setWidth(50);
    }
}

// Tinggi baris otomatis (bergantung pada wrapText dan isi sel)
$sheet->getRowDimension(1)->setRowHeight(25); // Tinggi baris header
for ($r = 2; $r < $rowIndex; $r++) {
    $sheet->getRowDimension($r)->setRowHeight(20); // Tinggi baris data awal
    // Untuk tinggi otomatis, Excel biasanya menanganinya saat file dibuka.
    // PHPSpreadsheet bisa menghitungnya, tapi ini bisa memakan waktu dan tidak selalu akurat.
}

// --- Header untuk download ---
// Nama file
$filename = 'Submissions_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $form_title) . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

?>
