<?php
session_start();
require "../dbconnect.php";

$business_name  = $_POST['business_name']  ?? '';
$owner_name     = $_POST['owner_name']     ?? '';
$barangay       = $_POST['barangay']       ?? '';
$contact_number = $_POST['contact_number'] ?? '';
$latitude       = $_POST['latitude']       ?? null;
$longitude      = $_POST['longitude']      ?? null;

if(empty($business_name)){
    die("Business name is required.");
}

$stmt = $conn->prepare("
    INSERT INTO businesses (business_name, owner_name, barangay, contact_number, latitude, longitude)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$business_name, $owner_name, $barangay, $contact_number, $latitude, $longitude]);

// Log activity
try {
    $log = $conn->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description) VALUES (?,?,?,?,'Add','Businesses',?)");
    $log->execute([
        $_SESSION["user_id"]   ?? 0,
        $_SESSION["user"]      ?? "",
        $_SESSION["full_name"] ?? "",
        $_SESSION["role"]      ?? "inspector",
        "Added new business: {$business_name}"
    ]);
} catch(Exception $e){}

header("Location: ../../business.php");
exit();