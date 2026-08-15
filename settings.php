<?php
session_start();
if(!isset($_SESSION["user"])){
    header("Location: index.html");
    exit();
}
if($_SESSION["role"] !== "admin"){
    header("Location: dashboard.php");
    exit();
}
$role     = "admin";
$fullname = $_SESSION["full_name"] ?? $_SESSION["user"] ?? "Administrator";
$username = $_SESSION["user"] ?? "";
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
    <title>DITMS — Settings</title>
    <link href="assets/img/borlogo.png" rel="icon">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <link href="assets/css/business.css" rel="stylesheet">
    <link href="assets/css/settings.css" rel="stylesheet">
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
        <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
        <div class="sidebar-section-label" style="margin-top:0.5rem">Admin</div>
        <a href="manage_inspectors.php"><i class="fas fa-users"></i> Manage Inspectors</a>
        <a href="activity_logs.php"><i class="fas fa-history"></i> Activity Logs</a>
        <a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a>
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
            <i class="fas fa-cog"></i>
            Settings
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
                <div class="urole">Administrator</div>
            </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <div style="padding:10px 14px;border-bottom:1px solid #F3F4F6">
                    <div style="font-size:13px;font-weight:600;color:#1C1400"><?= htmlspecialchars($fullname) ?></div>
                    <div style="font-size:11px;color:#9CA3AF;margin-bottom:4px">@<?= htmlspecialchars($username) ?></div>
                    <span class="role-badge role-admin">Administrator</span>
                </div>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <li><a class="dropdown-item text-danger" href="php/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </div>
</header>

<!-- ── MAIN CONTENT ── -->
<main class="main-content">

    <div class="page-header-wrap" style="margin-bottom:1.5rem">
        <div class="page-title">
            <h2>Settings</h2>
            <p>System configuration and account management</p>
        </div>
    </div>

    <!-- Top Banner -->
    <div class="settings-banner">
        <div class="settings-banner-left">
            <div class="settings-banner-avatar">
                <?php if(!empty($settings["logo"])): ?>
                <img src="uploads/<?= htmlspecialchars($settings["logo"]) ?>" alt="Logo">
                <?php else: ?>
                <i class="fas fa-map-marked-alt"></i>
                <?php endif; ?>
            </div>
            <div>
                <div class="settings-banner-title">
                    <?= htmlspecialchars($settings["municipality"] ?? "Borongan City") ?>
                </div>
                <div class="settings-banner-sub">
                    <?= htmlspecialchars($settings["province"] ?? "Eastern Samar") ?> &nbsp;·&nbsp;
                    Digital Inspection and Tax Mapping System
                </div>
            </div>
        </div>
        <div class="settings-banner-right">
            <div class="banner-stat">
                <div class="banner-stat-val">v1.0</div>
                <div class="banner-stat-label">Version</div>
            </div>
            <div class="banner-divider"></div>
            <div class="banner-stat">
                <div class="banner-stat-val">BPLO</div>
                <div class="banner-stat-label">Office</div>
            </div>
            <div class="banner-divider"></div>
            <div class="banner-stat">
                <div class="banner-stat-val"><?= date("Y") ?></div>
                <div class="banner-stat-label">Year</div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        <!-- LEFT COLUMN -->
        <div class="col-lg-7">

            <!-- System Information -->
            <div class="settings-card mb-3">
                <div class="settings-section-header">
                    <div class="settings-section-icon gold">
                        <i class="fas fa-building"></i>
                    </div>
                    <div>
                        <div class="settings-section-title">System Information</div>
                        <div class="settings-section-sub">Update your municipality details and logo</div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label-modern">Municipality Name</label>
                        <input type="text" id="municipality" class="form-control-modern"
                               placeholder="e.g. Borongan City">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-modern">Province</label>
                        <input type="text" id="province" class="form-control-modern"
                               placeholder="e.g. Eastern Samar">
                    </div>
                </div>

                <label class="form-label-modern">Municipality Logo</label>
                <div class="logo-upload-zone" id="logoUploadArea"
                     onclick="document.getElementById('logoInput').click()"
                     ondragover="event.preventDefault();this.classList.add('dragover')"
                     ondragleave="this.classList.remove('dragover')"
                     ondrop="handleLogoDrop(event)">
                    <img id="logoPreview" src="" alt="Logo Preview"
                         style="max-width:100px;max-height:100px;display:none;border-radius:10px;object-fit:contain;margin-bottom:.75rem">
                    <div id="uploadPlaceholder">
                        <div class="upload-icon-wrap">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-title">Click to upload or drag & drop</div>
                        <div class="upload-sub">PNG, JPG, SVG up to 2MB</div>
                    </div>
                    <input type="file" id="logoInput" style="display:none" accept="image/*">
                </div>

                <div id="systemMsg" class="settings-msg" style="display:none"></div>

                <button class="btn-settings-save" onclick="saveSystem()">
                    <i class="fas fa-save"></i> Save System Information
                </button>
            </div>

            <!-- About System -->
            <div class="settings-card">
                <div class="settings-section-header">
                    <div class="settings-section-icon blue">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <div class="settings-section-title">About the System</div>
                        <div class="settings-section-sub">DITMS system information</div>
                    </div>
                </div>
                <div class="about-list">
                    <div class="about-item">
                        <div class="about-label"><i class="fas fa-desktop"></i> System Name</div>
                        <div class="about-val">Digital Inspection and Tax Mapping System</div>
                    </div>
                    <div class="about-item">
                        <div class="about-label"><i class="fas fa-tag"></i> Version</div>
                        <div class="about-val"><span class="version-badge">v1.0.0</span></div>
                    </div>
                    <div class="about-item">
                        <div class="about-label"><i class="fas fa-building"></i> Office</div>
                        <div class="about-val">Business Permits and Licensing Office</div>
                    </div>
                    <div class="about-item">
                        <div class="about-label"><i class="fas fa-map-pin"></i> Location</div>
                        <div class="about-val">Borongan City, Eastern Samar, Philippines</div>
                    </div>
                    <div class="about-item">
                        <div class="about-label"><i class="fas fa-calendar"></i> Year</div>
                        <div class="about-val"><?= date("Y") ?></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN -->
        <div class="col-lg-5">

            <!-- Account Settings -->
            <div class="settings-card mb-3">
                <div class="settings-section-header">
                    <div class="settings-section-icon green">
                        <i class="fas fa-user-lock"></i>
                    </div>
                    <div>
                        <div class="settings-section-title">Account Settings</div>
                        <div class="settings-section-sub">Change your account password</div>
                    </div>
                </div>

                <!-- Profile info -->
                <div class="profile-info-box">
                    <div class="profile-avatar"><?= $initials ?></div>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#1C1400"><?= htmlspecialchars($fullname) ?></div>
                        <div style="font-size:12px;color:#9CA3AF">@<?= htmlspecialchars($username) ?></div>
                        <span class="role-badge role-admin" style="margin-top:4px;display:inline-flex">Administrator</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-modern">Current Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="current_password" class="form-control-modern"
                               placeholder="Enter current password">
                        <button type="button" class="pw-eye" onclick="togglePw('current_password',this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label-modern">New Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="new_password" class="form-control-modern"
                               placeholder="Enter new password">
                        <button type="button" class="pw-eye" onclick="togglePw('new_password',this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label-modern">Confirm New Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="confirm_password" class="form-control-modern"
                               placeholder="Confirm new password">
                        <button type="button" class="pw-eye" onclick="togglePw('confirm_password',this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div id="passwordMsg" class="settings-msg" style="display:none"></div>

                <button class="btn-settings-save" onclick="updatePassword()">
                    <i class="fas fa-lock"></i> Update Password
                </button>
            </div>

            <!-- Quick Info -->
            <div class="settings-card">
                <div class="settings-section-header">
                    <div class="settings-section-icon amber">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <div class="settings-section-title">System Status</div>
                        <div class="settings-section-sub">Current session information</div>
                    </div>
                </div>
                <div class="status-list">
                    <div class="status-item">
                        <span class="status-label">Status</span>
                        <span class="status-online"><i class="fas fa-circle"></i> Online</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Logged in as</span>
                        <span class="status-val"><?= htmlspecialchars($fullname) ?></span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Role</span>
                        <span class="role-badge role-admin">Administrator</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Database</span>
                        <span class="status-online"><i class="fas fa-circle"></i> Connected</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Date Today</span>
                        <span class="status-val"><?= date("F d, Y") ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script src="assets/js/jquery-4.0.0.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="js/settings.js"></script>
<script>
document.getElementById("sidebarToggle").addEventListener("click", function(){
    document.getElementById("sidebar").classList.toggle("open");
});

function togglePw(id, btn){
    let inp  = document.getElementById(id);
    let icon = btn.querySelector("i");
    if(inp.type === "password"){
        inp.type = "text";
        icon.className = "fas fa-eye-slash";
    } else {
        inp.type = "password";
        icon.className = "fas fa-eye";
    }
}

function showMsg(id, msg, type){
    let el = document.getElementById(id);
    el.textContent = msg;
    el.style.display = "block";
    el.style.background = type === "success" ? "#D1FAE5" : "#FEE2E2";
    el.style.color      = type === "success" ? "#065F46" : "#991B1B";
    el.style.border     = type === "success" ? "1px solid #6EE7B7" : "1px solid #FECACA";
    setTimeout(function(){ el.style.display = "none"; }, 4000);
}

function handleLogoDrop(e){
    e.preventDefault();
    document.getElementById("logoUploadArea").classList.remove("dragover");
    let file = e.dataTransfer.files[0];
    if(file && file.type.startsWith("image/")){
        let dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById("logoInput").files = dt.files;
        let reader = new FileReader();
        reader.onload = function(ev){
            $("#logoPreview").attr("src", ev.target.result).show();
            $("#uploadPlaceholder").hide();
        };
        reader.readAsDataURL(file);
    }
}
</script>
</body>
</html>