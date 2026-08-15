<?php

require "../dbconnect.php";

$search   = $_GET['search']   ?? '';
$status   = $_GET['status']   ?? '';
$barangay = $_GET['barangay'] ?? '';
$date     = $_GET['date']     ?? '';

$sql = "
SELECT 
    b.id,
    b.business_name,
    b.owner_name,
    b.barangay,
    b.contact_number,
    b.latitude,
    b.longitude,
    b.created_at,
    COUNT(i.id) as inspection_count
FROM businesses b
LEFT JOIN inspections i ON i.business_id = b.id
WHERE (
    b.business_name LIKE :search
    OR b.owner_name LIKE :search
)
";

// Barangay filter
if(!empty($barangay)){
    $sql .= " AND b.barangay = :barangay ";
}

// Date filter
if(!empty($date)){
    $sql .= " AND DATE(b.created_at) = :date ";
}

$sql .= "
GROUP BY 
    b.id, b.business_name, b.owner_name,
    b.barangay, b.contact_number,
    b.latitude, b.longitude, b.created_at
";

// Status filter — after GROUP BY kasi naka-depend sa inspection_count
if(!empty($status)){
    if($status === 'inspected'){
        $sql .= " HAVING COUNT(i.id) > 0 ";
    } elseif($status === 'pending'){
        $sql .= " HAVING COUNT(i.id) = 0 ";
    }
}

$sql .= " ORDER BY b.created_at DESC ";

$stmt = $conn->prepare($sql);

$params = [':search' => "%$search%"];

if(!empty($barangay)) $params[':barangay'] = $barangay;
if(!empty($date))     $params[':date']     = $date;

$stmt->execute($params);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));