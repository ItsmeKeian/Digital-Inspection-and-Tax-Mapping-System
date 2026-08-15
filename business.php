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
    <title>DITMS — Businesses</title>
    <link href="assets/img/borlogo.png" rel="icon">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <link href="assets/css/business.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
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
        <a href="business.php" class="active"><i class="fas fa-store"></i> Businesses</a>
        <a href="inspections.php"><i class="fas fa-search"></i> Inspections</a>
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
            <i class="fas fa-store"></i>
            Business Overview
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
            <h2>Businesses</h2>
            <p>Borongan City, Eastern Samar</p>
        </div>
        <div class="page-actions">
            <button class="btn-action-outline" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import"></i> Import Excel
            </button>
            <a href="php/export/export_business.php" class="btn-action-outline">
                <i class="fas fa-download"></i> Export
            </a>
            <button class="btn-action-gold" data-bs-toggle="modal" data-bs-target="#addBusinessModal">
                <i class="fas fa-plus"></i> New Business
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" id="searchBusiness" placeholder="Search business or owner...">
        </div>
        <select id="filterStatus" class="filter-select">
            <option value="">All Status</option>
            <option value="inspected">Inspected</option>
            <option value="pending">Pending</option>
        </select>
        <input type="date" id="filterDate" class="filter-select" style="width:160px" title="Filter by date added">
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
        <button class="btn-filter-clear" id="clearFilters" title="Clear all filters">
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
                        <th>Status</th>
                        <th>Date Added</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="businessTableBody">
                    <tr>
                        <td colspan="6" class="table-empty">
                            <i class="fas fa-store"></i>
                            <span>Loading businesses...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrap" id="businessPaginationWrap" style="display:none">
        <div style="font-size:13px;color:#6B7280" id="businessPageInfo"></div>
        <div class="pagination-btns">
            <button class="page-btn" id="businessPrevBtn" onclick="changeBusinessPage(-1)">
                <i class="fas fa-chevron-left"></i> Previous
            </button>
            <div class="page-numbers" id="businessPageNumbers"></div>
            <button class="page-btn" id="businessNextBtn" onclick="changeBusinessPage(1)">
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

</main>

<!-- ── ADD BUSINESS MODAL ── -->
<div class="modal fade" id="addBusinessModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
    <div class="modal-header">
        <div>
            <h5 class="modal-title"><i class="fas fa-plus-circle me-2" style="color:#C8960C"></i>Add Business</h5>
            <p style="font-size:12px;color:#9CA3AF;margin:0">Fill in the business details below</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form method="POST" action="php/create/create_business.php">
    <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-modern">Business Name <span class="text-danger">*</span></label>
                <input type="text" name="business_name" class="form-control-modern" required placeholder="Enter business name">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Owner Name</label>
                <input type="text" name="owner_name" class="form-control-modern" placeholder="Enter owner name">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Barangay</label>
                <select name="barangay" class="form-control-modern">
                    <option value="">Select Barangay</option>
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
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Contact Number</label>
                <input type="text" name="contact_number" class="form-control-modern" placeholder="e.g. 09xxxxxxxxx">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Latitude</label>
                <input type="text" name="latitude" id="lat_business" class="form-control-modern" readonly placeholder="Click Pick Location">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Longitude</label>
                <input type="text" name="longitude" id="lng_business" class="form-control-modern" readonly placeholder="Click Pick Location">
            </div>
            <div class="col-12">
                <button type="button" class="btn-pick-location" onclick="openMapModal('business')">
                    <i class="fas fa-map-pin"></i> Pick Location on Map
                </button>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-modal-save"><i class="fas fa-save me-1"></i>Save Business</button>
    </div>
    </form>
</div>
</div>
</div>

<!-- ── MAP PICKER MODAL ── -->
<div class="modal fade" id="mapModal" tabindex="-1">
<div class="modal-dialog modal-xl">
<div class="modal-content">
    <div class="modal-header">
        <div>
            <h5 class="modal-title"><i class="fas fa-map-pin me-2" style="color:#C8960C"></i>Select Location</h5>
            <p style="font-size:12px;color:#9CA3AF;margin:0">Click on the map to set the business location</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body p-0">
        <div id="mapPicker" style="height:500px;width:100%"></div>
    </div>
    <div class="modal-footer">
        <button class="btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
        <button class="btn-modal-save" data-bs-dismiss="modal"><i class="fas fa-check me-1"></i>Done</button>
    </div>
</div>
</div>
</div>

<!-- ── EDIT BUSINESS MODAL ── -->
<div class="modal fade" id="editModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-edit me-2" style="color:#C8960C"></i>Edit Business</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" id="edit_id">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label-modern">Business Name</label>
                <input type="text" id="edit_business_name" class="form-control-modern">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Owner Name</label>
                <input type="text" id="edit_owner_name" class="form-control-modern">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Barangay</label>
                <input type="text" id="edit_barangay" class="form-control-modern">
            </div>
            <div class="col-12">
                <label class="form-label-modern">Contact Number</label>
                <input type="text" id="edit_contact" class="form-control-modern">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
        <button class="btn-modal-save" onclick="updateBusiness()"><i class="fas fa-save me-1"></i>Save Changes</button>
    </div>
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
    <form action="php/import/import_business.php" method="POST" enctype="multipart/form-data">
    <div class="modal-body">
        <label class="form-label-modern">Select Excel File (.xlsx, .csv)</label>
        <input type="file" name="excel_file" class="form-control-modern" accept=".xlsx,.csv" required>
        <div class="import-note">
            <i class="fas fa-info-circle"></i>
            File must have columns: business_name, owner_name, barangay, contact_number
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
<script src="js/business.js"></script>
<script src="js/mapPicker.js"></script>
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
    $("#searchBusiness, #filterStatus, #filterBarangay").on("input change", function(){
        loadBusinesses(
            $("#searchBusiness").val(),
            $("#filterStatus").val(),
            $("#filterBarangay").val()
        );
    });
</script>
</body>
</html>