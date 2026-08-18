<?php
require "../dbconnect.php";

$business_id = $_GET["business_id"] ?? "";

if(empty($business_id)){
    echo json_encode(["id" => null]);
    exit();
}

$stmt = $conn->prepare("
    SELECT id FROM inspections
    WHERE business_id = ?
    ORDER BY date_of_inspection DESC, id DESC
    LIMIT 1
");
$stmt->execute([$business_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode(["id" => $row["id"] ?? null]);