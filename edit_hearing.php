<?php
include 'db.php';
$id=intval($_GET['id']); $criminal_id=intval($_GET['cid']??0);
if(!$id){header("Location: hearings.php");exit();}
if($_SERVER['REQUEST_METHOD']==='POST'){
 $verdict=esc($conn,$_POST['verdict']); $judge=esc($conn,$_POST['judge_name']);
 $court=esc($conn,$_POST['court_name']); $hdate=esc($conn,$_POST['hearing_date']);
 $nd=!empty($_POST['next_hearing_date'])?"'".$_POST['next_hearing_date']."'":'NULL';
 mysqli_query($conn,"UPDATE Court_Hearing SET verdict='$verdict',judge_name='$judge',court_name='$court',hearing_date='$hdate',next_hearing_date=$nd WHERE hearing_id=$id");
 log_activity('Updated','Hearing',$id,"Verdict set to $verdict");
 set_flash('success','Hearing updated.');
 $back=$criminal_id?"profile.php?id=$criminal_id#tab-hearings":"hearings.php";
 header("Location: $back"); exit();
}
$row=mysqli_fetch_assoc(mysqli_query($conn,"SELECT h.*,c.case_id FROM Court_Hearing h JOIN Case_Record c ON h.case_id=c.case_id WHERE h.hearing_id=$id"));
if(!$row){header("Location: hearings.php");exit();}
$pageTitle='Edit Hearing #'.$id;
include 'header.php';
?>
<div class="breadcrumb"><a href="hearings.php">Hearings</a><i data-lucide="chevron-right"></i><span>Edit Hearing #<?=$id?></span></div>
<div class="page-hdr">
 <div class="page-hdr-left"><div class="page-hdr-icon"><i data-lucide="scale"></i></div><div><h2>Edit Hearing #<?=$id?></h2><p>Case #<?=$row['case_id']?></p></div></div>
 <a href="hearings.php" class="btn btn-ghost"><i data-lucide="arrow-left"></i> Back</a>
</div>
<div class="form-section">
 <div class="form-section-title"><i data-lucide="scale"></i> Hearing Details</div>
 <form method="POST">
  <div class="form-grid">
   <div class="form-group"><label>Court Name</label><input type="text" name="court_name" value="<?=htmlspecialchars($row['court_name']??'')?>" required></div>
   <div class="form-group"><label>Judge Name</label><input type="text" name="judge_name" value="<?=htmlspecialchars($row['judge_name']??'')?>"></div>
   <div class="form-group"><label>Hearing Date</label><input type="date" name="hearing_date" value="<?=$row['hearing_date']?>"></div>
   <div class="form-group"><label>Next Hearing Date</label><input type="date" name="next_hearing_date" value="<?=$row['next_hearing_date']??''?>"></div>
   <div class="form-group"><label>Verdict</label>
    <select name="verdict"><?php foreach(['Pending','Guilty','Not Guilty'] as $v): ?><option value="<?=$v?>" <?=$row['verdict']===$v?'selected':''?>><?=$v?></option><?php endforeach; ?></select>
   </div>
  </div>
  <div style="display:flex;gap:10px;margin-top:8px">
   <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save Changes</button>
   <a href="hearings.php" class="btn btn-ghost">Cancel</a>
  </div>
 </form>
</div>
<?php include 'footer.php'; ?>
