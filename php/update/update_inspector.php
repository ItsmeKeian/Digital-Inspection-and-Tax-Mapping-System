<?php
session_start();
require "../dbconnect.php";

$id        = $_POST["id"]        ?? "";
$full_name = trim($_POST["full_name"] ?? "");
$username  = trim($_POST["username"]  ?? "");
$email     = trim($_POST["email"]     ?? "");
$role      = $_POST["role"]           ?? "inspector";
$password  = $_POST["password"]       ?? "";

if(!empty($password)){
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE user SET full_name=?, username=?, email=?, role=?, password=? WHERE id=?");
    $stmt->execute([$full_name, $username, $email, $role, $hash, $id]);
} else {
    $stmt = $conn->prepare("UPDATE user SET full_name=?, username=?, email=?, role=? WHERE id=?");
    $stmt->execute([$full_name, $username, $email, $role, $id]);
}

// Log activity
try {
    $log = $conn->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description) VALUES (?,?,?,?,'Edit','User Management',?)");
    $log->execute([
        $_SESSION["user_id"] ?? 0,
        $_SESSION["user"] ?? "",
        $_SESSION["full_name"] ?? "",
        $_SESSION["role"] ?? "admin",
        "Updated account: {$username}"
    ]);
} catch(Exception $e){}

echo json_encode(["status"=>"success"]);