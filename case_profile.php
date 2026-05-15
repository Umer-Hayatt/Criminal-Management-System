<?php
include 'db.php';
$id=intval($_GET['id']);
if(!$id){header("Location: cases.php");exit();}
$case=mysqli_fetch_assoc(mysqli_query($conn,"SELECT c.*,cr.crime_type,lo.first_name AS lead_first, lo.last_name AS lead_last FROM Case_Record c JOIN Crime cr ON c.crime_id=cr.crime_id LEFT JOIN Officer lo ON c.lead_officer_id=lo.officer_id WHERE c.case_id=$id"));
if(!$case){set_flash('error','Case not found.');header("Location: cases.php");exit();}
$officers=[];
if($res=mysqli_query($conn,"SELECT o.*,co.role AS oc_role FROM Officer o JOIN case_officers co ON o.officer_id=co.officer_id WHERE co.case_id=$id")){
 while($r=mysqli_fetch_assoc($res)) $officers[]=$r;
}
$criminals=[];
if($res=mysqli_query($conn,"SELECT c.*,cc.role AS cr_role FROM Criminal c JOIN case_criminals cc ON c.criminal_id=cc.criminal_id WHERE cc.case_id=$id")){
 while($r=mysqli_fetch_assoc($res)) $criminals[]=$r;
}
$victims=[];
if($res=mysqli_query($conn,"SELECT * FROM Victim WHERE case_id=$id")){
 while($r=mysqli_fetch_assoc($res)) $victims[]=$r;
}
$suspects=[];
if($res=mysqli_query($conn,"SELECT * FROM Suspect WHERE case_id=$id")){
 while($r=mysqli_fetch_assoc($res)) $suspects[]=$r;
}
$hearings=[];
if($res=mysqli_query($conn,"SELECT * FROM Court_Hearing WHERE case_id=$id ORDER BY hearing_date DESC")){
 while($r=mysqli_fetch_assoc($res)) $hearings[]=$r;
}

$all_officers=[];
if($res=mysqli_query($conn,"SELECT * FROM Officer ORDER BY first_name")){
 while($r=mysqli_fetch_assoc($res)) $all_officers[]=$r;
}
$all_criminals=[];
if($res=mysqli_query($conn,"SELECT * FROM Criminal ORDER BY first_name")){
 while($r=mysqli_fetch_assoc($res)) $all_criminals[]=$r;
}
$cs=match($case['case_status']){'Open'=>'b-orange','Closed'=>'b-green','Under Investigation'=>'b-blue',default=>'b-muted'};
$pageTitle='Case #'.$id;
include 'header.php';
?>
<div class="breadcrumb"><a href="cases.php">Cases</a><i data-lucide="chevron-right"></i><span>Case #<?=$id?></span></div>
<div class="case-id-card">
 <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px">
  <div>
   <div style="font-size:11px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:1px">Case #<?=str_pad($id,4,'0',STR_PAD_LEFT)?></div>
   <h2 style="font-size:20px;font-weight:800;margin:4px 0 8px"><?=htmlspecialchars($case['title']?:'Case #'.$id)?></h2>
   <div style="display:flex;gap:8px;flex-wrap:wrap">
    <span class="badge <?=$cs?>"><?=$case['case_status']?></span>
    <span class="badge b-muted">Opened <?=fmt_date($case['open_date'])?></span>
    <?php if($case['close_date']): ?><span class="badge b-green">Closed <?=fmt_date($case['close_date'])?></span><?php endif; ?>
    <?php if(!empty($case['lead_first'])): ?><span class="badge b-blue">Lead: <?=htmlspecialchars($case['lead_first'].' '.$case['lead_last'])?></span><?php endif; ?>
   </div>
  </div>
  <div style="display:flex;gap:8px">
   <a href="export_case.php?id=<?=$id?>" target="_blank" class="btn btn-primary btn-sm"><i data-lucide="download"></i> Export Report</a>
   <?php if(can('delete')): ?><button onclick="confirmDelete('delete_case.php?id=<?=$id?>','Delete Case #<?=$id?>?','Remove Case #<?=$id?> and all records?')" class="btn btn-danger btn-sm"><i data-lucide="trash-2"></i> Delete</button><?php endif; ?>
  </div>
 </div>
</div>

<div id="tabGroup">
<div class="tab-bar">
 <button class="tab-btn" data-tab="overview"  onclick="switchTab('overview','tabGroup')"><i data-lucide="info"></i> Overview</button>
 <button class="tab-btn" data-tab="criminals" onclick="switchTab('criminals','tabGroup')"><i data-lucide="users"></i> Criminals</button>
 <button class="tab-btn" data-tab="officers"  onclick="switchTab('officers','tabGroup')"><i data-lucide="shield"></i> Officers</button>
 <button class="tab-btn" data-tab="victims"   onclick="switchTab('victims','tabGroup')"><i data-lucide="heart"></i> Victims</button>
 <button class="tab-btn" data-tab="suspects"  onclick="switchTab('suspects','tabGroup')"><i data-lucide="user-x"></i> Suspects</button>
 <button class="tab-btn" data-tab="hearings"  onclick="switchTab('hearings','tabGroup')"><i data-lucide="scale"></i> Hearings</button>

</div>

<!-- OVERVIEW TAB -->
<div class="tab-pane" id="tab-overview">
<div class="section-card">
 <div class="section-hdr"><h3>Case Details</h3><?php if(can('edit')): ?><button class="btn btn-ghost btn-sm" onclick="toggleForm('editCaseForm')"><i data-lucide="edit-2"></i> Edit Case</button><?php endif; ?></div>
 <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
  <div><label style="font-size:10px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;display:block">Title</label><span style="font-size:14px;color:var(--txt);font-weight:600"><?=htmlspecialchars($case['title']?:'Case #'.$id)?></span></div>
  <div><label style="font-size:10px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;display:block">Status</label><span class="badge <?=$cs?>"><?=$case['case_status']?></span></div>
  <div><label style="font-size:10px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;display:block">Open Date</label><span style="color:var(--txt)"><?=fmt_date($case['open_date'])?></span></div>
  <div><label style="font-size:10px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;display:block">Close Date</label><span style="color:var(--txt)"><?=$case['close_date']?fmt_date($case['close_date']):'—'?></span></div>
  <?php if(!empty($case['lead_first'])): ?><div><label style="font-size:10px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;display:block">Lead Officer</label><span style="color:var(--txt)"><?=htmlspecialchars($case['lead_first'].' '.$case['lead_last'])?></span></div><?php endif; ?>
  <div><label style="font-size:10px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;display:block">Crime Type</label><span style="color:var(--txt)"><?=htmlspecialchars($case['crime_type'])?></span></div>
 </div>
 <div style="border-top:1px solid var(--border);padding-top:12px">
  <label style="font-size:10px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px">Description / Notes</label>
  <p style="color:var(--txt-mid);font-size:13px;line-height:1.7"><?=htmlspecialchars($case['description']?:'No case description available.')?></p>
 </div>
</div>
<?php if(can('edit')): ?>
<div id="editCaseForm" class="section-card" style="display:none;margin-top:12px">
 <div class="section-hdr"><h3>Edit Case</h3><button class="btn btn-primary btn-sm" onclick="saveCase()"><i data-lucide="save"></i> Save Changes</button></div>
 <div id="caseToast" style="display:none;margin-bottom:8px" class="flash flash-success"><i data-lucide="check-circle"></i> Saved!</div>
 <div class="form-grid">
  <div class="form-group"><label>Title</label><input id="c_title" value="<?=htmlspecialchars($case['title']??'')?>"></div>
  <div class="form-group"><label>Status</label><select id="c_status"><?php foreach(['Open','Under Investigation','Closed'] as $s): ?><option <?=$case['case_status']===$s?'selected':''?>><?=$s?></option><?php endforeach; ?></select></div>
  <div class="form-group"><label>Lead Officer</label><select id="c_lead"><option value="">-- None --</option><?php foreach($all_officers as $off): ?><option value="<?=$off['officer_id']?>" <?=$case['lead_officer_id']==$off['officer_id']?'selected':''?>><?=htmlspecialchars($off['first_name'].' '.$off['last_name'])?></option><?php endforeach; ?></select></div>
  <div class="form-group"><label>Open Date</label><input type="date" id="c_open" value="<?=$case['open_date']?>"></div>
  <div class="form-group"><label>Close Date</label><input type="date" id="c_close" value="<?=$case['close_date']??''?>"></div>
  <div class="form-group full"><label>Notes</label><textarea id="c_desc"><?=htmlspecialchars($case['description']??'')?></textarea></div>
 </div>
</div>
<?php endif; ?>
</div>

<!-- CRIMINALS TAB -->
<div class="tab-pane" id="tab-criminals">
<div class="section-card">
 <div class="section-hdr"><h3>Linked Criminals</h3><?php if(can('edit')): ?><button class="btn btn-ghost btn-sm" onclick="openSearch('criminal')"><i data-lucide="plus"></i> Add Criminal</button><?php endif; ?></div>
 <div class="tbl-wrap"><table>
  <thead><tr><th>Avatar</th><th>Name</th><th>Status</th><th>Role</th><th></th></tr></thead>
  <tbody>
  <?php if(empty($criminals)): ?>
  <tr class="empty-row"><td colspan="5">No linked criminals.</td></tr>
  <?php else: foreach($criminals as $r): $b=match($r['status']){'Imprisoned'=>'b-red','Released'=>'b-green','Wanted'=>'b-orange',default=>'b-blue'}; ?>
  <tr id="cc-<?=$r['criminal_id']?>">
   <td><img src="<?=(!empty($r['photo'])&&file_exists($r['photo']))?$r['photo']:'assets/anon.svg'?>" class="tbl-avatar" alt="" onerror="this.src='assets/anon.svg'"></td>
   <td><a href="profile.php?id=<?=$r['criminal_id']?>" style="color:var(--txt);font-weight:600;text-decoration:none;font-family:inherit"><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></a></td>
   <td><span class="badge <?=$b?>"><?=htmlspecialchars($r['status']??'—')?></span></td>
   <td><?=htmlspecialchars($r['cr_role']??'Suspect')?></td>
   <td><?php if(can('edit')): ?><button class="btn btn-danger btn-xs" onclick="removeAffil('criminal',<?=$r['criminal_id']?>)"><i data-lucide="x"></i></button><?php endif; ?></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
 </table></div>
</div>
</div>

<!-- OFFICERS TAB -->
<div class="tab-pane" id="tab-officers">
<div class="section-card">
 <div class="section-hdr"><h3>Assigned Officers</h3><?php if(can('edit')): ?><button class="btn btn-ghost btn-sm" onclick="openSearch('officer')"><i data-lucide="plus"></i> Assign Officer</button><?php endif; ?></div>
 <div class="tbl-wrap"><table>
  <thead><tr><th>Avatar</th><th>Name</th><th>Badge</th><th>Rank</th><th></th></tr></thead>
  <tbody>
  <?php if(empty($officers)): ?>
  <tr class="empty-row"><td colspan="5">No officers assigned.</td></tr>
  <?php else: foreach($officers as $r): ?>
  <tr id="oc-<?=$r['officer_id']?>">
   <td><img src="<?=(!empty($r['photo'])&&file_exists($r['photo']))?$r['photo']:'assets/anon.svg'?>" class="tbl-avatar" alt="" onerror="this.src='assets/anon.svg'"></td>
   <td><a href="officer_profile.php?id=<?=$r['officer_id']?>" style="color:var(--txt);font-weight:600;text-decoration:none;font-family:inherit"><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></a></td>
   <td><span class="badge b-blue"><?=htmlspecialchars($r['badge_number']??'—')?></span></td>
   <td><?=htmlspecialchars($r['rank']??'—')?></td>
   <td><?php if(can('edit')): ?><button class="btn btn-danger btn-xs" onclick="removeAffil('officer',<?=$r['officer_id']?>)"><i data-lucide="x"></i></button><?php endif; ?></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
 </table></div>
</div>
</div>

<!-- VICTIMS TAB -->
<div class="tab-pane" id="tab-victims">
<div class="section-card">
 <div class="section-hdr"><h3>Victims</h3><?php if(can('edit')): ?><button class="btn btn-ghost btn-sm" onclick="toggleForm('addVictimForm')"><i data-lucide="plus"></i> Add Victim</button><?php endif; ?></div>
 <?php if(can('edit')): ?>
 <div id="addVictimForm" style="display:none;margin-bottom:12px;padding:14px;background:var(--bg);border:1px solid var(--border);border-radius:8px">
  <div class="form-grid"><div class="form-group"><label>First Name</label><input id="v_fn"></div><div class="form-group"><label>Last Name</label><input id="v_ln"></div><div class="form-group"><label>Phone</label><input id="v_ph"></div><div class="form-group full"><label>Statement</label><textarea id="v_st"></textarea></div></div>
  <button class="btn btn-primary btn-sm" onclick="addVictim()"><i data-lucide="save"></i> Add</button>
 </div>
 <?php endif; ?>
 <div class="tbl-wrap"><table>
  <thead><tr><th>Name</th><th>Phone</th><th>Statement</th><th></th></tr></thead>
  <tbody>
  <?php if(empty($victims)): ?>
  <tr class="empty-row"><td colspan="4">No victims added.</td></tr>
  <?php else: foreach($victims as $r): ?>
  <tr id="vc-<?=$r['victim_id']?>">
   <td><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></td>
   <td><?=htmlspecialchars($r['phone']??'—')?></td>
   <td><?=htmlspecialchars($r['statement']??'—')?></td>
   <td><?php if(can('edit')): ?><button class="btn btn-danger btn-xs" onclick="removeVictim(<?=$r['victim_id']?>)"><i data-lucide="x"></i></button><?php endif; ?></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
 </table></div>
</div>
</div>

<!-- SUSPECTS TAB -->
<div class="tab-pane" id="tab-suspects">
<div class="section-card">
 <div class="section-hdr"><h3>Suspects</h3><?php if(can('edit')): ?><button class="btn btn-ghost btn-sm" onclick="toggleForm('addSuspectForm')"><i data-lucide="plus"></i> Add Suspect</button><?php endif; ?></div>
 <?php if(can('edit')): ?>
 <div id="addSuspectForm" style="display:none;margin-bottom:12px;padding:14px;background:var(--bg);border:1px solid var(--border);border-radius:8px">
  <div class="form-grid"><div class="form-group"><label>First Name</label><input id="s_fn"></div><div class="form-group"><label>Last Name</label><input id="s_ln"></div><div class="form-group"><label>Phone</label><input id="s_ph"></div><div class="form-group full"><label>Note</label><textarea id="s_nt"></textarea></div></div>
  <button class="btn btn-primary btn-sm" onclick="addSuspect()"><i data-lucide="save"></i> Add</button>
 </div>
 <?php endif; ?>
 <div class="tbl-wrap"><table>
  <thead><tr><th>Name</th><th>Phone</th><th>Note</th><th></th></tr></thead>
  <tbody>
  <?php if(empty($suspects)): ?>
  <tr class="empty-row"><td colspan="4">No suspects added.</td></tr>
  <?php else: foreach($suspects as $r): ?>
  <tr id="sc-<?=$r['suspect_id']?>">
   <td><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></td>
   <td><?=htmlspecialchars($r['phone']??'—')?></td>
   <td><?=htmlspecialchars($r['note']??'—')?></td>
   <td><?php if(can('edit')): ?><button class="btn btn-danger btn-xs" onclick="removeSuspect(<?=$r['suspect_id']?>)"><i data-lucide="x"></i></button><?php endif; ?></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
 </table></div>
</div>
</div>

<!-- HEARINGS TAB -->
<div class="tab-pane" id="tab-hearings">
<div class="section-card">
 <div class="section-hdr"><h3>Court Hearings</h3><?php if(can('edit')): ?><button class="btn btn-ghost btn-sm" onclick="toggleForm('addHearingForm')"><i data-lucide="plus"></i> Schedule Hearing</button><?php endif; ?></div>
 <?php if(can('edit')): ?>
 <div id="addHearingForm" style="display:none;margin-bottom:12px;padding:14px;background:var(--bg);border:1px solid var(--border);border-radius:8px">
  <div class="form-grid"><div class="form-group"><label>Court Name</label><input id="h_court"></div><div class="form-group"><label>Judge Name</label><input id="h_judge"></div><div class="form-group"><label>Hearing Date</label><input type="date" id="h_date"></div><div class="form-group"><label>Verdict</label><select id="h_verd"><option>Pending</option><option>Guilty</option><option>Not Guilty</option></select></div><div class="form-group"><label>Next Date</label><input type="date" id="h_next"></div></div>
  <button class="btn btn-primary btn-sm" onclick="addHearing()"><i data-lucide="save"></i> Schedule</button>
 </div>
 <?php endif; ?>
 <div class="tbl-wrap"><table>
  <thead><tr><th>Court</th><th>Judge</th><th>Date</th><th>Verdict</th><th>Next Date</th><th></th></tr></thead>
  <tbody>
  <?php if(empty($hearings)): ?>
  <tr class="empty-row"><td colspan="6">No hearings scheduled.</td></tr>
  <?php else: foreach($hearings as $r): $vb=match($r['verdict']??''){'Guilty'=>'b-red','Not Guilty'=>'b-green','Pending'=>'b-orange',default=>'b-muted'}; ?>
  <tr id="hc-<?=$r['hearing_id']?>">
   <td><?=htmlspecialchars($r['court_name']??'—')?></td>
   <td><?=htmlspecialchars($r['judge_name']??'—')?></td>
   <td><?=fmt_date($r['hearing_date'])?></td>
   <td><span class="badge <?=$vb?>"><?=htmlspecialchars($r['verdict']??'—')?></span></td>
   <td><?=($r['next_hearing_date']?fmt_date($r['next_hearing_date']):'—')?></td>
   <td><?php if(can('edit')): ?><a href="edit_hearing.php?id=<?=$r['hearing_id']?>" class="btn btn-ghost btn-xs"><i data-lucide="edit-2"></i></a><?php endif; ?></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
 </table></div>
</div>
</div>


</div><!-- end tabGroup -->

<!-- Search Modal -->
<div id="searchModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;align-items:center;justify-content:center">
 <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:24px;width:440px;max-width:90vw">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
   <h3 id="searchModalTitle" style="font-size:15px;font-weight:700">Add</h3>
   <button onclick="closeSearchModal()" class="btn btn-ghost btn-xs"><i data-lucide="x"></i></button>
  </div>
  <input type="text" id="searchModalInput" placeholder="Search..." oninput="doSearch(this.value)" style="margin-bottom:10px">
  <div id="searchResults" style="max-height:280px;overflow-y:auto"></div>
 </div>
</div>

<style>
.affil-chip{display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;margin-bottom:6px;}
.affil-chip:hover{border-color:#3a4048;}
</style>

<script>
var CASE_ID=<?=$id?>;
var searchType='';

function toast(msg,ok=true){
 var t=document.createElement('div');
 t.className='flash flash-'+(ok?'success':'error');
 t.style.cssText='position:fixed;top:70px;right:16px;z-index:9999;min-width:260px;animation:fadeUp .2s ease';
 t.innerHTML='<i data-lucide="'+(ok?'check-circle':'alert-circle')+'"></i> '+msg;
 document.body.appendChild(t);
 if(typeof lucide!=='undefined') lucide.createIcons();
 setTimeout(()=>t.remove(),3500);
}

function post(url,data){
 var fd=new FormData();
 Object.entries(data).forEach(([k,v])=>fd.append(k,v));
 return fetch(url,{method:'POST',body:fd}).then(r=>r.json());
}

function toggleForm(id){var el=document.getElementById(id);el.style.display=el.style.display==='none'?'block':'none';}

function saveCase(){
 post('ajax/case_affil.php',{action:'update_case',case_id:CASE_ID,title:document.getElementById('c_title').value,case_status:document.getElementById('c_status').value,lead_officer_id:document.getElementById('c_lead').value,open_date:document.getElementById('c_open').value,close_date:document.getElementById('c_close').value,description:document.getElementById('c_desc').value})
 .then(r=>toast(r.ok?'Case saved!':r.msg,r.ok));
}

function removeAffil(type,eid){
 if(!confirm('Remove this '+(type==='criminal'?'criminal':'officer')+'?')) return;
 post('ajax/case_affil.php',{action:'remove_'+type,case_id:CASE_ID,[type+'_id']:eid})
 .then(r=>{if(r.ok){var el=document.getElementById((type==='criminal'?'cc':'oc')+'-'+eid);if(el)el.remove();toast('Removed');}else toast(r.msg,false);});
}

function addVictim(){
 post('ajax/case_affil.php',{action:'add_victim',case_id:CASE_ID,first_name:document.getElementById('v_fn').value,last_name:document.getElementById('v_ln').value,phone:document.getElementById('v_ph').value,statement:document.getElementById('v_st').value})
 .then(r=>{if(r.ok){var d=r.data;var div=document.createElement('div');div.className='affil-chip';div.id='vc-'+d.victim_id;div.innerHTML='<span style="color:var(--txt)"><strong>'+d.first_name+' '+d.last_name+'</strong></span><button class="btn btn-danger btn-xs" onclick="removeVictim('+d.victim_id+')"><i data-lucide="x"></i></button>';document.getElementById('victims-list').appendChild(div);lucide.createIcons();toast('Victim added');}else toast(r.msg,false);});
}

function removeVictim(vid){
 if(!confirm('Remove victim?')) return;
 post('ajax/case_affil.php',{action:'remove_victim',case_id:CASE_ID,victim_id:vid})
 .then(r=>{if(r.ok){document.getElementById('vc-'+vid)?.remove();toast('Removed');}});
}

function addSuspect(){
 post('ajax/case_affil.php',{action:'add_suspect',case_id:CASE_ID,first_name:document.getElementById('s_fn').value,last_name:document.getElementById('s_ln').value,phone:document.getElementById('s_ph').value,note:document.getElementById('s_nt').value})
 .then(r=>{if(r.ok){var d=r.data;var div=document.createElement('div');div.className='affil-chip';div.id='sc-'+d.suspect_id;div.innerHTML='<strong style="color:var(--txt)">'+d.first_name+' '+d.last_name+'</strong><button class="btn btn-danger btn-xs" onclick="removeSuspect('+d.suspect_id+')"><i data-lucide="x"></i></button>';document.getElementById('suspects-list').appendChild(div);lucide.createIcons();toast('Suspect added');}else toast(r.msg,false);});
}

function removeSuspect(sid){
 if(!confirm('Remove suspect?')) return;
 post('ajax/case_affil.php',{action:'remove_suspect',case_id:CASE_ID,suspect_id:sid})
 .then(r=>{if(r.ok){document.getElementById('sc-'+sid)?.remove();toast('Removed');}});
}

function addHearing(){
 post('ajax/case_affil.php',{action:'add_hearing',case_id:CASE_ID,court_name:document.getElementById('h_court').value,judge_name:document.getElementById('h_judge').value,hearing_date:document.getElementById('h_date').value,verdict:document.getElementById('h_verd').value,next_hearing_date:document.getElementById('h_next').value})
 .then(r=>{if(r.ok){location.reload();}else toast(r.msg,false);});
}



function openSearch(type){
 searchType=type;
 document.getElementById('searchModalTitle').textContent='Add '+(type==='criminal'?'Criminal':'Officer');
 document.getElementById('searchModalInput').value='';
 document.getElementById('searchResults').innerHTML='';
 document.getElementById('searchModal').style.display='flex';
 doSearch('');
}

function closeSearchModal(){document.getElementById('searchModal').style.display='none';}

function doSearch(q){
 fetch('ajax/case_affil.php?action=search_'+searchType+'s&case_id='+CASE_ID+'&q='+encodeURIComponent(q))
 .then(r=>r.json()).then(r=>{
  var el=document.getElementById('searchResults');
  if(!r.data||!r.data.length){el.innerHTML='<div style="padding:20px;text-align:center;color:var(--txt-soft)">No results</div>';return;}
  el.innerHTML=r.data.map(x=>'<div onclick="selectResult('+x[searchType==="criminal"?"criminal_id":"officer_id"]+')" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;cursor:pointer;transition:.15s" onmouseover="this.style.background=\'var(--surface-2)\'" onmouseout="this.style.background=\'\'"><strong style="color:var(--txt)">'+(x.first_name||'')+' '+(x.last_name||'')+'</strong><span class="badge b-muted" style="margin-left:auto">'+(searchType==="criminal"?x.status:x.badge_number||'')+'</span></div>').join('');
 });
}

function selectResult(eid){
 post('ajax/case_affil.php',{action:'add_'+searchType,case_id:CASE_ID,[searchType+'_id']:eid})
 .then(r=>{if(r.ok){closeSearchModal();location.reload();}else toast(r.msg,false);});
}

document.addEventListener('DOMContentLoaded',function(){
 initTabs('tabGroup');
 var hash=window.location.hash.replace('#tab-','');
 if(hash) switchTab(hash,'tabGroup'); else switchTab('overview','tabGroup');
});
</script>
<?php include 'footer.php'; ?>
