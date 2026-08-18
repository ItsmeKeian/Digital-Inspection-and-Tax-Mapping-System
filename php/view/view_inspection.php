<?php
require "../dbconnect.php";

$id = $_GET["id"] ?? "";

if(empty($id)){
    die("No inspection ID provided.");
}

$stmt = $conn->prepare("SELECT * FROM inspections WHERE id = ?");
$stmt->execute([$id]);
$ins = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$ins){ die("Record not found."); }

$stmt2 = $conn->prepare("SELECT * FROM registration_status WHERE inspection_id = ?");
$stmt2->execute([$id]);
$reg = $stmt2->fetch(PDO::FETCH_ASSOC);

$stmt3 = $conn->prepare("SELECT * FROM findings WHERE inspection_id = ?");
$stmt3->execute([$id]);
$find = $stmt3->fetch(PDO::FETCH_ASSOC);

function chk($val){ return $val ? "&#10003;" : ""; }
function ckVal($val, $opt){ return ($val === $opt) ? "&#10003;" : ""; }
function ckYN($val, $yn){ return (strtolower($val ?? "") === strtolower($yn)) ? "&#10003;" : ""; }
function ckBit($val){ return ($val == 1) ? "&#10003;" : ""; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Business Permit Tax Mapping</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11.5px;
    background: #e0e0e0;
    padding: 20px;
}

.print-btn {
    display: block;
    margin: 0 auto 16px;
    padding: 9px 26px;
    background: #1C1400;
    color: #C8960C;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    font-family: 'Segoe UI', sans-serif;
}

.print-btn:hover { background: #2a1e00; }

.paper {
    width: 816px;
    margin: 0 auto;
    background: #fff;
    padding: 28px 32px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

/* ── HEADER ── */
.doc-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 10px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-left img {
    height: 55px;
    width: auto;
    object-fit: contain;
}

.header-center {
    font-size: 11.5px;
    line-height: 1.5;
}

.header-right {
    border: 1px solid #000;
    padding: 5px 10px;
    font-size: 10.5px;
    font-weight: bold;
    text-align: center;
    line-height: 1.4;
}

/* ── TWO COLUMN LAYOUT ── */
.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0 20px;
}

/* ── SECTION TITLE ── */
.sec-title {
    font-weight: bold;
    font-size: 12px;
    border-bottom: 1.5px solid #000;
    padding-bottom: 2px;
    margin: 10px 0 6px;
}

/* ── FIELD LINE ── */
.field-line {
    display: flex;
    align-items: baseline;
    margin-bottom: 5px;
    font-size: 11.5px;
    gap: 4px;
}

.field-lbl {
    flex-shrink: 0;
    white-space: nowrap;
}

.field-val {
    flex: 1;
    border-bottom: 1px solid #000;
    min-height: 16px;
    padding-left: 3px;
    font-size: 11.5px;
}

/* ── REGISTRATION TABLE ── */
.reg-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 6px;
    font-size: 11.5px;
}

.reg-table th {
    font-weight: normal;
    text-align: center;
    padding: 2px 4px;
    font-size: 11px;
}

.reg-table td {
    padding: 2px 4px;
    vertical-align: middle;
}

.reg-table td:first-child { min-width: 110px; }

/* ── CHECKBOX ── */
.cb {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 12px;
    height: 12px;
    border: 1px solid #000;
    font-size: 9px;
    font-weight: bold;
    flex-shrink: 0;
    vertical-align: middle;
    margin-right: 3px;
    line-height: 1;
}

/* ── TWO COLUMNS CHECKBOXES ── */
.cb-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3px 10px;
    font-size: 11.5px;
}

.cb-item {
    display: flex;
    align-items: center;
    gap: 3px;
}

/* ── REMARKS LINES ── */
.remark-lines {
    margin-top: 3px;
}

.remark-line {
    border-bottom: 1px solid #000;
    height: 16px;
    margin-bottom: 4px;
}

/* ── DIVIDER ── */
.divider {
    border: none;
    border-top: 1.5px solid #000;
    margin: 10px 0;
}

/* ── PRINT ── */
@media print {
    body { background: none; padding: 0; }
    .print-btn { display: none; }
    .paper {
        box-shadow: none;
        padding: 20px 26px;
        width: 100%;
        margin: 0;
    }
}
</style>
</head>
<body>

<button class="print-btn" onclick="window.print()">&#128438; Print Form</button>

<div class="paper">

    <!-- HEADER -->
    <div class="doc-header">
        <div class="header-left">
            <img src="../../assets/img/borlogo.png" alt="Borongan Seal"
                 onerror="this.style.display='none'">
            <img src="../../assets/img/bagongph.webp" alt="PH Seal"
                 onerror="this.style.display='none'">
            <div class="header-center">
                Republic of the Philippines<br>
                Province of Eastern Samar<br>
                <strong>CITY OF BORONGAN</strong>
            </div>
        </div>
        <div class="header-right">
            OFFICE OF THE CITY BUSINESS<br>PERMITS AND LICENSING
        </div>
    </div>

    <!-- TITLE -->
    <div style="font-size:18px;font-weight:bold;margin:8px 0 2px">
        BUSINESS PERMIT<br>TAX MAPPING
    </div>

    <!-- TWO COLUMN BODY -->
    <div class="two-col">

        <!-- LEFT COLUMN -->
        <div>
            <div class="sec-title">GENERAL INFORMATION</div>
            <div class="field-line"><span class="field-lbl">Date of inspection:</span><span class="field-val"><?= htmlspecialchars($ins["date_of_inspection"] ?? "") ?></span></div>
            <div class="field-line"><span class="field-lbl">Time:</span><span class="field-val"><?= htmlspecialchars($ins["time_of_inspection"] ?? "") ?></span></div>
            <div class="field-line"><span class="field-lbl">Barangay:</span><span class="field-val"><?= htmlspecialchars($ins["barangay"] ?? "") ?></span></div>

            <div class="sec-title" style="margin-top:8px">BUSINESS INFORMATION</div>
            <div class="field-line"><span class="field-lbl">Business Name:</span><span class="field-val"><?= htmlspecialchars($ins["business_name"] ?? "") ?></span></div>
            <div class="field-line"><span class="field-lbl">Trade Name (if any):</span><span class="field-val"><?= htmlspecialchars($ins["trade_name"] ?? "") ?></span></div>
            <div class="field-line"><span class="field-lbl">Owner's Name:</span><span class="field-val"><?= htmlspecialchars($ins["owner_name"] ?? "") ?></span></div>
            <div class="field-line"><span class="field-lbl">Contact Number:</span><span class="field-val"><?= htmlspecialchars($ins["contact_number"] ?? "") ?></span></div>

            <div class="sec-title" style="margin-top:8px">REGISTRATION STATUS</div>
            <table class="reg-table">
                <tr>
                    <th></th>
                    <th>Yes</th>
                    <th>No</th>
                    <th>Remarks</th>
                </tr>
                <tr>
                    <td>Mayor's Permit</td>
                    <td style="text-align:center"><span class="cb"><?= ckYN($reg["mayor_permit"] ?? "", "yes") ?></span></td>
                    <td style="text-align:center"><span class="cb"><?= ckYN($reg["mayor_permit"] ?? "", "no") ?></span></td>
                    <td style="border-bottom:1px solid #000;min-width:80px"></td>
                </tr>
                <tr>
                    <td>Barangay Clearance</td>
                    <td style="text-align:center"><span class="cb"><?= ckYN($reg["barangay_clearance"] ?? "", "yes") ?></span></td>
                    <td style="text-align:center"><span class="cb"><?= ckYN($reg["barangay_clearance"] ?? "", "no") ?></span></td>
                    <td style="border-bottom:1px solid #000"></td>
                </tr>
                <tr>
                    <td>DTI/SEC/CDA</td>
                    <td style="text-align:center"><span class="cb"><?= ckYN($reg["dti_sec_cda"] ?? "", "yes") ?></span></td>
                    <td style="text-align:center"><span class="cb"><?= ckYN($reg["dti_sec_cda"] ?? "", "no") ?></span></td>
                    <td style="border-bottom:1px solid #000"></td>
                </tr>
                <tr>
                    <td>BIR Registration</td>
                    <td style="text-align:center"><span class="cb"><?= ckYN($reg["bir_registration"] ?? "", "yes") ?></span></td>
                    <td style="text-align:center"><span class="cb"><?= ckYN($reg["bir_registration"] ?? "", "no") ?></span></td>
                    <td style="border-bottom:1px solid #000"></td>
                </tr>
            </table>
            <div class="field-line"><span class="field-lbl">Permit Number (if available):</span><span class="field-val"><?= htmlspecialchars($reg["permit_number"] ?? "") ?></span></div>
            <div class="field-line"><span class="field-lbl">Year Last Registered:</span><span class="field-val"><?= htmlspecialchars($reg["year_last_registered"] ?? "") ?></span></div>

            <div class="sec-title" style="margin-top:8px">BUSINESS DETAILS</div>
            <div style="font-size:11.5px;margin-bottom:3px">Declared Nature of Business:</div>
            <div style="border-bottom:1px solid #000;min-height:16px;margin-bottom:3px;padding-left:3px"><?= htmlspecialchars($ins["declared_nature"] ?? "") ?></div>
            <div style="border-bottom:1px solid #000;min-height:16px;margin-bottom:8px"></div>

            <div style="font-size:11.5px;margin-bottom:3px">Actual Nature of Business:</div>
            <div style="border-bottom:1px solid #000;min-height:16px;margin-bottom:3px;padding-left:3px"><?= htmlspecialchars($ins["actual_nature"] ?? "") ?></div>
            <div style="border-bottom:1px solid #000;min-height:16px;margin-bottom:8px"></div>

            <div style="margin-bottom:3px">
                <span class="cb"><?= ckBit($ins["activity_matches"] ?? 0) ?></span> Declared activity matches actual operation
            </div>
            <div style="margin-bottom:6px">
                <span class="cb"><?= ckBit($ins["activity_not_match"] ?? 0) ?></span> Declared activity does NOT match actual operation
            </div>
            <div class="field-line"><span class="field-lbl">PSIC Code (if known):</span><span class="field-val"><?= htmlspecialchars($ins["psic_code"] ?? "") ?></span></div>

            <div style="margin-top:8px;font-weight:bold;font-size:11.5px">Type of Business:</div>
            <div class="cb-grid" style="margin:4px 0">
                <div class="cb-item"><span class="cb"><?= ckVal($ins["type_of_business"] ?? "", "Single Proprietorship") ?></span> Single Proprietorship</div>
                <div class="cb-item"><span class="cb"><?= ckVal($ins["type_of_business"] ?? "", "Corporation") ?></span> Corporation</div>
                <div class="cb-item"><span class="cb"><?= ckVal($ins["type_of_business"] ?? "", "Partnership") ?></span> Partnership</div>
                <div class="cb-item"><span class="cb"><?= ckVal($ins["type_of_business"] ?? "", "Cooperative") ?></span> Cooperative</div>
            </div>

            <div style="font-weight:bold;font-size:11.5px;margin-top:6px">Business Operation Status:</div>
            <div class="cb-grid" style="margin:4px 0">
                <div class="cb-item"><span class="cb"><?= ckVal($ins["operation_status"] ?? "", "New") ?></span> New</div>
                <div class="cb-item"><span class="cb"><?= ckVal($ins["operation_status"] ?? "", "Unregistered") ?></span> Unregistered</div>
                <div class="cb-item"><span class="cb"><?= ckVal($ins["operation_status"] ?? "", "Existing") ?></span> Existing</div>
                <div class="cb-item"><span class="cb"><?= ckVal($ins["operation_status"] ?? "", "Closed") ?></span> Closed</div>
            </div>
            <div style="margin:3px 0" class="field-line">
                <span class="cb"><?= ckVal($ins["operation_status"] ?? "", "Transferred") ?></span>
                <span class="field-lbl">Transferred:</span>
                <span class="field-val"></span>
            </div>

            <div style="font-weight:bold;font-size:11.5px;margin-top:6px">Physical &amp; Operational Data</div>
            <div class="field-line"><span class="field-lbl">Floor Area (sqm):</span><span class="field-val"><?= htmlspecialchars($ins["floor_area"] ?? "") ?></span></div>

            <div style="font-weight:bold;font-size:11.5px;margin-top:6px">Number of Employees:</div>
            <div style="display:flex;gap:20px;margin:4px 0;font-size:11.5px">
                <div class="field-line"><span class="field-lbl">Female:</span><span class="field-val" style="min-width:50px"><?= htmlspecialchars($ins["female_employees"] ?? "") ?></span></div>
                <div class="field-line"><span class="field-lbl">Male:</span><span class="field-val" style="min-width:50px"><?= htmlspecialchars($ins["male_employees"] ?? "") ?></span></div>
            </div>

            <div style="font-weight:bold;font-size:11.5px;margin-top:6px">Compliance with other requirements</div>
            <div style="font-size:11.5px;margin:4px 0">
                <div style="margin-bottom:4px">
                    Sanitary Permit: &nbsp;
                    <span class="cb"><?= ckYN($find["sanitary_permit"] ?? "", "yes") ?></span> YES &nbsp;
                    <span class="cb"><?= ckYN($find["sanitary_permit"] ?? "", "no") ?></span> NO
                </div>
                <div style="margin-bottom:4px">
                    Fire Safety Inspection Cert: &nbsp;
                    <span class="cb"><?= ckYN($find["fire_cert"] ?? "", "yes") ?></span> YES &nbsp;
                    <span class="cb"><?= ckYN($find["fire_cert"] ?? "", "no") ?></span> NO
                </div>
                <div>
                    Mayor's Permit Displayed: &nbsp;
                    <span class="cb"><?= ckYN($find["permit_displayed"] ?? "", "yes") ?></span> YES &nbsp;
                    <span class="cb"><?= ckYN($find["permit_displayed"] ?? "", "no") ?></span> NO
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div>
            <div style="font-size:11.5px;margin-bottom:4px">
                Additional Support Doc: &nbsp;
                <span class="cb"><?= ckYN($ins["additional_support"] ?? "", "yes") ?></span> YES &nbsp;
                <span class="cb"><?= ckYN($ins["additional_support"] ?? "", "no") ?></span> NO
            </div>

            <div style="font-size:11.5px;margin-bottom:3px">Remarks:</div>
            <div class="remark-lines">
                <?php
                $remarks = $ins["remarks"] ?? "";
                $lines = $remarks ? explode("\n", wordwrap($remarks, 38, "\n", true)) : [];
                for($i = 0; $i < 7; $i++){
                    $v = $lines[$i] ?? "";
                    echo '<div class="remark-line" style="padding-left:3px">' . htmlspecialchars($v) . '</div>';
                }
                ?>
            </div>

            <hr class="divider">

            <div class="sec-title">TAX MAPPING FINDINGS</div>
            <div style="font-size:11px;margin-bottom:5px">(check all that apply)</div>
            <div style="font-size:11.5px;padding-left:14px">
                <div style="margin-bottom:4px"><span class="cb"><?= ckBit($find["no_mayor_permit"] ?? 0) ?></span> Operating without Mayor's Permit</div>
                <div style="margin-bottom:4px"><span class="cb"><?= ckBit($find["expired_permit"] ?? 0) ?></span> Expired Permit</div>
                <div style="margin-bottom:4px"><span class="cb"><?= ckBit($find["change_nature"] ?? 0) ?></span> Change in business nature not declared</div>
                <div style="margin-bottom:4px"><span class="cb"><?= ckBit($find["change_address"] ?? 0) ?></span> Change in business address not declared</div>
                <div style="margin-bottom:4px"><span class="cb"><?= ckBit($find["additional_line"] ?? 0) ?></span> additional line of business not declared</div>
                <div class="field-line"><span class="cb"><?= chk($find["others"] ?? "") ?></span><span class="field-lbl">Others:</span><span class="field-val"><?= htmlspecialchars($find["others"] ?? "") ?></span></div>
            </div>

            <hr class="divider">

            <div class="sec-title">ACTION TAKEN / RECOMMENDATION</div>
            <div style="font-size:11.5px;padding-left:4px">
                <div style="margin-bottom:4px"><span class="cb"><?= ckBit($find["notice_register"] ?? 0) ?></span> Issued notice to register</div>
                <div style="margin-bottom:4px"><span class="cb"><?= ckBit($find["notice_violation"] ?? 0) ?></span> Issued notice of violation</div>
                <div style="margin-bottom:4px;display:flex;align-items:center;gap:4px">
                    <span class="cb"></span> For compliance within
                    <span style="border-bottom:1px solid #000;min-width:50px;display:inline-block;padding-left:4px"><?= htmlspecialchars($find["compliance_days"] ?? "") ?></span> days
                </div>
                <div style="margin-bottom:4px"><span class="cb"><?= ckBit($find["reassessment"] ?? 0) ?></span> For reassessment</div>
                <div class="field-line" style="margin-bottom:4px">
                    <span class="cb"></span>
                    <span class="field-lbl">Referred to</span>
                    <span class="field-val"><?= htmlspecialchars($find["referred_to"] ?? "") ?></span>
                </div>
                <div style="margin-bottom:3px">Remarks:</div>
                <div style="border-bottom:1px solid #000;min-height:16px;margin-bottom:4px;padding-left:3px"><?= htmlspecialchars($find["action_remarks"] ?? "") ?></div>
                <div style="border-bottom:1px solid #000;min-height:16px;margin-bottom:4px"></div>
            </div>

            <hr class="divider">

            <div style="font-size:11.5px;margin-bottom:30px">INSPECTOR / AUDITOR SIGNATURE:</div>
            <div style="border-bottom:1.5px solid #000;margin-bottom:8px"></div>
            <div class="field-line" style="margin-top:10px">
                <span class="field-lbl">Date:</span>
                <span class="field-val"><?= htmlspecialchars($ins["date_signed"] ?? "") ?></span>
            </div>
        </div>

    </div>

</div>

</body>
</html>