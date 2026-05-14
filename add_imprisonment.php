<?php
include 'db.php';
$criminal_id=intval($_POST['criminal_id']);
if(!$criminal_id||$_SERVER['REQUEST_METHOD']!=='POST'){header("Location: records_criminals.php");exit();}
$prison_id=intval($_POST['prison_id']);
$cell=esc($conn,$_POST['cell_number']??'');
$years=intval($_POST['sentence_years']??0);
$start=esc($conn,$_POST['start_date']??'');
$end=!empty($_POST['end_date'])?"'".esc($conn,$_POST['end_date'])."'":'NULL';
$rd=!empty($_POST['release_date'])?"'".esc($conn,$_POST['release_date'])."'":'NULL';
mysqli_query($conn,"INSERT INTO Imprisonment(criminal_id,prison_id,cell_number,sentence_years,start_date,end_date,release_date) VALUES($criminal_id,$prison_id,'$cell',$years,'$start',$end,$rd)");
if($years>0) mysqli_query($conn,"UPDATE Criminal SET status='Imprisoned' WHERE criminal_id=$criminal_id");
log_activity('Created','Imprisonment',mysqli_insert_id($conn),"Criminal #$criminal_id → Prison #$prison_id");
set_flash('success','Imprisonment record added.');
header("Location: profile.php?id=$criminal_id#tab-imprisonments"); exit();
