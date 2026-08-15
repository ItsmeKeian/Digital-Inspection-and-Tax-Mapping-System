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
    <title>DITMS — Reports</title>
    <link href="assets/img/borlogo.png" rel="icon">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <link href="assets/css/business.css" rel="stylesheet">
    <link href="assets/css/reports.css" rel="stylesheet">
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
        <a href="inspections.php"><i class="fas fa-search"></i> Inspections</a>
        <a href="taxmapping.php"><i class="fas fa-map-marked-alt"></i> Tax Mapping</a>
        <a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a>
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
            <i class="fas fa-chart-bar"></i>
            Reports Overview
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
            <h2>Reports</h2>
            <p>Borongan City, Eastern Samar</p>
        </div>
        <div class="page-actions">
            <button class="btn-action-outline" onclick="exportReports()">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" id="searchReports" placeholder="Search business or owner...">
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
        <select id="filterStatus" class="filter-select">
            <option value="">All Status</option>
            <option value="Inspected">Inspected</option>
            <option value="Pending">Pending</option>
            <option value="Violation">Violation</option>
        </select>
        <div class="filter-date-group">
            <input type="date" id="fromDate" class="filter-select" style="width:150px" title="From date">
            <span style="font-size:12px;color:#9CA3AF">to</span>
            <input type="date" id="toDate" class="filter-select" style="width:150px" title="To date">
        </div>
        <button class="btn-action-gold" onclick="loadReports()">
            <i class="fas fa-filter"></i> Apply
        </button>
        <button class="btn-filter-clear" id="clearFilters">
            <i class="fas fa-times"></i> Clear
        </button>
    </div>

    <!-- Charts -->
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="chart-card">
                <div class="chart-card-header">
                    <div class="chart-title">
                        <div class="chart-title-dot"></div>
                        Monthly Inspections
                    </div>
                    <div class="chart-badge" id="chartYear"></div>
                </div>
                <div style="height:250px">
                    <canvas id="reportLineChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card">
                <div class="chart-card-header">
                    <div class="chart-title">
                        <div class="chart-title-dot"></div>
                        Inspection Status
                    </div>
                </div>
                <div style="height:250px;display:flex;align-items:center;justify-content:center">
                    <canvas id="reportPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Records Table -->
    <div class="table-card">
        <div class="reports-table-header">
            <div style="font-size:14px;font-weight:700;color:#1C1400">Inspection Records</div>
            <div style="font-size:12px;color:#9CA3AF" id="resultCount">Loading...</div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Business</th>
                        <th>Owner</th>
                        <th>Barangay</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="reportTableBody">
                    <tr>
                        <td colspan="6" class="table-empty">
                            <i class="fas fa-chart-bar"></i>
                            <span>Loading reports...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script src="assets/js/jquery-4.0.0.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/reports.js"></script>
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

    // Set current year badge
    document.getElementById("chartYear").textContent = new Date().getFullYear();

    // Clear filters
    document.getElementById("clearFilters").addEventListener("click", function(){
        document.getElementById("searchReports").value = "";
        document.getElementById("filterBarangay").value = "";
        document.getElementById("filterStatus").value = "";
        document.getElementById("fromDate").value = "";
        document.getElementById("toDate").value = "";
        loadReports();
    });
</script>
</body>
</html>