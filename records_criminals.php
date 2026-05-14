<?php
include 'db.php';
$pageTitle='Criminals';
include 'header.php';
$total   =mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal"))[0];
$wanted  =mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal WHERE status='Wanted'"))[0];
$imprisoned=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal WHERE status='Imprisoned'"))[0];
$released=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal WHERE status='Released'"))[0];
$criminals=mysqli_query($conn,"SELECT * FROM Criminal ORDER BY criminal_id ASC");
?>
<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="users"></i></div>
  <div><h2>Criminal Records</h2><p><?=$total?> registered criminals</p></div>
 </div>
 <?php if(can('edit')): ?><a href="register_criminal.php" class="btn btn-primary"><i data-lucide="plus"></i> Register Criminal</a><?php endif; ?>
</div>

<div class="summary-pills" id="statusFilters">
 <div class="spill sp-total filter-pill active" data-status="" onclick="filterStatus(this,'')"><span><?=$total?></span> All</div>
 <div class="spill sp-wanted filter-pill"     data-status="Wanted"     onclick="filterStatus(this,'Wanted')"><span><?=$wanted?></span> Wanted</div>
 <div class="spill sp-imprisoned filter-pill" data-status="Imprisoned" onclick="filterStatus(this,'Imprisoned')"><span><?=$imprisoned?></span> Imprisoned</div>
 <div class="spill sp-released filter-pill"   data-status="Released"   onclick="filterStatus(this,'Released')"><span><?=$released?></span> Released</div>
</div>

<div class="card">
 <div class="tbl-wrap"><table id="criminalsTable">
  <thead><tr><th>#</th><th>Photo</th><th>Name</th><th>Gender</th><th>Nationality</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php $row=0; while($r=mysqli_fetch_assoc($criminals)):
   $row++;
   $b=match($r['status']){'Imprisoned'=>'b-red','Released'=>'b-green','Wanted'=>'b-orange',default=>'b-blue'};
   $hasPhoto=(!empty($r['photo'])&&file_exists($r['photo']));
  ?>
  <tr data-status="<?=htmlspecialchars($r['status'])?>">
   <td style="color:var(--txt-soft);font-size:12px"><?=$row?></td>
   <td><?php if($hasPhoto): ?><img src="<?=htmlspecialchars($r['photo'])?>" class="tbl-avatar" alt="" onerror="this.outerHTML=document.getElementById('anon-tpl').innerHTML"><?php else: ?><img src="assets/anon.svg" class="tbl-avatar" alt=""><?php endif; ?></td>
   <td>
    <div class="person-cell">
     <div>
      <strong><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></strong>
      <?php if($r['phone']): ?><div class="txt-muted"><?=htmlspecialchars($r['phone'])?></div><?php endif; ?>
     </div>
    </div>
   </td>
   <td><?=htmlspecialchars($r['gender']??'—')?></td>
   <td><?=htmlspecialchars($r['nationality']??'—')?></td>
   <td><span class="badge <?=$b?>"><?=$r['status']?></span></td>
   <td><div class="td-actions">
    <a href="profile.php?id=<?=$r['criminal_id']?>" class="btn btn-ghost btn-sm"><i data-lucide="eye"></i> Profile</a>
    <?php if(can('edit')): ?><a href="edit_criminal.php?id=<?=$r['criminal_id']?>" class="btn btn-ghost btn-sm"><i data-lucide="edit-2"></i></a><?php endif; ?>
    <?php if(can('delete')): ?><button onclick="confirmDelete('delete_criminal.php?id=<?=$r['criminal_id']?>','Delete Criminal?','Remove <?=htmlspecialchars(addslashes($r['first_name'].' '.$r['last_name']))?> permanently?')" class="btn btn-danger btn-sm"><i data-lucide="trash-2"></i></button><?php endif; ?>
   </div></td>
  </tr>
  <?php endwhile; ?>
  </tbody>
 </table></div>
</div>

<template id="anon-tpl"><img src="assets/anon.svg" class="tbl-avatar" alt=""></template>

<style>
.filter-pill{cursor:pointer;transition:var(--trans);}
.filter-pill:hover{transform:translateY(-1px);filter:brightness(1.15);}
.filter-pill.active{outline:2px solid var(--accent);outline-offset:2px;}
</style>
<script>
function filterStatus(el,status){
 document.querySelectorAll('.filter-pill').forEach(p=>p.classList.remove('active'));
 el.classList.add('active');
 document.querySelectorAll('#criminalsTable tbody tr').forEach(function(tr){
  tr.style.display=(!status||tr.dataset.status===status)?'':'none';
 });
 var shown=[...document.querySelectorAll('#criminalsTable tbody tr')].filter(r=>r.style.display!=='none');
 shown.forEach((r,i)=>r.cells[0].textContent=i+1);
}
</script>
<?php include 'footer.php'; ?>
