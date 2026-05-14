<?php
include 'db.php';
$pageTitle='Warrants';
mysqli_query($conn,"UPDATE Warrant SET status='Expired' WHERE expiry_date < CURDATE() AND status='Active'");
include 'header.php';
?>
<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="file-warning"></i></div>
  <div><h2>Warrants</h2><p>All active and historical warrants</p></div>
 </div>
</div>
<div class="card">
 <div class="tbl-wrap"><table>
  <thead><tr><th>#</th><th>Type</th><th>Criminal</th><th>Case</th><th>Issued</th><th>Expiry</th><th>Status</th></tr></thead>
  <tbody>
  <?php
  $res=mysqli_query($conn,"SELECT w.*,CONCAT(c.first_name,' ',c.last_name) AS cname FROM Warrant w LEFT JOIN Criminal c ON w.criminal_id=c.criminal_id ORDER BY w.issued_date DESC");
  if(mysqli_num_rows($res)===0):?>
  <tr class="empty-row"><td colspan="7">No warrants issued.</td></tr>
  <?php else: while($r=mysqli_fetch_assoc($res)):
   $sb=match($r['status']){'Active'=>'b-blue','Expired'=>'b-muted','Executed'=>'b-green',default=>'b-muted'};
   $tb=match($r['type']??''){'Arrest'=>'b-red','Search'=>'b-orange',default=>'b-muted'};
  ?>
  <tr>
   <td style="color:var(--txt-soft)">#<?=$r['warrant_id']?></td>
   <td><span class="badge <?=$tb?>"><?=$r['type']?></span></td>
   <td><?=$r['cname']?htmlspecialchars($r['cname']):'—'?></td>
   <td><a href="case_profile.php?id=<?=$r['case_id']?>" style="color:var(--accent)">Case #<?=$r['case_id']?></a></td>
   <td><?=fmt_date($r['issued_date'])?></td>
   <td><?=fmt_date($r['expiry_date'])?></td>
   <td><span class="badge <?=$sb?>"><?=$r['status']?></span></td>
  </tr>
  <?php endwhile; endif; ?>
  </tbody>
 </table></div>
</div>
<?php include 'footer.php'; ?>
