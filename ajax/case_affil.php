<?php
// ajax/case_affil.php — handles all case affiliation add/remove
include '../db.php';
require_login();
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$case_id = intval($_POST['case_id'] ?? $_GET['case_id'] ?? 0);
if (!$case_id) json_response(false, 'Invalid case');

switch ($action) {
 // ── OFFICERS ─────────────────────────────────
 case 'add_officer':
  if (!can('edit')) json_response(false, 'Permission denied');
  $oid = intval($_POST['officer_id']);
  $role = esc($conn, $_POST['role'] ?? 'Investigator');
  $date = date('Y-m-d');
  mysqli_query($conn, "INSERT IGNORE INTO case_officers(case_id,officer_id,role,assigned_date) VALUES($case_id,$oid,'$role','$date')");
  $o = db_row($conn, "SELECT o.*,co.role AS oc_role FROM Officer o JOIN case_officers co ON o.officer_id=co.officer_id WHERE co.case_id=$case_id AND co.officer_id=$oid");
  log_activity('Updated','Case',$case_id,"Officer #$oid assigned");
  json_response(true, 'Officer assigned', $o);

 case 'remove_officer':
  if (!can('edit')) json_response(false, 'Permission denied');
  $oid = intval($_POST['officer_id']);
  mysqli_query($conn, "DELETE FROM case_officers WHERE case_id=$case_id AND officer_id=$oid");
  log_activity('Updated','Case',$case_id,"Officer #$oid removed");
  json_response(true, 'Officer removed');

 // ── CRIMINALS ────────────────────────────────
 case 'add_criminal':
  if (!can('edit')) json_response(false, 'Permission denied');
  $crid = intval($_POST['criminal_id']);
  $role = esc($conn, $_POST['role'] ?? 'Suspect');
  mysqli_query($conn, "INSERT IGNORE INTO case_criminals(case_id,criminal_id,role) VALUES($case_id,$crid,'$role')");
  $c = db_row($conn, "SELECT c.*,cc.role AS cr_role FROM Criminal c JOIN case_criminals cc ON c.criminal_id=cc.criminal_id WHERE cc.case_id=$case_id AND cc.criminal_id=$crid");
  log_activity('Updated','Case',$case_id,"Criminal #$crid linked");
  json_response(true, 'Criminal linked', $c);

 case 'remove_criminal':
  if (!can('edit')) json_response(false, 'Permission denied');
  $crid = intval($_POST['criminal_id']);
  mysqli_query($conn, "DELETE FROM case_criminals WHERE case_id=$case_id AND criminal_id=$crid");
  log_activity('Updated','Case',$case_id,"Criminal #$crid unlinked");
  json_response(true, 'Criminal unlinked');

 // ── VICTIMS ──────────────────────────────────
 case 'add_victim':
  if (!can('edit')) json_response(false, 'Permission denied');
  $fn = esc($conn,$_POST['first_name']??'');
  $ln = esc($conn,$_POST['last_name']??'');
  $ph = esc($conn,$_POST['phone']??'');
  $ad = esc($conn,$_POST['address']??'');
  $st = esc($conn,$_POST['statement']??'');
  mysqli_query($conn,"INSERT INTO Victim(first_name,last_name,phone,address,statement,case_id) VALUES('$fn','$ln','$ph','$ad','$st',$case_id)");
  $vid = mysqli_insert_id($conn);
  log_activity('Created','Victim',$vid,"Added victim $fn $ln to case $case_id");
  json_response(true,'Victim added',['victim_id'=>$vid,'first_name'=>$fn,'last_name'=>$ln,'phone'=>$ph]);

 case 'remove_victim':
  if (!can('edit')) json_response(false, 'Permission denied');
  $vid = intval($_POST['victim_id']);
  mysqli_query($conn,"DELETE FROM Victim WHERE victim_id=$vid AND case_id=$case_id");
  json_response(true,'Victim removed');

 // ── SUSPECTS ─────────────────────────────────
 case 'add_suspect':
  if (!can('edit')) json_response(false, 'Permission denied');
  $fn = esc($conn,$_POST['first_name']??'');
  $ln = esc($conn,$_POST['last_name']??'');
  $ph = esc($conn,$_POST['phone']??'');
  $nt = esc($conn,$_POST['note']??'');
  mysqli_query($conn,"INSERT INTO Suspect(first_name,last_name,phone,note,case_id) VALUES('$fn','$ln','$ph','$nt',$case_id)");
  $sid = mysqli_insert_id($conn);
  json_response(true,'Suspect added',['suspect_id'=>$sid,'first_name'=>$fn,'last_name'=>$ln,'phone'=>$ph]);

 case 'remove_suspect':
  if (!can('edit')) json_response(false, 'Permission denied');
  $sid = intval($_POST['suspect_id']);
  mysqli_query($conn,"DELETE FROM Suspect WHERE suspect_id=$sid AND case_id=$case_id");
  json_response(true,'Suspect removed');

 // ── HEARINGS ─────────────────────────────────
 case 'add_hearing':
  if (!can('edit')) json_response(false, 'Permission denied');
  $court = esc($conn,$_POST['court_name']??'');
  $judge = esc($conn,$_POST['judge_name']??'');
  $hd    = esc($conn,$_POST['hearing_date']??'');
  $verd  = esc($conn,$_POST['verdict']??'Pending');
  $nd    = !empty($_POST['next_hearing_date'])?"'".esc($conn,$_POST['next_hearing_date'])."'":'NULL';
  mysqli_query($conn,"INSERT INTO Court_Hearing(case_id,court_name,judge_name,hearing_date,verdict,next_hearing_date) VALUES($case_id,'$court','$judge','$hd','$verd',$nd)");
  $hid = mysqli_insert_id($conn);
  log_activity('Created','Hearing',$hid,"Scheduled for case $case_id");
  json_response(true,'Hearing added',['hearing_id'=>$hid,'court_name'=>$court,'judge_name'=>$judge,'hearing_date'=>$hd,'verdict'=>$verd]);

 // ── WARRANTS ─────────────────────────────────
 case 'add_warrant':
  if (!can('edit')) json_response(false, 'Permission denied');
  $type  = esc($conn,$_POST['type']??'Arrest');
  $crid  = intval($_POST['criminal_id']??0);
  $idate = esc($conn,$_POST['issued_date']??'');
  $edate = esc($conn,$_POST['expiry_date']??'');
  $notes = esc($conn,$_POST['notes']??'');
  mysqli_query($conn,"INSERT INTO Warrant(case_id,criminal_id,type,issued_date,expiry_date,notes) VALUES($case_id,$crid,'$type','$idate','$edate','$notes')");
  $wid=mysqli_insert_id($conn);
  log_activity('Created','Warrant',$wid,"Issued for case $case_id");
  json_response(true,'Warrant issued',['warrant_id'=>$wid,'type'=>$type,'issued_date'=>$idate,'expiry_date'=>$edate,'status'=>'Active']);

 // ── UPDATE CASE STATUS ───────────────────────
 case 'update_case':
  if (!can('edit')) json_response(false,'Permission denied');
  $st  = esc($conn,$_POST['case_status']??'');
  $cd  = !empty($_POST['close_date'])?"'".esc($conn,$_POST['close_date'])."'":'NULL';
  $ds  = esc($conn,$_POST['description']??'');
  $lo  = intval($_POST['lead_officer_id']??0) ?: 'NULL';
  $ttl = esc($conn,$_POST['title']??'');
  mysqli_query($conn,"UPDATE Case_Record SET case_status='$st',close_date=$cd,description='$ds',lead_officer_id=$lo,title='$ttl' WHERE case_id=$case_id");
  log_activity('Updated','Case',$case_id,"Status → $st");
  json_response(true,'Case updated');

 // ── SEARCH HELPERS ───────────────────────────
 case 'search_officers':
  $q = '%'.esc($conn,$_GET['q']??'').'%';
  $res = mysqli_query($conn,"SELECT officer_id,first_name,last_name,badge_number,rank,photo FROM Officer WHERE (first_name LIKE '$q' OR last_name LIKE '$q' OR badge_number LIKE '$q') AND officer_id NOT IN(SELECT officer_id FROM case_officers WHERE case_id=$case_id) LIMIT 10");
  $rows=[]; while($r=mysqli_fetch_assoc($res)) $rows[]=$r;
  json_response(true,'',$rows);

 case 'search_criminals':
  $q = '%'.esc($conn,$_GET['q']??'').'%';
  $res = mysqli_query($conn,"SELECT criminal_id,first_name,last_name,status,photo FROM Criminal WHERE (first_name LIKE '$q' OR last_name LIKE '$q') AND criminal_id NOT IN(SELECT criminal_id FROM case_criminals WHERE case_id=$case_id) LIMIT 10");
  $rows=[]; while($r=mysqli_fetch_assoc($res)) $rows[]=$r;
  json_response(true,'',$rows);

 default:
  json_response(false,'Unknown action');
}
