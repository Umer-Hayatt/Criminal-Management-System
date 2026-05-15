<?php
include 'db.php';
require_login();
if($_SESSION['role'] === 'viewer') {
 header('Location: index.php');
 exit();
}
$pageTitle = 'Register Criminal';
$errors = [];

if($_SERVER['REQUEST_METHOD']==='POST'){
 $fn  = esc($conn,$_POST['first_name']);
 $ln  = esc($conn,$_POST['last_name']);
 $dob = esc($conn,$_POST['date_of_birth']);
 $gen = esc($conn,$_POST['gender']);
 $nat = esc($conn,$_POST['nationality']);
 $addr= esc($conn,$_POST['address']);
 $ph  = esc($conn,$_POST['phone']);
 $st  = esc($conn,$_POST['status']);
 if(!$fn) $errors[]='First name is required.';
 if(!$ln) $errors[]='Last name is required.';
 if(empty($errors)){
  mysqli_begin_transaction($conn);
  try{
   mysqli_query($conn,"INSERT INTO Criminal(first_name,last_name,date_of_birth,gender,nationality,address,phone,status) VALUES('$fn','$ln','$dob','$gen','$nat','$addr','$ph','$st')");
   $cid=mysqli_insert_id($conn);
   // Handle photo upload
   if(!empty($_FILES['photo']['name'])){
    $ext=strtolower(pathinfo($_FILES['photo']['name'],PATHINFO_EXTENSION));
    if(in_array($ext,['jpg','jpeg','png','webp'])&&$_FILES['photo']['size']<=5*1024*1024){
     $fname="uploads/criminals/{$cid}_".time().".$ext";
     if(move_uploaded_file($_FILES['photo']['tmp_name'],$fname))
      mysqli_query($conn,"UPDATE Criminal SET photo='$fname' WHERE criminal_id=$cid");
    }
   }
   // Crime
   $ctype=esc($conn,$_POST['crime_type']??'');
   if($ctype){
    $cdesc=esc($conn,$_POST['crime_description']??'');
    $cdate=esc($conn,$_POST['date_occurred']??'');
    $cloc=esc($conn,$_POST['crime_location']??'');
    $csev=esc($conn,$_POST['severity']??'Minor');
    mysqli_query($conn,"INSERT INTO Crime(crime_type,description,date_occurred,location,severity) VALUES('$ctype','$cdesc','$cdate','$cloc','$csev')");
    $crime_id=mysqli_insert_id($conn);
    $role=esc($conn,$_POST['role']??'Suspect');
    $adate=esc($conn,$_POST['arrest_date']??'');
    mysqli_query($conn,"INSERT INTO Criminal_Crime(criminal_id,crime_id,role,arrest_date) VALUES($cid,$crime_id,'$role','$adate')");
    $cstat=esc($conn,$_POST['case_status']??'Open');
    $codate=esc($conn,$_POST['open_date']??'');
    $cdescr=esc($conn,$_POST['case_description']??'');
    mysqli_query($conn,"INSERT INTO Case_Record(crime_id,case_status,open_date,description) VALUES($crime_id,'$cstat','$codate','$cdescr')");
   }
   mysqli_commit($conn);
   log_activity('Created','Criminal',$cid,"Registered $fn $ln");
   set_flash('success',"Criminal $fn $ln registered successfully.");
   header("Location: profile.php?id=$cid"); exit();
  }catch(Exception $e){mysqli_rollback($conn);$errors[]=$e->getMessage();}
 }
}
$officers=mysqli_query($conn,"SELECT * FROM Officer ORDER BY first_name");
$prisons=mysqli_query($conn,"SELECT * FROM Prison ORDER BY prison_name");
include 'header.php';
?>
<div class="breadcrumb"><a href="records_criminals.php">Criminals</a><i data-lucide="chevron-right"></i><span>Register Criminal</span></div>
<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="user-plus"></i></div>
  <div><h2>Register Criminal</h2><p>Add a new criminal record to the system</p></div>
 </div>
 <a href="records_criminals.php" class="btn btn-ghost"><i data-lucide="arrow-left"></i> Back</a>
</div>
<?php foreach($errors as $e): ?><div class="flash flash-error"><i data-lucide="alert-circle"></i><?=htmlspecialchars($e)?></div><?php endforeach; ?>

<form method="POST" enctype="multipart/form-data">

<!-- Photo Upload -->
<div class="card">
 <div class="card-title"><i data-lucide="camera"></i> Profile Photo</div>
 <div class="photo-upload-box">
  <div class="photo-preview"><img src="assets/anon.svg" id="previewImg" alt="Preview"></div>
  <div>
   <label class="upload-btn" for="photo"><i data-lucide="camera"></i> Upload Photo</label>
   <input type="file" id="photo" name="photo" accept="image/*" hidden>
   <p class="upload-hint">JPG, PNG or WEBP — Max 5MB</p>
  </div>
 </div>
</div>

<!-- Section 1: Personal Info -->
<div class="form-section">
 <div class="form-section-title"><i data-lucide="user"></i> Personal Information</div>
 <div class="form-grid">
  <div class="form-group"><label>First Name *</label><input type="text" name="first_name" placeholder="e.g. Ali" required value="<?=htmlspecialchars($_POST['first_name']??'')?>"></div>
  <div class="form-group"><label>Last Name *</label><input type="text" name="last_name" placeholder="e.g. Hassan" required value="<?=htmlspecialchars($_POST['last_name']??'')?>"></div>
  <div class="form-group"><label>Date of Birth</label><input type="date" name="date_of_birth" value="<?=htmlspecialchars($_POST['date_of_birth']??'')?>"></div>
  <div class="form-group"><label>Gender</label><select name="gender"><option>Male</option><option>Female</option></select></div>
  <div class="form-group"><label>Nationality</label><input type="text" name="nationality" placeholder="e.g. Pakistani" value="<?=htmlspecialchars($_POST['nationality']??'')?>"></div>
  <div class="form-group"><label>Phone</label><input type="text" name="phone" placeholder="e.g. 0300-1234567" value="<?=htmlspecialchars($_POST['phone']??'')?>"></div>
  <div class="form-group"><label>Status *</label><select name="status"><option>Wanted</option><option>Imprisoned</option><option>Released</option><option>Under Trial</option></select></div>
  <div class="form-group full"><label>Address</label><input type="text" name="address" placeholder="Full home address" value="<?=htmlspecialchars($_POST['address']??'')?>"></div>
 </div>
</div>

<!-- Section 2: Crime -->
<div class="form-section">
 <div class="form-section-title"><i data-lucide="alert-triangle"></i> Crime Details <small style="font-weight:400;color:var(--txt-soft);font-size:10px">(Optional)</small></div>
 <div class="form-grid cols3">
  <div class="form-group"><label>Crime Type</label><select name="crime_type"><option value="">-- None --</option><?php foreach(['Robbery','Murder','Fraud','Drug Dealing','Car Theft','Kidnapping','Assault','Terrorism','Cyber Crime','Other'] as $t): ?><option><?=$t?></option><?php endforeach; ?></select></div>
  <div class="form-group"><label>Severity</label><select name="severity"><option>Minor</option><option>Major</option><option>Felony</option></select></div>
  <div class="form-group"><label>Date Occurred</label><input type="date" name="date_occurred"></div>
  <div class="form-group"><label>Location</label><input type="text" name="crime_location" placeholder="City or area"></div>
  <div class="form-group"><label>Criminal's Role</label><select name="role"><option>Main Accused</option><option>Accomplice</option><option>Suspect</option></select></div>
  <div class="form-group"><label>Arrest Date</label><input type="date" name="arrest_date"></div>
  <div class="form-group full"><label>Crime Description</label><textarea name="crime_description" placeholder="Describe what happened..."></textarea></div>
 </div>
</div>

<!-- Section 3: Case -->
<div class="form-section">
 <div class="form-section-title"><i data-lucide="folder-open"></i> Case Details <small style="font-weight:400;color:var(--txt-soft);font-size:10px">(Optional)</small></div>
 <div class="form-grid">
  <div class="form-group"><label>Case Status</label><select name="case_status"><option>Open</option><option>Under Investigation</option><option>Closed</option></select></div>
  <div class="form-group"><label>Open Date</label><input type="date" name="open_date"></div>
  <div class="form-group full"><label>Case Notes</label><textarea name="case_description" placeholder="Additional case notes..."></textarea></div>
 </div>
</div>

<div style="display:flex;gap:10px;align-items:center;margin-top:8px;">
 <button type="submit" class="btn btn-primary" style="padding:11px 28px"><i data-lucide="save"></i> Register Criminal</button>
 <a href="records_criminals.php" class="btn btn-ghost" style="padding:11px 20px">Cancel</a>
</div>
</form>

<script>document.addEventListener('DOMContentLoaded',()=>initPhotoPreview('photo','previewImg'));</script>
<?php include 'footer.php'; ?>
