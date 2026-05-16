<?php
include 'db.php';
$id=intval($_GET['id']);
if(!$id){header("Location: records_criminals.php");exit();}
mysqli_query($conn,"UPDATE Criminal c JOIN Imprisonment i ON c.criminal_id=i.criminal_id SET c.status='Released' WHERE i.release_date IS NOT NULL AND i.release_date < CURDATE() AND c.status='Imprisoned' AND c.criminal_id=$id");
$criminal=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Criminal WHERE criminal_id=$id"));
if(!$criminal){set_flash('error','Criminal not found.');header("Location: records_criminals.php");exit();}
$crimes   =mysqli_query($conn,"SELECT cr.*,cc.role,cc.arrest_date FROM Crime cr JOIN Criminal_Crime cc ON cr.crime_id=cc.crime_id WHERE cc.criminal_id=$id ORDER BY cr.date_occurred DESC");
$hearings =mysqli_query($conn,"SELECT h.*,c.case_id FROM Court_Hearing h JOIN Case_Record c ON h.case_id=c.case_id JOIN case_criminals ccr ON ccr.case_id=c.case_id WHERE ccr.criminal_id=$id ORDER BY h.hearing_date DESC");
$impris   =mysqli_query($conn,"SELECT i.*,p.prison_name FROM Imprisonment i JOIN Prison p ON i.prison_id=p.prison_id WHERE i.criminal_id=$id ORDER BY i.start_date DESC");
$prisons  =mysqli_query($conn,"SELECT * FROM Prison ORDER BY prison_name");
// Get linked cases for description display
$linked_cases=mysqli_query($conn,"SELECT c.*,cr.crime_type FROM Case_Record c JOIN case_criminals ccr ON c.case_id=ccr.case_id JOIN Crime cr ON c.crime_id=cr.crime_id WHERE ccr.criminal_id=$id ORDER BY c.open_date DESC");
$pageTitle=$criminal['first_name'].' '.$criminal['last_name'].' — Profile';
include 'header.php';
$stb=match($criminal['status']){'Imprisoned'=>'b-red','Released'=>'b-green','Wanted'=>'b-orange',default=>'b-blue'};
$hasPhoto=(!empty($criminal['photo'])&&file_exists($criminal['photo']));
?>
<div class="breadcrumb"><a href="records_criminals.php">Criminals</a><i data-lucide="chevron-right"></i><span><?=htmlspecialchars($criminal['first_name'].' '.$criminal['last_name'])?></span></div>
<div class="profile-card">
 <div style="display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap">
  <?php if($hasPhoto): ?><img src="<?=htmlspecialchars($criminal['photo'])?>" class="profile-photo" alt=""><?php else: ?><img src="assets/anon.svg" class="profile-photo" alt=""><?php endif; ?>
  <div style="flex:1;min-width:200px">
   <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px">
    <div>
     <div style="font-size:11px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:1px">Criminal #<?=str_pad($id,4,'0',STR_PAD_LEFT)?></div>
     <h2 style="font-size:22px;font-weight:800;margin:4px 0 8px"><?=htmlspecialchars($criminal['first_name'].' '.$criminal['last_name'])?></h2>
     <div style="display:flex;gap:8px;flex-wrap:wrap">
      <span class="badge <?=$stb?>"><?=$criminal['status']?></span>
      <?php if($criminal['nationality']): ?><span class="badge b-muted"><?=htmlspecialchars($criminal['nationality'])?></span><?php endif; ?>
     </div>
    </div>
    <div style="display:flex;gap:8px">
     <?php if(can('edit')): ?><a href="edit_criminal.php?id=<?=$id?>" class="btn btn-ghost btn-sm"><i data-lucide="edit-2"></i> Edit</a><?php endif; ?>
     <?php if(can('delete')): ?><button onclick="confirmDelete('delete_criminal.php?id=<?=$id?>','Delete Criminal?','Permanently remove <?=htmlspecialchars(addslashes($criminal['first_name'].' '.$criminal['last_name']))?> and all records?')" class="btn btn-danger btn-sm"><i data-lucide="trash-2"></i> Delete</button><?php endif; ?>
    </div>
   </div>
   <div class="profile-meta" style="margin-top:14px">
    <div class="meta-item"><label>Date of Birth</label><span><?=fmt_date($criminal['date_of_birth'])?></span></div>
    <div class="meta-item"><label>Gender</label><span><?=htmlspecialchars($criminal['gender']??'—')?></span></div>
    <div class="meta-item"><label>Phone</label><span><?=htmlspecialchars($criminal['phone']??'—')?></span></div>
   </div>
   <?php if(!empty($criminal['address'])): ?>
   <div style="margin-top:10px"><label style="font-size:10px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;display:block">Address</label><span style="font-size:13px;color:var(--txt);font-weight:500"><?=htmlspecialchars($criminal['address'])?></span></div>
   <?php endif; ?>
  </div>
 </div>
</div>

<!-- CASE DESCRIPTION OVERVIEW -->
<?php
$lc_rows=[]; while($r=mysqli_fetch_assoc($linked_cases)) $lc_rows[]=$r;
if(!empty($lc_rows)): ?>
<div class="card" style="margin-bottom:16px">
 <div class="card-title"><i data-lucide="folder-open"></i> Linked Cases</div>
 <?php foreach($lc_rows as $c):
  $cs=match($c['case_status']){'Open'=>'b-orange','Closed'=>'b-green','Under Investigation'=>'b-blue',default=>'b-muted'};
 ?>
 <div style="padding:10px 0;border-bottom:1px solid var(--border-lt);display:flex;align-items:flex-start;gap:10px">
  <a href="case_profile.php?id=<?=$c['case_id']?>" style="color:var(--accent);font-weight:700;white-space:nowrap">Case #<?=$c['case_id']?></a>
  <div style="flex:1">
   <strong style="color:var(--txt)"><?=htmlspecialchars($c['title']?:$c['crime_type'])?></strong>
   <span class="badge <?=$cs?>" style="margin-left:6px"><?=$c['case_status']?></span>
   <?php if(!empty($c['description'])): ?><p style="color:var(--txt-mid);font-size:12px;margin-top:4px"><?=htmlspecialchars($c['description'])?></p><?php endif; ?>
  </div>
  <span style="font-size:11px;color:var(--txt-soft);white-space:nowrap"><?=fmt_date($c['open_date'])?></span>
 </div>
 <?php endforeach; ?>
</div>
<?php endif; ?>

<div id="tabGroup">
<div class="tab-bar">
 <button class="tab-btn" data-tab="crimes"        onclick="switchTab('crimes','tabGroup')"><i data-lucide="alert-triangle"></i> Crimes</button>
 <button class="tab-btn" data-tab="imprisonments" onclick="switchTab('imprisonments','tabGroup')"><i data-lucide="building-2"></i> Imprisonments</button>
 <button class="tab-btn" data-tab="hearings"      onclick="switchTab('hearings','tabGroup')"><i data-lucide="scale"></i> Hearings</button>
</div>

<!-- CRIMES -->
<div class="tab-pane" id="tab-crimes">
<div class="section-card">
<div class="section-hdr"><h3>Crime History</h3><?php if(can('edit')): ?><a href="add_crime.php?id=<?=$id?>" class="btn btn-ghost btn-sm"><i data-lucide="plus"></i> Add Crime</a><?php endif; ?></div>
 <?php $cr_rows=[]; while($r=mysqli_fetch_assoc($crimes)) $cr_rows[]=$r; ?>
 <?php if(empty($cr_rows)): ?><div class="no-data">No crimes linked.</div>
 <?php else: ?><div class="tbl-wrap"><table>
  <thead><tr><th>#</th><th>Crime Type</th><th>Date</th><th>Severity</th><th>Role</th><th>Arrested</th></tr></thead>
  <tbody>
  <?php foreach($cr_rows as $i=>$r):
   $sev=match($r['severity']??''){'Felony'=>'b-red','Major'=>'b-orange','Minor'=>'b-blue',default=>'b-muted'};
   $rol=match($r['role']??''){'Main Accused'=>'b-red','Accomplice'=>'b-orange',default=>'b-blue'};
  ?>
  <tr>
   <td style="color:var(--txt-soft)"><?=$i+1?></td>
   <td><strong><?=htmlspecialchars($r['crime_type'])?></strong><?php if($r['location']): ?><br><span style="font-size:11px;color:var(--txt-mid)"><?=htmlspecialchars($r['location'])?></span><?php endif; ?></td>
   <td><?=fmt_date($r['date_occurred'])?></td>
   <td><span class="badge <?=$sev?>"><?=$r['severity']?></span></td>
   <td><span class="badge <?=$rol?>"><?=$r['role']?></span></td>
   <td><?=fmt_date($r['arrest_date'])?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
 </table></div><?php endif; ?>
</div>
</div>

<!-- IMPRISONMENTS -->
<div class="tab-pane" id="tab-imprisonments">
<div class="section-card">
 <div class="section-hdr"><h3>Imprisonment History</h3><?php if(can('edit')): ?><button class="btn btn-ghost btn-sm" onclick="toggleForm('addImprisonForm')"><i data-lucide="plus"></i> Add</button><?php endif; ?></div>
 <?php if(can('edit')): ?>
 <div id="addImprisonForm" style="display:none;margin-bottom:12px;padding:14px;background:var(--bg);border:1px solid var(--border);border-radius:8px">
  <form method="POST" action="add_imprisonment.php">
   <input type="hidden" name="criminal_id" value="<?=$id?>">
   <div class="form-grid cols3">
    <div class="form-group"><label>Prison *</label><select name="prison_id" required><?php while($p=mysqli_fetch_assoc($prisons)): ?><option value="<?=$p['prison_id']?>"><?=htmlspecialchars($p['prison_name'])?></option><?php endwhile; ?></select></div>
    <div class="form-group"><label>Cell #</label><input type="text" name="cell_number"></div>
    <div class="form-group"><label>Sentence (years)</label><input type="number" name="sentence_years" min="1"></div>
    <div class="form-group"><label>Start Date</label><input type="date" name="start_date"></div>
    <div class="form-group"><label>End Date</label><input type="date" name="end_date"></div>
    <div class="form-group"><label>Release Date</label><input type="date" name="release_date"></div>
   </div>
   <button type="submit" class="btn btn-primary btn-sm" style="margin-top:8px"><i data-lucide="save"></i> Add</button>
  </form>
 </div>
 <?php endif; ?>
 <?php $imp_rows=[]; while($r=mysqli_fetch_assoc($impris)) $imp_rows[]=$r; ?>
 <?php if(empty($imp_rows)): ?><div class="no-data">No imprisonment records.</div>
 <?php else: ?><div class="tbl-wrap"><table>
  <thead><tr><th>Prison</th><th>Cell</th><th>Sentence</th><th>From</th><th>To</th><th>Release</th><th></th></tr></thead>
  <tbody>
  <?php foreach($imp_rows as $r):
   $release_days=null;
   if($r['release_date']){$release_days=ceil((strtotime($r['release_date'])-time())/86400);}
   $countdown='—';
   if($release_days!==null){if($release_days<0)$countdown='<span class="badge b-red">OVERDUE</span>';elseif($release_days<=180)$countdown='<span class="badge b-orange">'.$release_days.'d left</span>';else $countdown='<span class="badge b-green">'.$release_days.'d left</span>';}
  ?>
  <tr>
   <td><strong><?=htmlspecialchars($r['prison_name'])?></strong></td>
   <td><?=htmlspecialchars($r['cell_number']??'—')?></td>
   <td><?=$r['sentence_years']?:0?> yrs</td>
   <td><?=fmt_date($r['start_date'])?></td>
   <td><?=$r['end_date']?fmt_date($r['end_date']):'—'?></td>
   <td><?=$countdown?></td>
   <td><?php if(can('edit')): ?><a href="edit_imprisonment.php?id=<?=$r['imprisonment_id']?>&cid=<?=$id?>" class="btn btn-ghost btn-xs"><i data-lucide="edit-2"></i></a><?php endif; ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
 </table></div><?php endif; ?>
</div>
</div>

<!-- HEARINGS (via case membership) -->
<div class="tab-pane" id="tab-hearings">
<div class="section-card">
 <div class="section-hdr"><h3>Court Hearings</h3><span style="font-size:11px;color:var(--txt-soft)">Via linked cases</span></div>
 <?php $h_rows=[]; if($hearings) while($r=mysqli_fetch_assoc($hearings)) $h_rows[]=$r; ?>
 <?php if(empty($h_rows)): ?><div class="no-data">No hearings found via linked cases.</div>
 <?php else: ?><div class="tbl-wrap"><table>
  <thead><tr><th>Court</th><th>Case</th><th>Judge</th><th>Date</th><th>Verdict</th><th>Next</th></tr></thead>
  <tbody>
  <?php foreach($h_rows as $r):
   $vb=match($r['verdict']??''){'Guilty'=>'b-red','Not Guilty'=>'b-green','Pending'=>'b-orange',default=>'b-muted'};
  ?>
  <tr>
   <td><strong><?=htmlspecialchars($r['court_name']??'—')?></strong></td>
   <td><a href="case_profile.php?id=<?=$r['case_id']?>" style="color:var(--accent)">Case #<?=$r['case_id']?></a></td>
   <td><?=htmlspecialchars($r['judge_name']??'—')?></td>
   <td><?=fmt_date($r['hearing_date'])?></td>
   <td><span class="badge <?=$vb?>"><?=$r['verdict']?></span></td>
   <td><?=$r['next_hearing_date']?fmt_date($r['next_hearing_date']):'—'?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
 </table></div><?php endif; ?>
</div>
</div>
</div>

<script>
function toggleForm(id){var el=document.getElementById(id);el.style.display=el.style.display==='none'?'block':'none';}
document.addEventListener('DOMContentLoaded',function(){
 initTabs('tabGroup');
 var hash=window.location.hash.replace('#tab-','');
 switchTab(hash||'crimes','tabGroup');
});
</script>
<?php include 'footer.php'; ?>
