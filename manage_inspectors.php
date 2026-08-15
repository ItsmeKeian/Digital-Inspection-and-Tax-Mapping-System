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

// Fetch all users
$users = $conn->query("SELECT * FROM user ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DITMS — Manage Inspectors</title>
    <link href="assets/img/borlogo.png" rel="icon">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <link href="assets/css/business.css" rel="stylesheet">
    <link href="assets/css/manage_inspectors.css" rel="stylesheet">
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
        <a href="manage_inspectors.php" class="active"><i class="fas fa-users"></i> Manage Inspectors</a>
        <a href="activity_logs.php"><i class="fas fa-history"></i> Activity Logs</a>
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
            <i class="fas fa-users"></i>
            Manage Inspectors
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
            <h2>Manage Inspectors</h2>
            <p>Add and manage inspector and admin accounts</p>
        </div>
        <div class="page-actions">
            <button class="btn-action-gold" onclick="openAddModal()">
                <i class="fas fa-user-plus"></i> Add Inspector
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-3">
        <div class="col-sm-4">
            <div class="mi-stat-card">
                <div class="mi-stat-icon" style="background:linear-gradient(135deg,#C8960C,#F5C518)">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="mi-stat-val"><?= count($users) ?></div>
                    <div class="mi-stat-label">Total Accounts</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mi-stat-card">
                <div class="mi-stat-icon" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <div class="mi-stat-val"><?= count(array_filter($users, fn($u) => $u['role'] === 'admin')) ?></div>
                    <div class="mi-stat-label">Administrators</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mi-stat-card">
                <div class="mi-stat-icon" style="background:linear-gradient(135deg,#16a34a,#4ade80)">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <div class="mi-stat-val"><?= count(array_filter($users, fn($u) => $u['role'] === 'inspector')) ?></div>
                    <div class="mi-stat-label">Inspectors</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Date Added</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                    <tr>
                        <td colspan="7" class="table-empty">
                            <i class="fas fa-users"></i>
                            <span>No accounts found</span>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach($users as $i => $u): ?>
                    <?php
                        $isMe = ($u["username"] === $_SESSION["user"]);
                        $roleClass = $u["role"] === "admin" ? "role-admin" : "role-inspector";
                        $roleLabel = $u["role"] === "admin" ? "Administrator" : "Inspector";
                        $date = !empty($u["created_at"]) ? date("M d, Y", strtotime($u["created_at"])) : "—";
                        $uFullname = $u["full_name"] ?? $u["username"];
                        $uInitial = strtoupper(substr($uFullname, 0, 1));
                    ?>
                    <tr>
                        <td style="color:#9CA3AF;font-size:13px"><?= $i + 1 ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div class="user-avatar-table"><?= $uInitial ?></div>
                                <div>
                                    <div style="font-weight:600;font-size:13.5px;color:#1C1400">
                                        <?= htmlspecialchars($uFullname) ?>
                                        <?php if($isMe): ?>
                                        <span class="badge-me">You</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="color:#6B7280;font-size:13px">@<?= htmlspecialchars($u["username"]) ?></td>
                        <td style="font-size:13px"><?= htmlspecialchars($u["email"] ?? "—") ?></td>
                        <td><span class="role-badge <?= $roleClass ?>"><?= $roleLabel ?></span></td>
                        <td style="font-size:13px;color:#6B7280"><?= $date ?></td>
                        <td style="white-space:nowrap">
                            <button class="tbl-btn tbl-btn-edit" title="Edit"
                                onclick="openEditModal(<?= $u['id'] ?>, '<?= htmlspecialchars($uFullname, ENT_QUOTES) ?>', '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['email'] ?? '', ENT_QUOTES) ?>', '<?= $u['role'] ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if(!$isMe): ?>
                            <button class="tbl-btn tbl-btn-delete" title="Delete"
                                onclick="deleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars($uFullname, ENT_QUOTES) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php else: ?>
                            <button class="tbl-btn" title="Cannot delete own account"
                                style="color:#D1D5DB;cursor:not-allowed" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- ── ADD INSPECTOR MODAL ── -->
<div class="modal fade" id="addInspectorModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
    <div class="modal-header">
        <div>
            <h5 class="modal-title"><i class="fas fa-user-plus me-2" style="color:#C8960C"></i>Add New Account</h5>
            <p style="font-size:12px;color:#9CA3AF;margin:0">Create a new inspector or admin account</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div id="addMsg" class="alert-msg" style="display:none"></div>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label-modern">Full Name *</label>
                <input type="text" id="add_fullname" class="form-control-modern" placeholder="Enter full name">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Username *</label>
                <input type="text" id="add_username" class="form-control-modern" placeholder="Enter username">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Email</label>
                <input type="email" id="add_email" class="form-control-modern" placeholder="Enter email">
            </div>
            <div class="col-12">
                <label class="form-label-modern">Role</label>
                <select id="add_role" class="form-control-modern">
                    <option value="inspector">Inspector</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Password *</label>
                <input type="password" id="add_password" class="form-control-modern" placeholder="Enter password">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Confirm Password *</label>
                <input type="password" id="add_confirm" class="form-control-modern" placeholder="Confirm password">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn-modal-save" onclick="saveNewUser()">
            <i class="fas fa-save me-1"></i>Save Account
        </button>
    </div>
</div>
</div>
</div>

<!-- ── EDIT INSPECTOR MODAL ── -->
<div class="modal fade" id="editInspectorModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
    <div class="modal-header">
        <div>
            <h5 class="modal-title"><i class="fas fa-user-edit me-2" style="color:#C8960C"></i>Edit Account</h5>
            <p style="font-size:12px;color:#9CA3AF;margin:0">Update account information</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div id="editMsg" class="alert-msg" style="display:none"></div>
        <input type="hidden" id="edit_id">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label-modern">Full Name</label>
                <input type="text" id="edit_fullname" class="form-control-modern">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Username</label>
                <input type="text" id="edit_username" class="form-control-modern">
            </div>
            <div class="col-md-6">
                <label class="form-label-modern">Email</label>
                <input type="email" id="edit_email" class="form-control-modern">
            </div>
            <div class="col-12">
                <label class="form-label-modern">Role</label>
                <select id="edit_role" class="form-control-modern">
                    <option value="inspector">Inspector</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label-modern">New Password
                    <span style="font-weight:400;color:#9CA3AF;text-transform:none;letter-spacing:0">(leave blank to keep current)</span>
                </label>
                <input type="password" id="edit_password" class="form-control-modern" placeholder="Leave blank to keep current password">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn-modal-save" onclick="saveEditUser()">
            <i class="fas fa-save me-1"></i>Save Changes
        </button>
    </div>
</div>
</div>
</div>

<script src="assets/js/jquery-4.0.0.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById("sidebarToggle").addEventListener("click", function(){
    document.getElementById("sidebar").classList.toggle("open");
});

function openAddModal(){
    document.getElementById("add_fullname").value = "";
    document.getElementById("add_username").value = "";
    document.getElementById("add_email").value = "";
    document.getElementById("add_role").value = "inspector";
    document.getElementById("add_password").value = "";
    document.getElementById("add_confirm").value = "";
    document.getElementById("addMsg").style.display = "none";
    new bootstrap.Modal(document.getElementById("addInspectorModal")).show();
}

function openEditModal(id, fullname, username, email, role){
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_fullname").value = fullname;
    document.getElementById("edit_username").value = username;
    document.getElementById("edit_email").value = email;
    document.getElementById("edit_role").value = role;
    document.getElementById("edit_password").value = "";
    document.getElementById("editMsg").style.display = "none";
    new bootstrap.Modal(document.getElementById("editInspectorModal")).show();
}

function showMsg(id, msg, type){
    let el = document.getElementById(id);
    el.textContent = msg;
    el.style.display = "block";
    el.style.background = type === "error" ? "#FEE2E2" : "#D1FAE5";
    el.style.color      = type === "error" ? "#991B1B" : "#065F46";
    el.style.border     = type === "error" ? "1px solid #FECACA" : "1px solid #6EE7B7";
}

function saveNewUser(){
    let fullname = $("#add_fullname").val().trim();
    let username = $("#add_username").val().trim();
    let email    = $("#add_email").val().trim();
    let role     = $("#add_role").val();
    let password = $("#add_password").val();
    let confirm  = $("#add_confirm").val();

    if(!fullname || !username || !password){
        showMsg("addMsg", "Full name, username, and password are required.", "error");
        return;
    }
    if(password !== confirm){
        showMsg("addMsg", "Passwords do not match.", "error");
        return;
    }

    $.post("php/create/create_inspector.php", {
        full_name: fullname,
        username:  username,
        email:     email,
        role:      role,
        password:  password
    }, function(res){
        let d = JSON.parse(res);
        if(d.status === "success"){
            location.reload();
        } else {
            showMsg("addMsg", d.message || "Something went wrong.", "error");
        }
    });
}

function saveEditUser(){
    let id       = $("#edit_id").val();
    let fullname = $("#edit_fullname").val().trim();
    let username = $("#edit_username").val().trim();
    let email    = $("#edit_email").val().trim();
    let role     = $("#edit_role").val();
    let password = $("#edit_password").val();

    if(!fullname || !username){
        showMsg("editMsg", "Full name and username are required.", "error");
        return;
    }

    $.post("php/update/update_inspector.php", {
        id:        id,
        full_name: fullname,
        username:  username,
        email:     email,
        role:      role,
        password:  password
    }, function(){
        location.reload();
    });
}

function deleteUser(id, name){
    if(!confirm("Delete account of \"" + name + "\"? This cannot be undone.")){
        return;
    }
    $.post("php/delete/delete_inspector.php", { id }, function(){
        location.reload();
    });
}
</script>
</body>
</html>