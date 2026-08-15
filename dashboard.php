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
    <title>DITMS — Dashboard</title>
    <link href="assets/img/borlogo.png" rel="icon">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
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
        <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="business.php"><i class="fas fa-store"></i> Businesses</a>
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
            <i class="fas fa-home"></i>
            Dashboard Overview
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

    <div class="page-title">
        <h2>Digital Inspection and Tax Mapping System</h2>
        <p>Borongan City, Eastern Samar</p>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon-wrap gold">
                    <i class="fas fa-store"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-val" id="totalBusinesses">0</div>
                    <div class="stat-label">Total Businesses</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon-wrap green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-val" id="inspectedCount">0</div>
                    <div class="stat-label">Inspected</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon-wrap amber">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-val" id="pendingCount">0</div>
                    <div class="stat-label">Pending Inspection</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon-wrap red">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-val" id="violationsCount">0</div>
                    <div class="stat-label">Violations</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="chart-card">
                <div class="chart-card-header">
                    <div class="chart-title">
                        <div class="chart-title-dot"></div>
                        Monthly Inspections
                    </div>
                    <div class="chart-badge">This Year</div>
                </div>
                <div style="height:270px">
                    <canvas id="lineChart"></canvas>
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
                <div style="height:270px;display:flex;align-items:center;justify-content:center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-3">
        <div class="col-12">
            <div class="chart-card">
                <div class="chart-card-header">
                    <div class="chart-title">
                        <div class="chart-title-dot"></div>
                        Businesses per Barangay
                    </div>
                    <div class="chart-badge">All Barangays</div>
                </div>
                <div style="height:270px">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</main>

<script src="assets/js/jquery-4.0.0.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/dashboard.js"></script>
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
</script>
</body>
</html>