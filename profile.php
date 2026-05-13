<?php
include 'db.php';
$id = intval($_GET['id']);
if (!$id) { header("Location: records.php"); exit(); }

// All data for this criminal 
$criminal = mysqli_fetch_assoc(mysqli_query($conn,
 "SELECT * FROM Criminal WHERE criminal_id = $id"));
if (!$criminal) { die("Criminal not found."); }

$crimes = mysqli_query($conn,
 "SELECT cr.*, cc.role, cc.arrest_date
 FROM Crime cr
 JOIN Criminal_Crime cc ON cr.crime_id = cc.crime_id
 WHERE cc.criminal_id = $id
 ORDER BY cr.date_occurred DESC");

$cases = mysqli_query($conn,
 "SELECT c.*, cr.crime_type, cr.severity
 FROM Case_Record c
 JOIN Crime cr ON c.crime_id = cr.crime_id
 JOIN Criminal_Crime cc ON cr.crime_id = cc.crime_id
 WHERE cc.criminal_id = $id
 ORDER BY c.open_date DESC");

$victims = mysqli_query($conn,
 "SELECT v.*, cr.crime_type
 FROM Victim v
 JOIN Crime cr ON v.crime_id = cr.crime_id
 JOIN Criminal_Crime cc ON cr.crime_id = cc.crime_id
 WHERE cc.criminal_id = $id");

$officers = mysqli_query($conn,
 "SELECT DISTINCT o.*, oc.role AS assigned_role, oc.assigned_date, c.description AS case_desc
 FROM Officer o
 JOIN Officer_Case oc ON o.officer_id = oc.officer_id
 JOIN Case_Record c ON oc.case_id = c.case_id
 JOIN Crime cr ON c.crime_id = cr.crime_id
 JOIN Criminal_Crime cc ON cr.crime_id = cc.crime_id
 WHERE cc.criminal_id = $id");

$hearings = mysqli_query($conn,
 "SELECT h.*, c.description AS case_desc
 FROM Court_Hearing h
 JOIN Case_Record c ON h.case_id = c.case_id
 JOIN Crime cr ON c.crime_id = cr.crime_id
 JOIN Criminal_Crime cc ON cr.crime_id = cc.crime_id
 WHERE cc.criminal_id = $id
 ORDER BY h.hearing_date DESC");

$imprisonments = mysqli_query($conn,
 "SELECT i.*, p.prison_name, p.location AS prison_city
 FROM Imprisonment i
 JOIN Prison p ON i.prison_id = p.prison_id
 WHERE i.criminal_id = $id
 ORDER BY i.start_date DESC");

$pageTitle = $criminal['first_name'].' '.$criminal['last_name'].' — Profile';
include 'header.php';

$stBadge = match($criminal['status']) {
 'Imprisoned' => 'b-red',
 'Released' => 'b-green',
 'Wanted' => 'b-orange',
 'Under Trial' => 'b-purple',
 default => 'b-blue'
};
?>

<?php if(isset($_GET['new'])): ?>
<div class="alert alert-success"> Criminal record registered successfully! Here is the complete profile.</div>
<?php endif; ?>

<!-- PAGE ACTIONS -->
<div class="page-hdr">
 <div>
 <h2> Criminal Profile</h2>
 <p>Complete record for <strong><?=$criminal['first_name'].' '.$criminal['last_name']?></strong></p>
 </div>
 <div style="display:flex;gap:8px;">
 <a href="edit_criminal.php?id=<?=$id?>" class="btn btn-blue"> Edit Personal Info</a>
 <a href="add_crime.php?id=<?=$id?>" class="btn btn-gray"> Add Another Crime</a>
 <a href="records.php" class="btn btn-gray">← Back</a>
 <a href="delete_criminal.php?id=<?=$id?>"
 class="btn btn-danger"
 onclick="return confirm('Permanently delete this criminal and ALL related records?')"> Delete</a>
 </div>
</div>

<!-- PERSONAL INFORMATION CARD -->
<div class="profile-header">
 <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
 <div>
 <div style="font-size:11px;opacity:0.6;letter-spacing:1px;text-transform:uppercase;">Criminal ID</div>
 <h2 style="margin-top:2px;">#<?=str_pad($id,4,'0',STR_PAD_LEFT)?> — <?=$criminal['first_name'].' '.$criminal['last_name']?></h2>
 <div style="margin-top:8px;">
 <span class="badge <?=$stBadge?>" style="font-size:13px;padding:5px 14px;"><?=$criminal['status']?></span>
 </div>
 </div>
 <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px 28px;opacity:0.9;">
 <div><div style="font-size:10px;opacity:0.6;text-transform:uppercase;">Date of Birth</div><div style="margin-top:2px;"><?=$criminal['date_of_birth']??'—'?></div></div>
 <div><div style="font-size:10px;opacity:0.6;text-transform:uppercase;">Gender</div><div style="margin-top:2px;"><?=$criminal['gender']??'—'?></div></div>
 <div><div style="font-size:10px;opacity:0.6;text-transform:uppercase;">Nationality</div><div style="margin-top:2px;"><?=$criminal['nationality']??'—'?></div></div>
 <div><div style="font-size:10px;opacity:0.6;text-transform:uppercase;">Phone</div><div style="margin-top:2px;"><?=$criminal['phone']??'—'?></div></div>
 <div style="grid-column:1/-1;"><div style="font-size:10px;opacity:0.6;text-transform:uppercase;">Address</div><div style="margin-top:2px;"><?=$criminal['address']??'—'?></div></div>
 </div>
 </div>
</div>

<!-- CRIMES COMMITTED -->
<?php $crimeRows = []; while($r=mysqli_fetch_assoc($crimes)) $crimeRows[]=$r; ?>
<div class="section-card">
 <div class="section-hdr">
 <h3>Crimes Committed (<?=count($crimeRows)?>)</h3>
 <a href="add_crime.php?id=<?=$id?>" class="btn btn-gray btn-sm"> Add Crime</a>
 </div>
 <?php if(empty($crimeRows)): ?>
 <div class="no-data">No crimes linked yet.</div>
 <?php else: ?>
 <div class="tbl-wrap"><table>
 <thead><tr><th>#</th><th>Crime Type</th><th>Date</th><th>Location</th><th>Severity</th><th>This Criminal's Role</th><th>Arrest Date</th></tr></thead>
 <tbody>
 <?php foreach($crimeRows as $r):
 $sev = match($r['severity']){'Felony'=>'b-red','Major'=>'b-orange','Minor'=>'b-blue',default=>'b-blue'};
 $rol = match($r['role']){'Main Accused'=>'b-red','Accomplice'=>'b-orange',default=>'b-blue'};
 ?>
 <tr>
 <td style="color:#999;vertical-align:middle;">#<?=$r['crime_id']?></td>
 <td><strong><?=$r['crime_type']?></strong><br><span style="color:#999;font-size:12px;"><?=substr($r['description'],0,50)?>...</span></td>
 <td><?=$r['date_occurred']?></td>
 <td><?=$r['location']?></td>
 <td><span class="badge <?=$sev?>"><?=$r['severity']?></span></td>
 <td><span class="badge <?=$rol?>"><?=$r['role']?></span></td>
 <td><?=$r['arrest_date']?></td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table></div>
 <?php endif; ?>
</div>

<!-- CASES -->
<?php $caseRows = []; while($r=mysqli_fetch_assoc($cases)) $caseRows[]=$r; ?>
<div class="section-card">
 <div class="section-hdr"><h3>Registered Cases (<?=count($caseRows)?>)</h3></div>
 <?php if(empty($caseRows)): ?>
 <div class="no-data">No cases found.</div>
 <?php else: ?>
 <div class="tbl-wrap"><table>
 <thead><tr><th>Case #</th><th>Crime</th><th>Status</th><th>Opened</th><th>Closed</th><th>Notes</th></tr></thead>
 <tbody>
 <?php foreach($caseRows as $r):
 $cs = match($r['case_status']){'Open'=>'b-orange','Closed'=>'b-green','Under Investigation'=>'b-blue',default=>'b-blue'};
 ?>
 <tr>
 <td style="color:#999;vertical-align:middle;">#<?=$r['case_id']?></td>
 <td><?=$r['crime_type']?></td>
 <td><span class="badge <?=$cs?>"><?=$r['case_status']?></span></td>
 <td><?=$r['open_date']?></td>
 <td><?=$r['close_date']??'Still Open'?></td>
 <td style="color:#666;font-size:12px;"><?=substr($r['description'],0,60)?></td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table></div>
 <?php endif; ?>
</div>

<!-- VICTIMS -->
<?php $victimRows = []; while($r=mysqli_fetch_assoc($victims)) $victimRows[]=$r; ?>
<div class="section-card">
 <div class="section-hdr"><h3>Victims Affected (<?=count($victimRows)?>)</h3></div>
 <?php if(empty($victimRows)): ?>
 <div class="no-data">No victim records found.</div>
 <?php else: ?>
 <div class="tbl-wrap"><table>
 <thead><tr><th>Name</th><th>Age</th><th>Gender</th><th>Crime</th><th>Contact</th><th>Statement</th></tr></thead>
 <tbody>
 <?php foreach($victimRows as $r): ?>
 <tr>
 <td><strong><?=$r['first_name'].' '.$r['last_name']?></strong></td>
 <td><?=$r['age']?></td>
 <td><?=$r['gender']?></td>
 <td><?=$r['crime_type']?></td>
 <td><?=$r['contact_number']?></td>
 <td style="font-style:italic;color:#555;font-size:12px;">"<?=substr($r['statement'],0,80)?>"</td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table></div>
 <?php endif; ?>
</div>

<!-- OFFICERS -->
<?php $officerRows = []; while($r=mysqli_fetch_assoc($officers)) $officerRows[]=$r; ?>
<div class="section-card">
 <div class="section-hdr"><h3>Investigating Officers (<?=count($officerRows)?>)</h3></div>
 <?php if(empty($officerRows)): ?>
 <div class="no-data">No officers assigned yet.</div>
 <?php else: ?>
 <div class="tbl-wrap"><table>
 <thead><tr><th>Officer Name</th><th>Badge</th><th>Rank</th><th>Department</th><th>Role on Case</th><th>Assigned Date</th></tr></thead>
 <tbody>
 <?php foreach($officerRows as $r): ?>
 <tr>
 <td><strong><?=$r['first_name'].' '.$r['last_name']?></strong></td>
 <td><span class="badge b-blue"><?=$r['badge_number']?></span></td>
 <td><?=$r['rank']?></td>
 <td><?=$r['department']?></td>
 <td><span class="badge b-purple"><?=$r['assigned_role']?></span></td>
 <td><?=$r['assigned_date']?></td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table></div>
 <?php endif; ?>
</div>

<!-- COURT HEARINGS -->
<?php $hearingRows = []; while($r=mysqli_fetch_assoc($hearings)) $hearingRows[]=$r; ?>
<div class="section-card">
 <div class="section-hdr"><h3>Court Hearings (<?=count($hearingRows)?>)</h3></div>
 <?php if(empty($hearingRows)): ?>
 <div class="no-data">No court hearings scheduled.</div>
 <?php else: ?>
 <div class="tbl-wrap"><table>
 <thead><tr><th>Court</th><th>Judge</th><th>Hearing Date</th><th>Verdict</th><th>Next Date</th></tr></thead>
 <tbody>
 <?php foreach($hearingRows as $r):
 $vb = match($r['verdict']){'Guilty'=>'b-red','Not Guilty'=>'b-green','Pending'=>'b-orange',default=>'b-blue'};
 ?>
 <tr>
 <td><strong><?=$r['court_name']?></strong></td>
 <td><?=$r['judge_name']?></td>
 <td><?=$r['hearing_date']?></td>
 <td><span class="badge <?=$vb?>"><?=$r['verdict']?></span></td>
 <td><?=$r['next_hearing_date']??'—'?></td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table></div>
 <?php endif; ?>
</div>

<!-- IMPRISONMENT HISTORY -->
<?php $impRows = []; while($r=mysqli_fetch_assoc($imprisonments)) $impRows[]=$r; ?>
<div class="section-card">
 <div class="section-hdr"><h3>Imprisonment History (<?=count($impRows)?>)</h3></div>
 <?php if(empty($impRows)): ?>
 <div class="no-data">No imprisonment records found.</div>
 <?php else: ?>
 <div class="tbl-wrap"><table>
 <thead><tr><th>Prison</th><th>City</th><th>Cell</th><th>Sentence</th><th>From</th><th>To</th><th>Status</th></tr></thead>
 <tbody>
 <?php foreach($impRows as $r):
 $active = empty($r['end_date']) || $r['end_date'] >= date('Y-m-d');
 ?>
 <tr>
 <td><strong><?=$r['prison_name']?></strong></td>
 <td><?=$r['prison_city']?></td>
 <td><?=$r['cell_number']?></td>
 <td><span class="badge b-red"><?=$r['sentence_years']?> years</span></td>
 <td><?=$r['start_date']?></td>
 <td><?=$r['end_date']??'—'?></td>
 <td><?=$active ? '<span class="badge b-red">Currently Serving</span>' : '<span class="badge b-green">Completed</span>'?></td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table></div>
 <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
