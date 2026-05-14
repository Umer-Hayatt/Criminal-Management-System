<?php
include 'db.php';
$pageTitle='All Cases';
include 'header.php';
?>
<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="folder-open"></i></div>
  <div><h2>Cases</h2><p>All investigation cases</p></div>
 </div>
 <a href="register_case.php" class="btn btn-primary"><i data-lucide="folder-plus"></i> New Case</a>
</div>
<div class="card">
 <div class="tbl-wrap"><table>
  <thead><tr><th>Case #</th><th>Crime Type</th><th>Status</th><th>Criminals</th><th>Officers</th><th>Opened</th><th>Actions</th></tr></thead>
  <tbody>
  <?php
  $res=mysqli_query($conn,"SELECT c.*,cr.crime_type FROM Case_Record c JOIN Crime cr ON c.crime_id=cr.crime_id ORDER BY c.case_id DESC");
  if(mysqli_num_rows($res)===0): ?>
  <tr class="empty-row"><td colspan="7"><i data-lucide="folder-open" style="width:24px;height:24px;margin:0 auto 8px;display:block;opacity:.3"></i>No cases yet. <a href="register_case.php" style="color:var(--accent)">Open one now</a></td></tr>
  <?php else: while($r=mysqli_fetch_assoc($res)):
   $cs=match($r['case_status']){'Open'=>'b-orange','Closed'=>'b-green','Under Investigation'=>'b-blue',default=>'b-muted'};
   $crim_cnt=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(DISTINCT cc.criminal_id) FROM Criminal_Crime cc WHERE cc.crime_id={$r['crime_id']}"))[0];
   $off_cnt=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Officer_Case WHERE case_id={$r['case_id']}"))[0];
  ?>
  <tr>
   <td><strong>#<?=$r['case_id']?></strong></td>
   <td><?=htmlspecialchars($r['crime_type'])?></td>
   <td><span class="badge <?=$cs?>"><?=$r['case_status']?></span></td>
   <td><span class="badge b-muted"><?=$crim_cnt?> criminal<?=$crim_cnt!=1?'s':''?></span></td>
   <td><span class="badge b-blue"><?=$off_cnt?> officer<?=$off_cnt!=1?'s':''?></span></td>
   <td><?=fmt_date($r['open_date'])?></td>
   <td><div class="td-actions">
    <a href="case_profile.php?id=<?=$r['case_id']?>" class="btn btn-ghost btn-sm"><i data-lucide="eye"></i> View</a>
    <?php if(can('edit')): ?><a href="edit_case.php?id=<?=$r['case_id']?>" class="btn btn-ghost btn-sm"><i data-lucide="edit-2"></i></a><?php endif; ?>
    <?php if(can('delete')): ?><button onclick="confirmDelete('delete_case.php?id=<?=$r['case_id']?>','Delete Case?','Remove Case #<?=$r['case_id']?> and all related records?')" class="btn btn-danger btn-sm"><i data-lucide="trash-2"></i></button><?php endif; ?>
   </div></td>
  </tr>
  <?php endwhile; endif; ?>
  </tbody>
 </table></div>
</div>
<?php include 'footer.php'; ?>
