<?php
include 'db.php';
$criminal_id = intval($_GET['id']);
$criminal = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Criminal WHERE criminal_id=$criminal_id"));
if (!$criminal) { header("Location: records.php"); exit(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $esc = fn($v) => mysqli_real_escape_string($conn, trim($v));
 mysqli_begin_transaction($conn);
 try {
 $ctype = $esc($_POST['crime_type']); $cdesc = $esc($_POST['crime_description']);
 $cdate = $esc($_POST['date_occurred']); $cloc = $esc($_POST['crime_location']); $csev = $esc($_POST['severity']);
 mysqli_query($conn,"INSERT INTO Crime(crime_type,description,date_occurred,location,severity) VALUES('$ctype','$cdesc','$cdate','$cloc','$csev')");
 $crime_id = mysqli_insert_id($conn);
 $role = $esc($_POST['role']); $adate = $esc($_POST['arrest_date']);
 mysqli_query($conn,"INSERT INTO Criminal_Crime(criminal_id,crime_id,role,arrest_date) VALUES($criminal_id,$crime_id,'$role','$adate')");
 // Also create a case
 $cstat = $esc($_POST['case_status']); $codate = $esc($_POST['open_date']); $cdescr = $esc($_POST['case_description']);
 mysqli_query($conn,"INSERT INTO Case_Record(crime_id,case_status,open_date,description) VALUES($crime_id,'$cstat','$codate','$cdescr')");
 mysqli_commit($conn);
 header("Location: profile.php?id=$criminal_id");
 exit();
 } catch(Exception $e) { mysqli_rollback($conn); $error=$e->getMessage(); }
}
$pageTitle = 'Add Crime';
include 'header.php';
?>
<div class="page-hdr">
 <div><h2> Add Another Crime</h2><p>For: <strong><?=$criminal['first_name'].' '.$criminal['last_name']?></strong></p></div>
 <a href="profile.php?id=<?=$criminal_id?>" class="btn btn-gray">← Back to Profile</a>
</div>

<?php if($error): ?><div class="alert alert-error"> <?=$error?></div><?php endif; ?>
<form method="POST">
<div class="form-section">
 <div class="form-section-title"> New Crime Details</div>
 <div class="form-grid cols3">
 <div class="form-group"><label>Crime Type *</label>
 <select name="crime_type" required>
 <option value="">-- Select --</option>
 <?php foreach(['Robbery','Murder','Fraud','Drug Dealing','Car Theft','Kidnapping','Assault','Terrorism','Cyber Crime','Other'] as $t): ?>
 <option><?=$t?></option><?php endforeach; ?>
 </select>
 </div>
 <div class="form-group"><label>Severity</label>
 <select name="severity"><option>Minor</option><option>Major</option><option>Felony</option></select>
 </div>
 <div class="form-group"><label>Date Occurred</label><input type="date" name="date_occurred"></div>
 <div class="form-group"><label>Location</label><input type="text" name="crime_location" placeholder="City or area"></div>
 <div class="form-group"><label>Role</label>
 <select name="role"><option>Main Accused</option><option>Accomplice</option><option>Suspect</option></select>
 </div>
 <div class="form-group"><label>Arrest Date</label><input type="date" name="arrest_date"></div>
 <div class="form-group full"><label>Description</label><textarea name="crime_description"></textarea></div>
 </div>
</div>
<div class="form-section">
 <div class="form-section-title"> Case for This Crime</div>
 <div class="form-grid">
 <div class="form-group"><label>Case Status</label>
 <select name="case_status"><option>Open</option><option>Under Investigation</option><option>Closed</option></select>
 </div>
 <div class="form-group"><label>Open Date</label><input type="date" name="open_date"></div>
 <div class="form-group full"><label>Notes</label><textarea name="case_description" placeholder="Case notes..."></textarea></div>
 </div>
</div>
<button type="submit" class="btn btn-primary" style="padding:12px 30px;"> Save Crime Record</button>
</form>
<?php include 'footer.php'; ?>
