<?php
session_start();
require "../dbconnect.php";

$full_name = trim($_POST["full_name"] ?? "");
$username  = trim($_POST["username"]  ?? "");
$email     = trim($_POST["email"]     ?? "");
$role      = $_POST["role"]           ?? "inspector";
$password  = $_POST["password"]       ?? "";

if(empty($full_name) || empty($username) || empty($password)){
    echo json_encode(["status"=>"error","message"=>"Full name, username and password are required."]);
    exit();
}

// Check if username exists
$check = $conn->prepare("SELECT id FROM user WHERE username = ?");
$check->execute([$username]);
if($check->fetch()){
    echo json_encode(["status"=>"error","message"=>"Username already exists. Please choose another."]);
    exit();
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO user (full_name, username, email, role, password, created_at) VALUES (?,?,?,?,?,NOW())");
$stmt->execute([$full_name, $username, $email, $role, $hash]);

// Log activity
try {
    $log = $conn->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description) VALUES (?,?,?,?,'Add','User Management',?)");
    $log->execute([
        $_SESSION["user_id"] ?? 0,
        $_SESSION["user"] ?? "",
        $_SESSION["full_name"] ?? "",
        $_SESSION["role"] ?? "admin",
        "Added new {$role} account: {$username}"
    ]);
} catch(Exception $e){}

echo json_encode(["status"=>"success"]);