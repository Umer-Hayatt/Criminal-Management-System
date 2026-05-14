<?php
include 'db.php';
$pageTitle='Activity Log';
$page=max(1,intval($_GET['page']??1));
$per=50; $offset=($page-1)*$per;
$entity=esc($conn,$_GET['entity']??'');
$uid_filter=intval($_GET['uid']??0);
$search=esc($conn,$_GET['q']??'');

$where=['1=1'];
if(!can('admin')) $where[]="l.user_id=".(int)($_SESSION['user_id']??0);
if($entity) $where[]="l.entity_type='$entity'";
if($uid_filter&&can('admin')) $where[]="l.user_id=$uid_filter";
if($search) $where[]="(l.detail LIKE '%$search%' OR l.action LIKE '%$search%')";
$wc=implode(' AND ',$where);

$total=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Activity_Log l WHERE $wc"))[0];
$logs=mysqli_query($conn,"SELECT l.*,u.username,u.role FROM Activity_Log l LEFT JOIN users u ON l.user_id=u.user_id WHERE $wc ORDER BY l.logged_at DESC LIMIT $per OFFSET $offset");
$users=mysqli_query($conn,"SELECT user_id,username FROM users ORDER BY username");
$pages=ceil($total/$per);
include 'header.php';
?>
<div class="page-hdr">
 <div class="page-hdr-left"><div class="page-hdr-icon"><i data-lucide="activity"></i></div><div><h2>Activity Log</h2><p><?=number_format($total)?> total entries</p></div></div>
</div>
<form method="GET">
 <div class="filter-bar">
  <input type="text" name="q" placeholder="Search actions or details..." value="<?=htmlspecialchars($_GET['q']??'')?>">
  <select name="entity"><option value="">All Entities</option><?php foreach(['Criminal','Officer','Case','Hearing','Warrant','Prison','User'] as $et): ?><option <?=($entity===$et)?'selected':''?>><?=$et?></option><?php endforeach; ?></select>
  <?php if(can('admin')): ?><select name="uid"><option value="">All Users</option><?php while($u=mysqli_fetch_assoc($users)): ?><option value="<?=$u['user_id']?>" <?=($uid_filter==$u['user_id'])?'selected':''?>><?=htmlspecialchars($u['username'])?></option><?php endwhile; ?></select><?php endif; ?>
  <button type="submit" class="btn btn-primary"><i data-lucide="filter"></i> Filter</button>
  <a href="activity_log.php" class="btn btn-ghost">Clear</a>
 </div>
</form>
<div class="card">
 <div class="tbl-wrap"><table>
  <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>Detail</th><th>IP</th></tr></thead>
  <tbody>
  <?php if(mysqli_num_rows($logs)===0): ?>
  <tr class="empty-row"><td colspan="6">No log entries found.</td></tr>
  <?php else: while($r=mysqli_fetch_assoc($logs)):
   $ab=match($r['action']){'Created'=>'b-blue','Updated'=>'b-orange','Deleted'=>'b-red','Login'=>'b-green','Logout'=>'b-muted',default=>'b-muted'};
   $rb=match($r['role']??''){'admin'=>'rb-admin','officer'=>'rb-officer',default=>'rb-viewer'};
  ?>
  <tr>
   <td style="font-size:12px;color:var(--txt-mid);white-space:nowrap"><?=date('d M Y H:i',strtotime($r['logged_at']))?></td>
   <td><strong><?=htmlspecialchars($r['username']??'System')?></strong> <span class="role-badge <?=$rb?>"><?=$r['role']??''?></span></td>
   <td><span class="badge <?=$ab?>"><?=htmlspecialchars($r['action'])?></span></td>
   <td style="font-size:12px"><?=htmlspecialchars($r['entity_type'])?> #<?=$r['entity_id']?></td>
   <td style="font-size:12px;color:var(--txt-mid)"><?=htmlspecialchars(substr($r['detail']??'',0,80))?></td>
   <td style="font-size:11px;color:var(--txt-soft)"><?=htmlspecialchars($r['ip_address']??'')?></td>
  </tr>
  <?php endwhile; endif; ?>
  </tbody>
 </table></div>
</div>
<?php if($pages>1): ?>
<div style="display:flex;gap:6px;justify-content:center;margin-top:12px">
 <?php for($p=1;$p<=$pages;$p++): $q=http_build_query(array_merge($_GET,['page'=>$p])); ?>
 <a href="?<?=$q?>" class="btn <?=$p===$page?'btn-primary':'btn-ghost'?> btn-sm"><?=$p?></a>
 <?php endfor; ?>
</div>
<?php endif; ?>
<?php include 'footer.php'; ?>
