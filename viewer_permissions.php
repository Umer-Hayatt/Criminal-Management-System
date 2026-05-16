<?php
include 'db.php';
require_login();
if(!can('admin')){ set_flash('error','Permission denied.'); header('Location: index.php'); exit(); }

// Actions: grant, revoke
if(isset($_GET['action']) && isset($_GET['uid'])){
 $uid = intval($_GET['uid']);
 if($_GET['action']==='grant'){
  grant_view_permission($uid, $_SESSION['user_id']);
  log_activity('Granted','ViewerAccess',$uid,'Granted view permission');
  set_flash('success','Access granted.');
  header('Location: viewer_permissions.php'); exit();
 } elseif($_GET['action']==='revoke'){
  revoke_view_permission($uid, $_SESSION['user_id']);
  log_activity('Revoked','ViewerAccess',$uid,'Revoked view permission');
  set_flash('success','Access revoked.');
  header('Location: viewer_permissions.php'); exit();
 }
}

$requests = get_view_requests();
$pageTitle = 'Viewer Access Requests';
include 'header.php';
?>
<div class="page-hdr">
 <div class="page-hdr-left"><div class="page-hdr-icon"><i data-lucide="users"></i></div><div><h2>Viewer Access</h2><p>Manage viewer requests to view all cases</p></div></div>
 <a href="analytics.php" class="btn btn-ghost">Back</a>
</div>
<div class="card">
 <div class="tbl-wrap"><table>
  <thead><tr><th>User</th><th>Role</th><th>Requested At</th><th>Granted</th><th>Actions</th></tr></thead>
  <tbody>
  <?php if(empty($requests)): ?>
   <tr class="empty-row"><td colspan="5">No viewer access entries.</td></tr>
  <?php else: foreach($requests as $r): ?>
   <tr>
    <td><?=htmlspecialchars($r['username'] ?? 'User '.$r['user_id'])?></td>
    <td><?=htmlspecialchars($r['role'] ?? 'viewer')?></td>
    <td><?=htmlspecialchars($r['requested_at'] ?? '—')?></td>
    <td><?=($r['granted']?'<span class="badge b-green">Yes</span>':'<span class="badge b-muted">No</span>')?></td>
    <td>
     <?php if(!$r['granted']): ?><a href="viewer_permissions.php?action=grant&uid=<?=$r['user_id']?>" class="btn btn-primary btn-sm">Grant</a>
     <?php else: ?><a href="viewer_permissions.php?action=revoke&uid=<?=$r['user_id']?>" class="btn btn-danger btn-sm">Revoke</a><?php endif; ?>
    </td>
   </tr>
  <?php endforeach; endif; ?>
  </tbody>
 </table></div>
</div>
<?php include 'footer.php'; ?>
