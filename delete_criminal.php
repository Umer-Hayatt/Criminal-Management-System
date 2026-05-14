<?php
include 'db.php';
$id=intval($_GET['id']);
if($id){
 $r=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Criminal WHERE criminal_id=$id"));
 if($r){log_activity('Deleted','Criminal',$id,"Removed {$r['first_name']} {$r['last_name']}");}
 mysqli_query($conn,"DELETE FROM Criminal WHERE criminal_id=$id");
}
set_flash('success','Criminal record deleted.');
header("Location: records_criminals.php"); exit();
