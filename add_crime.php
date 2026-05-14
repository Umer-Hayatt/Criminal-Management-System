<?php
include 'db.php';
$criminal_id = intval($_GET['id']);
if(!$criminal_id){header("Location: records_criminals.php");exit();}

$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 $ctype=esc($conn,$_POST['crime_type']);
 $desc=esc($conn,$_POST['description']);
 $date=esc($conn,$_POST['date_occurred']);
 $loc=esc($conn,$_POST['location']);
 $sev=esc($conn,$_POST['severity']??'Minor');
 $role=esc($conn,$_POST['role']??'Suspect');
 $adate=esc($conn,$_POST['arrest_date']??'');

 if(!$ctype) $errors[]='Crime type is required.';
 if(empty($errors)){
  mysqli_query($conn,"INSERT INTO Crime(crime_type,description,date_occurred,location,severity) VALUES('$ctype','$desc','$date','$loc','$sev')");
  $crime_id=mysqli_insert_id($conn);
  mysqli_query($conn,"INSERT INTO Criminal_Crime(criminal_id,crime_id,role,arrest_date) VALUES($criminal_id,$crime_id,'$role','$adate')");
  // Auto-create case
  $open=date('Y-m-d');
  mysqli_query($conn,"INSERT INTO Case_Record(crime_id,case_status,open_date) VALUES($crime_id,'Open','$open')");
  log_activity('Created','Crime',$crime_id,"Added $ctype to criminal #$criminal_id");
  set_flash('success','Crime added successfully.');
  header("Location: profile.php?id=$criminal_id#tab-crimes"); exit();
 }
}
$criminal=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Criminal WHERE criminal_id=$criminal_id"));
if(!$criminal){header("Location: records_criminals.php");exit();}
$pageTitle='Add Crime';
include 'header.php';
?>
<div class="breadcrumb">
 <a href="records_criminals.php">Criminals</a>
 <i data-lucide="chevron-right"></i>
 <a href="profile.php?id=<?=$criminal_id?>"><?=htmlspecialchars($criminal['first_name'].' '.$criminal['last_name'])?></a>
 <i data-lucide="chevron-right"></i>
 <span>Add Crime</span>
</div>
<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="alert-triangle"></i></div>
  <div><h2>Add Crime</h2><p><?=htmlspecialchars($criminal['first_name'].' '.$criminal['last_name'])?></p></div>
 </div>
 <a href="profile.php?id=<?=$criminal_id?>" class="btn btn-ghost"><i data-lucide="arrow-left"></i> Back</a>
</div>
<?php foreach($errors as $e): ?><div class="flash flash-error"><i data-lucide="alert-circle"></i><?=htmlspecialchars($e)?></div><?php endforeach; ?>
<div class="form-section">
 <div class="form-section-title"><i data-lucide="alert-triangle"></i> Crime Details</div>
 <form method="POST">
  <div class="form-grid cols3">
   <div class="form-group"><label>Crime Type *</label>
    <select name="crime_type" required>
     <option value="">-- Select --</option>
     <?php foreach(['Robbery','Murder','Fraud','Drug Dealing','Car Theft','Kidnapping','Assault','Terrorism','Cyber Crime','Other'] as $t): ?>
     <option><?=$t?></option>
     <?php endforeach; ?>
    </select>
   </div>
   <div class="form-group"><label>Severity</label>
    <select name="severity"><option>Minor</option><option>Major</option><option>Felony</option></select>
   </div>
   <div class="form-group"><label>Criminal's Role</label>
    <select name="role"><option>Main Accused</option><option>Accomplice</option><option>Suspect</option></select>
   </div>
   <div class="form-group"><label>Date Occurred</label><input type="date" name="date_occurred"></div>
   <div class="form-group"><label>Arrest Date</label><input type="date" name="arrest_date"></div>
   <div class="form-group"><label>Location</label><input type="text" name="location" placeholder="City or area"></div>
   <div class="form-group full"><label>Description</label><textarea name="description" placeholder="Describe what happened..."></textarea></div>
  </div>
  <div style="display:flex;gap:10px;margin-top:8px">
   <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Add Crime</button>
   <a href="profile.php?id=<?=$criminal_id?>" class="btn btn-ghost">Cancel</a>
  </div>
 </form>
</div>
<?php include 'footer.php'; ?>
