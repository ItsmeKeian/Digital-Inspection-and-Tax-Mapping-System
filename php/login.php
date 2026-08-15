<?php

session_start();
require "dbconnect.php";

$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";

if (empty($username) || empty($password)) {
    echo json_encode([
        "status"  => "error",
        "message" => "Please enter your username and password."
    ]);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {

    if (password_verify($password, $user["password"])) {

        // Store session
        $_SESSION["user"]      = $user["username"];
        $_SESSION["user_id"]   = $user["id"];
        $_SESSION["full_name"] = $user["full_name"] ?? $user["username"];
        $_SESSION["role"]      = $user["role"] ?? "admin";

        // Log login activity
        try {
            $log = $conn->prepare("
                INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description)
                VALUES (?, ?, ?, ?, 'Login', 'System', 'User logged in successfully')
            ");
            $log->execute([
                $user["id"],
                $user["username"],
                $user["full_name"] ?? $user["username"],
                $user["role"] ?? "admin"
            ]);
        } catch (Exception $e) {
            // fail silently
        }

        echo json_encode([
            "status" => "success",
            "role"   => $user["role"] ?? "admin"
        ]);

    } else {

        echo json_encode([
            "status"  => "error",
            "message" => "Incorrect password. Please try again."
        ]);
    }

} else {

    echo json_encode([
        "status"  => "error",
        "message" => "Username not found."
    ]);
}
?>