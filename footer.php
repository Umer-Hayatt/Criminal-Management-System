</div></div><!-- end .content .main -->

<script>
if(typeof lucide!=='undefined') lucide.createIcons();

// Notification dropdown
function toggleNotif(){
 var d=document.getElementById('notifDropdown');
 d.classList.toggle('open');
}
document.addEventListener('click',function(e){
 var btn=document.getElementById('notifBtn');
 var dd=document.getElementById('notifDropdown');
 if(btn&&dd&&!btn.contains(e.target)) dd.classList.remove('open');
});

// Delete modal
function confirmDelete(url,title,msg){
 document.getElementById('modalTitle').textContent=title||'Delete Record?';
 document.getElementById('modalMsg').textContent=msg||'This action cannot be undone.';
 document.getElementById('modalConfirmBtn').href=url;
 document.getElementById('deleteModal').style.display='flex';
}
function closeDeleteModal(){
 document.getElementById('deleteModal').style.display='none';
}
var _dm=document.getElementById('deleteModal');
if(_dm) _dm.addEventListener('click',function(e){if(e.target===this)closeDeleteModal();});

// Tabs
function switchTab(tabId,groupId){
 var grp=document.getElementById(groupId||'tabGroup');
 if(!grp) return;
 grp.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
 grp.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
 var btn=grp.querySelector('[data-tab="'+tabId+'"]');
 var pane=document.getElementById('tab-'+tabId);
 if(btn) btn.classList.add('active');
 if(pane) pane.classList.add('active');
}
function initTabs(groupId){
 var grp=document.getElementById(groupId||'tabGroup');
 if(!grp) return;
 var first=grp.querySelector('.tab-btn');
 if(first) switchTab(first.dataset.tab,groupId);
 // Check URL hash
 var hash=window.location.hash.replace('#','');
 if(hash) switchTab(hash,groupId);
}

// Photo preview
function initPhotoPreview(inputId,previewId){
 var inp=document.getElementById(inputId);
 var prev=document.getElementById(previewId);
 if(!inp||!prev) return;
 inp.addEventListener('change',function(){
  if(this.files&&this.files[0]){
   var reader=new FileReader();
   reader.onload=function(e){prev.src=e.target.result;};
   reader.readAsDataURL(this.files[0]);
  }
 });
}

// Chart defaults
if(typeof Chart!=='undefined'){
 Chart.defaults.font.family="'Plus Jakarta Sans',system-ui,sans-serif";
 Chart.defaults.font.size=12;
 Chart.defaults.color='#8b949e';
}

// Dashboard pie chart
if(typeof D!=='undefined'&&typeof Chart!=='undefined'){
 var palette=['#2f81f7','#3fb950','#d29922','#f85149','#8b5cf6','#ec4899'];
 if(typeof ChartDataLabels!=='undefined') Chart.register(ChartDataLabels);
 var pieEl=document.getElementById('chartPie');
 if(pieEl&&D.crimeTypes&&D.crimeTypes.length){
  var total=D.crimeCounts.reduce((a,b)=>a+b,0);
  new Chart(pieEl,{
   type:'pie',
   data:{labels:D.crimeTypes,datasets:[{data:D.crimeCounts,backgroundColor:palette,borderWidth:2,borderColor:'#161b22',hoverOffset:6}]},
   options:{responsive:true,maintainAspectRatio:false,
    plugins:{
     legend:{display:false},
     tooltip:{callbacks:{label:ctx=>' '+ctx.label+': '+ctx.parsed+' crimes'}},
     datalabels:{color:'#fff',font:{weight:'bold',size:11},formatter:function(val,ctx){
      var pct=total>0?Math.round(val/total*100):0;
      return pct>5?pct+'%':'';
     }}
    }
   }
  });
  var leg=document.getElementById('pieLegend');
  if(leg){
   D.crimeTypes.forEach(function(lbl,i){
    var pct=total>0?Math.round(D.crimeCounts[i]/total*100):0;
    var item=document.createElement('div');
    item.className='legend-item';
    item.innerHTML='<span class="legend-dot" style="background:'+palette[i]+'"></span>'+lbl+' <strong style="color:var(--txt)">'+D.crimeCounts[i]+'</strong> <span>('+pct+'%)</span>';
    leg.appendChild(item);
   });
  }
 }
}
</script>
</body>
</html>
