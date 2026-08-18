<?php
session_start();
require "../dbconnect.php";

ob_start();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=inspections.xls");

$stmt = $conn->prepare("
SELECT
    inspections.id, inspections.date_of_inspection, inspections.time_of_inspection,
    inspections.barangay, inspections.business_name, inspections.trade_name,
    inspections.owner_name, inspections.contact_number, inspections.declared_nature,
    inspections.actual_nature, inspections.psic_code, inspections.type_of_business,
    inspections.operation_status, inspections.floor_area, inspections.male_employees,
    inspections.female_employees, inspections.additional_support, inspections.remarks,
    inspections.inspector_name, inspections.date_signed,
    registration_status.mayor_permit, registration_status.barangay_clearance,
    registration_status.dti_sec_cda, registration_status.bir_registration,
    registration_status.permit_number, registration_status.year_last_registered,
    findings.no_mayor_permit, findings.expired_permit, findings.change_nature,
    findings.change_address, findings.additional_line, findings.others,
    findings.notice_register, findings.notice_violation, findings.reassessment,
    findings.compliance_days, findings.referred_to, findings.action_remarks,
    findings.sanitary_permit, findings.fire_cert, findings.permit_displayed
FROM inspections
LEFT JOIN registration_status ON inspections.id = registration_status.inspection_id
LEFT JOIN findings ON inspections.id = findings.inspection_id
ORDER BY inspections.id DESC
");
$stmt->execute();

echo "<table border='1'>";
echo "<tr>
<th>ID</th><th>Date</th><th>Time</th><th>Barangay</th>
<th>Business</th><th>Trade</th><th>Owner</th><th>Contact</th>
<th>Declared</th><th>Actual</th><th>PSIC</th><th>Type</th><th>Status</th>
<th>Floor</th><th>Male</th><th>Female</th>
<th>Support</th><th>Remarks</th><th>Inspector</th><th>Date Signed</th>
<th>Mayor</th><th>Brgy</th><th>DTI</th><th>BIR</th>
<th>Permit No</th><th>Year</th>
<th>No Mayor</th><th>Expired</th><th>Change Nature</th><th>Change Address</th>
<th>Additional</th><th>Others</th>
<th>Notice</th><th>Violation</th><th>Reassessment</th>
<th>Days</th><th>Referred</th><th>Action Remarks</th>
<th>Sanitary</th><th>Fire</th><th>Displayed</th>
</tr>";

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    echo "<tr>";
    foreach($row as $v){ echo "<td>".$v."</td>"; }
    echo "</tr>";
}
echo "</table>";

// Log activity
try {
    $log = $conn->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description) VALUES (?,?,?,?,'Export','Inspections',?)");
    $log->execute([
        $_SESSION["user_id"]   ?? 0,
        $_SESSION["user"]      ?? "",
        $_SESSION["full_name"] ?? "",
        $_SESSION["role"]      ?? "inspector",
        "Exported inspections to Excel"
    ]);
} catch(Exception $e){}