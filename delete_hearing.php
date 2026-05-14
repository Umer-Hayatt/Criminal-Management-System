<?php
include 'db.php';
$id=intval($_GET['id']);
if($id){
 $r=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Court_Hearing WHERE hearing_id=$id"));
 if($r) log_activity('Deleted','Hearing',$id,"Deleted hearing at ".($r['court_name']??''));
 mysqli_query($conn,"DELETE FROM Court_Hearing WHERE hearing_id=$id");
}
set_flash('success','Hearing deleted.');
$back=$_GET['back']??'hearings.php';
header("Location: $back"); exit();
