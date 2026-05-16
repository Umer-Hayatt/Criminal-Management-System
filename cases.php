<?php
include 'db.php';
require_login();
$pageTitle='Cases';
include 'header.php';

// If user is a viewer, require explicit permission to view cases
if(($_SESSION['role'] ?? '') === 'viewer' && !has_view_permission($_SESSION['user_id'] ?? 0)){
 ?>
 <div class="page-hdr">
  <div class="page-hdr-left">
   <div class="page-hdr-icon"><i data-lucide="folder-open"></i></div>
   <div><h2>All Cases</h2><p>Access restricted — request permission to view.</p></div>
  </div>
  <a href="request_view.php" class="btn btn-primary"><i data-lucide="unlock"></i> Request Access</a>
 </div>
 <div class="card"><div style="padding:20px">You don't currently have permission to view cases. Click Request Access to ask an admin for permission.</div></div>
 <?php
 include 'footer.php';
 exit();
}
$total =mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Case_Record"))[0];
$open  =mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Case_Record WHERE case_status='Open'"))[0];
$inv   =mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Case_Record WHERE case_status='Under Investigation'"))[0];
$closed=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Case_Record WHERE case_status='Closed'"))[0];
$cases =mysqli_query($conn,"SELECT c.*,cr.crime_type,(SELECT COUNT(*) FROM case_criminals cc WHERE cc.case_id=c.case_id) AS crim_count,(SELECT COUNT(*) FROM case_officers co WHERE co.case_id=c.case_id) AS off_count FROM Case_Record c JOIN Crime cr ON c.crime_id=cr.crime_id ORDER BY c.case_id ASC");
?>
<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="folder-open"></i></div>
  <div><h2>All Cases</h2><p><?=$total?> total cases</p></div>
 </div>
 <?php if(can('edit')): ?><a href="register_case.php" class="btn btn-primary"><i data-lucide="plus"></i> New Case</a><?php endif; ?>
</div>
<div class="summary-pills">
 <div class="spill sp-total active" onclick="filterCases('')" style="cursor:pointer"><span><?=$total?></span> All</div>
 <div class="spill sp-wanted" onclick="filterCases('Open')" style="cursor:pointer"><span><?=$open?></span> Open</div>
 <div class="spill sp-imprisoned" onclick="filterCases('Under Investigation')" style="cursor:pointer"><span><?=$inv?></span> Investigating</div>
 <div class="spill sp-released" onclick="filterCases('Closed')" style="cursor:pointer"><span><?=$closed?></span> Closed</div>
</div>
<div class="card">
 <div class="tbl-wrap"><table>
  <thead><tr><th>#</th><th>Case ID</th><th>Title / Crime</th><th>Status</th><th>Criminals</th><th>Officers</th><th>Opened</th><th>Actions</th></tr></thead>
  <tbody id="casesBody">
  <?php $row=0; if(mysqli_num_rows($cases)===0): ?>
  <tr class="empty-row"><td colspan="8">No cases found.</td></tr>
  <?php else: while($r=mysqli_fetch_assoc($cases)): $row++;
   $cs=match($r['case_status']){'Open'=>'b-orange','Closed'=>'b-green','Under Investigation'=>'b-blue',default=>'b-muted'};
   $label=$r['title']?htmlspecialchars($r['title']):'<span style="color:var(--txt-soft)">Case #'.str_pad($r['case_id'],4,'0',STR_PAD_LEFT).'</span>';
  ?>
  <tr data-status="<?=htmlspecialchars($r['case_status'])?>">
   <td style="color:var(--txt-soft);font-size:12px"><?=$row?></td>
   <td><strong style="color:var(--accent)">Case #<?=str_pad($r['case_id'],4,'0',STR_PAD_LEFT)?></strong></td>
   <td><?=$label?></td>
   <td><span class="badge <?=$cs?>"><?=$r['case_status']?></span></td>
   <td><span class="badge b-red"><?=$r['crim_count']?></span></td>
   <td><span class="badge b-blue"><?=$r['off_count']?></span></td>
   <td><?=fmt_date($r['open_date'])?></td>
   <td><div class="td-actions">
    <a href="case_profile.php?id=<?=$r['case_id']?>" class="btn btn-ghost btn-sm"><i data-lucide="eye"></i> View</a>
    <?php if(can('delete')): ?><button onclick="confirmDelete('delete_case.php?id=<?=$r['case_id']?>','Delete Case #<?=$r['case_id']?>?','Remove Case #<?=$r['case_id']?> and all associated records?')" class="btn btn-danger btn-sm"><i data-lucide="trash-2"></i></button><?php endif; ?>
   </div></td>
  </tr>
  <?php endwhile; endif; ?>
  </tbody>
 </table></div>
</div>
<script>
var activeFilter='';
function filterCases(status){
 var pills=document.querySelectorAll('.spill');
 if(activeFilter===status){activeFilter='';pills.forEach(p=>p.classList.remove('active'));pills[0].classList.add('active');}
 else{activeFilter=status;pills.forEach(p=>p.classList.remove('active'));
  if(status==='')pills[0].classList.add('active');
  else if(status==='Open')pills[1].classList.add('active');
  else if(status==='Under Investigation')pills[2].classList.add('active');
  else if(status==='Closed')pills[3].classList.add('active');
 }
 var rows=document.querySelectorAll('#casesBody tr[data-status]');
 var num=0;
 rows.forEach(function(row){
  if(!activeFilter||row.dataset.status===activeFilter){row.style.display='';num++;row.cells[0].textContent=num;}
  else{row.style.display='none';}
 });
}
</script>
<style>.spill.active{border-color:var(--accent);background:var(--accent-lt);color:var(--accent);}</style>
<?php include 'footer.php'; ?>
