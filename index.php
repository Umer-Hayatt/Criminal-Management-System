<?php
include 'db.php';
$pageTitle = 'Dashboard';
include 'header.php';

// KPI Stats 
$total = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal"))[0];
$wanted = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal WHERE status='Wanted'"))[0];
$imprisoned = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal WHERE status='Imprisoned'"))[0];
$open_cases = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Case_Record WHERE case_status='Open' OR case_status='Under Investigation'"))[0];
$closed_cases= mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Case_Record WHERE case_status='Closed'"))[0];
$pending_hrs = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Court_Hearing WHERE verdict='Pending'"))[0];
$total_officers = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Officer"))[0];

// Charts data 
$crimeTypes=[]; $crimeTypeCounts=[];
$r1=mysqli_query($conn,"SELECT crime_type, COUNT(*) as cnt FROM Crime GROUP BY crime_type ORDER BY cnt DESC LIMIT 6");
while($row=mysqli_fetch_assoc($r1)){$crimeTypes[]=$row['crime_type'];$crimeTypeCounts[]=(int)$row['cnt'];}

$caseLabels=[]; $caseCounts=[];
$r2=mysqli_query($conn,"SELECT case_status, COUNT(*) as cnt FROM Case_Record GROUP BY case_status ORDER BY cnt DESC");
while($row=mysqli_fetch_assoc($r2)){$caseLabels[]=$row['case_status'];$caseCounts[]=(int)$row['cnt'];}

$officerLoad=[];
$r3=mysqli_query($conn,"SELECT CONCAT(o.first_name,' ',o.last_name) as name, COUNT(oc.case_id) as cnt FROM Officer o LEFT JOIN Officer_Case oc ON o.officer_id=oc.officer_id GROUP BY o.officer_id ORDER BY cnt DESC LIMIT 5");
while($row=mysqli_fetch_assoc($r3)){$officerLoad[]=$row;}
$maxLoad=count($officerLoad)?max(array_column($officerLoad,'cnt')):1;
$maxLoad=$maxLoad?:1;

$recent_criminals=[];
$rr=mysqli_query($conn,"SELECT criminal_id,first_name,last_name,status,nationality FROM Criminal ORDER BY criminal_id DESC LIMIT 6");
while($row=mysqli_fetch_assoc($rr)) $recent_criminals[]=$row;

$recent_cases=[];
$rc=mysqli_query($conn,"SELECT c.case_id,c.case_status,c.open_date,cr.crime_type FROM Case_Record c JOIN Crime cr ON c.crime_id=cr.crime_id ORDER BY c.case_id DESC LIMIT 5");
while($row=mysqli_fetch_assoc($rc)) $recent_cases[]=$row;
?>

<div class="page-hdr">
 <div>
 <h2>Dashboard Overview</h2>
 <p>System summary — <?= date('l, d F Y') ?></p>
 </div>
 <a href="register.php" class="btn btn-primary">
 <i data-lucide="user-plus"></i> Register Criminal
 </a>
</div>

<!-- KPI CARDS -->
<div class="kpi-grid">

 <div class="kpi-card">
 <div class="kpi-left">
 <div class="kpi-icon kpi-blue"><i data-lucide="users"></i></div>
 <div>
 <div class="kpi-val"><?=$total?></div>
 <div class="kpi-lbl">Total Criminals</div>
 </div>
 </div>
 <div class="kpi-sub">
 <span class="kpi-pill pill-orange"><?=$wanted?> Wanted</span>
 <span class="kpi-pill pill-red"><?=$imprisoned?> Imprisoned</span>
 </div>
 </div>

 <div class="kpi-card">
 <div class="kpi-left">
 <div class="kpi-icon kpi-amber"><i data-lucide="folder-open"></i></div>
 <div>
 <div class="kpi-val"><?=$open_cases?></div>
 <div class="kpi-lbl">Active Cases</div>
 </div>
 </div>
 <div class="kpi-sub">
 <span class="kpi-pill pill-green"><?=$closed_cases?> Closed</span>
 </div>
 </div>

 <div class="kpi-card">
 <div class="kpi-left">
 <div class="kpi-icon kpi-red"><i data-lucide="scale"></i></div>
 <div>
 <div class="kpi-val"><?=$pending_hrs?></div>
 <div class="kpi-lbl">Pending Hearings</div>
 </div>
 </div>
 <div class="kpi-sub">
 <span class="kpi-pill pill-muted">Awaiting Verdict</span>
 </div>
 </div>

 <div class="kpi-card">
 <div class="kpi-left">
 <div class="kpi-icon kpi-green"><i data-lucide="shield-check"></i></div>
 <div>
 <div class="kpi-val"><?=$total_officers?></div>
 <div class="kpi-lbl">Active Officers</div>
 </div>
 </div>
 <div class="kpi-sub">
 <span class="kpi-pill pill-muted">On Duty</span>
 </div>
 </div>

</div>

<!-- CHARTS ROW -->
<div class="dash-two-col">

 <div class="card">
 <div class="card-title"><i data-lucide="pie-chart"></i> Crime Type Distribution</div>
 <?php if(empty($crimeTypes)): ?>
 <div class="no-data-msg">No crime data yet.</div>
 <?php else: ?>
 <div class="chart-container"><canvas id="chartDonut"></canvas></div>
 <div class="legend-list" id="donutLegend"></div>
 <?php endif; ?>
 </div>

 <div class="card">
 <div class="card-title"><i data-lucide="bar-chart-2"></i> Case Status Breakdown</div>
 <?php if(empty($caseLabels)): ?>
 <div class="no-data-msg">No case data yet.</div>
 <?php else: ?>
 <div class="chart-container"><canvas id="chartCaseBar"></canvas></div>
 <?php endif; ?>
 </div>

</div>

<!-- OFFICER CASELOAD + QUICK ACTIONS -->
<div class="dash-two-col">

 <div class="card">
 <div class="card-title"><i data-lucide="users-round"></i> Officer Caseload</div>
 <?php if(empty($officerLoad)): ?>
 <div class="no-data-msg">No officers assigned yet.</div>
 <?php else: ?>
 <div class="caseload-list">
 <?php
 $barColors=['#4f46e5','#0284c7','#7c3aed','#059669','#d97706'];
 foreach($officerLoad as $i=>$ol):
 $pct=$maxLoad>0?round(($ol['cnt']/$maxLoad)*100):0;
 $col=$barColors[$i%count($barColors)];
 ?>
 <div class="caseload-row">
 <div class="caseload-meta">
 <span class="caseload-name"><?=htmlspecialchars($ol['name'])?></span>
 <span class="caseload-count" style="color:<?=$col?>"><?=$ol['cnt']?> cases</span>
 </div>
 <div class="caseload-bar-bg">
 <div class="caseload-bar-fill" style="width:<?=$pct?>%;background:<?=$col?>"></div>
 </div>
 </div>
 <?php endforeach; ?>
 </div>
 <?php endif; ?>
 </div>

 <div class="card">
 <div class="card-title"><i data-lucide="zap"></i> Quick Actions</div>
 <div class="quick-actions">
 <a href="register.php" class="qa-btn">
 <div class="qa-icon" style="background:#eef2ff;color:#4f46e5"><i data-lucide="user-plus"></i></div>
 <div class="qa-text"><strong>Register Criminal</strong><span>Add a new criminal record</span></div>
 </a>
 <a href="search.php" class="qa-btn">
 <div class="qa-icon" style="background:#f0f9ff;color:#0284c7"><i data-lucide="search"></i></div>
 <div class="qa-text"><strong>Search Criminal</strong><span>Look up by name or phone</span></div>
 </a>
 <a href="records.php" class="qa-btn">
 <div class="qa-icon" style="background:#fffbeb;color:#d97706"><i data-lucide="file-text"></i></div>
 <div class="qa-text"><strong>All Records</strong><span>View complete criminal list</span></div>
 </a>
 <a href="officers.php" class="qa-btn">
 <div class="qa-icon" style="background:#f0fdf4;color:#059669"><i data-lucide="badge"></i></div>
 <div class="qa-text"><strong>Manage Officers</strong><span>Add or view officers</span></div>
 </a>
 </div>
 </div>

</div>

<!-- RECENT ACTIVITY -->
<div class="dash-two-col">

 <div class="card">
 <div class="card-header-row">
 <div class="card-title" style="margin:0;border:0;padding:0"><i data-lucide="clock"></i> Recently Registered</div>
 <a href="records.php" class="btn btn-gray btn-sm">View All</a>
 </div>
 <div style="margin-top:16px;">
 <?php foreach($recent_criminals as $r):
 $b=match($r['status']){'Imprisoned'=>'b-red','Released'=>'b-green','Wanted'=>'b-orange',default=>'b-blue'};
 ?>
 <div class="activity-row">
 <div class="activity-avatar"><?=strtoupper(substr($r['first_name'],0,1))?></div>
 <div class="activity-info">
 <strong><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></strong>
 <span><?=htmlspecialchars($r['nationality'])?></span>
 </div>
 <div style="display:flex;align-items:center;gap:8px;">
 <span class="badge <?=$b?>"><?=$r['status']?></span>
 <a href="profile.php?id=<?=$r['criminal_id']?>" class="btn btn-gray btn-sm">View</a>
 </div>
 </div>
 <?php endforeach;
 if(empty($recent_criminals)) echo '<div class="no-data-msg">No criminals registered yet.</div>';
 ?>
 </div>
 </div>

 <div class="card">
 <div class="card-header-row">
 <div class="card-title" style="margin:0;border:0;padding:0"><i data-lucide="folder"></i> Recent Cases</div>
 </div>
 <div style="margin-top:16px;">
 <?php foreach($recent_cases as $c):
 $cs=match($c['case_status']){'Open'=>'b-orange','Closed'=>'b-green','Under Investigation'=>'b-blue',default=>'b-blue'};
 ?>
 <div class="activity-row">
 <div class="activity-avatar case-av"><?=$c['case_id']?></div>
 <div class="activity-info">
 <strong>Case #<?=$c['case_id']?></strong>
 <span><?=htmlspecialchars($c['crime_type'])?></span>
 </div>
 <span class="badge <?=$cs?>"><?=$c['case_status']?></span>
 </div>
 <?php endforeach;
 if(empty($recent_cases)) echo '<div class="no-data-msg">No cases yet.</div>';
 ?>
 </div>
 </div>

</div>

<script>
const D = {
 crimeTypes: <?=json_encode($crimeTypes)?>,
 crimeCounts: <?=json_encode($crimeTypeCounts)?>,
 caseLabels: <?=json_encode($caseLabels)?>,
 caseCounts: <?=json_encode($caseCounts)?>
};
</script>

<?php include 'footer.php'; ?>
