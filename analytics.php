<?php
include 'db.php';
if(!can('admin')){set_flash('error','Admin access required.');header("Location: index.php");exit();}
$pageTitle='Analytics';

$total_c=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal"))[0];
$total_cs=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Case_Record"))[0];
$total_o=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Officer"))[0];
$total_p=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Prison"))[0];
$active_w=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Warrant WHERE status='Active'"))[0];
$pending_h=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Court_Hearing WHERE verdict='Pending'"))[0];

$crime_types=[]; $crime_counts=[];
$r1=mysqli_query($conn,"SELECT crime_type,COUNT(*) cnt FROM Crime GROUP BY crime_type ORDER BY cnt DESC");
while($r=mysqli_fetch_assoc($r1)){$crime_types[]=$r['crime_type'];$crime_counts[]=(int)$r['cnt'];}

$case_labels=[]; $case_counts=[];
$r2=mysqli_query($conn,"SELECT case_status,COUNT(*) cnt FROM Case_Record GROUP BY case_status");
while($r=mysqli_fetch_assoc($r2)){$case_labels[]=$r['case_status'];$case_counts[]=(int)$r['cnt'];}

$months=[]; $reg_counts=[];
for($i=11;$i>=0;$i--){
 $ym=date('Y-m',strtotime("-$i months"));
 $months[]=date('M Y',strtotime("-$i months"));
 $cnt=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Criminal WHERE DATE_FORMAT(criminal_id,'%Y-%m')='$ym'"))[0];
 $reg_counts[]=(int)$cnt;
}

$off_names=[]; $off_counts=[];
$r3=mysqli_query($conn,"SELECT CONCAT(o.first_name,' ',o.last_name) AS name,COUNT(oc.case_id) cnt FROM Officer o LEFT JOIN Officer_Case oc ON o.officer_id=oc.officer_id GROUP BY o.officer_id ORDER BY cnt DESC LIMIT 10");
while($r=mysqli_fetch_assoc($r3)){$off_names[]=$r['name'];$off_counts[]=(int)$r['cnt'];}

$prison_names=[]; $prison_cap=[]; $prison_occ=[];
$r4=mysqli_query($conn,"SELECT p.*,COUNT(i.imprisonment_id) AS occ FROM Prison p LEFT JOIN Imprisonment i ON p.prison_id=i.prison_id AND (i.end_date IS NULL OR i.end_date>=CURDATE()) GROUP BY p.prison_id");
while($r=mysqli_fetch_assoc($r4)){$prison_names[]=$r['prison_name'];$prison_cap[]=(int)$r['capacity'];$prison_occ[]=(int)$r['occ'];}

include 'header.php';
?>
<div class="page-hdr">
 <div class="page-hdr-left"><div class="page-hdr-icon"><i data-lucide="bar-chart-2"></i></div><div><h2>Analytics</h2><p>System-wide statistics and trends</p></div></div>
</div>

<!-- KPI Summary -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
 <?php $kpis=[['Criminals',$total_c,'users','kpi-blue'],['Cases',$total_cs,'folder-open','kpi-amber'],['Officers',$total_o,'shield','kpi-green'],['Prisons',$total_p,'building-2','kpi-violet'],['Active Warrants',$active_w,'file-warning','kpi-red'],['Pending Hearings',$pending_h,'scale','kpi-violet']];
 foreach($kpis as [$lbl,$val,$icon,$cls]): ?>
 <div class="kpi-card">
  <div class="kpi-inner">
   <div class="kpi-icon <?=$cls?>"><i data-lucide="<?=$icon?>"></i></div>
   <div><div class="kpi-val"><?=$val?></div><div class="kpi-lbl"><?=$lbl?></div></div>
  </div>
 </div>
 <?php endforeach; ?>
</div>

<div class="dash-two-col">
 <div class="card"><div class="card-title"><i data-lucide="pie-chart"></i> Crimes by Type</div>
  <div class="chart-container"><canvas id="pieChart"></canvas></div>
  <div class="legend-list" id="pieLegend2"></div>
 </div>
 <div class="card"><div class="card-title"><i data-lucide="pie-chart"></i> Case Status</div>
  <div class="chart-container"><canvas id="caseChart"></canvas></div>
  <div class="legend-list" id="caseLegend"></div>
 </div>
</div>
<div class="dash-two-col">
 <div class="card"><div class="card-title"><i data-lucide="trending-up"></i> Monthly Criminal Registrations (12 months)</div>
  <div class="chart-container"><canvas id="lineChart"></canvas></div>
 </div>
 <div class="card"><div class="card-title"><i data-lucide="bar-chart-2"></i> Officer Caseload (Top 10)</div>
  <div class="chart-container"><canvas id="offChart"></canvas></div>
 </div>
</div>
<div class="card"><div class="card-title"><i data-lucide="building-2"></i> Prison Occupancy</div>
 <div class="chart-container" style="height:200px"><canvas id="prisonChart"></canvas></div>
</div>

<script>
var pal=['#2f81f7','#3fb950','#d29922','#f85149','#8b5cf6','#ec4899'];
Chart.defaults.color='#8b949e';
if(typeof ChartDataLabels!=='undefined') Chart.register(ChartDataLabels);

function mkPie(id,labels,data,legId){
 var tot=data.reduce((a,b)=>a+b,0);
 var c=new Chart(document.getElementById(id),{type:'pie',data:{labels:labels,datasets:[{data:data,backgroundColor:pal,borderWidth:2,borderColor:'#161b22'}]},
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},datalabels:{color:'#fff',font:{weight:'bold',size:11},formatter:(v)=>tot>0&&v/tot>0.05?Math.round(v/tot*100)+'%':''}}}});
 if(legId){var leg=document.getElementById(legId);labels.forEach((l,i)=>{var d=document.createElement('div');d.className='legend-item';d.innerHTML='<span class="legend-dot" style="background:'+pal[i]+'"></span>'+l+' <strong style="color:var(--txt)">'+data[i]+'</strong>';leg.appendChild(d);});}
}

mkPie('pieChart',<?=json_encode($crime_types)?>,<?=json_encode($crime_counts)?>,'pieLegend2');
mkPie('caseChart',<?=json_encode($case_labels)?>,<?=json_encode($case_counts)?>,'caseLegend');

new Chart(document.getElementById('lineChart'),{type:'line',data:{labels:<?=json_encode($months)?>,datasets:[{label:'Registrations',data:<?=json_encode($reg_counts)?>,borderColor:'#2f81f7',backgroundColor:'rgba(47,129,247,.1)',fill:true,tension:.4,pointRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},datalabels:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'rgba(255,255,255,.05)'}},x:{grid:{display:false}}}}});

new Chart(document.getElementById('offChart'),{type:'bar',data:{labels:<?=json_encode($off_names)?>,datasets:[{label:'Cases',data:<?=json_encode($off_counts)?>,backgroundColor:'#2f81f7',borderRadius:6}]},options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},datalabels:{display:false}},scales:{x:{beginAtZero:true,grid:{color:'rgba(255,255,255,.05)'}},y:{grid:{display:false}}}}});

new Chart(document.getElementById('prisonChart'),{type:'bar',data:{labels:<?=json_encode($prison_names)?>,datasets:[{label:'Capacity',data:<?=json_encode($prison_cap)?>,backgroundColor:'rgba(47,129,247,.2)',borderRadius:4},{label:'Current',data:<?=json_encode($prison_occ)?>,backgroundColor:'#f85149',borderRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top'},datalabels:{display:false}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,grid:{color:'rgba(255,255,255,.05)'}}}}});
</script>
<?php include 'footer.php'; ?>
