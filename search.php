<?php
include 'db.php';
$pageTitle='Search';
include 'header.php';
$cases=mysqli_query($conn,"SELECT case_id,CONCAT('Case #',case_id,IFNULL(CONCAT(' — ',title),'')) AS label FROM Case_Record ORDER BY case_id DESC");
?>
<div class="page-hdr">
 <div class="page-hdr-left"><div class="page-hdr-icon"><i data-lucide="search"></i></div><div><h2>Search</h2><p>Find criminals, cases, officers, hearings and warrants</p></div></div>
</div>
<div class="card" style="margin-bottom:14px">
 <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
  <div class="form-group" style="flex:2;min-width:200px"><label>Search</label><input type="text" id="sq" placeholder="Name, case number, crime type, badge..." oninput="doSearch()"></div>
  <div class="form-group" style="flex:1;min-width:130px"><label>Type</label>
   <select id="stype" onchange="doSearch()">
    <option value="all">All Types</option>
    <option value="criminal">Criminals</option>
    <option value="officer">Officers</option>
    <option value="case">Cases</option>
    <option value="hearing">Hearings</option>
    <option value="warrant">Warrants</option>
   </select>
  </div>
  <div class="form-group" style="flex:1;min-width:130px"><label>Status</label><input type="text" id="sstatus" placeholder="e.g. Wanted / Open" oninput="doSearch()"></div>
  <div class="form-group" style="min-width:130px"><label>From Date</label><input type="date" id="sfrom" onchange="doSearch()"></div>
  <div class="form-group" style="min-width:130px"><label>To Date</label><input type="date" id="sto" onchange="doSearch()"></div>
  <button class="btn btn-ghost btn-sm" onclick="clearFilters()"><i data-lucide="x"></i> Clear</button>
 </div>
</div>
<div id="searchResults"><div class="no-data" style="padding:40px;text-align:center"><i data-lucide="search" style="width:32px;height:32px;margin:0 auto 10px;display:block;opacity:.3"></i>Start typing to search...</div></div>

<style>
.result-row{display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--surface);border:1px solid var(--border);border-radius:8px;margin-bottom:6px;text-decoration:none;transition:var(--trans);}
.result-row:hover{border-color:var(--accent);background:var(--accent-lt);}
.et-badge{font-size:9px;font-weight:800;padding:2px 7px;border-radius:4px;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;}
.et-criminal{background:rgba(248,81,73,.15);color:var(--danger);}
.et-officer{background:var(--accent-lt);color:var(--accent);}
.et-case{background:rgba(210,153,34,.15);color:var(--warning);}
.et-hearing{background:var(--violet-lt);color:var(--violet);}
.et-warrant{background:rgba(63,185,80,.15);color:var(--success);}
</style>

<script>
var searchTimer=null;
function doSearch(){
 clearTimeout(searchTimer);
 searchTimer=setTimeout(runSearch,250);
}
function runSearch(){
 var q=document.getElementById('sq').value;
 if(!q.trim()){document.getElementById('searchResults').innerHTML='<div class="no-data" style="padding:40px;text-align:center">Start typing to search...</div>';return;}
 var params=new URLSearchParams({q:q,type:document.getElementById('stype').value,status:document.getElementById('sstatus').value,from:document.getElementById('sfrom').value,to:document.getElementById('sto').value});
 fetch('ajax/search.php?'+params).then(r=>r.json()).then(renderResults);
}
function renderResults(r){
 var el=document.getElementById('searchResults');
 if(!r.results||!r.results.length){el.innerHTML='<div class="no-data" style="padding:40px;text-align:center"><i data-lucide="search" style="width:24px;height:24px;margin:0 auto 8px;display:block;opacity:.3"></i>No results found.</div>';if(typeof lucide!=='undefined')lucide.createIcons();return;}
 var urls={criminal:'profile.php?id=',officer:'officer_profile.php?id=',case:'case_profile.php?id=',hearing:'edit_hearing.php?id=',warrant:'warrants.php?'};
 el.innerHTML=r.results.map(x=>{
  var url=(urls[x.entity_type]||'#')+x.id;
  var avatar=x.photo?`<img src="${x.photo}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0" onerror="this.style.display='none'">`:`<span style="display:inline-flex;width:40px;height:40px;border-radius:50%;background:var(--surface-2);color:var(--txt-mid);font-weight:700;font-size:15px;align-items:center;justify-content:center;flex-shrink:0">${(x.name||'??').split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase()}</span>`;
  var statusBadge=x.status?`<span class="badge b-muted">${x.status}</span>`:'';
  return `<a href="${url}" class="result-row"><span class="et-badge et-${x.entity_type}">${x.entity_type}</span>${avatar}<div style="flex:1"><strong style="color:var(--txt)">${x.name}</strong>${x.extra?`<br><span style="font-size:11px;color:var(--txt-mid)">${x.extra}</span>`:''}</div>${statusBadge}<i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--txt-soft)"></i></a>`;
 }).join('');
 if(typeof lucide!=='undefined') lucide.createIcons();
}
function clearFilters(){['sq','sstatus','sfrom','sto'].forEach(id=>document.getElementById(id).value='');document.getElementById('stype').value='all';document.getElementById('searchResults').innerHTML='<div class="no-data" style="padding:40px;text-align:center">Start typing to search...</div>';}
</script>
<?php include 'footer.php'; ?>
