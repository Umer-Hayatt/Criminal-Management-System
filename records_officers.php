<?php
include 'db.php';
$pageTitle='Officers';
include 'header.php';
$total=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Officer"))[0];
?>
<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="shield"></i></div>
  <div><h2>Officers</h2><p><?=$total?> registered investigating officers</p></div>
 </div>
 <?php if(can('admin')): ?><a href="register_officer.php" class="btn btn-primary"><i data-lucide="badge"></i> Register Officer</a><?php endif; ?>
</div>
<div class="card">
 <div class="tbl-wrap"><table>
  <thead><tr><th>#</th><th>Photo</th><th>Name</th><th>Badge</th><th>Rank</th><th>Department</th><th>Phone</th><th>Actions</th></tr></thead>
  <tbody>
  <?php
  $res=mysqli_query($conn,"SELECT * FROM Officer ORDER BY officer_id ASC");
  if(mysqli_num_rows($res)===0): ?>
  <tr class="empty-row"><td colspan="8">No officers registered.</td></tr>
  <?php else: $row=0; while($r=mysqli_fetch_assoc($res)): $row++;
   $hasPhoto=(!empty($r['photo'])&&file_exists($r['photo']));
  ?>
  <tr>
   <td style="color:var(--txt-soft);font-size:12px"><?=$row?></td>
   <td><?php if($hasPhoto): ?><img src="<?=htmlspecialchars($r['photo'])?>" class="tbl-avatar" alt=""><?php else: ?><img src="assets/anon.svg" class="tbl-avatar" alt=""><?php endif; ?></td>
   <td><strong><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></strong></td>
   <td><span class="badge b-blue"><?=htmlspecialchars($r['badge_number']??'—')?></span></td>
   <td><?=htmlspecialchars($r['rank']??'—')?></td>
   <td><?=htmlspecialchars($r['department']??'—')?></td>
   <td><?=htmlspecialchars($r['phone']??'—')?></td>
   <td><div class="td-actions">
    <a href="officer_profile.php?id=<?=$r['officer_id']?>" class="btn btn-ghost btn-sm"><i data-lucide="eye"></i> Profile</a>
    <?php if(can('admin')): ?><a href="edit_officer.php?id=<?=$r['officer_id']?>" class="btn btn-ghost btn-sm"><i data-lucide="edit-2"></i></a><?php endif; ?>
    <?php if(can('delete')): ?><button onclick="confirmDelete('delete_officer.php?id=<?=$r['officer_id']?>','Delete Officer?','Remove <?=htmlspecialchars(addslashes($r['first_name'].' '.$r['last_name']))?> permanently?')" class="btn btn-danger btn-sm"><i data-lucide="trash-2"></i></button><?php endif; ?>
   </div></td>
  </tr>
  <?php endwhile; endif; ?>
  </tbody>
 </table></div>
</div>
<?php include 'footer.php'; ?>
