<?php
include 'db.php';
$id=intval($_GET['id']);
if($id){
 log_activity('Deleted','Case',$id,"Deleted case #$id");
 mysqli_query($conn,"DELETE FROM Case_Record WHERE case_id=$id");
}
set_flash('success','Case deleted.');
header("Location: cases.php"); exit();
