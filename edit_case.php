<?php
include 'db.php';
$id=intval($_GET['id']); $criminal_id=intval($_GET['cid']??0); $err='';
if(!$id){header("Location: cases.php");exit();}
if($_SERVER['REQUEST_METHOD']==='POST'){
 $st=esc($conn,$_POST['case_status']);
 $cd=!empty($_POST['close_date'])?"'".$_POST['close_date']."'":'NULL';
 $ds=esc($conn,$_POST['description']??'');
 mysqli_query($conn,"UPDATE Case_Record SET case_status='$st',close_date=$cd,description='$ds' WHERE case_id=$id");
 log_activity('Updated','Case',$id,"Status set to $st");
 set_flash('success','Case updated.');
 $back=$criminal_id?"profile.php?id=$criminal_id":"case_profile.php?id=$id";
 header("Location: $back"); exit();
}
$row=mysqli_fetch_assoc(mysqli_query($conn,"SELECT c.*,cr.crime_type FROM Case_Record c JOIN Crime cr ON c.crime_id=cr.crime_id WHERE c.case_id=$id"));
if(!$row){header("Location: cases.php");exit();}
$pageTitle='Edit Case #'.$id;
include 'header.php';
?>
<div class="breadcrumb"><a href="cases.php">Cases</a><i data-lucide="chevron-right"></i><span>Edit Case #<?=$id?></span></div>
<div class="page-hdr">
 <div class="page-hdr-left"><div class="page-hdr-icon"><i data-lucide="folder-open"></i></div><div><h2>Edit Case #<?=$id?></h2><p><?=htmlspecialchars($row['crime_type'])?></p></div></div>
 <a href="case_profile.php?id=<?=$id?>" class="btn btn-ghost"><i data-lucide="arrow-left"></i> Back</a>
</div>
<div class="form-section">
 <div class="form-section-title"><i data-lucide="activity"></i> Case Status &amp; Notes</div>
 <form method="POST">
  <div class="form-grid">
   <div class="form-group"><label>Case Status *</label>
    <select name="case_status" required>
     <?php foreach(['Open','Under Investigation','Closed'] as $s): ?><option value="<?=$s?>" <?=$row['case_status']===$s?'selected':''?>><?=$s?></option><?php endforeach; ?>
    </select>
   </div>
   <div class="form-group"><label>Date Closed</label><input type="date" name="close_date" value="<?=htmlspecialchars($row['close_date']??'')?>"></div>
   <div class="form-group full"><label>Description / Notes</label><textarea name="description" rows="4"><?=htmlspecialchars($row['description']??'')?></textarea></div>
  </div>
  <div style="display:flex;gap:10px;margin-top:8px">
   <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save Changes</button>
   <a href="case_profile.php?id=<?=$id?>" class="btn btn-ghost">Cancel</a>
  </div>
 </form>
</div>
<?php include 'footer.php'; ?>
