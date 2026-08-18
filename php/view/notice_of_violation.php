<?php
require "../dbconnect.php";

$id = $_GET["id"] ?? "";

if(empty($id)){
    die("No inspection ID provided.");
}

// Get inspection data
$stmt = $conn->prepare("
    SELECT 
        i.*,
        f.no_mayor_permit,
        f.others as violation_others
    FROM inspections i
    LEFT JOIN findings f ON f.inspection_id = i.id
    WHERE i.id = ?
");
$stmt->execute([$id]);
$ins = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$ins){
    die("Record not found.");
}

$date_issued = date("F d, Y");
$owner       = $ins["owner_name"]      ?? "";
$nature      = $ins["declared_nature"] ?? $ins["actual_nature"] ?? "";
$barangay    = $ins["barangay"]        ?? "";
$address     = $barangay . ", Borongan City, Eastern Samar";
$no_permit   = $ins["no_mayor_permit"] == 1;
$others      = $ins["violation_others"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notice of Violation</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Times New Roman', Times, serif;
    font-size: 13px;
    background: #f0f0f0;
    padding: 20px;
}

.print-btn {
    display: block;
    margin: 0 auto 20px;
    padding: 10px 28px;
    background: #1C1400;
    color: #C8960C;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    font-family: 'Segoe UI', sans-serif;
}

.print-btn:hover { background: #2a1e00; }

.paper {
    width: 816px;
    min-height: 1056px;
    margin: 0 auto;
    background: #fff;
    padding: 40px 60px 40px 60px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

/* ── HEADER ── */
.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}

.header-logos {
    display: flex;
    align-items: center;
    gap: 14px;
}

.header-logos img {
    height: 70px;
    width: auto;
    object-fit: contain;
}

.header-center {
    text-align: center;
    flex: 1;
    line-height: 1.5;
    font-size: 12.5px;
}

.header-center .bold-title {
    font-weight: bold;
    font-size: 13px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-right img {
    height: 70px;
    width: auto;
    object-fit: contain;
}

.header-divider {
    border: none;
    border-top: 3px solid #000;
    margin: 8px 0 4px;
}

/* ── DOCUMENT TITLE ── */
.doc-title {
    text-align: center;
    margin: 28px 0 6px;
}

.doc-title h1 {
    font-size: 20px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.doc-title .subtitle {
    font-size: 13px;
    font-style: normal;
}

/* ── FORM FIELDS ── */
.fields-section {
    margin: 22px 0;
}

.field-row {
    display: flex;
    align-items: flex-start;
    margin-bottom: 10px;
    gap: 0;
}

.field-label {
    font-weight: bold;
    font-size: 13px;
    min-width: 230px;
    flex-shrink: 0;
    padding-top: 2px;
}

.field-colon {
    margin-right: 10px;
    font-weight: bold;
    flex-shrink: 0;
    padding-top: 2px;
}

.field-value {
    flex: 1;
    border-bottom: 1px solid #000;
    min-height: 20px;
    padding-bottom: 2px;
    font-size: 13px;
    line-height: 1.4;
}

/* Violation field special */
.violation-section {
    display: flex;
    align-items: flex-start;
    margin-bottom: 10px;
    gap: 0;
}

.violation-label {
    font-weight: bold;
    font-size: 13px;
    min-width: 230px;
    flex-shrink: 0;
    padding-top: 2px;
}

.violation-colon {
    margin-right: 10px;
    font-weight: bold;
    flex-shrink: 0;
    padding-top: 2px;
}

.violation-content {
    flex: 1;
}

.violation-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 6px;
    gap: 6px;
    font-size: 13px;
}

.checkbox-box {
    width: 13px;
    height: 13px;
    border: 1.5px solid #000;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 2px;
    font-size: 11px;
    font-weight: bold;
}

.other-line {
    border-bottom: 1px solid #000;
    flex: 1;
    min-height: 18px;
    margin-left: 4px;
    font-size: 13px;
}

/* ── LEGAL TEXT ── */
.legal-section {
    margin: 18px 0 18px 230px;
    font-size: 13px;
    line-height: 1.7;
}

.legal-section p {
    margin-bottom: 14px;
    text-align: justify;
}

/* ── RECOMMENDATION ── */
.recommendation-section {
    display: flex;
    align-items: flex-start;
    margin: 0 0 20px 0;
    gap: 0;
    font-size: 13px;
}

.rec-label {
    font-weight: bold;
    min-width: 160px;
    flex-shrink: 0;
    padding-top: 2px;
}

.rec-colon {
    margin-right: 10px;
    font-weight: bold;
    flex-shrink: 0;
    padding-top: 2px;
}

.rec-text {
    flex: 1;
    line-height: 1.7;
    text-align: justify;
}

/* ── SIGNATURE ── */
.signature-section {
    margin-top: 40px;
    text-align: center;
    margin-left: auto;
    margin-right: 0;
    width: 300px;
}

.sig-name {
    font-weight: bold;
    font-size: 14px;
    text-transform: uppercase;
    margin-top: 10px;
    border-top: 1.5px solid #000;
    padding-top: 6px;
}

.sig-title {
    font-size: 13px;
}

/* ── FOOTER ── */
.footer-section {
    margin-top: 50px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    font-size: 12.5px;
}

.copy-furnished .cf-title { font-weight: bold; }
.copy-furnished ul {
    list-style: none;
    padding-left: 12px;
    margin-top: 2px;
}

.received-by {
    text-align: left;
}

.received-line {
    border-bottom: 1.5px solid #000;
    width: 220px;
    margin-top: 4px;
    height: 18px;
}

/* ── PRINT ── */
@media print {
    body { background: none; padding: 0; }
    .print-btn { display: none; }
    .paper {
        box-shadow: none;
        padding: 30px 50px;
        margin: 0;
        width: 100%;
    }
}
</style>
</head>
<body>

<button class="print-btn" onclick="window.print()">
    &#128438; Print Notice of Violation
</button>

<div class="paper">

    <!-- HEADER -->
    <div class="header">
        <div class="header-logos">
            <img src="../../assets/img/borlogo.png" alt="Borongan City Seal"
                 onerror="this.style.display='none'">
        </div>
        <div class="header-center">
            Republic of the Philippines<br>
            Province of Eastern Samar<br>
            City of Borongan<br>
            <span class="bold-title">OFFICE OF THE CITY MAYOR</span><br>
            <strong>BUSINESS PERMIT AND LICENSING OFFICE</strong>
        </div>
        <div class="header-right">
            <img src="../../assets/img/bagongph.webp" alt="Bagong Pilipinas"
                 onerror="this.style.display='none'">
        </div>
    </div>

    <hr class="header-divider">

    <!-- DOCUMENT TITLE -->
    <div class="doc-title">
        <h1>Notice of Violation</h1>
        <div class="subtitle">(1<sup>st</sup> Notice)</div>
    </div>

    <!-- FORM FIELDS -->
    <div class="fields-section">

        <div class="field-row">
            <div class="field-label">DATE ISSUED</div>
            <div class="field-colon">:</div>
            <div class="field-value"><?= htmlspecialchars($date_issued) ?></div>
        </div>

        <div class="field-row">
            <div class="field-label">BUSINESS OWNER/OPERATOR</div>
            <div class="field-colon">:</div>
            <div class="field-value"><?= htmlspecialchars($owner) ?></div>
        </div>

        <div class="field-row">
            <div class="field-label">NATURE OF BUSINESS</div>
            <div class="field-colon">:</div>
            <div class="field-value"><?= htmlspecialchars($nature) ?></div>
        </div>

        <div class="field-row">
            <div class="field-label">BUSINESS ADDRESS</div>
            <div class="field-colon">:</div>
            <div class="field-value"><?= htmlspecialchars($address) ?></div>
        </div>

        <!-- VIOLATION/S -->
        <div class="violation-section">
            <div class="violation-label">VIOLATION/S</div>
            <div class="violation-colon">:</div>
            <div class="violation-content">
                <div class="violation-item">
                    <div class="checkbox-box"><?= $no_permit ? "✓" : "" ?></div>
                    <span>Operating without Business/Mayor's Permit</span>
                </div>
                <div class="violation-item">
                    <div class="checkbox-box"><?= !empty($others) ? "✓" : "" ?></div>
                    <span>Other Violation/s:</span>
                    <div class="other-line"><?= htmlspecialchars($others) ?></div>
                </div>
                <div style="border-bottom:1px solid #000;margin-top:4px;height:18px"></div>
            </div>
        </div>

    </div>

    <!-- LEGAL TEXT -->
    <div class="legal-section">
        <p>
            <strong>Section 3A.01</strong> of City Ordinance No. 246, series of 2022, otherwise
            known as "Borongan City Local Revenue Code of 2022, provides that
            <strong>"All persons are required to obtain a Mayor's Permit for the
            privilege of conducting business within the city."</strong>
        </p>
        <p>
            <strong>Section 8.01</strong> of the same code provides that "Violation of any of the
            provisions of this ordinance shall upon conviction, be punished by a
            fine of Five Thousand (Php 5,000.00) pesos or imprisonment of one
            (1) year or at the discretion of the court."
        </p>
    </div>

    <!-- RECOMMENDATION -->
    <div class="recommendation-section">
        <div class="rec-label">RECOMMENDATION</div>
        <div class="rec-colon">:</div>
        <div class="rec-text">
            In pursuance with the above-cited ordinance, you are hereby advised
            to secure the necessary permit for the privilege of doing business in
            the city within seven (7) working days from receipt hereof.
            <br><br>
            Failure to comply within the given period will be dealt with
            accordingly.
        </div>
    </div>

    <!-- SIGNATURE -->
    <div style="display:flex;justify-content:flex-end">
        <div class="signature-section">
            <div style="height:55px"></div>
            <div class="sig-name">LOIS BIANCA A. LIMBAUAN-AGDA</div>
            <div class="sig-title">BPLO-Section Head</div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer-section">
        <div class="copy-furnished">
            <div class="cf-title">Copy Furnished:</div>
            <ul>
                <li>Office of the City Mayor</li>
                <li>City Legal Officer</li>
                <li>File</li>
            </ul>
        </div>
        <div class="received-by">
            <div>Received by:</div>
            <div class="received-line"></div>
        </div>
    </div>

</div>

</body>
</html>