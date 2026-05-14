<?php
include 'db.php';
$pageTitle='Search';
include 'header.php';
?>
<div class="page-hdr">
 <div class="page-hdr-left"><div class="page-hdr-icon"><i data-lucide="search"></i></div><div><h2>Search</h2><p>Find criminals, cases, officers and hearings</p></div></div>
</div>
<div class="card" style="margin-bottom:14px;padding:18px 20px">
 <div class="search-bar-row">
  <div class="search-main">
   <label>Search</label>
   <input type="text" id="sq" placeholder="Search criminals, officers, cases, hearings..." onkeydown="if(event.key==='Enter')runSearch()">
  </div>
  <div class="search-actions">
   <button class="btn btn-primary btn-sm" onclick="runSearch()" style="padding:10px 20px"><i data-lucide="search"></i> Search</button>
   <button class="btn btn-ghost btn-sm" onclick="clearFilters()"><i data-lucide="x"></i> Clear</button>
  </div>
 </div>
 <div class="search-filters">
  <div class="form-group"><label>Type</label>
   <select id="stype">
    <option value="all">All Types</option>
    <option value="criminal">Criminals</option>
    <option value="officer">Officers</option>
    <option value="case">Cases</option>
    <option value="hearing">Hearings</option>
   </select>
  </div>
  <div class="form-group"><label>Status</label><input type="text" id="sstatus" placeholder="Wanted / Open / Pending"></div>
  <div class="form-group"><label>From</label><input type="date" id="sfrom"></div>
  <div class="form-group"><label>To</label><input type="date" id="sto"></div>
 </div>
</div>
<div id="searchResults"><div class="no-data" style="padding:40px;text-align:center"><i data-lucide="search" style="width:32px;height:32px;margin:0 auto 10px;display:block;opacity:.3"></i>Enter a query and click Search</div></div>

<style>
.search-bar-row{display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap;margin-bottom:16px;}
.search-main{flex:2;min-width:240px;}
.search-main label{display:block;margin-bottom:6px;color:var(--txt-soft);}
.search-main input{width:100%;padding:12px 14px;font-size:16px;border:1px solid var(--border);border-radius:10px;background:var(--surface);}
.search-actions{display:flex;align-items:center;gap:10px;}
.search-filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;}
.result-row{display:flex;align-items:center;gap:12px;padding:14px 16px;background:var(--surface);border:1px solid var(--border);border-radius:12px;margin-bottom:10px;text-decoration:none;transition:var(--trans);}
.result-row:hover{border-color:var(--accent);background:var(--surface-2);}
.et-badge{font-size:10px;font-weight:700;padding:4px 8px;border-radius:999px;text-transform:uppercase;letter-spacing:.6px;white-space:nowrap;}
.et-criminal{background:rgba(248,81,73,.12);color:var(--danger);}
.et-officer{background:rgba(56,189,248,.12);color:var(--accent);}
.et-case{background:rgba(245,158,11,.12);color:var(--warning);}
.et-hearing{background:rgba(124,58,237,.12);color:var(--violet);}
</style>

<script>
function runSearch(){
 var q=document.getElementById('sq').value;
 var params=new URLSearchParams({q:q,type:document.getElementById('stype').value,status:document.getElementById('sstatus').value,from:document.getElementById('sfrom').value,to:document.getElementById('sto').value});
 fetch('ajax/search.php?'+params).then(r=>r.json()).then(renderResults);
}
function renderResults(r){
 var el=document.getElementById('searchResults');
 if(!r.results||!r.results.length){el.innerHTML='<div class="no-data" style="padding:40px;text-align:center"><i data-lucide="search" style="width:24px;height:24px;margin:0 auto 8px;display:block;opacity:.3"></i>No results found.</div>';if(typeof lucide!=='undefined')lucide.createIcons();return;}
 var urls={criminal:'profile.php?id=',officer:'officer_profile.php?id=',case:'case_profile.php?id=',hearing:'edit_hearing.php?id='};
 el.innerHTML=r.results.map(x=>{
  var url=(urls[x.entity_type]||'#')+x.id;
  var photo = x.photo && x.photo !== '' ? x.photo : 'assets/anon.svg';
  var avatar = `<img src="${photo}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;border:1px solid var(--border)" onerror="this.src='assets/anon.svg'">`;
  var statusBadge=x.status?`<span class="badge b-muted">${x.status}</span>`:'';
  return `<a href="${url}" class="result-row"><span class="et-badge et-${x.entity_type}">${x.entity_type}</span>${avatar}<div style="flex:1"><strong style="color:var(--txt)">${x.name}</strong>${x.extra?`<br><span style="font-size:11px;color:var(--txt-mid)">${x.extra}</span>`:''}</div>${statusBadge}<i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--txt-soft)"></i></a>`;
 }).join('');
 if(typeof lucide!=='undefined') lucide.createIcons();
}
function clearFilters(){['sq','sstatus','sfrom','sto'].forEach(id=>document.getElementById(id).value='');document.getElementById('stype').value='all';document.getElementById('searchResults').innerHTML='<div class="no-data" style="padding:40px;text-align:center">Enter a query and click Search</div>';}
</script>
<?php include 'footer.php'; ?>
