<?php
include 'db.php';
$id=intval($_GET['id']);
if(!$id){header("Location: records_officers.php");exit();}
$officer=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Officer WHERE officer_id=$id"));
if(!$officer){set_flash('error','Officer not found.');header("Location: records_officers.php");exit();}
$cases_q=mysqli_query($conn,"SELECT co.*,c.case_status,cr.crime_type,c.open_date,c.title FROM case_officers co JOIN Case_Record c ON co.case_id=c.case_id JOIN Crime cr ON c.crime_id=cr.crime_id WHERE co.officer_id=$id ORDER BY c.open_date DESC");
$hearings_q=mysqli_query($conn,"SELECT h.*,c.case_id FROM Court_Hearing h JOIN hearing_officers ho ON h.hearing_id=ho.hearing_id JOIN Case_Record c ON h.case_id=c.case_id WHERE ho.officer_id=$id ORDER BY h.hearing_date DESC");
$pageTitle=$officer['first_name'].' '.$officer['last_name'].' — Officer Profile';
include 'header.php';
$photo=$officer['photo']??null;
?>
<div class="breadcrumb"><a href="records_officers.php">Officers</a><i data-lucide="chevron-right"></i><span><?=htmlspecialchars($officer['first_name'].' '.$officer['last_name'])?></span></div>
<div class="profile-card">
 <div style="display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap">
  <?=avatar_html($officer['first_name'],$officer['last_name'],$photo,80)?>
  <div style="flex:1;min-width:200px">
   <div style="font-size:11px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:1px">Officer #<?=str_pad($id,4,'0',STR_PAD_LEFT)?></div>
   <h2 style="font-size:22px;font-weight:800;margin:4px 0 8px"><?=htmlspecialchars($officer['first_name'].' '.$officer['last_name'])?></h2>
   <div style="display:flex;gap:8px;flex-wrap:wrap">
    <span class="badge b-blue"><?=htmlspecialchars($officer['badge_number']??'—')?></span>
    <span class="badge b-purple"><?=htmlspecialchars($officer['rank']??'—')?></span>
   </div>
   <div class="profile-meta" style="margin-top:14px">
    <div class="meta-item"><label>Department</label><span><?=htmlspecialchars($officer['department']??'—')?></span></div>
    <div class="meta-item"><label>Phone</label><span><?=htmlspecialchars($officer['phone']??'—')?></span></div>
   </div>
  </div>
 </div>
 <?php if(can('edit')||can('delete')): ?>
 <div style="position:absolute;bottom:16px;right:16px;display:flex;gap:8px">
  <?php if(can('edit')): ?><a href="edit_officer.php?id=<?=$id?>" class="btn btn-ghost btn-sm"><i data-lucide="edit-2"></i> Edit</a><?php endif; ?>
  <?php if(can('delete')): ?><button onclick="confirmDelete('delete_officer.php?id=<?=$id?>','Remove Officer?','Remove <?=htmlspecialchars(addslashes($officer['first_name'].' '.$officer['last_name']))?> from the system?')" class="btn btn-danger btn-sm"><i data-lucide="trash-2"></i> Remove</button><?php endif; ?>
 </div>
 <?php endif; ?>
</div>

<div id="tabGroup">
<div class="tab-bar">
 <button class="tab-btn" data-tab="cases"    onclick="switchTab('cases','tabGroup')"><i data-lucide="folder-open"></i> Cases</button>
 <button class="tab-btn" data-tab="hearings" onclick="switchTab('hearings','tabGroup')"><i data-lucide="scale"></i> Hearings</button>
</div>

<div class="tab-pane" id="tab-cases">
<div class="section-card">
 <div class="section-hdr"><h3>Assigned Cases</h3></div>
 <?php $rows=[]; while($r=mysqli_fetch_assoc($cases_q)) $rows[]=$r; ?>
 <?php if(empty($rows)): ?><div class="no-data"><i data-lucide="folder-open"></i>No cases assigned.</div>
 <?php else: ?><div class="tbl-wrap"><table>
  <thead><tr><th>Case #</th><th>Title / Crime</th><th>Status</th><th>Role</th><th>Opened</th><th></th></tr></thead>
  <tbody>
  <?php foreach($rows as $r):
   $cs=match($r['case_status']){'Open'=>'b-orange','Closed'=>'b-green','Under Investigation'=>'b-blue',default=>'b-muted'};
  ?>
  <tr>
   <td><strong>Case #<?=$r['case_id']?></strong></td>
   <td><?=htmlspecialchars($r['title']?:$r['crime_type'])?></td>
   <td><span class="badge <?=$cs?>"><?=$r['case_status']?></span></td>
   <td><span class="badge b-purple"><?=htmlspecialchars($r['role']??'Investigator')?></span></td>
   <td><?=fmt_date($r['open_date'])?></td>
   <td><a href="case_profile.php?id=<?=$r['case_id']?>" class="btn btn-ghost btn-xs"><i data-lucide="eye"></i></a></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
 </table></div><?php endif; ?>
</div>
</div>

<div class="tab-pane" id="tab-hearings">
<div class="section-card">
 <div class="section-hdr"><h3>Court Hearings</h3></div>
 <?php $hrows=[]; while($r=mysqli_fetch_assoc($hearings_q)) $hrows[]=$r; ?>
 <?php if(empty($hrows)): ?><div class="no-data"><i data-lucide="scale"></i>No hearings.</div>
 <?php else: ?><div class="tbl-wrap"><table>
  <thead><tr><th>Court</th><th>Case</th><th>Date</th><th>Verdict</th><th>Next</th></tr></thead>
  <tbody>
  <?php foreach($hrows as $r):
   $vb=match($r['verdict']??''){'Guilty'=>'b-red','Not Guilty'=>'b-green','Pending'=>'b-orange',default=>'b-muted'};
  ?>
  <tr>
   <td><strong><?=htmlspecialchars($r['court_name']??'—')?></strong><br><small style="color:var(--txt-mid)"><?=htmlspecialchars($r['judge_name']??'')?></small></td>
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

<script>document.addEventListener('DOMContentLoaded',()=>{initTabs('tabGroup');switchTab('cases','tabGroup');});</script>
<?php include 'footer.php'; ?>
