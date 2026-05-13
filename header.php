<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRMS — <?= $pageTitle ?? 'Criminal Record Management System' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>

/* ══════════════════════════════════════════════════════════
   DESIGN SYSTEM
══════════════════════════════════════════════════════════ */
:root {
  /* Palette — Slate + Indigo professional */
  --bg:            #f8fafc;
  --surface:       #ffffff;
  --surface-alt:   #f1f5f9;
  --sidebar-bg:    #0f172a;
  --sidebar-hover: rgba(255,255,255,.06);
  --sidebar-act:   rgba(99,102,241,.18);

  --indigo:        #4f46e5;
  --indigo-lt:     #eef2ff;
  --indigo-dk:     #4338ca;
  --sky:           #0284c7;
  --sky-lt:        #f0f9ff;
  --emerald:       #059669;
  --emerald-lt:    #ecfdf5;
  --amber:         #d97706;
  --amber-lt:      #fffbeb;
  --rose:          #e11d48;
  --rose-lt:       #fff1f2;
  --violet:        #7c3aed;
  --violet-lt:     #f5f3ff;

  --txt:           #0f172a;
  --txt-mid:       #475569;
  --txt-soft:      #94a3b8;
  --border:        #e2e8f0;
  --border-lt:     #f1f5f9;

  --shadow-xs:     0 1px 2px rgba(0,0,0,.05);
  --shadow-sm:     0 1px 3px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.04);
  --shadow:        0 4px 6px -1px rgba(0,0,0,.07), 0 2px 4px -2px rgba(0,0,0,.04);
  --shadow-md:     0 10px 15px -3px rgba(0,0,0,.07), 0 4px 6px -4px rgba(0,0,0,.04);
  --shadow-lg:     0 20px 25px -5px rgba(0,0,0,.08), 0 8px 10px -6px rgba(0,0,0,.04);

  --radius:        10px;
  --radius-lg:     14px;
  --radius-xl:     18px;

  --sw:            256px;
  --th:            62px;
  --trans:         all .18s cubic-bezier(.4,0,.2,1);
}

/* ── RESET ─────────────────────────────── */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior:smooth; }
body {
  font-family:'Plus Jakarta Sans', system-ui, sans-serif;
  background:var(--bg);
  color:var(--txt);
  font-size:13.5px;
  line-height:1.6;
  -webkit-font-smoothing:antialiased;
}

/* ── SIDEBAR ───────────────────────────── */
.sidebar {
  position:fixed; top:0; left:0; bottom:0;
  width:var(--sw);
  background:var(--sidebar-bg);
  display:flex; flex-direction:column;
  overflow-y:auto; z-index:200;
}
.sidebar::-webkit-scrollbar { width:0; }

.sidebar-brand {
  padding:20px 22px 18px;
  border-bottom:1px solid rgba(255,255,255,.07);
  display:flex; align-items:center; gap:11px;
}
.brand-logo {
  width:36px; height:36px; border-radius:9px;
  background:linear-gradient(135deg, var(--indigo), var(--violet));
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.brand-logo i { color:#fff; width:18px; height:18px; }
.brand-copy strong {
  display:block; font-size:15px; font-weight:800;
  color:#fff; letter-spacing:-.2px; line-height:1.1;
}
.brand-copy small {
  font-size:10.5px; color:rgba(255,255,255,.35);
  font-weight:500; letter-spacing:.2px;
}

.nav-group { padding:14px 0 2px; }
.nav-group-label {
  padding:0 22px 7px;
  font-size:9.5px; font-weight:700;
  text-transform:uppercase; letter-spacing:1.6px;
  color:rgba(255,255,255,.22);
}
.nav-item {
  display:flex; align-items:center; gap:10px;
  padding:9px 22px;
  color:rgba(255,255,255,.5); text-decoration:none;
  font-size:13px; font-weight:500;
  border-left:2px solid transparent;
  transition:var(--trans); margin:1px 0;
}
.nav-item i { width:16px; height:16px; opacity:.7; transition:var(--trans); flex-shrink:0; }
.nav-item:hover { color:rgba(255,255,255,.85); background:var(--sidebar-hover); }
.nav-item:hover i { opacity:1; }
.nav-item.active {
  color:#fff; background:var(--sidebar-act);
  border-left-color:var(--indigo);
}
.nav-item.active i { opacity:1; color:#a5b4fc; }

.sidebar-foot {
  margin-top:auto; padding:18px 22px;
  border-top:1px solid rgba(255,255,255,.07);
}
.status-dot {
  display:flex; align-items:center; gap:8px;
  font-size:11px; color:rgba(255,255,255,.25); font-weight:500;
}
.status-dot::before {
  content:''; width:6px; height:6px; border-radius:50%;
  background:#34d399; flex-shrink:0;
  box-shadow:0 0 0 2px rgba(52,211,153,.25);
}

/* ── TOPBAR ────────────────────────────── */
.topbar {
  position:fixed; top:0; left:var(--sw); right:0;
  height:var(--th);
  background:rgba(255,255,255,.92);
  backdrop-filter:blur(14px);
  border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between;
  padding:0 28px; z-index:100;
}
.tb-title { font-size:15px; font-weight:700; color:var(--txt); letter-spacing:-.2px; }
.tb-right { display:flex; align-items:center; gap:12px; }
.tb-chip {
  font-size:12px; color:var(--txt-mid); font-weight:500;
  background:var(--surface-alt); border:1px solid var(--border);
  padding:5px 12px; border-radius:8px;
}
.tb-icon {
  width:36px; height:36px; border-radius:9px;
  background:var(--surface-alt); border:1px solid var(--border);
  display:flex; align-items:center; justify-content:center;
  color:var(--txt-mid); cursor:pointer; transition:var(--trans);
}
.tb-icon:hover { background:var(--indigo-lt); color:var(--indigo); border-color:#c7d2fe; }
.tb-icon i { width:16px; height:16px; }
.tb-avatar {
  width:34px; height:34px; border-radius:9px;
  background:linear-gradient(135deg, var(--indigo), var(--violet));
  display:flex; align-items:center; justify-content:center;
  color:#fff; font-size:13px; font-weight:700; cursor:pointer;
}

/* ── LAYOUT ────────────────────────────── */
.main { margin-left:var(--sw); padding-top:var(--th); min-height:100vh; }
.content { padding:28px 30px 48px; }

/* ── PAGE HEADER ───────────────────────── */
.page-hdr {
  display:flex; justify-content:space-between; align-items:center;
  margin-bottom:26px;
}
.page-hdr h2 { font-size:21px; font-weight:800; color:var(--txt); letter-spacing:-.3px; }
.page-hdr p  { font-size:12.5px; color:var(--txt-soft); margin-top:3px; }

/* ══════════════════════════════════════════════════════════
   DASHBOARD COMPONENTS
══════════════════════════════════════════════════════════ */

/* KPI Cards */
.kpi-grid {
  display:grid;
  grid-template-columns:repeat(4, 1fr);
  gap:16px; margin-bottom:24px;
}
.kpi-card {
  background:var(--surface);
  border:1px solid var(--border);
  border-radius:var(--radius-lg);
  padding:18px 20px;
  box-shadow:var(--shadow-sm);
  transition:var(--trans);
}
.kpi-card:hover { box-shadow:var(--shadow); transform:translateY(-1px); }
.kpi-left { display:flex; align-items:center; gap:14px; margin-bottom:12px; }
.kpi-icon {
  width:42px; height:42px; border-radius:10px;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.kpi-icon i { width:19px; height:19px; color:#fff; }
.kpi-blue   { background:linear-gradient(135deg,#4f46e5,#818cf8); }
.kpi-amber  { background:linear-gradient(135deg,#d97706,#fbbf24); }
.kpi-red    { background:linear-gradient(135deg,#e11d48,#fb7185); }
.kpi-green  { background:linear-gradient(135deg,#059669,#34d399); }
.kpi-val {
  font-size:28px; font-weight:800; color:var(--txt);
  letter-spacing:-.5px; line-height:1;
}
.kpi-lbl { font-size:12px; color:var(--txt-soft); font-weight:500; margin-top:2px; }
.kpi-sub { display:flex; gap:6px; flex-wrap:wrap; }
.kpi-pill {
  font-size:11px; font-weight:600; padding:3px 9px;
  border-radius:6px; white-space:nowrap;
}
.pill-orange { background:var(--amber-lt);  color:var(--amber); }
.pill-red    { background:var(--rose-lt);   color:var(--rose); }
.pill-green  { background:var(--emerald-lt);color:var(--emerald); }
.pill-muted  { background:var(--border-lt); color:var(--txt-soft); }

/* Two-column dashboard layout */
.dash-two-col {
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:18px; margin-bottom:18px;
}

/* Charts */
.chart-container {
  position:relative; width:100%; height:240px;
  margin-bottom:8px;
}
.legend-list {
  display:flex; flex-wrap:wrap; gap:8px;
  margin-top:12px;
}
.legend-item {
  display:flex; align-items:center; gap:5px;
  font-size:11.5px; color:var(--txt-mid); font-weight:500;
}
.legend-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }

/* Caseload bars */
.caseload-list { display:flex; flex-direction:column; gap:14px; }
.caseload-row {}
.caseload-meta {
  display:flex; justify-content:space-between;
  align-items:baseline; margin-bottom:5px;
}
.caseload-name { font-size:13px; font-weight:600; color:var(--txt); }
.caseload-count { font-size:12px; font-weight:700; }
.caseload-bar-bg {
  width:100%; height:7px; background:var(--border-lt);
  border-radius:99px; overflow:hidden;
}
.caseload-bar-fill {
  height:100%; border-radius:99px;
  transition:width .9s cubic-bezier(.4,0,.2,1);
}

/* Quick Actions */
.quick-actions { display:flex; flex-direction:column; gap:10px; }
.qa-btn {
  display:flex; align-items:center; gap:14px;
  padding:12px 14px; border-radius:10px;
  background:var(--surface-alt); border:1px solid var(--border);
  text-decoration:none; transition:var(--trans); cursor:pointer;
}
.qa-btn:hover {
  background:var(--indigo-lt); border-color:#c7d2fe;
  transform:translateX(3px);
}
.qa-icon {
  width:38px; height:38px; border-radius:9px;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.qa-icon i { width:17px; height:17px; }
.qa-text strong { display:block; font-size:13px; font-weight:600; color:var(--txt); }
.qa-text span { font-size:11.5px; color:var(--txt-soft); }

/* Activity rows */
.activity-row {
  display:flex; align-items:center; gap:12px;
  padding:10px 0; border-bottom:1px solid var(--border-lt);
}
.activity-row:last-child { border-bottom:none; padding-bottom:0; }
.activity-avatar {
  width:34px; height:34px; border-radius:9px; flex-shrink:0;
  background:linear-gradient(135deg,var(--indigo),var(--violet));
  display:flex; align-items:center; justify-content:center;
  color:#fff; font-size:13px; font-weight:700;
}
.activity-avatar.case-av {
  background:linear-gradient(135deg,var(--sky),#38bdf8);
  font-size:12px;
}
.activity-info { flex:1; min-width:0; }
.activity-info strong { display:block; font-size:13px; font-weight:600; color:var(--txt); }
.activity-info span { font-size:11.5px; color:var(--txt-soft); }

/* Card header row */
.card-header-row {
  display:flex; justify-content:space-between; align-items:center;
  margin-bottom:4px;
}

/* No data message */
.no-data-msg {
  text-align:center; padding:32px 20px;
  color:var(--txt-soft); font-size:13px;
}

/* ══════════════════════════════════════════════════════════
   GLOBAL COMPONENTS
══════════════════════════════════════════════════════════ */

/* Card */
.card {
  background:var(--surface); border:1px solid var(--border);
  border-radius:var(--radius-lg); padding:22px; margin-bottom:18px;
  box-shadow:var(--shadow-sm); transition:var(--trans);
}
.card:hover { box-shadow:var(--shadow); }
.card-title {
  font-size:13.5px; font-weight:700; color:var(--txt);
  margin-bottom:16px; padding-bottom:12px;
  border-bottom:1px solid var(--border-lt);
  display:flex; align-items:center; gap:8px; letter-spacing:-.1px;
}
.card-title i { width:16px; height:16px; color:var(--indigo); }

/* Buttons */
.btn {
  display:inline-flex; align-items:center; gap:6px;
  padding:9px 18px; border:none; border-radius:8px;
  font-family:inherit; font-size:13px; font-weight:600;
  cursor:pointer; text-decoration:none; transition:var(--trans);
  letter-spacing:-.1px; white-space:nowrap;
}
.btn:hover { transform:translateY(-1px); box-shadow:var(--shadow); }
.btn:active { transform:translateY(0); }
.btn i { width:14px; height:14px; }

.btn-primary  { background:var(--indigo); color:#fff; }
.btn-primary:hover { background:var(--indigo-dk); }
.btn-blue     { background:var(--sky); color:#fff; }
.btn-blue:hover { background:#0369a1; }
.btn-green    { background:var(--emerald); color:#fff; }
.btn-green:hover { background:#047857; }
.btn-amber    { background:var(--amber); color:#fff; }
.btn-gray     { background:var(--surface-alt); color:var(--txt-mid); border:1px solid var(--border); }
.btn-gray:hover { background:var(--indigo-lt); color:var(--indigo); border-color:#c7d2fe; }
.btn-danger   { background:var(--rose); color:#fff; }
.btn-danger:hover { background:#be123c; }
.btn-sm { padding:5px 12px; font-size:12px; border-radius:6px; }

/* Table */
.tbl-wrap { overflow-x:auto; border-radius:var(--radius); overflow:hidden; border:1px solid var(--border); }
table { width:100%; border-collapse:collapse; }
thead tr { background:var(--sidebar-bg); }
thead th {
  padding:11px 14px; text-align:left;
  font-size:11px; font-weight:600; letter-spacing:.6px;
  text-transform:uppercase; color:rgba(255,255,255,.75);
  white-space:nowrap;
}
tbody tr { border-bottom:1px solid var(--border-lt); transition:var(--trans); }
tbody tr:hover { background:var(--indigo-lt); }
tbody td { padding:11px 14px; vertical-align:middle; font-size:13px; }
.td-actions { display:flex; gap:6px; flex-wrap:wrap; }
.empty-row td { text-align:center; padding:40px; color:var(--txt-soft); font-size:13px; }

/* Badges */
.badge {
  display:inline-flex; align-items:center; gap:4px;
  padding:3px 9px; border-radius:6px;
  font-size:11px; font-weight:600; white-space:nowrap; letter-spacing:.1px;
}
.badge::before { content:''; width:5px; height:5px; border-radius:50%; flex-shrink:0; }
.b-red    { background:var(--rose-lt);   color:var(--rose);    } .b-red::before    { background:var(--rose); }
.b-green  { background:var(--emerald-lt);color:var(--emerald); } .b-green::before  { background:var(--emerald); }
.b-orange { background:var(--amber-lt);  color:var(--amber);   } .b-orange::before { background:var(--amber); }
.b-blue   { background:var(--sky-lt);    color:var(--sky);     } .b-blue::before   { background:var(--sky); }
.b-purple { background:var(--violet-lt); color:var(--violet);  } .b-purple::before { background:var(--violet); }

/* Forms */
.form-section {
  background:var(--surface-alt); border:1px solid var(--border);
  border-radius:var(--radius-lg); padding:22px; margin-bottom:16px;
}
.form-section-title {
  font-size:12.5px; font-weight:700; color:var(--txt);
  text-transform:uppercase; letter-spacing:.5px;
  margin-bottom:16px; display:flex; align-items:center; gap:8px;
}
.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.form-grid.cols3 { grid-template-columns:1fr 1fr 1fr; }
.form-group { display:flex; flex-direction:column; gap:5px; }
.form-group.full { grid-column:1/-1; }
label { font-size:11px; font-weight:600; color:var(--txt-mid); text-transform:uppercase; letter-spacing:.4px; }
input,select,textarea {
  padding:9px 13px; border:1px solid var(--border);
  border-radius:8px; font-size:13px; font-family:inherit;
  color:var(--txt); background:var(--surface); outline:none;
  transition:var(--trans); width:100%;
}
input::placeholder,textarea::placeholder { color:var(--txt-soft); }
input:focus,select:focus,textarea:focus {
  border-color:var(--indigo); box-shadow:0 0 0 3px rgba(79,70,229,.1);
}
select option { background:white; }
textarea { resize:vertical; min-height:72px; }

/* Alerts */
.alert {
  padding:12px 16px; border-radius:var(--radius); margin-bottom:16px;
  font-size:13px; font-weight:500; display:flex; align-items:center; gap:8px;
}
.alert-success { background:var(--emerald-lt); color:var(--emerald); border:1px solid #a7f3d0; }
.alert-error   { background:var(--rose-lt);    color:var(--rose);    border:1px solid #fecdd3; }

/* Profile */
.profile-header {
  background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#1e1b4b 100%);
  color:#fff; border-radius:var(--radius-xl); padding:28px; margin-bottom:18px;
  position:relative; overflow:hidden;
}
.profile-header::before {
  content:''; position:absolute; top:-40px; right:-40px;
  width:200px; height:200px; border-radius:50%;
  background:rgba(99,102,241,.12);
}
.section-card {
  background:var(--surface); border:1px solid var(--border);
  border-radius:var(--radius-lg); padding:20px; margin-bottom:14px;
  box-shadow:var(--shadow-sm);
}
.section-hdr {
  display:flex; justify-content:space-between; align-items:center;
  margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid var(--border-lt);
}
.section-hdr h3 { font-size:13.5px; font-weight:700; color:var(--txt); letter-spacing:-.1px; }
.no-data { text-align:center; padding:22px; color:var(--txt-soft); font-size:13px; }

/* Search */
.search-box { display:flex; gap:10px; }
.search-box input { flex:1; font-size:14px; padding:10px 15px; }

/* Scrollbar */
::-webkit-scrollbar { width:5px; height:5px; }
::-webkit-scrollbar-track { background:var(--bg); }
::-webkit-scrollbar-thumb { background:var(--border); border-radius:4px; }
::-webkit-scrollbar-thumb:hover { background:#94a3b8; }

/* Animations */
@keyframes fadeUp { from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:none;} }
.content { animation:fadeUp .2s ease both; }

/* Responsive */
@media(max-width:1200px){.kpi-grid{grid-template-columns:repeat(2,1fr);}.dash-two-col{grid-template-columns:1fr;}}
@media(max-width:768px){:root{--sw:0px;}.sidebar{transform:translateX(-100%);}.content{padding:16px;}.form-grid,.form-grid.cols3{grid-template-columns:1fr;}}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-logo"><i data-lucide="shield-check"></i></div>
    <div class="brand-copy">
      <strong>CRMS</strong>
      <small>Criminal Records System</small>
    </div>
  </div>

  <div class="nav-group">
    <div class="nav-group-label">Main</div>
    <a href="index.php"   class="nav-item <?= basename($_SERVER['PHP_SELF'])=='index.php'  ?'active':''?>"><i data-lucide="layout-dashboard"></i> Dashboard</a>
    <a href="records.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='records.php'?'active':''?>"><i data-lucide="file-text"></i> All Records</a>
    <a href="search.php"  class="nav-item <?= basename($_SERVER['PHP_SELF'])=='search.php' ?'active':''?>"><i data-lucide="search"></i> Search Criminal</a>
    <a href="register.php"class="nav-item <?= basename($_SERVER['PHP_SELF'])=='register.php'?'active':''?>"><i data-lucide="user-plus"></i> Register Criminal</a>
  </div>

  <div class="nav-group">
    <div class="nav-group-label">Manage</div>
    <a href="officers.php"class="nav-item <?= basename($_SERVER['PHP_SELF'])=='officers.php'?'active':''?>"><i data-lucide="badge"></i> Officers</a>
    <a href="prisons.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='prisons.php' ?'active':''?>"><i data-lucide="building"></i> Prisons</a>
  </div>

  <div class="sidebar-foot">
    <div class="status-dot">System Online — <?= date('H:i') ?></div>
  </div>
</div>

<!-- TOPBAR -->
<div class="topbar">
  <div class="tb-title"><?= $pageTitle ?? 'Dashboard' ?></div>
  <div class="tb-right">
    <span class="tb-chip"><?= date('D, d M Y') ?></span>
    <div class="tb-icon"><i data-lucide="bell"></i></div>
  </div>
</div>

<div class="main"><div class="content">
