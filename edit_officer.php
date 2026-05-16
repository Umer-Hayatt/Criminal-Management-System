<?php
include 'db.php';
require_login();
if(!can('admin')){
 set_flash('error','Permission denied.');
 header("Location: records_officers.php"); exit();
}
$id=intval($_GET['id']);
if(!$id){header("Location: records_officers.php");exit();}
if($_SERVER['REQUEST_METHOD']==='POST'){
 $fn=esc($conn,$_POST['first_name']); $ln=esc($conn,$_POST['last_name']);
 $badge=esc($conn,$_POST['badge_number']); $rank=esc($conn,$_POST['rank']);
 $dept=esc($conn,$_POST['department']); $ph=esc($conn,$_POST['phone']);
 mysqli_query($conn,"UPDATE Officer SET first_name='$fn',last_name='$ln',badge_number='$badge',rank='$rank',department='$dept',phone='$ph' WHERE officer_id=$id");
 if(!empty($_FILES['photo']['name'])){
  $ext=strtolower(pathinfo($_FILES['photo']['name'],PATHINFO_EXTENSION));
  if(in_array($ext,['jpg','jpeg','png','webp'])&&$_FILES['photo']['size']<=5*1024*1024){
   $fname="uploads/officers/{$id}_".time().".$ext";
   if(move_uploaded_file($_FILES['photo']['tmp_name'],$fname))
    mysqli_query($conn,"UPDATE Officer SET photo='$fname' WHERE officer_id=$id");
  }
 }
 log_activity('Updated','Officer',$id,"Updated $fn $ln");
 set_flash('success','Officer record updated.');
 header("Location: officer_profile.php?id=$id"); exit();
}
$r=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Officer WHERE officer_id=$id"));
if(!$r){header("Location: records_officers.php");exit();}
$pageTitle='Edit Officer';
include 'header.php';
$photo=$r['photo']??'assets/anon.png';
?>
<div class="breadcrumb"><a href="records_officers.php">Officers</a><i data-lucide="chevron-right"></i><a href="officer_profile.php?id=<?=$id?>"><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></a><i data-lucide="chevron-right"></i><span>Edit</span></div>
<div class="page-hdr">
 <div class="page-hdr-left"><div class="page-hdr-icon"><i data-lucide="edit-2"></i></div><div><h2>Edit Officer</h2><p><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></p></div></div>
 <a href="officer_profile.php?id=<?=$id?>" class="btn btn-ghost"><i data-lucide="arrow-left"></i> Back</a>
</div>
<form method="POST" enctype="multipart/form-data">
<div class="card">
 <div class="card-title"><i data-lucide="camera"></i> Profile Photo</div>
 <div class="photo-upload-box">
  <div class="photo-preview"><img src="<?=htmlspecialchars($photo)?>" id="previewImg" alt="" onerror="this.src='assets/anon.png'"></div>
  <div><label class="upload-btn" for="photo"><i data-lucide="camera"></i> Change Photo</label><input type="file" id="photo" name="photo" accept="image/*" hidden><p class="upload-hint">JPG, PNG or WEBP — Max 5MB</p></div>
 </div>
</div>
<div class="form-section">
 <div class="form-section-title"><i data-lucide="shield"></i> Officer Details</div>
 <div class="form-grid cols3">
  <div class="form-group"><label>First Name</label><input type="text" name="first_name" value="<?=htmlspecialchars($r['first_name'])?>" required></div>
  <div class="form-group"><label>Last Name</label><input type="text" name="last_name" value="<?=htmlspecialchars($r['last_name'])?>" required></div>
  <div class="form-group"><label>Badge Number</label><input type="text" name="badge_number" value="<?=htmlspecialchars($r['badge_number']??'')?>"></div>
  <div class="form-group"><label>Rank</label><select name="rank"><?php foreach(['Inspector','Sub-Inspector','DSP','SSP','ASI','SP'] as $rk): ?><option <?=$r['rank']===$rk?'selected':''?>><?=$rk?></option><?php endforeach; ?></select></div>
  <div class="form-group"><label>Department</label><input type="text" name="department" value="<?=htmlspecialchars($r['department']??'')?>"></div>
  <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?=htmlspecialchars($r['phone']??'')?>"></div>
 </div>
</div>
<div style="display:flex;gap:10px">
 <button type="submit" class="btn btn-primary" style="padding:11px 28px"><i data-lucide="save"></i> Save Changes</button>
 <a href="officer_profile.php?id=<?=$id?>" class="btn btn-ghost">Cancel</a>
</div>
</form>
<script>document.addEventListener('DOMContentLoaded',()=>initPhotoPreview('photo','previewImg'));</script>
<?php include 'footer.php'; ?>
