<?php
include 'db.php';
$pageTitle = 'Criminals';
include 'header.php';

$total      = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal"))[0];
$wanted     = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal WHERE status='Wanted'"))[0];
$imprisoned = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal WHERE status='Imprisoned'"))[0];
$released   = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal WHERE status='Released'"))[0];

// Auto-update overdue releases
mysqli_query($conn,"UPDATE Criminal c JOIN Imprisonment i ON c.criminal_id=i.criminal_id SET c.status='Released' WHERE i.release_date IS NOT NULL AND i.release_date < CURDATE() AND c.status='Imprisoned'");
?>

<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="users"></i></div>
  <div><h2>Criminals</h2><p>All registered criminal records</p></div>
 </div>
 <a href="register_criminal.php" class="btn btn-primary"><i data-lucide="user-plus"></i> Register Criminal</a>
</div>

<div class="summary-pills">
 <div class="spill sp-total"><i data-lucide="users" style="width:14px;height:14px"></i><span><?=$total?></span> Total</div>
 <div class="spill sp-wanted"><i data-lucide="alert-triangle" style="width:14px;height:14px;color:var(--warning)"></i><span><?=$wanted?></span> Wanted</div>
 <div class="spill sp-imprisoned"><i data-lucide="lock" style="width:14px;height:14px;color:var(--danger)"></i><span><?=$imprisoned?></span> Imprisoned</div>
 <div class="spill sp-released"><i data-lucide="check-circle" style="width:14px;height:14px;color:var(--success)"></i><span><?=$released?></span> Released</div>
</div>

<div class="card">
 <div class="tbl-wrap">
  <table>
   <thead><tr><th>#</th><th>Criminal</th><th>Status</th><th>Nationality</th><th>Phone</th><th>Actions</th></tr></thead>
   <tbody>
   <?php
   $res = mysqli_query($conn,"SELECT * FROM Criminal ORDER BY criminal_id DESC");
   if(mysqli_num_rows($res)===0): ?>
   <tr class="empty-row"><td colspan="6"><i data-lucide="users" style="width:24px;height:24px;margin:0 auto 8px;display:block;opacity:.3"></i>No criminals registered yet. <a href="register_criminal.php" style="color:var(--accent)">Register one now</a></td></tr>
   <?php else: while($r=mysqli_fetch_assoc($res)):
    $b=match($r['status']){'Imprisoned'=>'b-red','Released'=>'b-green','Wanted'=>'b-orange',default=>'b-blue'};
    $photo=$r['photo']??'assets/anon.png';
   ?>
   <tr>
    <td style="color:var(--txt-soft)">#<?=$r['criminal_id']?></td>
    <td>
     <div class="person-cell">
      <img src="<?=htmlspecialchars($photo)?>" class="tbl-avatar" alt="" onerror="this.src='assets/anon.png'">
      <div>
       <strong><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></strong>
       <span class="txt-muted"><?=htmlspecialchars($r['nationality']??'')?></span>
      </div>
     </div>
    </td>
    <td><span class="badge <?=$b?>"><?=$r['status']?></span></td>
    <td><?=htmlspecialchars($r['nationality']??'—')?></td>
    <td><?=htmlspecialchars($r['phone']??'—')?></td>
    <td><div class="td-actions">
     <a href="profile.php?id=<?=$r['criminal_id']?>" class="btn btn-ghost btn-sm"><i data-lucide="eye"></i> Profile</a>
     <?php if(can('edit')): ?>
     <a href="edit_criminal.php?id=<?=$r['criminal_id']?>" class="btn btn-ghost btn-sm"><i data-lucide="edit-2"></i> Edit</a>
     <?php endif; ?>
     <?php if(can('delete')): ?>
     <button onclick="confirmDelete('delete_criminal.php?id=<?=$r['criminal_id']?>','Delete Criminal?','Remove <?=htmlspecialchars(addslashes($r['first_name'].' '.$r['last_name']))?> and all related records permanently?')" class="btn btn-danger btn-sm"><i data-lucide="trash-2"></i></button>
     <?php endif; ?>
    </div></td>
   </tr>
   <?php endwhile; endif; ?>
   </tbody>
  </table>
 </div>
</div>
<?php include 'footer.php'; ?>
