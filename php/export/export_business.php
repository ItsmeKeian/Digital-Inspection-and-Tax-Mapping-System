<?php
session_start();
require "../dbconnect.php";
require "../../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Headers
$sheet->setCellValue('A1', 'Business Name');
$sheet->setCellValue('B1', 'Owner Name');
$sheet->setCellValue('C1', 'Barangay');
$sheet->setCellValue('D1', 'Contact');
$sheet->setCellValue('E1', 'Latitude');
$sheet->setCellValue('F1', 'Longitude');

// Get data
$stmt = $conn->query("SELECT * FROM businesses");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$row = 2;
foreach($data as $d){
    $sheet->setCellValue('A'.$row, $d['business_name']);
    $sheet->setCellValue('B'.$row, $d['owner_name']);
    $sheet->setCellValue('C'.$row, $d['barangay']);
    $sheet->setCellValue('D'.$row, $d['contact_number']);
    $sheet->setCellValue('E'.$row, $d['latitude']);
    $sheet->setCellValue('F'.$row, $d['longitude']);
    $row++;
}

$count = count($data);

// Log activity
try {
    $log = $conn->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description) VALUES (?,?,?,?,'Export','Businesses',?)");
    $log->execute([
        $_SESSION["user_id"]   ?? 0,
        $_SESSION["user"]      ?? "",
        $_SESSION["full_name"] ?? "",
        $_SESSION["role"]      ?? "inspector",
        "Exported {$count} businesses to Excel"
    ]);
} catch(Exception $e){}

// Download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="businesses.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();