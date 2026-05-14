<?php
include 'db.php';
$id=intval($_GET['id']);
if($id){ mysqli_query($conn,"DELETE FROM Prison WHERE prison_id=$id"); }
set_flash('success','Prison deleted.');
header("Location: records_prisons.php"); exit();
