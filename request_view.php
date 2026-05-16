<?php
include 'db.php';
require_login();
$uid = $_SESSION['user_id'] ?? 0;
if(!$uid){ header('Location: login.php'); exit(); }
// Only viewers need to request
if(($_SESSION['role'] ?? '') !== 'viewer'){
 set_flash('error','Only viewer accounts need to request access.'); header('Location: index.php'); exit();
}
request_view_permission($uid);
log_activity('Requested','ViewerAccess',$uid,'Requested access to view all cases');
set_flash('success','Access request submitted. An admin will review your request.');
header('Location: index.php'); exit();
?>
