<?php

require "../dbconnect.php";

$search   = $_GET["search"]   ?? "";
$barangay = $_GET["barangay"] ?? "";
$type     = $_GET["type"]     ?? "";
$date     = $_GET["date"]     ?? "";

$sql = "
SELECT
    inspections.*,
    findings.no_mayor_permit,
    findings.expired_permit
FROM inspections
LEFT JOIN findings ON inspections.id = findings.inspection_id
WHERE 1=1
";

$params = [];

// Search filter
if(!empty($search)){
    $sql .= " AND (
        inspections.business_name LIKE ?
        OR inspections.owner_name LIKE ?
        OR inspections.barangay LIKE ?
    )";
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}

// Barangay filter
if(!empty($barangay)){
    $sql .= " AND inspections.barangay = ?";
    $params[] = $barangay;
}

// Type of business filter
if(!empty($type)){
    $sql .= " AND inspections.type_of_business = ?";
    $params[] = $type;
}

// Date filter
if(!empty($date)){
    $sql .= " AND DATE(inspections.date_of_inspection) = ?";
    $params[] = $date;
}

$sql .= " ORDER BY inspections.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));