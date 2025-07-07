<?php
require 'vendor/autoload.php';
require_once 'config/db.php';
requireRole(['admin', 'gl_pama']);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Ambil ID dari GET atau array ids[] dari POST
$ids = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = array_map('intval', $_POST['ids']);
} elseif (isset($_GET['id'])) {
    $ids = [intval($_GET['id'])];
}
$ids = array_filter($ids); // buang 0/false

if (empty($ids)) {
    die('ID tidak valid.');
}

// 1. Mapping translate header
$translate = [
    "id" => "id",
    "nomor_unit" => "Nomor Unit",
    "driver_name" => "Nama Driver",
    "status_progress" => "Status Progress",
    "created_at" => "Dibuat Pada",
    "updated_at" => "Diupdate Pada",
    "pt_driver_name" => "Nama Driver",
    "pt_driver_id" => "Id Driver di Database",
    "pt_unit_number" => "Nomor Unit",
    "pt_created_by" => "Dibuat Oleh",
    "pt_created_at" => "Dibuat Pada",
    "pl_loading_start" => "Mulai Loading",
    "pl_loading_end" => "Selesai Loading",
    "pl_loading_location" => "Lokasi Loading",
    "pl_segel_photo_1" => "Foto Segel 1",
    "pl_segel_photo_2" => "Foto Segel 2",
    "pl_segel_photo_3" => "Foto Segel 3",
    "pl_segel_photo_4" => "Foto Segel 4",
    "pl_segel_1" => "Nomor Segel 1",
    "pl_segel_2" => "Nomor Segel 2",
    "pl_segel_3" => "Nomor Segel 3",
    "pl_segel_4" => "Nomor Segel 4",
    "pl_doc_sampel" => "Foto Sampel",
    "pl_doc_do" => "Foto DO",
    "pl_doc_suratjalan" => "Foto Surat Jalan",
    "pl_created_by" => "Dibuat Oleh",
    "pl_created_at" => "Dibuat Pada",
    "dr_loading_start" => "Mulai Loading",
    "dr_loading_end" => "Selesai Loading",
    "dr_loading_location" => "Lokasi Loading",
    "dr_waktu_keluar_pertamina" => "Waktu Keluar Pertamina",
    "dr_unload_start" => "Mulai Unload",
    "dr_unload_end" => "Unload Selesai",
    "dr_unload_location" => "Lokasi Unload",
    "dr_created_by" => "Dibuat Oleh",
    "dr_created_at" => "Dibuat Pada",
    "pd_arrived_at" => "Tiba Pada",
    "pd_foto_kondisi_1" => "Foto Segel 1",
    "pd_foto_kondisi_2" => "Foto Segel 2",
    "pd_foto_kondisi_3" => "Foto Segel 3",
    "pd_foto_kondisi_4" => "Foto Segel 4",
    "pd_foto_sib" => "Foto SIB",
    "pd_foto_ftw" => "Foto FTW",
    "pd_foto_p2h" => "Foto P2H",
    "pd_goto_msf" => "Pergi ke MSF Pada",
    "pd_created_by" => "Dibuat Oleh",
    "pd_created_at" => "Dibuat Pada",
    "fm_unload_start" => "Mulai Unload",
    "fm_unload_end" => "Unload Selesai",
    "fm_location" => "Lokasi Unload",
    "fm_segel_photo_awal_1" => "Foto Segel 1",
    "fm_segel_photo_awal_2" => "Foto Segel 2",
    "fm_segel_photo_awal_3" => "Foto Segel 3",
    "fm_segel_photo_awal_4" => "Foto Segel 4",
    "fm_photo_akhir_1" => "Foto Tangki Kosong 1",
    "fm_photo_akhir_2" => "Foto Tangki Kosong 2",
    "fm_photo_akhir_3" => "Foto Tangki Kosong 3",
    "fm_photo_akhir_4" => "Foto Tangki Kosong 4",
    "fm_photo_kejernihan" => "Foto Kejernihan",
    "fm_flowmeter" => "Flow Meter",
    "fm_serial" => "Serial Flow Meter",
    "fm_awal" => "Nilai Flow Meter Awal",
    "fm_akhir" => "Nilai Flow Meter Akhir",
    "fm_fuel_density" => "Densitas Bahan Bakar",
    "fm_fuel_temp" => "Temperatur Bahan Bakar",
    "fm_fuel_fame" => "Fame Bahan Bakar",
    "fm_created_by" => "Dibuat Oleh",
    "fm_created_at" => "Dibuat Pada",
];

// 2. Ambil mapping user id ke full_name
$userMap = [];
$stmtUser = $pdo->query("SELECT id, full_name FROM users");
foreach ($stmtUser->fetchAll() as $u) {
    $userMap[$u['id']] = $u['full_name'];
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Fuel Log Detail');

// Ambil data dari DB untuk id tertentu (bisa 1 atau banyak)
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM fuel_logs WHERE id IN ($placeholders)");
$stmt->execute($ids);
$data = $stmt->fetchAll();

if (!$data) {
    die('Data tidak ditemukan.');
}

// Cek field yang tidak null dari semua row
$nonEmptyFields = [];
foreach ($data as $row) {
    foreach ($row as $key => $value) {
        if (!empty($value) || is_numeric($value)) {
            $nonEmptyFields[$key] = true;
        }
    }
}

$headers = array_keys($nonEmptyFields);

// 3. Buat baris header 1 (field asli)
$sheet->fromArray($headers, NULL, 'A1');

// 4. Buat baris header 2 (translate)
$translatedHeaders = [];
foreach ($headers as $h) {
    $translatedHeaders[] = $translate[$h] ?? $h;
}
$sheet->fromArray($translatedHeaders, NULL, 'A2');

// 5. Style header
$headerStyle = [
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFCCCCCC']
    ]
];
$sheet->getStyle('A1:' . Coordinate::stringFromColumnIndex(count($headers)) . '2')->applyFromArray($headerStyle);

// 6. Kolom gambar
$imageFields = array_filter($headers, function ($field) {
    return str_contains($field, 'photo') || str_contains($field, 'doc') || str_contains($field, 'foto');
});

// 7. Warna kolom per prefix
$prefixColors = [
    'pt_' => 'FFB6D7A8', // hijau muda
    'pl_' => 'FFF9E79F', // kuning muda
    'dr_' => 'FFA9CCE3', // biru muda
    'pd_' => 'FFF5B7B1', // merah muda
    'fm_' => 'FFD2B4DE', // ungu muda
];
$colPrefix = [];
foreach ($headers as $col => $field) {
    foreach ($prefixColors as $prefix => $color) {
        if (str_starts_with($field, $prefix)) {
            $colPrefix[$col+1] = $color;
            break;
        }
    }
}

// 8. Tulis data mulai baris 3
$rowIndex = 3;
foreach ($data as $log) {
    $colIndex = 1;
    foreach ($headers as $field) {
        $cell = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
        // Ganti created_by dengan nama
        if (str_ends_with($field, 'created_by') && !empty($log[$field])) {
            $val = $userMap[$log[$field]] ?? $log[$field];
            $sheet->setCellValue($cell, $val);
        } elseif (in_array($field, $imageFields) && !empty($log[$field]) && file_exists($log[$field])) {
            try {
                $drawing = new Drawing();
                $drawing->setPath($log[$field]);
                $drawing->setCoordinates($cell);
                $drawing->setHeight(80);
                $drawing->setOffsetX(0);
                $drawing->setOffsetY(0);
                $drawing->setWorksheet($sheet);
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colIndex))->setWidth(15);
            } catch (Exception $e) {}
        } else {
            $sheet->setCellValue($cell, $log[$field]);
        }
        $colIndex++;
    }
    $sheet->getRowDimension($rowIndex)->setRowHeight(80);
    $rowIndex++;
}

// 9. Warnai kolom sesuai prefix
foreach ($colPrefix as $col => $color) {
    $colLetter = Coordinate::stringFromColumnIndex($col);
    $sheet->getStyle("{$colLetter}1:{$colLetter}{$rowIndex}")
        ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB($color);
}

// 10. Auto size
foreach (range(1, count($headers)) as $col) {
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
}

// Output ke browser
$filename = 'fuel_log_detail_' . (count($ids) === 1 ? $ids[0] : 'multi') . '_' . date('Y-m-d_H-i-s') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
