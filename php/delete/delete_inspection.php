<?php
session_start();
require "../dbconnect.php";

$id = $_POST["id"] ?? "";

// Get business name for logging
$get = $conn->prepare("SELECT business_name FROM inspections WHERE id = ?");
$get->execute([$id]);
$ins = $get->fetch(PDO::FETCH_ASSOC);
$business_name = $ins["business_name"] ?? "ID: {$id}";

// Delete findings
$stmt1 = $conn->prepare("DELETE FROM findings WHERE inspection_id = ?");
$stmt1->execute([$id]);

// Delete registration
$stmt2 = $conn->prepare("DELETE FROM registration_status WHERE inspection_id = ?");
$stmt2->execute([$id]);

// Delete inspection
$stmt3 = $conn->prepare("DELETE FROM inspections WHERE id = ?");
$stmt3->execute([$id]);

// Log activity
try {
    $log = $conn->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description) VALUES (?,?,?,?,'Delete','Inspections',?)");
    $log->execute([
        $_SESSION["user_id"]   ?? 0,
        $_SESSION["user"]      ?? "",
        $_SESSION["full_name"] ?? "",
        $_SESSION["role"]      ?? "inspector",
        "Deleted inspection for: {$business_name}"
    ]);
} catch(Exception $e){}

echo "ok";