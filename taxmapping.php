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
    <title>DITMS — Tax Mapping</title>
    <link href="assets/img/borlogo.png" rel="icon">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <link href="assets/css/taxmapping.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
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
        <a href="taxmapping.php" class="active"><i class="fas fa-map-marked-alt"></i> Tax Mapping</a>
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
            <i class="fas fa-map-marked-alt"></i>
            Tax Mapping Overview
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
<main class="main-content" style="padding-bottom:0.5rem">

    <div class="page-header-wrap" style="margin-bottom:1rem">
        <div class="page-title">
            <h2>Tax Mapping</h2>
            <p>Borongan City, Eastern Samar</p>
        </div>
    </div>

    <!-- Map Layout -->
    <div class="map-layout">

        <!-- Left Panel -->
        <div class="map-panel">

            <!-- Filters -->
            <div class="map-panel-section">
                <div class="map-panel-label">
                    <i class="fas fa-filter"></i> Filters
                </div>

                <label class="map-field-label">Barangay</label>
                <select id="filterBarangay" class="map-select">
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

                <label class="map-field-label" style="margin-top:0.75rem">Status</label>
                <select id="filterStatus" class="map-select">
                    <option value="">All Status</option>
                    <option value="Existing">Existing</option>
                    <option value="Unregistered">Unregistered</option>
                    <option value="New">New</option>
                    <option value="Closed">Closed</option>
                    <option value="Transferred">Transferred</option>
                </select>

                <label class="map-field-label" style="margin-top:0.75rem">Search</label>
                <div class="map-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="filterSearch" placeholder="Search business...">
                </div>
            </div>

            <!-- Legend -->
            <div class="map-panel-section">
                <div class="map-panel-label">
                    <i class="fas fa-circle-info"></i> Legend
                </div>
                <div class="legend-list">
                    <div class="legend-item"><div class="legend-pin" style="background:#22C55E"></div><span>Existing</span></div>
                    <div class="legend-item"><div class="legend-pin" style="background:#EF4444"></div><span>Unregistered</span></div>
                    <div class="legend-item"><div class="legend-pin" style="background:#3B82F6"></div><span>New</span></div>
                    <div class="legend-item"><div class="legend-pin" style="background:#9CA3AF"></div><span>Closed</span></div>
                    <div class="legend-item"><div class="legend-pin" style="background:#F97316"></div><span>Transferred</span></div>
                    <div class="legend-item"><div class="legend-pin" style="background:#C8960C"></div><span>No Inspection Yet</span></div>
                </div>
            </div>

            <!-- Summary -->
            <div class="map-panel-section">
                <div class="map-panel-label">
                    <i class="fas fa-chart-simple"></i> Summary
                </div>
                <div class="map-stat-box">
                    <span class="map-stat-label">Showing on map</span>
                    <span class="map-stat-val" id="markerCount">0</span>
                </div>
            </div>

        </div>

        <!-- Map -->
        <div class="map-container">
            <div id="map"></div>
        </div>

    </div>

</main>

<script src="assets/js/jquery-4.0.0.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="js/taxmapping.js"></script>
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