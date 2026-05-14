<?php
include 'db.php';
$pageTitle = 'Prisons';
include 'header.php';
?>
<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="building-2"></i></div>
  <div><h2>Prisons</h2><p>All registered prison facilities</p></div>
 </div>
 <a href="register_prison.php" class="btn btn-primary"><i data-lucide="plus-square"></i> Add Prison</a>
</div>
<div class="card">
 <div class="tbl-wrap">
  <table>
   <thead><tr><th>#</th><th>Prison Name</th><th>Location</th><th>Capacity</th><th>Current Inmates</th><th>Actions</th></tr></thead>
   <tbody>
   <?php
   $res=mysqli_query($conn,"SELECT * FROM Prison ORDER BY prison_name");
   if(mysqli_num_rows($res)===0): ?>
   <tr class="empty-row"><td colspan="6"><i data-lucide="building-2" style="width:24px;height:24px;margin:0 auto 8px;display:block;opacity:.3"></i>No prisons registered.</td></tr>
   <?php else: while($r=mysqli_fetch_assoc($res)):
    $occ=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Imprisonment WHERE prison_id={$r['prison_id']} AND (end_date IS NULL OR end_date >= CURDATE())"))[0];
    $pct=$r['capacity']>0?round($occ/$r['capacity']*100):0;
    $bar_color=$pct>80?'var(--danger)':($pct>50?'var(--warning)':'var(--success)');
   ?>
   <tr>
    <td style="color:var(--txt-soft)">#<?=$r['prison_id']?></td>
    <td><strong><?=htmlspecialchars($r['prison_name'])?></strong></td>
    <td><?=htmlspecialchars($r['location']??'—')?></td>
    <td><?=number_format($r['capacity'])?></td>
    <td>
     <div style="display:flex;align-items:center;gap:8px;">
      <div style="flex:1;height:6px;background:var(--surface-2);border-radius:4px;overflow:hidden">
       <div style="width:<?=$pct?>%;height:100%;background:<?=$bar_color?>;border-radius:4px;transition:width .6s"></div>
      </div>
      <span style="font-size:12px;color:var(--txt-mid);white-space:nowrap"><?=$occ?> / <?=number_format($r['capacity'])?></span>
     </div>
    </td>
    <td><div class="td-actions">
     <?php if(can('delete')): ?>
     <button onclick="confirmDelete('delete_prison.php?id=<?=$r['prison_id']?>','Delete Prison?','Remove <?=htmlspecialchars(addslashes($r['prison_name']))?> from the system?')" class="btn btn-danger btn-sm"><i data-lucide="trash-2"></i></button>
     <?php endif; ?>
    </div></td>
   </tr>
   <?php endwhile; endif; ?>
   </tbody>
  </table>
 </div>
</div>
<?php include 'footer.php'; ?>
