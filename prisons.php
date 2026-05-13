<?php
include 'db.php'; $pageTitle = 'Prisons';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 $n=mysqli_real_escape_string($conn,$_POST['prison_name']);
 $l=mysqli_real_escape_string($conn,$_POST['location']);
 $c=intval($_POST['capacity']);
 mysqli_query($conn,"INSERT INTO Prison(prison_name,location,capacity) VALUES('$n','$l',$c)");
}
if (isset($_GET['delete'])) { mysqli_query($conn,"DELETE FROM Prison WHERE prison_id=".intval($_GET['delete'])); header("Location: prisons.php"); exit(); }
include 'header.php';
?>
<div class="page-hdr"><div><h2> Prisons</h2><p>Manage prison facilities</p></div></div>
<div class="card">
 <div class="card-title"> Add New Prison</div>
 <form method="POST">
 <div class="form-grid">
 <div class="form-group"><label>Prison Name</label><input type="text" name="prison_name" required placeholder="e.g. Adiala Jail"></div>
 <div class="form-group"><label>Location / City</label><input type="text" name="location" placeholder="e.g. Rawalpindi"></div>
 <div class="form-group"><label>Capacity</label><input type="number" name="capacity" placeholder="e.g. 2000"></div>
 </div>
 <br><button type="submit" class="btn btn-primary"> Add Prison</button>
 </form>
</div>
<div class="card">
 <div class="card-title">All Prisons</div>
 <div class="tbl-wrap"><table>
 <thead><tr><th>#</th><th>Prison Name</th><th>Location</th><th>Capacity</th><th>Action</th></tr></thead>
 <tbody>
 <?php $res=mysqli_query($conn,"SELECT * FROM Prison ORDER BY prison_name");
 while($r=mysqli_fetch_assoc($res)): ?>
 <tr>
 <td style="color:#999;">#<?=$r['prison_id']?></td>
 <td><strong><?=$r['prison_name']?></strong></td>
 <td><?=$r['location']?></td>
 <td><?=number_format($r['capacity'])?> inmates</td>
 <td><a href="?delete=<?=$r['prison_id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"></a></td>
 </tr>
 <?php endwhile; ?>
 </tbody>
 </table></div>
</div>
<?php include 'footer.php'; ?>
