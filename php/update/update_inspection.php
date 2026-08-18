<?php
session_start();
require "../dbconnect.php";

if($_POST){

    $id = $_POST["inspection_id"];

    // UPDATE INSPECTIONS
    $stmt = $conn->prepare("
        UPDATE inspections SET
            business_id=?, date_of_inspection=?, time_of_inspection=?, barangay=?,
            business_name=?, trade_name=?, owner_name=?, contact_number=?,
            declared_nature=?, actual_nature=?, activity_matches=?, activity_not_match=?,
            psic_code=?, type_of_business=?, operation_status=?,
            floor_area=?, male_employees=?, female_employees=?,
            additional_support=?, remarks=?, latitude=?, longitude=?,
            inspector_name=?, date_signed=?
        WHERE id=?
    ");
    $stmt->execute([
        $_POST["business_id"],
        $_POST["date_of_inspection"],
        $_POST["time_of_inspection"],
        $_POST["barangay"],
        $_POST["business_name"],
        $_POST["trade_name"],
        $_POST["owner_name"],
        $_POST["contact_number"],
        $_POST["declared_nature"],
        $_POST["actual_nature"],
        $_POST["activity_matches"]   ?? 0,
        $_POST["activity_not_match"] ?? 0,
        $_POST["psic_code"],
        $_POST["type_of_business"],
        $_POST["operation_status"],
        $_POST["floor_area"],
        $_POST["male_employees"],
        $_POST["female_employees"],
        $_POST["additional_support"],
        $_POST["remarks"],
        $_POST["latitude"],
        $_POST["longitude"],
        $_POST["inspector_name"],
        $_POST["date_signed"],
        $id
    ]);

    // UPDATE REGISTRATION STATUS
    $stmt2 = $conn->prepare("
        UPDATE registration_status SET
            mayor_permit=?, barangay_clearance=?, dti_sec_cda=?,
            bir_registration=?, permit_number=?, year_last_registered=?
        WHERE inspection_id=?
    ");
    $stmt2->execute([
        $_POST["mayor_permit"],
        $_POST["barangay_clearance"],
        $_POST["dti_sec_cda"],
        $_POST["bir_registration"],
        $_POST["permit_number"],
        $_POST["year_last_registered"],
        $id
    ]);

    // UPDATE FINDINGS
    $stmt3 = $conn->prepare("
        UPDATE findings SET
            no_mayor_permit=?, expired_permit=?, change_nature=?,
            change_address=?, additional_line=?, others=?,
            notice_register=?, notice_violation=?, compliance_days=?,
            reassessment=?, referred_to=?, action_remarks=?,
            sanitary_permit=?, fire_cert=?, permit_displayed=?
        WHERE inspection_id=?
    ");
    $stmt3->execute([
        $_POST["no_mayor_permit"]  ?? 0,
        $_POST["expired_permit"]   ?? 0,
        $_POST["change_nature"]    ?? 0,
        $_POST["change_address"]   ?? 0,
        $_POST["additional_line"]  ?? 0,
        $_POST["others"],
        $_POST["notice_register"]  ?? 0,
        $_POST["notice_violation"] ?? 0,
        $_POST["compliance_days"],
        $_POST["reassessment"]     ?? 0,
        $_POST["referred_to"],
        $_POST["action_remarks"],
        $_POST["sanitary_permit"],
        $_POST["fire_cert"],
        $_POST["permit_displayed"],
        $id
    ]);

    // Log activity
    try {
        $business_name = $_POST["business_name"] ?? "ID: {$id}";
        $log = $conn->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description) VALUES (?,?,?,?,'Edit','Inspections',?)");
        $log->execute([
            $_SESSION["user_id"]   ?? 0,
            $_SESSION["user"]      ?? "",
            $_SESSION["full_name"] ?? "",
            $_SESSION["role"]      ?? "inspector",
            "Updated inspection for: {$business_name}"
        ]);
    } catch(Exception $e){}

    header("Location: ../../inspections.php");
    exit();
}