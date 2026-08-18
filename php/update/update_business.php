<?php
session_start();
require "../dbconnect.php";

$id            = $_POST['id'];
$business_name = $_POST['business_name'];
$owner_name    = $_POST['owner_name'];
$barangay      = $_POST['barangay'];
$contact       = $_POST['contact_number'];

$stmt = $conn->prepare("
    UPDATE businesses
    SET business_name=?, owner_name=?, barangay=?, contact_number=?
    WHERE id=?
");
$stmt->execute([$business_name, $owner_name, $barangay, $contact, $id]);

// Log activity
try {
    $log = $conn->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description) VALUES (?,?,?,?,'Edit','Businesses',?)");
    $log->execute([
        $_SESSION["user_id"]   ?? 0,
        $_SESSION["user"]      ?? "",
        $_SESSION["full_name"] ?? "",
        $_SESSION["role"]      ?? "inspector",
        "Updated business: {$business_name}"
    ]);
} catch(Exception $e){}

echo "Updated successfully!";