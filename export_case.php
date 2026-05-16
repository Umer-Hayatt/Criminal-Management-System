<?php
include 'db.php';
require_login();

if(!can('edit')){ set_flash('error','Permission denied.'); header('Location: index.php'); exit(); }

$id = intval($_GET['id'] ?? 0);
if(!$id) die('Invalid Case ID');

$case = mysqli_fetch_assoc(mysqli_query($conn,"SELECT c.*, cr.crime_type, cr.date_occurred, cr.location, cr.severity, lo.first_name AS lead_first, lo.last_name AS lead_last FROM Case_Record c JOIN Crime cr ON c.crime_id=cr.crime_id LEFT JOIN Officer lo ON c.lead_officer_id=lo.officer_id WHERE c.case_id=$id"));
if(!$case) die('Case not found');

// Fetch linked entities
$officers = [];
if($res=mysqli_query($conn,"SELECT o.*,co.role AS oc_role FROM Officer o JOIN case_officers co ON o.officer_id=co.officer_id WHERE co.case_id=$id")) {
 while($r=mysqli_fetch_assoc($res)) $officers[]=$r;
}
$criminals = [];
if($res=mysqli_query($conn,"SELECT c.*,cc.role AS cr_role FROM Criminal c JOIN case_criminals cc ON c.criminal_id=cc.criminal_id WHERE cc.case_id=$id")) {
 while($r=mysqli_fetch_assoc($res)) $criminals[]=$r;
}
$victims = [];
if($res=mysqli_query($conn,"SELECT * FROM Victim WHERE case_id=$id OR crime_id={$case['crime_id']}")) {
 while($r=mysqli_fetch_assoc($res)) $victims[]=$r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Case Report - #<?= $id ?></title>
<style>
 body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #333; max-width: 800px; margin: 0 auto; padding: 40px 20px; }
 h1 { font-size: 24px; border-bottom: 2px solid #222; padding-bottom: 10px; margin-bottom: 20px; }
 h2 { font-size: 18px; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 30px; margin-bottom: 15px; }
 table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
 th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
 th { background: #f5f5f5; width: 30%; font-weight: bold; }
 .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
 .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
 .print-btn { display: block; margin: 0 0 30px; padding: 10px 20px; background: #0066cc; color: #fff; text-decoration: none; border: none; cursor: pointer; border-radius: 4px; width: fit-content; }
 @media print { .print-btn { display: none; } body { padding: 0; } }
</style>
</head>
<body>

<button class="print-btn" onclick="window.print()">Print Report</button>

<h1>Case Report: <?= htmlspecialchars($case['title'] ?: 'Case #'.$id) ?></h1>

<div class="info-grid">
 <div>
  <strong>Case ID:</strong> #<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?><br>
  <strong>Status:</strong> <?= htmlspecialchars($case['case_status']) ?><br>
  <strong>Open Date:</strong> <?= fmt_date($case['open_date']) ?><br>
  <strong>Close Date:</strong> <?= $case['close_date'] ? fmt_date($case['close_date']) : 'N/A' ?><br>
  <strong>Lead Officer:</strong> <?= $case['lead_first'] ? htmlspecialchars($case['lead_first'].' '.$case['lead_last']) : 'Unassigned' ?>
 </div>
 <div>
  <strong>Crime Type:</strong> <?= htmlspecialchars($case['crime_type']) ?><br>
  <strong>Severity:</strong> <?= htmlspecialchars($case['severity']) ?><br>
  <strong>Date Occurred:</strong> <?= fmt_date($case['date_occurred']) ?><br>
  <strong>Location:</strong> <?= htmlspecialchars($case['location']) ?>
 </div>
</div>

<h2>Description / Notes</h2>
<p><?= nl2br(htmlspecialchars($case['description'] ?: 'No notes available.')) ?></p>

<h2>Assigned Officers</h2>
<?php if(empty($officers)): ?>
<p>No officers assigned.</p>
<?php else: ?>
<table>
 <thead><tr><th>Name</th><th>Badge</th><th>Rank</th><th>Role</th></tr></thead>
 <tbody>
  <?php foreach($officers as $o): ?>
  <tr>
   <td><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?></td>
   <td><?= htmlspecialchars($o['badge_number']) ?></td>
   <td><?= htmlspecialchars($o['rank']) ?></td>
   <td><?= htmlspecialchars($o['oc_role']) ?></td>
  </tr>
  <?php endforeach; ?>
 </tbody>
</table>
<?php endif; ?>

<h2>Linked Criminals (Suspects / Perpetrators)</h2>
<?php if(empty($criminals)): ?>
<p>No criminals linked.</p>
<?php else: ?>
<table>
 <thead><tr><th>Name</th><th>Status</th><th>Role</th></tr></thead>
 <tbody>
  <?php foreach($criminals as $c): ?>
  <tr>
   <td><?= htmlspecialchars($c['first_name'].' '.$c['last_name']) ?></td>
   <td><?= htmlspecialchars($c['status']) ?></td>
   <td><?= htmlspecialchars($c['cr_role']) ?></td>
  </tr>
  <?php endforeach; ?>
 </tbody>
</table>
<?php endif; ?>

<h2>Victims</h2>
<?php if(empty($victims)): ?>
<p>No victims recorded.</p>
<?php else: ?>
<table>
 <thead><tr><th>Name</th><th>Contact</th><th>Statement</th></tr></thead>
 <tbody>
  <?php foreach($victims as $v): ?>
  <tr>
   <td><?= htmlspecialchars($v['first_name'].' '.$v['last_name']) ?></td>
   <td><?= htmlspecialchars($v['contact_number'] ?: $v['phone'] ?? 'N/A') ?></td>
   <td><?= nl2br(htmlspecialchars($v['statement'])) ?></td>
  </tr>
  <?php endforeach; ?>
 </tbody>
</table>
<?php endif; ?>

<div style="margin-top:50px; border-top:1px solid #ccc; padding-top:10px; font-size:12px; color:#666; text-align:center;">
 Generated by Criminal Record Management System on <?= date('Y-m-d H:i:s') ?>
</div>

</body>
</html>
