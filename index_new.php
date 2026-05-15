<?php
include 'db.php';
$pageTitle = 'Dashboard';
include 'header.php';

$total      = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal"))[0];
$wanted     = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal WHERE status='Wanted'"))[0];
$imprisoned = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal WHERE status='Imprisoned'"))[0];
$released   = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal WHERE status='Released'"))[0];
$active_cases = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Case_Record WHERE case_status IN('Open','Under Investigation')"))[0];
$closed_cases = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Case_Record WHERE case_status='Closed'"))[0];

$crimeTypes=[]; $crimeCounts=[];
$r1=mysqli_query($conn,"SELECT crime_type,COUNT(*) cnt FROM Crime GROUP BY crime_type ORDER BY cnt DESC LIMIT 6");
while($row=mysqli_fetch_assoc($r1)){$crimeTypes[]=$row['crime_type'];$crimeCounts[]=(int)$row['cnt'];}
?>

<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="layout-dashboard"></i></div>
  <div><h2>Dashboard</h2><p>System overview — <?= date('l, d F Y') ?></p></div>
 </div>
 <?php if($_role === 'viewer'): ?>
 <a href="report_crime.php" class="btn btn-primary"><i data-lucide="alert-triangle"></i> Report a Crime</a>
 <?php else: ?>
 <a href="register_criminal.php" class="btn btn-primary"><i data-lucide="user-plus"></i> Register Criminal</a>
 <?php endif; ?>
</div>

<!-- KPI CARDS -->
<div class="kpi-grid">
 <div class="kpi-card">
  <div class="kpi-inner">
   <div class="kpi-icon kpi-blue"><i data-lucide="users"></i></div>
   <div><div class="kpi-val"><?=$total?></div><div class="kpi-lbl">Total Criminals</div></div>
  </div>
  <div class="kpi-sub">
   <span class="kpi-pill pill-orange"><?=$wanted?> Wanted</span>
   <span class="kpi-pill pill-red"><?=$imprisoned?> Imprisoned</span>
   <span class="kpi-pill pill-green"><?=$released?> Released</span>
  </div>
 </div>
 <div class="kpi-card">
  <div class="kpi-inner">
   <div class="kpi-icon kpi-amber"><i data-lucide="folder-open"></i></div>
   <div><div class="kpi-val"><?=$active_cases?></div><div class="kpi-lbl">Active Cases</div></div>
  </div>
  <div class="kpi-sub">
   <span class="kpi-pill pill-green"><?=$closed_cases?> Closed</span>
  </div>
 </div>
</div>

<!-- CHARTS + QUICK ACTIONS -->
<div class="dash-two-col">
 <div class="card">
  <div class="card-title"><i data-lucide="pie-chart"></i> Crime Type Distribution</div>
  <?php if(empty($crimeTypes)): ?>
  <div class="no-data"><i data-lucide="database"></i>No crime data yet.</div>
  <?php else: ?>
  <div class="chart-container"><canvas id="chartPie"></canvas></div>
  <div class="legend-list" id="pieLegend"></div>
  <?php endif; ?>
 </div>
 <div class="card">
  <div class="card-title"><i data-lucide="zap"></i> Quick Actions</div>
  <?php if($_role !== 'viewer'): ?>
  <a href="register_criminal.php" class="qa-btn"><div class="qa-icon" style="background:var(--accent-lt)"><i data-lucide="user-plus" style="color:var(--accent)"></i></div><div class="qa-text"><strong>Register Criminal</strong><span>Add a new criminal record</span></div></a>
  <a href="register_officer.php" class="qa-btn"><div class="qa-icon" style="background:rgba(63,185,80,.15)"><i data-lucide="badge" style="color:var(--success)"></i></div><div class="qa-text"><strong>Register Officer</strong><span>Add an investigating officer</span></div></a>
  <a href="register_case.php" class="qa-btn"><div class="qa-icon" style="background:rgba(210,153,34,.15)"><i data-lucide="folder-plus" style="color:var(--warning)"></i></div><div class="qa-text"><strong>New Case</strong><span>Open a new investigation case</span></div></a>
  <?php else: ?>
  <a href="report_crime.php" class="qa-btn"><div class="qa-icon" style="background:rgba(248,81,73,.15)"><i data-lucide="alert-triangle" style="color:#f85149"></i></div><div class="qa-text"><strong>Report a Crime</strong><span>Submit a crime report</span></div></a>
  <?php endif; ?>
  <a href="search.php" class="qa-btn"><div class="qa-icon" style="background:var(--violet-lt)"><i data-lucide="search" style="color:var(--violet)"></i></div><div class="qa-text"><strong>Search Records</strong><span>Find criminals, cases, officers</span></div></a>
 </div>
</div>

<script>
const D={crimeTypes:<?=json_encode($crimeTypes)?>,crimeCounts:<?=json_encode($crimeCounts)?>};
</script>
<?php include 'footer.php'; ?>
