<?php
include 'db.php';
$pageTitle = 'Add Prison';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $n=esc($conn,$_POST['prison_name']);
 $l=esc($conn,$_POST['location']);
 $c=intval($_POST['capacity']);
 mysqli_query($conn,"INSERT INTO Prison(prison_name,location,capacity) VALUES('$n','$l',$c)");
 $pid=mysqli_insert_id($conn);
 log_activity('Created','Prison',$pid,"Added $n in $l");
 set_flash('success',"Prison \"$n\" added successfully.");
 header("Location: records_prisons.php"); exit();
}
include 'header.php';
?>
<div class="breadcrumb"><a href="records_prisons.php">Prisons</a><i data-lucide="chevron-right"></i><span>Add Prison</span></div>
<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="building-2"></i></div>
  <div><h2>Add Prison</h2><p>Register a new prison facility</p></div>
 </div>
 <a href="records_prisons.php" class="btn btn-ghost"><i data-lucide="arrow-left"></i> Back</a>
</div>
<div class="form-section">
 <div class="form-section-title"><i data-lucide="building-2"></i> Prison Details</div>
 <form method="POST">
  <div class="form-grid">
   <div class="form-group"><label>Prison Name *</label><input type="text" name="prison_name" placeholder="e.g. Adiala Jail" required></div>
   <div class="form-group"><label>Location / City</label><input type="text" name="location" placeholder="e.g. Rawalpindi"></div>
   <div class="form-group"><label>Capacity</label><input type="number" name="capacity" placeholder="e.g. 2000" min="1"></div>
  </div>
  <br>
  <div style="display:flex;gap:10px;">
   <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Add Prison</button>
   <a href="records_prisons.php" class="btn btn-ghost">Cancel</a>
  </div>
 </form>
</div>
<?php include 'footer.php'; ?>
