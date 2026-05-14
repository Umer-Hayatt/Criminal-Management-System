<?php
include 'db.php';
$id=intval($_GET['id']);
if(!$id){header("Location: records_criminals.php");exit();}
mysqli_query($conn,"UPDATE Criminal c JOIN Imprisonment i ON c.criminal_id=i.criminal_id SET c.status='Released' WHERE i.release_date IS NOT NULL AND i.release_date < CURDATE() AND c.status='Imprisoned' AND c.criminal_id=$id");
$criminal=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Criminal WHERE criminal_id=$id"));
if(!$criminal){set_flash('error','Criminal not found.');header("Location: records_criminals.php");exit();}
$crimes   =mysqli_query($conn,"SELECT cr.*,cc.role,cc.arrest_date FROM Crime cr JOIN Criminal_Crime cc ON cr.crime_id=cc.crime_id WHERE cc.criminal_id=$id ORDER BY cr.date_occurred DESC");
$cases    =mysqli_query($conn,"SELECT c.*,cr.crime_type FROM Case_Record c JOIN case_criminals cc ON c.case_id=cc.case_id JOIN Crime cr ON c.crime_id=cr.crime_id WHERE cc.criminal_id=$id ORDER BY c.open_date DESC");
$hearings =mysqli_query($conn,"SELECT h.*,c.case_id FROM Court_Hearing h JOIN hearing_criminals hc ON h.hearing_id=hc.hearing_id JOIN Case_Record c ON h.case_id=c.case_id WHERE hc.criminal_id=$id ORDER BY h.hearing_date DESC");
$impris   =mysqli_query($conn,"SELECT i.*,p.prison_name,p.location AS prison_city FROM Imprisonment i JOIN Prison p ON i.prison_id=p.prison_id WHERE i.criminal_id=$id ORDER BY i.start_date DESC");
$prisons  =mysqli_query($conn,"SELECT * FROM Prison ORDER BY prison_name");
$all_cases=mysqli_query($conn,"SELECT c.case_id,c.title,cr.crime_type FROM Case_Record c JOIN Crime cr ON c.crime_id=cr.crime_id ORDER BY c.case_id DESC");
$pageTitle=$criminal['first_name'].' '.$criminal['last_name'].' — Profile';
include 'header.php';
$stb=match($criminal['status']){'Imprisoned'=>'b-red','Released'=>'b-green','Wanted'=>'b-orange',default=>'b-blue'};
$bor=match($criminal['status']){'Imprisoned'=>'border-red','Released'=>'border-green','Wanted'=>'border-orange',default=>''};
?>
<div class="breadcrumb"><a href="records_criminals.php">Criminals</a><i data-lucide="chevron-right"></i><span><?=htmlspecialchars($criminal['first_name'].' '.$criminal['last_name'])?></span></div>
<div class="profile-card">
 <div style="display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap">
  <?=avatar_html($criminal['first_name'],$criminal['last_name'],$criminal['photo']??null,80,$bor)?>
  <div style="flex:1;min-width:200px">
   <div style="font-size:11px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:1px">Criminal #<?=str_pad($id,4,'0',STR_PAD_LEFT)?></div>
   <h2 style="font-size:22px;font-weight:800;margin:4px 0 8px"><?=htmlspecialchars($criminal['first_name'].' '.$criminal['last_name'])?></h2>
   <div style="display:flex;gap:8px;flex-wrap:wrap">
    <span class="badge <?=$stb?>"><?=$criminal['status']?></span>
    <?php if($criminal['nationality']): ?><span class="badge b-muted"><?=htmlspecialchars($criminal['nationality'])?></span><?php endif; ?>
   </div>
   <div class="profile-meta" style="margin-top:14px">
    <div class="meta-item"><label>Date of Birth</label><span><?=fmt_date($criminal['date_of_birth'])?></span></div>
    <div class="meta-item"><label>Phone</label><span><?=htmlspecialchars($criminal['phone']??'—')?></span></div>
    <div class="meta-item" style="grid-column:span 2"><label>Address</label><span><?=htmlspecialchars($criminal['address']??'—')?></span></div>
   </div>
  </div>
 </div>
 <?php if(can('edit')||can('delete')): ?>
 <div style="position:absolute;bottom:16px;right:16px;display:flex;gap:8px">
  <?php if(can('edit')): ?><a href="edit_criminal.php?id=<?=$id?>" class="btn btn-ghost btn-sm"><i data-lucide="edit-2"></i> Edit</a><?php endif; ?>
  <?php if(can('delete')): ?><button onclick="confirmDelete('delete_criminal.php?id=<?=$id?>','Delete Criminal?','Permanently remove <?=htmlspecialchars(addslashes($criminal['first_name'].' '.$criminal['last_name']))?> and all records?')" class="btn btn-danger btn-sm"><i data-lucide="trash-2"></i> Delete</button><?php endif; ?>
 </div>
 <?php endif; ?>
</div>

<div id="tabGroup">
<div class="tab-bar">
 <button class="tab-btn" data-tab="crimes"       onclick="switchTab('crimes','tabGroup')"><i data-lucide="alert-triangle"></i> Crimes</button>
 <button class="tab-btn" data-tab="cases"        onclick="switchTab('cases','tabGroup')"><i data-lucide="folder-open"></i> Cases</button>
 <button class="tab-btn" data-tab="imprisonments" onclick="switchTab('imprisonments','tabGroup')"><i data-lucide="building-2"></i> Imprisonments</button>
 <button class="tab-btn" data-tab="hearings"     onclick="switchTab('hearings','tabGroup')"><i data-lucide="scale"></i> Hearings</button>
</div>

<!-- CRIMES TAB -->
<div class="tab-pane" id="tab-crimes">
<div class="section-card">
 <div class="section-hdr"><h3>Crimes</h3><a href="add_crime.php?id=<?=$id?>" class="btn btn-ghost btn-sm"><i data-lucide="plus"></i> Add Crime</a></div>
 <?php $cr_rows=[]; while($r=mysqli_fetch_assoc($crimes)) $cr_rows[]=$r; ?>
 <?php if(empty($cr_rows)): ?><div class="no-data">No crimes linked.</div>
 <?php else: ?><div class="tbl-wrap"><table>
  <thead><tr><th>#</th><th>Crime Type</th><th>Date</th><th>Severity</th><th>Role</th><th>Arrested</th></tr></thead>
  <tbody>
  <?php foreach($cr_rows as $r):
   $sev=match($r['severity']??''){'Felony'=>'b-red','Major'=>'b-orange','Minor'=>'b-blue',default=>'b-muted'};
   $rol=match($r['role']??''){'Main Accused'=>'b-red','Accomplice'=>'b-orange',default=>'b-blue'};
  ?>
  <tr>
   <td style="color:var(--txt-soft)">#<?=$r['crime_id']?></td>
   <td><strong><?=htmlspecialchars($r['crime_type'])?></strong></td>
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

<!-- CASES TAB -->
<div class="tab-pane" id="tab-cases">
<div class="section-card">
 <div class="section-hdr"><h3>Cases</h3>
  <?php if(can('edit')): ?>
  <button class="btn btn-ghost btn-sm" onclick="toggleForm('linkCaseForm')"><i data-lucide="link"></i> Link to Case</button>
  <?php endif; ?>
 </div>
 <?php if(can('edit')): ?>
 <div id="linkCaseForm" style="display:none;margin-bottom:12px;padding:14px;background:var(--bg);border:1px solid var(--border);border-radius:8px">
  <div class="form-group"><label>Select Case</label>
   <select id="linkCaseSelect">
    <option value="">-- Select Case --</option>
    <?php while($c=mysqli_fetch_assoc($all_cases)): ?>
    <option value="<?=$c['case_id']?>">Case #<?=$c['case_id']?> — <?=htmlspecialchars($c['title']?:$c['crime_type'])?></option>
    <?php endwhile; ?>
   </select>
  </div>
  <button class="btn btn-primary btn-sm" style="margin-top:8px" onclick="linkCase()"><i data-lucide="link"></i> Link</button>
 </div>
 <?php endif; ?>
 <?php $ca_rows=[]; while($r=mysqli_fetch_assoc($cases)) $ca_rows[]=$r; ?>
 <?php if(empty($ca_rows)): ?><div class="no-data">No cases linked.</div>
 <?php else: ?><div class="tbl-wrap"><table>
  <thead><tr><th>Case #</th><th>Crime Type</th><th>Status</th><th>Opened</th><th></th></tr></thead>
  <tbody>
  <?php foreach($ca_rows as $r):
   $cs=match($r['case_status']){'Open'=>'b-orange','Closed'=>'b-green','Under Investigation'=>'b-blue',default=>'b-muted'};
  ?>
  <tr id="case-row-<?=$r['case_id']?>">
   <td><strong>Case #<?=$r['case_id']?></strong></td>
   <td><?=htmlspecialchars($r['crime_type'])?></td>
   <td><span class="badge <?=$cs?>"><?=$r['case_status']?></span></td>
   <td><?=fmt_date($r['open_date'])?></td>
   <td><div class="td-actions">
    <a href="case_profile.php?id=<?=$r['case_id']?>" class="btn btn-ghost btn-xs"><i data-lucide="eye"></i></a>
    <?php if(can('edit')): ?><button onclick="unlinkCase(<?=$r['case_id']?>)" class="btn btn-danger btn-xs"><i data-lucide="x"></i></button><?php endif; ?>
   </div></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
 </table></div><?php endif; ?>
</div>
</div>

<!-- IMPRISONMENTS TAB -->
<div class="tab-pane" id="tab-imprisonments">
<div class="section-card">
 <div class="section-hdr"><h3>Imprisonment History</h3>
  <?php if(can('edit')): ?><button class="btn btn-ghost btn-sm" onclick="toggleForm('addImprisonForm')"><i data-lucide="plus"></i> Add Imprisonment</button><?php endif; ?>
 </div>
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
   $active=empty($r['end_date'])||$r['end_date']>=date('Y-m-d');
   $countdown='';
   if($r['release_date']){$days=ceil((strtotime($r['release_date'])-time())/86400);if($days<0)$countdown='<span class="badge b-red">OVERDUE</span>';elseif($days<=180)$countdown='<span class="badge b-orange">'.$days.'d</span>';else $countdown='<span class="badge b-green">'.$days.'d</span>';}
  ?>
  <tr>
   <td><strong><?=htmlspecialchars($r['prison_name'])?></strong></td>
   <td><?=htmlspecialchars($r['cell_number']??'—')?></td>
   <td><?=$r['sentence_years']?> yrs</td>
   <td><?=fmt_date($r['start_date'])?></td>
   <td><?=$r['end_date']?fmt_date($r['end_date']):'—'?></td>
   <td><?=$countdown?:fmt_date($r['release_date']??null)?></td>
   <td><?php if(can('edit')): ?><a href="edit_imprisonment.php?id=<?=$r['imprisonment_id']?>&cid=<?=$id?>" class="btn btn-ghost btn-xs"><i data-lucide="edit-2"></i></a><?php endif; ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
 </table></div><?php endif; ?>
</div>
</div>

<!-- HEARINGS TAB -->
<div class="tab-pane" id="tab-hearings">
<div class="section-card">
 <div class="section-hdr"><h3>Court Hearings</h3></div>
 <?php $h_rows=[]; while($r=mysqli_fetch_assoc($hearings)) $h_rows[]=$r; ?>
 <?php if(empty($h_rows)): ?><div class="no-data">No hearings.</div>
 <?php else: ?><div class="tbl-wrap"><table>
  <thead><tr><th>Court</th><th>Case</th><th>Date</th><th>Verdict</th><th>Next</th></tr></thead>
  <tbody>
  <?php foreach($h_rows as $r):
   $vb=match($r['verdict']??''){'Guilty'=>'b-red','Not Guilty'=>'b-green','Pending'=>'b-orange',default=>'b-muted'};
  ?>
  <tr>
   <td><strong><?=htmlspecialchars($r['court_name']??'—')?></strong></td>
   <td><a href="case_profile.php?id=<?=$r['case_id']?>" style="color:var(--accent)">Case #<?=$r['case_id']?></a></td>
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
var CRIMINAL_ID=<?=$id?>;
function toggleForm(id){var el=document.getElementById(id);el.style.display=el.style.display==='none'?'block':'none';}
function linkCase(){
 var cid=document.getElementById('linkCaseSelect').value;
 if(!cid){alert('Select a case first');return;}
 fetch('ajax/case_affil.php',{method:'POST',body:new URLSearchParams({action:'add_criminal',case_id:cid,criminal_id:CRIMINAL_ID})})
 .then(r=>r.json()).then(r=>{if(r.ok){location.reload();}else alert(r.msg);});
}
function unlinkCase(cid){
 if(!confirm('Unlink from Case #'+cid+'?')) return;
 fetch('ajax/case_affil.php',{method:'POST',body:new URLSearchParams({action:'remove_criminal',case_id:cid,criminal_id:CRIMINAL_ID})})
 .then(r=>r.json()).then(r=>{if(r.ok){document.getElementById('case-row-'+cid)?.remove();}else alert(r.msg);});
}
document.addEventListener('DOMContentLoaded',()=>{
 initTabs('tabGroup');
 var hash=window.location.hash.replace('#tab-','');
 switchTab(hash||'crimes','tabGroup');
});
</script>
<?php include 'footer.php'; ?>
