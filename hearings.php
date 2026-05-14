<?php
include 'db.php';
$pageTitle='Court Hearings';
include 'header.php';
$hearings=mysqli_query($conn,"SELECT h.*,c.case_id,cr.crime_type FROM Court_Hearing h LEFT JOIN Case_Record c ON h.case_id=c.case_id LEFT JOIN Crime cr ON c.crime_id=cr.crime_id ORDER BY h.hearing_date DESC");
?>
<div class="page-hdr">
 <div class="page-hdr-left"><div class="page-hdr-icon"><i data-lucide="scale"></i></div><div><h2>Court Hearings</h2><p>All scheduled and completed hearings</p></div></div>
</div>
<div class="card">
 <div class="tbl-wrap"><table>
  <thead><tr><th>#</th><th>Court</th><th>Case</th><th>Judge</th><th>Date</th><th>Verdict</th><th>Next</th><th>Actions</th></tr></thead>
  <tbody>
  <?php if(mysqli_num_rows($hearings)===0): ?>
  <tr class="empty-row"><td colspan="8">No hearings scheduled.</td></tr>
  <?php else: while($r=mysqli_fetch_assoc($hearings)):
   $vb=match($r['verdict']??''){'Guilty'=>'b-red','Not Guilty'=>'b-green','Pending'=>'b-orange',default=>'b-muted'};
  ?>
  <tr>
   <td style="color:var(--txt-soft)">#<?=$r['hearing_id']?></td>
   <td><strong><?=htmlspecialchars($r['court_name']??'—')?></strong></td>
   <td><?php if($r['case_id']): ?><a href="case_profile.php?id=<?=$r['case_id']?>#tab-hearings" style="color:var(--accent)">Case #<?=$r['case_id']?></a><br><span style="font-size:11px;color:var(--txt-soft)"><?=htmlspecialchars($r['crime_type']??'')?></span><?php else: ?>—<?php endif; ?></td>
   <td><?=htmlspecialchars($r['judge_name']??'—')?></td>
   <td><?=fmt_date($r['hearing_date'])?></td>
   <td><span class="badge <?=$vb?>"><?=$r['verdict']??'—'?></span></td>
   <td><?=$r['next_hearing_date']?fmt_date($r['next_hearing_date']):'—'?></td>
   <td><div class="td-actions">
    <?php if(can('edit')): ?><a href="edit_hearing.php?id=<?=$r['hearing_id']?>&cid=0" class="btn btn-ghost btn-xs"><i data-lucide="edit-2"></i></a><?php endif; ?>
    <?php if(can('delete')): ?><button onclick="confirmDelete('delete_hearing.php?id=<?=$r['hearing_id']?>','Delete Hearing?','Remove hearing #<?=$r['hearing_id']?> at <?=htmlspecialchars(addslashes($r['court_name']??''))?>')" class="btn btn-danger btn-xs"><i data-lucide="trash-2"></i></button><?php endif; ?>
   </div></td>
  </tr>
  <?php endwhile; endif; ?>
  </tbody>
 </table></div>
</div>
<?php include 'footer.php'; ?>
