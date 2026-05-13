<?php
// search.php
include 'db.php';
$pageTitle = 'Search Criminal';
include 'header.php';
$results = []; $searched = false; $q = '';

if (!empty($_GET['query'])) {
 $searched = true;
 $q = trim($_GET['query']);
 $sq = mysqli_real_escape_string($conn, $q);
 $res = mysqli_query($conn,
 "SELECT * FROM Criminal
 WHERE first_name LIKE '%$sq%' OR last_name LIKE '%$sq%'
 OR nationality LIKE '%$sq%' OR phone LIKE '%$sq%'
 OR status LIKE '%$sq%' OR address LIKE '%$sq%'
 ORDER BY first_name");
 while ($r = mysqli_fetch_assoc($res)) $results[] = $r;
}
?>
<div class="page-hdr"><div><h2> Search Criminal</h2><p>Search by name, phone, status or nationality</p></div></div>

<div class="card">
 <form method="GET">
 <div class="search-box">
 <input type="text" name="query" placeholder="Type a name, phone number, or status (e.g. Wanted)..." value="<?=htmlspecialchars($q)?>" style="font-size:15px;">
 <button type="submit" class="btn btn-primary" style="padding:11px 28px;"> Search</button>
 <?php if($searched):?><a href="search.php" class="btn btn-gray" style="padding:11px 16px;">Clear</a><?php endif;?>
 </div>
 </form>
 <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;">
 <span style="font-size:12px;color:#999;line-height:30px;">Quick:</span>
 <a href="?query=Wanted" class="btn btn-gray btn-sm"> All Wanted</a>
 <a href="?query=Imprisoned" class="btn btn-gray btn-sm"> Imprisoned</a>
 <a href="?query=Released" class="btn btn-gray btn-sm"> Released</a>
 </div>
</div>

<?php if ($searched): ?>
<div class="card">
 <div style="margin-bottom:14px;font-size:13px;color:#555;">
 <?=count($results)?> result(s) for "<strong><?=htmlspecialchars($q)?></strong>"
 </div>
 <?php if(empty($results)): ?>
 <div style="text-align:center;padding:40px;color:#7f8c8d;">
 <div style="font-size:40px;margin-bottom:10px;"></div>
 <p>No criminals found. Try a different search term.</p>
 </div>
 <?php else: ?>
 <div class="tbl-wrap"><table>
 <thead><tr><th>#</th><th>Full Name</th><th>DOB</th><th>Nationality</th><th>Phone</th><th>Status</th><th>Action</th></tr></thead>
 <tbody>
 <?php foreach($results as $r):
 $b = match($r['status']){'Imprisoned'=>'b-red','Released'=>'b-green','Wanted'=>'b-orange',default=>'b-blue'};
 ?>
 <tr>
 <td style="color:#999;">#<?=$r['criminal_id']?></td>
 <td><strong><?=$r['first_name'].' '.$r['last_name']?></strong></td>
 <td><?=$r['date_of_birth']?></td>
 <td><?=$r['nationality']?></td>
 <td><?=$r['phone']?></td>
 <td><span class="badge <?=$b?>"><?=$r['status']?></span></td>
 <td><a href="profile.php?id=<?=$r['criminal_id']?>" class="btn btn-blue btn-sm">View Full Profile →</a></td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table></div>
 <?php endif; ?>
</div>
<?php endif; ?>
<?php include 'footer.php'; ?>
