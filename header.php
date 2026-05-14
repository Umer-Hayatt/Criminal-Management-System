<?php
require_once 'db.php';
require_login();
$_u = current_user();
$_role = $_u['role'];
$_page = basename($_SERVER['PHP_SELF']);

// Notifications
$_notifs = [];
$_nres = mysqli_query($conn, "SELECT ch.hearing_id, ch.hearing_date, ch.case_id FROM Court_Hearing ch WHERE ch.hearing_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND ch.verdict='Pending' LIMIT 5");
while($_nr = mysqli_fetch_assoc($_nres)) $_notifs[] = ['type'=>'hearing','msg'=>'Upcoming hearing on '.fmt_date($_nr['hearing_date']),'url'=>'case_profile.php?id='.$_nr['case_id']];

$_notif_count = count($_notifs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRMS — <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
 <div class="sidebar-brand">
  <div class="brand-logo"><i data-lucide="shield-check"></i></div>
  <div class="brand-copy">
   <strong>CRMS</strong>
   <small>Intelligence Platform</small>
  </div>
 </div>

 <div class="nav-group">
  <div class="nav-group-label"><span>Main</span></div>
  <a href="index.php" class="nav-item <?= $_page==='index.php'?'active':'' ?>"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
 </div>

 <div class="nav-group">
  <div class="nav-group-label"><span>Records</span></div>
  <a href="records_criminals.php" class="nav-item <?= in_array($_page,['records_criminals.php','profile.php'])?'active':'' ?>"><i data-lucide="users"></i><span>Criminals</span></a>
  <a href="records_officers.php" class="nav-item <?= in_array($_page,['records_officers.php','officer_profile.php'])?'active':'' ?>"><i data-lucide="shield"></i><span>Officers</span></a>
  <a href="records_prisons.php" class="nav-item <?= $_page==='records_prisons.php'?'active':'' ?>"><i data-lucide="building-2"></i><span>Prisons</span></a>
 </div>

 <div class="nav-group">
  <div class="nav-group-label"><span>Cases</span></div>
  <a href="cases.php" class="nav-item <?= in_array($_page,['cases.php','case_profile.php'])?'active':'' ?>"><i data-lucide="folder-open"></i><span>All Cases</span></a>
  <a href="hearings.php" class="nav-item <?= $_page==='hearings.php'?'active':'' ?>"><i data-lucide="scale"></i><span>Court Hearings</span></a>
 </div>

 <div class="nav-group">
  <div class="nav-group-label"><span>Register</span></div>
  <a href="register_criminal.php" class="nav-item <?= $_page==='register_criminal.php'?'active':'' ?>"><i data-lucide="user-plus"></i><span>Register Criminal</span></a>
  <a href="register_officer.php" class="nav-item <?= $_page==='register_officer.php'?'active':'' ?>"><i data-lucide="badge"></i><span>Register Officer</span></a>
  <a href="register_prison.php" class="nav-item <?= $_page==='register_prison.php'?'active':'' ?>"><i data-lucide="plus-square"></i><span>Add Prison</span></a>
  <a href="register_case.php" class="nav-item <?= $_page==='register_case.php'?'active':'' ?>"><i data-lucide="folder-plus"></i><span>New Case</span></a>
 </div>

 <div class="nav-group">
  <div class="nav-group-label"><span>System</span></div>
  <a href="search.php" class="nav-item <?= $_page==='search.php'?'active':'' ?>"><i data-lucide="search"></i><span>Search</span></a>
  <a href="activity_log.php" class="nav-item <?= $_page==='activity_log.php'?'active':'' ?>"><i data-lucide="activity"></i><span>Activity Log</span></a>
  <?php if(can('admin')): ?>
  <a href="analytics.php" class="nav-item <?= $_page==='analytics.php'?'active':'' ?>"><i data-lucide="bar-chart-2"></i><span>Analytics</span></a>
  <?php endif; ?>
 </div>

 <div class="sidebar-foot">
  <div class="user-block">
   <div class="user-av"><?= strtoupper(substr($_u['username'],0,1)) ?></div>
   <div class="user-info">
    <strong><?= htmlspecialchars($_u['username']) ?></strong>
    <small><span class="role-badge rb-<?= $_role ?>"><?= $_role ?></span></small>
   </div>
  </div>
  <a href="logout.php" class="logout-btn"><i data-lucide="log-out"></i><span>Logout</span></a>
 </div>
</div>

<!-- TOPBAR -->
<div class="topbar">
 <div class="tb-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></div>
 <div class="tb-right">
  <span class="tb-chip"><?= date('D, d M Y') ?></span>
  <div class="tb-icon" id="notifBtn" onclick="toggleNotif()" style="position:relative;">
   <i data-lucide="bell"></i>
   <?php if($_notif_count > 0): ?><span class="notif-badge"><?= $_notif_count ?></span><?php endif; ?>
   <div class="notif-dropdown" id="notifDropdown">
    <div class="notif-hdr">Notifications (<?= $_notif_count ?>)</div>
    <?php if(empty($_notifs)): ?>
    <div style="padding:20px;text-align:center;color:var(--txt-soft);font-size:12px;">No new notifications</div>
    <?php else: foreach($_notifs as $_n):
     $_ic = $_n['type']==='hearing' ? 'scale' : 'file-warning';
     $_cls = $_n['type']==='hearing' ? 'ni-warn' : 'ni-danger';
    ?>
    <a href="<?= $_n['url'] ?>" class="notif-item">
     <div class="notif-icon <?= $_cls ?>"><i data-lucide="<?= $_ic ?>"></i></div>
     <div class="notif-txt"><strong><?= htmlspecialchars($_n['msg']) ?></strong></div>
    </a>
    <?php endforeach; endif; ?>
   </div>
  </div>
 </div>
</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="modal-overlay" style="display:none;">
 <div class="modal-box">
  <div class="modal-icon"><i data-lucide="trash-2"></i></div>
  <h3 id="modalTitle">Delete Record?</h3>
  <p id="modalMsg">This action cannot be undone.</p>
  <div class="modal-actions">
   <button class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
   <a id="modalConfirmBtn" href="#" class="btn btn-danger">Delete</a>
  </div>
 </div>
</div>

<!-- FLASH MESSAGE -->
<?php $flash = get_flash(); if($flash): ?>
<div style="position:fixed;top:calc(var(--th)+10px);right:16px;z-index:900;min-width:280px;">
 <div class="flash flash-<?= $flash['type'] ?>">
  <i data-lucide="<?= $flash['type']==='success'?'check-circle':'alert-circle' ?>"></i>
  <?= htmlspecialchars($flash['msg']) ?>
 </div>
</div>
<script>setTimeout(()=>{let f=document.querySelector('.flash');if(f)f.closest('div').remove();},4000);</script>
<?php endif; ?>

<div class="main"><div class="content">
