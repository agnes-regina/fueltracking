<?php
require 'vendor/autoload.php';
require_once 'config/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Kalau perlu batasi role
requireRole(['admin', 'gl_pama']);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Fuel Logs');

// Header kolom
$sheet->fromArray(['ID', 'Nomor Unit', 'Driver', 'Status', 'Tanggal Dibuat'], NULL, 'A1');

// Ambil data dari DB
$stmt = $pdo->query("SELECT id, nomor_unit, driver_name, status_progress, created_at FROM fuel_logs ORDER BY created_at DESC");
$data = $stmt->fetchAll();

$row = 2;
foreach ($data as $log) {
    $sheet->setCellValue("A{$row}", $log['id']);
    $sheet->setCellValue("B{$row}", $log['nomor_unit']);
    $sheet->setCellValue("C{$row}", $log['driver_name']);
    $sheet->setCellValue("D{$row}", $log['status_progress']);
    $sheet->setCellValue("E{$row}", $log['created_at']);
    $row++;
}

// Output ke browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="fuel_logs.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
