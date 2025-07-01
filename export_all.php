<?php
require 'vendor/autoload.php';
require_once 'config/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// Require admin or gl_pama role
requireRole(['admin', 'gl_pama']);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Fuel Logs');

// Header kolom
$headers = [
    'ID', 'Nomor Unit', 'Driver', 'Status', 'Tanggal Dibuat',
    'PT Driver ID', 'PT Unit Number',
    'PL Loading Start', 'PL Loading End', 'PL Location',
    'PL Segel 1', 'PL Segel 2', 'PL Segel 3', 'PL Segel 4',
    'DR Loading Start', 'DR Loading End', 'DR Location',
    'DR Waktu Keluar Pertamina', 'DR Unload Start', 'DR Unload End', 'DR Unload Location',
    'PD Arrived At', 'PD Alasan > 7 Jam',
    'FM Unload Start', 'FM Unload End', 'FM Location',
    'FM Flowmeter', 'FM Awal', 'FM Akhir', 'FM Density', 'FM Temp', 'FM FAME'
];

$sheet->fromArray($headers, NULL, 'A1');

// Style header
$headerStyle = [
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFCCCCCC']
    ]
];
$sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);

// Ambil data dari DB
$stmt = $pdo->query("
    SELECT * FROM fuel_logs 
    ORDER BY created_at DESC
");
$data = $stmt->fetchAll();

$row = 2;
foreach ($data as $log) {
    $values = [
        $log['id'],
        $log['nomor_unit'],
        $log['driver_name'],
        $log['status_progress'],
        $log['created_at'],
        $log['pt_driver_id'] ?? '',
        $log['pt_unit_number'] ?? '',
        $log['pl_loading_start'] ?? '',
        $log['pl_loading_end'] ?? '',
        $log['pl_loading_location'] ?? '',
        $log['pl_segel_1'] ?? '',
        $log['pl_segel_2'] ?? '',
        $log['pl_segel_3'] ?? '',
        $log['pl_segel_4'] ?? '',
        $log['dr_loading_start'] ?? '',
        $log['dr_loading_end'] ?? '',
        $log['dr_loading_location'] ?? '',
        $log['dr_waktu_keluar_pertamina'] ?? '',
        $log['dr_unload_start'] ?? '',
        $log['dr_unload_end'] ?? '',
        $log['dr_unload_location'] ?? '',
        $log['pd_arrived_at'] ?? '',
        $log['pd_alasan_lebih_7jam'] ?? '',
        $log['fm_unload_start'] ?? '',
        $log['fm_unload_end'] ?? '',
        $log['fm_location'] ?? '',
        $log['fm_flowmeter'] ?? '',
        $log['fm_awal'] ?? '',
        $log['fm_akhir'] ?? '',
        $log['fm_fuel_density'] ?? '',
        $log['fm_fuel_temp'] ?? '',
        $log['fm_fuel_fame'] ?? ''
    ];
    
    $sheet->fromArray($values, NULL, 'A' . $row);
    
    // Add images if they exist
    $imageFields = [
        'pl_segel_photo_1', 'pl_segel_photo_2', 'pl_segel_photo_3', 'pl_segel_photo_4',
        'pl_doc_sampel', 'pl_doc_do', 'pl_doc_suratjalan',
        'dr_segel_photo_1', 'dr_segel_photo_2', 'dr_segel_photo_3', 'dr_segel_photo_4',
        'dr_doc_do', 'dr_doc_surat_pertamina', 'dr_doc_sampel_bbm',
        'pd_foto_kondisi_1', 'pd_foto_kondisi_2', 'pd_foto_kondisi_3', 'pd_foto_kondisi_4',
        'fm_segel_photo_awal_1', 'fm_segel_photo_awal_2', 'fm_segel_photo_awal_3', 'fm_segel_photo_awal_4',
        'fm_photo_akhir_1', 'fm_photo_akhir_2', 'fm_photo_akhir_3', 'fm_photo_akhir_4'
    ];
    
    $imageColumn = count($headers) + 1;
    foreach ($imageFields as $field) {
        if (!empty($log[$field])) {
            $imagePath = $log[$field];
            if (file_exists($imagePath)) {
                try {
                    $drawing = new Drawing();
                    $drawing->setName($field);
                    $drawing->setDescription($field);
                    $drawing->setPath($imagePath);
                    $drawing->setHeight(100);
                    $drawing->setCoordinates(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($imageColumn) . $row);
                    $drawing->setWorksheet($sheet);
                    $imageColumn++;
                } catch (Exception $e) {
                    // Skip if image can't be loaded
                }
            }
        }
    }
    
    // Set row height for images
    $sheet->getRowDimension($row)->setRowHeight(80);
    $row++;
}

// Auto size columns
foreach (range('A', $sheet->getHighestColumn()) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Output ke browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="fuel_logs_with_images_' . date('Y-m-d_H-i-s') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
