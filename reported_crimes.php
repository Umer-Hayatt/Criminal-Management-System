<?php
include 'db.php';
require_login();
$pageTitle = 'Reported Crimes';
include 'header.php';

$crimes = mysqli_query($conn, "SELECT c.*, (SELECT COUNT(*) FROM Victim v WHERE v.crime_id=c.crime_id) AS vic_count FROM Crime c WHERE c.crime_id NOT IN (SELECT crime_id FROM Case_Record WHERE crime_id IS NOT NULL) ORDER BY c.date_occurred DESC");
$total = mysqli_num_rows($crimes);
?>
<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="alert-triangle"></i></div>
  <div><h2>Reported Crimes</h2><p><?= $total ?> reported crimes pending review</p></div>
 </div>
 <?php if(can('viewer')): ?>
 <a href="report_crime.php" class="btn btn-primary"><i data-lucide="plus"></i> Report Crime</a>
 <?php endif; ?>
</div>

<div class="card">
 <div class="tbl-wrap"><table>
  <thead><tr><th>#</th><th>Crime ID</th><th>Type</th><th>Location</th><th>Date Occurred</th><th>Severity</th><th>Victims</th><th>Actions</th></tr></thead>
  <tbody>
  <?php $row=0; if($total===0): ?>
  <tr class="empty-row"><td colspan="8">No reported crimes pending.</td></tr>
  <?php else: while($r=mysqli_fetch_assoc($crimes)): $row++; 
   $sev = match($r['severity']){'Minor'=>'b-green','Major'=>'b-orange','Felony'=>'b-red',default=>'b-muted'};
  ?>
  <tr>
   <td style="color:var(--txt-soft);font-size:12px"><?= $row ?></td>
   <td><strong style="color:var(--accent)">CR-<?= str_pad($r['crime_id'],4,'0',STR_PAD_LEFT) ?></strong></td>
   <td><?= htmlspecialchars($r['crime_type']) ?></td>
   <td><?= htmlspecialchars($r['location']) ?></td>
   <td><?= fmt_date($r['date_occurred']) ?></td>
   <td><span class="badge <?= $sev ?>"><?= $r['severity'] ?></span></td>
   <td><span class="badge b-blue"><?= $r['vic_count'] ?></span></td>
   <td><div class="td-actions">
    <?php if(can('edit')): ?>
    <a href="register_case.php?crime_id=<?= $r['crime_id'] ?>" class="btn btn-primary btn-sm"><i data-lucide="folder-plus"></i> Make Case</a>
    <?php endif; ?>
   </div></td>
  </tr>
  <?php endwhile; endif; ?>
  </tbody>
 </table></div>
</div>

<?php include 'footer.php'; ?>
