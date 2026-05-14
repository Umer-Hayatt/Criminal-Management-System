<?php
include 'db.php';
$id=intval($_GET['id']);
if($id){
 $r=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Officer WHERE officer_id=$id"));
 if($r){log_activity('Deleted','Officer',$id,"Removed {$r['first_name']} {$r['last_name']} badge {$r['badge_number']}");}
 mysqli_query($conn,"DELETE FROM Officer WHERE officer_id=$id");
}
set_flash('success','Officer removed.');
header("Location: records_officers.php"); exit();
