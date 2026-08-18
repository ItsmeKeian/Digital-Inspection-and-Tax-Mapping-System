<?php
session_start();
require "../dbconnect.php";
require "../../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;

if(isset($_FILES['excel_file']['name'])){
    $file = $_FILES['excel_file']['tmp_name'];

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows  = $sheet->toArray();

    $count = 0;

    for($i = 1; $i < count($rows); $i++){
        $business_name = $rows[$i][0];
        $owner_name    = $rows[$i][1];
        $barangay      = $rows[$i][2];
        $contact       = $rows[$i][3];
        $lat           = $rows[$i][4];
        $lng           = $rows[$i][5];

        if(empty($business_name)) continue;

        $stmt = $conn->prepare("
            INSERT INTO businesses (business_name, owner_name, barangay, contact_number, latitude, longitude)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$business_name, $owner_name, $barangay, $contact, $lat, $lng]);
        $count++;
    }

    // Log activity
    try {
        $log = $conn->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description) VALUES (?,?,?,?,'Import','Businesses',?)");
        $log->execute([
            $_SESSION["user_id"]   ?? 0,
            $_SESSION["user"]      ?? "",
            $_SESSION["full_name"] ?? "",
            $_SESSION["role"]      ?? "inspector",
            "Imported {$count} businesses from Excel"
        ]);
    } catch(Exception $e){}

    header("Location: ../../business.php");
    exit();
}