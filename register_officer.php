<?php
include 'db.php';
require_login();
if(!can('admin')) {
 header('Location: index.php');
 exit();
}
$pageTitle = 'Register Officer';
$errors = [];

if($_SERVER['REQUEST_METHOD']==='POST'){
 $fn  = esc($conn,$_POST['first_name']);
 $ln  = esc($conn,$_POST['last_name']);
 $badge= esc($conn,$_POST['badge_number']);
 $rank = esc($conn,$_POST['rank']);
 $dept = esc($conn,$_POST['department']);
 $ph   = esc($conn,$_POST['phone']);
 if(!$fn) $errors[]='First name is required.';
 if(empty($errors)){
  mysqli_query($conn,"INSERT INTO Officer(first_name,last_name,badge_number,rank,department,phone) VALUES('$fn','$ln','$badge','$rank','$dept','$ph')");
  $oid=mysqli_insert_id($conn);
  if(!empty($_FILES['photo']['name'])){
   $ext=strtolower(pathinfo($_FILES['photo']['name'],PATHINFO_EXTENSION));
   if(in_array($ext,['jpg','jpeg','png','webp'])&&$_FILES['photo']['size']<=5*1024*1024){
    $fname="uploads/officers/{$oid}_".time().".$ext";
    if(move_uploaded_file($_FILES['photo']['tmp_name'],$fname))
     mysqli_query($conn,"UPDATE Officer SET photo='$fname' WHERE officer_id=$oid");
   }
  }
  log_activity('Created','Officer',$oid,"Registered $fn $ln, badge $badge");
  set_flash('success',"Officer $fn $ln registered successfully.");
  header("Location: officer_profile.php?id=$oid"); exit();
 }
}
include 'header.php';
?>
<div class="breadcrumb"><a href="records_officers.php">Officers</a><i data-lucide="chevron-right"></i><span>Register Officer</span></div>
<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="badge"></i></div>
  <div><h2>Register Officer</h2><p>Add a new investigating officer</p></div>
 </div>
 <a href="records_officers.php" class="btn btn-ghost"><i data-lucide="arrow-left"></i> Back</a>
</div>
<?php foreach($errors as $e): ?><div class="flash flash-error"><i data-lucide="alert-circle"></i><?=htmlspecialchars($e)?></div><?php endforeach; ?>
<form method="POST" enctype="multipart/form-data">
<div class="card">
 <div class="card-title"><i data-lucide="camera"></i> Profile Photo</div>
 <div class="photo-upload-box">
  <div class="photo-preview"><img src="assets/anon.png" id="previewImg" alt="Preview"></div>
  <div>
   <label class="upload-btn" for="photo"><i data-lucide="camera"></i> Upload Photo</label>
   <input type="file" id="photo" name="photo" accept="image/*" hidden>
   <p class="upload-hint">JPG, PNG or WEBP — Max 5MB</p>
  </div>
 </div>
</div>
<div class="form-section">
 <div class="form-section-title"><i data-lucide="shield"></i> Officer Details</div>
 <div class="form-grid cols3">
  <div class="form-group"><label>First Name *</label><input type="text" name="first_name" required value="<?=htmlspecialchars($_POST['first_name']??'')?>"></div>
  <div class="form-group"><label>Last Name *</label><input type="text" name="last_name" required value="<?=htmlspecialchars($_POST['last_name']??'')?>"></div>
  <div class="form-group"><label>Badge Number</label><input type="text" name="badge_number" placeholder="e.g. B-1006" value="<?=htmlspecialchars($_POST['badge_number']??'')?>"></div>
  <div class="form-group"><label>Rank</label><select name="rank"><option>Inspector</option><option>Sub-Inspector</option><option>DSP</option><option>SSP</option><option>ASI</option><option>SP</option></select></div>
  <div class="form-group"><label>Department</label><input type="text" name="department" placeholder="e.g. CID Rawalpindi" value="<?=htmlspecialchars($_POST['department']??'')?>"></div>
  <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?=htmlspecialchars($_POST['phone']??'')?>"></div>
 </div>
</div>
<div style="display:flex;gap:10px;">
 <button type="submit" class="btn btn-primary" style="padding:11px 28px"><i data-lucide="save"></i> Register Officer</button>
 <a href="records_officers.php" class="btn btn-ghost" style="padding:11px 20px">Cancel</a>
</div>
</form>
<script>document.addEventListener('DOMContentLoaded',()=>initPhotoPreview('photo','previewImg'));</script>
<?php include 'footer.php'; ?>
