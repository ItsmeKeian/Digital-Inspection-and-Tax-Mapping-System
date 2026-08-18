<?php
session_start();
require "dbconnect.php";

// Log logout activity before destroying session
try {
    if(isset($_SESSION["user"])){
        $log = $conn->prepare("
            INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description)
            VALUES (?, ?, ?, ?, 'Logout', 'System', 'User logged out')
        ");
        $log->execute([
            $_SESSION["user_id"]   ?? 0,
            $_SESSION["user"]      ?? "",
            $_SESSION["full_name"] ?? "",
            $_SESSION["role"]      ?? "admin"
        ]);
    }
} catch(Exception $e){
    // fail silently
}

session_destroy();
header("Location: ../index.html");
exit();