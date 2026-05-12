<?php
// ═══════════════════════════════════════════════════════════════════
//  Staff HQ — unified activity dashboard across all sites
//  Auth: password stored in staff-hq-sites.php (never in source).
// ═══════════════════════════════════════════════════════════════════
session_name('staff_hq');
session_start();

$cfgFile = __DIR__ . '/staff-hq-sites.php';
if (!is_readable($cfgFile)) {
    die('<pre style="font-family:monospace;padding:30px;color:#dc2626">staff-hq-sites.php not found.<br>Copy staff-hq-sites.example.php → staff-hq-sites.php and fill in your credentials.</pre>');
}
$cfg      = require $cfgFile;
$hqPass   = $cfg['hq_password'] ?? '';
$sites    = $cfg['sites']        ?? [];

// ── Auth ─────────────────────────────────────────────────────────────────────
$authErr = '';
if (isset($_POST['hq_pass'])) {
    if (hash_equals($hqPass, $_POST['hq_pass'])) {
        $_SESSION['hq_ok'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']); exit;
    }
    $authErr = 'Wrong password.';
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: ' . $_SERVER['PHP_SELF']); exit; }

if (empty($_SESSION['hq_ok'])): ?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Staff HQ — Login</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;display:flex;align-items:center;justify-content:center;min-height:100vh}
.card{background:#1e293b;border:1px solid #334155;border-radius:16px;padding:40px;width:340px;text-align:center}
h1{color:#f1f5f9;font-size:22px;margin-bottom:4px}
.sub{color:#64748b;font-size:13px;margin-bottom:28px}
input{width:100%;padding:11px 14px;background:#0f172a;border:1px solid #334155;border-radius:9px;color:#f1f5f9;font-size:15px;margin-bottom:14px;outline:none}
input:focus{border-color:#3b82f6}
button{width:100%;padding:12px;background:#3b82f6;color:#fff;border:none;border-radius:9px;font-size:15px;font-weight:600;cursor:pointer}
button:hover{background:#2563eb}
.err{color:#f87171;font-size:13px;margin-top:10px}
</style></head>
<body>
<div class="card">
  <h1>🏢 Staff HQ</h1>
  <div class="sub">Unified staff activity across all sites</div>
  <form method="post">
    <input type="password" name="hq_pass" placeholder="HQ Password" autofocus autocomplete="current-password">
    <button type="submit">Sign In</button>
    <?php if ($authErr): ?><div class="err"><?= htmlspecialchars($authErr) ?></div><?php endif; ?>
  </form>
</div>
</body></html>
<?php exit; endif;

// ── Fetch logs from all sites ─────────────────────────────────────────────────
function fetchSiteLogs(array $site): array {
    $ts  = time();
    $sig = hash_hmac('sha256', $ts . ':staff-logs', $site['secret']);
    $url = rtrim($site['url'], '?') . '?limit=500';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => [
            'X-Deploy-Timestamp: ' . $ts,
            'X-Deploy-Signature: sha256=' . $sig,
        ],
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($code !== 200 || !$body) {
        return ['error' => $err ?: "HTTP $code", 'rows' => []];
    }
    $data = json_decode($body, true);
    if (!($data['ok'] ?? false)) {
        return ['error' => $data['error'] ?? 'API error', 'rows' => []];
    }
    $rows = $data['rows'] ?? [];
    foreach ($rows as &$row) {
        $row['site_tag']   = $site['tag'];
        $row['site_name']  = $site['name'];
        $row['site_color'] = $site['color'] ?? '#1d4ed8';
        $row['site_bg']    = $site['bg']    ?? '#dbeafe';
    }
    return ['error' => null, 'rows' => $rows];
}

$allRows    = [];
$siteErrors = [];
$siteCounts = [];

foreach ($sites as $site) {
    $result = fetchSiteLogs($site);
    if ($result['error']) {
        $siteErrors[$site['tag']] = $result['error'];
        $siteCounts[$site['tag']] = 0;
    } else {
        $siteCounts[$site['tag']] = count($result['rows']);
        $allRows = array_merge($allRows, $result['rows']);
    }
}

// Sort all rows by timestamp desc
usort($allRows, fn($a, $b) => strcmp($b['timestamp'], $a['timestamp']));

// Unique staff list
$staffList = array_unique(array_column($allRows, 'admin_name'));
sort($staffList);

// Type list
$typeList = array_unique(array_column($allRows, 'type'));
sort($typeList);

// Today count
$today      = date('Y-m-d');
$todayCount = count(array_filter($allRows, fn($r) => str_starts_with($r['timestamp'], $today)));

// Most active staff today
$staffToday = [];
foreach ($allRows as $r) {
    if (str_starts_with($r['timestamp'], $today)) {
        $staffToday[$r['admin_name']] = ($staffToday[$r['admin_name']] ?? 0) + 1;
    }
}
arsort($staffToday);
$topStaff = array_key_first($staffToday) ?? '—';
$topCount = $staffToday[$topStaff] ?? 0;

$typeColors = [
    'profile'  => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'icon' => '👤'],
    'bill'     => ['bg' => '#f0fdf4', 'color' => '#166534', 'icon' => '💳'],
    'followup' => ['bg' => '#fffbeb', 'color' => '#92400e', 'icon' => '📞'],
    'admin'    => ['bg' => '#fdf4ff', 'color' => '#7e22ce', 'icon' => '⚙️'],
    'story'    => ['bg' => '#fdf2f8', 'color' => '#9d174d', 'icon' => '❤️'],
    'setting'  => ['bg' => '#f0f9ff', 'color' => '#0369a1', 'icon' => '🔧'],
    'plan'     => ['bg' => '#fff7ed', 'color' => '#c2410c', 'icon' => '📋'],
];

function typeStyle(string $type): array {
    global $typeColors;
    return $typeColors[strtolower($type)] ?? ['bg' => '#f3f4f6', 'color' => '#374151', 'icon' => '•'];
}

function fmtTime(string $ts): string {
    $t = strtotime($ts);
    if (!$t) return $ts;
    $diff = time() - $t;
    if ($diff < 60)    return 'just now';
    if ($diff < 3600)  return floor($diff/60) . 'm ago';
    if ($diff < 86400) return floor($diff/3600) . 'h ago';
    return date('d M, H:i', $t);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Staff HQ — All Sites</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f1f5f9;color:#1e293b;min-height:100vh}
.topbar{background:#0f172a;color:#f8fafc;padding:0 28px;display:flex;align-items:center;gap:16px;height:54px;position:sticky;top:0;z-index:100}
.topbar h1{font-size:17px;font-weight:700;letter-spacing:.02em}
.topbar .sub{font-size:12px;color:#64748b}
.topbar a{margin-left:auto;color:#94a3b8;font-size:13px;text-decoration:none;padding:6px 12px;border:1px solid #334155;border-radius:7px}
.topbar a:hover{color:#f1f5f9;border-color:#64748b}
.wrap{max-width:1400px;margin:0 auto;padding:24px 20px}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:22px}
.stat{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 20px}
.stat-num{font-size:28px;font-weight:800;color:#0f172a}
.stat-lbl{font-size:12px;color:#64748b;margin-top:2px}
.site-tag{display:inline-block;font-size:10px;font-weight:700;padding:2px 7px;border-radius:4px;margin-left:6px;vertical-align:middle}
.filters{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:14px 18px;margin-bottom:18px;display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.filters input,.filters select{border:1px solid #e2e8f0;border-radius:8px;padding:7px 11px;font-size:13px;color:#1e293b;background:#fff;outline:none}
.filters input:focus,.filters select:focus{border-color:#3b82f6}
.filters input[type=search]{min-width:220px}
.btn-sm{padding:7px 14px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:13px;cursor:pointer;color:#374151;font-weight:600}
.btn-sm:hover{background:#f8fafc}
.btn-primary{background:#0f172a;color:#fff;border-color:#0f172a}
.btn-primary:hover{background:#1e293b}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden}
table{width:100%;border-collapse:collapse;font-size:13px}
th{padding:9px 14px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap}
td{padding:9px 14px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#f8fafc}
.badge{display:inline-flex;align-items:center;gap:4px;font-size:11.5px;font-weight:600;padding:2px 8px;border-radius:5px;white-space:nowrap}
.staff-av{width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0;margin-right:7px}
.no-data{text-align:center;padding:40px;color:#94a3b8;font-size:14px}
.err-chip{background:#fee2e2;color:#dc2626;border-radius:6px;padding:4px 10px;font-size:12px;font-weight:600;margin-right:6px}
.time-col{color:#64748b;font-size:12px;white-space:nowrap}
.detail-col{color:#475569;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.count-badge{background:#f1f5f9;color:#475569;font-size:11px;font-weight:700;padding:1px 7px;border-radius:10px;margin-left:6px}
@media(max-width:700px){.filters{flex-direction:column}.filters input,.filters select{width:100%}.detail-col{max-width:160px}}
</style>
</head>
<body>

<div class="topbar">
  <div>
    <div style="display:flex;align-items:center;gap:10px">
      <span style="font-size:20px">🏢</span>
      <div>
        <div class="topbar h1" style="font-size:17px;font-weight:700">Staff HQ</div>
        <div class="sub"><?= count($sites) ?> sites · <?= count($allRows) ?> total records</div>
      </div>
    </div>
  </div>
  <?php foreach ($sites as $s): ?>
    <span class="site-tag" style="background:<?= htmlspecialchars($s['bg']) ?>;color:<?= htmlspecialchars($s['color']) ?>">
      <?= htmlspecialchars($s['tag']) ?>
      <?php if (isset($siteErrors[$s['tag']])): ?>
        <span style="color:#dc2626" title="<?= htmlspecialchars($siteErrors[$s['tag']]) ?>">✗</span>
      <?php else: ?>
        <span>(<?= $siteCounts[$s['tag']] ?>)</span>
      <?php endif; ?>
    </span>
  <?php endforeach; ?>
  <a href="?logout=1">Sign out</a>
</div>

<div class="wrap">

  <!-- Stats -->
  <div class="stats">
    <div class="stat">
      <div class="stat-num"><?= $todayCount ?></div>
      <div class="stat-lbl">Actions today</div>
    </div>
    <div class="stat">
      <div class="stat-num"><?= count($allRows) ?></div>
      <div class="stat-lbl">Total records loaded</div>
    </div>
    <?php foreach ($sites as $s): ?>
    <div class="stat">
      <div class="stat-num"><?= $siteCounts[$s['tag']] ?? 0 ?></div>
      <div class="stat-lbl">
        <span class="site-tag" style="background:<?= htmlspecialchars($s['bg']) ?>;color:<?= htmlspecialchars($s['color']) ?>"><?= htmlspecialchars($s['tag']) ?></span>
        <?= htmlspecialchars($s['name']) ?>
      </div>
    </div>
    <?php endforeach; ?>
    <div class="stat">
      <div class="stat-num" style="font-size:18px"><?= htmlspecialchars($topStaff) ?></div>
      <div class="stat-lbl">Most active today (<?= $topCount ?> actions)</div>
    </div>
  </div>

  <!-- Errors -->
  <?php if ($siteErrors): ?>
  <div style="margin-bottom:14px">
    <?php foreach ($siteErrors as $tag => $err): ?>
      <span class="err-chip">⚠ <?= htmlspecialchars($tag) ?>: <?= htmlspecialchars($err) ?></span>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Filters -->
  <div class="filters">
    <input type="search" id="fSearch" placeholder="🔍  Search staff, action or detail…" oninput="applyFilters()">
    <select id="fSite" onchange="applyFilters()">
      <option value="">All Sites</option>
      <?php foreach ($sites as $s): ?>
        <option value="<?= htmlspecialchars($s['tag']) ?>"><?= htmlspecialchars($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="fType" onchange="applyFilters()">
      <option value="">All Types</option>
      <?php foreach ($typeList as $t): ?>
        <option value="<?= htmlspecialchars($t) ?>"><?= ucfirst(htmlspecialchars($t)) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="fStaff" onchange="applyFilters()">
      <option value="">All Staff</option>
      <?php foreach ($staffList as $s): ?>
        <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="date" id="fFrom" onchange="applyFilters()" title="From date">
    <input type="date" id="fTo"   onchange="applyFilters()" title="To date">
    <button class="btn-sm" onclick="clearFilters()">✕ Clear</button>
    <button class="btn-sm btn-primary" onclick="exportCSV()">⬇ Export CSV</button>
    <span id="rowCount" style="margin-left:auto;font-size:12px;color:#64748b"></span>
  </div>

  <!-- Table -->
  <div class="card">
    <table id="logTable">
      <thead>
        <tr>
          <th>#</th>
          <th>Time</th>
          <th>Site</th>
          <th>Staff</th>
          <th>Role</th>
          <th>Type</th>
          <th>Action</th>
          <th>Detail</th>
        </tr>
      </thead>
      <tbody id="logBody">
        <tr><td colspan="8" class="no-data">Loading…</td></tr>
      </tbody>
    </table>
  </div>

</div><!-- /wrap -->

<script>
const _rows = <?= json_encode(array_values($allRows), JSON_UNESCAPED_UNICODE) ?>;

const _typeStyle = {
  profile:  {bg:'#eff6ff',color:'#1d4ed8',icon:'👤'},
  bill:     {bg:'#f0fdf4',color:'#166534',icon:'💳'},
  followup: {bg:'#fffbeb',color:'#92400e',icon:'📞'},
  admin:    {bg:'#fdf4ff',color:'#7e22ce',icon:'⚙️'},
  story:    {bg:'#fdf2f8',color:'#9d174d',icon:'❤️'},
  setting:  {bg:'#f0f9ff',color:'#0369a1',icon:'🔧'},
  plan:     {bg:'#fff7ed',color:'#c2410c',icon:'📋'},
};
function typeStyle(t){return _typeStyle[t.toLowerCase()]||{bg:'#f3f4f6',color:'#374151',icon:'•'};}

function fmtTime(ts){
  const d=new Date(ts.replace(' ','T'));
  const diff=Math.floor((Date.now()-d)/1000);
  if(isNaN(diff)) return ts;
  if(diff<60)  return 'just now';
  if(diff<3600) return Math.floor(diff/60)+'m ago';
  if(diff<86400) return Math.floor(diff/3600)+'h ago';
  return d.toLocaleDateString('en-IN',{day:'numeric',month:'short'})+' '+d.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',hour12:false});
}

const avatarColors=['#3b82f6','#8b5cf6','#ec4899','#10b981','#f59e0b','#6366f1','#14b8a6','#f97316'];
function avColor(name){let h=0;for(const c of name)h=(h*31+c.charCodeAt(0))&0xffff;return avatarColors[h%avatarColors.length];}

function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

let _filtered = [..._rows];

function applyFilters(){
  const q    = document.getElementById('fSearch').value.toLowerCase();
  const site = document.getElementById('fSite').value;
  const type = document.getElementById('fType').value;
  const staff= document.getElementById('fStaff').value;
  const from = document.getElementById('fFrom').value;
  const to   = document.getElementById('fTo').value;

  _filtered = _rows.filter(r=>{
    if(site  && r.site_tag   !== site)  return false;
    if(type  && r.type       !== type)  return false;
    if(staff && r.admin_name !== staff) return false;
    const d = (r.timestamp||'').substring(0,10);
    if(from && d < from) return false;
    if(to   && d > to)   return false;
    if(q){
      const hay=(r.admin_name+' '+r.action+' '+r.detail+' '+r.site_tag).toLowerCase();
      if(!hay.includes(q)) return false;
    }
    return true;
  });

  render();
}

function render(){
  const tbody = document.getElementById('logBody');
  document.getElementById('rowCount').textContent = _filtered.length + ' records';

  if(!_filtered.length){
    tbody.innerHTML='<tr><td colspan="8" class="no-data">No records match the current filters.</td></tr>';
    return;
  }

  tbody.innerHTML = _filtered.map((r,i)=>{
    const ts = typeStyle(r.type||'');
    const init = (r.admin_name||'?')[0].toUpperCase();
    const av = avColor(r.admin_name||'');
    return `<tr>
      <td style="color:#94a3b8;font-size:12px">${i+1}</td>
      <td class="time-col" title="${esc(r.timestamp)}">${fmtTime(r.timestamp)}</td>
      <td><span class="badge" style="background:${esc(r.site_bg)};color:${esc(r.site_color)}">${esc(r.site_tag)}</span></td>
      <td style="white-space:nowrap">
        <span class="staff-av" style="background:${av}">${init}</span>${esc(r.admin_name)}
      </td>
      <td><span style="font-size:11.5px;color:#64748b">${esc(r.role||'')}</span></td>
      <td><span class="badge" style="background:${ts.bg};color:${ts.color}">${ts.icon} ${esc(r.type||'')}</span></td>
      <td style="font-weight:600">${esc(r.action||'')}</td>
      <td class="detail-col" title="${esc(r.detail)}">${esc(r.detail||'')}</td>
    </tr>`;
  }).join('');
}

function clearFilters(){
  ['fSearch','fSite','fType','fStaff','fFrom','fTo'].forEach(id=>{
    const el=document.getElementById(id);
    if(el) el.value='';
  });
  applyFilters();
}

function exportCSV(){
  const header=['#','Time','Site','Staff','Role','Type','Action','Detail'];
  const rows=_filtered.map((r,i)=>[
    i+1,
    r.timestamp,
    r.site_tag,
    r.admin_name,
    r.role,
    r.type,
    r.action,
    (r.detail||'').replace(/,/g,' ')
  ]);
  const csv=[header,...rows].map(r=>r.join(',')).join('\n');
  const a=document.createElement('a');
  a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(csv);
  a.download='staff-activity-'+new Date().toISOString().slice(0,10)+'.csv';
  a.click();
}

// Init
applyFilters();
</script>
</body>
</html>
