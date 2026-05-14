<?php
include 'db.php';
$id=intval($_GET['id']); $err='';
if(!$id){header("Location: records_criminals.php");exit();}
if($_SERVER['REQUEST_METHOD']==='POST'){
 $fn=esc($conn,$_POST['first_name']); $ln=esc($conn,$_POST['last_name']);
 $dob=esc($conn,$_POST['date_of_birth']); $gen=esc($conn,$_POST['gender']);
 $nat=esc($conn,$_POST['nationality']); $addr=esc($conn,$_POST['address']);
 $ph=esc($conn,$_POST['phone']); $st=esc($conn,$_POST['status']);
 mysqli_query($conn,"UPDATE Criminal SET first_name='$fn',last_name='$ln',date_of_birth='$dob',gender='$gen',nationality='$nat',address='$addr',phone='$ph',status='$st' WHERE criminal_id=$id");
 if(!empty($_FILES['photo']['name'])){
  $ext=strtolower(pathinfo($_FILES['photo']['name'],PATHINFO_EXTENSION));
  if(in_array($ext,['jpg','jpeg','png','webp'])&&$_FILES['photo']['size']<=5*1024*1024){
   $fname="uploads/criminals/{$id}_".time().".$ext";
   if(move_uploaded_file($_FILES['photo']['tmp_name'],$fname))
    mysqli_query($conn,"UPDATE Criminal SET photo='$fname' WHERE criminal_id=$id");
  }
 }
 log_activity('Updated','Criminal',$id,"Updated $fn $ln");
 set_flash('success','Criminal record updated.');
 header("Location: profile.php?id=$id"); exit();
}
$r=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Criminal WHERE criminal_id=$id"));
if(!$r){header("Location: records_criminals.php");exit();}
$pageTitle='Edit Criminal';
include 'header.php';
$photo=$r['photo']??'assets/anon.svg';
?>
<div class="breadcrumb"><a href="records_criminals.php">Criminals</a><i data-lucide="chevron-right"></i><a href="profile.php?id=<?=$id?>"><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></a><i data-lucide="chevron-right"></i><span>Edit</span></div>
<div class="page-hdr">
 <div class="page-hdr-left"><div class="page-hdr-icon"><i data-lucide="edit-2"></i></div><div><h2>Edit Criminal</h2><p><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></p></div></div>
 <a href="profile.php?id=<?=$id?>" class="btn btn-ghost"><i data-lucide="arrow-left"></i> Back</a>
</div>
<form method="POST" enctype="multipart/form-data">
<div class="card">
 <div class="card-title"><i data-lucide="camera"></i> Profile Photo</div>
 <div class="photo-upload-box">
  <div class="photo-preview"><img src="<?=htmlspecialchars($photo)?>" id="previewImg" alt="Preview" onerror="this.src='assets/anon.svg'"></div>
  <div>
   <label class="upload-btn" for="photo"><i data-lucide="camera"></i> Change Photo</label>
   <input type="file" id="photo" name="photo" accept="image/*" hidden>
   <p class="upload-hint">JPG, PNG or WEBP — Max 5MB</p>
  </div>
 </div>
</div>
<div class="form-section">
 <div class="form-section-title"><i data-lucide="user"></i> Personal Details</div>
 <div class="form-grid">
  <div class="form-group"><label>First Name</label><input type="text" name="first_name" value="<?=htmlspecialchars($r['first_name'])?>" required></div>
  <div class="form-group"><label>Last Name</label><input type="text" name="last_name" value="<?=htmlspecialchars($r['last_name'])?>" required></div>
  <div class="form-group"><label>Date of Birth</label><input type="date" name="date_of_birth" value="<?=$r['date_of_birth']?>"></div>
  <div class="form-group"><label>Gender</label><select name="gender"><option <?=$r['gender']==='Male'?'selected':''?>>Male</option><option <?=$r['gender']==='Female'?'selected':''?>>Female</option></select></div>
  <div class="form-group"><label>Nationality</label><input type="text" name="nationality" value="<?=htmlspecialchars($r['nationality']??'')?>"></div>
  <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?=htmlspecialchars($r['phone']??'')?>"></div>
  <div class="form-group"><label>Status</label><select name="status"><?php foreach(['Wanted','Imprisoned','Released','Under Trial'] as $s): ?><option value="<?=$s?>" <?=$r['status']===$s?'selected':''?>><?=$s?></option><?php endforeach; ?></select></div>
  <div class="form-group full"><label>Address</label><input type="text" name="address" value="<?=htmlspecialchars($r['address']??'')?>"></div>
 </div>
</div>
<div style="display:flex;gap:10px">
 <button type="submit" class="btn btn-primary" style="padding:11px 28px"><i data-lucide="save"></i> Save Changes</button>
 <a href="profile.php?id=<?=$id?>" class="btn btn-ghost">Cancel</a>
</div>
</form>
<script>document.addEventListener('DOMContentLoaded',()=>initPhotoPreview('photo','previewImg'));</script>
<?php include 'footer.php'; ?>
