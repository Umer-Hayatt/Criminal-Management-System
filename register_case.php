<?php
include 'db.php';
$pageTitle='New Case';
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 $crime_id=intval($_POST['crime_id']);
 $status=esc($conn,$_POST['case_status']);
 $open=esc($conn,$_POST['open_date']);
 $desc=esc($conn,$_POST['description']);
 $title=esc($conn,$_POST['title']??'');
 $lo=intval($_POST['lead_officer_id']??0) ?: 'NULL';
 if(!$crime_id) $errors[]='Please select a crime.';
 if(empty($errors)){
  mysqli_query($conn,"INSERT INTO Case_Record(crime_id,case_status,open_date,description,title,lead_officer_id) VALUES($crime_id,'$status','$open','$desc','$title',$lo)");
  $case_id=mysqli_insert_id($conn);
  // Officers chips
  if(!empty($_POST['officers'])) foreach($_POST['officers'] as $oid){$oid=intval($oid);$r=esc($conn,$_POST['officer_roles'][$oid]??'Investigator');mysqli_query($conn,"INSERT IGNORE INTO case_officers(case_id,officer_id,role,assigned_date) VALUES($case_id,$oid,'$r','$open')");}
  // Criminals chips
  if(!empty($_POST['criminals'])) foreach($_POST['criminals'] as $crid){$crid=intval($crid);mysqli_query($conn,"INSERT IGNORE INTO case_criminals(case_id,criminal_id,role) VALUES($case_id,$crid,'Suspect')");}
  log_activity('Created','Case',$case_id,"Opened case for crime #$crime_id");
  set_flash('success',"Case #$case_id opened successfully.");
  header("Location: case_profile.php?id=$case_id"); exit();
 }
}
$crimes=mysqli_query($conn,"SELECT * FROM Crime ORDER BY date_occurred DESC");
$officers=mysqli_query($conn,"SELECT * FROM Officer ORDER BY first_name");
$criminals=mysqli_query($conn,"SELECT * FROM Criminal ORDER BY first_name");
include 'header.php';
?>
<div class="breadcrumb"><a href="cases.php">Cases</a><i data-lucide="chevron-right"></i><span>New Case</span></div>
<div class="page-hdr">
 <div class="page-hdr-left"><div class="page-hdr-icon"><i data-lucide="folder-plus"></i></div><div><h2>New Case</h2><p>Open a new investigation case</p></div></div>
 <a href="cases.php" class="btn btn-ghost"><i data-lucide="arrow-left"></i> Back</a>
</div>
<?php foreach($errors as $e): ?><div class="flash flash-error"><i data-lucide="alert-circle"></i><?=htmlspecialchars($e)?></div><?php endforeach; ?>
<form method="POST" id="caseForm">
<div class="form-section">
 <div class="form-section-title"><i data-lucide="folder-open"></i> Case Details</div>
 <div class="form-grid">
  <div class="form-group"><label>Case Title</label><input type="text" name="title" placeholder="e.g. Operation Thunder" value="<?=htmlspecialchars($_POST['title']??'')?>"></div>
  <div class="form-group"><label>Crime *</label>
   <select name="crime_id" required>
    <option value="">-- Select Crime --</option>
    <?php while($c=mysqli_fetch_assoc($crimes)): ?><option value="<?=$c['crime_id']?>"><?=$c['crime_type']?> — <?=htmlspecialchars(substr($c['description']??$c['location']??'',0,40))?></option><?php endwhile; ?>
   </select>
  </div>
  <div class="form-group"><label>Case Status</label><select name="case_status"><option>Open</option><option>Under Investigation</option><option>Closed</option></select></div>
  <div class="form-group"><label>Open Date</label><input type="date" name="open_date" value="<?=date('Y-m-d')?>"></div>
  <div class="form-group"><label>Lead Officer</label>
   <select name="lead_officer_id"><option value="">-- None --</option>
    <?php mysqli_data_seek($officers,0); while($o=mysqli_fetch_assoc($officers)): ?><option value="<?=$o['officer_id']?>"><?=htmlspecialchars($o['first_name'].' '.$o['last_name'])?> — <?=$o['rank']?></option><?php endwhile; ?>
   </select>
  </div>
  <div class="form-group full"><label>Description / Notes</label><textarea name="description" placeholder="Investigation notes..."><?=htmlspecialchars($_POST['description']??'')?></textarea></div>
 </div>
</div>

<div class="form-section">
 <div class="form-section-title"><i data-lucide="shield"></i> Assign Officers</div>
 <div class="chip-search-wrap">
  <input type="text" id="officer_search" placeholder="Search officers by name or badge..." oninput="searchEntities('officer',this.value)">
  <div id="officer_dropdown" class="chip-dropdown" style="display:none"></div>
  <div id="officer_chips" class="chip-container"></div>
 </div>
</div>

<div class="form-section">
 <div class="form-section-title"><i data-lucide="users"></i> Link Criminals</div>
 <div class="chip-search-wrap">
  <input type="text" id="criminal_search" placeholder="Search criminals by name..." oninput="searchEntities('criminal',this.value)">
  <div id="criminal_dropdown" class="chip-dropdown" style="display:none"></div>
  <div id="criminal_chips" class="chip-container"></div>
 </div>
</div>

<div style="display:flex;gap:10px;margin-top:8px">
 <button type="submit" class="btn btn-primary" style="padding:11px 28px"><i data-lucide="save"></i> Open Case</button>
 <a href="cases.php" class="btn btn-ghost">Cancel</a>
</div>
</form>

<style>
.chip-search-wrap{position:relative;}
.chip-dropdown{position:absolute;top:42px;left:0;right:0;background:var(--surface);border:1px solid var(--border);border-radius:8px;z-index:100;max-height:200px;overflow-y:auto;box-shadow:var(--shadow);}
.chip-dropdown-item{padding:10px 14px;cursor:pointer;font-size:13px;display:flex;align-items:center;gap:8px;transition:var(--trans);}
.chip-dropdown-item:hover{background:var(--surface-2);}
.chip-container{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;min-height:32px;}
.chip{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:var(--accent-lt);border:1px solid var(--accent);border-radius:20px;font-size:12px;font-weight:600;color:var(--accent);}
.chip button{background:none;border:none;cursor:pointer;color:var(--accent);padding:0;line-height:1;font-size:14px;}
</style>
<script>
var officerData={}, criminalData={};
function searchEntities(type,q){
 if(q.length<1){document.getElementById(type+'_dropdown').style.display='none';return;}
 fetch('ajax/case_affil.php?action=search_'+type+'s&case_id=0&q='+encodeURIComponent(q))
 .then(r=>r.json()).then(r=>{
  var dd=document.getElementById(type+'_dropdown');
  if(!r.data||!r.data.length){dd.innerHTML='<div class="chip-dropdown-item" style="color:var(--txt-soft)">No results</div>';dd.style.display='block';return;}
  dd.innerHTML=r.data.map(x=>`<div class="chip-dropdown-item" onclick="addChip('${type}',${x[type==='officer'?'officer_id':'criminal_id']},'${(x.first_name+' '+x.last_name).replace(/'/g,"\\'")}','${x.badge_number||x.status||''}')"><strong>${x.first_name} ${x.last_name}</strong><span class="badge b-muted" style="margin-left:auto">${type==='officer'?x.badge_number||'':x.status||''}</span></div>`).join('');
  dd.style.display='block';
 });
}
function addChip(type,id,name,extra){
 if(document.getElementById(type+'-chip-'+id)) return;
 document.getElementById(type+'_dropdown').style.display='none';
 document.getElementById(type+'_search').value='';
 var container=document.getElementById(type+'_chips');
 var chip=document.createElement('div');
 chip.className='chip'; chip.id=type+'-chip-'+id;
 chip.innerHTML=`<span>${name}</span><input type="hidden" name="${type}s[]" value="${id}"><button type="button" onclick="this.parentElement.remove()">×</button>`;
 container.appendChild(chip);
}
document.addEventListener('click',function(e){
 if(!e.target.closest('.chip-search-wrap')){document.querySelectorAll('.chip-dropdown').forEach(d=>d.style.display='none');}
});
</script>
<?php include 'footer.php'; ?>
