<?php
session_start();
if(!isset($_SESSION["user"])){
    header("Location: index.html");
    exit();
}
$role     = $_SESSION["role"]      ?? "admin";
$fullname = $_SESSION["full_name"] ?? $_SESSION["user"] ?? "Administrator";
$initials = strtoupper(substr($fullname, 0, 1));

require "php/dbconnect.php";
try {
    $stmt = $conn->query("SELECT * FROM system_settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(Exception $e){ $settings = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DITMS — Inspections</title>
    <link href="assets/img/borlogo.png" rel="icon">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <link href="assets/css/business.css" rel="stylesheet">
    <link href="assets/css/inspections.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
</head>
<body>

<!-- ── SIDEBAR ── -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <img src="assets/img/borlogo.png" alt="DITMS"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
            <i class="fas fa-map-marked-alt" style="display:none"></i>
        </div>
        <div class="sidebar-brand-text">
            <div class="name">DITMS</div>
            <div class="sub">BPLO — Borongan City</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Main Menu</div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="business.php"><i class="fas fa-store"></i> Businesses</a>
        <a href="inspections.php" class="active"><i class="fas fa-search"></i> Inspections</a>
        <a href="taxmapping.php"><i class="fas fa-map-marked-alt"></i> Tax Mapping</a>
        <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
        <?php if($role === "admin"): ?>
        <div class="sidebar-section-label" style="margin-top:0.5rem">Admin</div>
        <a href="manage_inspectors.php"><i class="fas fa-users"></i> Manage Inspectors</a>
        <a href="activity_logs.php"><i class="fas fa-history"></i> Activity Logs</a>
        <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-bottom">
        <a href="php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<!-- ── TOP HEADER ── -->
<header class="top-header">
    <div style="display:flex;align-items:center;gap:12px">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <div class="header-title">
            <i class="fas fa-search"></i>
            Inspections Overview
        </div>
    </div>
    <div class="dropdown">
        <a class="user-badge dropdown-toggle" href="#" data-bs-toggle="dropdown" style="text-decoration:none">
            <div class="user-avatar">
                <?php if(!empty($settings["logo"])): ?>
                <img src="uploads/<?= htmlspecialchars($settings["logo"]) ?>" alt=""
                     onerror="this.outerHTML='<span><?= $initials ?></span>'">
                <?php else: ?>
                <?= $initials ?>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <div class="uname"><?= htmlspecialchars($fullname) ?></div>
                <div class="urole"><?= $role === "admin" ? "Administrator" : "Inspector" ?></div>
            </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <div style="padding:10px 14px;border-bottom:1px solid #F3F4F6">
                    <div style="font-size:13px;font-weight:600;color:#1C1400"><?= htmlspecialchars($fullname) ?></div>
                    <div style="font-size:11px;color:#9CA3AF;margin-bottom:4px">@<?= htmlspecialchars($_SESSION["user"]) ?></div>
                    <span class="role-badge <?= $role === "admin" ? "role-admin" : "role-inspector" ?>">
                        <?= $role === "admin" ? "Administrator" : "Inspector" ?>
                    </span>
                </div>
            </li>
            <?php if($role === "admin"): ?>
            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2 text-muted"></i>Settings</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider my-1"></li>
            <li><a class="dropdown-item text-danger" href="php/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </div>
</header>

<!-- ── MAIN CONTENT ── -->
<main class="main-content">

    <!-- Page Header -->
    <div class="page-header-wrap">
        <div class="page-title">
            <h2>Inspections</h2>
            <p>Borongan City, Eastern Samar</p>
        </div>
        <div class="page-actions">
            <button class="btn-action-outline" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import"></i> Import Excel
            </button>
            <button class="btn-action-outline" onclick="exportExcel()">
                <i class="fas fa-download"></i> Export
            </button>
            <button class="btn-action-gold" onclick="openAddModal()">
                <i class="fas fa-plus"></i> New Inspection
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInspection" placeholder="Search business or owner...">
        </div>
        <select id="filterBarangay" class="filter-select">
            <option value="">All Barangays</option>
            <option>Alang-alang</option><option>Amantacop</option><option>Ando</option>
            <option>Balacdas</option><option>Balud</option><option>Banuyo</option>
            <option>Baras</option><option>Bato</option><option>Bayobay</option>
            <option>Benowangan</option><option>Bugas</option><option>Cabalagnan</option>
            <option>Cabong</option><option>Cagbonga</option><option>Calico-an</option>
            <option>Calingatngan</option><option>Campesao</option><option>Can-abong</option>
            <option>Can-aga</option><option>Camada</option><option>Canjaway</option>
            <option>Canlaray</option><option>Canyopay</option><option>Divinubo</option>
            <option>Hebacong</option><option>Hindang</option><option>Lalawigan</option>
            <option>Libuton</option><option>Locso-on</option><option>Maybacong</option>
            <option>Maypangdan</option><option>Pepelitan</option><option>Pinanag-an</option>
            <option>Purok A (Poblacion)</option><option>Purok B (Pob.)</option>
            <option>Purok C (Pob.)</option><option>Purok D1 (Pob.)</option>
            <option>Purok D2 (Pob.)</option><option>Purok E (Pob.)</option>
            <option>Purok F (Pob.)</option><option>Purok G (Pob.)</option>
            <option>Purok H (Pob.)</option><option>Punta Maria</option>
            <option>Rawis</option><option>Sabang North</option><option>Sabang South</option>
            <option>San Andres</option><option>San Gabriel</option><option>San Gregorio</option>
            <option>San Jose</option><option>San Mateo</option><option>San Pablo</option>
            <option>San Saturnino</option><option>Santa Fe</option><option>Siha</option>
            <option>Songco</option><option>Sohutan</option><option>Suribao</option>
            <option>Surok</option><option>Taboc</option><option>Tabunan</option>
            <option>Tamoso</option><option>Tormento</option>
        </select>
        <select id="filterType" class="filter-select">
            <option value="">All Types</option>
            <option>Single Proprietorship</option>
            <option>Partnership</option>
            <option>Corporation</option>
            <option>Cooperative</option>
        </select>
        <input type="date" id="filterDate" class="filter-select" style="width:160px" title="Filter by date">
        <button class="btn-filter-clear" id="clearFilters">
            <i class="fas fa-times"></i> Clear
        </button>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Business</th>
                        <th>Owner</th>
                        <th>Barangay</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Findings</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="inspectionTable">
                    <tr>
                        <td colspan="8" class="table-empty">
                            <i class="fas fa-search"></i>
                            <span>Loading inspections...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- ── ADD / EDIT INSPECTION MODAL ── -->
<div class="modal fade" id="addInspectionModal" tabindex="-1">
<div class="modal-dialog modal-xl">
<div class="modal-content">
    <div class="modal-header">
        <div>
            <h5 class="modal-title"><i class="fas fa-clipboard-list me-2" style="color:#C8960C"></i>Inspection Record</h5>
            <p style="font-size:12px;color:#9CA3AF;margin:0">Fill in all inspection details below</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <form method="POST" id="inspectionForm" action="php/create/create_inspection.php">
    <input type="hidden" name="inspection_id" id="inspection_id">
    <input type="hidden" name="business_name" id="business_name">
    <input type="hidden" name="business_id"   id="inspection_business_id">

    <div class="modal-body">

        <!-- GENERAL INFO -->
        <div class="form-section-title">General Information</div>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label-modern">Date of Inspection</label>
                <input type="date" name="date_of_inspection" class="form-control-modern">
            </div>
            <div class="col-md-4">
                <label class="form-label-modern">Time</label>
                <input type="time" name="time_of_inspection" class="form-control-modern">
            </div>
            <div class="col-md-4">
                <label class="form-label-modern">Barangay</label>
                <input type="text" id="barangay" name="barangay" class="form-control-modern" readonly style="background:#F9FAFB">
            </div>
        </div>

        <!-- BUSINESS INFO -->
        <div class="form-section-title">Business Information</div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label-modern">Business Name <span style="color:#DC2626">*</span></label>
                <select id="selectBusiness" class="form-control-modern">
                    <option value="">— Select Business —</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Trade Name (if any)</label>
                <input type="text" name="trade_name" class="form-control-modern" placeholder="Trade name">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Owner's Name</label>
                <input type="text" name="owner_name" id="owner_name" class="form-control-modern" readonly style="background:#F9FAFB">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Contact Number</label>
                <input type="text" name="contact_number" class="form-control-modern" placeholder="Contact number">
            </div>
        </div>

        <!-- REGISTRATION STATUS -->
        <div class="form-section-title">Registration Status</div>
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label-modern">Mayor Permit</label>
                <select name="mayor_permit" class="form-control-modern">
                    <option value="">Select</option><option>Yes</option><option>No</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label-modern">Barangay Clearance</label>
                <select name="barangay_clearance" class="form-control-modern">
                    <option value="">Select</option><option>Yes</option><option>No</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label-modern">DTI / SEC / CDA</label>
                <select name="dti_sec_cda" class="form-control-modern">
                    <option value="">Select</option><option>Yes</option><option>No</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label-modern">BIR Registration</label>
                <select name="bir_registration" class="form-control-modern">
                    <option value="">Select</option><option>Yes</option><option>No</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Permit Number</label>
                <input type="text" name="permit_number" class="form-control-modern" placeholder="Permit number">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Year Last Registered</label>
                <input type="text" name="year_last_registered" class="form-control-modern" placeholder="e.g. 2024">
            </div>
        </div>

        <!-- BUSINESS DETAILS -->
        <div class="form-section-title">Business Details</div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label-modern">Declared Nature of Business</label>
                <textarea name="declared_nature" class="form-control-modern" rows="2" style="height:auto"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Actual Nature of Business</label>
                <textarea name="actual_nature" class="form-control-modern" rows="2" style="height:auto"></textarea>
            </div>
            <div class="col-12">
                <div class="check-group">
                    <label class="check-item">
                        <input type="checkbox" name="activity_matches" value="1">
                        <span>Declared activity matches actual operation</span>
                    </label>
                    <label class="check-item">
                        <input type="checkbox" name="activity_not_match" value="1">
                        <span>Declared activity does NOT match actual operation</span>
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label-modern">PSIC Code</label>
                <input type="text" name="psic_code" class="form-control-modern" placeholder="PSIC code">
            </div>
            <div class="col-md-4">
                <label class="form-label-modern">Type of Business</label>
                <select name="type_of_business" class="form-control-modern">
                    <option value="">Select</option>
                    <option>Single Proprietorship</option>
                    <option>Partnership</option>
                    <option>Corporation</option>
                    <option>Cooperative</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label-modern">Operation Status</label>
                <select name="operation_status" class="form-control-modern">
                    <option value="">Select</option>
                    <option>New</option><option>Existing</option>
                    <option>Unregistered</option><option>Closed</option><option>Transferred</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label-modern">Floor Area (sqm)</label>
                <input type="text" name="floor_area" class="form-control-modern" placeholder="Floor area">
            </div>
            <div class="col-md-4">
                <label class="form-label-modern">Male Employees</label>
                <input type="number" name="male_employees" class="form-control-modern" min="0">
            </div>
            <div class="col-md-4">
                <label class="form-label-modern">Female Employees</label>
                <input type="number" name="female_employees" class="form-control-modern" min="0">
            </div>
        </div>

        <!-- COMPLIANCE -->
        <div class="form-section-title">Compliance Requirements</div>
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label-modern">Sanitary Permit</label>
                <select name="sanitary_permit" class="form-control-modern">
                    <option value="">Select</option><option>Yes</option><option>No</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label-modern">Fire Safety Cert</label>
                <select name="fire_cert" class="form-control-modern">
                    <option value="">Select</option><option>Yes</option><option>No</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label-modern">Mayor's Permit Displayed</label>
                <select name="permit_displayed" class="form-control-modern">
                    <option value="">Select</option><option>Yes</option><option>No</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label-modern">Additional Support Doc</label>
                <select name="additional_support" class="form-control-modern">
                    <option value="">Select</option><option>Yes</option><option>No</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label-modern">Remarks</label>
                <textarea name="remarks" class="form-control-modern" rows="2" style="height:auto"></textarea>
            </div>
        </div>

        <!-- TAX MAPPING FINDINGS -->
        <div class="form-section-title">Tax Mapping Findings <span style="font-weight:400;font-size:11px;color:#9CA3AF">(check all that apply)</span></div>
        <div class="check-group mb-3">
            <label class="check-item"><input type="checkbox" name="no_mayor_permit" value="1"><span>Operating without Mayor's Permit</span></label>
            <label class="check-item"><input type="checkbox" name="expired_permit" value="1"><span>Expired Permit</span></label>
            <label class="check-item"><input type="checkbox" name="change_nature" value="1"><span>Change in business nature</span></label>
            <label class="check-item"><input type="checkbox" name="change_address" value="1"><span>Change in address</span></label>
            <label class="check-item"><input type="checkbox" name="additional_line" value="1"><span>Additional line of business</span></label>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-12">
                <label class="form-label-modern">Others</label>
                <input type="text" name="others" class="form-control-modern" placeholder="Specify other findings">
            </div>
        </div>

        <!-- ACTION TAKEN -->
        <div class="form-section-title">Action Taken / Recommendation</div>
        <div class="check-group mb-3">
            <label class="check-item"><input type="checkbox" name="notice_register" value="1"><span>Notice to register</span></label>
            <label class="check-item"><input type="checkbox" name="notice_violation" value="1"><span>Notice of violation</span></label>
            <label class="check-item"><input type="checkbox" name="reassessment" value="1"><span>For reassessment</span></label>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label-modern">Compliance Days</label>
                <input type="text" name="compliance_days" class="form-control-modern" placeholder="No. of days">
            </div>
            <div class="col-md-4">
                <label class="form-label-modern">Referred To</label>
                <input type="text" name="referred_to" class="form-control-modern" placeholder="Referred to">
            </div>
            <div class="col-md-4">
                <label class="form-label-modern">Remarks</label>
                <textarea name="action_remarks" class="form-control-modern" rows="1" style="height:42px"></textarea>
            </div>
        </div>

        <!-- INSPECTOR -->
        <div class="form-section-title">Inspector / Auditor</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-modern">Inspector / Auditor Name</label>
                <input type="text" name="inspector_name" class="form-control-modern"
                       value="<?= htmlspecialchars($fullname) ?>" placeholder="Inspector name">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Date Signed</label>
                <input type="date" name="date_signed" class="form-control-modern">
            </div>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-modal-save"><i class="fas fa-save me-1"></i>Save Inspection</button>
    </div>
    </form>
</div>
</div>
</div>

<!-- ── IMPORT MODAL ── -->
<div class="modal fade" id="importModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-file-import me-2" style="color:#C8960C"></i>Import Excel</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form action="php/import/import_inspections.php" method="POST" enctype="multipart/form-data">
    <div class="modal-body">
        <label class="form-label-modern">Select Excel File (.xlsx, .csv)</label>
        <input type="file" name="excel_file" class="form-control-modern" accept=".xlsx,.csv" required>
        <div class="import-note" style="margin-top:10px">
            <i class="fas fa-info-circle"></i>
            File must match the inspection record format.
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-modal-save"><i class="fas fa-upload me-1"></i>Import</button>
    </div>
    </form>
</div>
</div>
</div>

<script src="assets/js/jquery-4.0.0.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="js/inspections.js"></script>
<script>
    document.getElementById("sidebarToggle").addEventListener("click", function(){
        document.getElementById("sidebar").classList.toggle("open");
    });
    document.addEventListener("click", function(e){
        var sidebar = document.getElementById("sidebar");
        var toggle  = document.getElementById("sidebarToggle");
        if(window.innerWidth <= 992 && !sidebar.contains(e.target) && !toggle.contains(e.target)){
            sidebar.classList.remove("open");
        }
    });

    // Live filters
    $("#searchInspection, #filterBarangay, #filterType, #filterDate").on("input change", function(){
        loadInspections(
            $("#searchInspection").val(),
            $("#filterBarangay").val(),
            $("#filterType").val(),
            $("#filterDate").val()
        );
    });

    // Clear filters
    $("#clearFilters").on("click", function(){
        $("#searchInspection").val("");
        $("#filterBarangay").val("");
        $("#filterType").val("");
        $("#filterDate").val("");
        loadInspections();
    });
</script>
</body>
</html>