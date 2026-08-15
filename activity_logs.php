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
$initials = strtoupper(substr($fullname, 0, 1));

require "php/dbconnect.php";
try {
    $stmt = $conn->query("SELECT * FROM system_settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(Exception $e){ $settings = []; }

// Fetch logs
$logs = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalLogs   = count($logs);
$todayLogs   = count(array_filter($logs, fn($l) => date("Y-m-d", strtotime($l["created_at"])) === date("Y-m-d")));
$loginCount  = count(array_filter($logs, fn($l) => $l["action"] === "Login"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DITMS — Activity Logs</title>
    <link href="assets/img/borlogo.png" rel="icon">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <link href="assets/css/business.css" rel="stylesheet">
    <link href="assets/css/activity_logs.css" rel="stylesheet">
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
        <a href="activity_logs.php" class="active"><i class="fas fa-history"></i> Activity Logs</a>
        <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
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
            <i class="fas fa-history"></i>
            Activity Logs
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
                    <div style="font-size:11px;color:#9CA3AF;margin-bottom:4px">@<?= htmlspecialchars($_SESSION["user"]) ?></div>
                    <span class="role-badge role-admin">Administrator</span>
                </div>
            </li>
            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2 text-muted"></i>Settings</a></li>
            <li><hr class="dropdown-divider my-1"></li>
            <li><a class="dropdown-item text-danger" href="php/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </div>
</header>

<!-- ── MAIN CONTENT ── -->
<main class="main-content">

    <div class="page-header-wrap">
        <div class="page-title">
            <h2>Activity Logs</h2>
            <p>Track all user activities in the system — showing last 300 records</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-3">
        <div class="col-sm-4">
            <div class="mi-stat-card">
                <div class="mi-stat-icon" style="background:linear-gradient(135deg,#C8960C,#F5C518)">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <div class="mi-stat-val"><?= $totalLogs ?></div>
                    <div class="mi-stat-label">Total Logs</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mi-stat-card">
                <div class="mi-stat-icon" style="background:linear-gradient(135deg,#16a34a,#4ade80)">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div>
                    <div class="mi-stat-val"><?= $todayLogs ?></div>
                    <div class="mi-stat-label">Today's Activities</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mi-stat-card">
                <div class="mi-stat-icon" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <div>
                    <div class="mi-stat-val"><?= $loginCount ?></div>
                    <div class="mi-stat-label">Total Logins</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" id="searchLog" placeholder="Search user or action..." oninput="filterLogs()">
        </div>
        <select id="filterRole" class="filter-select" onchange="filterLogs()">
            <option value="">All Roles</option>
            <option value="admin">Administrator</option>
            <option value="inspector">Inspector</option>
        </select>
        <select id="filterAction" class="filter-select" onchange="filterLogs()">
            <option value="">All Actions</option>
            <option value="Login">Login</option>
            <option value="Logout">Logout</option>
            <option value="Add">Add</option>
            <option value="Edit">Edit</option>
            <option value="Delete">Delete</option>
        </select>
        <button class="btn-filter-clear" onclick="clearFilters()">
            <i class="fas fa-times"></i> Clear
        </button>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="reports-table-header">
            <div style="font-size:14px;font-weight:700;color:#1C1400">Activity Records</div>
            <div style="font-size:12px;color:#9CA3AF" id="logCount"><?= $totalLogs ?> record(s)</div>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="logsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($logs)): ?>
                    <tr>
                        <td colspan="7" class="table-empty">
                            <i class="fas fa-history"></i>
                            <span>No activity logs yet</span>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach($logs as $i => $log):
                        $roleClass  = $log["role"] === "admin" ? "role-admin" : "role-inspector";
                        $roleLabel  = $log["role"] === "admin" ? "Administrator" : "Inspector";
                        $uInitial   = strtoupper(substr($log["full_name"] ?? $log["username"], 0, 1));
                        $actionColors = [
                            "Login"    => ["bg"=>"#D1FAE5","color"=>"#065F46"],
                            "Logout"   => ["bg"=>"#FEF3C7","color"=>"#92400E"],
                            "Add"      => ["bg"=>"#DBEAFE","color"=>"#1E40AF"],
                            "Edit"     => ["bg"=>"#FEF3C7","color"=>"#92400E"],
                            "Delete"   => ["bg"=>"#FEE2E2","color"=>"#991B1B"],
                            "Generate" => ["bg"=>"#F5F3FF","color"=>"#5B21B6"],
                        ];
                        $ac = $actionColors[$log["action"]] ?? ["bg"=>"#F3F4F6","color"=>"#6B7280"];
                        $datetime = date("M d, Y h:i A", strtotime($log["created_at"]));
                    ?>
                    <tr data-user="<?= strtolower(($log['full_name'] ?? '') . ' ' . $log['username']) ?>"
                        data-role="<?= $log['role'] ?>"
                        data-action="<?= $log['action'] ?>">
                        <td style="color:#9CA3AF;font-size:12px"><?= $i + 1 ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px">
                                <div class="log-avatar"><?= $uInitial ?></div>
                                <div>
                                    <div style="font-weight:600;font-size:13px;color:#1C1400">
                                        <?= htmlspecialchars($log["full_name"] ?? $log["username"]) ?>
                                    </div>
                                    <div style="font-size:11px;color:#9CA3AF">
                                        @<?= htmlspecialchars($log["username"]) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><span class="role-badge <?= $roleClass ?>"><?= $roleLabel ?></span></td>
                        <td>
                            <span class="action-badge" style="background:<?= $ac['bg'] ?>;color:<?= $ac['color'] ?>">
                                <?= htmlspecialchars($log["action"]) ?>
                            </span>
                        </td>
                        <td style="font-size:13px;color:#6B7280"><?= htmlspecialchars($log["module"]) ?></td>
                        <td style="font-size:12.5px;color:#374151;max-width:280px">
                            <?= htmlspecialchars($log["description"] ?? "—") ?>
                        </td>
                        <td style="font-size:12px;color:#9CA3AF;white-space:nowrap"><?= $datetime ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrap" id="paginationWrap">
        <div style="font-size:13px;color:#6B7280" id="pageInfo"></div>
        <div class="pagination-btns">
            <button class="page-btn" id="prevBtn" onclick="changePage(-1)">
                <i class="fas fa-chevron-left"></i> Previous
            </button>
            <div class="page-numbers" id="pageNumbers"></div>
            <button class="page-btn" id="nextBtn" onclick="changePage(1)">
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

</main>

<script src="assets/js/jquery-4.0.0.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById("sidebarToggle").addEventListener("click", function(){
    document.getElementById("sidebar").classList.toggle("open");
});

const ROWS_PER_PAGE = 15;
let currentPage = 1;
let filteredRows = [];

function getAllRows(){
    return Array.from(document.querySelectorAll("#logsTable tbody tr[data-user]"));
}

function filterLogs(){
    let search = document.getElementById("searchLog").value.toLowerCase();
    let role   = document.getElementById("filterRole").value.toLowerCase();
    let action = document.getElementById("filterAction").value.toLowerCase();

    let allRows = getAllRows();
    filteredRows = allRows.filter(function(row){
        let user    = (row.getAttribute("data-user")   || "").toLowerCase();
        let rRole   = (row.getAttribute("data-role")   || "").toLowerCase();
        let rAction = (row.getAttribute("data-action") || "").toLowerCase();
        return (!search || user.includes(search))
            && (!role   || rRole === role)
            && (!action || rAction === action);
    });

    currentPage = 1;
    renderPage();
}

function renderPage(){
    let allRows = getAllRows();

    // Hide all first
    allRows.forEach(r => r.style.display = "none");

    let start = (currentPage - 1) * ROWS_PER_PAGE;
    let end   = start + ROWS_PER_PAGE;
    let pageRows = filteredRows.slice(start, end);

    pageRows.forEach(r => r.style.display = "");

    // Update count
    document.getElementById("logCount").textContent = filteredRows.length + " record(s)";

    // Update pagination info
    let totalPages = Math.ceil(filteredRows.length / ROWS_PER_PAGE);
    document.getElementById("pageInfo").textContent =
        "Showing " + (start + 1) + "–" + Math.min(end, filteredRows.length) + " of " + filteredRows.length;

    // Prev/Next buttons
    document.getElementById("prevBtn").disabled = currentPage <= 1;
    document.getElementById("nextBtn").disabled = currentPage >= totalPages;

    // Page numbers
    let nums = "";
    let range = 2;
    for(let i = 1; i <= totalPages; i++){
        if(i === 1 || i === totalPages || (i >= currentPage - range && i <= currentPage + range)){
            nums += `<button class="page-num ${i === currentPage ? "active" : ""}" onclick="goToPage(${i})">${i}</button>`;
        } else if(i === currentPage - range - 1 || i === currentPage + range + 1){
            nums += `<span class="page-ellipsis">…</span>`;
        }
    }
    document.getElementById("pageNumbers").innerHTML = nums;

    // Show/hide pagination
    document.getElementById("paginationWrap").style.display = totalPages > 1 ? "flex" : "none";
}

function changePage(dir){
    let totalPages = Math.ceil(filteredRows.length / ROWS_PER_PAGE);
    currentPage = Math.max(1, Math.min(currentPage + dir, totalPages));
    renderPage();
    window.scrollTo({ top: 0, behavior: "smooth" });
}

function goToPage(page){
    currentPage = page;
    renderPage();
    window.scrollTo({ top: 0, behavior: "smooth" });
}

function clearFilters(){
    document.getElementById("searchLog").value = "";
    document.getElementById("filterRole").value = "";
    document.getElementById("filterAction").value = "";
    filterLogs();
}

// Initialize on load
document.addEventListener("DOMContentLoaded", function(){
    filteredRows = getAllRows();
    renderPage();
});
</script>
</body>
</html>