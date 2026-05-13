<?php
include 'db.php';
$id = intval($_GET['id']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $fn = mysqli_real_escape_string($conn, $_POST['first_name']);
 $ln = mysqli_real_escape_string($conn, $_POST['last_name']);
 $dob = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
 $gen = mysqli_real_escape_string($conn, $_POST['gender']);
 $nat = mysqli_real_escape_string($conn, $_POST['nationality']);
 $addr = mysqli_real_escape_string($conn, $_POST['address']);
 $ph = mysqli_real_escape_string($conn, $_POST['phone']);
 $st = mysqli_real_escape_string($conn, $_POST['status']);
 mysqli_query($conn,
 "UPDATE Criminal SET first_name='$fn',last_name='$ln',date_of_birth='$dob',
 gender='$gen',nationality='$nat',address='$addr',phone='$ph',status='$st'
 WHERE criminal_id=$id");
 header("Location: profile.php?id=$id");
 exit();
}

$r = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Criminal WHERE criminal_id=$id"));
if (!$r) { header("Location: records.php"); exit(); }

$pageTitle = 'Edit Criminal';
include 'header.php';
?>
<div class="page-hdr">
 <div><h2> Edit Personal Information</h2><p><?=$r['first_name'].' '.$r['last_name']?></p></div>
 <a href="profile.php?id=<?=$id?>" class="btn btn-gray">← Back to Profile</a>
</div>
<div class="card">
<form method="POST">
 <div class="form-section">
 <div class="form-section-title"> Personal Details</div>
 <div class="form-grid">
 <div class="form-group"><label>First Name</label><input type="text" name="first_name" value="<?=$r['first_name']?>" required></div>
 <div class="form-group"><label>Last Name</label><input type="text" name="last_name" value="<?=$r['last_name']?>" required></div>
 <div class="form-group"><label>Date of Birth</label><input type="date" name="date_of_birth" value="<?=$r['date_of_birth']?>"></div>
 <div class="form-group"><label>Gender</label>
 <select name="gender">
 <option <?=$r['gender']=='Male'?'selected':''?> value="Male">Male</option>
 <option <?=$r['gender']=='Female'?'selected':''?> value="Female">Female</option>
 </select>
 </div>
 <div class="form-group"><label>Nationality</label><input type="text" name="nationality" value="<?=$r['nationality']?>"></div>
 <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?=$r['phone']?>"></div>
 <div class="form-group"><label>Status</label>
 <select name="status">
 <?php foreach(['Wanted','Imprisoned','Released','Under Trial'] as $s): ?>
 <option <?=$r['status']==$s?'selected':''?> value="<?=$s?>"><?=$s?></option>
 <?php endforeach; ?>
 </select>
 </div>
 <div class="form-group full"><label>Address</label><input type="text" name="address" value="<?=$r['address']?>"></div>
 </div>
 </div>
 <div style="display:flex;gap:10px;">
 <button type="submit" class="btn btn-primary" style="padding:12px 30px;"> Save Changes</button>
 <a href="profile.php?id=<?=$id?>" class="btn btn-gray" style="padding:12px 20px;">Cancel</a>
 </div>
</form>
</div>
<?php include 'footer.php'; ?>
