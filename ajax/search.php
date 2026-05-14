<?php
// ajax/search.php — global search with filters, returns JSON
include '../db.php';
require_login();
header('Content-Type: application/json');

$q      = '%'.esc($conn,$_GET['q']??'').'%';
$type   = $_GET['type']??'all';
$status = esc($conn,$_GET['status']??'');
$from   = esc($conn,$_GET['from']??'');
$to     = esc($conn,$_GET['to']??'');
$cat    = esc($conn,$_GET['cat']??'');

$results=[];

if($type==='all'||$type==='criminal'){
 $w=["(first_name LIKE '$q' OR last_name LIKE '$q' OR phone LIKE '$q')"];
 if($status) $w[]="status='$status'";
 $res=mysqli_query($conn,"SELECT 'criminal' AS entity_type,criminal_id AS id,CONCAT(first_name,' ',last_name) AS name,status,photo,nationality AS extra FROM Criminal WHERE ".implode(' AND ',$w)." LIMIT 20");
 while($r=mysqli_fetch_assoc($res)) $results[]=$r;
}

if($type==='all'||$type==='officer'){
 $w=["(first_name LIKE '$q' OR last_name LIKE '$q' OR badge_number LIKE '$q' OR department LIKE '$q')"];
 if($status) $w[]="rank='$status'";
 $res=mysqli_query($conn,"SELECT 'officer' AS entity_type,officer_id AS id,CONCAT(first_name,' ',last_name) AS name,badge_number AS status,photo,department AS extra FROM Officer WHERE ".implode(' AND ',$w)." LIMIT 20");
 while($r=mysqli_fetch_assoc($res)) $results[]=$r;
}

if($type==='all'||$type==='case'){
 $w=["(cr.crime_type LIKE '$q' OR c.description LIKE '$q' OR c.title LIKE '$q')"];
 if($status) $w[]="c.case_status='$status'";
 if($cat)    $w[]="cr.crime_type='$cat'";
 if($from)   $w[]="c.open_date>='$from'";
 if($to)     $w[]="c.open_date<='$to'";
 $res=mysqli_query($conn,"SELECT 'case' AS entity_type,c.case_id AS id,CASE WHEN c.title IS NULL OR c.title='' THEN CONCAT('Case #',c.case_id) ELSE CONCAT('Case #',c.case_id,' — ',c.title) END AS name,c.case_status AS status,NULL AS photo,cr.crime_type AS extra FROM Case_Record c JOIN Crime cr ON c.crime_id=cr.crime_id WHERE ".implode(' AND ',$w)." LIMIT 20");
 while($r=mysqli_fetch_assoc($res)) $results[]=$r;
}

if($type==='all'||$type==='hearing'){
 $w=["(h.court_name LIKE '$q' OR h.judge_name LIKE '$q')"];
 if($status) $w[]="h.verdict='$status'";
 if($from)   $w[]="h.hearing_date>='$from'";
 if($to)     $w[]="h.hearing_date<='$to'";
 $res=mysqli_query($conn,"SELECT 'hearing' AS entity_type,h.hearing_id AS id,h.court_name AS name,h.verdict AS status,NULL AS photo,DATE_FORMAT(h.hearing_date,'%d %b %Y') AS extra FROM Court_Hearing h WHERE ".implode(' AND ',$w)." LIMIT 20");
 while($r=mysqli_fetch_assoc($res)) $results[]=$r;
}


echo json_encode(['ok'=>true,'results'=>$results]);
