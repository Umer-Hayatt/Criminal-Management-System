</div></div><!-- end .content .main -->

<script>
// Init Lucide icons
if(typeof lucide !== 'undefined') lucide.createIcons();

// Chart.js Dashboard charts (only run on dashboard) 
if(typeof D !== 'undefined') {

 const palette = ['#4f46e5','#0284c7','#7c3aed','#059669','#d97706','#e11d48'];
 const paletteLight = ['rgba(79,70,229,.12)','rgba(2,132,199,.12)','rgba(124,58,237,.12)','rgba(5,150,105,.12)','rgba(217,119,6,.12)','rgba(225,29,72,.12)'];

 Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";
 Chart.defaults.font.size = 12;
 Chart.defaults.color = '#94a3b8';

 const donutEl = document.getElementById('chartDonut');
 if(donutEl && D.crimeTypes.length) {
 const chart = new Chart(donutEl, {
 type:'doughnut',
 data:{
 labels:D.crimeTypes,
 datasets:[{
 data:D.crimeCounts,
 backgroundColor:palette,
 borderWidth:0,
 hoverOffset:6
 }]
 },
 options:{
 responsive:true, maintainAspectRatio:false,
 cutout:'72%',
 plugins:{
 legend:{ display:false },
 tooltip:{
 callbacks:{
 label:ctx => ` ${ctx.label}: ${ctx.parsed} crimes`
 }
 }
 }
 }
 });

 // Custom legend
 const leg = document.getElementById('donutLegend');
 if(leg) {
 D.crimeTypes.forEach((lbl,i) => {
 const item = document.createElement('div');
 item.className = 'legend-item';
 item.innerHTML = `<span class="legend-dot" style="background:${palette[i]}"></span>${lbl}`;
 leg.appendChild(item);
 });
 }
 }

 const barEl = document.getElementById('chartCaseBar');
 if(barEl && D.caseLabels.length) {
 const statusColors = {
 'Open':'#d97706','Under Investigation':'#0284c7','Closed':'#059669'
 };
 const bgColors = D.caseLabels.map(l => statusColors[l] || '#4f46e5');
 new Chart(barEl, {
 type:'bar',
 data:{
 labels:D.caseLabels,
 datasets:[{
 label:'Cases',
 data:D.caseCounts,
 backgroundColor:bgColors,
 borderRadius:7,
 borderSkipped:false
 }]
 },
 options:{
 responsive:true, maintainAspectRatio:false,
 plugins:{ legend:{ display:false } },
 scales:{
 y:{
 beginAtZero:true,
 grid:{ color:'rgba(0,0,0,.05)' },
 ticks:{ stepSize:1 }
 },
 x:{ grid:{ display:false } }
 }
 }
 });
 }
}
</script>

</body>
</html>
