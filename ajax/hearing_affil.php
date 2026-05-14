<?php
// ajax/hearing_affil.php — add/remove criminals & officers from hearings
include '../db.php';
require_login();
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$hid = intval($_POST['hearing_id'] ?? $_GET['hearing_id'] ?? 0);
if(!$hid) json_response(false,'Invalid hearing');

switch($action){
 case 'add_criminal':
  if(!can('edit')) json_response(false,'Permission denied');
  $crid=intval($_POST['criminal_id']);
  mysqli_query($conn,"INSERT IGNORE INTO hearing_criminals(hearing_id,criminal_id) VALUES($hid,$crid)");
  $c=db_row($conn,"SELECT criminal_id,first_name,last_name,status,photo FROM Criminal WHERE criminal_id=$crid");
  json_response(true,'Criminal linked',$c??[]);

 case 'remove_criminal':
  if(!can('edit')) json_response(false,'Permission denied');
  $crid=intval($_POST['criminal_id']);
  mysqli_query($conn,"DELETE FROM hearing_criminals WHERE hearing_id=$hid AND criminal_id=$crid");
  json_response(true,'Criminal unlinked');

 case 'add_officer':
  if(!can('edit')) json_response(false,'Permission denied');
  $oid=intval($_POST['officer_id']);
  mysqli_query($conn,"INSERT IGNORE INTO hearing_officers(hearing_id,officer_id) VALUES($hid,$oid)");
  $o=db_row($conn,"SELECT officer_id,first_name,last_name,badge_number,rank,photo FROM Officer WHERE officer_id=$oid");
  json_response(true,'Officer linked',$o??[]);

 case 'remove_officer':
  if(!can('edit')) json_response(false,'Permission denied');
  $oid=intval($_POST['officer_id']);
  mysqli_query($conn,"DELETE FROM hearing_officers WHERE hearing_id=$hid AND officer_id=$oid");
  json_response(true,'Officer unlinked');

 case 'search_criminals':
  $q='%'.esc($conn,$_GET['q']??'').'%';
  $res=mysqli_query($conn,"SELECT criminal_id,first_name,last_name,status,photo FROM Criminal WHERE (first_name LIKE '$q' OR last_name LIKE '$q') AND criminal_id NOT IN(SELECT criminal_id FROM hearing_criminals WHERE hearing_id=$hid) LIMIT 10");
  $rows=[]; while($r=mysqli_fetch_assoc($res)) $rows[]=$r;
  json_response(true,'',$rows);

 case 'search_officers':
  $q='%'.esc($conn,$_GET['q']??'').'%';
  $res=mysqli_query($conn,"SELECT officer_id,first_name,last_name,badge_number,rank,photo FROM Officer WHERE (first_name LIKE '$q' OR last_name LIKE '$q' OR badge_number LIKE '$q') AND officer_id NOT IN(SELECT officer_id FROM hearing_officers WHERE hearing_id=$hid) LIMIT 10");
  $rows=[]; while($r=mysqli_fetch_assoc($res)) $rows[]=$r;
  json_response(true,'',$rows);

 default: json_response(false,'Unknown action');
}
