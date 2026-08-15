<?php
require "../dbconnect.php";

$barangay = $_GET['barangay'] ?? '';
$status   = $_GET['status']   ?? '';
$from     = $_GET['from']     ?? '';
$to       = $_GET['to']       ?? '';
$search   = $_GET['search']   ?? '';

$query = "
SELECT
    b.id,
    b.business_name,
    b.owner_name,
    b.barangay,
    i.date_of_inspection,
    i.id as inspection_id,
    f.notice_violation
FROM businesses b

LEFT JOIN inspections i
ON i.id = (
    SELECT id FROM inspections
    WHERE business_id = b.id
    ORDER BY date_of_inspection DESC
    LIMIT 1
)

LEFT JOIN (
    SELECT
        inspection_id,
        MAX(notice_violation) as notice_violation
    FROM findings
    GROUP BY inspection_id
) f ON i.id = f.inspection_id

WHERE 1=1
";

$params = [];

if(!empty($barangay)){
    $query .= " AND b.barangay = :barangay";
    $params[':barangay'] = $barangay;
}

if(!empty($from)){
    $query .= " AND (i.date_of_inspection >= :from OR i.date_of_inspection IS NULL)";
    $params[':from'] = $from;
}

if(!empty($to)){
    $query .= " AND (i.date_of_inspection <= :to OR i.date_of_inspection IS NULL)";
    $params[':to'] = $to;
}

if(!empty($search)){
    $query .= " AND (b.business_name LIKE :search OR b.owner_name LIKE :search)";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY i.date_of_inspection DESC, b.business_name ASC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine status per row
foreach($rows as &$r){
    if(!$r['inspection_id']){
        $r['status'] = "Pending";
    } elseif($r['notice_violation'] == 1){
        $r['status'] = "Violation";
    } else {
        $r['status'] = "Inspected";
    }
    // Use inspection_id as the id for view/edit/delete
    $r['id'] = $r['inspection_id'] ?? $r['id'];
}
unset($r);

// Status filter — after status is determined
if(!empty($status)){
    $rows = array_values(array_filter($rows, function($r) use ($status){
        return strcasecmp($r['status'], $status) === 0;
    }));
}

// Chart data
$months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
$monthly = array_fill(0, 12, 0);
$inspected = 0; $pending = 0; $violations = 0;

foreach($rows as $r){
    if(!empty($r['date_of_inspection'])){
        $m = (int)date("n", strtotime($r['date_of_inspection']));
        $monthly[$m - 1]++;
    }
    if($r['status'] === "Inspected")  $inspected++;
    if($r['status'] === "Pending")    $pending++;
    if($r['status'] === "Violation")  $violations++;
}

echo json_encode([
    "rows"       => array_values($rows),
    "months"     => $months,
    "monthly"    => $monthly,
    "inspected"  => $inspected,
    "pending"    => $pending,
    "violations" => $violations
]);