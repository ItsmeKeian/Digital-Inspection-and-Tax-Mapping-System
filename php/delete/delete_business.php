<?php
session_start();
require "../dbconnect.php";

$id = $_POST['id'] ?? "";

// Get business name for logging
$get = $conn->prepare("SELECT business_name FROM businesses WHERE id = ?");
$get->execute([$id]);
$biz = $get->fetch(PDO::FETCH_ASSOC);
$business_name = $biz["business_name"] ?? "ID: {$id}";

$stmt = $conn->prepare("DELETE FROM businesses WHERE id = ?");
$stmt->execute([$id]);

// Log activity
try {
    $log = $conn->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description) VALUES (?,?,?,?,'Delete','Businesses',?)");
    $log->execute([
        $_SESSION["user_id"]   ?? 0,
        $_SESSION["user"]      ?? "",
        $_SESSION["full_name"] ?? "",
        $_SESSION["role"]      ?? "inspector",
        "Deleted business: {$business_name}"
    ]);
} catch(Exception $e){}

echo "Deleted successfully!";