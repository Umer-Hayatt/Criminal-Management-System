<?php
include 'db.php';
$pageTitle = 'All Criminal Records';
include 'header.php';
?>
<div class="page-hdr">
 <div><h2> All Criminal Records</h2><p>Complete list of all registered criminals</p></div>
 <a href="register.php" class="btn btn-primary"> Register New Criminal</a>
</div>
<?php if(isset($_GET['msg'])): ?>
 <div class="alert alert-success"> <?=htmlspecialchars($_GET['msg'])?></div>
<?php endif; ?>
<div class="card">
 <div class="tbl-wrap"><table>
 <thead><tr><th>#</th><th>Full Name</th><th>Gender</th><th>Nationality</th><th>Phone</th><th>Address</th><th>Status</th><th>Actions</th></tr></thead>
 <tbody>
 <?php
 $res = mysqli_query($conn, "SELECT * FROM Criminal ORDER BY criminal_id DESC");
 if (mysqli_num_rows($res) === 0):
 ?><tr class="empty-row"><td colspan="8">No criminals registered yet. <a href="register.php">Register one now →</a></td></tr>
 <?php else: while($r=mysqli_fetch_assoc($res)):
 $b = match($r['status']){'Imprisoned'=>'b-red','Released'=>'b-green','Wanted'=>'b-orange',default=>'b-blue'};
 ?>
 <tr>
 <td style="color:#999;vertical-align:middle;">#<?=$r['criminal_id']?></td>
 <td><strong><?=$r['first_name'].' '.$r['last_name']?></strong></td>
 <td><?=$r['gender']?></td>
 <td><?=$r['nationality']?></td>
 <td><?=$r['phone']?></td>
 <td style="font-size:12px;color:#666;"><?=substr($r['address'],0,35)?>...</td>
 <td><span class="badge <?=$b?>"><?=$r['status']?></span></td>
 <td><div class="td-actions">
 <a href="profile.php?id=<?=$r['criminal_id']?>" class="btn btn-blue btn-sm"> Profile</a>
 <a href="edit_criminal.php?id=<?=$r['criminal_id']?>" class="btn btn-gray btn-sm"> Edit</a>
 <a href="delete_criminal.php?id=<?=$r['criminal_id']?>" class="btn btn-danger btn-sm"
 onclick="return confirm('Delete this criminal and all related records?')"></a>
 </div></td>
 </tr>
 <?php endwhile; endif; ?>
 </tbody>
 </table></div>
</div>
<?php include 'footer.php'; ?>
