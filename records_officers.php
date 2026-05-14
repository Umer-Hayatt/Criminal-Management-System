<?php
include 'db.php';
$pageTitle = 'Officers';
include 'header.php';
?>
<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="shield"></i></div>
  <div><h2>Officers</h2><p>All registered investigating officers</p></div>
 </div>
 <a href="register_officer.php" class="btn btn-primary"><i data-lucide="badge"></i> Register Officer</a>
</div>
<div class="card">
 <div class="tbl-wrap">
  <table>
   <thead><tr><th>#</th><th>Officer</th><th>Badge</th><th>Rank</th><th>Department</th><th>Phone</th><th>Actions</th></tr></thead>
   <tbody>
   <?php
   $res=mysqli_query($conn,"SELECT * FROM Officer ORDER BY first_name");
   if(mysqli_num_rows($res)===0): ?>
   <tr class="empty-row"><td colspan="7"><i data-lucide="shield" style="width:24px;height:24px;margin:0 auto 8px;display:block;opacity:.3"></i>No officers registered.</td></tr>
   <?php else: while($r=mysqli_fetch_assoc($res)):
    $photo=$r['photo']??'assets/anon.png';
   ?>
   <tr>
    <td style="color:var(--txt-soft)">#<?=$r['officer_id']?></td>
    <td>
     <div class="person-cell">
      <img src="<?=htmlspecialchars($photo)?>" class="tbl-avatar" alt="" onerror="this.src='assets/anon.png'">
      <div>
       <strong><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></strong>
       <span class="txt-muted"><?=htmlspecialchars($r['rank']??'')?></span>
      </div>
     </div>
    </td>
    <td><span class="badge b-blue"><?=htmlspecialchars($r['badge_number']??'—')?></span></td>
    <td><?=htmlspecialchars($r['rank']??'—')?></td>
    <td><?=htmlspecialchars($r['department']??'—')?></td>
    <td><?=htmlspecialchars($r['phone']??'—')?></td>
    <td><div class="td-actions">
     <a href="officer_profile.php?id=<?=$r['officer_id']?>" class="btn btn-ghost btn-sm"><i data-lucide="eye"></i> Profile</a>
     <?php if(can('edit')): ?>
     <a href="edit_officer.php?id=<?=$r['officer_id']?>" class="btn btn-ghost btn-sm"><i data-lucide="edit-2"></i> Edit</a>
     <?php endif; ?>
     <?php if(can('delete')): ?>
     <button onclick="confirmDelete('delete_officer.php?id=<?=$r['officer_id']?>','Delete Officer?','Remove <?=htmlspecialchars(addslashes($r['first_name'].' '.$r['last_name']))?> from the system?')" class="btn btn-danger btn-sm"><i data-lucide="trash-2"></i></button>
     <?php endif; ?>
    </div></td>
   </tr>
   <?php endwhile; endif; ?>
   </tbody>
  </table>
 </div>
</div>
<?php include 'footer.php'; ?>
