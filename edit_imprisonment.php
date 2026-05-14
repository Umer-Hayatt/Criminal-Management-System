<?php
include 'db.php';
$id=intval($_GET['id']); $criminal_id=intval($_GET['cid']??0);
if(!$id){header("Location: records_criminals.php");exit();}
if($_SERVER['REQUEST_METHOD']==='POST'){
 $prison_id=intval($_POST['prison_id']);
 $cell=esc($conn,$_POST['cell_number']);
 $start=esc($conn,$_POST['start_date']);
 $end=!empty($_POST['end_date'])?"'".$_POST['end_date']."'":'NULL';
 $years=intval($_POST['sentence_years']);
 $rd=!empty($_POST['release_date'])?"'".$_POST['release_date']."'":'NULL';
 mysqli_query($conn,"UPDATE Imprisonment SET prison_id=$prison_id,cell_number='$cell',start_date='$start',end_date=$end,sentence_years=$years,release_date=$rd WHERE imprisonment_id=$id");
 log_activity('Updated','Imprisonment',$id,"Updated sentence record");
 set_flash('success','Imprisonment record updated.');
 $back=$criminal_id?"profile.php?id=$criminal_id#tab-imprisonments":"records_criminals.php";
 header("Location: $back"); exit();
}
$row=mysqli_fetch_assoc(mysqli_query($conn,"SELECT i.*,p.prison_name,CONCAT(c.first_name,' ',c.last_name) AS cname FROM Imprisonment i JOIN Prison p ON i.prison_id=p.prison_id JOIN Criminal c ON i.criminal_id=c.criminal_id WHERE i.imprisonment_id=$id"));
if(!$row){header("Location: records_criminals.php");exit();}
$prisons=mysqli_query($conn,"SELECT * FROM Prison ORDER BY prison_name");
$pageTitle='Edit Imprisonment';
include 'header.php';
?>
<div class="breadcrumb"><span>Edit Imprisonment Record</span></div>
<div class="page-hdr">
 <div class="page-hdr-left"><div class="page-hdr-icon"><i data-lucide="building-2"></i></div><div><h2>Edit Imprisonment</h2><p><?=htmlspecialchars($row['cname'])?></p></div></div>
 <?php if($criminal_id): ?><a href="profile.php?id=<?=$criminal_id?>" class="btn btn-ghost"><i data-lucide="arrow-left"></i> Back</a><?php endif; ?>
</div>
<div class="form-section">
 <div class="form-section-title"><i data-lucide="building-2"></i> Prison Assignment</div>
 <form method="POST">
  <div class="form-grid">
   <div class="form-group"><label>Prison *</label>
    <select name="prison_id" required>
     <?php while($p=mysqli_fetch_assoc($prisons)): ?><option value="<?=$p['prison_id']?>" <?=$row['prison_id']==$p['prison_id']?'selected':''?>><?=htmlspecialchars($p['prison_name'])?> (<?=$p['location']?>)</option><?php endwhile; ?>
    </select>
   </div>
   <div class="form-group"><label>Cell Number</label><input type="text" name="cell_number" value="<?=htmlspecialchars($row['cell_number']??'')?>"></div>
   <div class="form-group"><label>Sentence (Years)</label><input type="number" name="sentence_years" value="<?=$row['sentence_years']?>" min="1"></div>
   <div class="form-group"><label>Start Date</label><input type="date" name="start_date" value="<?=$row['start_date']?>"></div>
   <div class="form-group"><label>End Date</label><input type="date" name="end_date" value="<?=$row['end_date']??''?>"></div>
   <div class="form-group"><label>Release Date</label><input type="date" name="release_date" value="<?=$row['release_date']??''?>"><small style="font-size:11px;color:var(--txt-soft)">Official release date for countdown tracking</small></div>
  </div>
  <div style="display:flex;gap:10px;margin-top:8px">
   <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save Changes</button>
   <?php if($criminal_id): ?><a href="profile.php?id=<?=$criminal_id?>" class="btn btn-ghost">Cancel</a><?php endif; ?>
  </div>
 </form>
</div>
<?php include 'footer.php'; ?>
