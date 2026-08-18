<?php
session_start();
require "../dbconnect.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $business_id        = $_POST["business_id"]       ?? null;
    $date               = $_POST["date_of_inspection"] ?? null;
    $time               = $_POST["time_of_inspection"] ?? null;
    $barangay           = $_POST["barangay"]           ?? "";
    $business_name      = $_POST["business_name"]      ?? "";
    $trade_name         = $_POST["trade_name"]         ?? "";
    $owner_name         = $_POST["owner_name"]         ?? "";
    $contact            = $_POST["contact_number"]     ?? "";
    $declared           = $_POST["declared_nature"]    ?? "";
    $actual             = $_POST["actual_nature"]      ?? "";
    $activity_match     = $_POST["activity_matches"]   ?? 0;
    $activity_not_match = $_POST["activity_not_match"] ?? 0;
    $psic_code          = $_POST["psic_code"]          ?? "";
    $businesstype       = $_POST["type_of_business"]   ?? "";
    $operation_status   = $_POST["operation_status"]   ?? "";
    $additional_support = $_POST["additional_support"] ?? "";
    $inspec_remarks     = $_POST["remarks"]            ?? "";
    $floor_area         = $_POST["floor_area"]         ?? "";
    $male               = $_POST["male_employees"]     ?? 0;
    $female             = $_POST["female_employees"]   ?? 0;
    $inspector          = $_POST["inspector_name"]     ?? "";
    $date_signed        = $_POST["date_signed"]        ?? null;

    // INSERT INSPECTION
    $stmt = $conn->prepare("
        INSERT INTO inspections (
            business_id, date_of_inspection, time_of_inspection, barangay,
            business_name, trade_name, owner_name, contact_number,
            declared_nature, actual_nature, activity_matches, activity_not_match,
            psic_code, type_of_business, operation_status, additional_support, remarks,
            floor_area, male_employees, female_employees, inspector_name, date_signed
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([
        $business_id, $date, $time, $barangay,
        $business_name, $trade_name, $owner_name, $contact,
        $declared, $actual, $activity_match, $activity_not_match,
        $psic_code, $businesstype, $operation_status, $additional_support, $inspec_remarks,
        $floor_area, $male, $female, $inspector, $date_signed
    ]);

    $inspection_id = $conn->lastInsertId();

    // INSERT REGISTRATION STATUS
    $mayor  = $_POST["mayor_permit"]        ?? "";
    $brgy   = $_POST["barangay_clearance"]  ?? "";
    $dti    = $_POST["dti_sec_cda"]         ?? "";
    $bir    = $_POST["bir_registration"]    ?? "";
    $permit = $_POST["permit_number"]       ?? "";
    $year   = $_POST["year_last_registered"] ?? "";

    $stmt2 = $conn->prepare("
        INSERT INTO registration_status (
            inspection_id, mayor_permit, barangay_clearance,
            dti_sec_cda, bir_registration, permit_number, year_last_registered
        ) VALUES (?,?,?,?,?,?,?)
    ");
    $stmt2->execute([$inspection_id, $mayor, $brgy, $dti, $bir, $permit, $year]);

    // INSERT FINDINGS
    $no_mayor        = $_POST["no_mayor_permit"]  ?? 0;
    $expired         = $_POST["expired_permit"]   ?? 0;
    $change_nature   = $_POST["change_nature"]    ?? 0;
    $change_address  = $_POST["change_address"]   ?? 0;
    $additional      = $_POST["additional_line"]  ?? 0;
    $others          = $_POST["others"]           ?? "";
    $notice_register = $_POST["notice_register"]  ?? 0;
    $notice_violation= $_POST["notice_violation"] ?? 0;
    $reassessment    = $_POST["reassessment"]     ?? 0;
    $compliance      = $_POST["compliance_days"]  ?? "";
    $referred        = $_POST["referred_to"]      ?? "";
    $action_remarks  = $_POST["action_remarks"]   ?? "";
    $sanitary        = $_POST["sanitary_permit"]  ?? "";
    $fire            = $_POST["fire_cert"]        ?? "";
    $displayed       = $_POST["permit_displayed"] ?? "";

    $stmt3 = $conn->prepare("
        INSERT INTO findings (
            inspection_id, no_mayor_permit, expired_permit, change_nature,
            change_address, additional_line, others, notice_register,
            notice_violation, compliance_days, reassessment, referred_to,
            action_remarks, sanitary_permit, fire_cert, permit_displayed
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt3->execute([
        $inspection_id, $no_mayor, $expired, $change_nature,
        $change_address, $additional, $others, $notice_register,
        $notice_violation, $compliance, $reassessment, $referred,
        $action_remarks, $sanitary, $fire, $displayed
    ]);

    // Log activity
    try {
        $log = $conn->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action, module, description) VALUES (?,?,?,?,'Add','Inspections',?)");
        $log->execute([
            $_SESSION["user_id"]   ?? 0,
            $_SESSION["user"]      ?? "",
            $_SESSION["full_name"] ?? "",
            $_SESSION["role"]      ?? "inspector",
            "Added new inspection for: {$business_name}"
        ]);
    } catch(Exception $e){}

    header("Location: ../../inspections.php");
    exit();
}