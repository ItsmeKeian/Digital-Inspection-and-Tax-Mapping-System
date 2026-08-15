<?php
session_start();
require "../dbconnect.php";

$id = $_POST["id"] ?? "";

// Prevent deleting own account
$check = $conn->prepare("SELECT username FROM user WHERE id = ?");
$check->execute([$id]);
$user = $check->fetch(PDO::FETCH_ASSOC);

if($user && $user["username"] === ($_SESSION["user"] ?? "")){
    echo json_encode(["status"=>"error","message"=>"You cannot delete your own account."]);
    exit();
}

$stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
$stmt->execute([$id]);

// Log activity
try {
    $log = $conn->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description) VALUES (?,?,?,?,'Delete','User Management',?)");
    $log->execute([
        $_SESSION["user_id"] ?? 0,
        $_SESSION["user"] ?? "",
        $_SESSION["full_name"] ?? "",
        $_SESSION["role"] ?? "admin",
        "Deleted account ID: {$id}"
    ]);
} catch(Exception $e){}

echo json_encode(["status"=>"success"]);