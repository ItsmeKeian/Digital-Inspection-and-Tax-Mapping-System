<?php
session_start();
require "../dbconnect.php";

$barangay = $_GET['barangay'] ?? '';
$status   = $_GET['status']   ?? '';
$from     = $_GET['from']     ?? '';
$to       = $_GET['to']       ?? '';
$search   = $_GET['search']   ?? '';

$query = "
SELECT
    b.business_name, b.owner_name, b.barangay,
    i.date_of_inspection, f.notice_violation, i.id as inspection_id
FROM businesses b
LEFT JOIN inspections i ON i.id = (
    SELECT id FROM inspections WHERE business_id = b.id
    ORDER BY date_of_inspection DESC LIMIT 1
)
LEFT JOIN (
    SELECT inspection_id, MAX(notice_violation) as notice_violation
    FROM findings GROUP BY inspection_id
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

$stmt = $conn->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($rows as &$r){
    if(!$r['inspection_id'])      $r['status'] = "Pending";
    elseif($r['notice_violation'] == 1) $r['status'] = "Violation";
    else                               $r['status'] = "Inspected";
}
unset($r);

if(!empty($status)){
    $rows = array_values(array_filter($rows, function($r) use ($status){
        return strcasecmp($r['status'], $status) === 0;
    }));
}

// Log activity
try {
    $log = $conn->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description) VALUES (?,?,?,?,'Export','Reports',?)");
    $log->execute([
        $_SESSION["user_id"]   ?? 0,
        $_SESSION["user"]      ?? "",
        $_SESSION["full_name"] ?? "",
        $_SESSION["role"]      ?? "inspector",
        "Exported reports to CSV (filters: barangay={$barangay}, status={$status})"
    ]);
} catch(Exception $e){}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="inspection_report.csv"');

$output = fopen("php://output", "w");
fputcsv($output, ["Business", "Owner", "Barangay", "Date", "Status"]);
foreach($rows as $r){
    fputcsv($output, [
        $r['business_name'],
        $r['owner_name'],
        $r['barangay'],
        $r['date_of_inspection'] ?? '-',
        $r['status']
    ]);
}
fclose($output);
exit;