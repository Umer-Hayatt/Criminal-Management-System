<?php
include 'db.php';
$pageTitle = 'Officers'; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
 $fn=$_POST['first_name']; $ln=$_POST['last_name']; $b=$_POST['badge_number'];
 $rk=$_POST['rank']; $dp=$_POST['department']; $ph=$_POST['phone'];
 $fn=mysqli_real_escape_string($conn,$fn); $ln=mysqli_real_escape_string($conn,$ln);
 $b=mysqli_real_escape_string($conn,$b); $rk=mysqli_real_escape_string($conn,$rk);
 $dp=mysqli_real_escape_string($conn,$dp); $ph=mysqli_real_escape_string($conn,$ph);
 mysqli_query($conn,"INSERT INTO Officer(first_name,last_name,badge_number,rank,department,phone) VALUES('$fn','$ln','$b','$rk','$dp','$ph')");
}
if (isset($_GET['delete'])) {
 mysqli_query($conn,"DELETE FROM Officer WHERE officer_id=".intval($_GET['delete']));
 header("Location: officers.php"); exit();
}
include 'header.php';
?>
<div class="page-hdr"><div><h2> Officers</h2><p>Manage investigating officers</p></div></div>
<div class="card">
 <div class="card-title"> Add New Officer</div>
 <form method="POST">
 <input type="hidden" name="action" value="add">
 <div class="form-grid cols3">
 <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
 <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
 <div class="form-group"><label>Badge Number</label><input type="text" name="badge_number" placeholder="e.g. B-1006"></div>
 <div class="form-group"><label>Rank</label>
 <select name="rank"><option>Inspector</option><option>Sub-Inspector</option><option>DSP</option><option>ASI</option><option>SSP</option></select>
 </div>
 <div class="form-group"><label>Department</label><input type="text" name="department" placeholder="e.g. CID Rawalpindi"></div>
 <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
 </div>
 <br><button type="submit" class="btn btn-primary"> Add Officer</button>
 </form>
</div>
<div class="card">
 <div class="card-title">All Officers</div>
 <div class="tbl-wrap"><table>
 <thead><tr><th>#</th><th>Name</th><th>Badge</th><th>Rank</th><th>Department</th><th>Phone</th><th>Action</th></tr></thead>
 <tbody>
 <?php $res=mysqli_query($conn,"SELECT * FROM Officer ORDER BY first_name");
 while($r=mysqli_fetch_assoc($res)): ?>
 <tr>
 <td style="color:#999;vertical-align:middle;">#<?=$r['officer_id']?></td>
 <td><strong><?=$r['first_name'].' '.$r['last_name']?></strong></td>
 <td><span class="badge b-blue"><?=$r['badge_number']?></span></td>
 <td><?=$r['rank']?></td><td><?=$r['department']?></td><td><?=$r['phone']?></td>
 <td><a href="?delete=<?=$r['officer_id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"></a></td>
 </tr>
 <?php endwhile; ?>
 </tbody>
 </table></div>
</div>
<?php include 'footer.php'; ?>
