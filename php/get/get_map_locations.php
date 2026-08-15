<?php

require "../dbconnect.php";

$barangay = $_GET["barangay"] ?? "";
$status   = $_GET["status"]   ?? "";
$search   = $_GET["search"]   ?? "";

// Get latest inspection per business
$sql = "
SELECT 
    b.id,
    b.business_name,
    b.owner_name,
    b.barangay,
    b.latitude,
    b.longitude,
    latest.operation_status

FROM businesses b

LEFT JOIN (
    SELECT business_id, operation_status
    FROM inspections
    WHERE id IN (
        SELECT MAX(id) FROM inspections GROUP BY business_id
    )
) AS latest ON b.id = latest.business_id

WHERE b.latitude IS NOT NULL
AND b.longitude IS NOT NULL
AND b.latitude != ''
AND b.longitude != ''
";

$params = [];

if(!empty($barangay)){
    $sql .= " AND b.barangay = ?";
    $params[] = $barangay;
}

if(!empty($status)){
    $sql .= " AND latest.operation_status = ?";
    $params[] = $status;
}

if(!empty($search)){
    $sql .= " AND (b.business_name LIKE ? OR b.owner_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));