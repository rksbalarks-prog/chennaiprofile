<?php
// matrimony/user-panel.php
// Serves the Member Portal HTML. All data ops go through api/*.php via fetch().
require_once __DIR__ . '/config.php';
secureSession();

// ── Admin "My Account" token redemption ─────────────────────────────────────
// Admin panel issues a one-time token via api/admin/auth.php?action=my_account_token.
// Redeeming it sets the user-panel session AND a hard 30-minute expiry flag.
// Preserves any existing admin_id in the session so the admin isn't logged out
// of admin-panel in the other tab.
if (!empty($_GET['admin_token'])) {
    $tok = preg_replace('/[^a-f0-9]/', '', (string)$_GET['admin_token']);
    if (strlen($tok) === 32) {
        $cacheKey = 'admin_imp:' . $tok;
        $data = cache_get($cacheKey);
        if (is_array($data) && !empty($data['mobile'])) {
            cache_delete($cacheKey); // one-time use
            $_SESSION['mobile']                         = $data['mobile'];
            $_SESSION['contact_verified']               = true;
            $_SESSION['contact_mobile']                 = $data['mobile'];
            $_SESSION['contact_verified_at']            = time();
            $_SESSION['admin_impersonation']            = true;
            $_SESSION['admin_impersonation_expires_at'] = time() + 1800; // 30 min hard limit
            log_info('admin_my_account_redeemed', [
                'admin_id' => $data['admin_id'] ?? null,
                'mobile'   => $data['mobile'],
            ]);
            // Strip token from URL so it can't be bookmarked or shared.
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        }
    }
    // Invalid / expired token — still strip it from the URL
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// ── 30-min hard expiry enforcement for admin-impersonated sessions ──────────
if (!empty($_SESSION['admin_impersonation_expires_at'])
    && time() > $_SESSION['admin_impersonation_expires_at']) {
    foreach (['mobile','contact_verified','contact_mobile','contact_verified_at',
              'admin_impersonation','admin_impersonation_expires_at'] as $k) {
        unset($_SESSION[$k]);
    }
}

$autoLoginMobile = '';
if (!empty($_SESSION['contact_verified']) && !empty($_SESSION['contact_mobile'])
    && (time() - ($_SESSION['contact_verified_at'] ?? 0)) < 86400) {
    $autoLoginMobile = $_SESSION['contact_mobile'];
}

// Expose the impersonation state + remaining time to the JS boot code so it
// can show a banner and wire a client-side countdown.
$_impersonation = !empty($_SESSION['admin_impersonation']);
$_impersonationMsLeft = $_impersonation
    ? max(0, ($_SESSION['admin_impersonation_expires_at'] - time()) * 1000)
    : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Kumbakonam Free Matrimony - Member Portal</title>
<style>
:root{
  --accent:#c2553d;--accent2:#a84330;--accent-bg:#fdf0ed;
  --bg:#f7f5f1;--card:#fff;
  --ink:#1c1917;--ink2:#44403c;--ink3:#78716c;--ink4:#a8a29e;
  --border:#e7e1d9;--border2:#d4cdc4;
  --green:#2d6a4f;--green-bg:#edf5f1;
  --amber:#92400e;--amber-bg:#fef9c3;
  --sidebar:#1a1a2e;
  --radius:12px;--radius2:18px;
  --shadow:0 1px 4px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.08);
  --shadow2:0 8px 40px rgba(0,0,0,.14);
  --font:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
  --serif:Georgia,'Times New Roman',serif;
  --mono:'Courier New',monospace;
  --sidebar-w:232px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{-webkit-font-smoothing:antialiased}
body{font-family:var(--font);background:var(--bg);color:var(--ink);min-height:100vh}
button,input,select,textarea{font-family:inherit;font-size:inherit}
input,select,textarea{outline:none}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-thumb{background:var(--border2);border-radius:10px}

@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes shake{0%,100%{transform:none}20%,60%{transform:translateX(-6px)}40%,80%{transform:translateX(6px)}}
@keyframes slideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:none}}

.login-page{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(145deg,#1a1a2e 0%,#252550 55%,#3d1a14 100%);padding:20px;animation:fadeIn .5s}
.login-card{background:var(--card);border-radius:var(--radius2);width:420px;max-width:100%;box-shadow:0 32px 80px rgba(0,0,0,.35);overflow:hidden}
.login-top{background:var(--sidebar);padding:28px 32px 24px;text-align:center;position:relative}
.login-top::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--accent),#e8a87c,var(--accent))}
.login-brand{font-family:var(--serif);font-size:26px;color:#fff;letter-spacing:.04em}
.login-subb{font-size:11px;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-top:5px}
.login-body{padding:26px 30px 30px}
.lbl{display:block;font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--ink3);margin-bottom:6px}
.inp{width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:9px;background:#fff;color:var(--ink);font-size:14px;transition:border .15s,box-shadow .15s}
.inp:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(194,85,61,.1)}
.inp::placeholder{color:var(--ink4)}
.inp-row{margin-bottom:14px}
.inp-flex{display:flex;gap:8px}
.inp-flex .inp{flex:1}
.send-btn{padding:11px 16px;background:var(--accent);color:#fff;border:none;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;transition:all .15s;flex-shrink:0;opacity:.4;pointer-events:none}
.send-btn.ready{opacity:1;pointer-events:auto}
.send-btn:hover{background:var(--accent2)}
.send-btn.sent{background:var(--green);opacity:1;pointer-events:none}
.otp-sec{display:none;animation:slideDown .3s ease}
.otp-div{display:flex;align-items:center;gap:10px;margin:14px 0 12px}
.otp-div span{font-size:10.5px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--ink3);white-space:nowrap}
.otp-div hr{flex:1;border:none;border-top:1px solid var(--border)}
.otp-lbl{font-size:13px;color:var(--ink3);margin-bottom:10px;line-height:1.5}
.otp-boxes{display:flex;gap:8px;margin-bottom:10px;justify-content:center}
.otp-box{width:42px;height:46px;text-align:center;font-size:20px;font-weight:700;font-family:var(--mono);border:1.5px solid var(--border);border-radius:8px;background:#fff;color:var(--ink);transition:border .12s;flex-shrink:0}
.otp-box:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(194,85,61,.1)}
.otp-boxes.shake{animation:shake .35s ease}
.timer-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;font-size:12px}
.timer-txt{color:var(--ink4);font-family:var(--mono)}
.resend-btn{color:var(--accent);cursor:pointer;font-weight:600;display:none;background:none;border:none;font-size:12px;text-decoration:underline}
.main-btn{width:100%;padding:13px;background:var(--accent);color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;transition:all .15s;display:flex;align-items:center;justify-content:center;gap:7px}
.main-btn:hover{background:var(--accent2)}
.main-btn.g{background:var(--green)}.main-btn.g:hover{background:#235a3f}
.back-lnk{text-align:center;margin-top:10px;font-size:12px;color:var(--ink3)}
.back-lnk span{color:var(--accent);font-weight:600;text-decoration:underline;cursor:pointer}
.msg{padding:9px 12px;border-radius:8px;font-size:13px;margin-bottom:11px;border-left:3px solid;display:none}
.msg-err{background:#fee2e2;color:#991b1b;border-color:#dc2626}
.msg-ok{background:var(--green-bg);color:var(--green);border-color:var(--green)}

.app-shell{display:none;min-height:100vh}
.app-shell.open{display:flex;animation:fadeIn .35s}
.sidebar{width:var(--sidebar-w);background:var(--sidebar);min-height:100vh;display:flex;flex-direction:column;position:sticky;top:0;height:100vh;flex-shrink:0;overflow-y:auto}
.sb-brand{padding:20px 16px 14px;border-bottom:1px solid rgba(255,255,255,.07)}
.sb-brand h1{font-family:var(--serif);font-size:19px;color:#fff;letter-spacing:.03em}
.sb-brand span{display:block;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.28);margin-top:2px}
.sb-user{margin:10px 9px 5px;background:rgba(255,255,255,.06);border-radius:9px;padding:9px 11px;display:flex;align-items:center;gap:8px}
.sb-av{width:30px;height:30px;border-radius:50%;background:rgba(194,85,61,.4);border:1.5px solid rgba(194,85,61,.6);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0}
.sb-name{font-size:12.5px;font-weight:600;color:rgba(255,255,255,.85);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sb-mob{font-size:10px;color:rgba(255,255,255,.32);font-family:var(--mono);margin-top:1px}
.sb-nav{flex:1;padding:5px 7px}
.sb-grp{font-size:9.5px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.2);padding:10px 9px 3px}
.sb-btn{width:100%;display:flex;align-items:center;gap:8px;padding:8px 10px;border:none;background:transparent;color:rgba(255,255,255,.48);font-size:12.5px;cursor:pointer;border-radius:7px;transition:all .13s;text-align:left}
.sb-btn svg{opacity:.55;flex-shrink:0}
.sb-btn:hover{background:rgba(255,255,255,.07);color:rgba(255,255,255,.85)}
.sb-btn:hover svg{opacity:1}
.sb-btn.active{background:var(--accent);color:#fff}.sb-btn.active svg{opacity:1}
.sb-parent[aria-expanded="true"] .sb-caret{transform:rotate(180deg)}
.sb-sub{display:flex;flex-direction:column;gap:1px;padding:1px 0 3px 6px;border-left:1.5px solid rgba(255,255,255,.07);margin:0 0 2px 14px}
.sb-sub[hidden]{display:none}
.sb-child{padding-left:14px;font-size:12px}
.sb-bullet{color:rgba(255,255,255,.35);font-size:14px;line-height:1;margin-right:2px}
.sb-foot{padding:11px 15px;border-top:1px solid rgba(255,255,255,.07);display:flex;align-items:center;justify-content:space-between}
.sb-ver{font-size:10px;color:rgba(255,255,255,.16);font-family:var(--mono)}
.sb-logout{background:none;border:none;color:rgba(255,255,255,.28);font-size:11.5px;cursor:pointer;display:flex;align-items:center;gap:4px;transition:color .13s;padding:3px 7px;border-radius:5px}
.sb-logout:hover{color:#fff;background:rgba(194,85,61,.22)}

.u-main{flex:1;min-width:0;display:flex;flex-direction:column;overflow:hidden}
.topbar{background:var(--card);border-bottom:1px solid var(--border);height:52px;display:flex;align-items:center;padding:0 26px;flex-shrink:0;justify-content:space-between;position:sticky;top:0;z-index:10}
.topbar-title{font-family:var(--serif);font-size:1.2rem;color:var(--ink);font-weight:400}
.content{flex:1;padding:22px 26px;overflow-y:auto}
.u-section{display:none}
.u-section.active{display:block;animation:fadeUp .25s ease}

.btn{display:inline-flex;align-items:center;gap:5px;padding:8px 16px;border-radius:8px;font-size:12.5px;font-weight:600;cursor:pointer;transition:all .14s;border:1.5px solid transparent}
.btn-primary{background:var(--accent);color:#fff;border-color:var(--accent)}.btn-primary:hover{background:var(--accent2)}
.btn-outline{background:#fff;color:var(--ink2);border-color:var(--border2)}.btn-outline:hover{border-color:var(--ink3)}
.btn-green{background:var(--green);color:#fff}.btn-green:hover{background:#235a3f}
.btn-danger{background:#dc2626;color:#fff}.btn-danger:hover{background:#b91c1c}
.btn-ghost{background:none;color:var(--ink3);border-color:transparent}.btn-ghost:hover{background:var(--bg)}
.btn-sm{padding:6px 12px;font-size:12px}
/* Topbar actions — single row, horizontally scrollable when wider than the
   topbar can fit (mobile). nowrap so buttons don't drop under the title;
   webkit scroll-snap keeps each button aligned at its left edge after a swipe. */
.action-row{display:flex;gap:7px;flex-wrap:nowrap;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;max-width:100%;scrollbar-width:thin;padding:2px 0;scroll-snap-type:x proximity}
.action-row > *{flex-shrink:0;scroll-snap-align:start}
.action-row::-webkit-scrollbar{height:4px}
.action-row::-webkit-scrollbar-thumb{background:var(--border2);border-radius:4px}

/* Generic horizontally-scrollable filter chip row — applied via .chip-row
   wherever a row of chips might overflow on mobile. */
.chip-row{display:flex;gap:6px;flex-wrap:nowrap;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;padding-bottom:4px;scrollbar-width:thin;scroll-snap-type:x proximity}
.chip-row > *{flex-shrink:0;scroll-snap-align:start}
.chip-row::-webkit-scrollbar{height:4px}
.chip-row::-webkit-scrollbar-thumb{background:var(--border2);border-radius:4px}

/* Loading animations: spinner ring + soft skeleton block. Applied wherever
   an async fetch/save is in flight so the UI never sits silent. */
@keyframes uSpin{to{transform:rotate(360deg)}}
.u-spinner{display:inline-block;width:14px;height:14px;border:2.5px solid rgba(0,0,0,0.12);border-top-color:var(--accent);border-radius:50%;animation:uSpin .7s linear infinite;vertical-align:-3px}
.u-spinner.lg{width:28px;height:28px;border-width:3px}
.u-spinner.on-dark{border-color:rgba(255,255,255,0.35);border-top-color:#fff}
.u-loading-overlay{position:absolute;inset:0;background:rgba(255,255,255,0.72);display:flex;align-items:center;justify-content:center;gap:10px;font-size:13px;font-weight:600;color:var(--ink2);z-index:5;border-radius:inherit;backdrop-filter:blur(2px)}
@keyframes uShimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
.u-skeleton{background:linear-gradient(90deg,#f0f0f0 0%,#fafafa 50%,#f0f0f0 100%);background-size:200% 100%;animation:uShimmer 1.4s ease-in-out infinite;border-radius:6px;color:transparent!important;display:inline-block}

.no-profile-wrap{max-width:500px;margin:0 auto;text-align:center;padding:50px 24px;background:var(--card);border-radius:var(--radius2);box-shadow:var(--shadow);border:2px dashed var(--border2);animation:fadeUp .3s ease}
.no-profile-icon{font-size:48px;opacity:.45;margin-bottom:14px}
.no-profile-chip{font-family:var(--mono);font-size:14px;font-weight:700;color:var(--accent);background:var(--accent-bg);padding:4px 14px;border-radius:7px;display:inline-block;margin-bottom:14px}

.profile-card{background:var(--card);border-radius:var(--radius2);box-shadow:var(--shadow);overflow:hidden;margin-bottom:20px}
.p-banner{height:100px;background:linear-gradient(135deg,var(--sidebar) 0%,#2d2d5e 50%,#3d1a14 100%);position:relative}
.p-av-wrap{position:absolute;bottom:-34px;left:22px}
.p-av{width:68px;height:68px;border-radius:50%;border:4px solid #fff;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#fff;box-shadow:0 4px 14px rgba(0,0,0,.18)}
.p-body{padding:42px 22px 22px}
.p-name{font-family:var(--serif);font-size:1.4rem;color:var(--ink)}
.p-meta{font-size:12.5px;color:var(--ink3);margin-top:3px}
.p-badges{display:flex;flex-wrap:wrap;gap:5px;margin-top:9px}
.badge{display:inline-flex;align-items:center;gap:3px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600}
.badge-green{background:var(--green-bg);color:var(--green)}
.badge-amber{background:var(--amber-bg);color:var(--amber)}
.badge-accent{background:var(--accent-bg);color:var(--accent)}
.badge-gray{background:#f3f4f6;color:var(--ink3)}
.badge-blue{background:#eff6ff;color:#2563eb}

.det-sec{margin-top:22px}
.det-sec-title{font-size:10.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--ink3);background:var(--bg);padding:5px 11px;border-radius:6px;display:inline-block;margin-bottom:12px}
.det-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(195px,1fr));gap:10px}
.det-item{background:var(--bg);border-radius:8px;padding:10px 13px;border:1px solid var(--border)}
.det-lbl{font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink3);margin-bottom:2px}
.det-val{font-size:13px;font-weight:600;color:var(--ink)}

.u-card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:16px}
.u-card-head{padding:13px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.u-card-title{font-weight:700;font-size:13px}
.u-card-body{padding:16px 18px}
.u-tw{overflow-x:auto}
.u-tbl{width:100%;border-collapse:collapse;font-size:12.5px}
.u-tbl th{background:var(--bg);padding:8px 13px;text-align:left;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink3);border-bottom:1px solid var(--border);white-space:nowrap}
.u-tbl td{padding:10px 13px;border-bottom:1px solid #f5f3ef;color:var(--ink2);vertical-align:middle}
.u-tbl tr:last-child td{border-bottom:none}
.u-tbl tbody tr:hover td{background:#faf9f7}

.stats-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:11px;margin-bottom:16px}
.stat-card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:14px 16px;border-left:3px solid var(--border2)}
.stat-num{font-family:var(--serif);font-size:1.7rem;font-weight:400;line-height:1}
.stat-lbl{font-size:11px;color:var(--ink3);margin-top:3px;text-transform:uppercase;letter-spacing:.06em;font-weight:600}

.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);backdrop-filter:blur(3px);display:flex;align-items:flex-start;justify-content:center;padding:30px 16px;z-index:1000;overflow-y:auto;opacity:0;pointer-events:none;transition:opacity .2s}
.modal-overlay.open{opacity:1;pointer-events:auto}
.modal{background:var(--card);border-radius:var(--radius2);width:100%;max-width:660px;box-shadow:var(--shadow2);transform:translateY(12px);transition:transform .25s ease;overflow:hidden}
.modal-overlay.open .modal{transform:none}
.modal-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--bg)}
.modal-title{font-family:var(--serif);font-size:1.05rem;font-weight:400}
.modal-x{background:none;border:none;cursor:pointer;color:var(--ink4);font-size:1.2rem}
.modal-x:hover{color:var(--ink)}
.modal-body{padding:20px;max-height:70vh;overflow-y:auto}
.modal-foot{padding:13px 20px;border-top:1px solid var(--border);display:flex;gap:7px;justify-content:flex-end;background:var(--bg)}

/* Full-page modal variant — turns a modal overlay into a page-like experience
   (no backdrop, fills viewport, no border-radius, no transform anim) */
.modal-overlay.modal-page{background:var(--bg);backdrop-filter:none;padding:0;align-items:stretch;overflow:hidden}
.modal-overlay.modal-page .modal{max-width:none;width:100%;height:100vh;max-height:100vh;border-radius:0;box-shadow:none;transform:none;display:flex;flex-direction:column}
.modal-overlay.modal-page .modal-head{background:#fff;padding:14px 22px;border-bottom:1px solid var(--border);position:sticky;top:0;z-index:2}
.modal-overlay.modal-page .modal-title{font-family:var(--serif);font-size:1.15rem;font-weight:600}
.modal-overlay.modal-page .modal-body{flex:1;max-height:none;padding:22px;max-width:980px;margin:0 auto;width:100%;box-sizing:border-box}
.modal-overlay.modal-page .modal-foot{background:#fff;position:sticky;bottom:0;padding:14px 22px;box-shadow:0 -4px 14px rgba(0,0,0,.05)}
.modal-back{background:transparent;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:8px;color:var(--ink);font-size:14px;font-weight:600;padding:6px 10px;border-radius:8px;transition:background .15s}
.modal-back:hover{background:var(--bg)}
.msec{font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#fff;background:var(--sidebar);padding:5px 11px;border-radius:5px;margin:16px 0 10px;display:inline-block}
.fg{margin-bottom:13px}
.fg2{display:grid;grid-template-columns:1fr 1fr;gap:0 12px}
.fg3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px}
.flbl{display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink3);margin-bottom:4px}
.flbl .req{color:var(--accent)}
.finp,.fsel,.fta{width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;background:#fff;color:var(--ink);font-size:13px;transition:border .13s}
.finp:focus,.fsel:focus,.fta:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(194,85,61,.08)}
.fta{resize:vertical;min-height:60px}

.u-popup{position:fixed;top:16px;left:50%;transform:translateX(-50%) translateY(-80px);z-index:99999;min-width:290px;max-width:390px;width:90%;border-radius:11px;padding:11px 14px;display:flex;align-items:flex-start;gap:9px;background:#fff;box-shadow:var(--shadow2);transition:transform .28s cubic-bezier(.34,1.56,.64,1),opacity .22s;opacity:0;pointer-events:none}
.u-popup.show{transform:translateX(-50%) translateY(0);opacity:1;pointer-events:auto}
.u-popup.pop-err{border-left:4px solid #dc2626}.u-popup.pop-ok{border-left:4px solid var(--green)}.u-popup.pop-warn{border-left:4px solid #d97706}
.u-popup-title{font-weight:700;font-size:12.5px;margin-bottom:1px}
.u-popup-msg{font-size:11.5px;color:var(--ink3)}
.u-popup-x{margin-left:auto;flex-shrink:0;background:none;border:none;font-size:16px;cursor:pointer;color:var(--ink4);padding:0 0 0 5px}
#uToast{position:fixed;bottom:20px;right:20px;background:var(--ink);color:#fff;padding:10px 16px;border-radius:9px;font-size:13px;font-weight:500;box-shadow:var(--shadow2);transform:translateY(60px);opacity:0;transition:all .3s;z-index:9999;display:flex;align-items:center;gap:6px}

.plan-card{transition:all .25s ease}
.plan-card:hover{transform:translateY(-4px);box-shadow:0 12px 28px rgba(0,0,0,.12);border-color:#cbd5e1 !important}
.plan-card.selected{border-color:var(--accent) !important;box-shadow:0 0 0 4px rgba(194,85,61,.18),0 12px 28px rgba(194,85,61,.18);transform:translateY(-4px)}
.plan-card.selected .plan-card-cta{background:var(--accent) !important;color:#fff !important;border-style:solid !important;border-color:var(--accent) !important}
.plan-card-badge{position:absolute;top:-8px;right:12px;background:var(--accent);color:#fff;font-size:10px;font-weight:700;padding:2px 9px;border-radius:20px}
.pay-step{display:flex;flex-direction:column;align-items:center;gap:5px}
.pay-step-circle{width:32px;height:32px;border-radius:50%;border:2px solid var(--border2);background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--ink4);transition:all .25s}
.pay-step-label{font-size:11px;color:var(--ink4);font-weight:500;white-space:nowrap}
.pay-step-done .pay-step-circle{background:var(--green);border-color:var(--green);color:#fff}
.pay-step-done .pay-step-label{color:var(--green);font-weight:600}
.pay-step-line{flex:1;height:2px;background:var(--border);margin-top:-18px;transition:background .25s}
.pay-step-line-active{background:var(--green)}
.pay-opt-card{background:var(--card);border:1.5px solid var(--border);border-radius:var(--radius);overflow:hidden;transition:border .15s}
.pay-opt-header{padding:14px 18px;display:flex;align-items:center;gap:10px;cursor:pointer;user-select:none}
.pay-opt-header:hover{background:#faf9f7}
.pay-opt-body{display:none;border-top:1px solid var(--border);padding:18px}
.pay-opt-body.open{display:block;animation:fadeUp .2s ease}
.pay-opt-card.expanded{border-color:var(--accent)}
.pay-opt-card.expanded .pay-opt-header{background:var(--accent-bg)}
.bank-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid var(--border)}
.bank-row:last-child{border-bottom:none}
.bank-lbl{font-size:11.5px;color:var(--ink3);font-weight:600;text-transform:uppercase;letter-spacing:.06em}
.bank-val{font-size:13.5px;font-weight:700;color:var(--ink);font-family:var(--mono)}
.copy-btn{background:var(--bg);border:1px solid var(--border);border-radius:6px;padding:3px 9px;font-size:11.5px;cursor:pointer;color:var(--ink2);transition:all .12s;white-space:nowrap}
.copy-btn:hover{background:var(--accent);color:#fff;border-color:var(--accent)}
.pay-deep-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:13px;border-radius:10px;border:none;font-size:14.5px;font-weight:700;cursor:pointer;transition:all .15s;margin-top:4px;color:#fff}
.pay-deep-btn:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(0,0,0,.18)}
.pay-notes-banner{background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;font-size:12.5px;color:#92400e;margin-top:12px;line-height:1.6}
.pay-i-paid-btn{width:100%;padding:12px;border-radius:9px;border:1.5px solid var(--green);background:var(--green-bg);color:var(--green);font-size:13.5px;font-weight:700;cursor:pointer;transition:all .15s;margin-top:14px;display:flex;align-items:center;justify-content:center;gap:7px}
.pay-i-paid-btn:hover{background:var(--green);color:#fff}
.loading-spinner{display:inline-block;width:16px;height:16px;border:2px solid rgba(0,0,0,.1);border-top-color:var(--accent);border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

/* ===== Wizard custom dropdowns ===== */
.cdd{position:relative}
.cdd-toggle{width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;background:#fff;color:var(--ink);font-size:13px;font-family:inherit;text-align:left;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:8px;transition:border .13s,box-shadow .13s}
.cdd-toggle:hover{border-color:var(--ink4)}
.cdd-toggle:focus,.cdd.open .cdd-toggle{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(194,85,61,.08)}
.cdd-toggle.ph{color:var(--ink4)}
.cdd-toggle::after{content:'';width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid var(--ink3);flex-shrink:0;transition:transform .15s}
.cdd.open .cdd-toggle::after{transform:rotate(180deg)}
.cdd-menu{position:absolute;top:calc(100% + 4px);left:0;right:0;max-height:280px;overflow-y:auto;background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.14);z-index:50;padding:0 0 4px}
.cdd-nav{display:flex;justify-content:space-between;gap:6px;padding:6px 8px;border-bottom:1px solid var(--border);position:sticky;top:0;background:#fff;z-index:2}
.cdd-navbtn{flex:1;font-size:11.5px;font-weight:600;background:var(--bg);border:1px solid var(--border);border-radius:6px;padding:5px 10px;cursor:pointer;color:var(--ink3);transition:all .12s}
.cdd-navbtn:hover{background:var(--accent);border-color:var(--accent);color:#fff}
.cdd-group{font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink4);padding:8px 12px 4px;background:#fafaf7}
.cdd-opt{display:block;width:100%;text-align:left;padding:8px 12px;background:transparent;border:none;font-size:12.5px;color:var(--ink);cursor:pointer;font-family:inherit}
.cdd-opt:hover{background:#fef2ed;color:var(--accent)}
.cdd-opt.active{background:var(--accent);color:#fff;font-weight:600}
.cdd-opt.active:hover{background:var(--accent);color:#fff}
.cdd-hidden-sel{position:absolute;opacity:0;pointer-events:none;height:0;width:0;padding:0;border:0;margin:0}

@media print{.sidebar,.topbar,.action-row,.u-popup,#uToast{display:none!important}.app-shell.open{display:block}.u-main{display:block}.content{padding:0}.u-section:not(.active){display:none}.profile-card{box-shadow:none}}
@media(max-width:768px){
  .sidebar{display:none;position:fixed;top:0;left:0;bottom:0;z-index:2000;width:260px;box-shadow:4px 0 20px rgba(0,0,0,0.3)}
  .sidebar.mob-open{display:flex}
  .mob-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:1999}
  .mob-overlay.open{display:block}
  .mob-topbar{display:flex!important;align-items:center;gap:10px;background:#1a1a2e;padding:10px 14px;position:fixed;top:0;left:0;right:0;z-index:100;height:50px}
  .mob-topbar-title{color:#fff;font-size:15px;font-weight:700;flex:1}
  .mob-topbar-sub{color:rgba(255,255,255,0.5);font-size:10px}
  .mob-hamburger{width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.1);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center}
  .mob-hamburger svg{stroke:#E8B76A}
  .u-main{padding-top:50px}
  .content{padding:14px}
  .fg2,.fg3{grid-template-columns:1fr}
  /* Modal mobile fix */
  .modal{width:95%!important;max-width:100%!important;margin:10px!important;border-radius:12px!important}
  .modal-body{padding:14px!important;max-height:75vh!important}
  .modal-head{padding:14px 16px!important}
  .modal-foot{padding:10px 16px!important}
  .modal-body .fg,
  .modal-body [style*="grid-template-columns"]{grid-template-columns:1fr 1fr!important}
  .modal-body .fg label,.modal-body .flbl{font-size:10px!important}
  .modal-body input,.modal-body select,.modal-body textarea{font-size:14px!important;padding:8px 10px!important}
  .modal-body .sec-title{font-size:12px!important;padding:5px 10px!important}
}
</style>
</head>
<body>

<div id="uPopup" class="u-popup">
  <div id="uPopIcon" style="font-size:19px;flex-shrink:0"></div>
  <div style="flex:1"><div class="u-popup-title" id="uPopTitle"></div><div class="u-popup-msg" id="uPopMsg"></div></div>
  <button class="u-popup-x" onclick="closePopup()">&#215;</button>
</div>
<div id="uToast"></div>

<!-- Session-check splash: hides both login and shell until boot() resolves.
     Prevents login page flashing on refresh when the user is still logged in. -->
<div id="sessionCheckSplash" style="position:fixed;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:linear-gradient(145deg,#1a1a2e 0%,#252550 55%,#3d1a14 100%);z-index:99999;color:#fff;font-family:system-ui,sans-serif">
  <div style="width:44px;height:44px;border:3px solid rgba(255,255,255,0.15);border-top-color:#c2553d;border-radius:50%;animation:usr_spin 0.8s linear infinite"></div>
  <div style="margin-top:16px;font-size:13px;color:rgba(255,255,255,0.7);letter-spacing:0.3px">Checking session…</div>
  <style>@keyframes usr_spin{to{transform:rotate(360deg)}}</style>
</div>

<!-- LOGIN -->
<div id="loginPage" class="login-page" style="display:none">
  <div class="login-card">
    <div class="login-top">
      <div class="login-brand">Kumbakonam</div>
      <div class="login-subb">Free Matrimony</div>
    </div>
    <div class="login-body">
      <div id="payLinkBanner" style="display:none;background:linear-gradient(135deg,#16a34a,#15803d);border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#fff;text-align:center">
        <div style="font-size:20px;margin-bottom:4px">&#128179;</div>
        <div style="font-weight:700;font-size:14px;margin-bottom:2px">Payment Link</div>
        <div style="font-size:12px;opacity:.85" id="payLinkBannerText">Log in to proceed with your registration payment</div>
      </div>
      <div id="loginMsg" class="msg"></div>
      <div class="inp-row">
        <label class="lbl">Mobile Number</label>
        <div class="inp-flex">
          <input class="inp" id="lg_mobile" type="tel" maxlength="10" placeholder="Enter 10-digit mobile" style="letter-spacing:2px;font-size:15px;font-weight:600" oninput="onMobileType(this)" onkeydown="if(event.key==='Enter')sendOtp()">
          <button class="send-btn" id="sendOtpBtn" onclick="sendOtp()">Send OTP</button>
        </div>
      </div>
      <div id="otpSection" class="otp-sec">
        <div class="otp-div"><hr><span>Enter OTP</span><hr></div>
        <div class="otp-lbl" id="otpSentLbl"></div>
        <div class="otp-boxes" id="otpBoxes">
          <input class="otp-box" id="ob1" maxlength="1" type="text" inputmode="numeric" oninput="omov(this,'ob2')" onkeydown="obk(event,this,'')">
          <input class="otp-box" id="ob2" maxlength="1" type="text" inputmode="numeric" oninput="omov(this,'ob3')" onkeydown="obk(event,this,'ob1')">
          <input class="otp-box" id="ob3" maxlength="1" type="text" inputmode="numeric" oninput="omov(this,'ob4')" onkeydown="obk(event,this,'ob2')">
          <input class="otp-box" id="ob4" maxlength="1" type="text" inputmode="numeric" oninput="omov(this,'')"    onkeydown="obk(event,this,'ob3')">
        </div>
        <div class="timer-row">
          <span class="timer-txt" id="otpTimer"></span>
          <button class="resend-btn" id="resendBtn" onclick="resendOtp()">Resend OTP</button>
        </div>
        <button class="main-btn g" onclick="verifyOtp()">Verify &amp; Login</button>
        <div class="back-lnk"><span onclick="resetLogin()">Change Number</span></div>
      </div>
    </div>
  </div>
</div>

<!-- APP SHELL -->
<div class="app-shell" id="appShell">
  <!-- Mobile Top Bar -->
  <div class="mob-topbar" style="display:none">
    <button onclick="if(history.length>1){history.back();}else{location.href='/';}" aria-label="Back" style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);color:#fff;width:34px;height:34px;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <button class="mob-hamburger" onclick="document.querySelector('.sidebar').classList.add('mob-open');document.getElementById('mobOverlay').classList.add('open')">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <div style="flex:1"><div class="mob-topbar-title">Kumbakonam</div><div class="mob-topbar-sub">FREE MATRIMONY</div></div>
    <button onclick="doLogout()" style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);color:#fff;padding:6px 12px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:4px;white-space:nowrap">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
      Sign Out
    </button>
  </div>
  <div class="mob-overlay" id="mobOverlay" onclick="document.querySelector('.sidebar').classList.remove('mob-open');this.classList.remove('open')"></div>
  <nav class="sidebar">
    <div class="sb-brand"><h1>Kumbakonam</h1><span>Free Matrimony</span></div>
    <div class="sb-user">
      <div class="sb-av" id="sbAv">?</div>
      <div><div class="sb-name" id="sbName">-</div><div class="sb-mob" id="sbMob">-</div></div>
    </div>
    <nav class="sb-nav">
      <a href="/" class="sb-btn" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:8px;width:100%;margin-bottom:4px;background:rgba(232,183,106,0.12);border:1px solid rgba(232,183,106,0.25)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#E8B76A" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span style="color:#E8B76A;font-weight:600">Home</span>
      </a>
      <div class="sb-grp">My Account</div>
      <button class="sb-btn active" data-page="page_profile" onclick="showSec('myProfile',this)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        My Profile
      </button>
      <button class="sb-btn" data-page="page_suggestions" onclick="showSec('suggestions',this)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        Suggestions
      </button>
      <button class="sb-btn sb-parent" data-page="page_matches" onclick="toggleSubmenu(this)" aria-expanded="false">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        Matches
        <svg class="sb-caret" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left:auto;transition:transform .2s"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="sb-sub" data-parent="page_matches" hidden>
        <button class="sb-btn sb-child" data-page="page_matches" onclick="showSec('basicMatches',this)">
          <span class="sb-bullet">•</span> Basic Matches
        </button>
        <button class="sb-btn sb-child" data-page="page_matches" onclick="showSec('mutualMatches',this)">
          <span class="sb-bullet">•</span> Mutual Matches
        </button>
      </div>
      <button class="sb-btn" data-page="page_allprofiles" onclick="showSec('allProfiles',this)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><circle cx="17" cy="7" r="3"/><path d="M21 21v-1.5a3.5 3.5 0 0 0-3.5-3.5"/></svg>
        All Profiles
      </button>
      <button class="sb-btn" data-page="page_bills" onclick="showSec('myBills',this)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        My Bills
      </button>
      <button class="sb-btn" data-page="page_addorder" onclick="showSec('addOrder',this)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        Pay Later
      </button>
      <button class="sb-btn" data-page="page_activity" onclick="showSec('myActivity',this)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        My Activity
      </button>
      <button class="sb-btn" data-page="page_loginhistory" onclick="showSec('loginHistory',this)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
        Login History
      </button>
      <button class="sb-btn" data-page="page_myreports" onclick="showSec('myReports',this)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        My Reports
      </button>
      <div class="sb-grp">Logs</div>
      <button class="sb-btn" data-page="page_profileviewlog" onclick="showSec('profileViewLog',this)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Profile View Log
      </button>
      <button class="sb-btn" data-page="page_contactlog" onclick="showSec('contactLog',this)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.36 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.27 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z"/></svg>
        Contact View Log
      </button>
      <div class="sb-grp">Settings</div>
      <button class="sb-btn" data-page="page_settings" onclick="showSec('mySettings',this)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M22 12h-2M4 12H2M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M12 22v-2M12 4V2"/></svg>
        Settings
      </button>
    </nav>
    <datalist id="partner_qual_list">
      <option value="Any"><option value="Any Degree"><option value="Any Bachelor Degree"><option value="Any Masters Degree">
      <option value="B.E"><option value="B.Tech."><option value="B.Sc."><option value="B.Com."><option value="B.A.">
      <option value="BCA"><option value="BBA"><option value="MBA"><option value="MCA"><option value="M.E">
      <option value="M.Tech."><option value="M.Sc."><option value="M.A."><option value="MCom"><option value="MBBS">
      <option value="BDS"><option value="B.Pharm"><option value="B.Ed."><option value="LL.B."><option value="CA">
      <option value="Ph.D."><option value="Diploma"><option value="ITI"><option value="Higher Secondary/High School">
      <option value="Doesn't Matter"><option value="Not Applicable">
    </datalist>
    <datalist id="partner_job_list">
      <option value="Any"><option value="Any Job"><option value="Government Job"><option value="Private Job">
      <option value="Self Employed"><option value="Business"><option value="IT Professional">
      <option value="Software Engineer"><option value="Doctor"><option value="Engineer"><option value="Teacher">
      <option value="Lawyer"><option value="Bank Employee"><option value="Police"><option value="Army">
      <option value="Employed"><option value="Well Settled"><option value="Doesn't Matter">
    </datalist>
    <datalist id="occupation_list">
      <option value="Government Employee"><option value="Private Employee"><option value="Self Employed">
      <option value="Business"><option value="Businessman"><option value="Farmer"><option value="Agriculture">
      <option value="Teacher"><option value="Professor"><option value="Doctor"><option value="Engineer">
      <option value="Lawyer"><option value="Advocate"><option value="Police"><option value="Army">
      <option value="Navy"><option value="Air Force"><option value="Bank Employee"><option value="Accountant">
      <option value="Driver"><option value="Mechanic"><option value="Electrician"><option value="Plumber">
      <option value="Carpenter"><option value="Mason"><option value="Tailor"><option value="Shopkeeper">
      <option value="Contractor"><option value="Supervisor"><option value="Manager"><option value="Clerk">
      <option value="Home Maker"><option value="Housewife"><option value="Retired"><option value="Pensioner">
      <option value="Daily Wage"><option value="Coolie"><option value="Auto Driver"><option value="Taxi Driver">
      <option value="Real Estate"><option value="Jeweller"><option value="IT Professional">
      <option value="Software Engineer"><option value="Not Employed"><option value="Passed Away">
      <option value="Late"><option value="Expired"><option value="Not Applicable">
    </datalist>
    <datalist id="nativity_list">
      <option value="India"><option value="Pondicherry"><option value="Chennai"><option value="Tamil Nadu">
      <option value="France"><option value="Singapore"><option value="Malaysia"><option value="UAE">
      <option value="Kuwait"><option value="Saudi Arabia"><option value="Qatar"><option value="Bahrain">
      <option value="Oman"><option value="USA"><option value="UK"><option value="Canada">
      <option value="Australia"><option value="Germany"><option value="Japan"><option value="Sri Lanka">
      <option value="Reunion"><option value="Other">
    </datalist>
    <div class="sb-foot">
      <span class="sb-ver">v2.0</span>
      <button class="sb-logout" onclick="doLogout()">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        Sign out
      </button>
    </div>
  </nav>
  <div class="u-main">
    <div class="topbar">
      <div class="topbar-title" id="topbarTitle">My Profile</div>
      <div class="action-row" id="topbarActions"></div>
    </div>
    <div class="content">
      <div class="u-section active" id="myProfileSection"></div>
      <div class="u-section" id="suggestionsSection">
        <div class="chip-row" style="margin-bottom:14px;align-items:center">
          <button class="btn btn-sm" onclick="filterSuggestions('all')" id="sgFiltAll" style="background:var(--accent);color:#fff;border:none;padding:6px 14px;border-radius:16px;font-size:12px;font-weight:600">All</button>
          <button class="btn btn-sm" onclick="filterSuggestions('interested')" id="sgFiltInterested" style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;padding:6px 14px;border-radius:16px;font-size:12px;font-weight:600">💚 Interested</button>
          <button class="btn btn-sm" onclick="filterSuggestions('later')" id="sgFiltLater" style="background:#fefce8;color:#a16207;border:1px solid #fde68a;padding:6px 14px;border-radius:16px;font-size:12px;font-weight:600">🕐 Later</button>
          <button class="btn btn-sm" onclick="filterSuggestions('not_interested')" id="sgFiltNot" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:6px 14px;border-radius:16px;font-size:12px;font-weight:600">❌ Not Interested</button>
          <button class="btn btn-sm" onclick="filterSuggestions('untagged')" id="sgFiltUntagged" style="background:#f3f4f6;color:#6b7280;border:1px solid #e5e7eb;padding:6px 14px;border-radius:16px;font-size:12px;font-weight:600">🏷 Untagged</button>
          <span style="margin-left:auto;display:inline-flex;align-items:center;gap:6px;flex-shrink:0">
            <span style="font-size:11px;color:var(--ink3)">Sort:</span>
            <select id="sgSort" onchange="renderSuggestionsUI()" style="padding:5px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:11px;font-weight:600;color:#374151;cursor:pointer">
              <option value="rating_desc">★ Rating High → Low</option>
              <option value="rating_asc">★ Rating Low → High</option>
              <option value="age_asc">Age Young → Old</option>
              <option value="age_desc">Age Old → Young</option>
              <option value="name_asc">Name A → Z</option>
            </select>
          </span>
        </div>
        <div id="suggestionsContent" style="display:flex;flex-direction:column;gap:16px"></div>
      </div>
      <div class="u-section" id="basicMatchesSection">
        <div class="u-card" style="padding:14px 16px;margin-bottom:14px;background:linear-gradient(135deg,#fef9c3 0%,#fef3c7 100%);border-left:3px solid #d97706">
          <div style="font-weight:700;color:#92400e;font-size:13px;margin-bottom:4px">💑 Basic Matches</div>
          <div style="font-size:11.5px;color:#78350f;line-height:1.5">Profiles that satisfy <b>your</b> partner preferences (caste, age, qualification, marital status, etc.).</div>
        </div>
        <div id="basicMatchesContent" style="display:flex;flex-direction:column;gap:10px"></div>
      </div>
      <div class="u-section" id="mutualMatchesSection">
        <div class="u-card" style="padding:14px 16px;margin-bottom:14px;background:linear-gradient(135deg,#dcfce7 0%,#bbf7d0 100%);border-left:3px solid #059669">
          <div style="font-weight:700;color:#065f46;font-size:13px;margin-bottom:4px">💞 Mutual Matches</div>
          <div style="font-size:11.5px;color:#064e3b;line-height:1.5">Two-way fit: profiles that match <b>your</b> preferences <i>and</i> whose own preferences are satisfied by <b>your basic details</b>.</div>
        </div>
        <div id="mutualMatchesContent" style="display:flex;flex-direction:column;gap:10px"></div>
      </div>
      <div class="u-section" id="allProfilesSection">
        <!-- Embeds the public homepage (filter tabs + cards + search) in headerless mode -->
        <iframe id="allProfilesFrame" src="about:blank" title="Browse profiles" style="width:100%;height:calc(100vh - 130px);min-height:520px;border:1px solid var(--border);border-radius:12px;background:#fff;display:block"></iframe>
      </div>
      <div class="u-section" id="myBillsSection">
        <div class="stats-row" id="billStats"></div>
        <div class="u-card"><div class="u-card-head"><span class="u-card-title">Active Plan</span></div><div class="u-card-body" id="activePlanBody"></div></div>
        <div class="u-card">
          <div class="u-card-head"><span class="u-card-title">Bill History</span><span id="billHistBadge" class="badge badge-gray">0</span></div>
          <div class="u-tw"><table class="u-tbl"><thead><tr><th>#</th><th>Plan</th><th>Amount</th><th>Payment</th><th>Date</th><th>Expiry</th><th>By</th><th>Status</th></tr></thead><tbody id="billTbody"></tbody></table></div>
        </div>
      </div>
      <div class="u-section" id="myActivitySection">
        <div class="stats-row" id="actStats"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div class="u-card"><div class="u-card-head"><span class="u-card-title">Profiles Viewed</span><span id="pvBadge" class="badge badge-blue">0</span></div><div class="u-tw"><table class="u-tbl"><thead><tr><th>#</th><th>CP ID</th><th>Name</th><th>Date &amp; Time</th><th>Duration</th></tr></thead><tbody id="pvTbody"></tbody></table></div></div>
          <div class="u-card"><div class="u-card-head"><span class="u-card-title">Contacts Viewed</span><span id="cvBadge" class="badge badge-green">0</span></div><div class="u-tw"><table class="u-tbl"><thead><tr><th>#</th><th>CP ID</th><th>Name</th><th>Date &amp; Time</th></tr></thead><tbody id="cvTbody"></tbody></table></div></div>
        </div>
        <div class="u-card"><div class="u-card-head"><span class="u-card-title">Who Viewed My Profile</span><span id="vbBadge" class="badge badge-gray">0</span></div><div class="u-tw"><table class="u-tbl"><thead><tr><th>#</th><th>Viewer</th><th>Plan</th><th>Date &amp; Time</th><th>Duration</th></tr></thead><tbody id="vbTbody"></tbody></table></div></div>
      </div>
      <div class="u-section" id="loginHistorySection">
        <div class="u-card"><div class="u-card-head"><span class="u-card-title">OTP &amp; Session Log</span><span id="otpBadge" class="badge badge-gray">0</span></div>
          <div class="u-tw"><table class="u-tbl"><thead><tr><th>#</th><th>Requested</th><th>Status</th><th>Last Login</th><th>Count</th><th>Account</th></tr></thead><tbody id="otpTbody"></tbody></table></div></div>
      </div>
      <div class="u-section" id="myReportsSection">
        <div class="u-card">
          <div class="u-card-head"><span class="u-card-title">My Profile Reports</span><span id="myReportsBadge" class="badge badge-gray">0</span></div>
          <div class="u-tw">
            <table class="u-tbl">
              <thead><tr><th>#</th><th>Reported Date</th><th>Profile ID</th><th>Profile Name</th><th>Reason</th><th>Status</th><th>Admin Note</th><th>Action</th></tr></thead>
              <tbody id="myReportsTbody"></tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- Profile View Log -->
      <div class="u-section" id="profileViewLogSection">
        <div class="u-card">
          <div class="u-card-head"><span class="u-card-title">Profile View Log</span><span id="pvlBadge" class="badge badge-blue">0</span></div>
          <div class="u-tw">
            <table class="u-tbl">
              <thead><tr><th>#</th><th>Viewed Profile</th><th>Name</th><th>Date &amp; Time</th></tr></thead>
              <tbody id="upPvlTbody"></tbody>
            </table>
          </div>
        </div>
        <div class="u-card" style="margin-top:14px">
          <div class="u-card-head"><span class="u-card-title">Who Viewed My Profile</span><span id="pvlWhoViewedBadge" class="badge badge-gray">0</span></div>
          <div class="u-tw">
            <table class="u-tbl">
              <thead><tr><th>#</th><th>Viewer</th><th>Plan</th><th>Date &amp; Time</th></tr></thead>
              <tbody id="upPvlWhoTbody"></tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- Contact View Log -->
      <div class="u-section" id="contactLogSection">
        <div class="u-card">
          <div class="u-card-head"><span class="u-card-title">Contact View Log</span><span id="cvlBadge" class="badge badge-green">0</span></div>
          <div class="u-tw">
            <table class="u-tbl">
              <thead><tr><th>#</th><th>Viewed Profile</th><th>Name</th><th>Date &amp; Time</th></tr></thead>
              <tbody id="upCvlTbody"></tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="u-section" id="mySettingsSection">
        <div class="u-card">
          <div class="u-card-head" style="background:#fffbeb"><div><span class="u-card-title" style="color:#92400e">Request Mobile Number Change</span><div style="font-size:12px;color:var(--ink3);margin-top:1px">Admin approval required.</div></div></div>
          <div class="u-card-body" id="settingsBody"></div>
        </div>
        <div class="u-card"><div class="u-card-head"><span class="u-card-title">Current Session</span></div><div class="u-card-body" id="sessionBody"></div></div>
      </div>
    </div>

    <!-- ADD PROFILE SECTION -->
    <div class="u-section" id="addProfileSection">
      <div id="upApResult" style="display:none;margin-bottom:16px"></div>
      <div class="profile-card" style="border:1px solid rgba(196,30,58,0.12)">
        <div style="background:linear-gradient(135deg,#8B0000,#C41E3A);padding:18px 22px;display:flex;align-items:center;gap:12px">
          <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:16px">👤</div>
          <div>
            <div style="color:white;font-family:var(--serif);font-size:1.1rem">Create New Profile</div>
            <div style="font-size:11px;color:rgba(255,255,255,0.7);margin-top:2px">Fill in your profile details below</div>
          </div>
        </div>
        <div style="padding:20px 22px">
          <!-- Personal -->
          <div class="msec" style="background:linear-gradient(90deg,#8B0000,#C41E3A 70%,transparent);border-radius:5px;color:white">👤 Personal Details</div>
          <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr 0.5fr;gap:0 10px">
            <div class="fg"><label class="flbl">Mobile <span class="req">*</span></label><input class="finp" id="up_ap_mobile" placeholder="10-digit mobile" type="tel" maxlength="10" style="border-color:#D4A0A8"></div>
            <div class="fg"><label class="flbl">Name <span class="req">*</span></label><input class="finp" id="up_ap_name" placeholder="Full name" style="border-color:#D4A0A8"></div>
            <div class="fg"><label class="flbl">Gender <span class="req">*</span></label><select class="fsel" id="up_ap_gender" style="border-color:#D4A0A8"><option value="">— Select —</option><option>Male</option><option>Female</option></select></div>
            <div class="fg"><label class="flbl">Date of Birth <span class="req">*</span> <span id="up_ap_age_display" style="font-weight:700;font-size:12px;margin-left:4px"></span></label><input class="finp" id="up_ap_dob" type="date" style="border-color:#D4A0A8"></div>
            <div class="fg"><label class="flbl">Age</label><input class="finp" id="up_ap_age_input" readonly style="border-color:#D4A0A8;background:#f0fdf4;font-weight:700;text-align:center;font-size:15px"></div>
          </div>
          <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
            <div class="fg"><label class="flbl">Religion <span class="req">*</span></label><select class="fsel" id="up_ap_religion" style="border-color:#D4A0A8"><option value="">— Select —</option><option>Hindu</option><option>Muslim</option><option>Christian</option><option>Sikh</option><option>Jain</option><option>Buddhist</option></select></div>
            <div class="fg"><label class="flbl">Caste <span class="req">*</span></label><select class="fsel" id="up_ap_caste" style="border-color:#D4A0A8" onchange="populateSubcaste('up_ap_caste','up_ap_subcaste','')"><option value="">— Select —</option></select></div>
            <div class="fg"><label class="flbl">Sub Caste</label><select class="fsel" id="up_ap_subcaste" style="border-color:#D4A0A8"><option value="">— Select —</option></select></div>
            <div class="fg"><label class="flbl">Mother Tongue <span class="req">*</span></label><select class="fsel" id="up_ap_tongue" style="border-color:#D4A0A8"><option value="">— Select —</option><option>Tamil</option><option>Telugu</option><option>Malayalam</option><option>Kannada</option><option>Hindi</option><option>English</option></select></div>
          </div>
          <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
            <div class="fg"><label class="flbl">Marital Status <span class="req">*</span></label><select class="fsel" id="up_ap_marital" style="border-color:#D4A0A8"><option value="">— Select —</option><option>Unmarried</option><option>Divorced</option><option>Widowed</option><option>Separated</option></select></div>
            <div class="fg"><label class="flbl">Nationality</label><select class="fsel" id="up_ap_nationality" style="border-color:#D4A0A8"><option value="">— Select —</option></select></div>
            <div class="fg"><label class="flbl">Own House</label><select class="fsel" id="up_ap_own_house" style="border-color:#D4A0A8"><option>Yes</option><option>No</option></select></div>
            <div class="fg"></div>
          </div>
          <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
            <div class="fg"><label class="flbl">Place of Birth</label><input class="finp" id="up_ap_place_birth" placeholder="e.g. Chennai" style="border-color:#D4A0A8"></div>
            <div class="fg"><label class="flbl">Nativity</label><input class="finp" id="up_ap_nativity" list="nativity_list" placeholder="Type or select" style="border-color:#D4A0A8"></div>
            <div class="fg"><label class="flbl">Present Country</label><select class="fsel" id="up_ap_workplace" style="border-color:#D4A0A8"><option value="">— Select —</option></select></div>
            <div class="fg"><label class="flbl">Blood Group</label><select class="fsel" id="up_ap_blood" style="border-color:#D4A0A8"><option value="">— Select —</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>O+</option><option>O-</option><option>AB+</option><option>AB-</option></select></div>
          </div>
          <!-- Physical -->
          <div class="msec" style="background:linear-gradient(90deg,#8B0000,#C41E3A 70%,transparent);border-radius:5px;color:white">⚖️ Physical Attributes</div>
          <div class="fg3">
            <div class="fg"><label class="flbl">Height</label><select class="fsel" id="up_ap_height" style="border-color:#D4A0A8"><option value="">— Select —</option><option>4ft 5in</option><option>4ft 6in</option><option>4ft 7in</option><option>4ft 8in</option><option>4ft 9in</option><option>4ft 10in</option><option>4ft 11in</option><option>5ft 0in</option><option>5ft 1in</option><option>5ft 2in</option><option>5ft 3in</option><option>5ft 4in</option><option>5ft 5in</option><option>5ft 6in</option><option>5ft 7in</option><option>5ft 8in</option><option>5ft 9in</option><option>5ft 10in</option><option>5ft 11in</option><option>6ft 0in</option><option>6ft 1in</option><option>6ft 2in</option><option>6ft 3in</option><option>6ft 4in</option><option>6ft 5in</option></select></div>
            <div class="fg"><label class="flbl">Weight</label><select class="fsel" id="up_ap_weight" style="border-color:#D4A0A8"><option value="">— Select —</option><option>40 kg</option><option>42 kg</option><option>45 kg</option><option>48 kg</option><option>50 kg</option><option>52 kg</option><option>55 kg</option><option>56 kg</option><option>58 kg</option><option>60 kg</option><option>62 kg</option><option>63 kg</option><option>65 kg</option><option>67 kg</option><option>68 kg</option><option>69 kg</option><option>70 kg</option><option>71 kg</option><option>72 kg</option><option>73 kg</option><option>75 kg</option><option>78 kg</option><option>80 kg</option><option>82 kg</option><option>85 kg</option><option>88 kg</option><option>90 kg</option><option>95 kg</option><option>100 kg</option><option>105 kg</option><option>110 kg</option></select></div>
            <div class="fg"><label class="flbl">Diet</label><select class="fsel" id="up_ap_diet" style="border-color:#D4A0A8"><option>Vegetarian</option><option>Non-Vegetarian</option><option>Eggetarian</option></select></div>
          </div>
          <!-- Education -->
          <div class="msec" style="background:linear-gradient(90deg,#8B0000,#C41E3A 70%,transparent);border-radius:5px;color:white">🎓 Education & Occupation</div>
          <div class="fg3">
            <div class="fg"><label class="flbl">Qualification</label><select class="fsel" id="up_ap_qual" style="border-color:#D4A0A8"><option value="">— Select —</option><optgroup label="Below 10th"><option>Below 10th</option><option>10th / SSLC</option></optgroup><optgroup label="Higher Secondary"><option>12th / HSC</option><option>ITI</option><option>Diploma</option></optgroup><optgroup label="Undergraduate"><option>B.A</option><option>B.Sc</option><option>B.Com</option><option>B.E / B.Tech</option><option>B.B.A</option><option>B.C.A</option><option>B.Ed</option><option>B.L / L.L.B</option><option>B.Arch</option><option>B.Pharm</option><option>B.D.S</option><option>M.B.B.S</option><option>B.V.Sc</option><option>B.P.T</option><option>B.Sc (Nursing)</option><option>B.S.W</option><option>B.F.A</option><option>B.Des</option></optgroup><optgroup label="Postgraduate"><option>M.A</option><option>M.Sc</option><option>M.Com</option><option>M.E / M.Tech</option><option>M.B.A</option><option>M.C.A</option><option>M.Ed</option><option>M.L / L.L.M</option><option>M.Pharm</option><option>M.D</option><option>M.S (Medical)</option><option>M.D.S</option><option>M.P.T</option><option>M.S.W</option><option>M.Des</option></optgroup><optgroup label="Doctorate / Research"><option>M.Phil</option><option>Ph.D</option><option>D.M</option><option>D.Litt</option></optgroup><optgroup label="Professional / Other"><option>C.A</option><option>C.S</option><option>I.C.W.A / C.M.A</option><option>C.F.A</option><option>I.A.S / I.P.S / I.F.S</option><option>Others</option></optgroup></select></div>
            <div class="fg"><label class="flbl">Occupation</label><select class="fsel" id="up_ap_job" style="border-color:#D4A0A8"><option value="">— Select —</option><optgroup label="Government / Public Sector"><option>Central Govt Employee</option><option>State Govt Employee</option><option>PSU Employee</option><option>Defense - Army</option><option>Defense - Navy</option><option>Defense - Air Force</option><option>Police / CRPF / BSF</option><option>IAS / IPS / IFS Officer</option><option>Railway Employee</option><option>Postal Employee</option><option>TNPSC Group Service</option></optgroup><optgroup label="IT / Software"><option>Software Engineer</option><option>Software Developer</option><option>Data Analyst</option><option>Data Scientist</option><option>System Administrator</option><option>Network Engineer</option><option>Web Developer</option><option>UI/UX Designer</option><option>IT Manager</option><option>Cyber Security Analyst</option></optgroup><optgroup label="Engineering / Manufacturing"><option>Mechanical Engineer</option><option>Civil Engineer</option><option>Electrical Engineer</option><option>Electronics Engineer</option><option>Chemical Engineer</option><option>Production Engineer</option><option>Site Engineer</option><option>Quality Engineer</option><option>Project Manager</option></optgroup><optgroup label="Medical / Healthcare"><option>Doctor</option><option>Surgeon</option><option>Dentist</option><option>Pharmacist</option><option>Nurse</option><option>Physiotherapist</option><option>Lab Technician</option><option>Ayurveda / Siddha / Homeopathy</option></optgroup><optgroup label="Education / Teaching"><option>Professor</option><option>Lecturer</option><option>School Teacher</option><option>Private Tutor</option><option>Research Scholar</option></optgroup><optgroup label="Banking / Finance"><option>Bank Manager</option><option>Bank Employee</option><option>Chartered Accountant</option><option>Financial Analyst</option><option>Insurance Agent</option><option>Auditor</option><option>Tax Consultant</option></optgroup><optgroup label="Legal"><option>Advocate / Lawyer</option><option>Judge</option><option>Legal Advisor</option><option>Notary</option></optgroup><optgroup label="Business / Entrepreneurship"><option>Business Owner</option><option>Shopkeeper</option><option>Trader / Merchant</option><option>Real Estate Business</option><option>Exporter / Importer</option><option>Contractor</option><option>Freelancer</option><option>Startup Founder</option></optgroup><optgroup label="Agriculture / Farming"><option>Farmer / Agriculturist</option><option>Dairy Farmer</option><option>Plantation Owner</option><option>Agricultural Officer</option></optgroup><optgroup label="Skilled Trades"><option>Electrician</option><option>Plumber</option><option>Carpenter</option><option>Welder</option><option>Mechanic</option><option>Tailor</option><option>Goldsmith</option><option>Mason</option></optgroup><optgroup label="Media / Creative"><option>Journalist</option><option>Content Writer</option><option>Photographer</option><option>Graphic Designer</option><option>Film / TV Professional</option></optgroup><optgroup label="Abroad / NRI"><option>Working in Gulf</option><option>Working in USA</option><option>Working in UK</option><option>Working in Canada</option><option>Working in Australia</option><option>Working in Singapore</option><option>Working in Malaysia</option><option>Merchant Navy</option></optgroup><optgroup label="Other"><option>Private Company Employee</option><option>Supervisor / Foreman</option><option>Driver</option><option>Chef / Cook</option><option>Security Guard</option><option>Home Maker</option><option>Retired</option><option>Student</option><option>Not Employed</option><option>Others</option></optgroup></select></div>
            <div class="fg"><label class="flbl">Monthly Income (₹)</label><input class="finp" id="up_ap_income" placeholder="e.g. 35000" style="border-color:#D4A0A8"></div>
          </div>
          <!-- Astrology -->
          <div class="msec" style="background:linear-gradient(90deg,#8B0000,#C41E3A 70%,transparent);border-radius:5px;color:white">🪐 Astrology</div>
          <div class="fg3">
            <div class="fg"><label class="flbl">Star</label><select class="fsel" id="up_ap_star" style="border-color:#D4A0A8"><option value="">— Select —</option><option>Ashwini</option><option>Bharani</option><option>Karthigai</option><option>Rohini</option><option>Mirigasirisham</option><option>Thiruvathirai</option><option>Punarpoosam</option><option>Poosam</option><option>Ayilyam</option><option>Makam</option><option>Pooram</option><option>Uthiram</option><option>Hastham</option><option>Chithirai</option><option>Swathi</option><option>Visakam</option><option>Anusham</option><option>Kettai</option><option>Moolam</option><option>Pooradam</option><option>Uthradam</option><option>Thiruvonam</option><option>Avittam</option><option>Sadhayam</option><option>Puratathi</option><option>Uthirattathi</option><option>Revathi</option></select></div>
            <div class="fg"></div>
          </div>
          <div class="fg3">
            <div class="fg"><label class="flbl">Raasi</label><select class="fsel" id="up_ap_raasi" style="border-color:#D4A0A8"><option value="">— Select —</option><option>Mesham</option><option>Rishabam</option><option>Midhunam</option><option>Kadagam</option><option>Simham</option><option>Kanni</option><option>Thulam</option><option>Viruchigam</option><option>Dhanusu</option><option>Makaram</option><option>Kumbam</option><option>Meenam</option></select></div>
            <div class="fg"><label class="flbl">Lagnam</label><select class="fsel" id="up_ap_lagnam" style="border-color:#D4A0A8"><option value="">— Select —</option><option>Mesham</option><option>Rishabam</option><option>Midhunam</option><option>Kadagam</option><option>Simham</option><option>Kanni</option><option>Thulam</option><option>Viruchigam</option><option>Dhanusu</option><option>Makaram</option><option>Kumbam</option><option>Meenam</option></select></div>
            <div class="fg"><label class="flbl">Dosham</label><select class="fsel" id="up_ap_dosham" style="border-color:#D4A0A8"><option>No</option><option>Yes</option><option>Partial</option></select></div>
          </div>
          <div class="fg3"><div class="fg" id="up_ap_dosham_type_wrap" style="display:none"><label class="flbl">Dosham Type</label><select class="fsel" id="up_ap_dosham_type" style="border-color:#D4A0A8"><option value="">— Select Dosham Type —</option></select></div><div class="fg"></div><div class="fg"></div></div>
          <!-- Plan -->
          <div class="msec" style="background:linear-gradient(90deg,#8B0000,#C41E3A 70%,transparent);border-radius:5px;color:white">🛒 Select Plan</div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:18px">
            <div class="up-ap-plan" data-plan="free" onclick="upApSelectPlan('free')" style="border:2px solid #E0C0C8;border-radius:10px;padding:13px 12px;cursor:pointer;background:#FFFAF9;transition:all .2s;text-align:center;position:relative"><div style="font-size:20px;margin-bottom:6px">🆓</div><div style="font-weight:700;color:#5A0010;font-size:0.85rem">Free</div><div style="font-weight:800;color:#78716c;font-size:1rem">₹0</div><div style="font-size:0.65rem;color:#9A7080">3 months</div></div>
            <div class="up-ap-plan" data-plan="silver" onclick="upApSelectPlan('silver')" style="border:2px solid #E0C0C8;border-radius:10px;padding:13px 12px;cursor:pointer;background:#FFFAF9;transition:all .2s;text-align:center;position:relative"><div style="font-size:20px;margin-bottom:6px">🥈</div><div style="font-weight:700;color:#5A0010;font-size:0.85rem">Silver</div><div style="font-weight:800;color:#64748b;font-size:1rem">₹999</div><div style="font-size:0.65rem;color:#9A7080">6 months</div></div>
            <div class="up-ap-plan" data-plan="gold" onclick="upApSelectPlan('gold')" style="border:2px solid #E0C0C8;border-radius:10px;padding:13px 12px;cursor:pointer;background:#FFFAF9;transition:all .2s;text-align:center;position:relative"><div style="font-size:20px;margin-bottom:6px">🥇</div><div style="font-weight:700;color:#5A0010;font-size:0.85rem">Gold</div><div style="font-weight:800;color:#C9913A;font-size:1rem">₹1,999</div><div style="font-size:0.65rem;color:#9A7080">1 year</div></div>
            <div class="up-ap-plan" data-plan="premium" onclick="upApSelectPlan('premium')" style="border:2px solid #E0C0C8;border-radius:10px;padding:13px 12px;cursor:pointer;background:#FFFAF9;transition:all .2s;text-align:center;position:relative"><div style="font-size:20px;margin-bottom:6px">💎</div><div style="font-weight:700;color:#5A0010;font-size:0.85rem">Premium</div><div style="font-weight:800;color:#8B0000;font-size:1rem">₹2,999</div><div style="font-size:0.65rem;color:#9A7080">2 years</div></div>
          </div>
          <input type="hidden" id="up_ap_plan" value="free">
          <div class="action-row" style="justify-content:flex-end;border-top:1px solid rgba(196,30,58,0.12);padding-top:14px">
            <button class="btn btn-outline" onclick="upApReset()">↺ Reset</button>
            <button class="btn btn-primary" style="background:linear-gradient(135deg,#8B0000,#C41E3A);border-color:#8B0000" onclick="upApSubmit()">✦ Create Profile</button>
          </div>
        </div>
      </div>
    </div>

    <!-- PAY LATER SECTION -->
    <div class="u-section" id="addOrderSection">
      <!-- Upgrade banner (hidden until JS runs) -->
      <div id="payLaterProfileCard" class="u-card" style="display:none;margin-bottom:16px"></div>
      <!-- Pay Now Button (hidden once payment submitted) -->
      <div id="payLaterPayNowCard" class="u-card" style="margin-bottom:16px;text-align:center;padding:24px 16px">
        <div style="font-size:15px;font-weight:700;color:var(--ink1);margin-bottom:6px">Want to upgrade your plan?</div>
        <div style="font-size:13px;color:var(--ink3);margin-bottom:16px">Choose a plan and complete payment to unlock premium features.</div>
        <button class="btn btn-primary" onclick="goToPayment()" style="background:linear-gradient(135deg,#f59e0b,#d97706);border-color:#d97706;padding:12px 32px;font-size:14px;font-weight:700">💳 Pay Now — Choose Plan</button>
      </div>
      <!-- Order History -->
      <div class="u-card" style="margin-bottom:16px">
        <div class="u-card-head"><span class="u-card-title">Order History</span><span id="myOrdersBadge" class="badge badge-gray">0</span></div>
        <div class="u-tw">
          <table class="u-tbl">
            <thead><tr><th>#</th><th>Date</th><th>Plan</th><th>Amount</th><th>Method</th><th>Txn Ref</th><th>Proof</th><th>Status</th><th>Admin Note</th></tr></thead>
            <tbody id="myOrdersTbody"></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
  </div>
</div>

<!-- EDIT MODAL -->
<!-- EDIT PROFILE PAGE (full-viewport modal variant) -->
<div class="modal-overlay modal-page" id="editModal">
  <div class="modal">
    <div class="modal-head">
      <button class="modal-back" onclick="closeModal('editModal')" title="Back to My Profile">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        <span class="modal-title">Edit My Profile</span>
      </button>
    </div>
    <div class="modal-body">
      <div class="msec">📸 Profile Photographs</div>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:14px">
        <div style="flex:1;min-width:120px">
          <label class="flbl">Photo 1</label>
          <div style="height:110px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:var(--bg);overflow:hidden" onclick="this.querySelector('input').click()">
            <img id="ep_photo1_prev" style="display:none;width:100%;height:100%;object-fit:cover"><span id="ep_photo1_ph" style="color:var(--ink4);font-size:11px;text-align:center">Upload Photo</span>
            <input type="file" id="ep_photo1_file" accept="image/*" style="display:none" onchange="upPhotoPreview(this,'ep_photo1')">
          </div>
        </div>
        <div style="flex:1;min-width:120px">
          <label class="flbl">Photo 2</label>
          <div style="height:110px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:var(--bg);overflow:hidden" onclick="this.querySelector('input').click()">
            <img id="ep_photo2_prev" style="display:none;width:100%;height:100%;object-fit:cover"><span id="ep_photo2_ph" style="color:var(--ink4);font-size:11px;text-align:center">Upload Photo</span>
            <input type="file" id="ep_photo2_file" accept="image/*" style="display:none" onchange="upPhotoPreview(this,'ep_photo2')">
          </div>
        </div>
        <div style="flex:1;min-width:120px">
          <label class="flbl">Photo 3</label>
          <div style="height:110px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:var(--bg);overflow:hidden" onclick="this.querySelector('input').click()">
            <img id="ep_photo3_prev" style="display:none;width:100%;height:100%;object-fit:cover"><span id="ep_photo3_ph" style="color:var(--ink4);font-size:11px;text-align:center">Upload Photo</span>
            <input type="file" id="ep_photo3_file" accept="image/*" style="display:none" onchange="upPhotoPreview(this,'ep_photo3')">
          </div>
        </div>
      </div>

      <div class="msec">👤 Personal Details</div>
      <div id="ep_lockNote" style="display:none;background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:9px 12px;border-radius:8px;font-size:12.5px;margin-bottom:10px;line-height:1.5">🔒 <strong>Identity locked.</strong> Mobile, Name, Gender, Date of Birth and Age cannot be edited after admin approval. Please contact admin if a correction is needed.</div>
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr 0.5fr;gap:0 10px">
        <div class="fg"><label class="flbl">Mobile <span class="req">*</span></label><input class="finp" id="ep_mobile" readonly style="background:#f3f4f6"></div>
        <div class="fg"><label class="flbl">Name <span class="req">*</span></label><input class="finp" id="ep_name"></div>
        <div class="fg"><label class="flbl">Gender <span class="req">*</span></label><select class="fsel" id="ep_gender"><option value="">-</option><option>Male</option><option>Female</option></select></div>
        <div class="fg"><label class="flbl">Date of Birth <span class="req">*</span> <span id="ep_age_display" style="font-weight:700;font-size:12px;margin-left:4px"></span></label><input class="finp" id="ep_dob" type="date"></div>
        <div class="fg"><label class="flbl">Age</label><input class="finp" id="ep_age_input" readonly style="background:#f0fdf4;font-weight:700;text-align:center;font-size:15px"></div>
      </div>
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
        <div class="fg"><label class="flbl">Religion <span class="req">*</span></label><select class="fsel" id="ep_religion"><option value="">-</option><option>Hindu</option><option>Muslim</option><option>Christian</option><option>Sikh</option><option>Jain</option><option>Buddhist</option></select></div>
        <div class="fg"><label class="flbl">Caste <span class="req">*</span></label><select class="fsel" id="ep_caste" onchange="populateSubcaste('ep_caste','ep_subcaste','')"><option value="">-</option></select></div>
        <div class="fg"><label class="flbl">Sub Caste</label><select class="fsel" id="ep_subcaste"><option value="">-</option></select></div>
        <div class="fg"><label class="flbl">Mother Tongue <span class="req">*</span></label><select class="fsel" id="ep_tongue"><option value="">-</option><option>Tamil</option><option>Telugu</option><option>Malayalam</option><option>Kannada</option><option>Hindi</option><option>English</option></select></div>
      </div>
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
        <div class="fg"><label class="flbl">Marital Status <span class="req">*</span></label><select class="fsel" id="ep_marital"><option value="">-</option><option>Unmarried</option><option>Divorced</option><option>Widowed</option><option>Separated</option></select></div>
        <div class="fg"><label class="flbl">Nationality</label><select class="fsel" id="ep_nationality"><option value="">-</option></select></div>
        <div class="fg"><label class="flbl">Own House</label><select class="fsel" id="ep_own_house"><option>Yes</option><option>No</option></select></div>
        <div class="fg"><label class="flbl">Born As</label><div style="display:flex;gap:4px"><input class="finp" id="ep_born_as_num" type="number" min="1" max="20" placeholder="e.g. 2" style="width:60px"><select class="fsel" id="ep_born_as_type"><option value="">-</option><option>Son</option><option>Daughter</option></select></div></div>
        <div class="fg"></div>
      </div>
      <input type="hidden" id="ep_age" value="">
      <div class="fg3">
        <div class="fg"><label class="flbl">Place of Birth</label><input class="finp" id="ep_place_birth" placeholder="e.g. Pondicherry"></div>
        <div class="fg"><label class="flbl">Nativity</label><input class="finp" id="ep_nativity" list="nativity_list" placeholder="Type or select"></div>
        <div class="fg"><label class="flbl">Present Country</label><select class="fsel" id="ep_workplace"><option value="">-</option></select></div>
      </div>

      <div class="msec">👨‍👩‍👧‍👦 Family Details</div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Father's Name</label><input class="finp" id="ep_father"></div>
        <div class="fg"><label class="flbl">Father Status</label><select class="fsel" id="ep_father_alive"><option value="">-</option><option>Employed</option><option>Businessman</option><option>Professional</option><option>Retired</option><option>Not Employed</option><option>Passed Away</option></select></div>
        <div class="fg"><label class="flbl">Father's Job</label><input class="finp" id="ep_father_job" list="occupation_list" placeholder="Type or select"></div>
      </div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Mother's Name</label><input class="finp" id="ep_mother"></div>
        <div class="fg"><label class="flbl">Mother Status</label><select class="fsel" id="ep_mother_alive"><option value="">-</option><option>Home Maker</option><option>Employed</option><option>Businessman</option><option>Professional</option><option>Retired</option><option>Not Employed</option><option>Passed Away</option></select></div>
        <div class="fg"><label class="flbl">Mother's Job</label><input class="finp" id="ep_mother_job" list="occupation_list" placeholder="Type or select"></div>
      </div>
      <div class="fg" style="font-size:12px;font-weight:600;color:var(--ink3);margin:4px 0 6px">Siblings</div>
      <div style="overflow-x:auto;margin-bottom:14px">
        <table style="width:100%;border-collapse:collapse;font-size:12px;border:1px solid var(--border);border-radius:6px">
          <thead><tr style="background:var(--bg)"><th style="padding:6px 8px;border:1px solid var(--border)"></th><th style="padding:6px 8px;border:1px solid var(--border)">Elder Brother</th><th style="padding:6px 8px;border:1px solid var(--border)">Younger Brother</th><th style="padding:6px 8px;border:1px solid var(--border)">Elder Sister</th><th style="padding:6px 8px;border:1px solid var(--border)">Younger Sister</th></tr></thead>
          <tbody>
            <tr><td style="padding:6px 8px;border:1px solid var(--border);font-weight:600">Married</td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="ep_sib_eb_m" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="ep_sib_yb_m" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="ep_sib_es_m" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="ep_sib_ys_m" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td></tr>
            <tr><td style="padding:6px 8px;border:1px solid var(--border);font-weight:600">Unmarried</td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="ep_sib_eb_u" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="ep_sib_yb_u" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="ep_sib_es_u" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="ep_sib_ys_u" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td></tr>
          </tbody>
        </table>
      </div>
      <div class="fg"><label class="flbl">Other Details</label><textarea class="fta" id="ep_others" rows="2" placeholder="Talents, achievements, visa status…"></textarea></div>

      <div class="msec">⚖️ Physical Attributes</div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Height</label><select class="fsel" id="ep_height"><option value="">-</option><option>4ft 5in</option><option>4ft 6in</option><option>4ft 7in</option><option>4ft 8in</option><option>4ft 9in</option><option>4ft 10in</option><option>4ft 11in</option><option>5ft 0in</option><option>5ft 1in</option><option>5ft 2in</option><option>5ft 3in</option><option>5ft 4in</option><option>5ft 5in</option><option>5ft 6in</option><option>5ft 7in</option><option>5ft 8in</option><option>5ft 9in</option><option>5ft 10in</option><option>5ft 11in</option><option>6ft 0in</option><option>6ft 1in</option><option>6ft 2in</option><option>6ft 3in</option><option>6ft 4in</option><option>6ft 5in</option></select></div>
        <div class="fg"><label class="flbl">Weight</label><select class="fsel" id="ep_weight"><option value="">-</option><option>40 kg</option><option>42 kg</option><option>45 kg</option><option>48 kg</option><option>50 kg</option><option>52 kg</option><option>55 kg</option><option>56 kg</option><option>58 kg</option><option>60 kg</option><option>62 kg</option><option>63 kg</option><option>65 kg</option><option>67 kg</option><option>68 kg</option><option>69 kg</option><option>70 kg</option><option>71 kg</option><option>72 kg</option><option>73 kg</option><option>75 kg</option><option>78 kg</option><option>80 kg</option><option>82 kg</option><option>85 kg</option><option>88 kg</option><option>90 kg</option><option>95 kg</option><option>100 kg</option><option>105 kg</option><option>110 kg</option></select></div>
        <div class="fg"><label class="flbl">Blood Group</label><select class="fsel" id="ep_blood"><option value="">-</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>O+</option><option>O-</option><option>AB+</option><option>AB-</option></select></div>
      </div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Complexion</label><select class="fsel" id="ep_complexion"><option value="">-</option><option>Very Fair</option><option>Fair</option><option>White</option><option>Wheatish</option><option>Brown</option><option>Dark</option></select></div>
        <div class="fg"><label class="flbl">Diet</label><select class="fsel" id="ep_diet"><option>Vegetarian</option><option>Non-Vegetarian</option><option>Eggetarian</option></select></div>
        <div class="fg"><label class="flbl">Disability</label><select class="fsel" id="ep_disability"><option>No</option><option>Yes</option></select></div>
      </div>

      <div class="msec">🎓 Education & Occupation</div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Qualification</label><select class="fsel" id="ep_qual"><option value="">-</option><optgroup label="Below 10th"><option>Below 10th</option><option>10th / SSLC</option></optgroup><optgroup label="Higher Secondary"><option>12th / HSC</option><option>ITI</option><option>Diploma</option></optgroup><optgroup label="Undergraduate"><option>B.A</option><option>B.Sc</option><option>B.Com</option><option>B.E / B.Tech</option><option>B.B.A</option><option>B.C.A</option><option>B.Ed</option><option>B.L / L.L.B</option><option>B.Arch</option><option>B.Pharm</option><option>B.D.S</option><option>M.B.B.S</option><option>B.V.Sc</option><option>B.P.T</option><option>B.Sc (Nursing)</option><option>B.S.W</option><option>B.F.A</option><option>B.Des</option></optgroup><optgroup label="Postgraduate"><option>M.A</option><option>M.Sc</option><option>M.Com</option><option>M.E / M.Tech</option><option>M.B.A</option><option>M.C.A</option><option>M.Ed</option><option>M.L / L.L.M</option><option>M.Pharm</option><option>M.D</option><option>M.S (Medical)</option><option>M.D.S</option><option>M.P.T</option><option>M.S.W</option><option>M.Des</option></optgroup><optgroup label="Doctorate / Research"><option>M.Phil</option><option>Ph.D</option><option>D.M</option><option>D.Litt</option></optgroup><optgroup label="Professional / Other"><option>C.A</option><option>C.S</option><option>I.C.W.A / C.M.A</option><option>C.F.A</option><option>I.A.S / I.P.S / I.F.S</option><option>Others</option></optgroup></select></div>
        <div class="fg"><label class="flbl">Job</label><select class="fsel" id="ep_job"><option value="">-</option><optgroup label="Government / Public Sector"><option>Central Govt Employee</option><option>State Govt Employee</option><option>PSU Employee</option><option>Defense - Army</option><option>Defense - Navy</option><option>Defense - Air Force</option><option>Police / CRPF / BSF</option><option>IAS / IPS / IFS Officer</option><option>Railway Employee</option><option>Postal Employee</option><option>TNPSC Group Service</option></optgroup><optgroup label="IT / Software"><option>Software Engineer</option><option>Software Developer</option><option>Data Analyst</option><option>Data Scientist</option><option>System Administrator</option><option>Network Engineer</option><option>Web Developer</option><option>UI/UX Designer</option><option>IT Manager</option><option>Cyber Security Analyst</option></optgroup><optgroup label="Engineering / Manufacturing"><option>Mechanical Engineer</option><option>Civil Engineer</option><option>Electrical Engineer</option><option>Electronics Engineer</option><option>Chemical Engineer</option><option>Production Engineer</option><option>Site Engineer</option><option>Quality Engineer</option><option>Project Manager</option></optgroup><optgroup label="Medical / Healthcare"><option>Doctor</option><option>Surgeon</option><option>Dentist</option><option>Pharmacist</option><option>Nurse</option><option>Physiotherapist</option><option>Lab Technician</option><option>Ayurveda / Siddha / Homeopathy</option></optgroup><optgroup label="Education / Teaching"><option>Professor</option><option>Lecturer</option><option>School Teacher</option><option>Private Tutor</option><option>Research Scholar</option></optgroup><optgroup label="Banking / Finance"><option>Bank Manager</option><option>Bank Employee</option><option>Chartered Accountant</option><option>Financial Analyst</option><option>Insurance Agent</option><option>Auditor</option><option>Tax Consultant</option></optgroup><optgroup label="Legal"><option>Advocate / Lawyer</option><option>Judge</option><option>Legal Advisor</option><option>Notary</option></optgroup><optgroup label="Business / Entrepreneurship"><option>Business Owner</option><option>Shopkeeper</option><option>Trader / Merchant</option><option>Real Estate Business</option><option>Exporter / Importer</option><option>Contractor</option><option>Freelancer</option><option>Startup Founder</option></optgroup><optgroup label="Agriculture / Farming"><option>Farmer / Agriculturist</option><option>Dairy Farmer</option><option>Plantation Owner</option><option>Agricultural Officer</option></optgroup><optgroup label="Skilled Trades"><option>Electrician</option><option>Plumber</option><option>Carpenter</option><option>Welder</option><option>Mechanic</option><option>Tailor</option><option>Goldsmith</option><option>Mason</option></optgroup><optgroup label="Media / Creative"><option>Journalist</option><option>Content Writer</option><option>Photographer</option><option>Graphic Designer</option><option>Film / TV Professional</option></optgroup><optgroup label="Abroad / NRI"><option>Working in Gulf</option><option>Working in USA</option><option>Working in UK</option><option>Working in Canada</option><option>Working in Australia</option><option>Working in Singapore</option><option>Working in Malaysia</option><option>Merchant Navy</option></optgroup><optgroup label="Other"><option>Private Company Employee</option><option>Supervisor / Foreman</option><option>Driver</option><option>Chef / Cook</option><option>Security Guard</option><option>Home Maker</option><option>Retired</option><option>Student</option><option>Not Employed</option><option>Others</option></optgroup></select></div>
        <div class="fg"><label class="flbl">Place of Job</label><input class="finp" id="ep_place_job" placeholder="City"></div>
      </div>
      <div class="fg2">
        <div class="fg"><label class="flbl">Income Per Month (₹)</label><input class="finp" id="ep_income" placeholder="e.g. 35000"></div>
        <div class="fg"></div>
      </div>

      <div class="msec">🪐 Astrology</div>
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
        <div class="fg"><label class="flbl">Star</label><select class="fsel" id="ep_star"><option value="">-</option><option>Ashwini</option><option>Bharani</option><option>Karthigai</option><option>Rohini</option><option>Mirigasirisham</option><option>Thiruvathirai</option><option>Punarpoosam</option><option>Poosam</option><option>Ayilyam</option><option>Makam</option><option>Pooram</option><option>Uthiram</option><option>Hastham</option><option>Chithirai</option><option>Swathi</option><option>Visakam</option><option>Anusham</option><option>Kettai</option><option>Moolam</option><option>Pooradam</option><option>Uthradam</option><option>Thiruvonam</option><option>Avittam</option><option>Sadhayam</option><option>Puratathi</option><option>Uthirattathi</option><option>Revathi</option></select></div>
        <div class="fg"><label class="flbl">Raasi</label><select class="fsel" id="ep_raasi"><option value="">-</option><option>Mesham</option><option>Rishabam</option><option>Midhunam</option><option>Kadagam</option><option>Simham</option><option>Kanni</option><option>Thulam</option><option>Viruchigam</option><option>Dhanusu</option><option>Makaram</option><option>Kumbam</option><option>Meenam</option></select></div>
        <div class="fg"><label class="flbl">Paadam</label><select class="fsel" id="ep_paadam"><option value="">-</option><option>1st Paadam</option><option>2nd Paadam</option><option>3rd Paadam</option><option>4th Paadam</option></select></div>
        <div class="fg"><label class="flbl">Lagnam</label><select class="fsel" id="ep_lagnam"><option value="">-</option><option>Mesham</option><option>Rishabam</option><option>Midhunam</option><option>Kadagam</option><option>Simham</option><option>Kanni</option><option>Thulam</option><option>Viruchigam</option><option>Dhanusu</option><option>Makaram</option><option>Kumbam</option><option>Meenam</option></select></div>
      </div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Gothram</label><input class="finp" id="ep_gothram"></div>
        <div class="fg"><label class="flbl">Dosham</label><select class="fsel" id="ep_dosham"><option>No</option><option>Yes</option><option>Partial</option></select></div>
        <div class="fg" id="ep_dosham_type_wrap" style="display:none"><label class="flbl">Dosham Type</label><select class="fsel" id="ep_dosham_type"><option value="">— Select Dosham Type —</option></select></div>
      </div>

      <div class="msec">💑 Partner Expectations</div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Qualification</label><input class="finp" id="ep_p_qual" list="partner_qual_list" placeholder="e.g. Any Degree"></div>
        <div class="fg"><label class="flbl">Job Preference</label><input class="finp" id="ep_p_job" list="partner_job_list" placeholder="e.g. Any, Govt Job"></div>
        <div class="fg"><label class="flbl">Job Requirement</label><select class="fsel" id="ep_p_jobreq"><option>Optional</option><option>Must</option><option>Not Required</option></select></div>
      </div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Income Expectation</label><input class="finp" id="ep_p_income" placeholder="e.g. 30000"></div>
        <div class="fg"><label class="flbl">Age From</label><input class="finp" id="ep_p_agefrom" type="number" min="18" max="70"></div>
        <div class="fg"><label class="flbl">Age To</label><input class="finp" id="ep_p_ageto" type="number" min="18" max="70"></div>
      </div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Diet Preference</label><select class="fsel" id="ep_p_diet"><option>Vegetarian</option><option>Non-Vegetarian</option><option>Any</option></select></div>
        <div class="fg"><label class="flbl">Marital Status</label><select class="fsel" id="ep_p_marital"><option>Unmarried</option><option>Divorced</option><option>Widowed</option><option>Any</option></select></div>
        <div class="fg"><label class="flbl">Horoscope Required?</label><select class="fsel" id="ep_p_horoscope"><option>No</option><option>Yes</option><option>Not Applicable</option></select></div>
      </div>
      <div class="fg"><div class="fg" style="grid-column:span 3"><label class="flbl">Caste Preference</label><input type="hidden" id="ep_p_caste"><div id="ep_p_caste_box" style="border:1px solid var(--border);border-radius:8px;padding:8px 10px;background:#faf9f7;max-height:160px;overflow-y:auto"></div></div></div>
      <div class="fg"><div class="fg" id="ep_p_subcaste_wrap" style="display:none"><label class="flbl">Sub Caste Preference</label><input type="hidden" id="ep_p_subcaste"><div id="ep_p_subcaste_box" style="border:1px solid var(--border);border-radius:8px;padding:8px 10px;background:#faf9f7;max-height:140px;overflow-y:auto"></div></div></div>
      <div class="fg"><label class="flbl">Other Requirements</label><textarea class="fta" id="ep_p_other" rows="2" placeholder="Any other expectations…"></textarea></div>

      <div class="msec">🔮 Horoscope Photos</div>
      <div class="fg2">
        <div class="fg">
          <label class="flbl">Rasi Chart Photo</label>
          <div style="height:100px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:var(--bg);overflow:hidden" onclick="this.querySelector('input').click()">
            <img id="ep_rasi_prev" style="display:none;width:100%;height:100%;object-fit:contain"><span id="ep_rasi_ph" style="color:var(--ink4);font-size:11px">📄 Upload Rasi Chart</span>
            <input type="file" id="ep_rasi_file" accept="image/*" style="display:none" onchange="upPhotoPreview(this,'ep_rasi')">
          </div>
        </div>
        <div class="fg">
          <label class="flbl">Amsam Chart Photo</label>
          <div style="height:100px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:var(--bg);overflow:hidden" onclick="this.querySelector('input').click()">
            <img id="ep_amsam_prev" style="display:none;width:100%;height:100%;object-fit:contain"><span id="ep_amsam_ph" style="color:var(--ink4);font-size:11px">📄 Upload Amsam Chart</span>
            <input type="file" id="ep_amsam_file" accept="image/*" style="display:none" onchange="upPhotoPreview(this,'ep_amsam')">
          </div>
        </div>
      </div>

      <div class="msec">📞 Communication</div>
      <div class="fg2">
        <div class="fg"><label class="flbl">Email</label><input class="finp" id="ep_email" type="email"></div>
        <div class="fg"><label class="flbl">Alt Mobile</label><input class="finp" id="ep_alt" type="tel" maxlength="10"></div>
      </div>
      <div class="fg2">
        <div class="fg"><label class="flbl">Contact Person</label><input class="finp" id="ep_contact_person" placeholder="Contact person name"></div>
        <div class="fg"></div>
      </div>
      <div class="fg2">
        <div class="fg"><label class="flbl">Permanent Address</label><textarea class="fta" id="ep_addr" rows="2"></textarea></div>
        <div class="fg"><label class="flbl">Present Address</label><textarea class="fta" id="ep_present_addr" rows="2" placeholder="Door No, Street, Area, City, District, State"></textarea></div>
      </div>
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
        <div class="fg"><label class="flbl">Area</label><input class="finp" id="ep_present_area" placeholder="e.g. Anna Nagar"></div>
        <div class="fg"><label class="flbl">City</label><input class="finp" id="ep_present_city" placeholder="e.g. Chennai"></div>
        <div class="fg"><label class="flbl">District</label><select class="fsel" id="ep_present_district"><option value="">— Select —</option></select></div>
        <div class="fg"><label class="flbl">State</label><select class="fsel" id="ep_present_state"><option value="">— Select —</option></select></div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveEdit()">Save Changes</button>
    </div>
  </div>
</div>

<!-- CREATE PROFILE PAGE (full-viewport modal variant) -->
<div class="modal-overlay modal-page" id="createModal">
  <div class="modal">
    <div class="modal-head">
      <button class="modal-back" onclick="closeModal('createModal')" title="Back to My Profile">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        <span class="modal-title">Create My Profile</span>
      </button>
    </div>
    <div class="modal-body">
      <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 13px;font-size:12.5px;color:#92400e;margin-bottom:14px">
        Profile will be created with <strong>Pending Approval</strong> status. Admin will review.
      </div>

      <div class="msec">📸 Profile Photographs</div>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:14px">
        <div style="flex:1;min-width:120px">
          <label class="flbl">Photo 1 <span class="req">*</span></label>
          <div style="height:110px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:var(--bg);overflow:hidden" onclick="this.querySelector('input').click()">
            <img id="cp_photo1_prev" style="display:none;width:100%;height:100%;object-fit:cover"><span id="cp_photo1_ph" style="color:var(--ink4);font-size:11px;text-align:center">Upload Photo</span>
            <input type="file" id="cp_photo1_file" accept="image/*" style="display:none" onchange="upPhotoPreview(this,'cp_photo1')">
          </div>
        </div>
        <div style="flex:1;min-width:120px">
          <label class="flbl">Photo 2</label>
          <div style="height:110px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:var(--bg);overflow:hidden" onclick="this.querySelector('input').click()">
            <img id="cp_photo2_prev" style="display:none;width:100%;height:100%;object-fit:cover"><span id="cp_photo2_ph" style="color:var(--ink4);font-size:11px;text-align:center">Upload Photo</span>
            <input type="file" id="cp_photo2_file" accept="image/*" style="display:none" onchange="upPhotoPreview(this,'cp_photo2')">
          </div>
        </div>
        <div style="flex:1;min-width:120px">
          <label class="flbl">Photo 3</label>
          <div style="height:110px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:var(--bg);overflow:hidden" onclick="this.querySelector('input').click()">
            <img id="cp_photo3_prev" style="display:none;width:100%;height:100%;object-fit:cover"><span id="cp_photo3_ph" style="color:var(--ink4);font-size:11px;text-align:center">Upload Photo</span>
            <input type="file" id="cp_photo3_file" accept="image/*" style="display:none" onchange="upPhotoPreview(this,'cp_photo3')">
          </div>
        </div>
      </div>

      <div class="msec">👤 Personal Details</div>
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr 0.5fr;gap:0 10px">
        <div class="fg"><label class="flbl">Mobile <span class="req">*</span></label><input class="finp" id="cp_mobile" placeholder="10-digit mobile" type="tel" maxlength="10"></div>
        <div class="fg"><label class="flbl">Full Name <span class="req">*</span></label><input class="finp" id="cp_name"></div>
        <div class="fg"><label class="flbl">Gender <span class="req">*</span></label><select class="fsel" id="cp_gender"><option value="">-</option><option>Male</option><option>Female</option></select></div>
        <div class="fg"><label class="flbl">Date of Birth <span class="req">*</span> <span id="cp_age_display" style="font-weight:700;font-size:12px;margin-left:4px"></span></label><input class="finp" id="cp_dob" type="date"></div>
        <div class="fg"><label class="flbl">Age</label><input class="finp" id="cp_age_input" readonly style="background:#f0fdf4;font-weight:700;text-align:center;font-size:15px"></div>
      </div>
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
        <div class="fg"><label class="flbl">Religion <span class="req">*</span></label><select class="fsel" id="cp_religion"><option value="">-</option><option>Hindu</option><option>Muslim</option><option>Christian</option><option>Sikh</option><option>Jain</option><option>Buddhist</option></select></div>
        <div class="fg"><label class="flbl">Caste <span class="req">*</span></label><select class="fsel" id="cp_caste" onchange="populateSubcaste('cp_caste','cp_subcaste','')"><option value="">-</option></select></div>
        <div class="fg"><label class="flbl">Sub Caste</label><select class="fsel" id="cp_subcaste"><option value="">-</option></select></div>
        <div class="fg"><label class="flbl">Mother Tongue <span class="req">*</span></label><select class="fsel" id="cp_tongue"><option value="">-</option><option>Tamil</option><option>Telugu</option><option>Malayalam</option><option>Kannada</option><option>Hindi</option><option>English</option></select></div>
      </div>
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
        <div class="fg"><label class="flbl">Marital Status <span class="req">*</span></label><select class="fsel" id="cp_marital"><option value="">-</option><option>Unmarried</option><option>Divorced</option><option>Widowed</option><option>Separated</option></select></div>
        <div class="fg"><label class="flbl">Nationality</label><select class="fsel" id="cp_nationality"><option value="">-</option></select></div>
        <div class="fg"><label class="flbl">Own House</label><select class="fsel" id="cp_own_house"><option>Yes</option><option>No</option></select></div>
        <div class="fg"><label class="flbl">Born As</label><div style="display:flex;gap:4px"><input class="finp" id="cp_born_as_num" type="number" min="1" max="20" placeholder="e.g. 2" style="width:60px"><select class="fsel" id="cp_born_as_type"><option value="">-</option><option>Son</option><option>Daughter</option></select></div></div>
        <div class="fg"></div>
      </div>
      <input type="hidden" id="cp_age" value="">
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
        <div class="fg"><label class="flbl">Birth Hour</label><select class="fsel" id="cp_birth_hour"><option value="">-</option><option>01</option><option>02</option><option>03</option><option>04</option><option>05</option><option>06</option><option>07</option><option>08</option><option>09</option><option>10</option><option>11</option><option>12</option></select></div>
        <div class="fg"><label class="flbl">Birth Min</label><select class="fsel" id="cp_birth_min"><option value="">-</option><option>00</option><option>01</option><option>02</option><option>03</option><option>04</option><option>05</option><option>06</option><option>07</option><option>08</option><option>09</option><option>10</option><option>11</option><option>12</option><option>13</option><option>14</option><option>15</option><option>16</option><option>17</option><option>18</option><option>19</option><option>20</option><option>21</option><option>22</option><option>23</option><option>24</option><option>25</option><option>26</option><option>27</option><option>28</option><option>29</option><option>30</option><option>31</option><option>32</option><option>33</option><option>34</option><option>35</option><option>36</option><option>37</option><option>38</option><option>39</option><option>40</option><option>41</option><option>42</option><option>43</option><option>44</option><option>45</option><option>46</option><option>47</option><option>48</option><option>49</option><option>50</option><option>51</option><option>52</option><option>53</option><option>54</option><option>55</option><option>56</option><option>57</option><option>58</option><option>59</option></select></div>
        <div class="fg"><label class="flbl">AM / PM</label><select class="fsel" id="cp_birth_ampm"><option>AM</option><option>PM</option></select></div>
        <div class="fg"></div>
      </div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Place of Birth</label><input class="finp" id="cp_place_birth" placeholder="e.g. Pondicherry"></div>
        <div class="fg"><label class="flbl">Nativity</label><input class="finp" id="cp_nativity" list="nativity_list" placeholder="Type or select"></div>
        <div class="fg"><label class="flbl">Present Country</label><select class="fsel" id="cp_workplace"><option value="">-</option></select></div>
      </div>
      <div class="fg"><label class="flbl">Additional Details</label><textarea class="fta" id="cp_others" rows="2" placeholder="Talents, achievements, visa status..."></textarea></div>

      <div class="msec">👨‍👩‍👧‍👦 Family Details</div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Father's Name</label><input class="finp" id="cp_father"></div>
        <div class="fg"><label class="flbl">Father Status</label><select class="fsel" id="cp_father_alive"><option value="">-</option><option>Employed</option><option>Businessman</option><option>Professional</option><option>Retired</option><option>Not Employed</option><option>Passed Away</option></select></div>
        <div class="fg"><label class="flbl">Father's Occupation</label><input class="finp" id="cp_father_job" list="occupation_list" placeholder="Type or select"></div>
      </div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Mother's Name</label><input class="finp" id="cp_mother"></div>
        <div class="fg"><label class="flbl">Mother Status</label><select class="fsel" id="cp_mother_alive"><option value="">-</option><option>Home Maker</option><option>Employed</option><option>Businessman</option><option>Professional</option><option>Retired</option><option>Not Employed</option><option>Passed Away</option></select></div>
        <div class="fg"><label class="flbl">Mother's Occupation</label><input class="finp" id="cp_mother_job" list="occupation_list" placeholder="Type or select"></div>
      </div>
      <div class="fg" style="font-size:12px;font-weight:600;color:var(--ink3);margin:4px 0 6px">Siblings</div>
      <div style="overflow-x:auto;margin-bottom:14px">
        <table style="width:100%;border-collapse:collapse;font-size:12px;border:1px solid var(--border);border-radius:6px">
          <thead><tr style="background:var(--bg)"><th style="padding:6px 8px;border:1px solid var(--border)"></th><th style="padding:6px 8px;border:1px solid var(--border)">Elder Brother</th><th style="padding:6px 8px;border:1px solid var(--border)">Younger Brother</th><th style="padding:6px 8px;border:1px solid var(--border)">Elder Sister</th><th style="padding:6px 8px;border:1px solid var(--border)">Younger Sister</th></tr></thead>
          <tbody>
            <tr><td style="padding:6px 8px;border:1px solid var(--border);font-weight:600">Married</td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="cp_sib_eb_m" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="cp_sib_yb_m" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="cp_sib_es_m" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="cp_sib_ys_m" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td></tr>
            <tr><td style="padding:6px 8px;border:1px solid var(--border);font-weight:600">Unmarried</td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="cp_sib_eb_u" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="cp_sib_yb_u" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="cp_sib_es_u" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td>
              <td style="padding:4px;border:1px solid var(--border)"><select class="fsel" id="cp_sib_ys_u" style="font-size:11px;padding:4px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td></tr>
          </tbody>
        </table>
      </div>

      <div class="msec">⚖️ Physical Attributes</div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Height</label><select class="fsel" id="cp_height"><option value="">-</option><option>4ft 5in</option><option>4ft 6in</option><option>4ft 7in</option><option>4ft 8in</option><option>4ft 9in</option><option>4ft 10in</option><option>4ft 11in</option><option>5ft 0in</option><option>5ft 1in</option><option>5ft 2in</option><option>5ft 3in</option><option>5ft 4in</option><option>5ft 5in</option><option>5ft 6in</option><option>5ft 7in</option><option>5ft 8in</option><option>5ft 9in</option><option>5ft 10in</option><option>5ft 11in</option><option>6ft 0in</option><option>6ft 1in</option><option>6ft 2in</option><option>6ft 3in</option><option>6ft 4in</option><option>6ft 5in</option></select></div>
        <div class="fg"><label class="flbl">Weight</label><select class="fsel" id="cp_weight"><option value="">-</option><option>40 kg</option><option>42 kg</option><option>45 kg</option><option>48 kg</option><option>50 kg</option><option>52 kg</option><option>55 kg</option><option>56 kg</option><option>58 kg</option><option>60 kg</option><option>62 kg</option><option>63 kg</option><option>65 kg</option><option>67 kg</option><option>68 kg</option><option>69 kg</option><option>70 kg</option><option>71 kg</option><option>72 kg</option><option>73 kg</option><option>75 kg</option><option>78 kg</option><option>80 kg</option><option>82 kg</option><option>85 kg</option><option>88 kg</option><option>90 kg</option><option>95 kg</option><option>100 kg</option><option>105 kg</option><option>110 kg</option></select></div>
        <div class="fg"><label class="flbl">Blood Group</label><select class="fsel" id="cp_blood"><option value="">-</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>O+</option><option>O-</option><option>AB+</option><option>AB-</option></select></div>
      </div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Diet</label><select class="fsel" id="cp_diet"><option>Vegetarian</option><option>Non-Vegetarian</option><option>Eggetarian</option></select></div>
        <div class="fg"><label class="flbl">Complexion</label><select class="fsel" id="cp_complexion"><option value="">-</option><option>Very Fair</option><option>Fair</option><option>White</option><option>Wheatish</option><option>Brown</option><option>Dark</option></select></div>
        <div class="fg"><label class="flbl">Disability</label><select class="fsel" id="cp_disability"><option>No</option><option>Yes</option></select></div>
      </div>

      <div class="msec">🎓 Education & Occupation</div>
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
        <div class="fg"><label class="flbl">Qualification</label><select class="fsel" id="cp_qual"><option value="">-</option><optgroup label="Below 10th"><option>Below 10th</option><option>10th / SSLC</option></optgroup><optgroup label="Higher Secondary"><option>12th / HSC</option><option>ITI</option><option>Diploma</option></optgroup><optgroup label="Undergraduate"><option>B.A</option><option>B.Sc</option><option>B.Com</option><option>B.E / B.Tech</option><option>B.B.A</option><option>B.C.A</option><option>B.Ed</option><option>B.L / L.L.B</option><option>B.Arch</option><option>B.Pharm</option><option>B.D.S</option><option>M.B.B.S</option><option>B.V.Sc</option><option>B.P.T</option><option>B.Sc (Nursing)</option><option>B.S.W</option><option>B.F.A</option><option>B.Des</option></optgroup><optgroup label="Postgraduate"><option>M.A</option><option>M.Sc</option><option>M.Com</option><option>M.E / M.Tech</option><option>M.B.A</option><option>M.C.A</option><option>M.Ed</option><option>M.L / L.L.M</option><option>M.Pharm</option><option>M.D</option><option>M.S (Medical)</option><option>M.D.S</option><option>M.P.T</option><option>M.S.W</option><option>M.Des</option></optgroup><optgroup label="Doctorate / Research"><option>M.Phil</option><option>Ph.D</option><option>D.M</option><option>D.Litt</option></optgroup><optgroup label="Professional / Other"><option>C.A</option><option>C.S</option><option>I.C.W.A / C.M.A</option><option>C.F.A</option><option>I.A.S / I.P.S / I.F.S</option><option>Others</option></optgroup></select></div>
        <div class="fg"><label class="flbl">Occupation</label><select class="fsel" id="cp_job"><option value="">-</option><optgroup label="Government / Public Sector"><option>Central Govt Employee</option><option>State Govt Employee</option><option>PSU Employee</option><option>Defense - Army</option><option>Defense - Navy</option><option>Defense - Air Force</option><option>Police / CRPF / BSF</option><option>IAS / IPS / IFS Officer</option><option>Railway Employee</option><option>Postal Employee</option><option>TNPSC Group Service</option></optgroup><optgroup label="IT / Software"><option>Software Engineer</option><option>Software Developer</option><option>Data Analyst</option><option>Data Scientist</option><option>System Administrator</option><option>Network Engineer</option><option>Web Developer</option><option>UI/UX Designer</option><option>IT Manager</option><option>Cyber Security Analyst</option></optgroup><optgroup label="Engineering / Manufacturing"><option>Mechanical Engineer</option><option>Civil Engineer</option><option>Electrical Engineer</option><option>Electronics Engineer</option><option>Chemical Engineer</option><option>Production Engineer</option><option>Site Engineer</option><option>Quality Engineer</option><option>Project Manager</option></optgroup><optgroup label="Medical / Healthcare"><option>Doctor</option><option>Surgeon</option><option>Dentist</option><option>Pharmacist</option><option>Nurse</option><option>Physiotherapist</option><option>Lab Technician</option><option>Ayurveda / Siddha / Homeopathy</option></optgroup><optgroup label="Education / Teaching"><option>Professor</option><option>Lecturer</option><option>School Teacher</option><option>Private Tutor</option><option>Research Scholar</option></optgroup><optgroup label="Banking / Finance"><option>Bank Manager</option><option>Bank Employee</option><option>Chartered Accountant</option><option>Financial Analyst</option><option>Insurance Agent</option><option>Auditor</option><option>Tax Consultant</option></optgroup><optgroup label="Legal"><option>Advocate / Lawyer</option><option>Judge</option><option>Legal Advisor</option><option>Notary</option></optgroup><optgroup label="Business / Entrepreneurship"><option>Business Owner</option><option>Shopkeeper</option><option>Trader / Merchant</option><option>Real Estate Business</option><option>Exporter / Importer</option><option>Contractor</option><option>Freelancer</option><option>Startup Founder</option></optgroup><optgroup label="Agriculture / Farming"><option>Farmer / Agriculturist</option><option>Dairy Farmer</option><option>Plantation Owner</option><option>Agricultural Officer</option></optgroup><optgroup label="Skilled Trades"><option>Electrician</option><option>Plumber</option><option>Carpenter</option><option>Welder</option><option>Mechanic</option><option>Tailor</option><option>Goldsmith</option><option>Mason</option></optgroup><optgroup label="Media / Creative"><option>Journalist</option><option>Content Writer</option><option>Photographer</option><option>Graphic Designer</option><option>Film / TV Professional</option></optgroup><optgroup label="Abroad / NRI"><option>Working in Gulf</option><option>Working in USA</option><option>Working in UK</option><option>Working in Canada</option><option>Working in Australia</option><option>Working in Singapore</option><option>Working in Malaysia</option><option>Merchant Navy</option></optgroup><optgroup label="Other"><option>Private Company Employee</option><option>Supervisor / Foreman</option><option>Driver</option><option>Chef / Cook</option><option>Security Guard</option><option>Home Maker</option><option>Retired</option><option>Student</option><option>Not Employed</option><option>Others</option></optgroup></select></div>
        <div class="fg"><label class="flbl">Place of Work</label><input class="finp" id="cp_place_job"></div>
        <div class="fg"><label class="flbl">Monthly Income (₹)</label><input class="finp" id="cp_income" placeholder="e.g. 35000"></div>
      </div>

      <div class="msec">🪐 Astrology</div>
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
        <div class="fg"><label class="flbl">Star</label><select class="fsel" id="cp_star"><option value="">-</option><option>Ashwini</option><option>Bharani</option><option>Karthigai</option><option>Rohini</option><option>Mirigasirisham</option><option>Thiruvathirai</option><option>Punarpoosam</option><option>Poosam</option><option>Ayilyam</option><option>Makam</option><option>Pooram</option><option>Uthiram</option><option>Hastham</option><option>Chithirai</option><option>Swathi</option><option>Visakam</option><option>Anusham</option><option>Kettai</option><option>Moolam</option><option>Pooradam</option><option>Uthradam</option><option>Thiruvonam</option><option>Avittam</option><option>Sadhayam</option><option>Puratathi</option><option>Uthirattathi</option><option>Revathi</option></select></div>
        <div class="fg"><label class="flbl">Raasi</label><select class="fsel" id="cp_raasi"><option value="">-</option><option>Mesham</option><option>Rishabam</option><option>Midhunam</option><option>Kadagam</option><option>Simham</option><option>Kanni</option><option>Thulam</option><option>Viruchigam</option><option>Dhanusu</option><option>Makaram</option><option>Kumbam</option><option>Meenam</option></select></div>
        <div class="fg"><label class="flbl">Paadam</label><select class="fsel" id="cp_paadam"><option value="">-</option><option>1st Paadam</option><option>2nd Paadam</option><option>3rd Paadam</option><option>4th Paadam</option></select></div>
        <div class="fg"><label class="flbl">Lagnam</label><select class="fsel" id="cp_lagnam"><option value="">-</option><option>Mesham</option><option>Rishabam</option><option>Midhunam</option><option>Kadagam</option><option>Simham</option><option>Kanni</option><option>Thulam</option><option>Viruchigam</option><option>Dhanusu</option><option>Makaram</option><option>Kumbam</option><option>Meenam</option></select></div>
      </div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Gothram</label><input class="finp" id="cp_gothram"></div>
        <div class="fg"><label class="flbl">Dosham</label><select class="fsel" id="cp_dosham"><option>No</option><option>Yes</option><option>Partial</option></select></div>
        <div class="fg" id="cp_dosham_type_wrap" style="display:none"><label class="flbl">Dosham Type</label><select class="fsel" id="cp_dosham_type"><option value="">— Select Dosham Type —</option></select></div>
      </div>

      <div class="msec">💑 Partner Expectations</div>
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
        <div class="fg"><label class="flbl">Qualification</label><input class="finp" id="cp_p_qual" list="partner_qual_list" placeholder="e.g. Any Degree"></div>
        <div class="fg"><label class="flbl">Job Preference</label><input class="finp" id="cp_p_job" list="partner_job_list" placeholder="e.g. Any"></div>
        <div class="fg"><label class="flbl">Job Requirement</label><select class="fsel" id="cp_p_job_req"><option>Optional</option><option>Must</option><option>Not Required</option></select></div>
        <div class="fg"><label class="flbl">Income Expectation</label><input class="finp" id="cp_p_income" placeholder="e.g. 30000"></div>
      </div>
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
        <div class="fg"><label class="flbl">Age From</label><input class="finp" id="cp_p_agefrom" type="number" min="18" max="70"></div>
        <div class="fg"><label class="flbl">Age To</label><input class="finp" id="cp_p_ageto" type="number" min="18" max="70"></div>
        <div class="fg"><label class="flbl">Diet Preference</label><select class="fsel" id="cp_p_diet"><option>Vegetarian</option><option>Non-Vegetarian</option><option>Any</option></select></div>
        <div class="fg"><label class="flbl">Marital Status</label><select class="fsel" id="cp_p_marital"><option>Unmarried</option><option>Divorced</option><option>Widowed</option><option>Any</option></select></div>
      </div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Horoscope Required?</label><select class="fsel" id="cp_p_horoscope"><option>No</option><option>Yes</option><option>Not Applicable</option></select></div>
        <div class="fg"></div><div class="fg"></div>
      </div>
      <div class="fg"><label class="flbl">Caste Preference</label><input type="hidden" id="cp_p_caste"><div id="cp_p_caste_box" style="border:1px solid var(--border);border-radius:8px;padding:8px 10px;background:#faf9f7;max-height:160px;overflow-y:auto"></div></div>
      <div class="fg" id="cp_p_subcaste_wrap" style="display:none"><label class="flbl">Sub Caste Preference</label><input type="hidden" id="cp_p_subcaste"><div id="cp_p_subcaste_box" style="border:1px solid var(--border);border-radius:8px;padding:8px 10px;background:#faf9f7;max-height:140px;overflow-y:auto"></div></div>
      <div class="fg"><label class="flbl">Other Requirements</label><textarea class="fta" id="cp_p_other" rows="2" placeholder="Any other expectations..."></textarea></div>

      <div class="msec">🔮 Horoscope Photos</div>
      <div class="fg2">
        <div class="fg">
          <label class="flbl">Rasi Chart Photo</label>
          <div style="height:100px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:var(--bg);overflow:hidden" onclick="this.querySelector('input').click()">
            <img id="cp_rasi_prev" style="display:none;width:100%;height:100%;object-fit:contain"><span id="cp_rasi_ph" style="color:var(--ink4);font-size:11px">Upload Rasi Chart</span>
            <input type="file" id="cp_rasi_file" accept="image/*" style="display:none" onchange="upPhotoPreview(this,'cp_rasi')">
          </div>
        </div>
        <div class="fg">
          <label class="flbl">Amsam Chart Photo</label>
          <div style="height:100px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:var(--bg);overflow:hidden" onclick="this.querySelector('input').click()">
            <img id="cp_amsam_prev" style="display:none;width:100%;height:100%;object-fit:contain"><span id="cp_amsam_ph" style="color:var(--ink4);font-size:11px">Upload Amsam Chart</span>
            <input type="file" id="cp_amsam_file" accept="image/*" style="display:none" onchange="upPhotoPreview(this,'cp_amsam')">
          </div>
        </div>
      </div>

      <div class="msec">📞 Communication</div>
      <div class="fg3">
        <div class="fg"><label class="flbl">Email</label><input class="finp" id="cp_email" type="email"></div>
        <div class="fg"><label class="flbl">Contact Person</label><input class="finp" id="cp_contact_person"></div>
        <div class="fg"><label class="flbl">Alt Mobile</label><input class="finp" id="cp_alt_mobile" type="tel" maxlength="10" placeholder="10-digit number"></div>
      </div>
      <div class="fg2">
        <div class="fg"><label class="flbl">Permanent Address</label><textarea class="fta" id="cp_addr" rows="2" placeholder="Door No, Street, Area..."></textarea></div>
        <div class="fg"><label class="flbl">Present Address</label><textarea class="fta" id="cp_present_addr" rows="2" placeholder="Door No, Street, Area, City, District, State"></textarea></div>
      </div>
      <div class="fg" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 10px">
        <div class="fg"><label class="flbl">Area</label><input class="finp" id="cp_present_area" placeholder="e.g. Anna Nagar"></div>
        <div class="fg"><label class="flbl">City</label><input class="finp" id="cp_present_city" placeholder="e.g. Chennai"></div>
        <div class="fg"><label class="flbl">District</label><select class="fsel" id="cp_present_district"><option value="">— Select —</option></select></div>
        <div class="fg"><label class="flbl">State</label><select class="fsel" id="cp_present_state"><option value="">— Select —</option></select></div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-ghost" onclick="closeModal('createModal')">Cancel</button>
      <button class="btn btn-primary" onclick="submitCreate()">Create Profile</button>
    </div>
  </div>
</div>

<!-- PAYMENT PAGE -->
<div id="paymentPage" style="display:none;position:fixed;inset:0;z-index:2000;background:var(--bg);overflow-y:auto;animation:fadeIn .3s ease">
  <div style="max-width:680px;margin:0 auto;padding:24px 20px 60px">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:28px;padding-bottom:20px;border-bottom:1px solid var(--border)">
      <button onclick="closePaymentPage()" class="btn btn-outline btn-sm">Back</button>
      <div>
        <div style="font-family:var(--serif);font-size:1.35rem">Complete Registration</div>
        <div style="font-size:12.5px;color:var(--ink3);margin-top:1px">Choose a plan and make payment</div>
      </div>
    </div>
    <div id="payProfSummary" style="background:var(--card);border-radius:var(--radius);border:1px solid var(--border);padding:16px 18px;margin-bottom:24px;display:flex;align-items:center;gap:14px;box-shadow:var(--shadow)">
      <div id="payProfAvatar" style="width:46px;height:46px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:#fff;flex-shrink:0">?</div>
      <div style="flex:1"><div id="payProfName" style="font-weight:700;font-size:15px">-</div><div id="payProfMeta" style="font-size:12.5px;color:var(--ink3);margin-top:2px">-</div></div>
    </div>
    <div id="payStepPlans">
      <div style="text-align:center;margin-bottom:22px">
        <div style="font-family:var(--serif);font-size:1.55rem;color:var(--ink1);margin-bottom:4px">Choose Your Plan</div>
        <div style="font-size:13px;color:var(--ink3)">Pick a plan and pay securely via PayU</div>
      </div>
      <div id="payPlansGrid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px"></div>
      <div id="payNowBtnWrap" style="display:none;margin-top:20px">
        <button id="payNowBtn" onclick="confirmPlanSelection()" class="btn btn-primary" style="width:100%;padding:15px;font-size:15.5px;font-weight:800;border-radius:12px;letter-spacing:.02em;box-shadow:0 6px 18px rgba(194,85,61,.25)">Pay Now</button>
      </div>
      <div style="text-align:center;margin-top:22px">
        <a onclick="skipPayment()" style="font-size:13px;color:var(--ink3);text-decoration:underline;cursor:pointer">Skip and pay later</a>
      </div>
    </div>
    <div id="payStepPayment" style="display:none">
      <div id="paySelectedPlanCard" style="background:linear-gradient(135deg,var(--sidebar) 0%,#2d2d5e 100%);border-radius:var(--radius);padding:18px 20px;margin-bottom:22px;color:#fff"></div>
      <div id="payOptsGrid" style="display:flex;flex-direction:column;gap:12px"></div>
      <button onclick="backToPlans()" class="btn btn-outline" style="margin-top:16px;width:100%">Back to Plans</button>
    </div>
    <div id="payStepDone" style="display:none;text-align:center;padding:40px 20px">
      <div style="font-size:52px;margin-bottom:14px">&#127881;</div>
      <div style="font-family:var(--serif);font-size:1.5rem;margin-bottom:8px">Registration Submitted!</div>
      <div style="font-size:13.5px;color:var(--ink3);line-height:1.75;margin-bottom:6px" id="payDoneMsg"></div>
      <button onclick="closePaymentPage()" class="btn btn-primary" style="padding:13px 32px;font-size:14px;margin-top:18px">Go to My Profile</button>
    </div>
  </div>
</div>

<script src="face-api.min.js"></script>
<script src="photo-utils.js"></script>
<script src="input-validation.js"></script>
<script src="form-autosave.js"></script>
<script src="dob-age.js?v=2"></script>
<script src="subcaste-data.js?v=2"></script>
<script src="place-suggest.js"></script>
<script src="nationality-data.js"></script>
<script src="gothram-data.js"></script>
<script src="dosham-data.js"></script>
<script src="partner-caste.js?v=2"></script>
<script src="mobile-check.js"></script>
<script src="address-extract.js"></script>
<script src="combobox.js?v=1"></script>
<script>
// ===== PHOTO PREVIEW HELPER (with face detection + compression) =====
// Store processed files for form submission
const _processedPhotos = {};

async function upPhotoPreview(input, prefix) {
  const file = input.files[0];
  if (!file) return;
  const prev = document.getElementById(prefix + '_prev');
  const ph = document.getElementById(prefix + '_ph');

  // Determine photo type
  const isHoroscope = prefix.includes('rasi') || prefix.includes('amsam');
  const isProfilePhoto = prefix.includes('photo1');

  // Show loading state
  if (ph) { ph.textContent = 'Processing...'; ph.style.color = 'var(--accent)'; }

  try {
    const result = await PhotoUtils.processPhoto(file, { isProfilePhoto, isHoroscope });
    prev.src = result.previewUrl;
    prev.style.display = 'block';
    if (ph) ph.style.display = 'none';
    // Store processed file for later submission
    _processedPhotos[prefix] = PhotoUtils.blobToFile(result.blob, file.name);
    toast((isProfilePhoto ? 'Face-cropped' : 'Compressed') + ' (' + result.sizeKB + ' KB)');
  } catch (e) {
    // Reset input
    input.value = '';
    delete _processedPhotos[prefix];
    prev.style.display = 'none';
    if (ph) { ph.textContent = 'Upload Photo'; ph.style.display = ''; ph.style.color = 'var(--ink4)'; }
    showPopup('warn', 'Photo Rejected', e.message);
  }
}

// ===== API HELPER =====
const API = 'api/';
async function api(endpoint, opts = {}) {
  const url = API + endpoint;
  const config = { credentials: 'same-origin', ...opts };
  if (opts.body && typeof opts.body === 'object') {
    config.headers = { 'Content-Type': 'application/json', ...(opts.headers || {}) };
    config.body = JSON.stringify(opts.body);
  }
  const res = await fetch(url, config);
  const data = await res.json();
  if (!data.ok && data.error) throw new Error(data.error);
  return data;
}
function apiPost(endpoint, body) { return api(endpoint, { method: 'POST', body }); }
function apiGet(endpoint) { return api(endpoint); }

// ===== STATE =====
let mob = '', profile = null, panelCtrl = null;

function initials(n) { return (n || '?').split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2); }
function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

// ===== POPUP / TOAST =====
let _popTimer;
function showPopup(type, title, msg, dur = 5000) {
  const icons = { err: '\u{1F6AB}', ok: '\u2705', warn: '\u26A0\uFE0F' };
  document.getElementById('uPopIcon').textContent = icons[type] || '';
  document.getElementById('uPopTitle').textContent = title;
  document.getElementById('uPopMsg').textContent = msg;
  const el = document.getElementById('uPopup');
  el.className = 'u-popup pop-' + type + ' show';
  clearTimeout(_popTimer);
  if (dur > 0) _popTimer = setTimeout(closePopup, dur);
}
function closePopup() { document.getElementById('uPopup').className = 'u-popup'; }
function toast(msg, type = 'ok') {
  const t = document.getElementById('uToast');
  const ic = { ok: '\u2713', err: '\u2717', warn: '!' };
  t.innerHTML = '<span>' + (ic[type] || '') + '</span> ' + esc(msg);
  t.style.background = type === 'err' ? '#991b1b' : type === 'warn' ? '#92400e' : '#1c1917';
  t.style.transform = 'translateY(0)'; t.style.opacity = '1';
  setTimeout(() => { t.style.transform = 'translateY(60px)'; t.style.opacity = '0'; }, 3000);
}
function showMsg(msg, type = 'err') { const el = document.getElementById('loginMsg'); el.textContent = msg; el.className = 'msg msg-' + type; el.style.display = ''; }
function clearMsg() { document.getElementById('loginMsg').style.display = 'none'; }

// ===== LOGIN FLOW =====
let otpTmr = null, otpTries = 0;

function onMobileType(el) {
  const valid = /^\d{10}$/.test(el.value.trim());
  document.getElementById('sendOtpBtn').classList.toggle('ready', valid);
  if (document.getElementById('otpSection').style.display === 'block') resetLogin();
}
async function sendOtp() {
  clearMsg();
  const mobile = document.getElementById('lg_mobile').value.trim();
  if (!/^\d{10}$/.test(mobile)) { showMsg('Enter a valid 10-digit mobile.'); return; }
  mob = mobile;
  try {
    // Check if direct login is enabled for this mobile
    const dlCheck = await apiPost('auth.php', { action: 'check_direct', mobile });
    if (dlCheck.direct) {
      // Direct login — skip OTP
      const dlResp = await apiPost('auth.php', { action: 'direct_login', mobile });
      if (dlResp.ok) {
        await launchApp();
        return;
      }
    }
    const data = await apiPost('auth.php', { action: 'send', mobile });
    document.getElementById('lg_mobile').disabled = true;
    const btn = document.getElementById('sendOtpBtn');
    btn.textContent = '\u2713 Sent'; btn.classList.add('sent');
    let sentHtml = 'OTP sent to <strong>' + mobile.slice(0,2) + '\u2022\u2022\u2022\u2022\u2022\u2022' + mobile.slice(-2) + '</strong>';
    // If OTP returned (SMS failed fallback or demo), show it
    if (data.otp) {
      sentHtml += '<div style="margin-top:8px;background:#f0fdf4;border:1.5px dashed #86efac;border-radius:8px;padding:8px 12px;text-align:center">'
        + '<div style="font-size:10px;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:.1em;margin-bottom:3px">Your OTP</div>'
        + '<div style="font-family:monospace;font-size:24px;font-weight:800;letter-spacing:6px;color:#1a1a2e">' + data.otp + '</div></div>';
    }
    document.getElementById('otpSentLbl').innerHTML = sentHtml;
    document.getElementById('otpSection').style.display = 'block';
    ['ob1','ob2','ob3','ob4'].forEach(id => { const e = document.getElementById(id); if (e) e.value = ''; });
    setTimeout(() => document.getElementById('ob1')?.focus(), 160);
    startTimer(120);
  } catch (e) {
    showMsg(e.message);
  }
}

function omov(el, nx) { el.value = el.value.replace(/\D/g, '').slice(-1); if (el.value && nx) document.getElementById(nx)?.focus(); }
function obk(e, el, pv) { if (e.key === 'Backspace' && !el.value && pv) document.getElementById(pv)?.focus(); }

function startTimer(secs) {
  clearInterval(otpTmr);
  const lbl = document.getElementById('otpTimer'), rsnd = document.getElementById('resendBtn');
  rsnd.style.display = 'none';
  let r = secs;
  const resendAfter = 30;
  const tick = () => {
    if (r <= 0) { clearInterval(otpTmr); lbl.textContent = 'OTP expired'; rsnd.style.display = ''; return; }
    lbl.textContent = '\u23F1 ' + r + 's';
    if ((secs - r) >= resendAfter && rsnd.style.display === 'none') rsnd.style.display = '';
    r--;
  };
  tick(); otpTmr = setInterval(tick, 1000);
}

async function resendOtp() {
  try {
    const data = await apiPost('auth.php', { action: 'send', mobile: mob });
    let resendHtml = 'OTP sent to <strong>' + mob.slice(0,2) + '\u2022\u2022\u2022\u2022\u2022\u2022' + mob.slice(-2) + '</strong>';
    if (data.otp) {
      resendHtml += '<div style="margin-top:8px;background:#f0fdf4;border:1.5px dashed #86efac;border-radius:8px;padding:8px 12px;text-align:center">'
        + '<div style="font-size:10px;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:.1em;margin-bottom:3px">Your OTP</div>'
        + '<div style="font-family:monospace;font-size:24px;font-weight:800;letter-spacing:6px;color:#1a1a2e">' + data.otp + '</div></div>';
    }
    document.getElementById('otpSentLbl').innerHTML = resendHtml;
    ['ob1','ob2','ob3','ob4'].forEach(id => { const e = document.getElementById(id); if (e) e.value = ''; });
    startTimer(120);
    document.getElementById('ob1')?.focus();
    toast('New OTP sent');
  } catch (e) { showPopup('err', 'Error', e.message); }
}

async function verifyOtp() {
  clearMsg();
  const digits = ['ob1','ob2','ob3','ob4'].map(id => (document.getElementById(id)?.value || '').trim()).join('');
  if (digits.length < 4) { showPopup('warn', 'Incomplete', 'Enter all 4 OTP digits.'); return; }
  try {
    await apiPost('auth.php', { action: 'verify', mobile: mob, otp: digits });
    clearInterval(otpTmr);
    launchApp();
  } catch (e) {
    otpTries++;
    const boxes = document.getElementById('otpBoxes');
    boxes.classList.remove('shake'); void boxes.offsetWidth; boxes.classList.add('shake');
    setTimeout(() => boxes.classList.remove('shake'), 400);
    if (otpTries >= 3) { showPopup('err', 'Too Many Attempts', 'Click "Change Number" to retry.'); return; }
    showPopup('err', 'Wrong OTP', e.message);
    ['ob1','ob2','ob3','ob4'].forEach(id => { const e2 = document.getElementById(id); if (e2) e2.value = ''; });
    document.getElementById('ob1')?.focus();
  }
}

function resetLogin() {
  document.getElementById('otpSection').style.display = 'none';
  document.getElementById('lg_mobile').disabled = false;
  document.getElementById('lg_mobile').value = '';
  const btn = document.getElementById('sendOtpBtn');
  btn.textContent = 'Send OTP'; btn.classList.remove('ready', 'sent');
  clearInterval(otpTmr); clearMsg(); mob = ''; otpTries = 0;
}

async function doLogout() {
  try { await apiPost('auth.php', { action: 'logout' }); } catch (e) {}
  // Also clear frontend contact session
  try { await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'contact_logout' }), credentials:'include' }); } catch(e) {}
  mob = ''; profile = null;
  // Redirect to frontend homepage
  window.location.href = '/';
}

// ===== APP LAUNCH =====
async function launchApp() {
  document.getElementById('loginPage').style.display = 'none';
  document.getElementById('appShell').classList.add('open');
  await loadProfile();
  await loadPanelCtrl();
  applyPanelCtrl();
  fillSidebar();
  renderMyProfile();
  setActions('myProfile');
  showWelcomeBalance();

  // Auto-open Create Profile modal when arriving via homepage "Register" link (?create=1)
  const launchParams = new URLSearchParams(window.location.search);
  if (launchParams.get('create') === '1' && !profile && upAllowed('feat_create_profile')) {
    history.replaceState({}, '', location.pathname);
    setTimeout(() => openCreate(), 150);
  }
}

async function showWelcomeBalance() {
  try {
    const data = await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'user_limits', mobile: mob }), credentials:'include' }).then(r=>r.json());
    if (!data.ok) return;
    const l = data.limits, u = data.used;
    const name = profile ? profile.name : mob;
    const cpId = profile ? profile.cp_id : '';

    const balItem = (label, allotted, used, icon, color) => {
      const remaining = allotted > 0 ? Math.max(0, allotted - used) : '∞';
      const pct = allotted > 0 ? Math.min(100, Math.round(used/allotted*100)) : 0;
      const barColor = pct >= 90 ? '#dc2626' : pct >= 70 ? '#d97706' : color;
      return `<div style="background:#fff;border-radius:12px;padding:14px 16px;border:1px solid #e5e7eb">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
          <span style="font-size:18px">${icon}</span>
          <span style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.5px">${label}</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:6px">
          <div><span style="font-size:24px;font-weight:800;color:${barColor}">${remaining}</span>
          <span style="font-size:12px;color:#9ca3af;margin-left:4px">remaining</span></div>
          <span style="font-size:11px;color:#6b7280">${used} used / ${allotted > 0 ? allotted : '∞'}</span>
        </div>
        <div style="height:6px;background:#f3f4f6;border-radius:3px;overflow:hidden">
          <div style="height:100%;width:${allotted>0?pct:0}%;background:${barColor};border-radius:3px;transition:width .5s"></div>
        </div>
      </div>`;
    };

    const overlay = document.createElement('div');
    overlay.id = 'welcomeBalanceOverlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px';
    overlay.innerHTML = `
      <div style="background:linear-gradient(135deg,#f8fafc,#fff);border-radius:20px;max-width:440px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,0.25);overflow:hidden;animation:popIn .3s ease">
        <div style="background:linear-gradient(135deg,#1e3a5f,#2563eb);padding:22px 20px;text-align:center">
          <div style="font-size:28px;margin-bottom:6px">👋</div>
          <div style="color:#fff;font-size:18px;font-weight:700;font-family:Georgia,serif">Welcome, ${esc(name)}!</div>
          ${cpId ? `<div style="color:rgba(255,255,255,0.6);font-size:12px;margin-top:4px">${esc(cpId)}</div>` : ''}
        </div>
        <div style="padding:20px">
          <div style="font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;text-align:center">Contact View Balance</div>
          <div style="display:flex;flex-direction:column;gap:10px">
            ${balItem('Today', l.day, u.day, '📅', '#2563eb')}
            ${balItem('This Month', l.month, u.month, '📆', '#7c3aed')}
            ${balItem('Lifetime', l.total, u.total, '📊', '#059669')}
          </div>
          <button onclick="document.getElementById('welcomeBalanceOverlay').remove()"
            style="width:100%;margin-top:16px;padding:12px;background:linear-gradient(135deg,#1e3a5f,#2563eb);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;letter-spacing:.5px">
            Continue →
          </button>
        </div>
      </div>`;
    document.body.appendChild(overlay);
    // Auto-dismiss after 15s
    setTimeout(() => { const el = document.getElementById('welcomeBalanceOverlay'); if (el) el.remove(); }, 15000);
  } catch(e) { console.log('Welcome balance error:', e); }
}

async function loadProfile() {
  try {
    const data = await apiGet('profile.php');
    profile = data.profile || null;
  } catch (e) { profile = null; }
}

async function loadPanelCtrl() {
  try {
    const data = await apiGet('panel.php');
    panelCtrl = data.ctrl || null;
  } catch (e) { panelCtrl = null; }
}

function upAllowed(id) { if (!panelCtrl) return true; return panelCtrl[id] !== false; }

function applyPanelCtrl() {
  document.querySelectorAll('.sb-btn[data-page]').forEach(btn => {
    btn.style.display = upAllowed(btn.dataset.page) ? '' : 'none';
  });
}

function fillSidebar() {
  const nm = profile?.name || mob;
  document.getElementById('sbAv').textContent = initials(nm);
  document.getElementById('sbName').textContent = nm;
  document.getElementById('sbMob').textContent = mob;
}

// ===== NAVIGATION =====
const TITLES = { myProfile: 'My Profile', suggestions: 'Suggestions', basicMatches: 'Basic Matches', mutualMatches: 'Mutual Matches', allProfiles: 'All Profiles', myBills: 'My Bills', myActivity: 'My Activity', loginHistory: 'Login History', myReports: 'My Reports', profileViewLog: 'Profile View Log', contactLog: 'Contact View Log', mySettings: 'Settings', addProfile: 'Add Profile', addOrder: 'Pay Later' };
const SEC_TO_PAGE = { myProfile: 'page_profile', suggestions: 'page_suggestions', basicMatches: 'page_matches', mutualMatches: 'page_matches', allProfiles: 'page_allprofiles', myBills: 'page_bills', myActivity: 'page_activity', loginHistory: 'page_loginhistory', myReports: 'page_myreports', profileViewLog: 'page_profileviewlog', contactLog: 'page_contactlog', mySettings: 'page_settings', addProfile: '', addOrder: 'page_addorder' };

// ===== ADD PROFILE (USER PANEL) =====
let upApSelectedPlan = 'free';
function upApSelectPlan(plan) {
  upApSelectedPlan = plan;
  document.getElementById('up_ap_plan').value = plan;
  document.querySelectorAll('.up-ap-plan').forEach(c => {
    const sel = c.dataset.plan === plan;
    c.style.border = sel ? '2px solid #8B0000' : '2px solid #E0C0C8';
    c.style.background = sel ? 'linear-gradient(135deg,#FFF0F2,#FFF8F5)' : '#FFFAF9';
    c.style.boxShadow = sel ? '0 4px 14px rgba(139,0,0,0.16)' : 'none';
    const ex = c.querySelector('.up-ap-badge'); if (ex) ex.remove();
    if (sel) { const b = document.createElement('div'); b.className='up-ap-badge'; b.style.cssText='position:absolute;top:-9px;right:10px;background:linear-gradient(135deg,#8B0000,#C41E3A);color:white;font-size:0.62rem;font-weight:700;padding:2px 8px;border-radius:20px'; b.textContent='✦ SELECTED'; c.appendChild(b); }
  });
}
function upApReset() {
  ['up_ap_name','up_ap_place_birth','up_ap_nativity','up_ap_qual','up_ap_job','up_ap_income','up_ap_subcaste'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
  ['up_ap_gender','up_ap_tongue','up_ap_marital','up_ap_blood','up_ap_height','up_ap_weight','up_ap_diet','up_ap_caste','up_ap_star','up_ap_raasi','up_ap_lagnam','up_ap_dosham'].forEach(id => { const el=document.getElementById(id); if(el) el.selectedIndex=0; });
  document.getElementById('up_ap_dob').value='';
  upApSelectPlan('free');
  document.getElementById('upApResult').style.display='none';
}
async function upApSubmit() {
  if (profile) { popup('error','Already Exists','You already have a profile. Only one profile per user.'); return; }
  const mobile = mob || document.getElementById('up_ap_mobile').value.trim();
  const name = document.getElementById('up_ap_name').value.trim();
  const gender = document.getElementById('up_ap_gender').value;
  const dob = DobAge.getIso('up_ap_dob');
  const religion = document.getElementById('up_ap_religion').value;
  const caste = document.getElementById('up_ap_caste').value;
  const tongue = document.getElementById('up_ap_tongue').value;
  const marital = document.getElementById('up_ap_marital').value;
  if (!mobile || mobile.length < 10) { popup('error','Validation Error','Enter valid 10-digit mobile'); return; }
  if (!name) { popup('error','Validation Error','Name is required'); return; }
  if (!gender) { popup('error','Validation Error','Gender is required'); return; }
  if (!dob) { popup('error','Validation Error','Date of Birth is required'); return; }
  const upApAgeErr = DobAge.validateAge('up_ap_dob', gender);
  if (upApAgeErr) { popup('error','Age Not Eligible', upApAgeErr); return; }
  if (!religion) { popup('error','Validation Error','Religion is required'); return; }
  if (!caste) { popup('error','Validation Error','Caste is required'); return; }
  if (!tongue) { popup('error','Validation Error','Mother Tongue is required'); return; }
  if (!marital) { popup('error','Validation Error','Marital Status is required'); return; }
  // Input format validation
  const upApValErrs = InputValidator.validateAll('up_ap_');
  if (upApValErrs.length > 0) { popup('error','Invalid Input', upApValErrs[0].msg + ' (' + upApValErrs[0].id.replace('up_ap_','') + ')'); document.getElementById(upApValErrs[0].id)?.focus(); return; }

  try {
    const fd = new FormData();
    fd.append('contactNumber', mobile);
    fd.append('name', name);
    fd.append('gender', gender);
    fd.append('dob', dob);
    fd.append('religion', religion);
    fd.append('caste', caste);
    fd.append('motherTongue', tongue);
    fd.append('maritalStatus', marital);
    fd.append('placeBirth', document.getElementById('up_ap_place_birth')?.value || '');
    fd.append('nativity', document.getElementById('up_ap_nativity')?.value || '');
    fd.append('bloodGroup', document.getElementById('up_ap_blood')?.value || '');
    fd.append('height', document.getElementById('up_ap_height')?.value || '');
    fd.append('weight', document.getElementById('up_ap_weight')?.value || '');
    fd.append('diet', document.getElementById('up_ap_diet')?.value || '');
    fd.append('qualification', document.getElementById('up_ap_qual')?.value || '');
    fd.append('job', document.getElementById('up_ap_job')?.value || '');
    fd.append('incomeMonth', document.getElementById('up_ap_income')?.value || '');
    fd.append('subCaste', document.getElementById('up_ap_subcaste')?.value || '');
    fd.append('star', document.getElementById('up_ap_star')?.value || '');
    fd.append('raasi', document.getElementById('up_ap_raasi')?.value || '');
    fd.append('laknam', document.getElementById('up_ap_lagnam')?.value || '');
    fd.append('dosham', document.getElementById('up_ap_dosham')?.value || '');
    fd.append('scheme', upApSelectedPlan || 'free');

    const resp = await fetch(API + 'public.php', { method: 'POST', body: fd, credentials: 'same-origin' });
    const data = await resp.json();
    if (!resp.ok || data.error) throw new Error(data.error || 'Failed to create profile');

    // Show summary
    const fields = [
      ['Mobile', mobile], ['Name', name], ['Gender', gender], ['Date of Birth', dob],
      ['Religion', religion], ['Caste', caste],
      ['Mother Tongue', tongue], ['Marital Status', marital],
      ['Place of Birth', document.getElementById('up_ap_place_birth')?.value],
      ['Nativity', document.getElementById('up_ap_nativity')?.value],
      ['Blood Group', document.getElementById('up_ap_blood')?.value],
      ['Height', document.getElementById('up_ap_height')?.value],
      ['Weight', document.getElementById('up_ap_weight')?.value],
      ['Diet', document.getElementById('up_ap_diet')?.value],
      ['Qualification', document.getElementById('up_ap_qual')?.value],
      ['Occupation', document.getElementById('up_ap_job')?.value],
      ['Monthly Income', document.getElementById('up_ap_income')?.value],
      ['Sub Caste', document.getElementById('up_ap_subcaste')?.value],
      ['Star', document.getElementById('up_ap_star')?.value],
      ['Raasi', document.getElementById('up_ap_raasi')?.value],
      ['Lagnam', document.getElementById('up_ap_lagnam')?.value],
      ['Dosham', document.getElementById('up_ap_dosham')?.value],
      ['Plan Selected', upApSelectedPlan],
      ['CP ID', data.cp_id || ''],
    ].filter(([,v]) => v);
    const rows = fields.map(([k,v],i) => `<tr><td style="padding:9px 16px;font-size:0.78rem;font-weight:700;color:#7A1020;text-transform:uppercase;border-bottom:1px solid #F0E0E4;background:${i%2===0?'white':'#FFFAF9'}">${k}</td><td style="padding:9px 16px;font-size:0.88rem;color:#2A0A0E;border-bottom:1px solid #F0E0E4;background:${i%2===0?'white':'#FFFAF9'}">${v}</td></tr>`).join('');
    const el = document.getElementById('upApResult');
    el.innerHTML = `<div style="background:white;border-radius:14px;box-shadow:var(--shadow);border:1px solid rgba(196,30,58,0.12);overflow:hidden"><div style="background:linear-gradient(135deg,#8B0000,#C41E3A);padding:13px 20px;display:flex;align-items:center;gap:9px"><span style="font-size:16px">✅</span><span style="color:white;font-family:var(--serif);font-size:1rem">Profile Submitted — Registration Summary</span></div><div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse"><thead><tr style="background:#FFF0F2"><th style="padding:9px 16px;text-align:left;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B0000;border-bottom:1px solid #E0C0C8;width:38%">Field</th><th style="padding:9px 16px;text-align:left;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B0000;border-bottom:1px solid #E0C0C8">Value</th></tr></thead><tbody>${rows}</tbody></table></div></div>`;
    el.style.display='block';
    el.scrollIntoView({behavior:'smooth',block:'start'});
    FormAutoSave.clear('up_quick_create');
    popup('ok','Success','Profile created successfully! CP ID: ' + (data.cp_id || ''));

    // Reload profile if this is the logged-in user's mobile
    try {
      const profData = await apiGet('profile.php');
      if (profData.profile) { profile = profData.profile; fillSidebar(); }
    } catch(e) {}
  } catch(e) {
    popup('error','Error', e.message || 'Failed to create profile');
  }
}

// ===== ADD ORDER (USER PANEL) =====
let upAoSelectedPlan = '';
let upAoRawAmount = '';
let myOrdersList = [];

async function renderMyOrders() {
  const tbody = document.getElementById('myOrdersTbody');
  const badge = document.getElementById('myOrdersBadge');
  if (!tbody) return;

  // Load orders
  try {
    const ordersResp = await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'my_orders', mobile: mob }), credentials:'same-origin' }).then(r => r.json());
    if (ordersResp.ok) myOrdersList = ordersResp.orders || [];
  } catch(e) {}

  const hasApprovedOrder = myOrdersList.some(o => o.status === 'approved');
  const hasPendingOrApproved = myOrdersList.some(o => o.status === 'pending' || o.status === 'approved');

  // Upgrade banner — hide once approved
  const profCard = document.getElementById('payLaterProfileCard');
  if (profCard) {
    const isFree = !profile?.plan || profile.plan === 'free';
    const showBanner = isFree && !hasApprovedOrder;
    profCard.style.display = showBanner ? 'block' : 'none';
    if (showBanner) {
      profCard.innerHTML = '<div style="display:flex;align-items:center;gap:14px;padding:16px 18px">'
        + '<div style="font-size:26px">👑</div>'
        + '<div><div style="font-weight:700;font-size:14px;color:#92400e">Upgrade Your Plan</div>'
        + '<div style="font-size:12.5px;color:#b45309;margin-top:3px">You are on the <strong>Free</strong> plan. Upgrade to unlock unlimited contacts, priority listing &amp; more features.</div></div></div>';
    }
  }

  // Pay Now card — hide once payment pending or approved
  const payNowCard = document.getElementById('payLaterPayNowCard');
  if (payNowCard) payNowCard.style.display = hasPendingOrApproved ? 'none' : '';

  if (badge) badge.textContent = myOrdersList.length;
  if (myOrdersList.length === 0) {
    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:20px;color:var(--ink3)">No orders yet</td></tr>';
    return;
  }
  tbody.innerHTML = myOrdersList.map((o, i) => {
    const st = o.status === 'approved'
      ? '<span style="background:#dcfce7;color:#16a34a;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600">✓ Approved</span>'
      : o.status === 'rejected'
      ? '<span style="background:#fee2e2;color:#dc2626;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600">✕ Rejected</span>'
      : '<span style="background:#fef3c7;color:#d97706;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600">⏳ Pending</span>';
    const processedInfo = o.processed_by ? `<div style="font-size:10px;color:var(--ink3)">by ${esc(o.processed_by)} · ${esc(o.processed_at||'')}</div>` : '';
    const proofHtml = o.payment_proof
      ? `<a href="api/uploads/${esc(o.payment_proof)}" target="_blank" style="color:#2563eb;font-size:11px;font-weight:600">📄 View</a>`
      : (o.status === 'pending' ? '<span style="color:#d97706;font-size:10px">Awaiting</span>' : '-');
    return `<tr>
      <td>${i+1}</td>
      <td style="font-size:11px;white-space:nowrap">${esc(o.created_at||'-')}</td>
      <td style="font-weight:600">${esc(o.plan)}</td>
      <td>${o.amount ? 'Rs. '+esc(o.amount) : '-'}</td>
      <td style="font-size:12px">${esc(o.method||'-')}</td>
      <td style="font-size:12px">${esc(o.txn_ref||'-')}</td>
      <td>${proofHtml}</td>
      <td>${st}${processedInfo}</td>
      <td style="font-size:11px;color:var(--ink3)">${esc(o.admin_note||'-')}</td>
    </tr>`;
  }).join('');
}

function renderPayLaterOpts(opts) {
  const grid = document.getElementById('payLaterOptsGrid');
  if (!grid) return;
  if (!opts || opts.length === 0) {
    grid.innerHTML = '<div style="text-align:center;padding:20px;color:var(--ink3)">No payment options available. Contact admin.</div>';
    return;
  }
  const mIcon  = { qr:'💳', upi:'📲', bank:'🏦', mobile:'📱' };
  const mLabel = { qr:'QR Code', upi:'UPI ID', bank:'Bank Transfer', mobile:'UPI Mobile' };
  grid.innerHTML = opts.map((opt, i) => {
    const d = opt.details || {};
    let body = '';
    if (opt.method === 'upi') {
      body = '<div style="text-align:center;margin-bottom:14px"><div style="font-family:monospace;font-size:18px;font-weight:800;background:#f3f4f6;display:inline-block;padding:8px 18px;border-radius:9px">' + esc(d.upi_id || d.upiId || '') + '</div>'
        + '<div style="font-size:12.5px;color:var(--ink3);margin-top:6px">Send payment to this UPI ID</div></div>'
        + '<button onclick="copyText(\'' + esc(d.upi_id || d.upiId || '') + '\',\'UPI ID\')" class="copy-btn" style="width:100%;padding:9px;border-radius:8px;font-size:13px;margin-bottom:10px">Copy UPI ID</button>';
    } else if (opt.method === 'bank') {
      const rows = [['Account Name', d.account_name||d.accountName],['Account No',d.account_no||d.accountNo],['IFSC',d.ifsc],['Bank',d.bank_name||d.bankName],['Branch',d.branch]].filter(([,v])=>v);
      body = '<div style="background:#faf9f7;border-radius:9px;padding:4px 14px;margin-bottom:12px">'
        + rows.map(([l,v]) => '<div class="bank-row"><div><div class="bank-lbl">'+esc(l)+'</div><div class="bank-val">'+esc(v)+'</div></div><button class="copy-btn" onclick="copyText(\''+esc(v)+'\',\''+esc(l)+'\')">Copy</button></div>').join('') + '</div>';
    } else if (opt.method === 'mobile') {
      body = '<div style="text-align:center;margin-bottom:14px"><div style="font-family:monospace;font-size:22px;font-weight:800;background:#f3f4f6;display:inline-block;padding:8px 20px;border-radius:9px">' + esc(d.mobileNo||d.mobile_no||'') + '</div>'
        + '<div style="font-size:13px;font-weight:600;margin-top:4px">' + esc(d.holderName||d.holder_name||'') + '</div></div>'
        + '<button onclick="copyText(\'' + esc(d.mobileNo||d.mobile_no||'') + '\',\'Number\')" class="copy-btn" style="width:100%;padding:9px;border-radius:8px;font-size:13px;margin-bottom:10px">Copy Number</button>';
    } else if (opt.method === 'qr') {
      const qrUrl = d.qr_url || d.qrUrl || '';
      body = (qrUrl ? '<div style="text-align:center;margin-bottom:14px"><img src="' + esc(qrUrl) + '" alt="QR" style="width:180px;height:180px;object-fit:contain;border:2px solid var(--border);border-radius:10px;padding:10px;background:#fff"></div>' : '')
        + '<div style="text-align:center;font-size:13px;color:var(--ink3)">Scan QR code to pay</div>';
    }
    if (opt.notes) body += '<div style="margin-top:10px;padding:8px 12px;background:#fffbeb;border-radius:8px;font-size:12px;color:#92400e">' + esc(opt.notes) + '</div>';
    body += '<div style="margin-top:14px;padding-top:12px;border-top:1px dashed #e5e7eb">'
      + '<div style="font-size:11px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">📤 Upload Payment Proof</div>'
      + '<div style="margin-bottom:8px"><input type="file" id="plProofFile_'+i+'" accept="image/*" class="finp" style="font-size:12px;padding:6px;border-color:#fde68a"></div>'
      + '<div style="margin-bottom:10px"><input class="finp" id="plTxnRef_'+i+'" placeholder="Transaction Ref / UPI ID (optional)" style="font-size:12px;padding:8px;border-color:#fde68a"></div>'
      + '<button class="pay-i-paid-btn" onclick="submitPayLaterProof(\'' + esc(opt.opt_id) + '\',' + i + ')">📤 Submit Proof & Notify Admin</button>'
      + '</div>';
    return '<div class="pay-opt-card" id="plOptCard_'+i+'">'
      + '<div class="pay-opt-header" onclick="togglePayLaterOpt('+i+')">'
      + '<div style="width:36px;height:36px;border-radius:9px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">' + (mIcon[opt.method]||'💳') + '</div>'
      + '<div style="flex:1"><div style="font-weight:700;font-size:13.5px">'+esc(opt.label)+'</div><div style="font-size:12px;color:var(--ink3);margin-top:1px">'+esc(mLabel[opt.method]||opt.method)+'</div></div>'
      + '<svg id="plOptChevron_'+i+'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--ink4);transition:transform .2s"><polyline points="6 9 12 15 18 9"/></svg>'
      + '</div><div class="pay-opt-body" id="plOptBody_'+i+'">' + body + '</div></div>';
  }).join('');
}

function togglePayLaterOpt(i) {
  const body = document.getElementById('plOptBody_' + i);
  const card = document.getElementById('plOptCard_' + i);
  const chevron = document.getElementById('plOptChevron_' + i);
  const isOpen = body.classList.contains('open');
  document.querySelectorAll('#payLaterOptsGrid .pay-opt-body').forEach((b, idx) => {
    b.classList.remove('open');
    document.getElementById('plOptCard_' + idx)?.classList.remove('expanded');
    const ch = document.getElementById('plOptChevron_' + idx);
    if (ch) ch.style.transform = '';
  });
  if (!isOpen) { body.classList.add('open'); card.classList.add('expanded'); chevron.style.transform = 'rotate(180deg)'; }
}

async function submitPayLaterProof(optId, idx) {
  const file = document.getElementById('plProofFile_' + idx)?.files[0];
  const txnRef = document.getElementById('plTxnRef_' + idx)?.value.trim() || '';
  if (!file) { toast('Please select a payment proof image', 'error'); return; }
  const planName = profile?.pending_plan || profile?.plan || 'paid';
  try {
    const orderResp = await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'place_order', mobile: mob, plan: planName, amount: '0', method: optId, txn_ref: txnRef, notes: 'Pay Later via ' + optId }),
      credentials:'same-origin' }).then(r => r.json());
    if (orderResp.ok && orderResp.order_id) {
      const fd = new FormData();
      fd.append('action', 'upload_proof');
      fd.append('order_id', orderResp.order_id);
      fd.append('mobile', mob);
      fd.append('txn_ref', txnRef);
      fd.append('proof', file);
      await fetch('api/public.php', { method:'POST', body: fd, credentials:'same-origin' });
    }
    toast('Payment proof submitted! Admin will verify shortly.');
    renderMyOrders();
  } catch(e) { toast(e.message || 'Failed to submit proof', 'error'); }
}

async function submitPayProof() {
  const orderId = document.getElementById('proofOrderId').value;
  const file = document.getElementById('proofFile').files[0];
  const txnRef = document.getElementById('proofTxnRef').value;
  if (!orderId) { toast('Select an order', 'error'); return; }
  if (!file) { toast('Please select a payment proof image', 'error'); return; }

  const fd = new FormData();
  fd.append('action', 'upload_proof');
  fd.append('order_id', orderId);
  fd.append('mobile', mob);
  fd.append('txn_ref', txnRef);
  fd.append('proof', file);

  try {
    const resp = await fetch('api/public.php', { method:'POST', body: fd, credentials:'same-origin' });
    const data = await resp.json();
    if (data.ok) {
      toast('Payment proof submitted! Admin will verify.');
      document.getElementById('proofFile').value = '';
      document.getElementById('proofTxnRef').value = '';
      renderMyOrders();
    } else {
      toast(data.error || 'Failed', 'error');
    }
  } catch(e) { toast('Network error', 'error'); }
}

function upAoSelectPlan(el, plan, price, period) {
  upAoSelectedPlan = plan;
  upAoRawAmount = price.replace(/[^\d]/g, '');
  document.getElementById('up_ao_plan').value = plan;
  document.getElementById('up_ao_amt').value = price + ' / ' + period;
  document.querySelectorAll('.up-ao-plan').forEach(c => {
    const sel = c.dataset.plan === plan;
    c.style.border = sel ? '2px solid #8B0000' : '2px solid #E0C0C8';
    c.style.background = sel ? 'linear-gradient(135deg,#FFF0F2,#FFF8F5)' : '#FFFAF9';
    c.style.boxShadow = sel ? '0 4px 14px rgba(139,0,0,0.16)' : 'none';
  });
}
function upAoReset() {
  ['up_ao_txn','up_ao_notes'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
  document.getElementById('up_ao_amt').value='';
  document.getElementById('up_ao_method').selectedIndex=0;
  document.getElementById('up_ao_plan').value='';
  upAoSelectedPlan='';
  document.querySelectorAll('.up-ao-plan').forEach(c => { c.style.border='2px solid #E0C0C8'; c.style.background='#FFFAF9'; c.style.boxShadow='none'; });
  document.getElementById('upAoResult').style.display='none';
}
async function upAoSubmit() {
  const plan = document.getElementById('up_ao_plan').value;
  const method = document.getElementById('up_ao_method').value;
  const amount = document.getElementById('up_ao_amt').value;
  const txnRef = document.getElementById('up_ao_txn').value || '';
  const notes = document.getElementById('up_ao_notes').value || '';
  if (!plan) { popup('error','Validation Error','Please select a plan'); return; }
  if (!method) { popup('error','Validation Error','Please select a payment method'); return; }

  try {
    const resp = await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({
        action: 'place_order', mobile: mob,
        plan, amount: upAoRawAmount || amount.replace(/[^\d]/g,''), method, txn_ref: txnRef, notes
      }), credentials:'same-origin' });
    const data = await resp.json();
    if (!data.ok) { popup('error','Error', data.error || 'Failed to place order'); return; }
  } catch(e) { popup('error','Error','Network error'); return; }

  const orderData = [
    ['Plan', plan], ['Amount', amount],
    ['Payment Method', method],
    ['Transaction Ref', txnRef || '-'],
    ['Notes', notes || '-'],
    ['Order Date', new Date().toLocaleDateString('en-IN')],
  ];
  const rows = orderData.map(([k,v],i) => `<tr><td style="padding:9px 16px;font-size:0.78rem;font-weight:700;color:#7A1020;text-transform:uppercase;border-bottom:1px solid #F0E0E4;background:${i%2===0?'white':'#FFFAF9'}">${k}</td><td style="padding:9px 16px;font-size:0.88rem;color:#2A0A0E;border-bottom:1px solid #F0E0E4;background:${i%2===0?'white':'#FFFAF9'}">${v}</td></tr>`).join('');
  const el = document.getElementById('upAoResult');
  el.innerHTML = `<div style="background:white;border-radius:14px;box-shadow:var(--shadow);border:1px solid rgba(196,30,58,0.12);overflow:hidden"><div style="background:linear-gradient(135deg,#8B0000,#C41E3A);padding:13px 20px;display:flex;align-items:center;gap:9px"><span style="font-size:16px">✅</span><span style="color:white;font-family:var(--serif);font-size:1rem">Order Placed — Order Summary</span></div><div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse"><thead><tr style="background:#FFF0F2"><th style="padding:9px 16px;text-align:left;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B0000;border-bottom:1px solid #E0C0C8;width:38%">Field</th><th style="padding:9px 16px;text-align:left;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B0000;border-bottom:1px solid #E0C0C8">Value</th></tr></thead><tbody>${rows}</tbody></table></div></div>`;
  el.style.display='block';
  el.scrollIntoView({behavior:'smooth',block:'start'});
  popup('ok','Success','Order placed successfully! Admin will verify your payment.');
  renderMyOrders();
}

function showSec(name, btn) {
  if (!upAllowed(SEC_TO_PAGE[name] || '')) { toast('This page is not available', 'warn'); return; }
  document.querySelectorAll('.u-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.sb-btn').forEach(b => b.classList.remove('active'));
  document.getElementById(name + 'Section').classList.add('active');
  if (btn) btn.classList.add('active');
  document.getElementById('topbarTitle').textContent = TITLES[name] || name;
  // Close mobile menu
  document.querySelector('.sidebar')?.classList.remove('mob-open');
  document.getElementById('mobOverlay')?.classList.remove('open');
  setActions(name);
  const renderers = { myProfile: renderMyProfile, suggestions: renderSuggestions, basicMatches: () => renderMatches('basic'), mutualMatches: () => renderMatches('mutual'), allProfiles: renderAllProfiles, myBills: renderMyBills, addOrder: renderMyOrders, myActivity: renderMyActivity, loginHistory: renderLoginHistory, myReports: renderMyReports, profileViewLog: renderUserProfileViewLog, contactLog: renderUserContactLog, mySettings: renderSettings };
  if (renderers[name]) renderers[name]();
  // Show autosave restore banner for add profile section
  if (name === 'addProfile') FormAutoSave.showRestoreBanner('up_quick_create', '#addProfileSection .profile-card > div:nth-child(2)', () => toast('Draft restored'));
}

function setActions(sec) {
  const acts = document.getElementById('topbarActions');
  if (sec === 'myProfile' && profile) {
    const canEdit = upAllowed('feat_edit_profile');
    const canDelete = upAllowed('feat_delete_profile');
    const isFree = !profile.plan || profile.plan === 'free';
    const isPending = profile.payment_status === 'payment_notified';
    let html = '';
    if (isFree && !isPending) html += '<button class="btn btn-sm btn-primary" onclick="goToPayment()" style="background:linear-gradient(135deg,#f59e0b,#d97706);border-color:#d97706">Pay Now</button>';
    if (canEdit) html += '<button class="btn btn-outline btn-sm" onclick="openEdit()">Edit</button>';
    html += '<button class="btn btn-outline btn-sm" onclick="window.print()">Print</button>';
    if (canDelete) html += '<button class="btn btn-danger btn-sm" onclick="deleteProf()">Delete</button>';
    acts.innerHTML = html;
  } else if (sec === 'myProfile' && !profile) {
    // Inline buttons — the .action-row container scrolls horizontally on
    // mobile so buttons stay on a single line instead of wrapping under
    // the page title.
    let html = '';
    if (upAllowed('feat_create_profile')) {
      html += '<button class="btn btn-primary btn-sm" onclick="openCreate()">+ Create New Profile</button>';
    }
    html += '<a href="/" class="btn btn-outline btn-sm" style="text-decoration:none;display:inline-flex;align-items:center;gap:5px"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Go Home</a>';
    acts.innerHTML = html;
  } else {
    acts.innerHTML = '';
  }
}

// ===== PROFILE COMPLETION =====
// Returns { filled, total, pct } counting meaningful fields across every
// section of the profile. Photo fields ignore the legacy "default_*" sentinel.
function computeProfileCompletion(p) {
  if (!p) return { filled: 0, total: 0, pct: 0 };
  const fields = [
    'name','age','gender','dob','place_birth','nativity','workplace',
    'religion','caste','sub_caste','mother_tongue','marital','nationality','own_house','born_as',
    'height','weight','blood_group','complexion','diet','disability',
    'qualification','job','place_of_job','income',
    'star','raasi','paadam','lagnam','gothram','dosham',
    'photo1','rasi_photo','amsam_photo',
    'email','alt_mobile','contact_person','perm_address','present_address',
    'present_area','present_city','present_district','present_state',
    'father','father_alive','father_job','mother','mother_alive','mother_job',
    'others',
    'partner_qualification','partner_job','partner_job_requirement','partner_income_month',
    'partner_age_from','partner_age_to','partner_diet','partner_marital_status',
    'partner_horoscope_required','partner_caste','partner_sub_caste'
  ];
  let filled = 0;
  fields.forEach(f => {
    const v = p[f];
    if (v === null || v === undefined || v === '') return;
    if (typeof v === 'string') {
      const s = v.trim();
      if (!s) return;
      if ((f === 'photo1' || f === 'photo2' || f === 'photo3') && s.startsWith('default_')) return;
    }
    filled++;
  });
  const pct = fields.length > 0 ? Math.round((filled / fields.length) * 100) : 0;
  return { filled, total: fields.length, pct };
}

// ===== MY PROFILE =====
function renderMyProfile() {
  const sec = document.getElementById('myProfileSection');
  const p = profile;
  if (!p) {
    const canCreate = upAllowed('feat_create_profile');
    const _oc = canCreate ? 'openCreate()' : 'alert("Contact admin to create a profile.")';
    const tabBtn = (label, icon) =>
      '<button onclick="' + _oc + '" style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:20px;border:1px solid #d1d5db;background:#fff;font-size:13px;font-weight:500;color:#374151;cursor:pointer">'
      + icon + label + '</button>';
    const activeTabBtn = (label, icon) =>
      '<button onclick="' + _oc + '" style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:20px;border:none;background:var(--primary,#7f1d1d);color:#fff;font-size:13px;font-weight:600;cursor:pointer">'
      + icon + label + '</button>';
    const filterBtn = (label, icon) =>
      '<button onclick="' + _oc + '" style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:16px;border:1px solid #d1d5db;background:#fff;font-size:12px;color:#374151;cursor:pointer">'
      + icon + label + '</button>';
    const activeFilterBtn = (label, icon) =>
      '<button onclick="' + _oc + '" style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:16px;border:none;background:var(--primary,#7f1d1d);color:#fff;font-size:12px;font-weight:600;cursor:pointer">'
      + icon + label + '</button>';
    const dummyCard = '<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px;display:flex;gap:12px;align-items:center;cursor:pointer" onclick="' + _oc + '">'
      + '<div style="width:60px;height:72px;border-radius:8px;background:#f3f4f6;flex-shrink:0"></div>'
      + '<div style="flex:1"><div style="height:12px;background:#e5e7eb;border-radius:6px;width:60%;margin-bottom:8px"></div>'
      + '<div style="height:10px;background:#f3f4f6;border-radius:6px;width:80%;margin-bottom:6px"></div>'
      + '<div style="height:10px;background:#f3f4f6;border-radius:6px;width:50%"></div></div></div>';
    sec.innerHTML =
      '<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:10px 14px;margin-bottom:12px;display:flex;align-items:center;gap:10px;font-size:13px;color:#92400e;flex-wrap:wrap">'
      + '<span style="font-size:16px">&#128100;</span>'
      + '<div style="flex:1;min-width:180px">No profile linked to <strong>' + esc(mob) + '</strong>. '
      + (canCreate ? 'Click any button below to <strong>create your profile</strong>.' : 'Contact admin to create one.')
      + '</div></div>'
      + '<div style="border:1px solid var(--border,#e5e7eb);border-radius:12px;background:#fff;overflow:hidden">'
      // tab row
      + '<div style="padding:12px 14px;border-bottom:1px solid #f3f4f6;display:flex;gap:8px;flex-wrap:wrap;align-items:center">'
      + activeTabBtn('All', '&#128101; ')
      + tabBtn('Female', '&#128100; ')
      + tabBtn('Male', '&#128100; ')
      + (canCreate ? '<button onclick="' + _oc + '" style="margin-left:auto;display:inline-flex;align-items:center;gap:5px;padding:8px 16px;border-radius:20px;border:2px solid var(--primary,#7f1d1d);background:#fff;color:var(--primary,#7f1d1d);font-size:13px;font-weight:600;cursor:pointer">+ Add Profile</button>' : '')
      + '</div>'
      // search bar (decorative, click → create)
      + '<div style="padding:10px 14px;border-bottom:1px solid #f3f4f6" onclick="' + _oc + '">'
      + '<div style="display:flex;align-items:center;gap:8px;border:1px solid #d1d5db;border-radius:8px;padding:7px 12px;cursor:pointer;color:#9ca3af;font-size:13px">'
      + '<span>&#128269;</span> Search by name or profile ID...</div></div>'
      // filter pills
      + '<div style="padding:10px 14px;border-bottom:1px solid #f3f4f6;display:flex;gap:8px;flex-wrap:wrap">'
      + activeFilterBtn('Recent', '&#128197; ')
      + filterBtn('Random', '&#127922; ')
      + filterBtn('Photos', '&#128247; ')
      + filterBtn('Not Viewed', '&#10024; ')
      + '</div>'
      // placeholder cards with CTA overlay
      + '<div style="position:relative;padding:14px;display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px">'
      + dummyCard + dummyCard + dummyCard + dummyCard
      + '<div style="position:absolute;inset:0;background:rgba(255,255,255,0.82);backdrop-filter:blur(2px);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;border-radius:0 0 12px 12px;cursor:pointer" onclick="' + _oc + '">'
      + '<div style="font-size:38px">&#128100;</div>'
      + (canCreate
        ? '<div style="font-weight:700;font-size:15px;color:#111827">Create your profile to get started</div>'
          + '<button onclick="' + _oc + '" style="background:var(--primary,#7f1d1d);color:#fff;border:none;padding:12px 32px;border-radius:10px;font-weight:700;font-size:14px;cursor:pointer">+ Create Profile</button>'
        : '<div style="font-weight:600;font-size:14px;color:#374151">Contact admin to create a profile</div>')
      + '</div></div>'
      + '</div>';
    return;
  }
  const sb = p.status === 'Approved' ? '<span class="badge badge-green">Approved</span>' : '<span class="badge badge-amber">Pending</span>';
  const pl = (p.plan || 'free'); const plLabel = pl.charAt(0).toUpperCase() + pl.slice(1);
  const isFree = !p.plan || p.plan === 'free';
  const isPending = p.payment_status === 'payment_notified';
  const det = (l, v) => v ? '<div class="det-item"><div class="det-lbl">' + esc(l) + '</div><div class="det-val">' + esc(v) + '</div></div>' : '';

  // Pay Now banner for free plan users
  let payBanner = '';
  if (isFree && !isPending) {
    payBanner = '<div style="margin:0 -22px;padding:18px 22px;background:linear-gradient(135deg,#fef3c7,#fffbeb);border-top:1px solid #fde68a;border-bottom:1px solid #fde68a;display:flex;align-items:center;gap:16px;flex-wrap:wrap">'
      + '<div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">&#x1F451;</div>'
      + '<div style="flex:1;min-width:180px"><div style="font-weight:700;font-size:14.5px;color:#92400e">Upgrade Your Plan</div>'
      + '<div style="font-size:12.5px;color:#a16207;margin-top:2px;line-height:1.5">You are on the <strong>Free</strong> plan. Upgrade to unlock unlimited contacts, priority listing & more features.</div></div>'
      + '<button onclick="goToPayment()" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;border:none;padding:12px 28px;border-radius:10px;font-weight:700;font-size:14px;cursor:pointer;white-space:nowrap;box-shadow:0 4px 14px rgba(217,119,6,0.3);transition:all .2s" onmouseover="this.style.transform=\'translateY(-1px)\';this.style.boxShadow=\'0 6px 20px rgba(217,119,6,0.4)\'" onmouseout="this.style.transform=\'\';this.style.boxShadow=\'0 4px 14px rgba(217,119,6,0.3)\'">Pay Now</button>'
      + '</div>';
  } else if (isPending) {
    payBanner = '<div style="margin:0 -22px;padding:14px 22px;background:#eff6ff;border-top:1px solid #bfdbfe;border-bottom:1px solid #bfdbfe;display:flex;align-items:center;gap:12px">'
      + '<div style="font-size:18px">&#x23F3;</div>'
      + '<div><div style="font-weight:700;font-size:13.5px;color:#1e40af">Payment Verification Pending</div>'
      + '<div style="font-size:12px;color:#3b82f6;margin-top:2px">Your payment for <strong>' + esc(p.pending_plan || '') + '</strong> is being verified by admin.</div></div></div>';
  } else if (!isFree) {
    // Paid plan — show success banner
    payBanner = '<div style="margin:0 -22px;padding:14px 22px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-top:1px solid #bbf7d0;border-bottom:1px solid #bbf7d0;display:flex;align-items:center;gap:12px">'
      + '<div style="font-size:20px">✅</div>'
      + '<div><div style="font-weight:700;font-size:13.5px;color:#16a34a">Plan Active — ' + esc(plLabel) + '</div>'
      + '<div style="font-size:12px;color:#15803d;margin-top:2px">Valid till <strong>' + esc(p.expiry || 'N/A') + '</strong></div></div></div>';
  }

  // Profile photo
  const ph1 = p.photo1 && !p.photo1.startsWith('default_') ? (p.photo1.startsWith('uploads/') ? 'api/'+p.photo1 : 'api/uploads/'+p.photo1) : '';
  const avHtml = ph1
    ? '<img src="'+ph1+'" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,0.5)" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'"><div class="p-av" style="display:none">'+esc(initials(p.name))+'</div>'
    : '<div class="p-av">'+esc(initials(p.name))+'</div>';

  // Profile completion percentage — counts non-empty fields across all the main
  // sections + a primary photo + horoscope charts + partner preferences.
  const completion = computeProfileCompletion(p);
  const compColor = completion.pct >= 80 ? '#16a34a' : completion.pct >= 50 ? '#d97706' : '#dc2626';
  const compBanner = '<div style="margin:0 -22px 0;padding:14px 22px;background:linear-gradient(135deg,#f8fafc,#f1f5f9);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px;flex-wrap:wrap">'
    + '<div style="position:relative;width:56px;height:56px;flex-shrink:0">'
    + '<svg width="56" height="56" viewBox="0 0 36 36" style="transform:rotate(-90deg)">'
    + '<circle cx="18" cy="18" r="15.9" fill="none" stroke="#e5e7eb" stroke-width="3"/>'
    + '<circle cx="18" cy="18" r="15.9" fill="none" stroke="' + compColor + '" stroke-width="3" stroke-dasharray="' + (completion.pct * 0.999).toFixed(2) + ' 100" stroke-linecap="round"/>'
    + '</svg>'
    + '<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:' + compColor + '">' + completion.pct + '%</div>'
    + '</div>'
    + '<div style="flex:1;min-width:180px">'
    + '<div style="font-weight:700;font-size:14px;color:var(--ink1)">Profile Completion</div>'
    + '<div style="font-size:12px;color:var(--ink3);margin-top:2px;line-height:1.5">' + completion.filled + ' of ' + completion.total + ' details filled'
    + (completion.pct < 100 ? '. Click <strong>Edit</strong> to add missing info for better matches.' : '. Your profile is complete!') + '</div>'
    + '</div>'
    + (completion.pct < 100 && upAllowed('feat_edit_profile') ? '<button onclick="openEdit()" class="btn btn-outline btn-sm" style="white-space:nowrap">Complete Now</button>' : '')
    + '</div>';

  sec.innerHTML = '<div class="profile-card"><div class="p-banner"><div class="p-av-wrap">'+avHtml+'</div></div>'
    + '<div class="p-body"><div class="p-name">' + esc(p.name) + '</div>'
    + '<div class="p-meta">' + esc([p.age ? p.age + ' yrs' : '', p.gender, mob].filter(Boolean).join(' \u00B7 ')) + '</div>'
    + '<div class="p-badges">' + sb + '<span class="badge badge-blue">' + esc(plLabel) + '</span>'
    + (p.star ? '<span class="badge badge-gray">' + esc(p.star) + '</span>' : '') + '</div>'
    + compBanner
    + payBanner
    + '<div id="usageLimitsCard"></div>'
    + (() => {
      // Photo gallery
      const photos = [p.photo1, p.photo2, p.photo3].filter(ph => ph && !ph.startsWith('default_')).map(ph =>
        ph.startsWith('http') ? ph : ph.startsWith('uploads/') ? 'api/' + ph : 'api/uploads/' + ph
      );
      const _fixHoro = ph => !ph ? '' : ph.startsWith('http') ? ph : ph.startsWith('uploads/') ? 'api/' + ph : 'api/uploads/' + ph;
      const rasi = _fixHoro(p.rasi_photo);
      const amsam = _fixHoro(p.amsam_photo);
      let html = '';
      if (photos.length > 0) {
        html += '<div class="det-sec"><div class="det-sec-title">Photos</div>'
          + '<div style="display:flex;gap:10px;flex-wrap:wrap;padding:4px 0">'
          + photos.map(src => '<img src="'+src+'" style="width:100px;height:120px;object-fit:cover;border-radius:10px;border:2px solid #e5e7eb;cursor:pointer" onclick="window.open(this.src)" onerror="this.style.display=\'none\'">').join('')
          + '</div></div>';
      }
      if (rasi || amsam) {
        html += '<div class="det-sec"><div class="det-sec-title">Horoscope Charts</div>'
          + '<div style="display:flex;gap:12px;flex-wrap:wrap;padding:4px 0">';
        if (rasi) html += '<div style="text-align:center"><img src="'+rasi+'" style="width:180px;border-radius:10px;border:2px solid #e5e7eb" onerror="this.parentElement.style.display=\'none\'"><div style="font-size:11px;color:var(--ink3);margin-top:4px">Rasi Chart</div></div>';
        if (amsam) html += '<div style="text-align:center"><img src="'+amsam+'" style="width:180px;border-radius:10px;border:2px solid #e5e7eb" onerror="this.parentElement.style.display=\'none\'"><div style="font-size:11px;color:var(--ink3);margin-top:4px">Amsam Chart</div></div>';
        html += '</div></div>';
      }
      return html;
    })()
    + '<div class="det-sec"><div class="det-sec-title">Personal &amp; Education</div><div class="det-grid">'
    + det('CP ID', p.cp_id) + det('Age', p.age ? p.age + ' years' : '') + det('Gender', p.gender)
    + det('DOB', p.dob) + det('Birth Time', p.birth_hour && p.birth_min ? p.birth_hour + ':' + p.birth_min + ' ' + (p.birth_ampm||'') : '')
    + det('Place of Birth', p.place_birth) + det('Religion', p.religion) + det('Caste', p.caste)
    + det('Marital', p.marital) + det('Mother Tongue', p.mother_tongue) + det('Nativity', p.nativity)
    + det('Nationality', p.nationality) + det('Own House', p.own_house) + det('Born As', p.born_as)
    + det('Height', p.height) + det('Weight', p.weight) + det('Blood', p.blood_group) + det('Complexion', p.complexion)
    + det('Diet', p.diet) + det('Disability', p.disability)
    + det('Qualification', p.qualification)
    + det('Job', p.job) + det('Place of Job', p.place_of_job) + det('Income', p.income && /\d/.test(p.income) ? 'Rs. ' + p.income : p.income)
    + det('Registered', p.created) + det('Approved', p.approved || 'Pending') + det('Expiry', p.expiry || '-')
    + '</div></div>'
    + '<div class="det-sec"><div class="det-sec-title">Astrology</div><div class="det-grid">'
    + det('Caste', p.caste) + det('Sub Caste', p.sub_caste)
    + det('Gothram', p.gothram) + det('Star', p.star) + det('Raasi', p.raasi)
    + det('Padam', p.paadam) + det('Lagnam', p.lagnam)
    + det('Dosham', p.dosham) + (p.dosham === 'Yes' ? det('Dosham Type', p.dosham_type) : '')
    + '</div></div>'
    + '<div class="det-sec"><div class="det-sec-title">Family</div><div class="det-grid">'
    + det('Father', p.father) + det('Father Job', p.father_job)
    + det('Mother', p.mother) + det('Mother Job', p.mother_job)
    + '</div></div>'
    + '<div class="det-sec"><div class="det-sec-title">Partner Expectations</div><div class="det-grid">'
    + det('Qualification', p.partner_qualification) + det('Job', p.partner_job)
    + det('Income', p.partner_income_month && /\d/.test(p.partner_income_month) ? 'Rs. ' + p.partner_income_month : p.partner_income_month)
    + det('Age Range', (p.partner_age_from||'') + (p.partner_age_to ? ' - '+p.partner_age_to+' yrs' : p.partner_age_from ? ' yrs' : ''))
    + det('Diet', p.partner_diet) + det('Horoscope', p.partner_horoscope_required)
    + det('Caste', p.partner_caste) + det('Sub Caste', p.partner_sub_caste) + det('Marital Status', p.partner_marital_status)
    + det('Job Req', p.partner_job_requirement) + det('Other Req', p.partner_other_requirement)
    + '</div></div>'
    + '<div class="det-sec"><div class="det-sec-title">Contact</div><div class="det-grid">'
    + det('Mobile', mob) + det('Alt Mobile', p.alt_mobile) + det('Email', p.email)
    + det('Contact Person', p.contact_person)
    + det('Perm Address', p.perm_address) + det('Present Address', p.present_address)
    + det('Area', p.present_area) + det('City', p.present_city) + det('District', p.present_district) + det('State', p.present_state)
    + det('Present Country', p.workplace)
    + '</div></div></div></div>';
  loadUsageLimits();
}

// ===== USAGE LIMITS =====
async function loadUsageLimits() {
  const card = document.getElementById('usageLimitsCard');
  if (!card) return;
  try {
    const data = await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'user_limits', mobile: mob }), credentials:'include' }).then(r=>r.json());
    if (!data.ok) return;
    const l = data.limits, u = data.used;
    const pct = (used, limit) => limit > 0 ? Math.min(100, Math.round(used/limit*100)) : 0;
    const bar = (used, limit, color) => {
      const p = pct(used, limit);
      const lbl = limit > 0 ? `${used} / ${limit}` : `${used} / Unlimited`;
      const barColor = p >= 90 ? '#dc2626' : p >= 70 ? '#d97706' : color;
      return `<div style="flex:1;min-width:140px">
        <div style="display:flex;justify-content:space-between;margin-bottom:4px">
          <span style="font-weight:700;color:${barColor};font-size:18px">${used}</span>
          <span style="font-size:12px;color:#9ca3af;align-self:flex-end">${limit > 0 ? 'of '+limit : 'Unlimited'}</span>
        </div>
        <div style="height:6px;background:#f3f4f6;border-radius:3px;overflow:hidden">
          <div style="height:100%;width:${limit>0?p:0}%;background:${barColor};border-radius:3px;transition:width .5s"></div>
        </div>
      </div>`;
    };
    card.innerHTML = `<div style="margin:0 -22px;padding:16px 22px;background:linear-gradient(135deg,#f0f9ff,#eff6ff);border-top:1px solid #bfdbfe;border-bottom:1px solid #bfdbfe">
      <div style="font-weight:700;font-size:13px;color:#1e40af;margin-bottom:12px">📊 Contact View Usage</div>
      <div style="display:flex;gap:20px;flex-wrap:wrap">
        <div style="flex:1;min-width:130px;background:#fff;border-radius:10px;padding:12px 14px;border:1px solid #dbeafe">
          <div style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">Today</div>
          ${bar(u.day, l.day, '#2563eb')}
        </div>
        <div style="flex:1;min-width:130px;background:#fff;border-radius:10px;padding:12px 14px;border:1px solid #dbeafe">
          <div style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">This Month</div>
          ${bar(u.month, l.month, '#7c3aed')}
        </div>
        <div style="flex:1;min-width:130px;background:#fff;border-radius:10px;padding:12px 14px;border:1px solid #dbeafe">
          <div style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">Total (Lifetime)</div>
          ${bar(u.total, l.total, '#059669')}
        </div>
      </div>
    </div>`;
  } catch(e) { console.log('Usage limits error:', e); }
}

// ===== EDIT =====
function openEdit() {
  if (!profile) return;
  const p = profile;
  const f = (id, v) => { const e = document.getElementById(id); if (e) e.value = v || ''; };
  // Personal
  f('ep_mobile', p.mobile); f('ep_name', p.name); f('ep_age', p.age);
  const epGender = (p.gender || '').charAt(0).toUpperCase() + (p.gender || '').slice(1).toLowerCase();
  f('ep_gender', epGender); DobAge.setFromIso('ep_dob', p.dob);

  // Lock identity fields once profile is approved (mobile/name/dob/age/gender)
  const isApproved = (p.status || '').toLowerCase() === 'approved';
  const lockStyle  = 'background:#f3f4f6;cursor:not-allowed';
  ['ep_name','ep_dob','ep_age_input','ep_gender'].forEach(id => {
    const el = document.getElementById(id); if (!el) return;
    if (isApproved) {
      if (el.tagName === 'SELECT') el.disabled = true;
      else el.readOnly = true;
      el.style.cssText = (el.style.cssText || '') + ';' + lockStyle;
      el.title = 'Locked after approval. Contact admin to change.';
    } else {
      if (el.tagName === 'SELECT') el.disabled = false;
      else if (id !== 'ep_age_input') el.readOnly = false;
      el.style.cursor = ''; el.title = '';
    }
  });
  const lockNote = document.getElementById('ep_lockNote');
  if (lockNote) lockNote.style.display = isApproved ? '' : 'none';
  f('ep_religion', p.religion); f('ep_caste', p.caste);
  f('ep_tongue', p.mother_tongue); f('ep_marital', p.marital || 'Unmarried');
  populateNationality('ep_nationality', p.nationality || 'Indian');
  f('ep_own_house', p.own_house || 'Yes');
  const epBorn = (p.born_as || '').trim().match(/^(\d+)\s*(Son|Daughter)?$/i);
  if (epBorn) { f('ep_born_as_num', epBorn[1]); f('ep_born_as_type', epBorn[2] ? epBorn[2].charAt(0).toUpperCase()+epBorn[2].slice(1).toLowerCase() : ''); }
  f('ep_place_birth', p.place_birth); f('ep_nativity', p.nativity); populateCountry('ep_workplace', p.workplace || 'India');
  // Family
  f('ep_father', p.father); f('ep_father_alive', p.father_alive || 'Yes');
  f('ep_father_job', p.father_job); f('ep_mother', p.mother);
  f('ep_mother_alive', p.mother_alive || 'Yes'); f('ep_mother_job', p.mother_job);
  // Siblings
  f('ep_sib_eb_m', p.sib_married_eb || '0'); f('ep_sib_yb_m', p.sib_married_yb || '0');
  f('ep_sib_es_m', p.sib_married_es || '0'); f('ep_sib_ys_m', p.sib_married_ys || '0');
  f('ep_sib_eb_u', p.sib_unmarried_eb || '0'); f('ep_sib_yb_u', p.sib_unmarried_yb || '0');
  f('ep_sib_es_u', p.sib_unmarried_es || '0'); f('ep_sib_ys_u', p.sib_unmarried_ys || '0');
  f('ep_others', p.others);
  // Physical
  f('ep_height', p.height); f('ep_weight', p.weight); f('ep_blood', p.blood_group);
  f('ep_complexion', p.complexion); f('ep_diet', p.diet || 'Vegetarian');
  f('ep_disability', p.disability || 'No');
  // Education
  f('ep_qual', p.qualification); f('ep_job', p.job);
  f('ep_place_job', p.place_of_job); f('ep_income', p.income);
  // Astrology
  f('ep_caste', p.caste); populateSubcaste('ep_caste', 'ep_subcaste', p.sub_caste || ''); f('ep_gothram', p.gothram);
  f('ep_star', p.star); f('ep_raasi', p.raasi); f('ep_paadam', p.paadam);
  f('ep_lagnam', p.lagnam); f('ep_religion', p.religion); f('ep_dosham', p.dosham || 'No');
  populateDoshamType('ep_dosham_type'); f('ep_dosham_type', p.dosham_type || '');
  toggleDoshamType(p.dosham || 'No', 'ep_dosham_type_wrap');
  // Partner
  f('ep_p_qual', p.partner_qualification); f('ep_p_job', p.partner_job);
  f('ep_p_jobreq', p.partner_job_requirement || 'Optional');
  f('ep_p_income', p.partner_income_month);
  f('ep_p_agefrom', p.partner_age_from); f('ep_p_ageto', p.partner_age_to);
  f('ep_p_diet', p.partner_diet || 'Vegetarian');
  PartnerCaste.setValue('ep_p_caste_box', 'ep_p_caste', p.partner_caste); f('ep_p_marital', p.partner_marital_status || 'Unmarried');
  f('ep_p_horoscope', p.partner_horoscope_required || 'No');
  PartnerCaste.setSubValue('ep_p_subcaste_box', 'ep_p_subcaste', p.partner_sub_caste);
  PartnerCaste.updateSubCasteWidget('ep_p_caste_box');
  f('ep_p_other', p.partner_other_requirement);
  // Communication
  f('ep_email', p.email); f('ep_alt', p.alt_mobile);
  f('ep_contact_person', p.contact_person);
  f('ep_addr', p.perm_address); f('ep_present_addr', p.present_address);
  setAddressLocation('ep', p.present_area, p.present_city, p.present_district, p.present_state);

  // Load existing photos
  ['photo1','photo2','photo3'].forEach((key, i) => {
    const prefix = 'ep_photo' + (i+1);
    const raw = p[key] || '';
    let src = '';
    if (raw && !raw.startsWith('default_')) {
      src = raw.startsWith('http') ? raw : raw.startsWith('uploads/') ? 'api/' + raw : 'api/uploads/' + raw;
    }
    const prev = document.getElementById(prefix + '_prev');
    const ph = document.getElementById(prefix + '_ph');
    // Reset stale state (e.g. "Processing..." from a previous open) and any staged file
    if (ph) { ph.textContent = 'Upload Photo'; ph.style.color = 'var(--ink4)'; }
    delete _processedPhotos[prefix];
    const fileInput = document.getElementById(prefix + '_file');
    if (fileInput) fileInput.value = '';
    if (src && prev) { prev.src = src; prev.style.display = 'block'; if (ph) ph.style.display = 'none'; }
    else if (prev) { prev.style.display = 'none'; prev.src = ''; if (ph) ph.style.display = ''; }
  });

  // Load horoscope images
  ['rasi_photo','amsam_photo'].forEach((key, i) => {
    const prefix = i === 0 ? 'ep_rasi' : 'ep_amsam';
    const raw = p[key] || '';
    let src = '';
    if (raw) {
      src = raw.startsWith('http') ? raw : raw.startsWith('uploads/') ? 'api/' + raw : 'api/uploads/' + raw;
    }
    const prev = document.getElementById(prefix + '_prev');
    const ph = document.getElementById(prefix + '_ph');
    const defaultLabel = prefix === 'ep_rasi' ? '📄 Upload Rasi Chart' : '📄 Upload Amsam Chart';
    if (ph) { ph.textContent = defaultLabel; ph.style.color = 'var(--ink4)'; }
    // Drop any staged file from a prior open so Save doesn't re-upload it
    delete _processedPhotos[prefix];
    const fileInput = document.getElementById(prefix + '_file');
    if (fileInput) fileInput.value = '';
    if (src && prev) { prev.src = src; prev.style.display = 'block'; if (ph) ph.style.display = 'none'; }
    else if (prev) { prev.style.display = 'none'; prev.src = ''; if (ph) ph.style.display = ''; }
  });

  openModal('editModal');
  // Auto-save: show restore banner if draft exists (draft has unsaved edits beyond server data)
  // Clear stale draft since we're loading fresh data from server
  FormAutoSave.clear('up_edit');
}
async function saveEdit() {
  const g = id => document.getElementById(id)?.value || '';
  const name = g('ep_name').trim();
  if (!name) { showPopup('warn', 'Required', 'Name is required.'); return; }
  if (!g('ep_gender')) { showPopup('warn', 'Required', 'Gender is required.'); return; }
  if (!DobAge.getIso('ep_dob')) { showPopup('warn', 'Required', 'Date of Birth is required (dd/mm/yyyy).'); return; }
  const epAgeErr = DobAge.validateAge('ep_dob', g('ep_gender'));
  if (epAgeErr) { showPopup('warn', 'Age Not Eligible', epAgeErr); return; }
  if (!g('ep_religion')) { showPopup('warn', 'Required', 'Religion is required.'); return; }
  if (!g('ep_caste')) { showPopup('warn', 'Required', 'Caste is required.'); return; }
  if (!g('ep_tongue')) { showPopup('warn', 'Required', 'Mother Tongue is required.'); return; }
  if (!g('ep_marital')) { showPopup('warn', 'Required', 'Marital Status is required.'); return; }
  // Input format validation
  const valErrs = InputValidator.validateAll('ep_');
  if (valErrs.length > 0) { showPopup('warn', 'Invalid Input', valErrs[0].msg + ' (' + valErrs[0].id.replace('ep_','') + ')'); document.getElementById(valErrs[0].id)?.focus(); return; }
  try {
    // Build the payload as FormData so we can also attach the staged photo
    // blobs (photo1/2/3, rasi chart, amsam chart) from _processedPhotos.
    const fd = new FormData();
    const text = {
      action: 'update', name, age: g('ep_age'), gender: g('ep_gender'),
      dob: DobAge.getIso('ep_dob'), religion: g('ep_religion'), caste: g('ep_caste'),
      place_birth: g('ep_place_birth'), nativity: g('ep_nativity'), workplace: g('ep_workplace'),
      mother_tongue: g('ep_tongue'), marital: g('ep_marital'), nationality: g('ep_nationality'), own_house: g('ep_own_house'),
      born_as: (g('ep_born_as_num') || '') + (document.getElementById('ep_born_as_type').value ? ' ' + document.getElementById('ep_born_as_type').value : ''),
      father: g('ep_father'), father_alive: g('ep_father_alive'),
      father_job: g('ep_father_job'), mother: g('ep_mother'),
      mother_alive: g('ep_mother_alive'), mother_job: g('ep_mother_job'),
      sib_married_eb: g('ep_sib_eb_m'), sib_married_yb: g('ep_sib_yb_m'),
      sib_married_es: g('ep_sib_es_m'), sib_married_ys: g('ep_sib_ys_m'),
      sib_unmarried_eb: g('ep_sib_eb_u'), sib_unmarried_yb: g('ep_sib_yb_u'),
      sib_unmarried_es: g('ep_sib_es_u'), sib_unmarried_ys: g('ep_sib_ys_u'),
      others: g('ep_others'),
      height: g('ep_height'), weight: g('ep_weight'), blood_group: g('ep_blood'),
      complexion: g('ep_complexion'), diet: g('ep_diet'), disability: g('ep_disability'),
      qualification: g('ep_qual'), job: g('ep_job'),
      place_of_job: g('ep_place_job'), income: g('ep_income'),
      sub_caste: g('ep_subcaste'), gothram: g('ep_gothram'),
      star: g('ep_star'), raasi: g('ep_raasi'), paadam: g('ep_paadam'),
      lagnam: g('ep_lagnam'), dosham: g('ep_dosham'), dosham_type: g('ep_dosham_type'),
      partner_qualification: g('ep_p_qual'), partner_job: g('ep_p_job'),
      partner_job_requirement: g('ep_p_jobreq'), partner_income_month: g('ep_p_income'),
      partner_age_from: g('ep_p_agefrom'), partner_age_to: g('ep_p_ageto'),
      partner_diet: g('ep_p_diet'), partner_caste: g('ep_p_caste'),
      partner_marital_status: g('ep_p_marital'),
      partner_horoscope_required: g('ep_p_horoscope'),
      partner_sub_caste: g('ep_p_subcaste'), partner_other_requirement: g('ep_p_other'),
      email: g('ep_email'), alt_mobile: g('ep_alt'),
      contact_person: g('ep_contact_person'),
      perm_address: g('ep_addr'), present_address: g('ep_present_addr'),
      present_area: g('ep_present_area'), present_city: g('ep_present_city'),
      present_district: g('ep_present_district'), present_state: g('ep_present_state')
    };
    for (const [k, v] of Object.entries(text)) fd.append(k, v ?? '');

    // Attach staged photo files prepared by upPhotoPreview()
    const stagedMap = {
      'ep_photo1':  'photo1',
      'ep_photo2':  'photo2',
      'ep_photo3':  'photo3',
      'ep_rasi':    'rasiPhoto',
      'ep_amsam':   'amsamPhoto',
    };
    for (const [prefix, field] of Object.entries(stagedMap)) {
      if (_processedPhotos[prefix]) fd.append(field, _processedPhotos[prefix]);
    }

    const res = await fetch('api/profile.php', { method: 'POST', body: fd, credentials: 'same-origin' });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error || 'Update failed');
    profile = data.profile;
    // Clear staged photos so a subsequent Save doesn't re-upload the same files
    ['ep_photo1','ep_photo2','ep_photo3','ep_rasi','ep_amsam'].forEach(k => { delete _processedPhotos[k]; });
    FormAutoSave.clear('up_edit');
    closeModal('editModal');
    renderMyProfile(); fillSidebar();
    toast('Profile updated');
  } catch (e) { showPopup('err', 'Error', e.message); }
}

// ===== DELETE =====
async function deleteProf() {
  if (!confirm('⚠ Delete your profile?\n\nAll your profile data and active plan will be permanently removed. To use the site again you will need to create a new profile and pay again.\n\nClick OK to delete, Cancel to keep your profile.')) return;
  try {
    await apiPost('profile.php', { action: 'delete', reason: 'User self-delete from user-panel' });
    profile = null;
    renderMyProfile(); setActions('myProfile');
    toast('Profile deleted', 'warn');
  } catch (e) { showPopup('err', 'Error', e.message); }
}

// ===== CREATE =====
function openCreate() {
  if (profile) { showPopup('warn', 'Already Exists', 'You already have a profile. Only one profile per user is allowed.'); return; }
  const mEl = document.getElementById('cp_mobile');
  if (mEl) { mEl.value = mob; mEl.readOnly = true; mEl.style.background = '#f3f4f6'; }
  openModal('createModal');
  // Auto-save: show restore banner if draft exists
  FormAutoSave.showRestoreBanner('up_create', '#createModal .modal-body', () => toast('Draft restored'));
}
async function submitCreate() {
  const g = id => document.getElementById(id)?.value || '';
  if (profile) { showPopup('warn', 'Already Exists', 'You already have a profile. Only one profile per user.'); return; }
  const cpMobile = mob;
  const name = g('cp_name').trim();
  const age = g('cp_age');
  const gender = g('cp_gender');
  const dob = DobAge.getIso('cp_dob');
  const religion = g('cp_religion');
  const caste = g('cp_caste');
  const tongue = g('cp_tongue');
  const marital = g('cp_marital');
  if (!cpMobile || cpMobile.length < 10) { showPopup('warn', 'Required', 'Mobile number not found. Please re-login.'); return; }
  if (!name) { showPopup('warn', 'Required', 'Name is required.'); return; }
  if (!gender) { showPopup('warn', 'Required', 'Gender is required.'); return; }
  if (!dob) { showPopup('warn', 'Required', 'Date of Birth is required.'); return; }
  const cpAgeErr = DobAge.validateAge('cp_dob', gender);
  if (cpAgeErr) { showPopup('warn', 'Age Not Eligible', cpAgeErr); return; }
  if (!religion) { showPopup('warn', 'Required', 'Religion is required.'); return; }
  if (!caste) { showPopup('warn', 'Required', 'Caste is required.'); return; }
  if (!tongue) { showPopup('warn', 'Required', 'Mother Tongue is required.'); return; }
  if (!marital) { showPopup('warn', 'Required', 'Marital Status is required.'); return; }
  // Input format validation
  const cpValErrs = InputValidator.validateAll('cp_');
  if (cpValErrs.length > 0) { showPopup('warn', 'Invalid Input', cpValErrs[0].msg + ' (' + cpValErrs[0].id.replace('cp_','') + ')'); document.getElementById(cpValErrs[0].id)?.focus(); return; }
  try {
    // Use FormData via public.php to support photo uploads
    const fd = new FormData();
    fd.append('contactNumber', cpMobile);
    fd.append('name', name); fd.append('gender', gender); fd.append('dob', dob);
    fd.append('birthHour', g('cp_birth_hour')); fd.append('birthMin', g('cp_birth_min')); fd.append('birthAmPm', g('cp_birth_ampm'));
    fd.append('placeBirth', g('cp_place_birth')); fd.append('nativity', g('cp_nativity')); fd.append('workplace', g('cp_workplace'));
    fd.append('motherTongue', g('cp_tongue')); fd.append('maritalStatus', g('cp_marital')); fd.append('nationality', g('cp_nationality')); fd.append('ownHouse', g('cp_own_house'));
    fd.append('bornAs', (g('cp_born_as_num') || '') + (document.getElementById('cp_born_as_type').value ? ' ' + document.getElementById('cp_born_as_type').value : ''));
    fd.append('others', g('cp_others'));
    fd.append('fatherName', g('cp_father')); fd.append('fatherAlive', g('cp_father_alive'));
    fd.append('fatherJob', g('cp_father_job')); fd.append('motherName', g('cp_mother'));
    fd.append('motherAlive', g('cp_mother_alive')); fd.append('motherJob', g('cp_mother_job'));
    fd.append('sibMarriedEB', g('cp_sib_eb_m')); fd.append('sibMarriedYB', g('cp_sib_yb_m'));
    fd.append('sibMarriedES', g('cp_sib_es_m')); fd.append('sibMarriedYS', g('cp_sib_ys_m'));
    fd.append('sibUnmarriedEB', g('cp_sib_eb_u')); fd.append('sibUnmarriedYB', g('cp_sib_yb_u'));
    fd.append('sibUnmarriedES', g('cp_sib_es_u')); fd.append('sibUnmarriedYS', g('cp_sib_ys_u'));
    fd.append('height', g('cp_height')); fd.append('weight', g('cp_weight'));
    fd.append('bloodGroup', g('cp_blood')); fd.append('complexion', g('cp_complexion'));
    fd.append('diet', g('cp_diet')); fd.append('disability', g('cp_disability'));
    fd.append('qualification', g('cp_qual')); fd.append('job', g('cp_job'));
    fd.append('placeJob', g('cp_place_job')); fd.append('incomeMonth', g('cp_income'));
    fd.append('caste', g('cp_caste')); fd.append('subCaste', g('cp_subcaste'));
    fd.append('gothram', g('cp_gothram')); fd.append('star', g('cp_star'));
    fd.append('raasi', g('cp_raasi')); fd.append('padam', g('cp_paadam'));
    fd.append('laknam', g('cp_lagnam')); fd.append('dosham', g('cp_dosham')); fd.append('doshamType', g('cp_dosham_type'));
    fd.append('religion', g('cp_religion'));
    fd.append('partnerQualification', g('cp_p_qual')); fd.append('partnerJob', g('cp_p_job'));
    fd.append('partnerJobRequirement', g('cp_p_job_req')); fd.append('partnerIncomeMonth', g('cp_p_income'));
    fd.append('partnerDiet', g('cp_p_diet')); fd.append('partnerAgeFrom', g('cp_p_agefrom'));
    fd.append('partnerAgeTo', g('cp_p_ageto')); fd.append('partnerCaste', g('cp_p_caste'));
    fd.append('partnerMaritalStatus', g('cp_p_marital')); fd.append('partnerHoroscopeRequired', g('cp_p_horoscope'));
    fd.append('partnerSubCaste', g('cp_p_subcaste'));
    fd.append('partnerOtherRequirement', g('cp_p_other'));
    fd.append('email', g('cp_email')); fd.append('permanentAddress', g('cp_addr'));
    fd.append('presentAddress', g('cp_present_addr'));
    fd.append('presentArea', g('cp_present_area')); fd.append('presentCity', g('cp_present_city'));
    fd.append('presentDistrict', g('cp_present_district')); fd.append('presentState', g('cp_present_state'));
    fd.append('contactPerson', g('cp_contact_person')); fd.append('altMobile', g('cp_alt_mobile'));
    // Photos (use processed versions if available, fallback to raw)
    const pf = (prefix, key) => _processedPhotos[prefix] || document.getElementById(prefix + '_file')?.files[0];
    if (pf('cp_photo1')) fd.append('photo1', pf('cp_photo1'));
    if (pf('cp_photo2')) fd.append('photo2', pf('cp_photo2'));
    if (pf('cp_photo3')) fd.append('photo3', pf('cp_photo3'));
    if (pf('cp_rasi'))   fd.append('rasiPhoto', pf('cp_rasi'));
    if (pf('cp_amsam'))  fd.append('amsamPhoto', pf('cp_amsam'));

    const resp = await fetch(API + 'public.php', { method: 'POST', body: fd, credentials: 'same-origin' });
    const data = await resp.json();
    if (!resp.ok || data.error) throw new Error(data.error || 'Failed');
    // Reload profile from server
    const profData = await apiGet('profile.php');
    profile = profData.profile;
    FormAutoSave.clear('up_create');
    closeModal('createModal');
    fillSidebar();
    renderMyProfile();
    toast('Profile created — ' + (data.cp_id || ''));
    if (typeof openPaymentPage === 'function') openPaymentPage();
  } catch (e) { showPopup('err', 'Error', e.message); }
}

// ===== MY BILLS =====
async function renderMyBills() {
  try {
    const data = await apiGet('bills.php');
    const bills = data.bills || [];
    const today = new Date().toISOString().split('T')[0];
    const active = bills.find(b => b.expiry && b.expiry >= today);
    const totalPd = bills.reduce((s, b) => s + (b.amount || 0), 0);
    document.getElementById('billStats').innerHTML = ''
      + '<div class="stat-card" style="border-color:var(--accent)"><div class="stat-num">' + bills.length + '</div><div class="stat-lbl">Total Bills</div></div>'
      + '<div class="stat-card" style="border-color:var(--green)"><div class="stat-num">\u20B9' + totalPd.toLocaleString('en-IN') + '</div><div class="stat-lbl">Total Paid</div></div>'
      + '<div class="stat-card" style="border-color:#d97706"><div class="stat-num">' + (active?.expiry || '-') + '</div><div class="stat-lbl">Plan Expiry</div></div>';
    document.getElementById('activePlanBody').innerHTML = active
      ? '<div style="font-weight:700;font-size:14.5px">' + esc(active.plan_name || '-') + '</div><div style="font-size:12px;color:var(--ink3);margin-top:3px">\u20B9' + Number(active.amount).toLocaleString('en-IN') + ' \u00B7 ' + esc(active.payment || '-') + ' \u00B7 Expires: ' + esc(active.expiry) + '</div>'
      : '<div style="color:var(--ink3);font-size:13px">No active plan.</div>';
    document.getElementById('billHistBadge').textContent = bills.length;
    document.getElementById('billTbody').innerHTML = bills.length === 0
      ? '<tr><td colspan="8" style="text-align:center;padding:22px;color:var(--ink3)">No bills yet</td></tr>'
      : bills.map((b, i) => {
        const a = b.expiry && b.expiry >= today;
        return '<tr><td style="color:var(--ink4);font-size:11px">' + (i+1) + '</td>'
          + '<td style="font-weight:600">' + esc(b.plan_name || '-') + '</td>'
          + '<td style="font-family:var(--mono)">\u20B9' + Number(b.amount).toLocaleString('en-IN') + '</td>'
          + '<td style="font-size:12px">' + esc(b.payment || '-') + '</td>'
          + '<td style="font-size:11.5px">' + esc(b.billed_date || '-') + '</td>'
          + '<td style="font-size:11.5px">' + esc(b.expiry || '-') + '</td>'
          + '<td style="font-size:11.5px">' + esc(b.billed_by || '-') + '</td>'
          + '<td><span class="badge ' + (a ? 'badge-green' : 'badge-gray') + '">' + (a ? 'Active' : 'Expired') + '</span></td></tr>';
      }).join('');
  } catch (e) { toast('Failed to load bills: ' + e.message, 'err'); }
}

// ===== MY ACTIVITY =====
async function renderMyActivity() {
  try {
    const data = await apiGet('activity.php');
    const pv = data.profileViews || [], cv = data.contactViews || [], vb = data.viewedBy || [];
    document.getElementById('actStats').innerHTML = ''
      + '<div class="stat-card" style="border-color:var(--accent)"><div class="stat-num">' + pv.length + '</div><div class="stat-lbl">Profiles Viewed</div></div>'
      + '<div class="stat-card" style="border-color:var(--green)"><div class="stat-num">' + cv.length + '</div><div class="stat-lbl">Contacts Requested</div></div>'
      + '<div class="stat-card" style="border-color:#9333ea"><div class="stat-num">' + vb.length + '</div><div class="stat-lbl">Viewed My Profile</div></div>';
    document.getElementById('pvBadge').textContent = pv.length;
    document.getElementById('cvBadge').textContent = cv.length;
    document.getElementById('vbBadge').textContent = vb.length;
    const emp = (c, m) => '<tr><td colspan="' + c + '" style="text-align:center;padding:18px;color:var(--ink3)">' + m + '</td></tr>';
    document.getElementById('pvTbody').innerHTML = pv.length === 0 ? emp(5, 'No profile views yet') : pv.map((v, i) =>
      '<tr><td style="color:var(--ink4)">' + (i+1) + '</td><td><code style="background:var(--bg);padding:2px 6px;border-radius:4px;font-size:11.5px">' + esc(v.target_cp_id) + '</code></td><td style="font-size:12px">' + esc(v.target_name || '-') + '</td><td style="font-size:11.5px">' + esc(v.datetime) + '</td><td><span class="badge badge-blue">' + (v.time_spent || 0) + 's</span></td></tr>').join('');
    document.getElementById('cvTbody').innerHTML = cv.length === 0 ? emp(4, 'No contacts viewed yet') : cv.map((v, i) =>
      '<tr><td style="color:var(--ink4)">' + (i+1) + '</td><td><code style="background:var(--bg);padding:2px 6px;border-radius:4px;font-size:11.5px">' + esc(v.target_cp_id) + '</code></td><td style="font-size:12px">' + esc(v.target_name || '-') + '</td><td style="font-size:11.5px">' + esc(v.datetime) + '</td></tr>').join('');
    document.getElementById('vbTbody').innerHTML = vb.length === 0 ? emp(5, 'No one has viewed your profile yet') : vb.map((v, i) =>
      '<tr><td style="color:var(--ink4)">' + (i+1) + '</td><td style="font-size:12px">' + esc(v.viewer_profile_name || v.viewer_name || 'Anonymous') + '</td><td><span class="badge badge-blue">' + esc((v.viewer_plan || v.viewer_profile_plan || 'free').charAt(0).toUpperCase() + (v.viewer_plan || v.viewer_profile_plan || 'free').slice(1)) + '</span></td><td style="font-size:11.5px">' + esc(v.datetime) + '</td><td><span class="badge badge-gray">' + (v.time_spent || 0) + 's</span></td></tr>').join('');
  } catch (e) { toast('Failed to load activity: ' + e.message, 'err'); }
}

// ===== LOGIN HISTORY =====
let myReports = [];

async function renderMyReports() {
  try {
    const data = await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'my_reports', mobile: mob }), credentials:'include' }).then(r=>r.json());
    if (data.ok && data.reports) myReports = data.reports;
  } catch(e) {}

  const badge = document.getElementById('myReportsBadge');
  if (badge) badge.textContent = myReports.length;
  const tbody = document.getElementById('myReportsTbody');
  if (!tbody) return;

  if (myReports.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:30px;color:#9ca3af">No reports submitted yet</td></tr>';
    return;
  }

  tbody.innerHTML = myReports.map((r, i) => {
    const reason = r.reason === 'already_married' || r.reason === 'Already Married'
      ? '<span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600">💍 Already Married</span>'
      : r.reason === 'fraud'
      ? '<span style="background:#fee2e2;color:#dc2626;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600">🚨 Fraud</span>'
      : r.reason === 'misinformation'
      ? '<span style="background:#fff7ed;color:#c2410c;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600">⚠️ Misinformation</span>'
      : `<span style="background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600">${r.reason}</span>`;

    const status = r.status === 'resolved'
      ? '<span style="background:#dcfce7;color:#16a34a;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600">Resolved</span>'
      : r.status === 'dismissed'
      ? '<span style="background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600">Dismissed</span>'
      : r.status === 'revoked'
      ? '<span style="background:#e0e7ff;color:#4338ca;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600">Revoked</span>'
      : '<span style="background:#fef3c7;color:#d97706;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600">Pending</span>';

    const adminNote = r.admin_note || '';
    const resolvedInfo = r.resolved_by ? `<div style="font-size:10px;color:#6b7280">by ${r.resolved_by} · ${r.resolved_at || ''}</div>` : '';

    const revokeBtn = r.status === 'pending'
      ? `<button onclick="revokeReport(${r.id})" style="background:#e0e7ff;color:#4338ca;border:1px solid #c7d2fe;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer">Revoke</button>`
      : '';

    return `<tr>
      <td>${i+1}</td>
      <td style="font-size:11px;white-space:nowrap">${r.reported_at || '-'}</td>
      <td><code style="font-size:11px;background:#f3f4f6;padding:2px 6px;border-radius:4px">${r.cp_id}</code></td>
      <td>${r.profile_name || '-'}</td>
      <td>${reason}</td>
      <td>${status}${resolvedInfo}</td>
      <td style="font-size:11px;color:#6b7280">${adminNote || '-'}</td>
      <td>${revokeBtn}</td>
    </tr>`;
  }).join('');
}

async function revokeReport(id) {
  if (!confirm('Are you sure you want to revoke this report?')) return;
  try {
    const resp = await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'revoke_report', id, mobile: mob }), credentials:'include' }).then(r=>r.json());
    if (resp.ok) { toast('Report revoked'); renderMyReports(); }
    else toast(resp.error || 'Failed', 'error');
  } catch(e) { toast('Error', 'error'); }
}

// ===== SUGGESTIONS =====
let sgData = { interest:[], preference:[], notViewed:[] };
let sgFilter = 'all';

async function renderSuggestions() {
  const container = document.getElementById('suggestionsContent');
  if (!container) return;
  container.innerHTML = '<div style="text-align:center;padding:36px 20px;color:var(--ink3);display:flex;flex-direction:column;align-items:center;gap:12px">'
    + '<span class="u-spinner lg"></span>'
    + '<span style="font-size:13px;font-weight:500">Loading suggestions…</span>'
    + '</div>';

  try {
    const resp = await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'suggestions', mobile: mob }), credentials:'same-origin' });
    const data = await resp.json();
    if (data.ok) sgData = data;
  } catch(e) {}
  renderSuggestionsUI();
}

function filterSuggestions(f) {
  sgFilter = f;
  document.querySelectorAll('[id^="sgFilt"]').forEach(b => { b.style.opacity = '0.5'; b.style.fontWeight = '500'; });
  const btn = document.getElementById('sgFilt' + f.charAt(0).toUpperCase() + f.slice(1).replace('_i','I').replace('_',''));
  if (btn) { btn.style.opacity = '1'; btn.style.fontWeight = '700'; }
  renderSuggestionsUI();
}

function starRating(score) {
  const full = Math.floor(score);
  const half = score - full >= 0.5 ? 1 : 0;
  const empty = 5 - full - half;
  return '<span style="color:#f59e0b;font-size:13px;letter-spacing:1px">'
    + '★'.repeat(full) + (half ? '½' : '') + '<span style="color:#e5e7eb">' + '★'.repeat(empty) + '</span>'
    + '</span><span style="font-size:10px;color:#9ca3af;margin-left:4px">' + score + '/5</span>';
}

function renderSuggestionsUI() {
  const container = document.getElementById('suggestionsContent');
  if (!container) return;
  const photoBase = 'api/uploads/';

  const tagBtn = (cpId, tag, icon, label, color, bgColor) => {
    const isActive = tag === label.toLowerCase().replace(' ','_');
    return `<button onclick="event.stopPropagation();tagProfile('${esc(cpId)}','${label.toLowerCase().replace(' ','_')}')"
      style="padding:3px 8px;border-radius:12px;font-size:10px;font-weight:600;cursor:pointer;border:1px solid ${isActive?color:'#e5e7eb'};
      background:${isActive?bgColor:'#fff'};color:${isActive?color:'#9ca3af'};transition:all .15s">${icon}</button>`;
  };

  const card = (p) => {
    const src = p.photo1 && !p.photo1.startsWith('default_')
      ? (p.photo1.startsWith('uploads/') ? 'api/' + p.photo1 : photoBase + p.photo1) : '';
    const imgHtml = src
      ? `<img src="${src}" style="width:54px;height:54px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">`
      : '';
    const fallback = `<div style="${src?'display:none;':'display:flex;'}width:54px;height:54px;border-radius:50%;background:var(--bg);align-items:center;justify-content:center;font-size:16px;color:var(--ink3);font-weight:700;border:2px solid #e5e7eb">${esc((p.name||'?').charAt(0))}</div>`;
    const score = p.match_score || 0;
    const currentTag = p.tag || '';
    const tagBorder = currentTag === 'interested' ? '#16a34a' : currentTag === 'not_interested' ? '#dc2626' : currentTag === 'later' ? '#d97706' : '#f0e0e4';

    return `<div class="sg-card" data-tag="${currentTag}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:white;border-radius:10px;border:1.5px solid ${tagBorder};transition:all .2s"
      onmouseover="this.style.boxShadow='0 4px 12px rgba(139,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
      <div style="cursor:pointer;flex-shrink:0" onclick="window.open('/detail/${esc(p.cp_id)}','_blank')">${imgHtml}${fallback}</div>
      <div style="flex:1;min-width:0;cursor:pointer" onclick="window.open('/detail/${esc(p.cp_id)}','_blank')">
        <div style="font-weight:700;font-size:13px;color:#1a1a2e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(p.name)}</div>
        <div style="font-size:11px;color:var(--ink3)">${esc(p.cp_id)} · ${p.age||''} yrs · ${esc(p.caste||'')}</div>
        <div style="margin-top:3px">${starRating(score)}</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:3px;align-items:flex-end;flex-shrink:0">
        <div style="font-size:10px;color:var(--ink3)">${esc(p.star||'')}</div>
        <div style="display:flex;gap:3px">
          ${tagBtn(p.cp_id, currentTag, '💚', 'interested', '#16a34a', '#dcfce7')}
          ${tagBtn(p.cp_id, currentTag, '🕐', 'later', '#d97706', '#fefce8')}
          ${tagBtn(p.cp_id, currentTag, '❌', 'not_interested', '#dc2626', '#fef2f2')}
        </div>
      </div>
    </div>`;
  };

  const sortKey = document.getElementById('sgSort')?.value || 'rating_desc';
  const sortFn = (a, b) => {
    if (sortKey === 'rating_desc') return (b.match_score||0) - (a.match_score||0);
    if (sortKey === 'rating_asc') return (a.match_score||0) - (b.match_score||0);
    if (sortKey === 'age_asc') return (a.age||0) - (b.age||0);
    if (sortKey === 'age_desc') return (b.age||0) - (a.age||0);
    if (sortKey === 'name_asc') return (a.name||'').localeCompare(b.name||'');
    return 0;
  };

  const filterCards = (items) => {
    let arr = sgFilter === 'all' ? [...items] : sgFilter === 'untagged' ? items.filter(p => !p.tag) : items.filter(p => p.tag === sgFilter);
    return arr.sort(sortFn);
  };

  const section = (icon, title, desc, items, color) => {
    const filtered = filterCards(items || []);
    if (!filtered || filtered.length === 0) return '';
    return `<div class="u-card" style="border-left:3px solid ${color}">
      <div class="u-card-head" style="background:linear-gradient(135deg,${color}15,${color}08)">
        <span class="u-card-title" style="color:${color}">${icon} ${title}</span>
        <span class="badge" style="background:${color}20;color:${color}">${filtered.length}</span>
      </div>
      <div style="padding:4px 10px 10px;font-size:11px;color:var(--ink3);margin-bottom:4px">${desc}</div>
      <div style="display:flex;flex-direction:column;gap:6px;padding:0 10px 14px">
        ${filtered.map(card).join('')}
      </div>
    </div>`;
  };

  let html = section('💡', 'Based on Your Interest', 'Profiles similar to what you\'ve viewed', sgData.interest, '#7c3aed')
    + section('💑', 'Partner Preference Match', 'Matches your partner expectations', sgData.preference, '#059669')
    + section('✨', 'New & Not Viewed', 'Recent profiles you haven\'t seen yet', sgData.notViewed, '#2563eb');

  // Fallback: if none of the primary sections produced cards, show all profiles
  if (!html && sgData.allProfiles && sgData.allProfiles.length > 0) {
    html = section('🌟', 'All Profiles', 'Browse all available profiles', sgData.allProfiles, '#8B0000');
  }

  container.innerHTML = html || '<div style="text-align:center;padding:30px;color:var(--ink3)">No profiles match this filter</div>';
}

// ===== SIDEBAR: collapsible parent submenus =====
function toggleSubmenu(btn) {
  const sub = btn.nextElementSibling;
  if (!sub || !sub.classList.contains('sb-sub')) return;
  const open = btn.getAttribute('aria-expanded') === 'true';
  btn.setAttribute('aria-expanded', open ? 'false' : 'true');
  sub.hidden = open;
}

// ===== BASIC / MUTUAL MATCHES =====
async function renderMatches(mode) {
  const containerId = mode === 'mutual' ? 'mutualMatchesContent' : 'basicMatchesContent';
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = '<div style="text-align:center;padding:36px 20px;color:var(--ink3);display:flex;flex-direction:column;align-items:center;gap:12px">'
    + '<span class="u-spinner lg"></span>'
    + '<span style="font-size:13px;font-weight:500">Finding matches…</span>'
    + '</div>';
  if (!profile) {
    container.innerHTML = '<div style="text-align:center;padding:30px;color:var(--ink3);font-size:13px">Create your profile first to see matches.</div>';
    return;
  }
  const action = mode === 'mutual' ? 'mutual_matches' : 'basic_matches';
  let data = null;
  try {
    const resp = await fetch('api/public.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
      body: JSON.stringify({ action, mobile: mob, limit: 60 })
    });
    data = await resp.json();
  } catch (e) {}
  if (!data || !data.ok) {
    container.innerHTML = '<div style="text-align:center;padding:30px;color:#dc2626;font-size:13px">Could not load matches. Please try again.</div>';
    return;
  }
  const list = data.profiles || [];
  if (!list.length) {
    container.innerHTML = '<div class="u-card" style="padding:28px 20px;text-align:center;color:var(--ink3)">'
      + '<div style="font-size:34px;margin-bottom:8px">🔍</div>'
      + '<div style="font-weight:600;color:#1a1a2e;margin-bottom:4px">No matches yet</div>'
      + '<div style="font-size:12px">Try widening your partner preferences (age range, caste, qualification) in Settings.</div>'
      + '</div>';
    return;
  }
  const photoBase = 'api/uploads/';
  const card = (p) => {
    const src = p.photo1 && !p.photo1.startsWith('default_')
      ? (p.photo1.startsWith('uploads/') ? 'api/' + p.photo1 : photoBase + p.photo1) : '';
    const imgHtml = src
      ? `<img src="${src}" style="width:54px;height:54px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">`
      : '';
    const fallback = `<div style="${src?'display:none;':'display:flex;'}width:54px;height:54px;border-radius:50%;background:var(--bg);align-items:center;justify-content:center;font-size:16px;color:var(--ink3);font-weight:700;border:2px solid #e5e7eb">${esc((p.name||'?').charAt(0))}</div>`;
    const where = [p.present_city, p.present_district, p.present_state].filter(Boolean).join(', ');
    return `<div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border-radius:10px;border:1.5px solid #f0e0e4;cursor:pointer;transition:all .15s"
      onclick="window.open('/detail/${esc(p.cp_id)}','_blank')"
      onmouseover="this.style.boxShadow='0 4px 12px rgba(139,0,0,0.1)';this.style.borderColor='#d4a574'"
      onmouseout="this.style.boxShadow='none';this.style.borderColor='#f0e0e4'">
      <div style="flex-shrink:0">${imgHtml}${fallback}</div>
      <div style="flex:1;min-width:0">
        <div style="font-weight:700;font-size:13px;color:#1a1a2e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(p.name)}</div>
        <div style="font-size:11px;color:var(--ink3)">${esc(p.cp_id)} · ${p.age||''} yrs · ${esc(p.caste||'')} ${p.marital?('· '+esc(p.marital)):''}</div>
        <div style="font-size:10.5px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(p.qualification||'')}${p.job?(' · '+esc(p.job)):''}${where?(' · '+esc(where)):''}</div>
      </div>
      <div style="font-size:10px;color:var(--ink3);text-align:right;flex-shrink:0">${esc(p.star||'')}<br>${esc(p.height||'')}</div>
    </div>`;
  };
  const header = `<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;padding:0 4px">
    <span style="font-size:12px;color:var(--ink3);font-weight:600">${list.length} of ${data.total||list.length} matches</span>
  </div>`;
  container.innerHTML = header + list.map(card).join('');
}

// ===== ALL PROFILES (opposite-gender browse) =====
let apData = [];

// All Profiles section now embeds the public homepage (filter tabs + search +
// card feed) inside an iframe with ?embed=1 so the inner Navbar/bottom-nav hide.
// Lazy-loaded: the iframe src is set only on first visit so the panel's cold
// start stays fast.
function renderAllProfiles() {
  const frame = document.getElementById('allProfilesFrame');
  if (!frame) return;
  if (!frame.src || frame.src === 'about:blank' || frame.src.endsWith('about:blank')) {
    frame.src = '/?embed=1';
  }
}

// Legacy hooks (search/sort widgets were removed) — kept as no-ops so any stale
// inline onchange/oninput references don't throw at runtime.
function renderAllProfilesUI() {}

async function tagProfile(cpId, tag) {
  try {
    await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'tag_profile', mobile: mob, target_cp_id: cpId, tag }), credentials:'same-origin' });
    // Update local data
    [sgData.interest, sgData.preference, sgData.notViewed].forEach(arr => {
      const p = (arr||[]).find(p => p.cp_id === cpId);
      if (p) p.tag = (p.tag === tag) ? null : tag;
    });
    // If un-tagging (same tag clicked), remove it
    const allProfs = [...(sgData.interest||[]), ...(sgData.preference||[]), ...(sgData.notViewed||[])];
    const prof = allProfs.find(p => p.cp_id === cpId);
    if (prof && prof.tag === null) {
      await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ action:'remove_tag', mobile: mob, target_cp_id: cpId }), credentials:'same-origin' });
    }
    renderSuggestionsUI();
  } catch(e) { toast('Failed to tag', 'error'); }
}

async function renderUserProfileViewLog() {
  const emp = (c, m) => '<tr><td colspan="' + c + '" style="text-align:center;padding:18px;color:var(--ink3)">' + m + '</td></tr>';
  let pv = [], vb = [];
  try {
    const resp = await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'user_activity', mobile: mob }), credentials:'same-origin' });
    const data = await resp.json();
    if (data.ok) { pv = data.profileViews || []; vb = data.viewedBy || []; }
  } catch(e) { console.log('Profile view log error:', e); }
  try {

    document.getElementById('pvlBadge').textContent = pv.length;
    document.getElementById('pvlWhoViewedBadge').textContent = vb.length;

    document.getElementById('upPvlTbody').innerHTML = pv.length === 0
      ? emp(4, 'No profiles viewed yet')
      : pv.map((v, i) => `<tr>
          <td style="color:var(--ink4)">${i+1}</td>
          <td><code style="background:var(--bg);padding:2px 6px;border-radius:4px;font-size:11.5px">${esc(v.target_cp_id)}</code></td>
          <td style="font-size:12px">${esc(v.target_name || '-')}</td>
          <td style="font-size:11.5px">${esc(v.datetime)}</td>
        </tr>`).join('');

    document.getElementById('upPvlWhoTbody').innerHTML = vb.length === 0
      ? emp(4, 'No one has viewed your profile yet')
      : vb.map((v, i) => `<tr>
          <td style="color:var(--ink4)">${i+1}</td>
          <td style="font-size:12px">${esc(v.viewer_profile_name || v.viewer_name || 'Anonymous')}</td>
          <td><span class="badge badge-blue">${esc((v.viewer_plan || v.viewer_profile_plan || 'free').charAt(0).toUpperCase() + (v.viewer_plan || v.viewer_profile_plan || 'free').slice(1))}</span></td>
          <td style="font-size:11.5px">${esc(v.datetime)}</td>
        </tr>`).join('');
  } catch(e) { console.log('Profile view log error:', e); }
}

async function renderUserContactLog() {
  const emp = (c, m) => '<tr><td colspan="' + c + '" style="text-align:center;padding:18px;color:var(--ink3)">' + m + '</td></tr>';
  let cv = [];
  try {
    const resp = await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'user_activity', mobile: mob }), credentials:'same-origin' });
    const data = await resp.json();
    if (data.ok) cv = data.contactViews || [];
  } catch(e) { console.log('Contact log error:', e); }
  try {

    document.getElementById('cvlBadge').textContent = cv.length;

    document.getElementById('upCvlTbody').innerHTML = cv.length === 0
      ? emp(4, 'No contacts viewed yet')
      : cv.map((v, i) => `<tr>
          <td style="color:var(--ink4)">${i+1}</td>
          <td><code style="background:var(--bg);padding:2px 6px;border-radius:4px;font-size:11.5px">${esc(v.target_cp_id)}</code></td>
          <td style="font-size:12px">${esc(v.target_name || '-')}</td>
          <td style="font-size:11.5px">${esc(v.datetime)}</td>
        </tr>`).join('');
  } catch(e) { console.log('Contact log error:', e); }
}

async function renderLoginHistory() {
  try {
    const data = await apiGet('logs.php');
    const log = data.log;
    document.getElementById('otpBadge').textContent = log ? '1 record' : '0';
    document.getElementById('otpTbody').innerHTML = !log
      ? '<tr><td colspan="6" style="text-align:center;padding:22px;color:var(--ink3)">No login records</td></tr>'
      : '<tr><td style="color:var(--ink4)">1</td>'
        + '<td style="font-size:11.5px">' + esc(log.otp_requested_at || '-') + '</td>'
        + '<td><span class="badge ' + (log.verified === 'verified' ? 'badge-green' : 'badge-amber') + '">' + ({verified:'OTP Verified',otp_failed:'OTP Failed',otp_request:'OTP Request',unverified:'OTP Request',web_in:'Web In',typing:'Web In',web_out:'Web Out',skip:'Web Out'}[log.verified] || 'Pending') + '</span></td>'
        + '<td style="font-size:11.5px">' + (log.last_login ? esc(log.last_login) : '-') + '</td>'
        + '<td style="font-weight:700;font-family:var(--mono)">' + (log.login_count || 0) + '</td>'
        + '<td><span class="badge ' + (log.banned ? 'badge-amber' : 'badge-green') + '">' + (log.banned ? 'Banned' : 'Active') + '</span></td></tr>';
  } catch (e) { toast('Failed to load login history: ' + e.message, 'err'); }
}

// ===== SETTINGS =====
async function renderSettings() {
  const canReqMobile = upAllowed('feat_req_mobile');
  try {
    const data = await apiGet('settings.php');
    const reqs = data.requests || [];
    const pending = reqs.find(r => r.status === 'pending');
    let html = '';
    if (reqs.length > 0) {
      html += '<div style="margin-bottom:16px"><div style="font-weight:600;font-size:13px;margin-bottom:7px">Your Requests</div>'
        + '<div class="u-tw"><table class="u-tbl"><thead><tr><th>#</th><th>Requested</th><th>Old</th><th>New</th><th>Status</th><th>Note</th></tr></thead><tbody>'
        + reqs.map((r, i) => '<tr><td style="font-size:11px;color:var(--ink4)">' + (i+1) + '</td>'
          + '<td style="font-size:11.5px">' + esc(r.requested_at) + '</td>'
          + '<td style="font-family:var(--mono);font-size:11.5px">' + esc(r.old_mobile) + '</td>'
          + '<td style="font-family:var(--mono);font-size:11.5px">' + esc(r.new_mobile) + '</td>'
          + '<td><span class="badge ' + (r.status === 'approved' ? 'badge-green' : r.status === 'rejected' ? 'badge-amber' : 'badge-blue') + '">' + esc(r.status) + '</span></td>'
          + '<td style="font-size:11.5px;color:var(--ink3)">' + esc(r.admin_note || '-') + '</td></tr>').join('')
        + '</tbody></table></div></div>';
    }
    if (!canReqMobile) {
      html += '<div style="background:#f3f4f6;border:1px solid var(--border);border-radius:8px;padding:12px;font-size:13px;color:var(--ink3)">Mobile change requests are currently disabled.</div>';
    } else if (pending) {
      html += '<div style="background:var(--green-bg);border:1px solid #a7f3d0;border-radius:8px;padding:11px 14px;font-size:13px;color:var(--green)">Pending request for <strong>' + esc(pending.new_mobile) + '</strong>. Awaiting admin approval.</div>';
    } else {
      html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">'
        + '<div class="fg"><label class="flbl">New Mobile <span style="color:var(--accent)">*</span></label><input class="finp" id="req_new" type="tel" maxlength="10" placeholder="New 10-digit number"></div>'
        + '<div class="fg"><label class="flbl">Reason <span style="color:var(--accent)">*</span></label><select class="fsel" id="req_reason"><option value="">- Select -</option><option>SIM lost / stolen</option><option>Number changed by provider</option><option>Using different number now</option><option>Others</option></select></div>'
        + '</div><button class="btn btn-primary" onclick="submitReq()">Request Change</button>';
    }
    document.getElementById('settingsBody').innerHTML = html;
    document.getElementById('sessionBody').innerHTML = '<div class="det-grid">'
      + '<div class="det-item"><div class="det-lbl">Logged In As</div><div class="det-val" style="font-family:var(--mono)">' + esc(mob) + '</div></div>'
      + '<div class="det-item"><div class="det-lbl">Session Time</div><div class="det-val">' + new Date().toLocaleString() + '</div></div></div>';
  } catch (e) { toast('Failed to load settings: ' + e.message, 'err'); }
}

async function submitReq() {
  const newM = document.getElementById('req_new')?.value.trim();
  const reason = document.getElementById('req_reason')?.value;
  if (!/^\d{10}$/.test(newM)) { showPopup('warn', 'Invalid', 'Enter a valid 10-digit new number.'); return; }
  if (!reason) { showPopup('warn', 'Required', 'Please select a reason.'); return; }
  try {
    await apiPost('settings.php', { action: 'submit', newMobile: newM, reason });
    showPopup('ok', 'Submitted', 'Admin will review soon.', 6000);
    renderSettings();
  } catch (e) { showPopup('err', 'Error', e.message); }
}

// ===== MODAL =====
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); }));

// ===== PAYMENT PAGE =====
let _payPlan = null, _payPlans = [], _payOpts = [];

async function goToPayment() {
  if (!profile) { showPopup('warn', 'No Profile', 'Create a profile first.'); return; }
  openPaymentPage();
}

async function openPaymentPage() {
  if (!profile) return;
  _payPlan = null;
  document.getElementById('payProfAvatar').textContent = initials(profile.name);
  document.getElementById('payProfName').textContent = profile.name;
  document.getElementById('payProfMeta').textContent = [profile.cp_id, mob, profile.gender, profile.age ? profile.age + ' yrs' : ''].filter(Boolean).join(' \u00B7 ');
  showPayStep('plans');
  document.getElementById('paymentPage').style.display = 'block';
  document.body.style.overflow = 'hidden';
  try {
    const planData = await apiGet('payment.php?type=plans');
    _payPlans = (planData.plans || []).filter(p => p.user_visible == 1 || p.user_visible === undefined);
    const optData = await apiGet('payment.php?type=options');
    _payOpts = optData.options || [];
    renderPayPlans();
  } catch (e) { toast('Failed to load payment info: ' + e.message, 'err'); }
}

function closePaymentPage() {
  document.getElementById('paymentPage').style.display = 'none';
  document.body.style.overflow = '';
  _payPlan = null;
  loadProfile().then(() => { renderMyProfile(); setActions('myProfile'); });
}

function showPayStep(step) {
  document.getElementById('payStepPlans').style.display = step === 'plans' ? '' : 'none';
  document.getElementById('payStepPayment').style.display = step === 'payment' ? '' : 'none';
  document.getElementById('payStepDone').style.display = step === 'done' ? '' : 'none';
}

function renderPayPlans() {
  const grid = document.getElementById('payPlansGrid');
  if (_payPlans.length === 0) {
    grid.innerHTML = '<div style="text-align:center;padding:20px;color:var(--ink3);grid-column:1/-1">No plans available. Contact admin.</div>';
    return;
  }
  const themes = {
    free:    { grad: 'linear-gradient(135deg,#64748b,#475569)', icon: '🆓', tag: '' },
    basic:   { grad: 'linear-gradient(135deg,#0ea5e9,#0284c7)', icon: '🎯', tag: 'Try it' },
    paid:    { grad: 'linear-gradient(135deg,#3b82f6,#1d4ed8)', icon: '💳', tag: '' },
    silver:  { grad: 'linear-gradient(135deg,#94a3b8,#64748b)', icon: '🥈', tag: '' },
    gold:    { grad: 'linear-gradient(135deg,#f59e0b,#d97706)', icon: '🥇', tag: 'Best value' },
    premium: { grad: 'linear-gradient(135deg,#8B0000,#C41E3A)', icon: '👑', tag: 'Most popular' },
    vip:     { grad: 'linear-gradient(135deg,#7c3aed,#5b21b6)', icon: '💎', tag: 'VIP' },
    custom:  { grad: 'linear-gradient(135deg,#0f766e,#134e4a)', icon: '🎁', tag: '' },
  };
  const features = {
    free:    ['Limited contacts','Basic listing','3 months access'],
    basic:   ['PayU integration test','Quick verification','Short access'],
    silver:  ['5 contacts/day','Enhanced listing','Profile boost'],
    gold:    ['Unlimited contacts','Top listing','Priority support'],
    premium: ['Everything in Gold','VIP badge','Dedicated manager'],
    vip:     ['All premium perks','Personal matchmaker','Lifetime support'],
  };

  grid.innerHTML = _payPlans.map((p, i) => {
    const isFree = Number(p.amount) === 0;
    const t = themes[p.type] || themes.paid;
    const feats = features[p.type] || (p.description ? [p.description] : ['Premium features']);
    const months = p.validity >= 30 ? Math.round(p.validity/30) + ' month' + (p.validity >= 60 ? 's' : '') : p.validity + ' days';

    return '<div class="plan-card" onclick="selectPlan(' + i + ')" id="planCard_' + i + '" '
      + 'style="position:relative;background:#fff;border:2px solid #e5e7eb;border-radius:16px;padding:0;cursor:pointer;transition:all .25s;overflow:hidden;display:flex;flex-direction:column">'
      + (t.tag ? '<div style="position:absolute;top:12px;right:12px;background:' + t.grad + ';color:#fff;font-size:10px;font-weight:800;padding:4px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:.06em;box-shadow:0 2px 8px rgba(0,0,0,.15)">' + t.tag + '</div>' : '')
      + '<div style="background:' + t.grad + ';padding:18px 18px 14px;color:#fff">'
        + '<div style="font-size:28px;line-height:1;margin-bottom:8px">' + t.icon + '</div>'
        + '<div style="font-size:15px;font-weight:800;letter-spacing:.02em">' + esc(p.name) + '</div>'
        + '<div style="font-size:11px;opacity:.85;margin-top:2px;text-transform:uppercase;letter-spacing:.08em">' + esc(p.type) + ' plan</div>'
      + '</div>'
      + '<div style="padding:16px 18px 18px;display:flex;flex-direction:column;flex:1">'
        + '<div style="display:flex;align-items:baseline;gap:6px;margin-bottom:4px">'
          + '<span style="font-size:30px;font-weight:900;color:#0f172a;font-family:var(--mono);line-height:1">'
          + (isFree ? '<span style="color:#16a34a">Free</span>' : '\u20B9' + Number(p.amount).toLocaleString('en-IN'))
          + '</span>'
        + '</div>'
        + '<div style="font-size:11.5px;color:#64748b;margin-bottom:14px">Valid for ' + esc(months) + '</div>'
        + '<ul style="list-style:none;padding:0;margin:0 0 14px;display:flex;flex-direction:column;gap:7px;flex:1">'
        + feats.map(f => '<li style="font-size:12.5px;color:#334155;display:flex;align-items:flex-start;gap:7px;line-height:1.4">'
            + '<span style="color:#16a34a;font-weight:800;flex-shrink:0">\u2713</span>' + esc(f) + '</li>').join('')
        + '</ul>'
        + '<div class="plan-card-cta" style="text-align:center;font-size:12.5px;font-weight:700;color:' + (isFree ? '#16a34a' : '#c2553d') + ';padding:9px;border:1.5px dashed ' + (isFree ? '#bbf7d0' : '#fde2da') + ';border-radius:9px;background:' + (isFree ? '#f0fdf4' : '#fef7f5') + '">' + (isFree ? 'Continue Free' : 'Select & Pay') + '</div>'
      + '</div>'
      + '</div>';
  }).join('');
}

function selectPlan(idx) {
  _payPlan = _payPlans[idx];
  document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
  const sel = document.getElementById('planCard_' + idx);
  if (sel) sel.classList.add('selected');
  // Clicking a plan card now goes straight to PayU (or continues with Free Plan) —
  // the intermediate "Pay Now" confirm button has been removed.
  confirmPlanSelection();
}

function _deprecated_oldSelectPlanTail() { if (false) {
  // kept only because the old code had glyph-matching trouble. Never runs.
    btn.textContent = 'Pay Now - \u20B9' + Number(_payPlan.amount).toLocaleString('en-IN');
    btn.className = 'btn btn-primary';
    btn.style.cssText = 'width:100%;padding:14px;font-size:15px;font-weight:700;border-radius:10px';
  }
  wrap.style.display = '';
}

function confirmPlanSelection() {
  if (!_payPlan) return;
  const isFree = !_payPlan || _payPlan.amount === 0;
  if (isFree) { finishRegistration(true); return; }

  const f = document.createElement('form');
  f.method = 'POST';
  f.action = 'api/payu-initiate.php';
  const i = document.createElement('input');
  i.type = 'hidden'; i.name = 'plan_id'; i.value = _payPlan.plan_id;
  f.appendChild(i);
  document.body.appendChild(f);
  f.submit();
}

function renderPayOpts(opts) {
  const plan = _payPlan;
  const amount = Number(plan.amount).toLocaleString('en-IN');
  document.getElementById('paySelectedPlanCard').innerHTML = '<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">'
    + '<div><div style="font-size:11px;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.5);margin-bottom:4px">Selected Plan</div>'
    + '<div style="font-size:18px;font-weight:700">' + esc(plan.name) + '</div></div>'
    + '<div style="text-align:right"><div style="font-size:28px;font-weight:800;font-family:var(--mono)">\u20B9' + amount + '</div></div></div>';
  const mIcon = { qr: '\uD83D\uDCB3', upi: '\uD83D\uDCF2', bank: '\uD83C\uDFE6', mobile: '\uD83D\uDCF1' };
  const mLabel = { qr: 'QR Code', upi: 'UPI ID', bank: 'Bank Transfer', mobile: 'UPI Mobile' };

  document.getElementById('payOptsGrid').innerHTML = opts.map((opt, i) => {
    const d = opt.details || {};
    let body = '';
    if (opt.method === 'upi') {
      body = '<div style="text-align:center;margin-bottom:14px"><div style="font-family:var(--mono);font-size:18px;font-weight:800;background:#f3f4f6;display:inline-block;padding:8px 18px;border-radius:9px">' + esc(d.upi_id || d.upiId || '') + '</div>'
        + '<div style="font-size:12.5px;color:var(--ink3);margin-top:6px">Send <strong>\u20B9' + amount + '</strong> to this UPI ID</div></div>'
        + '<button onclick="copyText(\'' + esc(d.upi_id || d.upiId || '') + '\',\'UPI ID\')" class="copy-btn" style="width:100%;padding:9px;border-radius:8px;font-size:13px;margin-bottom:10px">Copy UPI ID</button>';
    } else if (opt.method === 'bank') {
      const rows = [['Account Name', d.account_name || d.accountName], ['Account No', d.account_no || d.accountNo], ['IFSC', d.ifsc], ['Bank', d.bank_name || d.bankName], ['Branch', d.branch]].filter(([,v]) => v);
      body = '<div style="background:#faf9f7;border-radius:9px;padding:4px 14px;margin-bottom:12px">'
        + rows.map(([l,v]) => '<div class="bank-row"><div><div class="bank-lbl">' + esc(l) + '</div><div class="bank-val">' + esc(v) + '</div></div><button class="copy-btn" onclick="copyText(\'' + esc(v) + '\',\'' + esc(l) + '\')">Copy</button></div>').join('') + '</div>';
    } else if (opt.method === 'mobile') {
      body = '<div style="text-align:center;margin-bottom:14px"><div style="font-family:var(--mono);font-size:22px;font-weight:800;background:#f3f4f6;display:inline-block;padding:8px 20px;border-radius:9px">' + esc(d.mobileNo || d.mobile_no || '') + '</div>'
        + '<div style="font-size:13px;font-weight:600;margin-top:4px">' + esc(d.holderName || d.holder_name || '') + '</div></div>'
        + '<button onclick="copyText(\'' + esc(d.mobileNo || d.mobile_no || '') + '\',\'Number\')" class="copy-btn" style="width:100%;padding:9px;border-radius:8px;font-size:13px;margin-bottom:10px">Copy Number</button>';
    } else if (opt.method === 'qr') {
      const qrUrl = d.qr_url || d.qrUrl || '';
      body = (qrUrl ? '<div style="text-align:center;margin-bottom:14px"><img src="' + esc(qrUrl) + '" alt="QR" style="width:180px;height:180px;object-fit:contain;border:2px solid var(--border);border-radius:var(--radius);padding:10px;background:#fff"></div>' : '')
        + '<div style="text-align:center;font-size:13px;color:var(--ink3)">Scan QR code and pay <strong>\u20B9' + amount + '</strong></div>';
    }
    body += (opt.notes ? '<div class="pay-notes-banner">' + esc(opt.notes) + '</div>' : '');
    body += '<div style="margin-top:14px;padding-top:12px;border-top:1px dashed #e5e7eb">'
      + '<div style="font-size:11px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">📤 Submit Payment Proof</div>'
      + '<div style="margin-bottom:8px"><input type="file" id="payProofFile_' + i + '" accept="image/*,.pdf" class="finp" style="font-size:12px;padding:6px;border-color:#fde68a"></div>'
      + '<div style="margin-bottom:8px"><input class="finp" id="payTxnRef_' + i + '" placeholder="Transaction Ref / UPI ID (optional)" style="font-size:12px;padding:8px;border-color:#fde68a"></div>'
      + '<button class="pay-i-paid-btn" onclick="markPaidWithProof(\'' + esc(opt.opt_id) + '\',' + i + ')">📤 I\'ve Paid - Submit Proof & Notify Admin</button>'
      + '</div>';
    return '<div class="pay-opt-card" id="payOptCard_' + i + '"><div class="pay-opt-header" onclick="togglePayOpt(' + i + ')">'
      + '<div style="width:36px;height:36px;border-radius:9px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">' + (mIcon[opt.method] || '') + '</div>'
      + '<div style="flex:1"><div style="font-weight:700;font-size:13.5px">' + esc(opt.label) + '</div><div style="font-size:12px;color:var(--ink3);margin-top:1px">' + esc(mLabel[opt.method] || opt.method) + '</div></div>'
      + '<svg id="payOptChevron_' + i + '" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--ink4);transition:transform .2s"><polyline points="6 9 12 15 18 9"/></svg>'
      + '</div><div class="pay-opt-body" id="payOptBody_' + i + '">' + body + '</div></div>';
  }).join('');
}

function togglePayOpt(i) {
  const body = document.getElementById('payOptBody_' + i);
  const card = document.getElementById('payOptCard_' + i);
  const chevron = document.getElementById('payOptChevron_' + i);
  const isOpen = body.classList.contains('open');
  document.querySelectorAll('.pay-opt-body').forEach((b, idx) => {
    b.classList.remove('open');
    document.getElementById('payOptCard_' + idx)?.classList.remove('expanded');
    const ch = document.getElementById('payOptChevron_' + idx);
    if (ch) ch.style.transform = '';
  });
  if (!isOpen) { body.classList.add('open'); card.classList.add('expanded'); chevron.style.transform = 'rotate(180deg)'; }
}

function copyText(text, label) {
  navigator.clipboard?.writeText(text).then(() => toast(label + ' copied!')).catch(() => toast(label + ' copied!'));
}

async function markPaid(optId) {
  try {
    await apiPost('payment.php', { action: 'notify', planName: _payPlan.name, planAmount: _payPlan.amount, payOptId: optId });
    finishRegistration(false);
  } catch (e) { showPopup('err', 'Error', e.message); }
}

async function markPaidWithProof(optId, idx) {
  const fileInput = document.getElementById('payProofFile_' + idx);
  const txnRef = document.getElementById('payTxnRef_' + idx)?.value || '';
  const file = fileInput?.files[0];

  try {
    // First create the order
    const orderResp = await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({
        action: 'place_order', mobile: mob,
        plan: _payPlan?.name || 'paid', amount: String(_payPlan?.amount || 0),
        method: optId, txn_ref: txnRef, notes: 'Payment via ' + optId
      }), credentials:'same-origin' });
    const orderData = await orderResp.json();

    // Then upload proof if file selected
    if (file && orderData.ok && orderData.order_id) {
      const fd = new FormData();
      fd.append('action', 'upload_proof');
      fd.append('order_id', orderData.order_id);
      fd.append('mobile', mob);
      fd.append('txn_ref', txnRef);
      fd.append('proof', file);
      await fetch('api/public.php', { method:'POST', body: fd, credentials:'same-origin' });
    }

    // Notify admin via payment API
    try {
      await apiPost('payment.php', { action: 'notify', planName: _payPlan.name, planAmount: _payPlan.amount, payOptId: optId });
    } catch(e) {}

    finishRegistration(false);
  } catch (e) { showPopup('err', 'Error', e.message || 'Failed to submit'); }
}

function skipPayment() { finishRegistration(true); }
function backToPlans() { showPayStep('plans'); _payPlan = null; document.getElementById('payNowBtnWrap').style.display = 'none'; }

function finishRegistration(isFree) {
  document.getElementById('payDoneMsg').innerHTML = isFree
    ? 'Your free profile has been submitted.<br>Admin will review and approve soon.'
    : 'Payment notification sent for <strong>' + esc(_payPlan?.name || '') + '</strong>.<br>Admin will verify and activate within 24 hours.';
  showPayStep('done');
}

// ===== PASTE OTP =====
document.addEventListener('paste', function(e) {
  if (document.getElementById('otpSection')?.style.display !== 'block') return;
  const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 4);
  if (pasted.length === 4) {
    e.preventDefault();
    ['ob1','ob2','ob3','ob4'].forEach((id, i) => { const el = document.getElementById(id); if (el) el.value = pasted[i]; });
    document.getElementById('ob4')?.focus();
  }
});

// ===== BOOT =====
const _autoLoginMobile   = '<?php echo $autoLoginMobile; ?>';
const _impersonation     = <?php echo $_impersonation ? 'true' : 'false'; ?>;
const _impersonationMs   = <?php echo (int)$_impersonationMsLeft; ?>;

// Admin-impersonated user-panel tabs hard-logout after 30 min.
// A small banner shows the remaining time so the admin knows.
function installImpersonationGuard() {
  if (!_impersonation || _impersonationMs <= 0) return;
  // Floating banner
  const bar = document.createElement('div');
  bar.id = 'impersonationBar';
  bar.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:99998;' +
    'background:linear-gradient(135deg,#b91c1c,#7f1d1d);color:#fff;' +
    'padding:6px 12px;text-align:center;font-size:12px;font-weight:600;' +
    'letter-spacing:0.3px;box-shadow:0 2px 6px rgba(0,0,0,0.2);' +
    'font-family:system-ui,sans-serif';
  bar.innerHTML = '&#128274; Admin session &middot; auto-logout in <span id="impCount">--:--</span>';
  document.body.appendChild(bar);
  // Push page content down so the bar doesn't cover the topbar
  document.body.style.paddingTop = '28px';

  const expiresAt = Date.now() + _impersonationMs;
  const tick = () => {
    const ms = expiresAt - Date.now();
    if (ms <= 0) {
      doLogout();
      return;
    }
    const s = Math.round(ms / 1000);
    const mm = Math.floor(s / 60).toString().padStart(2, '0');
    const ss = (s % 60).toString().padStart(2, '0');
    const el = document.getElementById('impCount');
    if (el) el.textContent = mm + ':' + ss;
  };
  tick();
  setInterval(tick, 1000);
}

(async function boot() {
  const removeSplash = () => {
    const s = document.getElementById('sessionCheckSplash');
    if (s) s.remove();
  };
  const showLogin = () => {
    document.getElementById('loginPage').style.display = 'flex';
  };
  const enterShell = async () => {
    document.getElementById('loginPage').style.display = 'none';
    document.getElementById('appShell').classList.add('open');
    await loadProfile();
    await loadPanelCtrl();
    applyPanelCtrl();
    fillSidebar();
    renderMyProfile();
    setActions('myProfile');
    installImpersonationGuard();
  };

  // Safety net: never leave the user stuck on the splash if the network hangs.
  const failSafe = setTimeout(() => { removeSplash(); showLogin(); }, 10000);

  // 1. Already logged in via user-panel auth
  try {
    const data = await apiPost('auth.php', { action: 'check' });
    if (data.loggedIn && data.mobile) {
      mob = data.mobile;
      await enterShell();

      const params = new URLSearchParams(window.location.search);
      const payCpId = params.get('pay');
      if (payCpId === 'success') {
        showPopup('ok', 'Payment Successful', 'Your plan has been activated. Welcome aboard!');
        history.replaceState({}, '', location.pathname);
      } else if (payCpId === 'failure') {
        const reason = params.get('reason') || 'cancelled';
        showPopup('err', 'Payment Failed', 'Your payment did not complete (' + reason + '). You can try again from My Profile.');
        history.replaceState({}, '', location.pathname);
      } else if (payCpId && profile && profile.cp_id === payCpId) {
        setTimeout(() => { openPaymentPage(); toast('Opening payment page...'); }, 400);
      } else if (params.get('create') === '1' && !profile && upAllowed('feat_create_profile')) {
        history.replaceState({}, '', location.pathname);
        setTimeout(() => openCreate(), 200);
      }
      clearTimeout(failSafe);
      removeSplash();
      return;
    }
  } catch (e) {}

  // 2. Auto-login if verified via frontend OTP
  if (_autoLoginMobile) {
    try {
      const autoResp = await apiPost('auth.php', { action: 'auto_login' });
      if (autoResp.ok && autoResp.mobile) {
        mob = autoResp.mobile;
        await enterShell();
        const params = new URLSearchParams(window.location.search);
        if (params.get('create') === '1' && !profile && upAllowed('feat_create_profile')) {
          history.replaceState({}, '', location.pathname);
          setTimeout(() => openCreate(), 200);
        }
        clearTimeout(failSafe);
        removeSplash();
        return;
      }
    } catch (e) { console.log('Auto-login skip:', e); }
  }

  clearTimeout(failSafe);
  removeSplash();
  showLogin();
})();

// ===== DOB FORMAT INITIALIZATION =====
DobAge.init('up_ap_dob', 'up_ap_age_display', null, 'up_ap_gender', 'up_ap_age_input');
DobAge.init('ep_dob', 'ep_age_display', 'ep_age', 'ep_gender', 'ep_age_input');
DobAge.init('cp_dob', 'cp_age_display', 'cp_age', 'cp_gender', 'cp_age_input');

// ===== NATIONALITY & COUNTRY DROPDOWN INITIALIZATION =====
['up_ap_nationality','ep_nationality','cp_nationality'].forEach(id => populateNationality(id, 'Indian'));
['up_ap_workplace','ep_workplace','cp_workplace'].forEach(id => populateCountry(id, 'India'));

// ===== CASTE DROPDOWN INITIALIZATION =====
['up_ap_caste','ep_caste','cp_caste'].forEach(id => populateCasteDropdown(id, ''));

// ===== PLACE AUTOCOMPLETE INITIALIZATION =====
['up_ap_place_birth','up_ap_nativity','ep_place_birth','ep_nativity','ep_place_job','cp_place_birth','cp_nativity','cp_place_job'].forEach(id => PlaceSuggest.attach(id));

// ===== GOTHRAM AUTOCOMPLETE INITIALIZATION =====
['ep_gothram','cp_gothram'].forEach(id => GothramSuggest.attach(id));

// ===== MOBILE DUPLICATE CHECK =====
MobileCheck.attach('cp_mobile', null);

// ===== ADDRESS LOCATION INITIALIZATION =====
setupAddressExtract('ep', 'ep_present_addr');
setupAddressExtract('cp', 'cp_present_addr');

// ===== PARTNER CASTE PREFERENCE INITIALIZATION =====
PartnerCaste.build('ep_p_caste_box', 'ep_p_caste');
PartnerCaste.linkSubCaste('ep_p_caste_box', 'ep_p_subcaste_box', 'ep_p_subcaste', 'ep_caste');
PartnerCaste.build('cp_p_caste_box', 'cp_p_caste');
PartnerCaste.linkSubCaste('cp_p_caste_box', 'cp_p_subcaste_box', 'cp_p_subcaste', 'cp_caste');

// ===== DOSHAM TYPE INITIALIZATION =====
['up_ap_dosham_type','ep_dosham_type','cp_dosham_type'].forEach(id => populateDoshamType(id));
attachDoshamSelect('up_ap_dosham', 'up_ap_dosham_type_wrap');
attachDoshamSelect('ep_dosham', 'ep_dosham_type_wrap');
attachDoshamSelect('cp_dosham', 'cp_dosham_type_wrap');

// ===== FORM AUTO-SAVE INITIALIZATION =====
FormAutoSave.track('up_create', { container: '#createModal', fieldPrefix: 'cp_', excludeIds: ['cp_mobile'] });
FormAutoSave.track('up_edit', { container: '#editModal', fieldPrefix: 'ep_', excludeIds: ['ep_mobile'] });
FormAutoSave.track('up_quick_create', { container: '#addProfileSection', fieldPrefix: 'up_ap_', excludeIds: ['up_ap_mobile'] });
</script>

<script>
// ===== WIZARD-STYLE AUTO-ADVANCE FOR CREATE / EDIT MODALS =====
(function(){
  const MODALS = ['createModal', 'editModal'];
  const state = {};

  function isEligible(el){
    if (!el.id) return false;
    if (el.closest('table')) return false;              // skip siblings grid
    if (el.type === 'hidden' || el.type === 'file') return false;
    if (el.hasAttribute('readonly')) return false;      // skip cp_age_input / ep_mobile
    return true;
  }

  function isPlaceholder(v, txt){
    return v === '' && (txt === '-' || txt === '' || txt.startsWith('—') || /select/i.test(txt));
  }

  function buildMenu(sel, modalId, fieldId){
    const menu = document.createElement('div');
    menu.className = 'cdd-menu';
    menu.style.display = 'none';

    const nav = document.createElement('div');
    nav.className = 'cdd-nav';
    nav.innerHTML = '<button type="button" class="cdd-navbtn" data-act="prev">&laquo; Prev</button><button type="button" class="cdd-navbtn" data-act="skip">Skip &raquo;</button>';
    nav.querySelector('[data-act="prev"]').onclick = (e) => { e.stopPropagation(); wizNav(modalId, fieldId, -1); };
    nav.querySelector('[data-act="skip"]').onclick = (e) => { e.stopPropagation(); wizNav(modalId, fieldId, +1); };
    menu.appendChild(nav);

    const opts = document.createElement('div');
    Array.from(sel.children).forEach(ch => {
      if (ch.tagName === 'OPTGROUP') {
        const g = document.createElement('div');
        g.className = 'cdd-group';
        g.textContent = ch.label;
        opts.appendChild(g);
        Array.from(ch.children).forEach(o => addOpt(opts, o, sel, modalId, fieldId));
      } else if (ch.tagName === 'OPTION') {
        addOpt(opts, ch, sel, modalId, fieldId);
      }
    });
    menu.appendChild(opts);
    return menu;
  }

  function addOpt(container, o, sel, modalId, fieldId){
    const v = o.value != null && o.value !== '' ? o.value : o.textContent;
    const txt = o.textContent;
    if (isPlaceholder(o.value, txt)) return;
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'cdd-opt';
    b.textContent = txt;
    if (sel.value === v) b.classList.add('active');
    b.onclick = (e) => { e.stopPropagation(); wizSelect(modalId, fieldId, v); };
    container.appendChild(b);
  }

  function updateLabel(fieldId){
    const sel = document.getElementById(fieldId);
    if (!sel || sel.tagName !== 'SELECT') return;
    const wrap = sel.closest('.cdd');
    if (!wrap) return;
    const btn = wrap.querySelector('.cdd-toggle');
    // mirror disabled state from the underlying <select>
    if (sel.disabled) {
      btn.disabled = true;
      btn.style.background = '#f3f4f6';
      btn.style.cursor = 'not-allowed';
      btn.style.opacity = '0.85';
    } else {
      btn.disabled = false;
      btn.style.background = '';
      btn.style.cursor = '';
      btn.style.opacity = '';
    }
    const idx = sel.selectedIndex;
    const opt = idx >= 0 ? sel.options[idx] : null;
    if (opt && opt.value !== '' && !isPlaceholder(opt.value, opt.textContent)) {
      btn.textContent = opt.textContent;
      btn.classList.remove('ph');
    } else {
      btn.textContent = btn.getAttribute('data-placeholder') || '— Select —';
      btn.classList.add('ph');
    }
  }

  function rebuildMenuOpts(sel, modalId, fieldId){
    const wrap = sel.closest('.cdd');
    if (!wrap) return;
    const oldMenu = wrap.querySelector('.cdd-menu');
    const wasOpen = oldMenu && oldMenu.style.display !== 'none';
    const newMenu = buildMenu(sel, modalId, fieldId);
    if (wasOpen) newMenu.style.display = '';
    wrap.replaceChild(newMenu, oldMenu);
  }

  function transformSelect(sel, modalId){
    const id = sel.id;
    const wrap = document.createElement('div');
    wrap.className = 'cdd';
    wrap.setAttribute('data-field', id);

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'cdd-toggle ph';
    btn.setAttribute('data-placeholder', '— Select —');
    btn.textContent = '— Select —';
    btn.onclick = () => wizToggle(modalId, id);

    const menu = buildMenu(sel, modalId, id);

    sel.classList.add('cdd-hidden-sel');
    sel.setAttribute('tabindex', '-1');
    sel.setAttribute('aria-hidden', 'true');

    const parent = sel.parentNode;
    parent.insertBefore(wrap, sel);
    wrap.appendChild(btn);
    wrap.appendChild(menu);
    wrap.appendChild(sel);

    sel.addEventListener('change', () => updateLabel(id));
    new MutationObserver(() => {
      updateLabel(id);
      rebuildMenuOpts(sel, modalId, id);
    }).observe(sel, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled'] });

    // Intercept programmatic `.value = x` sets (e.g. FormAutoSave.restore,
    // loadProfile's f() helper) so the toggle label stays in sync
    const valDesc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
    if (valDesc && valDesc.set) {
      Object.defineProperty(sel, 'value', {
        get(){ return valDesc.get.call(this); },
        set(v){ valDesc.set.call(this, v); updateLabel(id); },
        configurable: true
      });
    }

    updateLabel(id);
  }

  function transformModal(modalId){
    const modalEl = document.getElementById(modalId);
    if (!modalEl) return;

    const fieldOrder = [];
    const nonDropdown = new Set();

    modalEl.querySelectorAll('.fsel, .finp, .fta').forEach(el => {
      if (!isEligible(el)) return;
      const id = el.id;
      if (el.tagName === 'SELECT') {
        transformSelect(el, modalId);
        fieldOrder.push(id);
      } else {
        if (!el.hasAttribute('name')) el.setAttribute('name', id);
        nonDropdown.add(id);
        fieldOrder.push(id);
      }
    });

    state[modalId] = { modalEl, formData: {}, activeDropdown: null, fieldOrder, nonDropdown };
  }

  function shouldHideField(modalId, field){
    const st = state[modalId];
    const anchor = st.modalEl.querySelector('[data-field="' + CSS.escape(field) + '"]') || document.getElementById(field);
    if (!anchor) return true;
    // Runtime-locked fields (e.g. ep_gender disabled after approval) are skipped by navigation
    const sel = document.getElementById(field);
    if (sel && (sel.disabled || sel.readOnly)) return true;
    let cur = anchor;
    while (cur && cur !== st.modalEl) {
      if (cur.style && cur.style.display === 'none') return true;
      cur = cur.parentElement;
    }
    return false;
  }

  function filteredFieldOrder(modalId){
    const st = state[modalId];
    return st.fieldOrder.filter(f => !shouldHideField(modalId, f));
  }

  function wizToggle(modalId, field){
    const st = state[modalId];
    if (!st) return;
    const cur = st.activeDropdown;
    if (cur) {
      const prev = st.modalEl.querySelector('[data-field="' + CSS.escape(cur) + '"]');
      if (prev) {
        prev.classList.remove('open');
        const m = prev.querySelector('.cdd-menu'); if (m) m.style.display = 'none';
      }
      if (cur === field) { st.activeDropdown = null; return; }
    }
    const wrap = st.modalEl.querySelector('[data-field="' + CSS.escape(field) + '"]');
    if (!wrap) { st.activeDropdown = null; return; }
    const sel = wrap.querySelector('.cdd-hidden-sel');
    if (sel && sel.disabled) { st.activeDropdown = null; return; }  // locked field
    wrap.classList.add('open');
    const menu = wrap.querySelector('.cdd-menu');
    menu.style.top = '';
    menu.style.bottom = '';
    menu.style.display = '';
    st.activeDropdown = field;
    // Flip to open upward if it would overflow viewport bottom
    setTimeout(() => {
      const r = menu.getBoundingClientRect();
      if (r.bottom > window.innerHeight - 8 && r.height < r.top) {
        menu.style.top = 'auto';
        menu.style.bottom = 'calc(100% + 4px)';
      }
    }, 0);
  }

  function wizSelect(modalId, field, value){
    const st = state[modalId];
    if (!st) return;
    const sel = document.getElementById(field);
    if (sel) {
      sel.value = value;
      sel.dispatchEvent(new Event('change', { bubbles: true }));
    }
    st.formData[field] = value;
    updateLabel(field);
    wizToggle(modalId, field);          // close current
    advance(modalId, field, +1);        // move to next
  }

  function wizNav(modalId, field, dir){
    const st = state[modalId];
    if (!st) return;
    wizToggle(modalId, field);          // close current
    advance(modalId, field, dir);
  }

  function advance(modalId, fromField, dir){
    const order = filteredFieldOrder(modalId);
    const idx = order.indexOf(fromField);
    const nextField = order[idx + dir];
    if (!nextField) return;
    const st = state[modalId];
    if (st.nonDropdown.has(nextField)) {
      setTimeout(() => {
        const el = document.querySelector('[name="' + CSS.escape(nextField) + '"]');
        el.focus();
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }, 150);
    } else {
      setTimeout(() => {
        wizToggle(modalId, nextField);
        setTimeout(() => {
          const w = document.querySelector('[data-field="' + CSS.escape(nextField) + '"]');
          w.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
      }, 0);
    }
  }

  // Click outside closes the open dropdown
  document.addEventListener('click', (e) => {
    MODALS.forEach(mid => {
      const st = state[mid];
      if (!st || !st.activeDropdown) return;
      const wrap = st.modalEl.querySelector('[data-field="' + CSS.escape(st.activeDropdown) + '"]');
      if (wrap && !wrap.contains(e.target)) {
        wizToggle(mid, st.activeDropdown);
      }
    });
    // Topbar scroll-menu dropdowns close when clicking anywhere outside them.
    document.querySelectorAll('.topbar-menu.open').forEach(m => {
      if (!m.contains(e.target)) m.classList.remove('open');
    });
  });

  // Re-sync toggle labels whenever a modal opens (programmatic .value= sets
  // from f()/loadProfile don't fire change events)
  const _origOpenModal = window.openModal;
  window.openModal = function(id){
    if (_origOpenModal) _origOpenModal(id);
    const st = state[id];
    if (st) st.fieldOrder.forEach(f => { if (!st.nonDropdown.has(f)) updateLabel(f); });
  };

  window.Wizard = { toggle: wizToggle, select: wizSelect, nav: wizNav, state };

  function init(){ MODALS.forEach(id => transformModal(id)); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
</script>
</body>
</html>
