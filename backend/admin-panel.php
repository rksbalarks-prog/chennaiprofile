<?php
require_once __DIR__ . '/admin-config.php';
adminSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Matrimony Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #F4FAF8;
  --sidebar-bg: #1a1a2e;
  --sidebar-accent: #0D7B6A;
  --card: #ffffff;
  --text-primary: #1A1A2E;
  --text-secondary: #6b7280;
  --border: #C8EDE6;
  --accent: #0D7B6A;
  --accent-soft: #E8F5F2;
  --purple: #6B3FA0;
  --purple-soft: #EDE8F5;
  --gold: #C9A84C;
  --green: #16a34a;
  --green-soft: #f0fdf4;
  --amber: #d97706;
  --amber-soft: #fffbeb;
  --blue: #2563eb;
  --blue-soft: #eff6ff;
  --shadow: 0 2px 12px rgba(13,123,106,0.08);
  --radius: 12px;
}

*{margin:0;padding:0;box-sizing:border-box}

body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--text-primary);
  min-height: 100vh;
}

/* SIDEBAR */
.sidebar {
  width: 240px;
  height: 100vh;
  background: var(--sidebar-bg);
  position: fixed;
  top: 0; left: 0;
  display: flex;
  flex-direction: column;
  padding: 0;
  z-index: 100;
  overflow-y: auto;
  overflow-x: hidden;
}

.sidebar-header {
  padding: 24px 20px 20px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

.sidebar-logo {
  font-family: 'DM Serif Display', serif;
  font-size: 18px;
  color: #fff;
  line-height: 1.2;
}

.sidebar-logo span {
  display: block;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  font-weight: 400;
  color: rgba(255,255,255,0.4);
  letter-spacing: 1.5px;
  text-transform: uppercase;
  margin-top: 2px;
}

.sidebar-nav {
  flex: 1;
  padding: 16px 12px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nav-btn {
  width: 100%;
  background: transparent;
  border: none;
  color: rgba(255,255,255,0.6);
  padding: 10px 14px;
  border-radius: 8px;
  cursor: pointer;
  text-align: left;
  font-family: 'DM Sans', sans-serif;
  font-size: 16px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: all 0.18s;
}

.nav-btn:hover {
  background: rgba(255,255,255,0.07);
  color: #fff;
}

.nav-btn.active {
  background: var(--sidebar-accent);
  color: #fff;
}

.nav-btn .icon {
  width: 16px;
  opacity: 0.8;
  flex-shrink: 0;
}

.nav-section-label {
  font-size: 16px;
  font-weight: 600;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: rgba(255,255,255,0.25);
  padding: 12px 14px 4px;
}

.nav-parent[aria-expanded="true"] .nav-caret { transform: rotate(180deg); }
.nav-sub { display: flex; flex-direction: column; gap: 1px; padding: 2px 0 4px 8px; border-left: 1.5px solid rgba(255,255,255,0.07); margin: 0 0 2px 18px; }
.nav-sub[hidden] { display: none; }
.nav-child { padding-left: 18px; font-size: 17px; }
.nav-bullet { color: rgba(255,255,255,0.35); font-size: 14px; line-height: 1; margin-right: 2px; }

.sidebar-footer {
  padding: 16px 20px;
  border-top: 1px solid rgba(255,255,255,0.08);
  font-size: 17px;
  color: rgba(255,255,255,0.3);
  position: sticky;
  bottom: 0;
  background: var(--sidebar-bg);
  flex-shrink: 0;
  margin-top: auto;
}
/* Sidebar scrollbar */
.sidebar::-webkit-scrollbar { width: 3px; }
.sidebar::-webkit-scrollbar-track { background: transparent; }
.sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 10px; }

/* MAIN */
.main {
  margin-left: 0px;
  padding: 28px;
  
   
  overflow-x: auto;
}

.page-header {
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  min-width: 0;
}

.page-title {
  font-family: 'DM Serif Display', serif;
  font-size: 26px;
  color: var(--text-primary);
}

.page-subtitle {
  font-size: 16px;
  color: var(--text-secondary);
  margin-top: 2px;
}

/* STATS ROW */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: var(--card);
  border-radius: var(--radius);
  padding: 18px 20px;
  box-shadow: var(--shadow);
  display: flex;
  align-items: center;
  gap: 14px;
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.stat-body .val {
  font-size: 22px;
  font-weight: 600;
  line-height: 1;
}

.stat-body .lbl {
  font-size: 17px;
  color: var(--text-secondary);
  margin-top: 3px;
}

/* SECTION */
.section {
  display: none;
  min-width: 0;
 
}

.section.active {
  display: block;
  animation: fadeIn 0.22s ease;
 margin-left: 260px;;
 margin-right:10px;
}

@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

/* CARD */
.card {
  background: var(--card);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
}

.card-header {
  padding: 18px 22px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--border);
}

.card-title {
  font-weight: 600;
  font-size: 17px;
}

/* TABLE */
.table-wrap {
  overflow-x: auto;
  overflow-y: visible;
  -webkit-overflow-scrolling: touch;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th {
  background: #f8f7f5;
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  color: var(--text-secondary);
  padding: 10px 16px;
  text-align: left;
  border-bottom: 1px solid var(--border);
}

td {
  padding: 12px 16px;
  font-size: 16px;
  border-bottom: 1px solid var(--border);
  vertical-align: middle;
}

tr:last-child td {
  border-bottom: none;
}

tr:hover td {
  background: #E8F5F2;
}

/* BADGES */
.badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 11.5px;
  font-weight: 600;
  gap: 4px;
}

.badge-green { background: var(--green-soft); color: var(--green); }
.badge-amber { background: var(--amber-soft); color: var(--amber); }
.badge-blue  { background: var(--blue-soft);  color: var(--blue); }
.badge-gray  { background: #f3f4f6; color: #6b7280; }

/* BUTTONS */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  border-radius: 7px;
  font-family: 'DM Sans', sans-serif;
  font-size: 17px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.15s;
}

.btn-primary { background: var(--accent); color: #fff; }
.btn-primary:hover { background: #0A5A4E; }

.btn-outline {
  background: transparent;
  color: var(--text-primary);
  border: 1.5px solid var(--border);
}

.btn-outline:hover { background: #f5f4f0; }

.btn-ghost {
  background: transparent;
  color: var(--text-secondary);
  padding: 5px 8px;
}

.btn-ghost:hover { background: #f0ede8; color: var(--text-primary); }

.btn-danger { background: #fee2e2; color: #dc2626; }
.btn-danger:hover { background: #fecaca; }

.btn-green { background: var(--green-soft); color: var(--green); }
.btn-green:hover { background: #dcfce7; }

.btn-sm { padding: 4px 9px; font-size: 17px; }

.actions { display: flex; gap: 4px; flex-wrap: wrap; }

/* FORM INPUTS */
.input {
  width: 100%;
  padding: 9px 12px;
  border: 1.5px solid var(--border);
  border-radius: 8px;
  font-family: 'DM Sans', sans-serif;
  font-size: 16px;
  background: #faf9f7;
  color: var(--text-primary);
  outline: none;
  transition: border 0.15s;
  margin-bottom: 10px;
}

.input:focus { border-color: var(--accent); background: #fff; }

select.input { cursor: pointer; }

.input-label {
  font-size: 17px;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 4px;
  display: block;
}

.form-row { margin-bottom: 12px; }

/* MODAL OVERLAY */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(26,26,46,0.45);
  backdrop-filter: blur(3px);
  z-index: 200;
  align-items: center;
  justify-content: center;
}

.modal-overlay.open {
  display: flex;
}

.modal {
  background: var(--card);
  border-radius: 16px;
  padding: 28px;
  width: 360px;
  box-shadow: 0 20px 60px rgba(26,26,46,0.2);
  animation: modalIn 0.2s ease;
}

@keyframes modalIn {
  from { opacity: 0; transform: scale(0.96) translateY(10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.modal-title {
  font-weight: 600;
  font-size: 16px;
}

.modal-close {
  background: #f3f4f6;
  border: none;
  border-radius: 6px;
  width: 28px;
  height: 28px;
  cursor: pointer;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
}

.modal-footer {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid var(--border);
}

/* SETTINGS GRID */
.settings-grid {
  display: grid;
  grid-template-columns: repeat(3,1fr);
  gap: 16px;
  padding: 20px;
}

.plan-card {
  border: 2px solid var(--border);
  border-radius: var(--radius);
  padding: 18px;
  text-align: center;
  transition: border-color 0.2s;
}

.plan-card:hover { border-color: var(--accent); }

.plan-card .plan-name {
  font-weight: 600;
  font-size: 17px;
  margin-bottom: 14px;
}

.plan-card .plan-name.free  { color: var(--text-secondary); }
.plan-card .plan-name.paid  { color: var(--blue); }
.plan-card .plan-name.premium { color: var(--amber); }

.plan-setting { margin-bottom: 10px; }

/* SETTINGS TABS */
.settings-tabs {
  display: flex;
  gap: 4px;
  padding: 16px 20px 0;
  border-bottom: 1px solid var(--border);
}

.stab {
  padding: 8px 18px;
  border: none;
  background: transparent;
  font-family: 'DM Sans', sans-serif;
  font-size: 16px;
  font-weight: 500;
  color: var(--text-secondary);
  cursor: pointer;
  border-radius: 8px 8px 0 0;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
  transition: all 0.15s;
}

.stab:hover { color: var(--text-primary); background: #f5f3ef; }
.stab.active { color: var(--accent); border-bottom-color: var(--accent);
 background: transparent; }

.stab-panel { display: none; }
.stab-panel.active { display: block; }

/* ── Pagination ── */
.pg-btn{padding:4px 10px;border:1.5px solid var(--border);border-radius:6px;background:#fff;font-size:12px;font-weight:600;cursor:pointer;color:var(--text-secondary);transition:all .13s;min-width:30px;text-align:center}
.pg-btn:hover{border-color:var(--accent);color:var(--accent)}
.pg-btn.active{background:var(--accent);color:#fff;border-color:var(--accent)}
.pg-btn:disabled{opacity:.35;cursor:not-allowed}

/* Toggle switch */
.up-toggle-row{display:flex;align-items:center;justify-content:space-between;padding:10px 13px;background:#fff;border:1.5px solid var(--border);border-radius:9px;transition:border .14s}
.up-toggle-row:hover{border-color:var(--accent)}
.up-toggle-row.disabled-row{opacity:.45;pointer-events:none}
.up-toggle-label{font-size:13px;font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:8px}
.up-toggle-sub{font-size:11px;color:var(--text-secondary);margin-top:1px}
.toggle-switch{position:relative;width:40px;height:22px;flex-shrink:0;cursor:pointer}
.toggle-switch input{opacity:0;width:0;height:0;position:absolute}
.toggle-track{position:absolute;inset:0;background:#d1d5db;border-radius:20px;transition:background .2s}
.toggle-switch input:checked + .toggle-track{background:var(--accent)}
.toggle-knob{position:absolute;top:3px;left:3px;width:16px;height:16px;background:#fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,.18);transition:transform .2s}
.toggle-switch input:checked ~ .toggle-knob{transform:translateX(18px)}
/* override badge */
.ov-page-badge{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:6px;font-size:12px;background:#eff6ff;cursor:default}
.ov-page-badge.hidden{background:#fee2e2;opacity:.5}

/* Payment option cards */
.pay-method-card {
  display: block; cursor: pointer; border: 1.5px solid var(--border);
  border-radius: 10px; transition: all .15s; user-select: none;
}
.pay-method-card:hover { border-color: var(--accent); background: #fff8f6; }
.pay-method-card.selected { border-color: var(--accent); background: #fdf0ed; box-shadow: 0 0 0 3px rgba(232,98,74,.12); }
.pay-method-inner { display: flex; flex-direction: column; align-items: center; padding: 12px 8px; gap: 4px; text-align: center; }
.pay-app-chip { display: inline-block; padding: 3px 10px; background: #fff; border: 1.5px solid var(--border); border-radius: 20px; font-size: 17px; font-weight: 600; cursor: pointer; transition: all .13s; }
.pay-app-chip:hover { background: var(--accent); color: #fff; border-color: var(--accent); }
/* Payment method type badge */
.pay-type-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
.pay-type-qr     { background: #eff6ff; color: #2563eb; }
.pay-type-upi    { background: #f0fdf4; color: #16a34a; }
.pay-type-bank   { background: #fffbeb; color: #d97706; }
.pay-type-mobile { background: #fdf4ff; color: #9333ea; }

/* ADMIN TABLE */
.role-badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 11.5px;
  font-weight: 600;
}

.role-super   { background: #fdf4ff; color: #9333ea; }
.role-manager { background: var(--blue-soft); color: var(--blue); }
.role-staff   { background: #f3f4f6; color: #6b7280; }

.pwd-cell {
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: monospace;
  font-size: 16px;
}

.eye-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-secondary);
  font-size: 14px;
  padding: 0 2px;
  line-height: 1;
}

.status-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  display: inline-block;
  margin-right: 5px;
}

/* EMPTY STATE */
.empty-state {
  text-align: center;
  padding: 50px 20px;
  color: var(--text-secondary);
}

.empty-state .icon { font-size: 36px; margin-bottom: 10px; }
.empty-state p { font-size: 14px; }

/* AVATAR */
.avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 17px;
  font-weight: 600;
  background: var(--accent-soft);
  color: var(--accent);
  flex-shrink: 0;
}

.name-cell { display: flex; align-items: center; gap: 8px; }

/* SEARCH BAR */
.search-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}
.search-input {
  flex: 1;
  min-width: 180px;
  padding: 9px 14px 9px 36px;
  border: 1.5px solid var(--border);
  border-radius: 8px;
  font-family: 'DM Sans', sans-serif;
  font-size: 16px;
  background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='M21 21l-4.35-4.35'/%3E%3C/svg%3E") no-repeat 10px center;
  outline: none;
  color: var(--text-primary);
  transition: border 0.15s;
}
.search-input:focus { border-color: var(--accent); }
.filter-select {
  padding: 9px 12px;
  border: 1.5px solid var(--border);
  border-radius: 8px;
  font-family: 'DM Sans', sans-serif;
  font-size: 16px;
  background: #fff;
  color: var(--text-primary);
  outline: none;
  cursor: pointer;
}
.filter-select:focus { border-color: var(--accent); }

/* REPORTS PAGE */
.reports-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 24px;
}
.chart-card {
  background: var(--card);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 20px 22px;
}
.chart-title {
  font-weight: 600;
  font-size: 14.5px;
  margin-bottom: 16px;
  color: var(--text-primary);
}
.bar-chart { display: flex; flex-direction: column; gap: 10px; }
.bar-row { display: flex; align-items: center; gap: 10px; font-size: 16px; }
.bar-label { width: 90px; color: var(--text-secondary); font-size: 17px; flex-shrink: 0; text-align: right; }
.bar-track { flex: 1; height: 18px; background: #f3f4f6; border-radius: 20px; overflow: hidden; }
.bar-fill { height: 100%; border-radius: 20px; transition: width 0.6s ease; }
.bar-val { width: 32px; font-weight: 600; font-size: 16px; text-align: right; flex-shrink: 0; }
.donut-wrap { display: flex; align-items: center; gap: 24px; }
.donut-legend { display: flex; flex-direction: column; gap: 8px; }
.legend-item { display: flex; align-items: center; gap: 8px; font-size: 16px; }
.legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.kpi-row { display: grid; grid-template-columns: repeat(2,1fr); gap: 12px; }
.kpi-box { background: #f8f7f5; border-radius: 10px; padding: 14px 16px; }
.kpi-box .kval { font-size: 22px; font-weight: 700; }
.kpi-box .klbl { font-size: 17px; color: var(--text-secondary); margin-top: 2px; }

/* NOTIFICATIONS PAGE */
.notif-list { padding: 8px 0; }
.notif-item {
  display: flex;
  gap: 14px;
  align-items: flex-start;
  padding: 14px 20px;
  border-bottom: 1px solid var(--border);
  transition: background 0.12s;
  cursor: pointer;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #faf9f7; }
.notif-item.unread { background: #fffbf9; }
.notif-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.notif-body { flex: 1; }
.notif-title { font-size: 16px; font-weight: 600; color: var(--text-primary); }
.notif-desc  { font-size: 17px; color: var(--text-secondary); margin-top: 2px; }
.notif-time  { font-size: 11.5px; color: var(--text-secondary); margin-top: 5px; }
.notif-dot   { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); margin-top: 6px; flex-shrink: 0; }

/* SUCCESS STORIES PAGE */
.stories-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 20px; }
.story-card {
  background: var(--card);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 20px 22px;
  position: relative;
  overflow: hidden;
}
.story-card::before {
  content: '❤️';
  position: absolute;
  top: 14px; right: 14px;
  font-size: 20px;
  opacity: 0.18;
}
.story-couple { font-family: 'DM Serif Display', serif; font-size: 17px; margin-bottom: 4px; }
.story-date   { font-size: 17px; color: var(--text-secondary); margin-bottom: 10px; }
.story-quote  { font-size: 16px; color: var(--text-secondary); font-style: italic; border-left: 3px solid var(--accent); padding-left: 10px; }
.story-ids    { margin-top: 12px; display: flex; gap: 6px; }

/* HAMBURGER (mobile) */
.hamburger {
  display: none;
  position: fixed;
  top: 14px; left: 14px;
  z-index: 300;
  background: var(--sidebar-bg);
  border: none;
  border-radius: 8px;
  width: 38px; height: 38px;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-direction: column;
  gap: 5px;
  padding: 8px;
}
.hamburger span { display: block; width: 20px; height: 2px; background: #fff; border-radius: 2px; transition: all 0.2s; }
.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  z-index: 99;
}

/* MOBILE RESPONSIVE */
@media (max-width: 768px) {
  .hamburger { display: flex; }
  .sidebar {
    transform: translateX(-100%);
    transition: transform 0.25s ease;
    z-index: 200;
  }
  .sidebar.open { transform: translateX(0); }
  .sidebar-overlay.open { display: block; }
  .main { margin-left: 0; padding: 16px; padding-top: 60px; max-width: 100vw; }
  .stats-row { grid-template-columns: repeat(2,1fr); }
  .reports-grid { grid-template-columns: 1fr; }
  .stories-grid { grid-template-columns: 1fr; }
  .page-header { flex-direction: column; align-items: flex-start; gap: 10px; }
  .modal { width: calc(100vw - 32px); padding: 20px; }
  .settings-grid { grid-template-columns: 1fr; }
  .search-bar { flex-direction: column; align-items: stretch; }
  .search-input { min-width: 0; }
}

/* SUBSCRIPTION PLAN TAB */
.sp-preset {
  padding: 3px 10px;
  border: 1.5px solid var(--border);
  border-radius: 20px;
  background: #fff;
  font-family: 'DM Sans', sans-serif;
  font-size: 11.5px;
  font-weight: 500;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.15s;
}
.sp-preset:hover {
  border-color: var(--accent);
  color: var(--accent);
  background: var(--accent-soft);
}
.sp-preset.active {
  border-color: var(--accent);
  background: var(--accent-soft);
  color: var(--accent);
  font-weight: 600;
}
@media (max-width: 900px) {
  #subPlansPanel > div[style*="grid-template-columns:390px"] {
    grid-template-columns: 1fr !important;
  }
  #subPlansPanel > div > div:first-child {
    border-right: none !important;
    border-bottom: 1px solid var(--border);
  }
}

/* MANAGE PROFILE ACTION STEPPER */
.action-stepper { display:flex; flex-direction:column; gap:5px; min-width:182px; }
.step-row { display:flex; align-items:center; gap:7px; position:relative; }
.step-row:not(:last-child)::after { content:''; position:absolute; left:11px; top:26px; width:1.5px; height:6px; background:var(--border); }
.step-num { width:23px; height:23px; border-radius:50%; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; border:1.5px solid var(--border); background:#fff; color:var(--text-secondary); }
.step-num.done   { background:#f0fdf4; border-color:#16a34a; color:#16a34a; }
.step-num.active { background:var(--accent-soft); border-color:var(--accent); color:var(--accent); }
.step-num.locked { background:#f3f4f6; border-color:#e5e0d8; color:#d1d5db; }
.step-btn { flex:1; padding:5px 10px; border-radius:7px; font-family:'DM Sans',sans-serif; font-size:12px; font-weight:600; cursor:pointer; border:1.5px solid transparent; display:flex; align-items:center; gap:5px; transition:all .15s; white-space:nowrap; }
.step-btn.s-follow     { background:#fff; border-color:var(--border); color:var(--text-primary); }
.step-btn.s-follow:hover { border-color:#2563eb; color:#2563eb; background:#eff6ff; }
.step-btn.s-scheduled  { background:#f0fdf4; border-color:#86efac; color:#16a34a; cursor:default; pointer-events:none; }
.step-btn.s-approve    { background:#f0fdf4; border-color:#86efac; color:#15803d; }
.step-btn.s-approve:hover { background:#dcfce7; border-color:#4ade80; }
.step-btn.s-revert     { background:#fff; border-color:var(--border); color:var(--text-secondary); }
.step-btn.s-revert:hover  { border-color:#d97706; color:#d97706; background:#fffbeb; }
.step-btn.s-bill       { background:var(--accent); border-color:var(--accent); color:#fff; }
.step-btn.s-bill:hover { background:#d4553f; }
.step-btn.s-billed     { background:#f0fdf4; border-color:#86efac; color:#16a34a; cursor:default; pointer-events:none; }
.step-btn.s-locked     { background:#f9fafb; border-color:#e5e7eb; color:#d1d5db; cursor:not-allowed; pointer-events:none; }
.step-btn.s-inactive   { background:#f3f4f6; border-color:#e5e0d8; color:#9ca3af; cursor:default; pointer-events:none; }

/* USAGE EXPAND / COLLAPSE */
.usage-card { background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow); margin-bottom:14px; overflow:hidden; }
.usage-card-header {
  display:flex; align-items:center; gap:14px;
  padding:14px 18px; cursor:pointer; user-select:none;
  transition:background .15s;
}
.usage-card-header:hover { background:#faf9f7; }
.usage-toggle-icon { font-size:16px; color:var(--text-secondary); transition:transform .2s; flex-shrink:0; line-height:1; }
.usage-toggle-icon.open { transform:rotate(90deg); }
.usage-meta { flex:1; display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
.usage-name { font-weight:600; font-size:14px; }
.usage-mobile { font-size:12.5px; color:var(--text-secondary); }
.usage-totals { display:flex; gap:10px; margin-left:auto; flex-wrap:wrap; }
.usage-total-chip {
  display:flex; align-items:center; gap:6px;
  background:#f8f7f5; border-radius:8px;
  padding:5px 12px; font-size:13px;
}
.usage-total-chip .chip-num { font-weight:700; font-size:15px; }
.usage-detail { display:none; border-top:1px solid var(--border); }
.usage-detail.open { display:block; }
.usage-detail-inner { display:grid; grid-template-columns:1fr 1fr; }
.usage-detail-col { padding:0; }
.usage-detail-col:first-child { border-right:1px solid var(--border); }
.usage-col-head {
  padding:10px 16px; background:#f8f7f5;
  font-size:11px; font-weight:700; letter-spacing:.8px;
  text-transform:uppercase; color:var(--text-secondary);
  border-bottom:1px solid var(--border);
  display:flex; align-items:center; gap:6px;
}
.usage-log-row {
  display:flex; align-items:center; justify-content:space-between;
  padding:9px 16px; border-bottom:1px solid var(--border);
  font-size:13px;
}
.usage-log-row:last-child { border-bottom:none; }
.usage-log-cpid { font-family:monospace; background:#f3f4f6; padding:2px 7px; border-radius:5px; font-size:11.5px; }
.usage-log-time { font-size:11.5px; color:var(--text-secondary); white-space:nowrap; }
.usage-empty-col { padding:22px 16px; text-align:center; color:var(--text-secondary); font-size:13px; }
@media(max-width:640px){ .usage-detail-inner{grid-template-columns:1fr;} .usage-detail-col:first-child{border-right:none;border-bottom:1px solid var(--border);} }

/* OTP LOGS PAGE */
.otp-verified   { background:#f0fdf4; color:#16a34a; }
.otp-unverified { background:#fef9c3; color:#854d0e; }
.otp-banned     { background:#fee2e2; color:#dc2626; }
.otp-active     { background:#f0fdf4; color:#16a34a; }
.ban-btn  { background:#fee2e2; color:#dc2626; border:1.5px solid #fca5a5; }
.ban-btn:hover  { background:#fecaca; }
.unban-btn{ background:#f0fdf4; color:#16a34a; border:1.5px solid #86efac; }
.unban-btn:hover{ background:#dcfce7; }
.bh-created { background:#eff6ff; color:#2563eb; }
.bh-updated { background:#fffbeb; color:#d97706; }
.badge-expired { background:#fff8f0; color:#d97706; }
/* FULL REGISTRATION FORM */
.form-section-title {
  font-size:12px; font-weight:700; letter-spacing:.8px; text-transform:uppercase;
  color:#fff; background:linear-gradient(90deg,#1a1a2e,#3b3b6e);
  padding:8px 14px; border-radius:6px; margin:18px 0 10px;
}
.req { color:#e8624a; }

/* INSTAGRAM-STYLE PROFILE CARDS */
.insta-grid {
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
  gap:16px;
  padding:4px 0;
}
.insta-card {
  background:#fff;
  border-radius:16px;
  overflow:hidden;
  box-shadow:0 2px 12px rgba(0,0,0,0.08);
  transition:transform .18s, box-shadow .18s;
  cursor:pointer;
  position:relative;
}
.insta-card:hover {
  transform:translateY(-4px);
  box-shadow:0 8px 28px rgba(0,0,0,0.14);
}
.insta-card-cover {
  height:100px;
  background:linear-gradient(135deg,var(--c1),var(--c2));
  position:relative;
  display:flex;align-items:center;justify-content:center;
}
.insta-card-avatar {
  width:72px;height:72px;border-radius:50%;
  background:#fff;
  display:flex;align-items:center;justify-content:center;
  font-size:26px;font-weight:700;
  border:3px solid #fff;
  box-shadow:0 4px 16px rgba(0,0,0,0.12);
  position:absolute;
  bottom:-28px;left:50%;transform:translateX(-50%);
}
.insta-card-body {
  padding:34px 14px 14px;
  text-align:center;
}
.insta-card-name {
  font-weight:700;font-size:14px;color:var(--text-primary);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.insta-card-cpid {
  font-size:11.5px;color:var(--text-secondary);margin-top:2px;
}
.insta-card-tags {
  display:flex;flex-wrap:wrap;gap:4px;justify-content:center;
  margin:10px 0 8px;
}
.insta-tag {
  font-size:10.5px;padding:2px 8px;border-radius:10px;
  font-weight:600;
}
.insta-card-stats {
  display:flex;justify-content:space-around;
  border-top:1px solid var(--border);padding-top:10px;margin-top:8px;
}
.insta-stat { text-align:center; }
.insta-stat-num { font-size:14px;font-weight:700;color:var(--text-primary); }
.insta-stat-lbl { font-size:10px;color:var(--text-secondary); }
.insta-card-badge {
  position:absolute;top:10px;right:10px;
  font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;
}
.view-toggle {
  display:flex;gap:4px;background:#f3f4f6;border-radius:8px;padding:3px;
}
.view-toggle-btn {
  padding:5px 12px;border-radius:6px;border:none;font-size:12px;
  font-family:'DM Sans',sans-serif;font-weight:600;cursor:pointer;
  color:var(--text-secondary);background:transparent;transition:all .15s;
}
.view-toggle-btn.active { background:#fff;color:var(--text-primary);box-shadow:0 1px 4px rgba(0,0,0,0.1); }
.pattern-pill {
  display:inline-flex; align-items:center; gap:5px;
  padding:5px 10px; border-radius:8px; font-size:12px;
  font-weight:600; line-height:1.4; white-space:normal; word-break:break-word;
  max-width:220px;
}
.pattern-1 { background:#ede9fe; color:#6d28d9; border:1px solid #ddd6fe; }
.pattern-2 { background:#fff8f0; color:#c2410c; border:1px solid #fde68a; }
.pattern-3 { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.confidence-bar {
  height:5px; border-radius:3px; margin-top:5px;
  background:linear-gradient(90deg,var(--bar-color) var(--pct), #f3f4f6 var(--pct));
}
.session-badge { background:#eff6ff; color:#2563eb; font-size:11px;
  padding:3px 8px; border-radius:6px; font-weight:600; }
.interest-row { cursor:pointer; transition:background .12s; }
.interest-row:hover { background:#faf7ff !important; }
.perm-section { margin-bottom:20px; }
.perm-section-title {
  font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase;
  color:var(--text-secondary); padding:10px 0 8px; border-bottom:1px solid var(--border);
  margin-bottom:12px; display:flex; align-items:center; gap:6px;
}
.perm-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:0; }
.perm-row {
  display:flex; align-items:center; gap:10px; padding:9px 0;
  border-bottom:1px solid #f3f4f6; cursor:pointer; border-radius:6px;
  transition:background .12s; padding:8px 10px;
}
.perm-row:hover { background:#faf9f7; }
.perm-checkbox {
  width:17px; height:17px; border-radius:4px; border:1.8px solid #d1d5db;
  background:#fff; flex-shrink:0; display:flex; align-items:center;
  justify-content:center; transition:all .15s; cursor:pointer;
}
.perm-checkbox.checked { background:var(--accent); border-color:var(--accent); }
.perm-checkbox.checked::after { content:'✓'; color:#fff; font-size:11px; font-weight:700; }
.perm-checkbox.disabled { background:#f3f4f6; border-color:#e5e7eb; cursor:not-allowed; }
.perm-checkbox.disabled.checked { background:#16a34a; border-color:#16a34a; }
.perm-label { font-size:13px; color:var(--text-primary); flex:1; }
.perm-label.disabled { color:var(--text-secondary); }
.role-selector-bar {
  display:flex; gap:8px; padding:16px 20px; border-bottom:1px solid var(--border);
  align-items:center; flex-wrap:wrap; background:#faf9f7;
}
.role-tab {
  padding:6px 16px; border-radius:20px; border:1.5px solid var(--border);
  background:#fff; font-family:'DM Sans',sans-serif; font-size:13px;
  font-weight:600; cursor:pointer; transition:all .15s; color:var(--text-secondary);
}
.role-tab:hover { border-color:var(--accent); color:var(--accent); }
.role-tab.active { background:var(--accent); border-color:var(--accent); color:#fff; }

/* LOGIN POPUP NOTIFICATIONS */
.login-popup {
  position:fixed; top:20px; left:50%; transform:translateX(-50%) translateY(-80px);
  z-index:99999; min-width:320px; max-width:440px; width:90%;
  border-radius:14px; padding:14px 18px 14px 16px;
  display:flex; align-items:flex-start; gap:12px;
  box-shadow:0 8px 32px rgba(0,0,0,0.22);
  transition:transform .35s cubic-bezier(.34,1.56,.64,1), opacity .3s;
  opacity:0; pointer-events:none;
}
.login-popup.show {
  transform:translateX(-50%) translateY(0);
  opacity:1; pointer-events:auto;
}
.login-popup-error   { background:#fff; border-left:4px solid #dc2626; }
.login-popup-warning { background:#fff; border-left:4px solid #d97706; }
.login-popup-success { background:#fff; border-left:4px solid #16a34a; }
.login-popup-icon { font-size:22px; flex-shrink:0; line-height:1; margin-top:1px; }
.login-popup-title { font-weight:700; font-size:14px; margin-bottom:2px; }
.login-popup-msg   { font-size:12.5px; color:#4b5563; line-height:1.5; }
.login-popup-close {
  margin-left:auto; flex-shrink:0; background:none; border:none;
  font-size:18px; cursor:pointer; color:#9ca3af; padding:0 0 0 8px;
  line-height:1; transition:color .15s;
}
.login-popup-close:hover { color:#374151; }
.login-page {
  position:fixed; inset:0; z-index:9999;
  display:flex; align-items:center; justify-content:center;
  background:linear-gradient(135deg,#1a1a2e 0%,#2d2d5e 100%);
}
.login-card {
  background:#fff; border-radius:20px; padding:40px 36px;
  width:400px; max-width:calc(100vw - 32px);
  box-shadow:0 24px 80px rgba(0,0,0,0.3);
}
.login-logo {
  text-align:center; margin-bottom:28px;
}
.login-logo h1 { font-family:'DM Serif Display',serif; font-size:26px; color:var(--text-primary); margin-bottom:4px; }
.login-logo p  { font-size:13px; color:var(--text-secondary); }
.login-step { display:none; }
.login-step.active { display:block; animation:fadeIn .22s ease; }
.otp-inputs { display:flex; gap:10px; justify-content:center; margin:16px 0; }
.otp-digit {
  width:52px; height:56px; text-align:center; font-size:22px; font-weight:700;
  border:2px solid var(--border); border-radius:10px; outline:none;
  font-family:'DM Sans',sans-serif; color:var(--text-primary); transition:border .15s;
}
.otp-digit:focus { border-color:var(--accent); }
.login-btn {
  width:100%; padding:12px; background:var(--accent); color:#fff; border:none;
  border-radius:10px; font-family:'DM Sans',sans-serif; font-size:15px;
  font-weight:600; cursor:pointer; transition:background .15s; margin-top:8px;
}
.login-btn:hover { background:#d4553f; }
.login-link { color:var(--accent); cursor:pointer; font-size:13px; font-weight:500; text-decoration:underline; }
.login-error { background:#fee2e2; color:#dc2626; border-radius:8px; padding:10px 14px; font-size:13px; margin-bottom:14px; display:none; }
.login-success { background:#f0fdf4; color:#16a34a; border-radius:8px; padding:10px 14px; font-size:13px; margin-bottom:14px; display:none; }
.sev-high   { background:#fee2e2; color:#dc2626; }
.sev-medium { background:#fff8f0; color:#d97706; }
.sev-low    { background:#fefce8; color:#ca8a04; }
.watermark-preview {
  position:relative; display:inline-block;
  background:#f8f7f5; border-radius:8px; padding:10px 14px;
  font-size:13px; color:var(--text-primary); font-weight:500;
}
.watermark-preview::after {
  content: attr(data-wm);
  position:absolute; bottom:4px; right:8px;
  font-size:10px; color:rgba(220,38,38,0.35);
  font-weight:700; letter-spacing:1px; pointer-events:none;
}
.al-login   { background:#f0fdf4; color:#16a34a; }
.al-profile { background:#eff6ff; color:#2563eb; }
.al-bill    { background:#fdf4ff; color:#9333ea; }
.al-followup{ background:#fffbeb; color:#d97706; }
.al-admin   { background:#f3f4f6; color:#6b7280; }
.al-plan    { background:#eff6ff; color:#2563eb; }
.al-setting { background:#f0fdf4; color:#16a34a; }
.al-expired { background:#fff8f0; color:#d97706; }
.al-story   { background:#fdf2f8; color:#db2777; }
.al-ban     { background:#fee2e2; color:#dc2626; }
input[type="date"].filter-select { padding:8px 10px; cursor:pointer; }
</style>
</head>
<body>

<!-- ═══════════════════════════════════════════ -->
<!-- ADMIN LOGIN PAGE                          -->
<!-- ═══════════════════════════════════════════ -->
<!-- Session-check splash: hides both the login page and the shell until boot()
     resolves. Prevents the login page from flashing on refresh when the admin
     is actually still logged in. Removed in boot() once auth check completes. -->
<div id="sessionCheckSplash" style="position:fixed;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f0ede8;z-index:99999;font-family:'DM Sans',system-ui,sans-serif">
  <div style="width:44px;height:44px;border:3px solid #e5e0d8;border-top-color:#e8624a;border-radius:50%;animation:adm_spin 0.8s linear infinite"></div>
  <div style="margin-top:16px;font-size:13px;color:#6b7280;letter-spacing:0.3px">Checking session…</div>
  <style>@keyframes adm_spin{to{transform:rotate(360deg)}}</style>
</div>

<div id="loginPage" class="login-page" style="display:none">
  <div class="login-card">
    <div class="login-logo">
      <h1>Chennai Profile</h1>
      <p>Free Matrimony — Admin Console</p>
    </div>

    <div id="loginError"  class="login-error"></div>
    <div id="loginSuccess" class="login-success"></div>

    <!-- STEP 1: User ID + Password -->
    <div class="login-step active" id="loginStep1">
      <div class="form-row">
        <label class="input-label">Username / User ID</label>
        <input class="input" id="lg_user" placeholder="Enter your username" autocomplete="username">
      </div>
      <div class="form-row" style="position:relative">
        <label class="input-label">Password</label>
        <input class="input" id="lg_pass" type="password" placeholder="Enter your password" autocomplete="current-password" style="padding-right:40px;margin-bottom:0" onkeydown="if(event.key==='Enter')doLogin()">
        <button type="button" class="eye-btn" style="position:absolute;right:10px;top:38px" onclick="togglePwd('lg_pass',this)">👁</button>
      </div>
      <div style="text-align:right;margin:6px 0 16px">
        <span class="login-link" onclick="showForgot()">Forgot Password?</span>
      </div>
      <button class="login-btn" onclick="doLogin().catch(e=>alert('Login error: '+e.message))">Login</button>
    </div>

    <!-- STEP 2: OTP Verification -->
    <div class="login-step" id="loginStep2">
      <div style="text-align:center;margin-bottom:16px">
        <div style="font-size:36px">🔐</div>
        <div style="font-weight:600;font-size:15px;margin-top:8px">OTP Verification</div>
        <div id="otpSentTo" style="font-size:13px;color:var(--text-secondary);margin-top:4px"></div>
      </div>
      <div class="otp-inputs">
        <input class="otp-digit" id="otp1" maxlength="1" type="text" inputmode="numeric" oninput="otpMove(this,'otp2')" onkeydown="otpBack(event,this,'')">
        <input class="otp-digit" id="otp2" maxlength="1" type="text" inputmode="numeric" oninput="otpMove(this,'otp3')" onkeydown="otpBack(event,this,'otp1')">
        <input class="otp-digit" id="otp3" maxlength="1" type="text" inputmode="numeric" oninput="otpMove(this,'otp4')" onkeydown="otpBack(event,this,'otp2')">
        <input class="otp-digit" id="otp4" maxlength="1" type="text" inputmode="numeric" oninput="otpMove(this,'')"     onkeydown="otpBack(event,this,'otp3')">
      </div>
      <div style="text-align:center;margin-bottom:14px">
        <span id="otpTimer" style="font-size:13px;color:var(--text-secondary)"></span>
        <span class="login-link" id="resendBtn" style="display:none" onclick="resendOtp()">Resend OTP</span>
      </div>
      <button class="login-btn" onclick="verifyOtp()">Verify OTP</button>
      <div style="text-align:center;margin-top:12px">
        <span class="login-link" onclick="showLoginStep(1)">← Back to Login</span>
      </div>
    </div>

    <!-- STEP 3: Forgot Password -->
    <div class="login-step" id="loginStep3">
      <div style="text-align:center;margin-bottom:16px">
        <div style="font-size:36px">🔑</div>
        <div style="font-weight:600;font-size:15px;margin-top:8px">Reset Password</div>
        <div style="font-size:13px;color:var(--text-secondary);margin-top:4px">Enter your username to receive a reset OTP</div>
      </div>
      <div class="form-row">
        <label class="input-label">Username / User ID</label>
        <input class="input" id="lg_fp_user" placeholder="Enter your username">
      </div>
      <div class="form-row">
        <label class="input-label">Registered Mobile</label>
        <input class="input" id="lg_fp_mobile" type="tel" maxlength="10" placeholder="10-digit mobile">
      </div>
      <button class="login-btn" onclick="doForgotPassword()">Send Reset OTP</button>
      <div style="text-align:center;margin-top:12px">
        <span class="login-link" onclick="showLoginStep(1)">← Back to Login</span>
      </div>
    </div>
  </div>
</div>

<!-- LOGIN NOTIFICATION POPUP -->
<div id="loginPopup" class="login-popup">
  <div class="login-popup-icon" id="lpIcon">⚠️</div>
  <div style="flex:1">
    <div class="login-popup-title" id="lpTitle"></div>
    <div class="login-popup-msg"   id="lpMsg"></div>
  </div>
  <button class="login-popup-close" onclick="hideLoginPopup()">×</button>
</div>

<!-- HAMBURGER -->
<button class="hamburger" id="hamburger" onclick="toggleSidebar()" aria-label="Menu" style="display:none">
  <span></span><span></span><span></span>
</button>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- APP SHELL (hidden until login) -->
<div id="appShell" style="display:none">

<!-- SIDEBAR -->
<nav class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo">
      Chennai Profile
      <span>Matrimony</span>
    </div>
    <a href="print-profile-form.php" target="_blank" style="display:flex;align-items:center;gap:6px;margin-top:12px;padding:7px 12px;background:rgba(232,98,74,0.15);border:1px solid rgba(232,98,74,0.3);border-radius:8px;color:#e8624a;font-size:11.5px;font-weight:600;text-decoration:none;transition:all 0.2s" onmouseover="this.style.background='rgba(232,98,74,0.25)'" onmouseout="this.style.background='rgba(232,98,74,0.15)'">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Download PDF Form
    </a>
  </div>
  <div class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <button class="nav-btn active" data-perm="view_profiles" onclick="show('profile',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Profiles
    </button>
    <button class="nav-btn" data-perm="view_manage" onclick="show('manage',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Manage
    </button>
    <button class="nav-btn" data-perm="view_otp" onclick="show('otp',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/><line x1="12" y1="15" x2="12" y2="17"/></svg>
      OTP Logs
    </button>
    <button class="nav-btn" data-perm="view_bills" onclick="show('bill',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
      Bills
    </button>
    <button class="nav-btn" data-perm="view_bills" onclick="show('userOrders',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      User Orders
    </button>
    <button class="nav-btn" data-perm="view_deleted" onclick="show('deleted',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
      Deleted
    </button>
    <button class="nav-btn" data-perm="view_expired" onclick="show('expired',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      Expired
    </button>
    <button class="nav-btn" data-perm="view_alerts" onclick="show('alerts',this)" id="alertsNavBtn">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      <span>Alerts</span>
      <span id="alertBadge" style="margin-left:auto;background:#dc2626;color:#fff;border-radius:10px;font-size:10px;font-weight:700;padding:1px 6px;display:none">0</span>
    </button>
    <button class="nav-btn" data-perm="view_contactlog" onclick="show('contactLog',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.36 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.27 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z"/></svg>
      Contact Log
    </button>
    <button class="nav-btn" data-perm="view_contactlog" onclick="show('profileViewLog',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      Profile View Log
    </button>
    <button class="nav-btn" data-perm="view_contactlog" onclick="show('userResponse',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M22 11l-3 3-1.5-1.5"/></svg>
      User Response
    </button>
    <button class="nav-btn" data-perm="view_contactlog" onclick="show('userActivity',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      User Activity
    </button>
    <button class="nav-btn" onclick="show('messages',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      Messages <span id="msgBadge" style="display:none;background:#dc2626;color:#fff;font-size:10px;padding:1px 6px;border-radius:10px;margin-left:4px"></span>
    </button>
    <button class="nav-btn" data-perm="view_settings" onclick="show('directLogin',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
      Direct Login
    </button>
    <button class="nav-btn" data-perm="view_reports" onclick="show('profileReports',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      Profile Reports
    </button>
    <div class="nav-section-label">Operations</div>
    <button class="nav-btn nav-parent" data-perm="view_profiles" onclick="toggleAdminSubmenu(this)" aria-expanded="false">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      Matches
      <svg class="nav-caret" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left:auto;transition:transform .2s"><polyline points="6 9 12 15 18 9"/></svg>
    </button>
    <div class="nav-sub" data-parent="matches" hidden>
      <button class="nav-btn nav-child" data-perm="view_profiles" onclick="show('basicMatches',this)">
        <span class="nav-bullet">•</span> Basic Matches
      </button>
      <button class="nav-btn nav-child" data-perm="view_profiles" onclick="show('mutualMatches',this)">
        <span class="nav-bullet">•</span> Mutual Matches
      </button>
    </div>
    <button class="nav-btn" data-perm="view_followups" onclick="show('follow',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.36 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.27 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z"/></svg>
      Follow-ups
    </button>
    <button class="nav-btn" data-perm="view_reports" onclick="show('interest',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M11 8v6M8 11h6"/></svg>
      Interest Patterns
    </button>
    <button class="nav-btn" data-perm="view_usage" onclick="show('usage',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      Usage
    </button>
    <!-- Add Profile and Add Order removed from sidebar -->
    <div class="nav-section-label" data-perm-group="settings">Config</div>
    <button class="nav-btn" data-perm="view_settings" onclick="show('settings',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M22 12h-2M4 12H2M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M12 22v-2M12 4V2"/></svg>
      Settings
    </button>
    <div class="nav-section-label" data-perm-group="insights">Insights</div>
    <button class="nav-btn" data-perm="view_reports" onclick="show('reports',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><polyline points="8 21 12 17 16 21"/><line x1="12" y1="17" x2="12" y2="21"/><polyline points="7 10 10 7 13 10 17 6"/></svg>
      Reports
    </button>
    <button class="nav-btn" data-perm="view_notifs" onclick="show('notifications',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      Notifications
    </button>
    <button class="nav-btn" data-perm="view_stories" onclick="show('stories',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      Success Stories
    </button>
    <button class="nav-btn" data-perm="view_bills" onclick="show('accounts',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      Accounts
    </button>
    <button class="nav-btn" data-perm="view_settings" onclick="show('updateHistory',this)">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      Update History
    </button>
  </div>
  <div class="sidebar-footer">
    <div id="sidebarAdminInfo" style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
      <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#e8624a,#d4553f);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0" id="sidebarAdminAvatar">A</div>
      <div style="flex:1;min-width:0">
        <div id="sidebarAdminName" style="font-size:12.5px;font-weight:600;color:rgba(255,255,255,0.85);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">Admin</div>
        <div id="sidebarAdminRole" style="font-size:10.5px;color:rgba(255,255,255,0.4);text-transform:uppercase;letter-spacing:0.05em">Super Admin</div>
        <div id="sidebarAdminMobile" style="font-size:10px;color:rgba(255,255,255,0.35);margin-top:1px;font-family:monospace"></div>
      </div>
    </div>
    <button id="myAccountBtn" onclick="openMyAccount()" style="width:100%;padding:9px 14px;margin-bottom:6px;border:1px solid rgba(100,180,255,0.25);border-radius:8px;background:rgba(30,64,175,0.15);color:#bfdbfe;font-size:12.5px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.2s" onmouseover="this.style.background='rgba(30,64,175,0.3)';this.style.borderColor='rgba(100,180,255,0.5)';this.style.color='#dbeafe'" onmouseout="this.style.background='rgba(30,64,175,0.15)';this.style.borderColor='rgba(100,180,255,0.25)';this.style.color='#bfdbfe'">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      My Account
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left:auto;opacity:0.6"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
    </button>
    <button onclick="doSignOut()" style="width:100%;padding:9px 14px;border:1px solid rgba(255,255,255,0.15);border-radius:8px;background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.7);font-size:12.5px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.2s" onmouseover="this.style.background='rgba(220,38,38,0.2)';this.style.borderColor='rgba(220,38,38,0.4)';this.style.color='#fca5a5'" onmouseout="this.style.background='rgba(255,255,255,0.06)';this.style.borderColor='rgba(255,255,255,0.15)';this.style.color='rgba(255,255,255,0.7)'">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Sign Out
    </button>
    <div style="text-align:center;margin-top:10px;font-size:10.5px;color:rgba(255,255,255,0.2)">Chennai Profile Matrimony v2.0</div>
  </div>
</nav>

<!-- MAIN -->
<div class="main">

  <!-- PROFILES -->
  <div class="section active" id="profileSection">
    <div class="page-header">
      <div>
        <div class="page-title">Profiles</div>
        <div class="page-subtitle">All registered members</div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <div class="view-toggle" style="margin-right:4px">
          <button class="view-toggle-btn active" id="vtable" onclick="switchProfileView('table',this)">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Table
          </button>
          <button class="view-toggle-btn" id="vcards" onclick="switchProfileView('cards',this)">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><polyline points="8 21 12 17 16 21"/></svg>
            Cards
          </button>
        </div>
        <button class="btn btn-outline" onclick="exportCSV('profiles')">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Export CSV
        </button>
        <button class="btn btn-primary" onclick="openAdd()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add Profile
        </button>
      </div>
    </div>

    <div class="search-bar">
      <input class="search-input" id="profileSearch" placeholder="Search by name, mobile or CP ID…" oninput="applyProfileFilter()">
      <select class="filter-select" id="profileStatusFilter" onchange="applyProfileFilter()">
        <option value="">All Statuses</option>
        <option value="Approved">Approved</option>
        <option value="Preapproved">Pre-approved</option>
      </select>
      <select class="filter-select" id="profilePlanFilter" onchange="applyProfileFilter()">
        <option value="">All Plans</option>
        <option value="free">Free</option>
        <option value="paid">Paid</option>
        <option value="premium">Premium</option>
      </select>
      <select class="filter-select" id="profileGenderFilter" onchange="applyProfileFilter()">
        <option value="">All Genders</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
      </select>
      <select class="filter-select" id="profilePhotoFilter" onchange="applyProfileFilter()">
        <option value="">All Photos</option>
        <option value="with">With Photo</option>
        <option value="without">Without Photo</option>
      </select>
      <input class="filter-select" id="profileDateFrom" type="date" title="Registered From" onchange="applyProfileFilter()" style="font-size:12px">
      <input class="filter-select" id="profileDateTo"   type="date" title="Registered To"   onchange="applyProfileFilter()" style="font-size:12px">
      <button class="btn btn-ghost btn-sm" onclick="clearProfileFilters()" title="Clear">✕ Clear</button>
    </div>

    <div class="stats-row" id="statsRow"></div>

    <div class="card">
      <div class="card-header">
        <span class="card-title">All Members</span>
        <span class="badge badge-gray" id="profileCount"></span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>CP ID</th>
              <th>Picture</th>
              <th>Member</th>
              <th>Created By</th>
              <th>Age</th>
              <th>Mobile</th>
              <th>Status</th>
              <th>Plan</th>
              <th>Photo</th>
              <th>Mandatory</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="profileTable"></tbody>
        </table>
      </div>
    </div>
    <div id="instaCards" style="display:none"></div>
  </div>
  <div class="section" id="manageSection">
    <div class="page-header">
      <div>
        <div class="page-title">Manage Profiles</div>
        <div class="page-subtitle">Approve, follow-up or update billing</div>
      </div>
    </div>
    <div class="search-bar" style="margin-bottom:16px">
      <input class="search-input" id="manageSearch" placeholder="Search by CP ID, name or mobile…" oninput="applyManageFilter()">
      <select class="filter-select" id="manageStatusFilter" onchange="applyManageFilter()">
        <option value="">All Statuses</option>
        <option value="Approved">Approved</option>
        <option value="Preapproved">Pre-approved</option>
      </select>
      <select class="filter-select" id="managePlanFilter" onchange="applyManageFilter()">
        <option value="">All Plans</option>
        <option value="free">Free</option>
        <option value="paid">Paid</option>
        <option value="premium">Premium</option>
      </select>
      <input class="filter-select" id="manageDateFrom" type="date" title="Created From" onchange="applyManageFilter()" style="font-size:12px">
      <input class="filter-select" id="manageDateTo"   type="date" title="Created To"   onchange="applyManageFilter()" style="font-size:12px">
      <button class="btn btn-ghost btn-sm" onclick="clearManageFilters()" title="Clear">✕ Clear</button>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:8px">
      <div id="serverPagInfo" style="font-size:13px;color:var(--text-secondary)">Loading…</div>
      <div style="display:flex;gap:6px">
        <button class="btn btn-outline btn-sm" id="serverPagPrev" onclick="profPagePrev()" disabled>← Prev</button>
        <button class="btn btn-outline btn-sm" id="serverPagNext" onclick="profPageNext()">Next →</button>
      </div>
    </div>
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>CP ID</th>
              <th>Member</th>
              <th>Created By</th>
              <th>Mobile</th>
              <th>Status</th>
              <th>Plan</th>
              <th>Created</th>
              <th>Approved</th>
              <th>Expiry</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="manageTable"></tbody>
        </table>
      </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;flex-wrap:wrap;gap:8px">
      <div id="serverPagInfo2" style="font-size:13px;color:var(--text-secondary)"></div>
      <div style="display:flex;gap:6px">
        <button class="btn btn-outline btn-sm" id="serverPagPrev2" onclick="profPagePrev()" disabled>← Prev</button>
        <button class="btn btn-outline btn-sm" id="serverPagNext2" onclick="profPageNext()">Next →</button>
      </div>
    </div>
  </div>

  <!-- BASIC MATCHES -->
  <div class="section" id="basicMatchesSection">
    <div class="page-header">
      <div>
        <div class="page-title">Basic Matches</div>
        <div class="page-subtitle">Profiles that satisfy a user's partner preferences (one-way).</div>
      </div>
    </div>
    <div class="card" style="padding:14px 16px;margin-bottom:16px;display:flex;flex-wrap:wrap;gap:10px;align-items:center">
      <label style="font-size:12px;font-weight:600;color:var(--text-secondary)">Source:</label>
      <input type="text" id="bmSourceCpId" placeholder="CP ID (e.g. CP1234)" style="padding:7px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;width:160px">
      <span style="font-size:11px;color:var(--text-secondary)">or</span>
      <input type="text" id="bmSourceMobile" placeholder="Mobile" style="padding:7px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;width:140px">
      <button class="btn btn-primary btn-sm" onclick="loadAdminMatches('basic')">Find Matches</button>
      <span id="bmStatus" style="font-size:12px;color:var(--text-secondary);margin-left:auto"></span>
    </div>
    <div id="bmResults"></div>
  </div>

  <!-- MUTUAL MATCHES -->
  <div class="section" id="mutualMatchesSection">
    <div class="page-header">
      <div>
        <div class="page-title">Mutual Matches</div>
        <div class="page-subtitle">Two-way fit — both sides' partner preferences are satisfied.</div>
      </div>
    </div>
    <div class="card" style="padding:14px 16px;margin-bottom:16px;display:flex;flex-wrap:wrap;gap:10px;align-items:center">
      <label style="font-size:12px;font-weight:600;color:var(--text-secondary)">Source:</label>
      <input type="text" id="mmSourceCpId" placeholder="CP ID (e.g. CP1234)" style="padding:7px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;width:160px">
      <span style="font-size:11px;color:var(--text-secondary)">or</span>
      <input type="text" id="mmSourceMobile" placeholder="Mobile" style="padding:7px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;width:140px">
      <button class="btn btn-primary btn-sm" onclick="loadAdminMatches('mutual')">Find Matches</button>
      <span id="mmStatus" style="font-size:12px;color:var(--text-secondary);margin-left:auto"></span>
    </div>
    <div id="mmResults"></div>
  </div>

  <!-- FOLLOW-UPS -->
  <div class="section" id="followSection">
    <div class="page-header">
      <div>
        <div class="page-title">Follow-ups</div>
        <div class="page-subtitle">Categorized by date — edit or reassign anytime</div>
      </div>
      <button class="btn btn-primary btn-sm" onclick="openAddFollowUp()" style="gap:5px">+ Add Follow-up</button>
    </div>
    <div class="search-bar" style="margin-bottom:16px">
      <input class="search-input" id="followSearch" placeholder="Search by CP ID, name or mobile…" oninput="renderFollowTables()">
      <select class="filter-select" id="followTypeFilter" onchange="renderFollowTables()">
        <option value="">All Types</option>
        <option value="data">Data</option>
        <option value="payment">Payment</option>
        <option value="not_interested">Not Interested</option>
        <option value="paid">Paid</option>
      </select>
      <input class="filter-select" id="followDateFrom" type="date" title="From date" onchange="renderFollowTables()" style="font-size:12px">
      <input class="filter-select" id="followDateTo"   type="date" title="To date"   onchange="renderFollowTables()" style="font-size:12px">
      <button class="btn btn-ghost btn-sm" onclick="clearFollowFilters()" title="Clear">✕ Clear</button>
    </div>

    <!-- TABLE 1: TODAY -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-header" style="background:#fffbeb;border-radius:12px 12px 0 0">
        <span class="card-title" style="color:#d97706">📅 Today's Follow-ups</span>
        <span class="badge badge-amber" id="todayCount">0</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>CP ID</th><th>Member</th><th>Type</th><th>Admin</th><th>Date</th><th>Notes</th><th>Action</th></tr></thead>
          <tbody id="todayFollowTable"></tbody>
        </table>
      </div>
    </div>

    <!-- TABLE 2: PAST / YESTERDAY -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-header" style="background:#fff5f5;border-radius:12px 12px 0 0">
        <span class="card-title" style="color:#dc2626">⏰ Past Follow-ups</span>
        <span class="badge" style="background:#fee2e2;color:#dc2626" id="pastCount">0</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>CP ID</th><th>Member</th><th>Type</th><th>Admin</th><th>Date</th><th>Notes</th><th>Action</th></tr></thead>
          <tbody id="pastFollowTable"></tbody>
        </table>
      </div>
    </div>

    <!-- TABLE 3: TOMORROW & FUTURE -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-header" style="background:#f0fdf4;border-radius:12px 12px 0 0">
        <span class="card-title" style="color:#16a34a">🔮 Upcoming Follow-ups</span>
        <span class="badge badge-green" id="futureCount">0</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>CP ID</th><th>Member</th><th>Type</th><th>Admin</th><th>Date</th><th>Notes</th><th>Action</th></tr></thead>
          <tbody id="futureFollowTable"></tbody>
        </table>
      </div>
    </div>

    <!-- TABLE 4: CLOSED (PAID / NOT INTERESTED) -->
    <div class="card">
      <div class="card-header" style="background:#f8f7f5;border-radius:12px 12px 0 0">
        <span class="card-title" style="color:#6b7280">🗂 Closed Follow-ups <span style="font-size:12px;font-weight:400">(Paid &amp; Not Interested)</span></span>
        <span class="badge badge-gray" id="closedCount">0</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>CP ID</th><th>Member</th><th>Type</th><th>Admin</th><th>Closed On</th><th>Notes</th><th>Action</th></tr></thead>
          <tbody id="closedFollowTable"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- BILLS -->
  <div class="section" id="billSection">
    <div class="page-header">
      <div>
        <div class="page-title">Bills</div>
        <div class="page-subtitle">Active billing records &amp; full change history</div>
      </div>
      <button class="btn btn-outline" onclick="exportCSV('bills')">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export CSV
      </button>
    </div>

    <!-- ── ACTIVE BILLS ── -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-header">
        <span class="card-title">💳 Active Bills</span>
        <span class="badge badge-gray" id="billCount">0</span>
      </div>
      <div class="search-bar" style="padding:12px 16px 0;margin-bottom:0">
        <input class="search-input" id="billSearch" placeholder="Search by name, CP ID or mobile…" oninput="renderBills()">
        <select class="filter-select" id="billPlanFilter" onchange="renderBills()">
          <option value="">All Plan Types</option>
          <option value="free">Free</option>
          <option value="basic">Basic</option>
          <option value="paid">Paid</option>
          <option value="premium">Premium</option>
          <option value="vip">VIP</option>
        </select>
        <select class="filter-select" id="billPaymentFilter" onchange="renderBills()">
          <option value="">All Payments</option>
          <option value="Cash">Cash</option>
          <option value="UPI">UPI</option>
          <option value="GPay">GPay</option>
          <option value="QR Code">QR Code</option>
          <option value="Pay Link">Pay Link</option>
          <option value="Payment Gateway">Payment Gateway</option>
          <option value="Bank Transfer">Bank Transfer</option>
          <option value="Free">Free</option>
        </select>
        <input class="filter-select" id="billDateFrom" type="date" title="Billed From" onchange="renderBills()" style="font-size:12px">
        <input class="filter-select" id="billDateTo"   type="date" title="Billed To"   onchange="renderBills()" style="font-size:12px">
        <button class="btn btn-ghost btn-sm" onclick="clearBillFilters()" title="Clear filters">✕ Clear</button>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>CP ID</th><th>Name</th><th>Mobile</th>
              <th>Plan Name</th><th>Plan Type</th><th>Amount</th>
              <th>Payment</th><th>Billed By</th><th>Billed Date</th><th>Expiry</th><th>Action</th>
            </tr>
          </thead>
          <tbody id="billTable"></tbody>
        </table>
      </div>
    </div>

    <!-- ── BILL HISTORY (all updates, newest first) ── -->
    <div class="card">
      <div class="card-header" style="background:#faf9f7">
        <div>
          <span class="card-title">🗂 Bill Update History</span>
          <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">Every bill creation and edit is recorded here — oldest versions kept permanently</div>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
          <span class="badge badge-gray" id="billHistoryCount">0 records</span>
          <button class="btn btn-outline btn-sm" onclick="exportCSV('billHistory')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
          </button>
        </div>
      </div>
      <div class="search-bar" style="padding:12px 16px 0;margin-bottom:0">
        <input class="search-input" id="bhSearch"   placeholder="Search by CP ID, name or mobile…" oninput="renderBillHistory()">
        <select class="filter-select" id="bhActionFilter" onchange="renderBillHistory()">
          <option value="">All Actions</option>
          <option value="Created">Created</option>
          <option value="Updated">Updated</option>
        </select>
        <input class="filter-select" id="bhDateFrom" type="date" title="From date" onchange="renderBillHistory()" style="font-size:12px">
        <input class="filter-select" id="bhDateTo"   type="date" title="To date"   onchange="renderBillHistory()" style="font-size:12px">
        <button class="btn btn-ghost btn-sm" onclick="clearBhFilters()" title="Clear">✕ Clear</button>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Action</th><th>CP ID</th><th>Name</th><th>Mobile</th>
              <th>Plan Name</th><th>Amount</th><th>Payment</th>
              <th>Billed By</th><th>Recorded At</th><th>Expiry</th>
            </tr>
          </thead>
          <tbody id="billHistoryTable"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- USAGE -->
  <div class="section" id="usageSection">
    <div class="page-header">
      <div>
        <div class="page-title">User Usage</div>
        <div class="page-subtitle">Profile and contact view activity per member</div>
      </div>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <input class="search-input" id="usageMobileSearch" placeholder="🔍 Search by mobile number…" style="width:220px" oninput="renderUsage()">
        <button class="btn btn-ghost btn-sm" onclick="document.getElementById('usageMobileSearch').value='';renderUsage()">✕</button>
        <button class="btn btn-outline btn-sm" onclick="collapseAllUsage()">⊟ Collapse All</button>
        <button class="btn btn-outline btn-sm" onclick="expandAllUsage()">⊞ Expand All</button>
      </div>
    </div>
    <div id="usageCards"></div>
  </div>

  <!-- SETTINGS -->
  <div class="section" id="settingsSection">
    <div class="page-header">
      <div>
        <div class="page-title">Settings</div>
        <div class="page-subtitle">Manage subscription plans &amp; admin accounts</div>
      </div>
    </div>

    <div class="card">
      <!-- TABS -->
      <div class="settings-tabs">
        <button class="stab active" id="stab_subPlans"  onclick="switchStab('subPlansPanel',  this)">📦 Subscription Plans</button>
        <button class="stab"        id="stab_restrict"   onclick="switchStab('restrictPanel',   this)">🔒 Restrictions</button>
        <button class="stab"        id="stab_adminAcct"  onclick="switchStab('adminPanel',      this)">👤 Admin Accounts</button>
        <button class="stab"        id="stab_roleDetails" onclick="switchStab('roleDetailsPanel', this)">🔐 Role Details</button>
        <button class="stab"        id="stab_adminLog"   onclick="switchStab('adminLogPanel',   this)">📋 Admin Log</button>
        <button class="stab"        id="stab_mobileReq"  onclick="switchStab('mobileReqPanel',  this)">📱 Number Change Requests</button>
        <button class="stab"        id="stab_payment"    onclick="switchStab('paymentPanel',    this)">💳 Payment Options</button>
        <button class="stab"        id="stab_points"     onclick="switchStab('pointsPanel',     this)">🪙 Points (Chennai Profile)</button>
        <button class="stab"        id="stab_userCtrl"   onclick="switchStab('userCtrlPanel',   this)">📱 User Panel Control</button>
      </div>

      <!-- ══ SUBSCRIPTION PLANS PANEL ══ -->
      <div class="stab-panel active" id="subPlansPanel">
        <div style="display:grid;grid-template-columns:390px 1fr;min-height:460px">

          <!-- LEFT: FORM -->
          <div style="border-right:1px solid var(--border);padding:22px 24px;background:#faf9f7">
            <div style="font-weight:700;font-size:14px;margin-bottom:16px;display:flex;align-items:center;gap:8px">
              <span id="spFormIcon">➕</span>
              <span id="spFormTitle">Create Subscription Plan</span>
            </div>

            <div class="form-row">
              <label class="input-label">Plan Name <span style="color:#e8624a">*</span></label>
              <input class="input" id="cp_name" placeholder="e.g. Gold 3-Month, Silver Annual…">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
              <div class="form-row">
                <label class="input-label">Plan Type <span style="color:#e8624a">*</span></label>
                <select class="input" id="cp_type">
                  <option value="">— Select —</option>
                  <option value="free">🔓 Free</option>
                  <option value="basic">📄 Basic</option>
                  <option value="paid">💎 Paid</option>
                  <option value="premium">⭐ Premium</option>
                  <option value="vip">👑 VIP</option>
                  <option value="custom">🔧 Custom</option>
                </select>
              </div>
              <div class="form-row">
                <label class="input-label">Amount (₹) <span style="color:#e8624a">*</span></label>
                <input class="input" id="cp_amount" type="number" min="0" placeholder="0 = Free">
              </div>
            </div>

            <div class="form-row">
              <label class="input-label">Validity — Number of Days <span style="color:#e8624a">*</span></label>
              <input class="input" id="cp_validity" type="number" min="1" placeholder="e.g. 30, 90, 180, 365">
              <div style="display:flex;gap:6px;margin-top:6px;flex-wrap:wrap">
                <button type="button" class="sp-preset" onclick="setValidity(30)">30 days</button>
                <button type="button" class="sp-preset" onclick="setValidity(60)">60 days</button>
                <button type="button" class="sp-preset" onclick="setValidity(90)">90 days</button>
                <button type="button" class="sp-preset" onclick="setValidity(180)">6 months</button>
                <button type="button" class="sp-preset" onclick="setValidity(365)">1 year</button>
              </div>
            </div>

            <div class="form-row">
              <label class="input-label">Description <span style="color:#e8624a">*</span></label>
              <input class="input" id="cp_desc" placeholder="Brief description of this plan…">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
              <div class="form-row">
                <label class="input-label">Created By <span style="color:#e8624a">*</span></label>
                <input class="input" id="cp_createdby" placeholder="Admin name" readonly style="background:#f0ede8;color:var(--text-primary);font-weight:500">
              </div>
              <div class="form-row">
                <label class="input-label">Plan Status</label>
                <select class="input" id="cp_status">
                  <option value="active">✅ Active</option>
                  <option value="inactive">⛔ Inactive</option>
                </select>
              </div>
            </div>

            <div style="display:flex;gap:8px;margin-top:6px">
              <button class="btn btn-primary" style="flex:1" onclick="saveCustomPlan()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span id="cpSaveTxt">Create Plan</span>
              </button>
              <button class="btn btn-outline" id="cpCancelBtn" onclick="cancelEditPlan()" style="display:none">Cancel</button>
            </div>
          </div>

          <!-- RIGHT: PLANS TABLE -->
          <div style="display:flex;flex-direction:column;overflow:hidden">
            <div style="padding:14px 20px 12px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
              <span style="font-weight:600;font-size:14px">All Subscription Plans</span>
              <div style="display:flex;gap:8px;align-items:center">
                <span class="badge badge-gray" id="planCount">0 plans</span>
                <button class="btn btn-outline btn-sm" onclick="exportCSV('plans')">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                  Export CSV
                </button>
              </div>
            </div>
            <div class="table-wrap" style="flex:1">
              <table>
                <thead>
                  <tr>
                    <th>#</th><th>Plan Name</th><th>Type</th><th>Amount</th>
                    <th>Validity</th><th>Created Date</th><th>Created By</th><th>Status</th><th>User Side</th><th>Actions</th>
                  </tr>
                </thead>
                <tbody id="customPlanTable"></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ── PLAN UPDATE HISTORY TABLE ────────────────────── -->
        <div style="border-top:2px solid var(--border);margin-top:0">
          <div style="padding:14px 20px 12px;background:#faf9f7;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border)">
            <div>
              <span style="font-weight:700;font-size:13.5px">📋 Subscription Plan Update History</span>
              <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">Every plan creation and edit is recorded here — oldest versions kept permanently</div>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
              <span class="badge badge-gray" id="planHistCount">0 records</span>
              <button class="btn btn-ghost btn-sm" onclick="clearPlanHistory()" style="font-size:11.5px;color:#dc2626;border-color:#fca5a5">🗑 Clear History</button>
            </div>
          </div>
          <!-- Search / Filter -->
          <div style="display:flex;gap:10px;align-items:center;padding:10px 16px;background:#faf9f7;border-bottom:1px solid var(--border);flex-wrap:wrap">
            <input class="search-input" id="phSearch" placeholder="Search by plan name or admin…" oninput="renderPlanHistory()" style="max-width:280px;flex:1">
            <select class="filter-select" id="phActionFilter" onchange="renderPlanHistory()">
              <option value="">All Actions</option>
              <option value="Created">Created</option>
              <option value="Updated">Updated</option>
            </select>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th><th>Action</th><th>Plan Name</th><th>Type</th>
                  <th>Amount</th><th>Validity</th><th>Description</th>
                  <th>Status</th><th>Recorded By</th><th>Recorded At</th>
                </tr>
              </thead>
              <tbody id="planHistoryTable"></tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- ══ RESTRICTIONS PANEL ══ -->
      <div class="stab-panel" id="restrictPanel">
        <div style="display:grid;grid-template-columns:1fr 1fr;min-height:420px">

          <!-- LEFT: ALL USERS -->
          <div style="border-right:1px solid var(--border);padding:24px 26px">
            <div style="font-weight:700;font-size:14px;margin-bottom:4px;display:flex;align-items:center;gap:8px">
              <span style="background:#eff6ff;color:#2563eb;border-radius:8px;padding:4px 8px;font-size:13px">👥</span>
              All Users — Global Limit
            </div>
            <div style="font-size:12px;color:var(--text-secondary);margin-bottom:20px">
              Applies to every user unless overridden by an individual restriction
            </div>

            <div style="background:#f8f7f5;border-radius:10px;padding:16px 18px;margin-bottom:16px">
              <div style="font-size:12px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--text-secondary);margin-bottom:12px">Contact Views</div>
              <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                <div>
                  <label class="input-label">Per Day</label>
                  <input class="input" id="gl_day" type="number" min="0" placeholder="e.g. 5" style="margin-bottom:0">
                </div>
                <div>
                  <label class="input-label">Per Month</label>
                  <input class="input" id="gl_month" type="number" min="0" placeholder="e.g. 50" style="margin-bottom:0">
                </div>
                <div>
                  <label class="input-label">Total (Lifetime)</label>
                  <input class="input" id="gl_total" type="number" min="0" placeholder="e.g. 200" style="margin-bottom:0">
                </div>
              </div>
              <div style="font-size:11px;color:var(--text-secondary);margin-top:8px">Enter 0 or leave blank for unlimited</div>
            </div>

            <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:16px 18px;margin-bottom:16px">
              <div style="font-size:12px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:#c2410c;margin-bottom:4px">Unverified Visitors — Per Session</div>
              <div style="font-size:11px;color:var(--text-secondary);margin-bottom:12px">Anonymous (mobile-not-verified) users. Session starts on the first contact viewed.</div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                  <label class="input-label">Max Contacts / Session</label>
                  <input class="input" id="gl_uv_views" type="number" min="0" placeholder="e.g. 2" style="margin-bottom:0">
                </div>
                <div>
                  <label class="input-label">Session Time Limit (hrs)</label>
                  <input class="input" id="gl_uv_hours" type="number" min="0" placeholder="e.g. 4" style="margin-bottom:0">
                </div>
              </div>
              <div style="font-size:11px;color:var(--text-secondary);margin-top:8px">Enter 0 or leave blank for unlimited / no auto-reset</div>
            </div>

            <button class="btn btn-primary" style="width:100%" onclick="saveGlobalRestriction()">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
              Save Global Restriction
            </button>

            <!-- Saved global display -->
            <div id="globalRestrictionDisplay" style="margin-top:18px;display:none">
              <div style="font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:8px;text-transform:uppercase;letter-spacing:.7px">Current Global Limit</div>
              <div id="globalRestrictionCard"></div>
            </div>
          </div>

          <!-- RIGHT: INDIVIDUAL USER -->
          <div style="padding:24px 26px">
            <div style="font-weight:700;font-size:14px;margin-bottom:4px;display:flex;align-items:center;gap:8px">
              <span style="background:#fdf4ff;color:#9333ea;border-radius:8px;padding:4px 8px;font-size:13px">👤</span>
              Individual User — Override
            </div>
            <div style="font-size:12px;color:var(--text-secondary);margin-bottom:20px">
              Set a custom limit for a specific user by mobile number
            </div>

            <div style="background:#f8f7f5;border-radius:10px;padding:16px 18px;margin-bottom:16px">
              <div style="margin-bottom:12px">
                <label class="input-label">Mobile Number <span style="color:#e8624a">*</span></label>
                <input class="input" id="ind_mobile" type="tel" maxlength="10" placeholder="10-digit mobile number" style="margin-bottom:0" oninput="lookupIndividualUser()">
                <div id="ind_user_hint" style="font-size:11.5px;margin-top:5px;min-height:16px"></div>
              </div>

              <div style="font-size:12px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--text-secondary);margin-bottom:12px">Contact View Limits</div>
              <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                <div>
                  <label class="input-label">Per Day</label>
                  <input class="input" id="ind_day" type="number" min="0" placeholder="e.g. 3" style="margin-bottom:0">
                </div>
                <div>
                  <label class="input-label">Per Month</label>
                  <input class="input" id="ind_month" type="number" min="0" placeholder="e.g. 20" style="margin-bottom:0">
                </div>
                <div>
                  <label class="input-label">Total (Lifetime)</label>
                  <input class="input" id="ind_total" type="number" min="0" placeholder="e.g. 100" style="margin-bottom:0">
                </div>
              </div>
              <div style="font-size:11px;color:var(--text-secondary);margin-top:8px">Enter 0 or leave blank for unlimited</div>
            </div>

            <button class="btn btn-primary" style="width:100%" onclick="saveIndividualRestriction()">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
              Save Individual Restriction
            </button>

            <!-- Individual restrictions list -->
            <div style="margin-top:20px">
              <div style="font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:10px;text-transform:uppercase;letter-spacing:.7px;display:flex;align-items:center;justify-content:space-between">
                <span>Saved Individual Restrictions</span>
                <span class="badge badge-gray" id="indRestCount">0</span>
              </div>
              <div id="indRestrictionList" style="display:flex;flex-direction:column;gap:8px;max-height:240px;overflow-y:auto"></div>
            </div>
          </div>

        </div>
      </div>

      <!-- ══ ADMIN ACCOUNTS PANEL ══ -->
      <div class="stab-panel" id="adminPanel">
        <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
          <span class="card-title">Staff Login Credentials</span>
          <button class="btn btn-primary" onclick="openAddAdmin()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Admin
          </button>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th><th>Name</th><th>Username</th><th>Mobile</th>
                <th>Role</th><th>Password</th><th>Status</th><th>Created</th><th>Actions</th>
              </tr>
            </thead>
            <tbody id="adminTable"></tbody>
          </table>
        </div>
      </div>

      <!-- ══ ROLE DETAILS PANEL ══ -->
      <div class="stab-panel" id="roleDetailsPanel">
        <!-- Role selector bar -->
        <div class="role-selector-bar">
          <span style="font-size:13px;font-weight:600;color:var(--text-secondary);margin-right:4px">Select Role:</span>
          <button class="role-tab active" id="rtab_super"   onclick="selectRoleTab('super',this)">👑 Super Admin</button>
          <button class="role-tab"        id="rtab_manager" onclick="selectRoleTab('manager',this)">🛡 Manager</button>
          <button class="role-tab"        id="rtab_staff"   onclick="selectRoleTab('staff',this)">👤 Staff</button>
          <div style="margin-left:auto;display:flex;gap:8px">
            <button class="btn btn-outline btn-sm" onclick="selectAllPerms()">✓ Select All</button>
            <button class="btn btn-outline btn-sm" onclick="clearAllPerms()">✕ Clear All</button>
            <button class="btn btn-primary btn-sm" onclick="saveRolePerms()">💾 Save Permissions</button>
          </div>
        </div>

        <div id="roleDetailsNote" style="padding:10px 20px;font-size:13px;background:#f0fdf4;color:#15803d;border-bottom:1px solid var(--border);display:none">
          👑 Super Admin has full access to all menus and actions — permissions cannot be restricted.
        </div>

        <!-- Sidebar Menu Access -->
        <div style="padding:14px 22px 0;font-size:13px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--border);padding-bottom:10px">
          <span style="font-size:16px">📋</span> Sidebar Menu Access
          <span style="font-size:11px;font-weight:400;color:var(--text-secondary);margin-left:8px">Control which pages this role can see</span>
        </div>
        <div style="padding:18px 22px;overflow-y:auto" id="permGrid"></div>

        <!-- Action Controls -->
        <div style="padding:14px 22px 0;font-size:13px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--border);border-top:1px solid var(--border);padding-bottom:10px">
          <span style="font-size:16px">🎛️</span> Action Controls
          <span style="font-size:11px;font-weight:400;color:var(--text-secondary);margin-left:8px">Control what actions this role can perform</span>
        </div>
        <div style="padding:18px 22px;overflow-y:auto" id="actionPermGrid"></div>

        <!-- Save bar -->
        <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px;background:#faf9f7">
          <button class="btn btn-outline" onclick="resetRolePerms()">Reset to Default</button>
          <button class="btn btn-primary" onclick="saveRolePerms()">💾 Save Permissions</button>
        </div>
      </div>

      <!-- ══ ADMIN LOG PANEL ══ -->
      <div class="stab-panel" id="adminLogPanel">
        <!-- Header -->
        <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
          <div>
            <span class="card-title">📋 Admin Activity Log</span>
            <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">All admin actions — login, logout and every data change</div>
          </div>
          <div style="display:flex;gap:8px;align-items:center">
            <span class="badge badge-gray" id="adminLogCount">0 entries</span>
            <button class="btn btn-outline btn-sm" onclick="exportCSV('adminLog')">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Export CSV
            </button>
            <button class="btn btn-danger btn-sm" onclick="clearAdminLog()" style="font-size:11.5px">🗑 Clear Log</button>
          </div>
        </div>

        <!-- Filters -->
        <div class="search-bar" style="padding:12px 16px 0;margin-bottom:0">
          <input class="search-input" id="alSearch" placeholder="Search by admin name, action or detail…" oninput="renderAdminLog()">
          <select class="filter-select" id="alTypeFilter" onchange="renderAdminLog()">
            <option value="">All Types</option>
            <option value="login">Login / Logout</option>
            <option value="profile">Profile</option>
            <option value="bill">Billing</option>
            <option value="followup">Follow-up</option>
            <option value="admin">Admin Account</option>
            <option value="plan">Plan</option>
            <option value="setting">Setting</option>
            <option value="expired">Expired</option>
            <option value="story">Story</option>
            <option value="ban">Ban / Unban</option>
          </select>
          <input class="filter-select" id="alDateFrom" type="date" title="From date" onchange="renderAdminLog()" style="font-size:12px">
          <input class="filter-select" id="alDateTo"   type="date" title="To date"   onchange="renderAdminLog()" style="font-size:12px">
          <button class="btn btn-ghost btn-sm" onclick="clearAlFilters()" title="Clear">✕ Clear</button>
        </div>

        <!-- Table -->
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Admin</th>
                <th>Role</th>
                <th>Action</th>
                <th>Detail</th>
                <th>Type</th>
                <th>Date &amp; Time</th>
              </tr>
            </thead>
            <tbody id="adminLogTable"></tbody>
          </table>
        </div>
      </div>

      <!-- ══ MOBILE CHANGE REQUESTS PANEL ══ -->
      <div class="stab-panel" id="mobileReqPanel">
        <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
          <div>
            <span class="card-title">📱 Member Mobile Number Change Requests</span>
            <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">Review, approve or reject number change requests submitted by users</div>
          </div>
          <div style="display:flex;gap:8px;align-items:center">
            <span class="badge badge-amber" id="mobileReqCount">0 pending</span>
            <button class="btn btn-ghost btn-sm" onclick="renderMobileReqs()">🔄 Refresh</button>
          </div>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th><th>Old Mobile</th><th>New Mobile</th><th>Member</th>
                <th>Reason</th><th>Requested On</th><th>OTP Verified</th>
                <th>Status</th><th>Actions</th>
              </tr>
            </thead>
            <tbody id="mobileReqTable"></tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

      <!-- ══ PAYMENT OPTIONS PANEL ══ -->
      <div class="stab-panel" id="paymentPanel">

        <!-- Header -->
        <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:#faf9f7">
          <div>
            <span class="card-title">💳 Payment Options</span>
            <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">Payment methods shown to members for registration fee payment</div>
          </div>
          <div style="display:flex;gap:8px;align-items:center;">
            <span class="badge badge-gray" id="payOptCount">0 options</span>
            <button class="btn btn-primary btn-sm" onclick="openAddPaymentOption()">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Add Payment Option
            </button>
          </div>
        </div>

        <div style="display:flex;flex-direction:column">

          <!-- TOP: FORM -->
          <div style="border-bottom:1px solid var(--border);padding:20px 22px;background:#faf9f7">
            <div style="font-weight:700;font-size:13.5px;margin-bottom:16px;display:flex;align-items:center;gap:7px">
              <span id="payFormIcon">➕</span>
              <span id="payFormTitle">Add Payment Option</span>
            </div>

            <!-- Method Type -->
            <div class="form-row">
              <label class="input-label">Payment Method <span style="color:#e8624a">*</span></label>
              <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px" id="payMethodGrid">
                <div class="pay-method-card" id="pm_qr_lbl" onclick="selectPayMethod('qr')" style="cursor:pointer">
                  <div class="pay-method-inner">
                    <span style="font-size:22px">🔳</span>
                    <span style="font-weight:600;font-size:13px">QR Code</span>
                    <span style="font-size:11px;color:var(--text-secondary)">Scan to pay</span>
                  </div>
                </div>
                <div class="pay-method-card" id="pm_upi_lbl" onclick="selectPayMethod('upi')" style="cursor:pointer">
                  <div class="pay-method-inner">
                    <span style="font-size:22px">📲</span>
                    <span style="font-weight:600;font-size:13px">UPI ID</span>
                    <span style="font-size:11px;color:var(--text-secondary)">name@bank</span>
                  </div>
                </div>
                <div class="pay-method-card" id="pm_bank_lbl" onclick="selectPayMethod('bank')" style="cursor:pointer">
                  <div class="pay-method-inner">
                    <span style="font-size:22px">🏦</span>
                    <span style="font-weight:600;font-size:13px">Bank Details</span>
                    <span style="font-size:11px;color:var(--text-secondary)">NEFT / IMPS</span>
                  </div>
                </div>
                <div class="pay-method-card" id="pm_mob_lbl" onclick="selectPayMethod('mobile')" style="cursor:pointer">
                  <div class="pay-method-inner">
                    <span style="font-size:22px">📱</span>
                    <span style="font-weight:600;font-size:13px">UPI Mobile</span>
                    <span style="font-size:11px;color:var(--text-secondary)">Pay to number</span>
                  </div>
                </div>
              </div>
              <!-- Hidden selected method tracker -->
              <input type="hidden" id="pay_selected_method" value="">
            </div>

            <!-- Display Label + Status row -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
              <div class="form-row">
                <label class="input-label">Display Label <span style="color:#e8624a">*</span></label>
                <input class="input" id="pay_label" placeholder="e.g. Scan & Pay, GPay, HDFC Savings…">
              </div>
              <div class="form-row">
                <label class="input-label">Instructions / Notes (optional)</label>
                <input class="input" id="pay_notes" placeholder="e.g. Add CP ID in payment remarks…">
              </div>
              <div class="form-row">
                <label class="input-label">Status</label>
                <select class="input" id="pay_status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>

            <!-- ── QR CODE fields ── -->
            <div id="pay_qr_fields" style="display:none">
              <div class="form-row">
                <label class="input-label">QR Code Image <span style="color:#e8624a">*</span></label>
                <div style="display:flex;gap:10px;margin-bottom:8px">
                  <button type="button" class="btn btn-outline btn-sm" id="qrTabUpload" onclick="switchQrTab('upload')" style="font-size:12px;padding:6px 14px;border-radius:6px;font-weight:600;background:var(--accent);color:#fff;border-color:var(--accent)">Upload Image</button>
                  <button type="button" class="btn btn-outline btn-sm" id="qrTabUrl" onclick="switchQrTab('url')" style="font-size:12px;padding:6px 14px;border-radius:6px;font-weight:600">Paste URL</button>
                </div>
                <!-- Upload tab -->
                <div id="qr_upload_tab">
                  <div id="pay_qr_dropzone" style="border:2px dashed var(--border);border-radius:10px;padding:24px;text-align:center;cursor:pointer;background:#faf9f7;transition:all .2s" onclick="document.getElementById('pay_qr_file').click()" ondragover="event.preventDefault();this.style.borderColor='var(--accent)';this.style.background='#fff5f3'" ondragleave="this.style.borderColor='var(--border)';this.style.background='#faf9f7'" ondrop="event.preventDefault();this.style.borderColor='var(--border)';this.style.background='#faf9f7';handleQrDrop(event)">
                    <div style="font-size:32px;margin-bottom:8px">📤</div>
                    <div style="font-weight:600;font-size:13.5px;color:var(--text-primary)">Click to upload or drag & drop</div>
                    <div style="font-size:11.5px;color:var(--text-secondary);margin-top:4px">PNG, JPG, WEBP — Max 5MB</div>
                  </div>
                  <input type="file" id="pay_qr_file" accept="image/*" style="display:none" onchange="handleQrFileSelect(this)">
                </div>
                <!-- URL tab -->
                <div id="qr_url_tab" style="display:none">
                  <input class="input" id="pay_qr_url" placeholder="https://… image URL" oninput="previewQR(this.value)">
                  <div style="font-size:11.5px;color:var(--text-secondary);margin-top:4px">Paste a direct image URL</div>
                </div>
              </div>
              <div id="pay_qr_preview" style="display:none;margin-top:8px;text-align:center;position:relative">
                <img id="pay_qr_img" src="" alt="QR Preview" style="width:180px;height:180px;object-fit:contain;border:1.5px solid var(--border);border-radius:10px;padding:8px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.06)">
                <div style="font-size:11px;color:var(--text-secondary);margin-top:6px">QR Code Preview</div>
                <button type="button" onclick="removeQrImage()" style="position:absolute;top:0;right:calc(50% - 100px);width:24px;height:24px;border-radius:50%;background:#dc2626;color:#fff;border:none;cursor:pointer;font-size:14px;line-height:1;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.15)">×</button>
              </div>
              <input type="hidden" id="pay_qr_data" value="">
              <div class="form-row" style="margin-top:12px">
                <label class="input-label">UPI ID linked to QR (optional)</label>
                <input class="input" id="pay_qr_upi" placeholder="e.g. matrimony@hdfcbank">
              </div>
            </div>

            <!-- ── UPI ID fields ── -->
            <div id="pay_upi_fields" style="display:none">
              <div class="form-row">
                <label class="input-label">UPI ID <span style="color:#e8624a">*</span></label>
                <input class="input" id="pay_upi_id" placeholder="e.g. matrimony@paytm or name@upi">
              </div>
              <div class="form-row">
                <label class="input-label">UPI App Name (optional)</label>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px">
                  <span class="pay-app-chip" onclick="setUpiApp('GPay')">GPay</span>
                  <span class="pay-app-chip" onclick="setUpiApp('PhonePe')">PhonePe</span>
                  <span class="pay-app-chip" onclick="setUpiApp('Paytm')">Paytm</span>
                  <span class="pay-app-chip" onclick="setUpiApp('BHIM')">BHIM</span>
                  <span class="pay-app-chip" onclick="setUpiApp('Any UPI App')">Any UPI</span>
                </div>
                <input class="input" id="pay_upi_app" placeholder="e.g. GPay, PhonePe…" style="margin-top:8px">
              </div>
            </div>

            <!-- ── BANK DETAILS fields ── -->
            <div id="pay_bank_fields" style="display:none">
              <div class="form-row">
                <label class="input-label">Account Holder Name <span style="color:#e8624a">*</span></label>
                <input class="input" id="pay_bank_name" placeholder="Full name as per bank">
              </div>
              <div class="form-row">
                <label class="input-label">Account Number <span style="color:#e8624a">*</span></label>
                <input class="input" id="pay_bank_acno" placeholder="e.g. 0123456789012">
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
                <div class="form-row">
                  <label class="input-label">IFSC Code <span style="color:#e8624a">*</span></label>
                  <input class="input" id="pay_bank_ifsc" placeholder="e.g. HDFC0001234" style="text-transform:uppercase" oninput="this.value=this.value.toUpperCase()">
                </div>
                <div class="form-row">
                  <label class="input-label">Account Type</label>
                  <select class="input" id="pay_bank_type">
                    <option value="Savings">Savings</option>
                    <option value="Current">Current</option>
                  </select>
                </div>
              </div>
              <div class="form-row">
                <label class="input-label">Bank Name</label>
                <input class="input" id="pay_bank_bank" placeholder="e.g. HDFC Bank, SBI…">
              </div>
              <div class="form-row">
                <label class="input-label">Branch</label>
                <input class="input" id="pay_bank_branch" placeholder="Branch name / city">
              </div>
            </div>

            <!-- ── UPI MOBILE fields ── -->
            <div id="pay_mob_fields" style="display:none">
              <div class="form-row">
                <label class="input-label">Mobile Number <span style="color:#e8624a">*</span></label>
                <input class="input" id="pay_mob_num" type="tel" maxlength="10" placeholder="10-digit mobile number">
              </div>
              <div class="form-row">
                <label class="input-label">Registered Name <span style="color:#e8624a">*</span></label>
                <input class="input" id="pay_mob_holder" placeholder="Name registered on UPI">
              </div>
              <div class="form-row">
                <label class="input-label">Preferred UPI App</label>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px">
                  <span class="pay-app-chip" onclick="setMobApp('GPay')">GPay</span>
                  <span class="pay-app-chip" onclick="setMobApp('PhonePe')">PhonePe</span>
                  <span class="pay-app-chip" onclick="setMobApp('Paytm')">Paytm</span>
                  <span class="pay-app-chip" onclick="setMobApp('BHIM')">BHIM</span>
                </div>
                <input class="input" id="pay_mob_app" placeholder="e.g. GPay, PhonePe…" style="margin-top:8px">
              </div>
            </div>

            <!-- Buttons -->
            <div style="display:flex;gap:8px;margin-top:8px">
              <button class="btn btn-primary" style="flex:1" onclick="savePaymentOption()">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span id="payBtnTxt">Add Option</span>
              </button>
              <button class="btn btn-outline" id="payCancelBtn" onclick="resetPaymentForm()" style="display:none">Cancel</button>
            </div>
          </div>

          <!-- RIGHT: PAYMENT OPTIONS TABLE -->
          <div style="display:flex;flex-direction:column;overflow:hidden">
            <div style="padding:14px 18px;border-bottom:1px solid var(--border);background:#faf9f7">
              <span style="font-weight:600;font-size:13.5px">Active Payment Methods</span>
              <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">Members see these when paying registration fees</div>
            </div>
            <div class="table-wrap" style="flex:1">
              <table>
                <thead>
                  <tr>
                    <th>#</th><th>Method</th><th>Label</th><th>Details</th>
                    <th>Notes</th><th>Status</th><th>Actions</th>
                  </tr>
                </thead>
                <tbody id="paymentOptsTable"></tbody>
              </table>
            </div>
          </div>

        </div>
      </div>

      <!-- ══ POINTS PANEL ══ -->
      <div class="stab-panel" id="pointsPanel">
        <div style="padding:24px;margin-left:260px">

          <!-- Header -->
          <div style="display:flex;align-items:center;gap:14px;margin-bottom:22px">
            <div style="width:44px;height:44px;background:linear-gradient(135deg,#8B0000,#c0392b);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">🪙</div>
            <div>
              <div style="font-size:16px;font-weight:700;color:#1a1a2e">Points System — Chennai Profile</div>
              <div style="font-size:12px;color:#888;margin-top:2px">10 points per contact view. Applies to Chennai Profile only.</div>
            </div>
            <button onclick="loadPtsStats();loadPtsUsers();" style="margin-left:auto;background:#f5f3ef;border:1px solid #e0ddd8;border-radius:8px;padding:7px 16px;font-size:12.5px;cursor:pointer;color:#555;font-weight:500">↻ Refresh</button>
          </div>

          <!-- Stats row -->
          <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px">
            <div style="background:linear-gradient(135deg,#fff5f5,#fff);border:1px solid #fecaca;border-radius:12px;padding:18px 16px">
              <div style="font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#ef4444;margin-bottom:10px">👥 Users</div>
              <div style="font-size:30px;font-weight:800;color:#8B0000;line-height:1" id="aPtsUsers">…</div>
              <div style="font-size:11px;color:#aaa;margin-top:5px">with points</div>
            </div>
            <div style="background:linear-gradient(135deg,#f0fdf4,#fff);border:1px solid #bbf7d0;border-radius:12px;padding:18px 16px">
              <div style="font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#16a34a;margin-bottom:10px">💰 Purchased</div>
              <div style="font-size:30px;font-weight:800;color:#166534;line-height:1" id="aPtsBought">…</div>
              <div style="font-size:11px;color:#aaa;margin-top:5px">total points</div>
            </div>
            <div style="background:linear-gradient(135deg,#eff6ff,#fff);border:1px solid #bfdbfe;border-radius:12px;padding:18px 16px">
              <div style="font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#3b82f6;margin-bottom:10px">📞 Used</div>
              <div style="font-size:30px;font-weight:800;color:#1e40af;line-height:1" id="aPtsUsed">…</div>
              <div style="font-size:11px;color:#aaa;margin-top:5px">total points</div>
            </div>
            <div style="background:linear-gradient(135deg,#fffbeb,#fff);border:1px solid #fde68a;border-radius:12px;padding:18px 16px">
              <div style="font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#d97706;margin-bottom:10px">💎 Balance</div>
              <div style="font-size:30px;font-weight:800;color:#78350f;line-height:1" id="aPtsBalance">…</div>
              <div style="font-size:11px;color:#aaa;margin-top:5px">remaining</div>
            </div>
          </div>

          <!-- Point Packages -->
          <div style="background:#fff;border:1px solid #e8e5e0;border-radius:12px;overflow:hidden;margin-bottom:16px">
            <div style="padding:14px 18px;border-bottom:1px solid #f0ede8;display:flex;align-items:center;gap:10px">
              <span style="background:#fdf4ff;color:#9333ea;border-radius:6px;padding:3px 9px;font-size:12px;font-weight:700">📦</span>
              <span style="font-weight:700;font-size:13px;color:#1a1a2e">Point Packages</span>
              <span style="font-size:11.5px;color:#888;margin-left:4px">— manage buyable packages shown to users</span>
              <button onclick="openAddPtsPackage()" style="margin-left:auto;background:#8B0000;color:#fff;border:none;border-radius:8px;padding:6px 14px;font-size:12px;cursor:pointer;font-weight:600">+ Add Package</button>
            </div>
            <!-- Add/Edit form (hidden) -->
            <div id="pkgForm" style="display:none;padding:18px 20px;background:#faf9f7;border-bottom:1px solid #f0ede8">
              <div style="font-weight:700;font-size:13px;margin-bottom:14px" id="pkgFormTitle">Add Package</div>
              <input type="hidden" id="pkgEditId">
              <div style="display:grid;grid-template-columns:1fr 1.6fr 1fr 100px 100px;gap:10px;margin-bottom:12px">
                <div>
                  <label style="font-size:11.5px;font-weight:600;color:#555;display:block;margin-bottom:4px">Package ID</label>
                  <input id="pkgId" placeholder="e.g. p500" style="border:1px solid #e0ddd8;border-radius:7px;padding:7px 10px;font-size:13px;width:100%;box-sizing:border-box">
                </div>
                <div>
                  <label style="font-size:11.5px;font-weight:600;color:#555;display:block;margin-bottom:4px">Label</label>
                  <input id="pkgLabel" placeholder="e.g. 500 Points" style="border:1px solid #e0ddd8;border-radius:7px;padding:7px 10px;font-size:13px;width:100%;box-sizing:border-box">
                </div>
                <div>
                  <label style="font-size:11.5px;font-weight:600;color:#555;display:block;margin-bottom:4px">Badge</label>
                  <input id="pkgBadge" placeholder="Popular (optional)" style="border:1px solid #e0ddd8;border-radius:7px;padding:7px 10px;font-size:13px;width:100%;box-sizing:border-box">
                </div>
                <div>
                  <label style="font-size:11.5px;font-weight:600;color:#555;display:block;margin-bottom:4px">Points</label>
                  <input id="pkgPoints" type="number" min="1" placeholder="500" style="border:1px solid #e0ddd8;border-radius:7px;padding:7px 10px;font-size:13px;width:100%;box-sizing:border-box">
                </div>
                <div>
                  <label style="font-size:11.5px;font-weight:600;color:#555;display:block;margin-bottom:4px">Price (₹)</label>
                  <input id="pkgPrice" type="number" min="1" step="0.01" placeholder="500" style="border:1px solid #e0ddd8;border-radius:7px;padding:7px 10px;font-size:13px;width:100%;box-sizing:border-box">
                </div>
              </div>
              <div style="display:flex;gap:8px;align-items:center">
                <button onclick="savePtsPackage()" style="background:#166534;color:#fff;border:none;border-radius:8px;padding:8px 20px;font-size:13px;cursor:pointer;font-weight:600">💾 Save</button>
                <button onclick="closePkgForm()" style="background:#f5f3ef;border:1px solid #e0ddd8;border-radius:8px;padding:8px 16px;font-size:13px;cursor:pointer;color:#555">Cancel</button>
                <span id="pkgFormMsg" style="font-size:12.5px;display:none;border-radius:6px;padding:6px 12px;margin-left:4px"></span>
              </div>
            </div>
            <!-- Packages table -->
            <div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;font-size:12.5px">
              <thead><tr style="background:#fafaf9">
                <th style="padding:9px 14px;text-align:left;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">ID</th>
                <th style="padding:9px 10px;text-align:left;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Label</th>
                <th style="padding:9px 10px;text-align:right;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Points</th>
                <th style="padding:9px 10px;text-align:right;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Price (₹)</th>
                <th style="padding:9px 10px;text-align:left;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Badge</th>
                <th style="padding:9px 10px;text-align:center;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Status</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Actions</th>
              </tr></thead>
              <tbody id="pkgTbody"><tr><td colspan="7" style="padding:20px;text-align:center;color:#aaa">Loading…</td></tr></tbody>
            </table></div>
          </div>

          <!-- Two-column: credit/debit form + recent transactions -->
          <div style="display:grid;grid-template-columns:320px 1fr;gap:16px;margin-bottom:16px;align-items:start">

            <!-- Credit/Debit form -->
            <div style="background:#fff;border:1px solid #e8e5e0;border-radius:12px;padding:20px">
              <div style="font-weight:700;font-size:13px;color:#1a1a2e;margin-bottom:16px;display:flex;align-items:center;gap:8px">
                <span style="background:#fef2f2;color:#dc2626;border-radius:6px;padding:3px 9px;font-size:12px">⚡</span>
                Credit / Debit Manually
              </div>
              <div style="display:flex;flex-direction:column;gap:12px">
                <div>
                  <label style="font-size:11.5px;font-weight:600;color:#555;display:block;margin-bottom:5px">Mobile Number</label>
                  <input id="aPtsMobile" type="text" placeholder="10-digit mobile" style="border:1px solid #e0ddd8;border-radius:8px;padding:8px 12px;font-size:13px;width:100%;box-sizing:border-box">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                  <div>
                    <label style="font-size:11.5px;font-weight:600;color:#555;display:block;margin-bottom:5px">Points</label>
                    <input id="aPtsAmount" type="number" min="1" placeholder="e.g. 100" style="border:1px solid #e0ddd8;border-radius:8px;padding:8px 12px;font-size:13px;width:100%;box-sizing:border-box">
                  </div>
                  <div>
                    <label style="font-size:11.5px;font-weight:600;color:#555;display:block;margin-bottom:5px">Note</label>
                    <input id="aPtsNote" type="text" placeholder="Reason" style="border:1px solid #e0ddd8;border-radius:8px;padding:8px 12px;font-size:13px;width:100%;box-sizing:border-box">
                  </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;padding-top:2px">
                  <button onclick="adminPtsCredit()" style="background:#166534;color:#fff;border:none;border-radius:8px;padding:10px;font-size:13px;cursor:pointer;font-weight:600">+ Credit</button>
                  <button onclick="adminPtsDebit()" style="background:#991b1b;color:#fff;border:none;border-radius:8px;padding:10px;font-size:13px;cursor:pointer;font-weight:600">− Debit</button>
                </div>
              </div>
              <div id="aPtsMsgBox" style="margin-top:12px;font-size:12.5px;display:none;border-radius:7px;padding:9px 12px"></div>
            </div>

            <!-- Recent transactions -->
            <div style="background:#fff;border:1px solid #e8e5e0;border-radius:12px;overflow:hidden">
              <div style="padding:14px 18px;border-bottom:1px solid #f0ede8;font-weight:700;font-size:13px;color:#1a1a2e;display:flex;align-items:center;gap:8px">
                <span style="background:#eff6ff;color:#2563eb;border-radius:6px;padding:3px 9px;font-size:12px">📋</span>
                Recent Transactions
              </div>
              <div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;font-size:12.5px">
                <thead><tr style="background:#fafaf9">
                  <th style="padding:9px 14px;text-align:left;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;border-bottom:1px solid #f0ede8">Mobile</th>
                  <th style="padding:9px 10px;text-align:left;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Name</th>
                  <th style="padding:9px 10px;text-align:left;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Type</th>
                  <th style="padding:9px 10px;text-align:right;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Points</th>
                  <th style="padding:9px 10px;text-align:right;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">After</th>
                  <th style="padding:9px 10px;text-align:left;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Note</th>
                  <th style="padding:9px 14px;text-align:left;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8;white-space:nowrap">Date</th>
                </tr></thead>
                <tbody id="aPtsTxnTbody"><tr><td colspan="7" style="padding:24px;text-align:center;color:#aaa">Loading…</td></tr></tbody>
              </table></div>
            </div>
          </div>

          <!-- User balances -->
          <div style="background:#fff;border:1px solid #e8e5e0;border-radius:12px;overflow:hidden">
            <div style="padding:14px 18px;border-bottom:1px solid #f0ede8;display:flex;align-items:center;gap:10px">
              <span style="background:#f0fdf4;color:#16a34a;border-radius:6px;padding:3px 9px;font-size:12px;font-weight:700">👥</span>
              <span style="font-weight:700;font-size:13px;color:#1a1a2e">User Balances</span>
              <input id="aPtsSearch" type="text" placeholder="Search mobile or name…" style="margin-left:auto;border:1px solid #e0ddd8;border-radius:8px;padding:7px 12px;font-size:12px;width:210px" oninput="loadPtsUsers()">
            </div>
            <div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;font-size:12.5px">
              <thead><tr style="background:#fafaf9">
                <th style="padding:9px 14px;text-align:left;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Mobile</th>
                <th style="padding:9px 10px;text-align:left;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Name</th>
                <th style="padding:9px 10px;text-align:right;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Balance</th>
                <th style="padding:9px 10px;text-align:right;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Purchased</th>
                <th style="padding:9px 10px;text-align:right;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8">Used</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f0ede8;white-space:nowrap">Last Update</th>
              </tr></thead>
              <tbody id="aPtsUserTbody"><tr><td colspan="6" style="padding:24px;text-align:center;color:#aaa">Loading…</td></tr></tbody>
            </table></div>
          </div>

        </div>
      </div>

      <!-- ══ USER PANEL CONTROL PANEL ══ -->
      <div class="stab-panel" id="userCtrlPanel">

        <!-- Header -->
        <div style="margin-left:260px;padding:14px 20px 13px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:#faf9f7">
          <div>
            <span class="card-title">📱 User Panel Control</span>
            <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">Control which pages and features are visible to members in the user panel</div>
          </div>
          <div style="display:flex;gap:8px">
            <button class="btn btn-primary btn-sm" onclick="saveUserPanelControl()">💾 Save All Settings</button>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;min-height:500px;margin-left:260px">

          <!-- LEFT: GLOBAL PAGE VISIBILITY -->
          <div style="border-right:1px solid var(--border);padding:20px 22px;overflow-y:auto">
            <div style="font-weight:700;font-size:13px;margin-bottom:4px;display:flex;align-items:center;gap:7px">
              <span style="background:#eff6ff;color:#2563eb;border-radius:7px;padding:4px 8px;font-size:12px">🌐</span>
              Global — All Users
            </div>
            <div style="font-size:12px;color:var(--text-secondary);margin-bottom:18px">These settings apply to every member unless overridden individually below.</div>

            <!-- Pages -->
            <div style="font-size:10.5px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px">Pages / Sections</div>
            <div id="globalPageToggles" style="display:flex;flex-direction:column;gap:8px;margin-bottom:22px"></div>

            <!-- Features -->
            <div style="font-size:10.5px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px;margin-top:4px">Feature Controls</div>
            <div id="globalFeatureToggles" style="display:flex;flex-direction:column;gap:8px;margin-bottom:22px"></div>

            <!-- Save button -->
            <button class="btn btn-primary" style="width:100%" onclick="saveUserPanelControl()">💾 Save Global Settings</button>
          </div>

          <!-- RIGHT: PER-USER OVERRIDES -->
          <div style="display:flex;flex-direction:column;overflow:hidden">
            <!-- Header -->
            <div style="padding:14px 18px 12px;border-bottom:1px solid var(--border);background:#faf9f7;display:flex;align-items:center;justify-content:space-between">
              <div>
                <div style="font-weight:700;font-size:13px;display:flex;align-items:center;gap:7px">
                  <span style="background:#fdf4ff;color:#9333ea;border-radius:7px;padding:4px 8px;font-size:12px">👤</span>
                  Per-User Overrides
                </div>
                <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">Override global settings for a specific member</div>
              </div>
              <button class="btn btn-outline btn-sm" onclick="openAddUserOverride()">+ Add Override</button>
            </div>

            <!-- Add/Edit override form (hidden by default) -->
            <div id="userOverrideForm" style="display:none;padding:16px 18px;background:#faf9f7;border-bottom:1px solid var(--border)">
              <div style="font-weight:600;font-size:12.5px;margin-bottom:12px" id="overrideFormTitle">➕ Add User Override</div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
                <div class="form-row">
                  <label class="input-label">CP ID or Mobile <span style="color:#e8624a">*</span></label>
                  <input class="input" id="uov_cpid" placeholder="e.g. CP1001 or 9876543210" oninput="lookupOverrideUser(this.value)" style="font-size:13px">
                </div>
                <div class="form-row">
                  <label class="input-label">Member Name</label>
                  <input class="input" id="uov_name" placeholder="Auto-filled" readonly style="background:#f0ede8;font-size:13px">
                </div>
              </div>
              <div style="font-size:10.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:8px">Override Page Visibility</div>
              <div id="userOverrideToggles" style="display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-bottom:14px"></div>
              <div style="display:flex;gap:8px">
                <button class="btn btn-primary btn-sm" style="flex:1" onclick="saveUserOverride()">💾 Save Override</button>
                <button class="btn btn-outline btn-sm" onclick="closeUserOverrideForm()">Cancel</button>
              </div>
            </div>

            <!-- Override list table -->
            <div class="table-wrap" style="flex:1">
              <table>
                <thead>
                  <tr>
                    <th>Member</th><th>CP ID</th><th>Overridden Pages</th><th>Set By</th><th>Actions</th>
                  </tr>
                </thead>
                <tbody id="userOverrideTable"></tbody>
              </table>
            </div>
          </div>

        </div>

        <!-- ── USER PANEL CONTROL UPDATE HISTORY ── -->
        <div style="margin-left:260px;border-top:2px solid var(--border)">
          <div style="margin-left:0px;padding:13px 20px 11px;background:#faf9f7;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <div>
              <span style="font-weight:700;font-size:13.5px">📋 User Panel Control — Update History</span>
              <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">Every global save and per-user override is recorded here permanently</div>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
              <span class="badge badge-gray" id="upHistCount">0 records</span>
              <button class="btn btn-ghost btn-sm" onclick="clearUPCtrlHistory()" style="color:#dc2626;border-color:#fca5a5;font-size:11.5px">🗑 Clear</button>
            </div>
          </div>
          <div style="margin-left:0px;display:flex;gap:10px;padding:10px 16px;background:#faf9f7;border-bottom:1px solid var(--border);flex-wrap:wrap">
            <input class="search-input" id="upHistSearch" placeholder="Search by admin or member…" oninput="renderUPCtrlHistory()" style="max-width:260px;flex:1">
            <select class="filter-select" id="upHistTypeFilter" onchange="renderUPCtrlHistory()">
              <option value="">All Actions</option>
              <option value="Global Save">Global Save</option>
              <option value="Override Added">Override Added</option>
              <option value="Override Updated">Override Updated</option>
              <option value="Override Removed">Override Removed</option>
            </select>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr>
                <th>#</th><th>Action</th><th>Scope</th><th>Details</th><th>Changed By</th><th>Recorded At</th>
              </tr></thead>
              <tbody id="upCtrlHistTable"></tbody>
            </table>
          </div>
          <div id="upHistPagination" style="margin-left:260px;padding:9px 18px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:#faf9f7">
            <span style="font-size:12px;color:var(--text-secondary)" id="upHistPageInfo"></span>
            <div style="display:flex;gap:5px" id="upHistPageBtns"></div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- DELETED PROFILES -->
  <div class="section" id="deletedSection">
    <div class="page-header">
      <div>
        <div class="page-title">Deleted Profiles</div>
        <div class="page-subtitle">Archive of removed members &amp; full action history</div>
      </div>
    </div>
    <div class="search-bar" style="margin-bottom:16px">
      <input class="search-input" id="deletedSearch" placeholder="Search by CP ID, name or mobile…" oninput="applyDeletedFilter()">
      <input class="filter-select" id="deletedDateFrom" type="date" title="Deleted From" onchange="applyDeletedFilter()" style="font-size:12px">
      <input class="filter-select" id="deletedDateTo"   type="date" title="Deleted To"   onchange="applyDeletedFilter()" style="font-size:12px">
      <button class="btn btn-ghost btn-sm" onclick="clearDeletedFilters()" title="Clear">✕ Clear</button>
    </div>

    <!-- DELETED ARCHIVE TABLE -->
    <div class="card" style="margin-bottom:24px">
      <div class="card-header">
        <span class="card-title">🗑️ Deleted Archive</span>
        <span class="badge badge-gray" id="deletedCount">0 records</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>CP ID</th><th>Name</th><th>Mobile</th><th>Deleted By</th><th>Reason</th><th>Deleted On</th><th>Restore</th></tr>
          </thead>
          <tbody id="deletedTable"></tbody>
        </table>
      </div>
    </div>

    <!-- ACTION LOG TABLE -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">📋 Action Log</span>
        <span class="badge badge-gray" id="logCount">0 entries</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Timestamp</th><th>Action</th><th>CP ID</th><th>Member</th><th>Admin</th><th>Reason / Note</th></tr>
          </thead>
          <tbody id="actionLogTable"></tbody>
        </table>
      </div>
    </div>
  </div>
  <!-- EXPIRED PROFILES -->
  <div class="section" id="expiredSection">
    <div class="page-header">
      <div>
        <div class="page-title">Expired Profiles</div>
        <div class="page-subtitle">Members whose subscription has expired — restore to reactivate</div>
      </div>
      <button class="btn btn-outline" onclick="exportCSV('expired')">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export CSV
      </button>
    </div>

    <!-- Search + date filter -->
    <div class="search-bar" style="margin-bottom:16px;flex-wrap:wrap">
      <input class="search-input" id="expiredSearch" placeholder="Search by CP ID, name, mobile or plan…" oninput="renderExpired()">
      <select class="filter-select" id="expiredReasonFilter" onchange="renderExpired()">
        <option value="">All Reasons</option>
        <option value="Subscription expired">Subscription Expired</option>
        <option value="Plan expired">Plan Expired</option>
        <option value="Manual">Manual</option>
      </select>
      <input class="filter-select" id="expiredDateFrom" type="date" title="Expired From" onchange="renderExpired()" style="font-size:12px">
      <input class="filter-select" id="expiredDateTo"   type="date" title="Expired To"   onchange="renderExpired()" style="font-size:12px">
      <button class="btn btn-ghost btn-sm" onclick="clearExpiredFilters()" title="Clear">✕ Clear</button>
    </div>

    <!-- Archive table -->
    <div class="card">
      <div class="card-header" style="background:#fff8f0">
        <div>
          <span class="card-title" style="color:#d97706">⏰ Expired Archive</span>
          <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">Undo restores the profile to Pre-approved status</div>
        </div>
        <span class="badge badge-amber" id="expiredCount">0 records</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>CP ID</th>
              <th>Name</th>
              <th>Mobile</th>
              <th>Plan Name</th>
              <th>Expired Date</th>
              <th>Reason</th>
              <th>Expired On</th>
              <th>Actioned By</th>
              <th>Restore</th>
            </tr>
          </thead>
          <tbody id="expiredTable"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- REPORTS -->
  <div class="section" id="reportsSection">
    <div class="page-header">
      <div>
        <div class="page-title">Reports &amp; Analytics</div>
        <div class="page-subtitle">Key metrics and performance overview</div>
      </div>
      <button class="btn btn-outline" onclick="exportCSV('reports')">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export Report
      </button>
    </div>

    <!-- KPIs -->
    <div class="reports-grid" id="reportsGrid">
      <!-- filled by renderReports() -->
    </div>
  </div>

  <!-- NOTIFICATIONS -->
  <div class="section" id="notificationsSection">
    <div class="page-header">
      <div>
        <div class="page-title">Notifications</div>
        <div class="page-subtitle">System alerts and activity feed</div>
      </div>
      <button class="btn btn-outline" onclick="markAllRead()">✓ Mark all read</button>
    </div>
    <div class="card">
      <div class="notif-list" id="notifList"></div>
    </div>
  </div>

  <!-- ACCOUNTS -->
  <div class="section" id="accountsSection">
    <div class="page-header">
      <div>
        <div class="page-title">Accounts</div>
        <div class="page-subtitle">Complete financial records — Income & Expenses</div>
      </div>
    </div>
    <!-- Summary Cards -->
    <div class="stats-row" id="accStatsRow"></div>
    <!-- Filters -->
    <div class="search-bar" style="margin:16px 0">
      <select class="filter-select" id="accTypeFilter" onchange="renderAccounts()">
        <option value="">All Types</option><option value="income">Income</option><option value="expense">Expense</option>
      </select>
      <select class="filter-select" id="accCatFilter" onchange="renderAccounts()">
        <option value="">All Categories</option>
      </select>
      <input class="filter-select" id="accDateFrom" type="date" onchange="renderAccounts()" style="font-size:12px">
      <input class="filter-select" id="accDateTo" type="date" onchange="renderAccounts()" style="font-size:12px">
      <button class="btn btn-ghost btn-sm" onclick="clearAccFilters()">✕ Clear</button>
    </div>
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px">
      <!-- Add Entry Form -->
      <div class="card" style="padding:20px">
        <div style="font-weight:700;font-size:14px;color:var(--text-primary);margin-bottom:16px">Add Entry</div>
        <div class="form-row"><label class="input-label">Date *</label><input class="input" id="acc_date" type="date"></div>
        <div class="form-row"><label class="input-label">Type *</label>
          <div style="display:flex;gap:8px">
            <label style="flex:1;display:flex;align-items:center;gap:6px;padding:8px 12px;border:2px solid #bbf7d0;border-radius:8px;cursor:pointer;background:#f0fdf4" id="acc_type_income_wrap">
              <input type="radio" name="acc_type" value="income" id="acc_type_income" checked onchange="updateAccCategories()"> <span style="font-weight:600;color:#16a34a">💰 Income</span>
            </label>
            <label style="flex:1;display:flex;align-items:center;gap:6px;padding:8px 12px;border:2px solid #fecaca;border-radius:8px;cursor:pointer;background:#fef2f2" id="acc_type_expense_wrap">
              <input type="radio" name="acc_type" value="expense" id="acc_type_expense" onchange="updateAccCategories()"> <span style="font-weight:600;color:#dc2626">💸 Expense</span>
            </label>
          </div>
        </div>
        <div class="form-row"><label class="input-label">Category *</label><select class="input" id="acc_category"><option value="">— Select —</option></select></div>
        <div class="form-row"><label class="input-label">Amount (Rs.) *</label><input class="input" id="acc_amount" type="number" min="0" placeholder="e.g. 2999"></div>
        <div class="form-row"><label class="input-label">Description</label><input class="input" id="acc_desc" placeholder="Details..."></div>
        <div class="form-row"><label class="input-label">Payment Mode</label><select class="input" id="acc_mode"><option value="">— Select —</option><option>Cash</option><option>UPI</option><option>Bank Transfer</option><option>Online</option><option>Cheque</option><option>Card</option></select></div>
        <div class="form-row"><label class="input-label">Reference / Txn ID</label><input class="input" id="acc_ref" placeholder="Optional"></div>
        <div class="form-row"><label class="input-label">Related CP ID / Mobile</label><input class="input" id="acc_related" placeholder="Optional"></div>
        <button class="btn btn-green" onclick="addAccountEntry()" style="width:100%;margin-top:12px;padding:10px">Add Entry</button>
        <button class="btn btn-outline" onclick="syncBillsToAccounts()" style="width:100%;margin-top:8px;padding:10px;font-size:12px">💳 Sync Bills to Accounts</button>
      </div>
      <!-- Entries Table -->
      <div class="card" style="padding:0">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
          <span style="font-weight:700;font-size:14px">All Entries</span>
          <span class="badge badge-gray" id="accCount">0</span>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr>
              <th>#</th><th>Date</th><th>Type</th><th>Category</th><th>Description</th><th>Amount</th><th>Mode</th><th>Ref</th><th>By</th><th>Actions</th>
            </tr></thead>
            <tbody id="accTable"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- USER ORDERS -->
  <div class="section" id="userOrdersSection">
    <div class="page-header">
      <div>
        <div class="page-title">User Orders</div>
        <div class="page-subtitle">Orders placed by users from the Member Portal</div>
      </div>
      <button class="btn btn-outline btn-sm" onclick="exportUserOrders()" style="gap:4px">📥 Export CSV</button>
    </div>
    <div class="search-bar" style="margin-bottom:16px;flex-wrap:wrap">
      <input class="search-input" id="uoSearch" placeholder="Search by mobile, name, CP ID, plan, txn ref..." oninput="renderUserOrders()" style="min-width:200px">
      <select class="filter-select" id="uoStatusFilter" onchange="renderUserOrders()">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
      <select class="filter-select" id="uoPlanFilter" onchange="renderUserOrders()">
        <option value="">All Plans</option>
        <option value="silver">Silver</option>
        <option value="gold">Gold</option>
        <option value="premium">Premium</option>
      </select>
      <input class="filter-select" id="uoDateFrom" type="date" title="From" onchange="renderUserOrders()" style="font-size:12px">
      <input class="filter-select" id="uoDateTo" type="date" title="To" onchange="renderUserOrders()" style="font-size:12px">
      <button class="btn btn-ghost btn-sm" onclick="clearUoFilters()">✕ Clear</button>
      <span class="badge badge-gray" id="uoCount" style="margin-left:auto">0</span>
    </div>
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead><tr>
            <th>#</th><th>Date</th><th>Mobile</th><th>Name</th><th>CP ID</th><th>Plan</th><th>Amount</th><th>Method</th><th>Txn Ref</th><th>Proof</th><th>Status</th><th>Actions</th>
          </tr></thead>
          <tbody id="userOrdersTable"></tbody>
        </table>
      </div>
    </div>
    <!-- Order Archive -->
    <div class="card" style="margin-top:20px">
      <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
        <div>
          <span style="font-weight:700;font-size:14px">Order Archive</span>
          <span style="font-size:12px;color:var(--text-secondary);margin-left:8px">Permanent log — no delete or edit</span>
        </div>
        <span class="badge badge-gray" id="orderArchiveCount">0</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr>
            <th>#</th><th>Date</th><th>Order ID</th><th>Mobile</th><th>Name</th><th>CP ID</th><th>Plan</th><th>Amount</th><th>Method</th><th>Action</th><th>Action By</th><th>Note</th>
          </tr></thead>
          <tbody id="orderArchiveTable"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- DIRECT LOGIN -->
  <div class="section" id="directLoginSection">
    <div class="page-header">
      <div>
        <div class="page-title">Direct Login</div>
        <div class="page-subtitle">Allow users to access user panel without OTP verification</div>
      </div>
      <button class="btn btn-outline btn-sm" onclick="exportDirectLogins()" style="gap:4px">📥 Export CSV</button>
    </div>
    <div class="search-bar" style="margin-bottom:16px;flex-wrap:wrap">
      <input class="search-input" id="dlSearch" placeholder="Search by mobile, name, CP ID, created by..." oninput="renderDirectLogins()" style="min-width:200px">
      <select class="filter-select" id="dlStatusFilter" onchange="renderDirectLogins()">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
      <input class="filter-select" id="dlDateFrom" type="date" title="From" onchange="renderDirectLogins()" style="font-size:12px">
      <input class="filter-select" id="dlDateTo" type="date" title="To" onchange="renderDirectLogins()" style="font-size:12px">
      <button class="btn btn-ghost btn-sm" onclick="clearDlFilters()">✕ Clear</button>
    </div>
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px">
      <!-- Add Form -->
      <div class="card" style="padding:20px">
        <div style="font-weight:700;font-size:14px;color:var(--text-primary);margin-bottom:16px">Add Direct Login</div>
        <div class="form-row"><label class="input-label">Mobile Number *</label><input class="input" id="dl_mobile" placeholder="10-digit mobile" maxlength="10" type="tel"></div>
        <div id="dl_profile_info" style="display:none;margin:8px 0;padding:10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:12px"></div>
        <button class="btn btn-green" onclick="addDirectLogin()" style="width:100%;margin-top:12px;padding:10px">Add Direct Login</button>
      </div>
      <!-- Table -->
      <div class="card" style="padding:0">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
          <span style="font-weight:700;font-size:14px">Direct Login Users</span>
          <span class="badge badge-gray" id="dlCount">0</span>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr>
              <th>#</th><th>Mobile</th><th>Name</th><th>CP ID</th><th>Status</th><th>Created By</th><th>Created</th><th>Last Used</th><th>Uses</th><th>Actions</th>
            </tr></thead>
            <tbody id="dlTable"></tbody>
          </table>
        </div>
      </div>
    </div>
    <!-- Archive Log -->
    <div class="card" style="margin-top:20px">
      <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
        <div>
          <span style="font-weight:700;font-size:14px">Direct Login Archive</span>
          <span style="font-size:12px;color:var(--text-secondary);margin-left:8px">Permanent log — no delete or edit</span>
        </div>
        <span class="badge badge-gray" id="dlLogCount">0</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr>
            <th>#</th><th>Date & Time</th><th>Mobile</th><th>Name</th><th>CP ID</th><th>Action</th><th>Action By</th>
          </tr></thead>
          <tbody id="dlLogTable"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- PROFILE REPORTS -->
  <div class="section" id="profileReportsSection">
    <div class="page-header">
      <div>
        <div class="page-title">Profile Reports</div>
        <div class="page-subtitle">User-reported profiles — Already Married, Fraud, Misinformation</div>
      </div>
      <button class="btn btn-outline btn-sm" onclick="exportProfileReports()" style="gap:4px">📥 Export CSV</button>
    </div>
    <div class="search-bar" style="margin-bottom:16px;flex-wrap:wrap">
      <input class="search-input" id="prSearch" placeholder="Search by name, CP ID, mobile, reporter..." oninput="renderProfileReports()" style="min-width:200px">
      <select class="filter-select" id="prReasonFilter" onchange="renderProfileReports()">
        <option value="">All Reasons</option>
        <option value="already_married">Already Married</option>
        <option value="fraud">Fraud</option>
        <option value="misinformation">Misinformation</option>
      </select>
      <select class="filter-select" id="prStatusFilter" onchange="renderProfileReports()">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="resolved">Resolved</option>
        <option value="dismissed">Dismissed</option>
      </select>
      <input class="filter-select" id="prDateFrom" type="date" title="From" onchange="renderProfileReports()" style="font-size:12px">
      <input class="filter-select" id="prDateTo" type="date" title="To" onchange="renderProfileReports()" style="font-size:12px">
      <button class="btn btn-ghost btn-sm" onclick="clearPrFilters()">✕ Clear</button>
      <span class="badge badge-gray" id="prCount" style="margin-left:auto">0</span>
    </div>
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Reported Date</th>
              <th>Profile ID</th>
              <th>Profile Name</th>
              <th>Profile Mobile</th>
              <th>Reported By</th>
              <th>Reason</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="reportsTable"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- RESOLVE REPORT POPUP -->
  <div class="modal-overlay" id="resolvePopupOverlay">
    <div class="modal" style="max-width:420px">
      <div class="modal-header">
        <span class="modal-title" id="resolvePopupTitle">Resolve Report</span>
        <button class="modal-close" onclick="closeModal('resolvePopupOverlay')">×</button>
      </div>
      <div style="padding:20px">
        <div id="resolvePopupInfo" style="font-size:13px;color:#6b7280;margin-bottom:16px;background:#f9fafb;padding:10px;border-radius:8px;text-align:center;font-weight:600"></div>
        <label style="font-size:12px;font-weight:600;color:#374151;margin-bottom:8px;display:block">Resolution Reason</label>
        <select id="resolveReason" class="input" style="margin-bottom:16px">
          <option value="">— Select Reason —</option>
          <option value="Wrongly Reported">Wrongly Reported — Profile is valid</option>
          <option value="Confirmed & Deleted">Confirmed — Profile deleted</option>
          <option value="Confirmed & Suspended">Confirmed — Profile suspended</option>
          <option value="Under Investigation">Under Investigation</option>
          <option value="Duplicate Report">Duplicate Report</option>
          <option value="Contacted & Resolved">Contacted & Resolved</option>
        </select>
        <div style="display:flex;gap:10px">
          <button class="btn btn-outline" onclick="closeModal('resolvePopupOverlay')" style="flex:1;padding:10px">Cancel</button>
          <button class="btn btn-green" onclick="submitResolve()" style="flex:1;padding:10px">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <!-- SUCCESS STORIES -->
  <div class="section" id="storiesSection">
    <div class="page-header">
      <div>
        <div class="page-title">Marriage Success Stories</div>
        <div class="page-subtitle">Couples who found their match</div>
      </div>
      <button class="btn btn-primary" onclick="openAddStory()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Story
      </button>
    </div>
    <div class="stories-grid" id="storiesGrid"></div>
  </div>

  <!-- UPDATE HISTORY -->
  <div class="section" id="updateHistorySection">
    <div class="page-header">
      <div>
        <div class="page-title">Update History</div>
        <div class="page-subtitle">Every edit and change is recorded permanently — no delete or edit</div>
      </div>
      <button class="btn btn-outline btn-sm" onclick="exportUpdateHistory()" style="gap:4px">📥 Export CSV</button>
    </div>
    <div class="search-bar" style="margin-bottom:16px;flex-wrap:wrap">
      <input class="search-input" id="historySearch" placeholder="Search by entity, field, value, changed by..." oninput="renderUpdateHistory()" style="min-width:200px">
      <select class="filter-select" id="historyFilter" onchange="renderUpdateHistory()">
        <option value="">All Types</option>
        <option value="profile">Profiles</option>
        <option value="admin">Admin Accounts</option>
        <option value="plan">Subscription Plans</option>
        <option value="payment_option">Payment Options</option>
        <option value="role_permission">Role Permissions</option>
        <option value="bill">Bills</option>
        <option value="follow_up">Follow-ups</option>
        <option value="story">Stories</option>
      </select>
      <select class="filter-select" id="historyActionFilter" onchange="renderUpdateHistory()">
        <option value="">All Actions</option>
        <option value="created">Created</option>
        <option value="updated">Updated</option>
        <option value="deleted">Deleted</option>
        <option value="approved">Approved</option>
      </select>
      <input class="filter-select" id="historyDateFrom" type="date" title="From" onchange="renderUpdateHistory()" style="font-size:12px">
      <input class="filter-select" id="historyDateTo" type="date" title="To" onchange="renderUpdateHistory()" style="font-size:12px">
      <button class="btn btn-ghost btn-sm" onclick="clearHistoryFilters()">✕ Clear</button>
      <button class="btn btn-outline btn-sm" onclick="loadUpdateHistory()">Refresh</button>
      <span class="badge badge-gray" id="historyCount" style="margin-left:auto">0</span>
    </div>
    <div class="card" style="overflow:hidden">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Date & Time</th>
              <th>Type</th>
              <th>Entity</th>
              <th>Action</th>
              <th>Field</th>
              <th>Old Value</th>
              <th>New Value</th>
              <th>Changed By</th>
              <th>Role</th>
            </tr>
          </thead>
          <tbody id="updateHistoryTbody">
            <tr><td colspan="10" style="text-align:center;padding:40px;color:var(--text-secondary)">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- OTP LOGS -->
  <div class="section" id="otpSection">
    <div class="page-header">
      <div>
        <div class="page-title">OTP Logs</div>
        <div class="page-subtitle">User login OTP requests, verification status &amp; session history</div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <button class="btn btn-outline" onclick="exportCSV('otp')">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Export CSV
        </button>
      </div>
    </div>

    <!-- Filter bar -->
    <div class="search-bar" style="margin-bottom:16px">
      <input class="search-input" id="otpSearch" placeholder="Search by mobile, CP ID or name…" oninput="renderOtp()">
      <select class="filter-select" id="otpStatusFilter" onchange="renderOtp()">
        <option value="">All Status</option>
        <option value="web_in">Web In</option>
        <option value="web_out">Web Out</option>
        <option value="otp_request">OTP Request</option>
        <option value="verified">OTP Verified</option>
        <option value="otp_failed">OTP Failed</option>
      </select>
      <select class="filter-select" id="otpBanFilter" onchange="renderOtp()">
        <option value="">All Users</option>
        <option value="active">Active</option>
        <option value="banned">Banned</option>
      </select>
      <input class="filter-select" id="otpDateFrom" type="date" title="OTP Requested From" onchange="renderOtp()" style="font-size:12px">
      <input class="filter-select" id="otpDateTo"   type="date" title="OTP Requested To"   onchange="renderOtp()" style="font-size:12px">
      <button class="btn btn-ghost btn-sm" onclick="clearOtpFilters()" title="Clear">✕ Clear</button>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Sl.No</th>
              <th>Mobile</th>
              <th>CP ID</th>
              <th>Name</th>
              <th>OTP Requested</th>
              <th>Live OTP</th>
              <th>Status</th>
              <th>Last Login</th>
              <th>Login Count</th>
              <th>User Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="otpTable"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ALERTS -->
  <div class="section" id="alertsSection">
    <div class="page-header">
      <div>
        <div class="page-title">🚨 Smart Alerts</div>
        <div class="page-subtitle">Auto-detected suspicious activity and usage violations</div>
      </div>
      <button class="btn btn-outline" onclick="renderAlerts()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
        Refresh
      </button>
    </div>

    <!-- Summary stat chips -->
    <div class="stats-row" id="alertStatsRow" style="margin-bottom:20px"></div>

    <!-- Alert rules config card -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-header" style="background:#fff8f0">
        <span class="card-title" style="color:#d97706">⚙️ Alert Thresholds</span>
        <button class="btn btn-primary btn-sm" onclick="saveAlertThresholds()">Save Thresholds</button>
      </div>
      <div style="padding:16px 20px;display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
        <div>
          <label class="input-label">Max Contact Views / Day <span style="color:#e8624a">*</span></label>
          <input class="input" id="th_contactDay" type="number" min="1" value="10" style="margin-bottom:0">
          <div style="font-size:11px;color:var(--text-secondary);margin-top:4px">Flag users who exceed this</div>
        </div>
        <div>
          <label class="input-label">Max OTP Requests / Day</label>
          <input class="input" id="th_otpDay" type="number" min="1" value="3" style="margin-bottom:0">
          <div style="font-size:11px;color:var(--text-secondary);margin-top:4px">Flag repeated OTP abuse</div>
        </div>
        <div>
          <label class="input-label">Max Profile Views / Day (Free)</label>
          <input class="input" id="th_profileDay" type="number" min="1" value="10" style="margin-bottom:0">
          <div style="font-size:11px;color:var(--text-secondary);margin-top:4px">Free user daily limit</div>
        </div>
      </div>
    </div>

    <!-- Live alert table -->
    <div class="card">
      <div class="card-header" style="background:#fff8f8">
        <span class="card-title" style="color:#dc2626">🚨 Active Alerts</span>
        <span class="badge" style="background:#fee2e2;color:#dc2626" id="alertCount">0 alerts</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Severity</th><th>Mobile</th><th>CP ID</th><th>Name</th>
              <th>Alert Reason</th><th>Value</th><th>Threshold</th><th>Action</th>
            </tr>
          </thead>
          <tbody id="alertTable"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- CONTACT VIEW LOG -->
  <div class="section" id="contactLogSection">
    <div class="page-header">
      <div>
        <div class="page-title">Contact View Log</div>
        <div class="page-subtitle">Every contact reveal — who viewed whose number, when</div>
      </div>
      <button class="btn btn-outline" onclick="exportCSV('contactLog')">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export CSV
      </button>
    </div>
    <div class="search-bar" style="margin-bottom:16px">
      <input class="search-input" id="clSearch" placeholder="Search by viewer mobile, CP ID or viewed CP ID…" oninput="renderContactLog()">
      <input class="filter-select" id="clDateFrom" type="date" title="From" onchange="renderContactLog()" style="font-size:12px">
      <input class="filter-select" id="clDateTo"   type="date" title="To"   onchange="renderContactLog()" style="font-size:12px">
      <button class="btn btn-ghost btn-sm" onclick="clearClFilters()">✕ Clear</button>
    </div>
    <div class="card">
      <div class="card-header">
        <span class="card-title">📞 All Contact Views</span>
        <span class="badge badge-gray" id="clCount">0 records</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Viewer Mobile</th><th>Viewer CP ID</th><th>Viewer Plan</th>
              <th>Viewed Profile</th><th>Viewed CP ID</th><th>Viewed Mobile</th><th>Date &amp; Time</th><th>🔍</th>
            </tr>
          </thead>
          <tbody id="contactLogTable"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- PROFILE VIEW LOG -->
  <div class="section" id="profileViewLogSection">
    <div class="page-header">
      <div>
        <div class="page-title">Profile View Log</div>
        <div class="page-subtitle">Every profile viewed — who viewed whose profile, when</div>
      </div>
    </div>
    <div class="search-bar" style="margin-bottom:16px">
      <input class="search-input" id="pvlSearch" placeholder="Search by viewer mobile, name or CP ID…" oninput="renderProfileViewLog()">
      <input class="filter-select" id="pvlDateFrom" type="date" title="From" onchange="renderProfileViewLog()" style="font-size:12px">
      <input class="filter-select" id="pvlDateTo" type="date" title="To" onchange="renderProfileViewLog()" style="font-size:12px">
      <button class="btn btn-ghost btn-sm" onclick="document.getElementById('pvlSearch').value='';document.getElementById('pvlDateFrom').value='';document.getElementById('pvlDateTo').value='';renderProfileViewLog()">✕ Clear</button>
    </div>
    <div class="card">
      <div class="card-header">
        <span class="card-title">👁 All Profile Views</span>
        <span class="badge badge-gray" id="pvlCount">0 records</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Viewer Mobile</th><th>Viewer Name</th><th>Viewer Plan</th>
              <th>Viewed Profile</th><th>Viewed Name</th><th>Date &amp; Time</th>
            </tr>
          </thead>
          <tbody id="pvlTable"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- USER RESPONSE — per-user activity received (profile views, contact reveals, reports, interest tags) -->
  <div class="section" id="userResponseSection">
    <div class="page-header">
      <div>
        <div class="page-title">User Response</div>
        <div class="page-subtitle">Enter a member's mobile number to see every response received on their profile</div>
      </div>
    </div>
    <div class="search-bar" style="margin-bottom:16px;gap:8px;display:flex;flex-wrap:wrap">
      <input class="search-input" id="urMobile" placeholder="Enter 10-digit mobile or CP ID…" maxlength="20"
             style="flex:1;min-width:220px;font-size:13.5px;font-weight:600"
             oninput="renderUserResponse()">
      <button class="btn btn-primary btn-sm" onclick="renderUserResponse()">Search</button>
      <button class="btn btn-ghost btn-sm" onclick="document.getElementById('urMobile').value='';renderUserResponse();">✕ Clear</button>
    </div>
    <div id="urHeader"></div>
    <div id="urBody" style="display:grid;grid-template-columns:1fr;gap:16px">
      <div class="card">
        <div class="card-header">
          <span class="card-title">👁 Profile Views Received</span>
          <span class="badge badge-gray" id="urProfileViewCount">0</span>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Viewer Mobile</th><th>Viewer CP ID</th><th>Viewer Name</th><th>Plan</th><th>When</th></tr></thead>
            <tbody id="urProfileViewTable"></tbody>
          </table>
        </div>
      </div>
      <div class="card">
        <div class="card-header">
          <span class="card-title">📞 Contact Views Received</span>
          <span class="badge badge-gray" id="urContactViewCount">0</span>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Viewer Mobile</th><th>Viewer CP ID</th><th>Viewer Name</th><th>Plan</th><th>When</th></tr></thead>
            <tbody id="urContactViewTable"></tbody>
          </table>
        </div>
      </div>
      <div class="card">
        <div class="card-header">
          <span class="card-title">⚠️ Reports Against This Profile</span>
          <span class="badge badge-gray" id="urReportCount">0</span>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Reporter Mobile</th><th>Reporter CP ID</th><th>Reason</th><th>Status</th><th>When</th></tr></thead>
            <tbody id="urReportTable"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- USER ACTIVITY — outgoing actions a given member has taken (profiles they viewed, contacts they revealed, reports they filed) -->
  <div class="section" id="userActivitySection">
    <div class="page-header">
      <div>
        <div class="page-title">User Activity</div>
        <div class="page-subtitle">Enter a member's mobile number to see every outgoing action they've taken on other profiles</div>
      </div>
    </div>
    <div class="search-bar" style="margin-bottom:16px;gap:8px;display:flex;flex-wrap:wrap">
      <input class="search-input" id="uaMobile" placeholder="Enter 10-digit mobile or CP ID…" maxlength="20"
             style="flex:1;min-width:220px;font-size:13.5px;font-weight:600"
             oninput="renderUserActivity()">
      <button class="btn btn-primary btn-sm" onclick="renderUserActivity()">Search</button>
      <button class="btn btn-ghost btn-sm" onclick="document.getElementById('uaMobile').value='';renderUserActivity();">✕ Clear</button>
    </div>
    <div id="uaHeader"></div>
    <div id="uaBody" style="display:grid;grid-template-columns:1fr;gap:16px">
      <div class="card">
        <div class="card-header">
          <span class="card-title">👁 Profiles Viewed</span>
          <span class="badge badge-gray" id="uaProfileViewCount">0</span>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Viewed CP ID</th><th>Viewed Name</th><th>When</th></tr></thead>
            <tbody id="uaProfileViewTable"></tbody>
          </table>
        </div>
      </div>
      <div class="card">
        <div class="card-header">
          <span class="card-title">📞 Contacts Revealed</span>
          <span class="badge badge-gray" id="uaContactViewCount">0</span>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Viewed CP ID</th><th>Viewed Name</th><th>Viewed Mobile</th><th>When</th></tr></thead>
            <tbody id="uaContactViewTable"></tbody>
          </table>
        </div>
      </div>
      <div class="card">
        <div class="card-header">
          <span class="card-title">⚠️ Reports Filed</span>
          <span class="badge badge-gray" id="uaReportCount">0</span>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Reported CP ID</th><th>Reported Name</th><th>Reason</th><th>Status</th><th>When</th></tr></thead>
            <tbody id="uaReportTable"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- MESSAGES -->
  <div class="section" id="messagesSection">
    <div class="page-header">
      <div>
        <div class="page-title">Messages</div>
        <div class="page-subtitle">Contact form submissions from users</div>
      </div>
    </div>
    <div class="search-bar" style="margin-bottom:16px">
      <input class="search-input" id="msgSearch" placeholder="Search by name, phone, message..." oninput="renderMessages()">
      <select class="filter-select" id="msgStatusFilter" onchange="renderMessages()">
        <option value="">All Status</option><option value="new">New</option><option value="read">Read</option><option value="replied">Replied</option><option value="closed">Closed</option>
      </select>
      <button class="btn btn-ghost btn-sm" onclick="document.getElementById('msgSearch').value='';document.getElementById('msgStatusFilter').value='';renderMessages()">✕ Clear</button>
    </div>
    <div id="msgList" style="display:flex;flex-direction:column;gap:12px"></div>
  </div>

  <!-- INTEREST PATTERN ANALYSIS -->
  <div class="section" id="interestSection">
    <div class="page-header">
      <div>
        <div class="page-title">Interest Pattern Analysis</div>
        <div class="page-subtitle">AI-scored behavioural profiling — what each user is really looking for</div>
      </div>
      <div style="display:flex;gap:8px;align-items:center">
        <button class="btn btn-outline" onclick="exportCSV('interest')">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Export CSV
        </button>
        <button class="btn btn-primary" onclick="renderInterest()">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
          Re-analyse
        </button>
      </div>
    </div>

    <!-- Signal weight legend -->
    <div class="card" style="margin-bottom:18px">
      <div class="card-header" style="background:#faf7ff">
        <span class="card-title" style="color:#7c3aed">🧠 Scoring Engine — Signal Weights</span>
      </div>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;padding:0">
        <div style="padding:14px 18px;border-right:1px solid var(--border);text-align:center">
          <div style="font-size:22px;font-weight:800;color:#dc2626">40%</div>
          <div style="font-size:12px;font-weight:600;margin:3px 0">Time Spent</div>
          <div style="font-size:11px;color:var(--text-secondary)">Seconds on profile page</div>
        </div>
        <div style="padding:14px 18px;border-right:1px solid var(--border);text-align:center">
          <div style="font-size:22px;font-weight:800;color:#d97706">25%</div>
          <div style="font-size:12px;font-weight:600;margin:3px 0">Scroll Depth</div>
          <div style="font-size:11px;color:var(--text-secondary)">How far they read (%)</div>
        </div>
        <div style="padding:14px 18px;border-right:1px solid var(--border);text-align:center">
          <div style="font-size:22px;font-weight:800;color:#16a34a">25%</div>
          <div style="font-size:12px;font-weight:600;margin:3px 0">Contact Viewed</div>
          <div style="font-size:11px;color:var(--text-secondary)">Requested mobile number</div>
        </div>
        <div style="padding:14px 18px;text-align:center">
          <div style="font-size:22px;font-weight:800;color:#2563eb">10%</div>
          <div style="font-size:12px;font-weight:600;margin:3px 0">Repeat Views</div>
          <div style="font-size:11px;color:var(--text-secondary)">Revisited same profile</div>
        </div>
      </div>
    </div>

    <!-- Search / filter -->
    <div class="search-bar" style="margin-bottom:16px">
      <input class="search-input" id="interestSearch" placeholder="Search by mobile number…" oninput="renderInterest()">
      <select class="filter-select" id="interestMinViews" onchange="renderInterest()">
        <option value="1">Min 1 view</option>
        <option value="3" selected>Min 3 views</option>
        <option value="5">Min 5 views</option>
        <option value="10">Min 10 views</option>
      </select>
      <button class="btn btn-ghost btn-sm" onclick="document.getElementById('interestSearch').value='';renderInterest()">✕ Clear</button>
    </div>

    <!-- Main results table -->
    <div class="card">
      <div class="card-header" style="background:#faf7ff">
        <div>
          <span class="card-title" style="color:#7c3aed">📊 User Interest Profiles</span>
          <div style="font-size:11.5px;color:var(--text-secondary);margin-top:2px">Patterns extracted from time-spent, scroll depth, contact views &amp; repeat behaviour</div>
        </div>
        <span class="badge" style="background:#ede9fe;color:#7c3aed" id="interestCount">0 users</span>
      </div>
      <div class="table-wrap">
        <table id="interestTable">
          <thead>
            <tr>
              <th>#</th>
              <th>User Mobile</th>
              <th>Name</th>
              <th>Views / Contacts</th>
              <th style="min-width:240px">🥇 Interest Pattern 1</th>
              <th style="min-width:240px">🥈 Interest Pattern 2</th>
              <th style="min-width:240px">🥉 Interest Pattern 3</th>
              <th>Session Style</th>
            </tr>
          </thead>
          <tbody id="interestTableBody"></tbody>
        </table>
      </div>
    </div>

    <!-- Detail drawer — shown when row is clicked -->
    <div id="interestDrawer" style="display:none;margin-top:18px">
      <div class="card">
        <div class="card-header" style="background:#faf7ff">
          <span class="card-title" style="color:#7c3aed">🔍 Deep Profile — <span id="drawerMobile"></span></span>
          <button class="btn btn-ghost btn-sm" onclick="document.getElementById('interestDrawer').style.display='none'">✕ Close</button>
        </div>
        <div id="drawerBody" style="padding:20px"></div>
      </div>
    </div>
  </div>

</div>
<!-- UNDO CLOSED FOLLOW-UP MODAL -->
<div class="modal-overlay" id="undoFollowOverlay">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">↩ Reopen Follow-up</span>
      <button class="modal-close" onclick="closeModal('undoFollowOverlay')">×</button>
    </div>
    <div style="background:#f0fdf4;border-radius:8px;padding:11px 14px;margin-bottom:16px;font-size:13px;color:#15803d;display:flex;align-items:center;gap:8px">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
      Set a new date to move this back to active follow-ups.
    </div>
    <div class="form-row">
      <label class="input-label">CP ID / Member</label>
      <input class="input" id="uf_member" readonly style="background:#f3f4f6;font-weight:500">
    </div>
    <div class="form-row">
      <label class="input-label">New Follow-up Date <span style="color:#e8624a">*</span></label>
      <input class="input" id="uf_date" type="date">
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('undoFollowOverlay')">Cancel</button>
      <button class="btn btn-green" onclick="confirmUndoFollow()">↩ Reopen Follow-up</button>
    </div>
  </div>
</div>

<!-- EXPIRE PROFILE MODAL -->
<div class="modal-overlay" id="expireOverlay">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">⏰ Mark Profile as Expired</span>
      <button class="modal-close" onclick="closeModal('expireOverlay')">×</button>
    </div>
    <div style="background:#fff8f0;border-radius:8px;padding:11px 14px;margin-bottom:16px;font-size:13px;color:#92400e;display:flex;align-items:center;gap:8px">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      This profile will be moved to the Expired archive. You can restore it anytime.
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
      <div class="form-row">
        <label class="input-label">CP ID</label>
        <input class="input" id="exp_cpid" readonly style="background:#f3f4f6;font-weight:700">
      </div>
      <div class="form-row">
        <label class="input-label">Mobile</label>
        <input class="input" id="exp_mobile" readonly style="background:#f3f4f6">
      </div>
    </div>
    <div class="form-row">
      <label class="input-label">Member Name</label>
      <input class="input" id="exp_name" readonly style="background:#f3f4f6">
    </div>
    <div class="form-row">
      <label class="input-label">Plan Name</label>
      <input class="input" id="exp_plan" readonly style="background:#f3f4f6">
    </div>
    <div class="form-row">
      <label class="input-label">Expiry Date</label>
      <input class="input" id="exp_date" readonly style="background:#f3f4f6">
    </div>
    <div class="form-row">
      <label class="input-label">Reason for Expiry <span style="color:#e8624a">*</span></label>
      <select class="input" id="exp_reason">
        <option value="">— Select Reason —</option>
        <option value="Plan expired">Plan expired</option>
        <option value="Non-renewal">Non-renewal</option>
        <option value="Payment failed">Payment failed</option>
        <option value="Member inactive">Member inactive</option>
        <option value="Manual expiry by admin">Manual expiry by admin</option>
        <option value="Others">Others</option>
      </select>
    </div>
    <div class="form-row">
      <label class="input-label">Actioned By</label>
      <input class="input" id="exp_by" readonly style="background:#f0ede8;color:var(--text-primary);font-weight:500">
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('expireOverlay')">Cancel</button>
      <button class="btn" style="background:#d97706;color:#fff;border:none" onclick="confirmExpire()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Mark as Expired
      </button>
    </div>
  </div>
</div>

<!-- ADD STORY MODAL -->
<div class="modal-overlay" id="addStoryOverlay">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">❤️ Add Success Story</span>
      <button class="modal-close" onclick="closeModal('addStoryOverlay')">×</button>
    </div>
    <div class="form-row">
      <label class="input-label">Groom's Name &amp; CP ID</label>
      <input class="input" id="st_groom" placeholder="e.g. Ravi Kumar (CP1002)">
    </div>
    <div class="form-row">
      <label class="input-label">Bride's Name &amp; CP ID</label>
      <input class="input" id="st_bride" placeholder="e.g. Priya Devi (CP1003)">
    </div>
    <div class="form-row">
      <label class="input-label">Marriage Date</label>
      <input class="input" id="st_date" type="date">
    </div>
    <div class="form-row">
      <label class="input-label">Their Quote / Message</label>
      <input class="input" id="st_quote" placeholder="A short message from the couple…">
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('addStoryOverlay')">Cancel</button>
      <button class="btn btn-primary" onclick="saveStory()">Save Story</button>
    </div>
  </div>
</div>

<!-- ADD PROFILE — FULL FORM -->
<datalist id="admin_partner_qual_list">
  <option value="Any"><option value="Any Degree"><option value="Any Bachelor Degree"><option value="Any Masters Degree">
  <option value="B.E"><option value="B.Tech."><option value="B.Sc."><option value="B.Com."><option value="B.A.">
  <option value="BCA"><option value="BBA"><option value="MBA"><option value="MCA"><option value="M.E">
  <option value="M.Tech."><option value="M.Sc."><option value="M.A."><option value="MCom"><option value="MBBS">
  <option value="BDS"><option value="B.Pharm"><option value="M.D."><option value="B.Ed."><option value="M.Ed.">
  <option value="LL.B."><option value="LL.M."><option value="CA"><option value="CS"><option value="ICWA">
  <option value="Ph.D."><option value="Diploma"><option value="ITI"><option value="Polytechnic">
  <option value="Higher Secondary/High School"><option value="B.Sc. Nursing">
  <option value="IAS"><option value="IPS"><option value="IFS">
  <option value="Doesn't Matter"><option value="Not Applicable">
</datalist>
<datalist id="admin_partner_job_list">
  <option value="Any"><option value="Any Job"><option value="Government Job"><option value="Private Job">
  <option value="Self Employed"><option value="Business"><option value="IT Professional">
  <option value="Software Engineer"><option value="Doctor"><option value="Engineer"><option value="Teacher">
  <option value="Professor"><option value="Lawyer"><option value="Bank Employee"><option value="Police">
  <option value="Army"><option value="Navy"><option value="Air Force"><option value="Accountant">
  <option value="Manager"><option value="Supervisor"><option value="Contractor"><option value="Farmer">
  <option value="Employed"><option value="Well Settled"><option value="Doesn't Matter">
  <option value="Not Applicable">
</datalist>
<datalist id="admin_occupation_list">
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
<datalist id="admin_nativity_list">
  <option value="India"><option value="Puducherry"><option value="Pondicherry"><option value="Chennai"><option value="Tamil Nadu">
  <option value="France"><option value="Singapore"><option value="Malaysia"><option value="UAE">
  <option value="Kuwait"><option value="Saudi Arabia"><option value="Qatar"><option value="Bahrain">
  <option value="Oman"><option value="USA"><option value="UK"><option value="Canada">
  <option value="Australia"><option value="Germany"><option value="Japan"><option value="Sri Lanka">
  <option value="Reunion"><option value="Other">
</datalist>
<datalist id="admin_tongue_list">
  <option value="Tamil"><option value="Telugu"><option value="Malayalam"><option value="Kannada">
  <option value="Hindi"><option value="English"><option value="Urdu"><option value="Marathi">
  <option value="Gujarati"><option value="Bengali"><option value="Punjabi"><option value="Odia">
  <option value="Assamese"><option value="Konkani"><option value="Tulu"><option value="Sanskrit">
  <option value="Sindhi"><option value="Kashmiri"><option value="Nepali"><option value="Manipuri">
  <option value="Sourashtra"><option value="French"><option value="Arabic"><option value="Other">
</datalist>
<datalist id="admin_self_job_list">
  <!-- Government / Public Sector -->
  <option value="Central Govt Employee"><option value="State Govt Employee"><option value="PSU Employee">
  <option value="Defense - Army"><option value="Defense - Navy"><option value="Defense - Air Force">
  <option value="Police / CRPF / BSF"><option value="IAS / IPS / IFS Officer"><option value="Railway Employee">
  <option value="Postal Employee"><option value="TNPSC Group Service">
  <!-- IT / Software -->
  <option value="Software Engineer"><option value="Software Developer"><option value="Data Analyst">
  <option value="Data Scientist"><option value="System Administrator"><option value="Network Engineer">
  <option value="Web Developer"><option value="UI/UX Designer"><option value="IT Manager">
  <option value="Cyber Security Analyst">
  <!-- Engineering -->
  <option value="Mechanical Engineer"><option value="Civil Engineer"><option value="Electrical Engineer">
  <option value="Electronics Engineer"><option value="Chemical Engineer"><option value="Production Engineer">
  <option value="Site Engineer"><option value="Quality Engineer"><option value="Project Manager">
  <!-- Medical -->
  <option value="Doctor"><option value="Surgeon"><option value="Dentist"><option value="Pharmacist">
  <option value="Nurse"><option value="Physiotherapist"><option value="Lab Technician">
  <option value="Ayurveda / Siddha / Homeopathy">
  <!-- Education -->
  <option value="Professor"><option value="Lecturer"><option value="School Teacher">
  <option value="Private Tutor"><option value="Research Scholar">
  <!-- Banking / Finance -->
  <option value="Bank Manager"><option value="Bank Employee"><option value="Chartered Accountant">
  <option value="Financial Analyst"><option value="Insurance Agent"><option value="Auditor">
  <option value="Tax Consultant">
  <!-- Legal -->
  <option value="Advocate / Lawyer"><option value="Judge"><option value="Legal Advisor"><option value="Notary">
  <!-- Business -->
  <option value="Business Owner"><option value="Shopkeeper"><option value="Trader / Merchant">
  <option value="Real Estate Business"><option value="Exporter / Importer"><option value="Contractor">
  <option value="Freelancer"><option value="Startup Founder">
  <!-- Agriculture -->
  <option value="Farmer / Agriculturist"><option value="Dairy Farmer"><option value="Plantation Owner">
  <option value="Agricultural Officer">
  <!-- Skilled Trades -->
  <option value="Electrician"><option value="Plumber"><option value="Carpenter"><option value="Welder">
  <option value="Mechanic"><option value="Tailor"><option value="Goldsmith"><option value="Mason">
  <!-- Media / Creative -->
  <option value="Journalist"><option value="Content Writer"><option value="Photographer">
  <option value="Graphic Designer"><option value="Film / TV Professional">
  <!-- Abroad / NRI -->
  <option value="Working in Gulf"><option value="Working in USA"><option value="Working in UK">
  <option value="Working in Canada"><option value="Working in Australia"><option value="Working in Singapore">
  <option value="Working in Malaysia"><option value="Merchant Navy">
  <!-- Other -->
  <option value="Private Company Employee"><option value="Supervisor / Foreman"><option value="Driver">
  <option value="Chef / Cook"><option value="Security Guard"><option value="Home Maker">
  <option value="Retired"><option value="Student"><option value="Not Employed"><option value="Others">
</datalist>
<div class="modal-overlay" id="addOverlay" style="align-items:flex-start;padding:20px 10px;overflow-y:auto">
  <div class="modal" style="max-width:880px;width:100%;margin:auto">
    <div class="modal-header" style="background:linear-gradient(135deg,#1a1a2e,#2d2d5e);color:#fff;border-radius:12px 12px 0 0;padding:16px 22px">
      <div>
        <span class="modal-title" style="color:#fff;font-size:16px">👤 New Member Registration</span>
        <div style="font-size:12px;color:rgba(255,255,255,0.65);margin-top:2px">Fill all details — fields marked <span style="color:#fca5a5">*</span> are required</div>
      </div>
      <button class="modal-close" style="color:#fff;font-size:22px" onclick="closeModal('addOverlay')">×</button>
    </div>

    <div style="padding:0 22px 10px;max-height:80vh;overflow-y:auto">

      <div id="addFormBanner"></div>
      <!-- ═══ 1. Profile Photos ═══ -->
      <div class="form-section-title">📸 Profile Photographs</div>
      <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px">
        <div style="flex:1;min-width:140px"><label class="input-label" style="margin-bottom:6px">Photo 1</label><div id="a_photo1_wrap" style="width:100%;height:140px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#faf9f7;overflow:hidden" onclick="document.getElementById('a_photo1_file').click()"><img id="a_photo1_preview" style="display:none;width:100%;height:100%;object-fit:cover"><span id="a_photo1_placeholder" style="color:var(--text-secondary);font-size:12px;text-align:center">Click to upload</span></div><input type="file" id="a_photo1_file" accept="image/*" style="display:none" onchange="previewAdminPhoto(this,'a_photo1')"></div>
        <div style="flex:1;min-width:140px"><label class="input-label" style="margin-bottom:6px">Photo 2</label><div id="a_photo2_wrap" style="width:100%;height:140px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#faf9f7;overflow:hidden" onclick="document.getElementById('a_photo2_file').click()"><img id="a_photo2_preview" style="display:none;width:100%;height:100%;object-fit:cover"><span id="a_photo2_placeholder" style="color:var(--text-secondary);font-size:12px;text-align:center">Click to upload</span></div><input type="file" id="a_photo2_file" accept="image/*" style="display:none" onchange="previewAdminPhoto(this,'a_photo2')"></div>
        <div style="flex:1;min-width:140px"><label class="input-label" style="margin-bottom:6px">Photo 3</label><div id="a_photo3_wrap" style="width:100%;height:140px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#faf9f7;overflow:hidden" onclick="document.getElementById('a_photo3_file').click()"><img id="a_photo3_preview" style="display:none;width:100%;height:100%;object-fit:cover"><span id="a_photo3_placeholder" style="color:var(--text-secondary);font-size:12px;text-align:center">Click to upload</span></div><input type="file" id="a_photo3_file" accept="image/*" style="display:none" onchange="previewAdminPhoto(this,'a_photo3')"></div>
      </div>

      <!-- ═══ 2. Personal Details ═══ -->
      <div class="form-section-title">👤 Personal Details</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr 0.5fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Mobile <span class="req">*</span></label><input class="input" id="a_mobile" placeholder="10-digit mobile" type="tel" maxlength="10"></div>
        <div class="form-row"><label class="input-label">Name <span class="req">*</span></label><input class="input" id="a_name" placeholder="Full name"></div>
        <div class="form-row"><label class="input-label">Gender <span class="req">*</span></label><select class="input" id="a_gender"><option value="">— Select —</option><option>Male</option><option>Female</option></select></div>
        <div class="form-row"><label class="input-label">Date of Birth <span class="req">*</span> <span id="a_age_display" style="font-weight:700;font-size:12px;margin-left:4px"></span></label><input class="input" id="a_dob" type="date"></div>
        <div class="form-row"><label class="input-label">Age</label><input class="input" id="a_age_input" readonly style="background:#f0fdf4;font-weight:700;text-align:center;font-size:15px"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Religion <span class="req">*</span></label><select class="input" id="a_religion"><option value="">— Select —</option><option>Hindu</option><option>Muslim</option><option>Christian</option><option>Sikh</option><option>Jain</option><option>Buddhist</option></select></div>
        <div class="form-row"><label class="input-label">Caste <span class="req">*</span></label><select class="input" id="a_caste" onchange="onCasteChange('a')"><option value="">— Select —</option></select></div>
        <div class="form-row"><label class="input-label">Sub Caste</label><select class="input" id="a_subcaste"><option value="">— Select —</option></select></div>
        <div class="form-row"><label class="input-label">Mother Tongue <span class="req">*</span></label><input class="input" id="a_tongue" list="admin_tongue_list" placeholder="Type or select — any language"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Marital Status <span class="req">*</span></label><select class="input" id="a_marital"><option value="">— Select —</option><option>Unmarried</option><option>Divorced</option><option>Widowed</option><option>Separated</option></select></div>
        <div class="form-row"><label class="input-label">Nationality</label><select class="input" id="a_nationality"><option value="">— Select —</option></select></div>
        <div class="form-row"><label class="input-label">Own House</label><select class="input" id="a_own_house"><option>Yes</option><option>No</option></select></div>
        <div class="form-row"><label class="input-label">Born As</label><div style="display:flex;gap:6px"><input class="input" id="a_born_as_num" type="number" min="1" max="20" placeholder="e.g. 2" style="width:70px"><select class="input" id="a_born_as_type" style="flex:1"><option value="">— Select —</option><option value="Son">Son</option><option value="Daughter">Daughter</option></select></div></div>
        <div class="form-row"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Birth Time (Hour)</label><select class="input" id="a_birth_hour"><option value="">—</option><option>01</option><option>02</option><option>03</option><option>04</option><option>05</option><option>06</option><option>07</option><option>08</option><option>09</option><option>10</option><option>11</option><option>12</option></select></div>
        <div class="form-row"><label class="input-label">Birth Time (Min)</label><select class="input" id="a_birth_min"><option value="">—</option><option>00</option><option>01</option><option>02</option><option>03</option><option>04</option><option>05</option><option>06</option><option>07</option><option>08</option><option>09</option><option>10</option><option>11</option><option>12</option><option>13</option><option>14</option><option>15</option><option>16</option><option>17</option><option>18</option><option>19</option><option>20</option><option>21</option><option>22</option><option>23</option><option>24</option><option>25</option><option>26</option><option>27</option><option>28</option><option>29</option><option>30</option><option>31</option><option>32</option><option>33</option><option>34</option><option>35</option><option>36</option><option>37</option><option>38</option><option>39</option><option>40</option><option>41</option><option>42</option><option>43</option><option>44</option><option>45</option><option>46</option><option>47</option><option>48</option><option>49</option><option>50</option><option>51</option><option>52</option><option>53</option><option>54</option><option>55</option><option>56</option><option>57</option><option>58</option><option>59</option></select></div>
        <div class="form-row"><label class="input-label">AM / PM</label><select class="input" id="a_birth_ampm"><option>AM</option><option>PM</option></select></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Place of Birth</label><input class="input" id="a_pob" placeholder="e.g. Puducherry"></div>
        <div class="form-row"><label class="input-label">Nativity</label><input class="input" id="a_nativity" list="admin_nativity_list" placeholder="Type or select"></div>
        <div class="form-row"><label class="input-label">Present Country</label><select class="input" id="a_workplace"><option value="">— Select —</option></select></div>
      </div>
      <div class="form-row"><label class="input-label">Additional Details</label><textarea class="input" id="a_others" rows="2" placeholder="Talents, Achievements, Visa Status…" style="resize:vertical"></textarea></div>
      <input type="hidden" id="a_age" value="">

      <!-- ═══ 3. Family Details ═══ -->
      <div class="form-section-title">👨‍👩‍👧‍👦 Family Details</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Father's Name</label><input class="input" id="a_father" placeholder="Father's name"></div>
        <div class="form-row"><label class="input-label">Father's Occupation</label><input class="input" id="a_father_job" list="admin_occupation_list" placeholder="Type or select"></div>
        <div class="form-row"><label class="input-label">Father Status</label><select class="input" id="a_father_alive"><option value="">— Select —</option><option>Employed</option><option>Businessman</option><option>Professional</option><option>Retired</option><option>Not Employed</option><option>Passed Away</option></select></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Mother's Name</label><input class="input" id="a_mother" placeholder="Mother's name"></div>
        <div class="form-row"><label class="input-label">Mother's Occupation</label><input class="input" id="a_mother_job" list="admin_occupation_list" placeholder="Type or select"></div>
        <div class="form-row"><label class="input-label">Mother Status</label><select class="input" id="a_mother_alive"><option value="">— Select —</option><option>Home Maker</option><option>Employed</option><option>Businessman</option><option>Professional</option><option>Retired</option><option>Not Employed</option><option>Passed Away</option></select></div>
      </div>
      <div class="form-row"><label class="input-label" style="margin-bottom:8px">Siblings</label>
        <table style="width:100%;border-collapse:collapse;font-size:13px;border:1px solid var(--border);border-radius:8px;overflow:hidden"><thead style="background:#f3f4f6"><tr><th style="padding:8px 12px;text-align:left;font-weight:600"></th><th style="padding:8px 12px;text-align:center;font-weight:600">Elder Brother</th><th style="padding:8px 12px;text-align:center;font-weight:600">Younger Brother</th><th style="padding:8px 12px;text-align:center;font-weight:600">Elder Sister</th><th style="padding:8px 12px;text-align:center;font-weight:600">Younger Sister</th></tr></thead><tbody>
          <tr style="background:#fff"><td style="padding:8px 12px;font-weight:500">Married</td><td style="padding:6px;text-align:center"><select class="input" id="a_sib_eb_m" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td><td style="padding:6px;text-align:center"><select class="input" id="a_sib_yb_m" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td><td style="padding:6px;text-align:center"><select class="input" id="a_sib_es_m" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td><td style="padding:6px;text-align:center"><select class="input" id="a_sib_ys_m" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td></tr>
          <tr style="background:#faf9f7"><td style="padding:8px 12px;font-weight:500">Unmarried</td><td style="padding:6px;text-align:center"><select class="input" id="a_sib_eb_u" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td><td style="padding:6px;text-align:center"><select class="input" id="a_sib_yb_u" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td><td style="padding:6px;text-align:center"><select class="input" id="a_sib_es_u" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td><td style="padding:6px;text-align:center"><select class="input" id="a_sib_ys_u" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td></tr>
        </tbody></table>
      </div>

      <!-- ═══ 4. Physical Attributes ═══ -->
      <div class="form-section-title">⚖️ Physical Attributes</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Height</label><select class="input" id="a_height"><option value="">— Select —</option><option>4ft 5in</option><option>4ft 6in</option><option>4ft 7in</option><option>4ft 8in</option><option>4ft 9in</option><option>4ft 10in</option><option>4ft 11in</option><option>5ft 0in</option><option>5ft 1in</option><option>5ft 2in</option><option>5ft 3in</option><option>5ft 4in</option><option>5ft 5in</option><option>5ft 6in</option><option>5ft 7in</option><option>5ft 8in</option><option>5ft 9in</option><option>5ft 10in</option><option>5ft 11in</option><option>6ft 0in</option><option>6ft 1in</option><option>6ft 2in</option><option>6ft 3in</option><option>6ft 4in</option><option>6ft 5in</option></select></div>
        <div class="form-row"><label class="input-label">Weight</label><select class="input" id="a_weight"><option value="">— Select —</option><option>40 kg</option><option>42 kg</option><option>45 kg</option><option>48 kg</option><option>50 kg</option><option>52 kg</option><option>55 kg</option><option>56 kg</option><option>58 kg</option><option>60 kg</option><option>62 kg</option><option>63 kg</option><option>65 kg</option><option>67 kg</option><option>68 kg</option><option>69 kg</option><option>70 kg</option><option>71 kg</option><option>72 kg</option><option>73 kg</option><option>75 kg</option><option>78 kg</option><option>80 kg</option><option>82 kg</option><option>85 kg</option><option>88 kg</option><option>90 kg</option><option>95 kg</option><option>100 kg</option><option>105 kg</option><option>110 kg</option></select></div>
        <div class="form-row"><label class="input-label">Blood Group</label><select class="input" id="a_blood"><option value="">— Select —</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>O+</option><option>O-</option><option>AB+</option><option>AB-</option></select></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Diet</label><div style="display:flex;gap:14px;margin-top:10px;flex-wrap:wrap"><label style="font-size:13px;cursor:pointer"><input type="radio" name="a_diet" value="Vegetarian" checked> Vegetarian</label><label style="font-size:13px;cursor:pointer"><input type="radio" name="a_diet" value="Non-Vegetarian"> Non-Veg</label><label style="font-size:13px;cursor:pointer"><input type="radio" name="a_diet" value="Eggetarian"> Eggetarian</label></div></div>
        <div class="form-row"><label class="input-label">Disability</label><div style="display:flex;gap:18px;margin-top:10px"><label style="font-size:13px;cursor:pointer"><input type="radio" name="a_disability" value="No" checked> No</label><label style="font-size:13px;cursor:pointer"><input type="radio" name="a_disability" value="Yes"> Yes</label></div></div>
        <div class="form-row"><label class="input-label">Complexion</label><select class="input" id="a_complexion"><option value="">— Select —</option><option>Very Fair</option><option>Fair</option><option>White</option><option>Wheatish</option><option>Brown</option><option>Dark</option></select></div>
      </div>

      <!-- ═══ 5. Education & Occupation ═══ -->
      <div class="form-section-title">🎓 Education &amp; Occupation</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Qualification</label><select class="input" id="a_qualification"><option value="">— Select —</option><optgroup label="Below 10th"><option>Below 10th</option><option>10th / SSLC</option></optgroup><optgroup label="Higher Secondary"><option>12th / HSC</option><option>ITI</option><option>Diploma</option></optgroup><optgroup label="Undergraduate"><option>B.A</option><option>B.Sc</option><option>B.Com</option><option>B.E / B.Tech</option><option>B.B.A</option><option>B.C.A</option><option>B.Ed</option><option>B.L / L.L.B</option><option>B.Arch</option><option>B.Pharm</option><option>B.D.S</option><option>M.B.B.S</option><option>B.V.Sc</option><option>B.P.T</option><option>B.Sc (Nursing)</option><option>B.S.W</option><option>B.F.A</option><option>B.Des</option></optgroup><optgroup label="Postgraduate"><option>M.A</option><option>M.Sc</option><option>M.Com</option><option>M.E / M.Tech</option><option>M.B.A</option><option>M.C.A</option><option>M.Ed</option><option>M.L / L.L.M</option><option>M.Pharm</option><option>M.D</option><option>M.S (Medical)</option><option>M.D.S</option><option>M.P.T</option><option>M.S.W</option><option>M.Des</option></optgroup><optgroup label="Doctorate / Research"><option>M.Phil</option><option>Ph.D</option><option>D.M</option><option>D.Litt</option></optgroup><optgroup label="Professional / Other"><option>C.A</option><option>C.S</option><option>I.C.W.A / C.M.A</option><option>C.F.A</option><option>I.A.S / I.P.S / I.F.S</option><option>Others</option></optgroup></select></div>
        <div class="form-row"><label class="input-label">Occupation</label><input class="input" id="a_job" list="admin_self_job_list" placeholder="Type or select — any occupation"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Place of Work</label><input class="input" id="a_place_job" placeholder="City or Company"></div>
        <div class="form-row"><label class="input-label">Monthly Income (₹)</label><input class="input" id="a_income" type="number" placeholder="e.g. 35000"></div>
      </div>

      <!-- ═══ 6. Astrology ═══ -->
      <div style="display:flex;align-items:center;justify-content:space-between">
        <div class="form-section-title" style="margin-bottom:0">🪐 Astrology</div>
        <button type="button" class="btn btn-sm" onclick="autoCalcHoroscope('a')" style="background:#7c3aed;color:#fff;border:none;font-size:11.5px;padding:5px 12px;border-radius:6px;cursor:pointer">🔮 Auto Calculate Star / Raasi</button>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Star</label><select class="input" id="a_star"><option value="">— Select —</option><option>Ashwini</option><option>Bharani</option><option>Karthigai</option><option>Rohini</option><option>Mirigasirisham</option><option>Thiruvathirai</option><option>Punarpoosam</option><option>Poosam</option><option>Ayilyam</option><option>Makam</option><option>Pooram</option><option>Uthiram</option><option>Hastham</option><option>Chithirai</option><option>Swathi</option><option>Visakam</option><option>Anusham</option><option>Kettai</option><option>Moolam</option><option>Pooradam</option><option>Uthradam</option><option>Thiruvonam</option><option>Avittam</option><option>Sadhayam</option><option>Puratathi</option><option>Uthirattathi</option><option>Revathi</option></select></div>
        <div class="form-row"><label class="input-label">Raasi</label><select class="input" id="a_raasi"><option value="">— Select —</option><option>Mesham</option><option>Rishabam</option><option>Midhunam</option><option>Kadagam</option><option>Simham</option><option>Kanni</option><option>Thulam</option><option>Viruchigam</option><option>Dhanusu</option><option>Makaram</option><option>Kumbam</option><option>Meenam</option></select></div>
        <div class="form-row"><label class="input-label">Padam</label><select class="input" id="a_paadam"><option value="">— Select —</option><option>1st Paadam</option><option>2nd Paadam</option><option>3rd Paadam</option><option>4th Paadam</option></select></div>
        <div class="form-row"><label class="input-label">Laknam</label><select class="input" id="a_lagnam"><option value="">— Select —</option><option>Mesham</option><option>Rishabam</option><option>Midhunam</option><option>Kadagam</option><option>Simham</option><option>Kanni</option><option>Thulam</option><option>Viruchigam</option><option>Dhanusu</option><option>Makaram</option><option>Kumbam</option><option>Meenam</option></select></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Gothram</label><input class="input" id="a_gothram" placeholder="e.g. Kashyapa"></div>
        <div class="form-row"><label class="input-label">Dosham</label><div style="display:flex;gap:18px;margin-top:10px"><label style="font-size:13px;cursor:pointer"><input type="radio" name="a_dosham" value="No" checked> No</label><label style="font-size:13px;cursor:pointer"><input type="radio" name="a_dosham" value="Yes"> Yes</label><label style="font-size:13px;cursor:pointer"><input type="radio" name="a_dosham" value="Partial"> Partial</label></div></div>
        <div class="form-row" id="a_dosham_type_wrap" style="display:none"><label class="input-label">Dosham Type</label><select class="input" id="a_dosham_type"><option value="">— Select Dosham Type —</option></select></div>
      </div>

      <!-- ═══ 7. Horoscope Details ═══ -->
      <div class="form-section-title">🔮 Horoscope Details</div>
      <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:12px">
        <div style="flex:1;min-width:200px"><label class="input-label" style="margin-bottom:6px">Rasi Chart Photo</label><div id="a_rasi_photo_wrap" style="width:100%;height:120px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#faf9f7;overflow:hidden" onclick="document.getElementById('a_rasi_photo_file').click()"><img id="a_rasi_photo_preview" style="display:none;width:100%;height:100%;object-fit:contain"><span id="a_rasi_photo_placeholder" style="color:var(--text-secondary);font-size:12px">📄 Upload Rasi Chart</span></div><input type="file" id="a_rasi_photo_file" accept="image/*" style="display:none" onchange="previewAdminPhoto(this,'a_rasi_photo')"></div>
        <div style="flex:1;min-width:200px"><label class="input-label" style="margin-bottom:6px">Amsam Chart Photo</label><div id="a_amsam_photo_wrap" style="width:100%;height:120px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#faf9f7;overflow:hidden" onclick="document.getElementById('a_amsam_photo_file').click()"><img id="a_amsam_photo_preview" style="display:none;width:100%;height:100%;object-fit:contain"><span id="a_amsam_photo_placeholder" style="color:var(--text-secondary);font-size:12px">📄 Upload Amsam Chart</span></div><input type="file" id="a_amsam_photo_file" accept="image/*" style="display:none" onchange="previewAdminPhoto(this,'a_amsam_photo')"></div>
      </div>

      <!-- ═══ 8. Partner Expectations ═══ -->
      <div class="form-section-title">💑 Partner Expectations</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Qualification</label><input class="input" id="a_p_qualification" list="admin_partner_qual_list" placeholder="e.g. Any Degree"></div>
        <div class="form-row"><label class="input-label">Job Preference</label><input class="input" id="a_p_job" list="admin_partner_job_list" placeholder="e.g. Any, Govt Job"></div>
        <div class="form-row"><label class="input-label">Job Requirement</label><select class="input" id="a_p_job_req"><option>Optional</option><option>Must</option><option>Not Required</option></select></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Income Expectation</label><input class="input" id="a_p_income" placeholder="e.g. 30000"></div>
        <div class="form-row"><label class="input-label">Age From</label><input class="input" id="a_p_age_from" type="number" min="18" max="70"></div>
        <div class="form-row"><label class="input-label">Age To</label><input class="input" id="a_p_age_to" type="number" min="18" max="70"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Diet</label><select class="input" id="a_p_diet"><option>Vegetarian</option><option>Non-Vegetarian</option><option>Any</option></select></div>
        <div class="form-row"><label class="input-label">Marital Status</label><select class="input" id="a_p_marital"><option>Unmarried</option><option>Divorced</option><option>Widowed</option><option>Any</option></select></div>
        <div class="form-row"><label class="input-label">Horoscope Required?</label><select class="input" id="a_p_horoscope"><option>No</option><option>Yes</option><option>Not Applicable</option></select></div>
      </div>
      <div class="form-row"><label class="input-label">Caste Preference</label><input type="hidden" id="a_p_caste"><div id="a_p_caste_box" style="border:1px solid var(--border);border-radius:8px;padding:8px 10px;background:#faf9f7;max-height:160px;overflow-y:auto"></div></div>
      <div class="form-row" id="a_p_subcaste_wrap" style="display:none"><label class="input-label">Sub Caste Preference</label><input type="hidden" id="a_p_subcaste"><div id="a_p_subcaste_box" style="border:1px solid var(--border);border-radius:8px;padding:8px 10px;background:#faf9f7;max-height:140px;overflow-y:auto"></div></div>
      <div class="form-row"><label class="input-label">Other Requirements</label><textarea class="input" id="a_p_other" rows="2" placeholder="Any other expectations…" style="resize:vertical"></textarea></div>

      <!-- ═══ 9. Communication Details ═══ -->
      <div class="form-section-title">📞 Communication Details</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Permanent Address</label><textarea class="input" id="a_perm_addr" rows="2" placeholder="Door No, Street, Area…" style="resize:vertical"></textarea></div>
        <div class="form-row"><label class="input-label">Present Address</label><textarea class="input" id="a_present_addr" rows="2" placeholder="Door No, Street, Area, City, District, State" style="resize:vertical"></textarea></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Area</label><input class="input" id="a_present_area" placeholder="e.g. Anna Nagar"></div>
        <div class="form-row"><label class="input-label">City</label><input class="input" id="a_present_city" placeholder="e.g. Chennai"></div>
        <div class="form-row"><label class="input-label">District</label><select class="input" id="a_present_district"><option value="">— Select —</option></select></div>
        <div class="form-row"><label class="input-label">State</label><select class="input" id="a_present_state"><option value="">— Select —</option></select></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Contact Person</label><input class="input" id="a_contact_person" placeholder="Contact person name"></div>
        <div class="form-row"><label class="input-label">Contact Number</label><input class="input" id="a_alt_mobile" type="tel" maxlength="10" placeholder="Alternative mobile"></div>
        <div class="form-row"><label class="input-label">Email</label><input class="input" id="a_email" type="email" placeholder="Email address"></div>
      </div>

    </div><!-- end scroll div -->

    <div class="modal-footer" style="border-top:1px solid var(--border);padding:14px 22px">
      <button class="btn btn-outline" onclick="closeModal('addOverlay')">Cancel</button>
      <button class="btn btn-primary" id="addProfileBtn" style="min-width:140px" onclick="saveAdd()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Profile
      </button>
    </div>
  </div>
</div>

<!-- EDIT — FULL FORM -->
<div class="modal-overlay" id="editOverlay" style="align-items:flex-start;padding:20px 10px;overflow-y:auto">
  <div class="modal" style="max-width:880px;width:100%;margin:auto">
    <div class="modal-header" style="background:linear-gradient(135deg,#1a1a2e,#2d2d5e);color:#fff;border-radius:12px 12px 0 0;padding:16px 22px">
      <div>
        <span class="modal-title" style="color:#fff;font-size:16px">✏️ Edit Profile</span>
        <div style="font-size:12px;color:rgba(255,255,255,0.65);margin-top:2px">Fields marked <span style="color:#fca5a5">*</span> are required</div>
      </div>
      <button class="modal-close" style="color:#fff;font-size:22px" onclick="closeModal('editOverlay')">×</button>
    </div>
    <div style="padding:0 22px 10px;max-height:80vh;overflow-y:auto">

      <div id="editFormBanner"></div>
      <div class="form-section-title">📸 Profile Photographs</div>
      <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:16px;align-items:flex-start">
        <!-- Photo 1: Face crop (circle) + Full view side by side -->
        <div style="display:flex;gap:14px;align-items:flex-start">
          <div style="text-align:center">
            <label class="input-label" style="margin-bottom:6px;display:block">Face</label>
            <div style="width:120px;height:120px;border-radius:50%;border:3px solid #3b82f6;overflow:hidden;background:#faf9f7;display:flex;align-items:center;justify-content:center">
              <img id="e_photo1_face" style="display:none;width:100%;height:100%;object-fit:cover;object-position:top">
              <span id="e_photo1_face_ph" style="color:var(--text-secondary);font-size:11px">No Photo</span>
            </div>
          </div>
          <div style="text-align:center">
            <label class="input-label" style="margin-bottom:6px;display:block">Full Photo</label>
            <div id="e_photo1_wrap" style="width:140px;height:180px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#faf9f7;overflow:hidden" onclick="document.getElementById('e_photo1_file').click()">
              <img id="e_photo1_preview" style="display:none;width:100%;height:100%;object-fit:contain">
              <span id="e_photo1_placeholder" style="color:var(--text-secondary);font-size:12px;text-align:center">Click to upload</span>
            </div>
            <input type="file" id="e_photo1_file" accept="image/*" style="display:none" onchange="previewAdminPhoto(this,'e_photo1')">
          </div>
        </div>
        <!-- Photo 2 -->
        <div style="text-align:center">
          <label class="input-label" style="margin-bottom:6px;display:block">Photo 2</label>
          <div id="e_photo2_wrap" style="width:140px;height:180px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#faf9f7;overflow:hidden" onclick="document.getElementById('e_photo2_file').click()">
            <img id="e_photo2_preview" style="display:none;width:100%;height:100%;object-fit:contain">
            <span id="e_photo2_placeholder" style="color:var(--text-secondary);font-size:12px;text-align:center">Click to upload</span>
          </div>
          <input type="file" id="e_photo2_file" accept="image/*" style="display:none" onchange="previewAdminPhoto(this,'e_photo2')">
        </div>
        <!-- Photo 3 -->
        <div style="text-align:center">
          <label class="input-label" style="margin-bottom:6px;display:block">Photo 3</label>
          <div id="e_photo3_wrap" style="width:140px;height:180px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#faf9f7;overflow:hidden" onclick="document.getElementById('e_photo3_file').click()">
            <img id="e_photo3_preview" style="display:none;width:100%;height:100%;object-fit:contain">
            <span id="e_photo3_placeholder" style="color:var(--text-secondary);font-size:12px;text-align:center">Click to upload</span>
          </div>
          <input type="file" id="e_photo3_file" accept="image/*" style="display:none" onchange="previewAdminPhoto(this,'e_photo3')">
        </div>
      </div>

      <div class="form-section-title">👤 Personal Details</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr 0.5fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Mobile <span class="req">*</span></label><input class="input" id="e_mobile" placeholder="10-digit mobile" type="tel" maxlength="10" readonly style="background:#f3f4f6"></div>
        <div class="form-row"><label class="input-label">Name <span class="req">*</span></label><input class="input" id="e_name" placeholder="Full name"></div>
        <div class="form-row"><label class="input-label">Gender <span class="req">*</span></label><select class="input" id="e_gender"><option value="">— Select —</option><option>Male</option><option>Female</option></select></div>
        <div class="form-row"><label class="input-label">Date of Birth <span class="req">*</span> <span id="e_age_display" style="font-weight:700;font-size:12px;margin-left:4px"></span></label><input class="input" id="e_dob" type="date"></div>
        <div class="form-row"><label class="input-label">Age</label><input class="input" id="e_age_input" readonly style="background:#f0fdf4;font-weight:700;text-align:center;font-size:15px"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Religion <span class="req">*</span></label><select class="input" id="e_religion"><option value="">— Select —</option><option>Hindu</option><option>Muslim</option><option>Christian</option><option>Sikh</option><option>Jain</option><option>Buddhist</option></select></div>
        <div class="form-row"><label class="input-label">Caste <span class="req">*</span></label><select class="input" id="e_caste" onchange="onCasteChange('e')"><option value="">— Select —</option></select></div>
        <div class="form-row"><label class="input-label">Sub Caste</label><select class="input" id="e_subcaste"><option value="">— Select —</option></select></div>
        <div class="form-row"><label class="input-label">Mother Tongue <span class="req">*</span></label><input class="input" id="e_tongue" list="admin_tongue_list" placeholder="Type or select — any language"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Marital Status <span class="req">*</span></label><select class="input" id="e_marital"><option value="">— Select —</option><option>Unmarried</option><option>Divorced</option><option>Widowed</option><option>Separated</option></select></div>
        <div class="form-row"><label class="input-label">Nationality</label><select class="input" id="e_nationality"><option value="">— Select —</option></select></div>
        <div class="form-row"><label class="input-label">Own House</label><select class="input" id="e_own_house"><option>Yes</option><option>No</option></select></div>
        <div class="form-row"><label class="input-label">Born As</label><div style="display:flex;gap:6px"><input class="input" id="e_born_as_num" type="number" min="1" max="20" placeholder="e.g. 2" style="width:70px"><select class="input" id="e_born_as_type" style="flex:1"><option value="">— Select —</option><option value="Son">Son</option><option value="Daughter">Daughter</option></select></div></div>
        <div class="form-row"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Birth Time (Hour)</label><select class="input" id="e_birth_hour"><option value="">—</option><option>01</option><option>02</option><option>03</option><option>04</option><option>05</option><option>06</option><option>07</option><option>08</option><option>09</option><option>10</option><option>11</option><option>12</option></select></div>
        <div class="form-row"><label class="input-label">Birth Time (Min)</label><select class="input" id="e_birth_min"><option value="">—</option><option>00</option><option>01</option><option>02</option><option>03</option><option>04</option><option>05</option><option>06</option><option>07</option><option>08</option><option>09</option><option>10</option><option>11</option><option>12</option><option>13</option><option>14</option><option>15</option><option>16</option><option>17</option><option>18</option><option>19</option><option>20</option><option>21</option><option>22</option><option>23</option><option>24</option><option>25</option><option>26</option><option>27</option><option>28</option><option>29</option><option>30</option><option>31</option><option>32</option><option>33</option><option>34</option><option>35</option><option>36</option><option>37</option><option>38</option><option>39</option><option>40</option><option>41</option><option>42</option><option>43</option><option>44</option><option>45</option><option>46</option><option>47</option><option>48</option><option>49</option><option>50</option><option>51</option><option>52</option><option>53</option><option>54</option><option>55</option><option>56</option><option>57</option><option>58</option><option>59</option></select></div>
        <div class="form-row"><label class="input-label">AM / PM</label><select class="input" id="e_birth_ampm"><option>AM</option><option>PM</option></select></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Place of Birth</label><input class="input" id="e_pob" placeholder="e.g. Puducherry"></div>
        <div class="form-row"><label class="input-label">Nativity</label><input class="input" id="e_nativity" list="admin_nativity_list" placeholder="Type or select"></div>
        <div class="form-row"><label class="input-label">Present Country</label><select class="input" id="e_workplace"><option value="">— Select —</option></select></div>
      </div>
      <div class="form-row"><label class="input-label">Additional Details</label><textarea class="input" id="e_others" rows="2" placeholder="Talents, Achievements, Visa Status…" style="resize:vertical"></textarea></div>
      <input type="hidden" id="e_age" value="">

      <div class="form-section-title">👨‍👩‍👧‍👦 Family Details</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Father's Name</label><input class="input" id="e_father" placeholder="Father's name"></div>
        <div class="form-row"><label class="input-label">Father's Occupation</label><input class="input" id="e_father_job" list="admin_occupation_list" placeholder="Type or select"></div>
        <div class="form-row"><label class="input-label">Father Status</label><select class="input" id="e_father_alive"><option value="">— Select —</option><option>Employed</option><option>Businessman</option><option>Professional</option><option>Retired</option><option>Not Employed</option><option>Passed Away</option></select></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Mother's Name</label><input class="input" id="e_mother" placeholder="Mother's name"></div>
        <div class="form-row"><label class="input-label">Mother's Occupation</label><input class="input" id="e_mother_job" list="admin_occupation_list" placeholder="Type or select"></div>
        <div class="form-row"><label class="input-label">Mother Status</label><select class="input" id="e_mother_alive"><option value="">— Select —</option><option>Home Maker</option><option>Employed</option><option>Businessman</option><option>Professional</option><option>Retired</option><option>Not Employed</option><option>Passed Away</option></select></div>
      </div>
      <div class="form-row"><label class="input-label" style="margin-bottom:8px">Siblings</label>
        <table style="width:100%;border-collapse:collapse;font-size:13px;border:1px solid var(--border);border-radius:8px;overflow:hidden"><thead style="background:#f3f4f6"><tr><th style="padding:8px 12px;text-align:left;font-weight:600"></th><th style="padding:8px 12px;text-align:center;font-weight:600">Elder Brother</th><th style="padding:8px 12px;text-align:center;font-weight:600">Younger Brother</th><th style="padding:8px 12px;text-align:center;font-weight:600">Elder Sister</th><th style="padding:8px 12px;text-align:center;font-weight:600">Younger Sister</th></tr></thead><tbody>
          <tr style="background:#fff"><td style="padding:8px 12px;font-weight:500">Married</td><td style="padding:6px;text-align:center"><select class="input" id="e_sib_eb_m" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td><td style="padding:6px;text-align:center"><select class="input" id="e_sib_yb_m" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td><td style="padding:6px;text-align:center"><select class="input" id="e_sib_es_m" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td><td style="padding:6px;text-align:center"><select class="input" id="e_sib_ys_m" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td></tr>
          <tr style="background:#faf9f7"><td style="padding:8px 12px;font-weight:500">Unmarried</td><td style="padding:6px;text-align:center"><select class="input" id="e_sib_eb_u" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td><td style="padding:6px;text-align:center"><select class="input" id="e_sib_yb_u" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td><td style="padding:6px;text-align:center"><select class="input" id="e_sib_es_u" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td><td style="padding:6px;text-align:center"><select class="input" id="e_sib_ys_u" style="margin:0;padding:4px 8px;font-size:12px"><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option></select></td></tr>
        </tbody></table>
      </div>

      <div class="form-section-title">⚖️ Physical Attributes</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Height</label><select class="input" id="e_height"><option value="">— Select —</option><option>4ft 5in</option><option>4ft 6in</option><option>4ft 7in</option><option>4ft 8in</option><option>4ft 9in</option><option>4ft 10in</option><option>4ft 11in</option><option>5ft 0in</option><option>5ft 1in</option><option>5ft 2in</option><option>5ft 3in</option><option>5ft 4in</option><option>5ft 5in</option><option>5ft 6in</option><option>5ft 7in</option><option>5ft 8in</option><option>5ft 9in</option><option>5ft 10in</option><option>5ft 11in</option><option>6ft 0in</option><option>6ft 1in</option><option>6ft 2in</option><option>6ft 3in</option><option>6ft 4in</option><option>6ft 5in</option></select></div>
        <div class="form-row"><label class="input-label">Weight</label><select class="input" id="e_weight"><option value="">— Select —</option><option>40 kg</option><option>42 kg</option><option>45 kg</option><option>48 kg</option><option>50 kg</option><option>52 kg</option><option>55 kg</option><option>56 kg</option><option>58 kg</option><option>60 kg</option><option>62 kg</option><option>63 kg</option><option>65 kg</option><option>67 kg</option><option>68 kg</option><option>69 kg</option><option>70 kg</option><option>71 kg</option><option>72 kg</option><option>73 kg</option><option>75 kg</option><option>78 kg</option><option>80 kg</option><option>82 kg</option><option>85 kg</option><option>88 kg</option><option>90 kg</option><option>95 kg</option><option>100 kg</option><option>105 kg</option><option>110 kg</option></select></div>
        <div class="form-row"><label class="input-label">Blood Group</label><select class="input" id="e_blood"><option value="">— Select —</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>O+</option><option>O-</option><option>AB+</option><option>AB-</option></select></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Diet</label><div style="display:flex;gap:14px;margin-top:10px;flex-wrap:wrap"><label style="font-size:13px;cursor:pointer"><input type="radio" name="e_diet" value="Vegetarian" checked> Vegetarian</label><label style="font-size:13px;cursor:pointer"><input type="radio" name="e_diet" value="Non-Vegetarian"> Non-Veg</label><label style="font-size:13px;cursor:pointer"><input type="radio" name="e_diet" value="Eggetarian"> Eggetarian</label></div></div>
        <div class="form-row"><label class="input-label">Disability</label><div style="display:flex;gap:18px;margin-top:10px"><label style="font-size:13px;cursor:pointer"><input type="radio" name="e_disability" value="No" checked> No</label><label style="font-size:13px;cursor:pointer"><input type="radio" name="e_disability" value="Yes"> Yes</label></div></div>
        <div class="form-row"><label class="input-label">Complexion</label><select class="input" id="e_complexion"><option value="">— Select —</option><option>Very Fair</option><option>Fair</option><option>White</option><option>Wheatish</option><option>Brown</option><option>Dark</option></select></div>
      </div>

      <div class="form-section-title">🎓 Education &amp; Occupation</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Qualification</label><select class="input" id="e_qualification"><option value="">— Select —</option><optgroup label="Below 10th"><option>Below 10th</option><option>10th / SSLC</option></optgroup><optgroup label="Higher Secondary"><option>12th / HSC</option><option>ITI</option><option>Diploma</option></optgroup><optgroup label="Undergraduate"><option>B.A</option><option>B.Sc</option><option>B.Com</option><option>B.E / B.Tech</option><option>B.B.A</option><option>B.C.A</option><option>B.Ed</option><option>B.L / L.L.B</option><option>B.Arch</option><option>B.Pharm</option><option>B.D.S</option><option>M.B.B.S</option><option>B.V.Sc</option><option>B.P.T</option><option>B.Sc (Nursing)</option><option>B.S.W</option><option>B.F.A</option><option>B.Des</option></optgroup><optgroup label="Postgraduate"><option>M.A</option><option>M.Sc</option><option>M.Com</option><option>M.E / M.Tech</option><option>M.B.A</option><option>M.C.A</option><option>M.Ed</option><option>M.L / L.L.M</option><option>M.Pharm</option><option>M.D</option><option>M.S (Medical)</option><option>M.D.S</option><option>M.P.T</option><option>M.S.W</option><option>M.Des</option></optgroup><optgroup label="Doctorate / Research"><option>M.Phil</option><option>Ph.D</option><option>D.M</option><option>D.Litt</option></optgroup><optgroup label="Professional / Other"><option>C.A</option><option>C.S</option><option>I.C.W.A / C.M.A</option><option>C.F.A</option><option>I.A.S / I.P.S / I.F.S</option><option>Others</option></optgroup></select></div>
        <div class="form-row"><label class="input-label">Occupation</label><input class="input" id="e_job" list="admin_self_job_list" placeholder="Type or select — any occupation"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Place of Work</label><input class="input" id="e_place_job" placeholder="City or Company"></div>
        <div class="form-row"><label class="input-label">Monthly Income (₹)</label><input class="input" id="e_income" type="number" placeholder="e.g. 35000"></div>
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between">
        <div class="form-section-title" style="margin-bottom:0">🪐 Astrology</div>
        <button type="button" class="btn btn-sm" onclick="autoCalcHoroscope('e')" style="background:#7c3aed;color:#fff;border:none;font-size:11.5px;padding:5px 12px;border-radius:6px;cursor:pointer">🔮 Auto Calculate Star / Raasi</button>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Star</label><select class="input" id="e_star"><option value="">— Select —</option><option>Ashwini</option><option>Bharani</option><option>Karthigai</option><option>Rohini</option><option>Mirigasirisham</option><option>Thiruvathirai</option><option>Punarpoosam</option><option>Poosam</option><option>Ayilyam</option><option>Makam</option><option>Pooram</option><option>Uthiram</option><option>Hastham</option><option>Chithirai</option><option>Swathi</option><option>Visakam</option><option>Anusham</option><option>Kettai</option><option>Moolam</option><option>Pooradam</option><option>Uthradam</option><option>Thiruvonam</option><option>Avittam</option><option>Sadhayam</option><option>Puratathi</option><option>Uthirattathi</option><option>Revathi</option></select></div>
        <div class="form-row"><label class="input-label">Raasi</label><select class="input" id="e_raasi"><option value="">— Select —</option><option>Mesham</option><option>Rishabam</option><option>Midhunam</option><option>Kadagam</option><option>Simham</option><option>Kanni</option><option>Thulam</option><option>Viruchigam</option><option>Dhanusu</option><option>Makaram</option><option>Kumbam</option><option>Meenam</option></select></div>
        <div class="form-row"><label class="input-label">Padam</label><select class="input" id="e_paadam"><option value="">— Select —</option><option>1st Paadam</option><option>2nd Paadam</option><option>3rd Paadam</option><option>4th Paadam</option></select></div>
        <div class="form-row"><label class="input-label">Laknam</label><select class="input" id="e_lagnam"><option value="">— Select —</option><option>Mesham</option><option>Rishabam</option><option>Midhunam</option><option>Kadagam</option><option>Simham</option><option>Kanni</option><option>Thulam</option><option>Viruchigam</option><option>Dhanusu</option><option>Makaram</option><option>Kumbam</option><option>Meenam</option></select></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Gothram</label><input class="input" id="e_gothram" placeholder="e.g. Kashyapa"></div>
        <div class="form-row"><label class="input-label">Dosham</label><div style="display:flex;gap:18px;margin-top:10px"><label style="font-size:13px;cursor:pointer"><input type="radio" name="e_dosham" value="No" checked> No</label><label style="font-size:13px;cursor:pointer"><input type="radio" name="e_dosham" value="Yes"> Yes</label><label style="font-size:13px;cursor:pointer"><input type="radio" name="e_dosham" value="Partial"> Partial</label></div></div>
        <div class="form-row" id="e_dosham_type_wrap" style="display:none"><label class="input-label">Dosham Type</label><select class="input" id="e_dosham_type"><option value="">— Select Dosham Type —</option></select></div>
      </div>

      <div class="form-section-title">🔮 Horoscope Details</div>
      <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:12px">
        <div style="flex:1;min-width:200px"><label class="input-label" style="margin-bottom:6px">Rasi Chart Photo</label><div id="e_rasi_photo_wrap" style="width:100%;height:120px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#faf9f7;overflow:hidden" onclick="document.getElementById('e_rasi_photo_file').click()"><img id="e_rasi_photo_preview" style="display:none;width:100%;height:100%;object-fit:contain"><span id="e_rasi_photo_placeholder" style="color:var(--text-secondary);font-size:12px">Upload Rasi Chart</span></div><input type="file" id="e_rasi_photo_file" accept="image/*" style="display:none" onchange="previewAdminPhoto(this,'e_rasi_photo')"></div>
        <div style="flex:1;min-width:200px"><label class="input-label" style="margin-bottom:6px">Amsam Chart Photo</label><div id="e_amsam_photo_wrap" style="width:100%;height:120px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#faf9f7;overflow:hidden" onclick="document.getElementById('e_amsam_photo_file').click()"><img id="e_amsam_photo_preview" style="display:none;width:100%;height:100%;object-fit:contain"><span id="e_amsam_photo_placeholder" style="color:var(--text-secondary);font-size:12px">Upload Amsam Chart</span></div><input type="file" id="e_amsam_photo_file" accept="image/*" style="display:none" onchange="previewAdminPhoto(this,'e_amsam_photo')"></div>
      </div>

      <div class="form-section-title">💑 Partner Expectations</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Qualification</label><input class="input" id="e_p_qualification" list="admin_partner_qual_list" placeholder="e.g. Any Degree"></div>
        <div class="form-row"><label class="input-label">Job Preference</label><input class="input" id="e_p_job" list="admin_partner_job_list" placeholder="e.g. Any, Govt Job"></div>
        <div class="form-row"><label class="input-label">Job Requirement</label><select class="input" id="e_p_job_req"><option>Optional</option><option>Must</option><option>Not Required</option></select></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Income Expectation</label><input class="input" id="e_p_income" placeholder="e.g. 30000"></div>
        <div class="form-row"><label class="input-label">Age From</label><input class="input" id="e_p_age_from" type="number" min="18" max="70"></div>
        <div class="form-row"><label class="input-label">Age To</label><input class="input" id="e_p_age_to" type="number" min="18" max="70"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Diet</label><select class="input" id="e_p_diet"><option>Vegetarian</option><option>Non-Vegetarian</option><option>Any</option></select></div>
        <div class="form-row"><label class="input-label">Marital Status</label><select class="input" id="e_p_marital"><option>Unmarried</option><option>Divorced</option><option>Widowed</option><option>Any</option></select></div>
        <div class="form-row"><label class="input-label">Horoscope Required?</label><select class="input" id="e_p_horoscope"><option>No</option><option>Yes</option><option>Not Applicable</option></select></div>
      </div>
      <div class="form-row"><label class="input-label">Caste Preference</label><input type="hidden" id="e_p_caste"><div id="e_p_caste_box" style="border:1px solid var(--border);border-radius:8px;padding:8px 10px;background:#faf9f7;max-height:160px;overflow-y:auto"></div></div>
      <div class="form-row" id="e_p_subcaste_wrap" style="display:none"><label class="input-label">Sub Caste Preference</label><input type="hidden" id="e_p_subcaste"><div id="e_p_subcaste_box" style="border:1px solid var(--border);border-radius:8px;padding:8px 10px;background:#faf9f7;max-height:140px;overflow-y:auto"></div></div>
      <div class="form-row"><label class="input-label">Other Requirements</label><textarea class="input" id="e_p_other" rows="2" placeholder="Any other expectations…" style="resize:vertical"></textarea></div>

      <div class="form-section-title">📞 Communication Details</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Permanent Address</label><textarea class="input" id="e_perm_addr" rows="2" placeholder="Door No, Street, Area…" style="resize:vertical"></textarea></div>
        <div class="form-row"><label class="input-label">Present Address</label><textarea class="input" id="e_present_addr" rows="2" placeholder="Door No, Street, Area, City, District, State" style="resize:vertical"></textarea></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Area</label><input class="input" id="e_present_area" placeholder="e.g. Anna Nagar"></div>
        <div class="form-row"><label class="input-label">City</label><input class="input" id="e_present_city" placeholder="e.g. Chennai"></div>
        <div class="form-row"><label class="input-label">District</label><select class="input" id="e_present_district"><option value="">— Select —</option></select></div>
        <div class="form-row"><label class="input-label">State</label><select class="input" id="e_present_state"><option value="">— Select —</option></select></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 12px">
        <div class="form-row"><label class="input-label">Contact Person</label><input class="input" id="e_contact_person" placeholder="Contact person name"></div>
        <div class="form-row"><label class="input-label">Contact Number</label><input class="input" id="e_alt_mobile" type="tel" maxlength="10" placeholder="Alternative mobile"></div>
        <div class="form-row"><label class="input-label">Email</label><input class="input" id="e_email" type="email" placeholder="Email address"></div>
      </div>
    </div>

    <div class="modal-footer" style="border-top:1px solid var(--border);padding:14px 22px">
      <button class="btn btn-outline" onclick="closeModal('editOverlay')">Cancel</button>
      <button class="btn btn-primary" id="editProfileBtn" style="min-width:140px" onclick="saveEdit()">Save Changes</button>
    </div>
  </div>
</div>

<!-- DELETE -->
<!-- VIEW PROFILE MODAL -->
<div class="modal-overlay" id="viewOverlay" style="align-items:flex-start;padding:20px 10px;overflow-y:auto">
  <div class="modal" style="max-width:800px;width:95%;border-radius:16px">
    <div class="modal-header" style="background:linear-gradient(135deg,#1e3a5f,#2563eb);padding:20px 22px">
      <span class="modal-title" style="color:#fff;font-size:16px">👁 View Profile</span>
      <button class="modal-close" onclick="closeModal('viewOverlay')" style="color:#fff">×</button>
    </div>
    <div id="viewContent" style="padding:20px 22px;max-height:80vh;overflow-y:auto"></div>
  </div>
</div>

<!-- OFFICE INFO MODAL -->
<div class="modal-overlay" id="officeInfoOverlay" style="align-items:flex-start;padding:20px 10px;overflow-y:auto">
  <div class="modal" style="max-width:680px;width:95%;border-radius:16px">
    <div class="modal-header" style="background:linear-gradient(135deg,#1a1a2e,#312e81);padding:18px 22px">
      <span class="modal-title" style="color:#fff;font-size:16px">🏢 Office Info</span>
      <button class="modal-close" onclick="closeModal('officeInfoOverlay')" style="color:#fff">×</button>
    </div>
    <div id="officeInfoContent" style="padding:20px 22px;max-height:80vh;overflow-y:auto"></div>
  </div>
</div>

<div class="modal-overlay" id="deleteOverlay">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">🗑️ Delete Profile</span>
      <button class="modal-close" onclick="closeModal('deleteOverlay')">×</button>
    </div>
    <div style="background:#fee2e2;border-radius:8px;padding:11px 14px;margin-bottom:16px;font-size:13px;color:#991b1b;display:flex;align-items:center;gap:8px">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      This profile will be moved to the deleted archive.
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
      <div class="form-row">
        <label class="input-label">CP ID</label>
        <input class="input" id="d_cpid" readonly style="background:#f3f4f6;font-weight:600">
      </div>
      <div class="form-row">
        <label class="input-label">Mobile</label>
        <input class="input" id="d_mobile" readonly style="background:#f3f4f6">
      </div>
    </div>
    <div class="form-row">
      <label class="input-label">Deleted By</label>
      <input class="input" id="d_admin" readonly style="background:#f0ede8;color:var(--text-primary);font-weight:500">
    </div>
    <div class="form-row">
      <label class="input-label">Reason for Deletion <span style="color:#e8624a">*</span></label>
      <input class="input" id="d_reason" maxlength="120" placeholder="Enter reason for deleting this profile…">
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('deleteOverlay')">Cancel</button>
      <button class="btn btn-danger" onclick="confirmDelete()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
        Delete Profile
      </button>
    </div>
  </div>
</div>

<!-- FOLLOW-UP -->
<div class="modal-overlay" id="followOverlay">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Schedule Follow-up</span>
      <button class="modal-close" onclick="closeModal('followOverlay')">×</button>
    </div>
    <div class="form-row">
      <label class="input-label">CP ID</label>
      <input class="input" id="fu_cpid" readonly style="background:#f3f4f6">
    </div>
    <div class="form-row">
      <label class="input-label">Member Name</label>
      <input class="input" id="fu_member" readonly style="background:#f3f4f6">
    </div>
    <div class="form-row">
      <label class="input-label">Follow-up Type</label>
      <select class="input" id="fu_type">
        <option value="">— Select Type —</option>
        <option value="data">📋 Data</option>
        <option value="payment">💳 Payment</option>
        <option value="not_interested">🚫 Not Interested</option>
        <option value="paid">✅ Paid</option>
      </select>
    </div>
    <div class="form-row">
      <label class="input-label">Follow-up Date <span style="font-weight:400;color:var(--text-secondary)">(today or future only)</span></label>
      <input class="input" id="fu_date" type="date">
    </div>
    <div class="form-row">
      <label class="input-label">Admin / Staff</label>
      <input class="input" id="fu_admin" readonly style="background:#f3f4f6;color:var(--text-primary);font-weight:500">
    </div>
    <div class="form-row">
      <label class="input-label">Notes</label>
      <input class="input" id="fu_reason" placeholder="Additional notes...">
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('followOverlay')">Cancel</button>
      <button class="btn btn-primary" onclick="saveFollow()">Save Follow-up</button>
    </div>
  </div>
</div>

<!-- BILL -->
<div class="modal-overlay" id="billOverlay">
  <div class="modal" style="width:480px;max-width:calc(100vw - 32px)">
    <div class="modal-header">
      <span class="modal-title" id="billModalTitle">💳 Create Bill</span>
      <button class="modal-close" onclick="closeModal('billOverlay')">×</button>
    </div>

    <!-- Auto-filled readonly fields -->
    <div style="background:#f8f7f5;border-radius:10px;padding:13px 15px;margin-bottom:14px">
      <div style="font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px">Member Info</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
        <div>
          <div style="font-size:10.5px;color:var(--text-secondary);margin-bottom:2px">CP ID</div>
          <input class="input" id="bill_cpid" readonly style="background:#fff;font-weight:700;font-size:13px;margin-bottom:0;padding:6px 10px">
        </div>
        <div>
          <div style="font-size:10.5px;color:var(--text-secondary);margin-bottom:2px">Mobile</div>
          <input class="input" id="bill_mobile" readonly style="background:#fff;font-size:13px;margin-bottom:0;padding:6px 10px">
        </div>
        <div>
          <div style="font-size:10.5px;color:var(--text-secondary);margin-bottom:2px">Name</div>
          <input class="input" id="bill_name" readonly style="background:#fff;font-size:13px;margin-bottom:0;padding:6px 10px">
        </div>
      </div>
    </div>

    <!-- Plan fields -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
      <div class="form-row">
        <label class="input-label">Plan Name <span style="color:#e8624a">*</span></label>
        <select class="input" id="bill_planname" onchange="onBillPlanChange()">
          <option value="">— Select Plan —</option>
        </select>
      </div>
      <div class="form-row">
        <label class="input-label">Plan Type</label>
        <input class="input" id="bill_plantype" readonly style="background:#f3f4f6;font-size:13px">
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
      <div class="form-row">
        <label class="input-label">Billed Amount (₹) <span style="color:#e8624a">*</span></label>
        <input class="input" id="bill_amount" type="number" min="0" placeholder="Enter amount">
      </div>
      <div class="form-row">
        <label class="input-label">Payment Type <span style="color:#e8624a">*</span></label>
        <select class="input" id="bill_type">
          <option value="">— Select —</option>
          <option value="Free">🆓 Free</option>
          <option value="Cash">💵 Cash</option>
          <option value="Online">🌐 Online</option>
        </select>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px">
      <div class="form-row">
        <label class="input-label">Billed Date</label>
        <input class="input" id="bill_date" type="date" style="margin-bottom:0">
      </div>
      <div class="form-row">
        <label class="input-label">Expiry Date</label>
        <input class="input" id="bill_expiry" type="date" style="margin-bottom:0">
      </div>
    </div>

    <div class="form-row" style="margin-top:10px">
      <label class="input-label">Billed By</label>
      <input class="input" id="bill_billedby" readonly style="background:#f0ede8;color:var(--text-primary);font-weight:500;margin-bottom:0">
    </div>

    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('billOverlay')">Cancel</button>
      <button class="btn btn-primary" id="billSaveBtn" onclick="saveBill()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save Bill
      </button>
    </div>
  </div>
</div>

<!-- ADMIN PAYMENT PAGE MODAL -->
<div class="modal-overlay" id="adminPayOverlay">
  <div class="modal" style="width:600px;max-width:calc(100vw - 32px);max-height:90vh;overflow-y:auto">
    <div class="modal-header" style="background:linear-gradient(135deg,#1e293b,#334155);border:none">
      <span class="modal-title" style="color:#fff" id="adminPayTitle">💳 Payment</span>
      <button class="modal-close" onclick="closeModal('adminPayOverlay')" style="color:#fff">×</button>
    </div>
    <div style="padding:18px">
      <div id="adminPayProfile" style="display:flex;align-items:center;gap:12px;background:#f8fafc;border-radius:10px;padding:12px 16px;margin-bottom:18px;border:1px solid #e2e8f0"></div>
      <div id="adminPayStep1">
        <div style="font-weight:700;font-size:14px;margin-bottom:12px">Select a Plan</div>
        <div id="adminPayPlans" style="display:flex;flex-direction:column;gap:10px"></div>
        <div id="adminPayNowWrap" style="display:none;margin-top:14px">
          <button id="adminPayNowBtn" onclick="adminPayConfirm()" class="btn btn-primary" style="width:100%;padding:13px;font-size:15px;font-weight:700;border-radius:10px">Pay Now</button>
        </div>
      </div>
      <div id="adminPayStep2" style="display:none">
        <div id="adminPaySelectedPlan" style="background:linear-gradient(135deg,#1e293b,#334155);border-radius:10px;padding:16px 18px;margin-bottom:16px;color:#fff"></div>
        <div style="font-weight:700;font-size:14px;margin-bottom:12px">Select Payment Method & Create Bill</div>
        <div id="adminPayOpts" style="display:flex;flex-direction:column;gap:10px"></div>
        <button class="btn btn-outline" onclick="adminPayBackToPlans()" style="width:100%;margin-top:14px">← Back to Plans</button>
      </div>
      <div id="adminPayStep3" style="display:none;text-align:center;padding:30px 10px">
        <div style="font-size:48px;margin-bottom:12px">🎉</div>
        <div style="font-size:18px;font-weight:700;margin-bottom:6px">Bill Created!</div>
        <div id="adminPayDoneMsg" style="font-size:13px;color:#64748b;line-height:1.6"></div>
        <button class="btn btn-primary" onclick="closeModal('adminPayOverlay');render();postRender();" style="margin-top:18px;padding:10px 30px">Done</button>
      </div>
    </div>
  </div>
</div>

<!-- ADD ADMIN -->
<div class="modal-overlay" id="addAdminOverlay">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Add Admin Account</span>
      <button class="modal-close" onclick="closeModal('addAdminOverlay')">×</button>
    </div>
    <div class="form-row">
      <label class="input-label">Full Name</label>
      <input class="input" id="aa_name" placeholder="Staff member name">
    </div>
    <div class="form-row">
      <label class="input-label">Username</label>
      <input class="input" id="aa_username" placeholder="e.g. ravi.staff" autocomplete="off">
    </div>
    <div class="form-row">
      <label class="input-label">Mobile Number</label>
      <input class="input" id="aa_mobile" placeholder="10-digit mobile" type="tel">
    </div>
    <div class="form-row">
      <label class="input-label">Role</label>
      <select class="input" id="aa_role">
        <option value="staff">Staff</option>
        <option value="manager">Manager</option>
        <option value="super">Super Admin</option>
      </select>
    </div>
    <div class="form-row">
      <label class="input-label">Password</label>
      <div style="position:relative">
        <input class="input" id="aa_password" type="password" placeholder="Set password" autocomplete="new-password" style="padding-right:40px;margin-bottom:0">
        <button type="button" class="eye-btn" style="position:absolute;right:10px;top:50%;transform:translateY(-50%)" onclick="togglePwd('aa_password', this)">👁</button>
      </div>
      <div id="aa_pwd_strength" style="margin-top:5px;font-size:11px;color:var(--text-secondary)"></div>
    </div>
    <div class="form-row">
      <label class="input-label">Confirm Password</label>
      <input class="input" id="aa_confirm" type="password" placeholder="Re-enter password">
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('addAdminOverlay')">Cancel</button>
      <button class="btn btn-primary" onclick="saveAddAdmin()">Create Account</button>
    </div>
  </div>
</div>

<!-- EDIT ADMIN -->
<div class="modal-overlay" id="editAdminOverlay">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Edit Admin Account</span>
      <button class="modal-close" onclick="closeModal('editAdminOverlay')">×</button>
    </div>
    <div class="form-row">
      <label class="input-label">Full Name</label>
      <input class="input" id="ea_name">
    </div>
    <div class="form-row">
      <label class="input-label">Username</label>
      <input class="input" id="ea_username">
    </div>
    <div class="form-row">
      <label class="input-label">Mobile Number</label>
      <input class="input" id="ea_mobile" type="tel">
    </div>
    <div class="form-row">
      <label class="input-label">Role</label>
      <select class="input" id="ea_role">
        <option value="staff">Staff</option>
        <option value="manager">Manager</option>
        <option value="super">Super Admin</option>
      </select>
    </div>
    <div class="form-row">
      <label class="input-label">New Password <span style="font-weight:400;color:var(--text-secondary)">(leave blank to keep current)</span></label>
      <div style="position:relative">
        <input class="input" id="ea_password" type="password" placeholder="New password" style="padding-right:40px;margin-bottom:0">
        <button type="button" class="eye-btn" style="position:absolute;right:10px;top:50%;transform:translateY(-50%)" onclick="togglePwd('ea_password', this)">👁</button>
      </div>

      
    </div>
    <div class="form-row">
      <label class="input-label">Status</label>
      <select class="input" id="ea_status">
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('editAdminOverlay')">Cancel</button>
      <button class="btn btn-primary" onclick="saveEditAdmin()">Save Changes</button>
    </div>
  </div>
</div>

<!-- DELETE ADMIN CONFIRM -->
<div class="modal-overlay" id="deleteAdminOverlay">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Remove Admin Account</span>
      <button class="modal-close" onclick="closeModal('deleteAdminOverlay')">×</button>
    </div>
    <div style="background:#fee2e2;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:#991b1b;">
      ⚠️ This will permanently remove the admin account. They will lose login access immediately.
    </div>
    <p style="font-size:13.5px;color:var(--text-secondary)">Removing: <strong id="da_name_display" style="color:var(--text-primary)"></strong></p>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('deleteAdminOverlay')">Cancel</button>
      <button class="btn btn-danger" onclick="confirmDeleteAdmin()">Remove Account</button>
    </div>
  </div>
</div>


<!-- EDIT FOLLOW-UP DATE -->
<div class="modal-overlay" id="editFollowOverlay">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Reschedule Follow-up</span>
      <button class="modal-close" onclick="closeModal('editFollowOverlay')">×</button>
    </div>
    <div class="form-row">
      <label class="input-label">CP ID</label>
      <input class="input" id="ef_cpid" readonly style="background:#f3f4f6">
    </div>
    <div class="form-row">
      <label class="input-label">Member</label>
      <input class="input" id="ef_member" readonly style="background:#f3f4f6">
    </div>
    <div class="form-row">
      <label class="input-label">Follow-up Type</label>
      <select class="input" id="ef_type">
        <option value="data">📋 Data</option>
        <option value="payment">💳 Payment</option>
        <option value="not_interested">🚫 Not Interested</option>
        <option value="paid">✅ Paid</option>
      </select>
    </div>
    <div class="form-row">
      <label class="input-label">Next Follow-up Date <span style="font-weight:400;color:var(--text-secondary)">(today or future)</span></label>
      <input class="input" id="ef_date" type="date">
    </div>
    <div class="form-row">
      <label class="input-label">Notes</label>
      <input class="input" id="ef_reason" placeholder="Update notes...">
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('editFollowOverlay')">Cancel</button>
      <button class="btn btn-primary" onclick="saveEditFollow()">Save</button>
    </div>
  </div>
</div>

</div><!-- /appShell -->

<div id="toast" style="position:fixed;bottom:24px;right:24px;background:#1a1a2e;color:#fff;padding:12px 20px;border-radius:10px;font-size:13.5px;font-weight:500;box-shadow:0 8px 30px rgba(0,0,0,0.2);transform:translateY(80px);opacity:0;transition:all 0.3s;z-index:999;display:flex;align-items:center;gap:8px;"></div>

<!-- SHARE PAYMENT LINK MODAL -->
<div class="modal-overlay" id="sharePayOverlay">
  <div class="modal" style="max-width:500px">
    <div class="modal-header" style="background:linear-gradient(135deg,#1a1a2e,#2d2d5e)">
      <span class="modal-title" style="color:#fff">📤 Share Payment Link</span>
      <button class="modal-close" onclick="closeModal('sharePayOverlay')" style="color:rgba(255,255,255,.6)">×</button>
    </div>
    <div class="modal-body" style="padding:22px">
      <div id="shareMemberInfo" style="background:#f8f7f5;border-radius:9px;padding:12px 16px;margin-bottom:18px;display:flex;align-items:center;gap:10px">
        <div id="shareAvatar" style="width:38px;height:38px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0">?</div>
        <div>
          <div id="shareName" style="font-weight:700;font-size:14px"></div>
          <div id="shareMeta" style="font-size:12px;color:var(--text-secondary);margin-top:1px"></div>
        </div>
      </div>
      <label class="input-label">Payment Page Link</label>
      <div style="display:flex;gap:8px;margin-bottom:14px">
        <input class="input" id="sharePayLink" readonly style="font-family:monospace;font-size:12px;background:#f3f4f6;flex:1;color:var(--text-primary)">
        <button class="btn btn-primary btn-sm" onclick="copyShareLink()" style="white-space:nowrap;padding:8px 14px">📋 Copy</button>
      </div>
      <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:11px 14px;font-size:12.5px;color:#2563eb;margin-bottom:18px;line-height:1.7">
        <strong>When member opens this link:</strong><br>
        → Member logs in with their registered mobile number<br>
        → Payment page opens automatically showing active plans &amp; payment options<br>
        → Member selects a plan and completes payment
      </div>
      <div style="font-weight:600;font-size:11.5px;margin-bottom:10px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.08em">Share via</div>
      <div style="display:flex;gap:9px;flex-wrap:wrap">
        <button onclick="shareViaWhatsApp()" style="background:#25D366;color:#fff;border:none;border-radius:9px;font-size:13px;font-weight:600;flex:1;min-width:120px;padding:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:opacity .15s" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
          WhatsApp
        </button>
        <button onclick="shareViaSMS()" style="background:#3b82f6;color:#fff;border:none;border-radius:9px;font-size:13px;font-weight:600;flex:1;min-width:120px;padding:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:opacity .15s" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
          💬 SMS
        </button>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('sharePayOverlay')">Close</button>
    </div>
  </div>
</div>

<!-- VIEW BILL DETAILS MODAL -->
<div class="modal-overlay" id="viewBillOverlay">
  <div class="modal" style="max-width:560px">
    <div class="modal-header">
      <span class="modal-title" id="viewBillTitle">👁 Bill Details</span>
      <button class="modal-close" onclick="closeModal('viewBillOverlay')">×</button>
    </div>
    <div class="modal-body" id="viewBillBody" style="padding:0">
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('viewBillOverlay')">Close</button>
      <button class="btn btn-primary" id="viewBillPrintBtn" onclick="">🖨️ Print Bill</button>
      <button class="btn btn-green" id="viewBillEditBtn" onclick="">✏️ Edit Bill</button>
    </div>
  <!-- ADD PROFILE SECTION -->
  <div class="section" id="addProfileSection">
    <div class="page-header">
      <div>
        <div class="page-title">Add Profile</div>
        <div class="page-subtitle">Create a new member profile</div>
      </div>
    </div>
    <div style="max-width:900px">
      <div id="apResult" style="display:none;margin-bottom:20px"></div>
      <div style="background:white;border-radius:16px;box-shadow:0 2px 20px rgba(139,0,0,0.06);border:1px solid rgba(196,30,58,0.1);overflow:hidden">
        <!-- Header -->
        <div style="background:linear-gradient(135deg,#8B0000,#C41E3A);padding:20px 28px;display:flex;align-items:center;gap:14px">
          <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">👤</div>
          <div>
            <h2 style="margin:0;color:white;font-family:'DM Serif Display',serif;font-size:1.2rem">New Member Registration</h2>
            <div style="font-size:12px;color:rgba(255,255,255,0.7);margin-top:2px">Fields marked <span style="color:#fca5a5">*</span> are required</div>
          </div>
        </div>
        <div style="padding:24px 28px">
          <!-- Photos -->
          <div style="background:#FFF5F5;border-radius:10px;padding:14px 18px;border:1px solid rgba(196,30,58,0.12);margin-bottom:20px">
            <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#8B0000;margin-bottom:12px">📸 Profile Photos</div>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
              <div style="flex:1;min-width:120px"><div style="height:100px;border:2px dashed #D4A0A8;border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#FFFAF9;overflow:hidden;font-size:11px;color:#9A7080" onclick="this.querySelector('input').click()">Photo 1<input type="file" accept="image/*" style="display:none" onchange="apPhotoPreview(this,'ap_p1')"></div></div>
              <div style="flex:1;min-width:120px"><div style="height:100px;border:2px dashed #D4A0A8;border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#FFFAF9;overflow:hidden;font-size:11px;color:#9A7080" onclick="this.querySelector('input').click()">Photo 2<input type="file" accept="image/*" style="display:none" onchange="apPhotoPreview(this,'ap_p2')"></div></div>
              <div style="flex:1;min-width:120px"><div style="height:100px;border:2px dashed #D4A0A8;border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#FFFAF9;overflow:hidden;font-size:11px;color:#9A7080" onclick="this.querySelector('input').click()">Photo 3<input type="file" accept="image/*" style="display:none" onchange="apPhotoPreview(this,'ap_p3')"></div></div>
            </div>
          </div>
          <!-- Personal Details -->
          <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:white;background:linear-gradient(90deg,#8B0000,#C41E3A 60%,transparent);padding:7px 14px;border-radius:6px;margin-bottom:14px">👤 Personal Details</div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Name <span style="color:#C41E3A">*</span></label><input class="input" id="ap_name" placeholder="Full name" style="border-color:#D4A0A8;background:#FFFAF9"></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Gender <span style="color:#C41E3A">*</span></label><select class="input" id="ap_gender" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option><option>Male</option><option>Female</option></select></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Date of Birth <span id="ap_age_display" style="font-weight:700;font-size:11px;margin-left:4px"></span></label><input class="input" id="ap_dob" type="date" style="border-color:#D4A0A8;background:#FFFAF9"></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Age</label><input class="input" id="ap_age_input" readonly style="border-color:#D4A0A8;background:#f0fdf4;font-weight:700;text-align:center;font-size:15px"></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Place of Birth</label><input class="input" id="ap_place_birth" placeholder="e.g. Puducherry" style="border-color:#D4A0A8;background:#FFFAF9"></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Nativity</label><input class="input" id="ap_nativity" placeholder="Town & District" style="border-color:#D4A0A8;background:#FFFAF9"></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Present Country</label><select class="input" id="ap_workplace" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option></select></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Area</label><input class="input" id="ap_present_area" placeholder="e.g. Anna Nagar" style="border-color:#D4A0A8;background:#FFFAF9"></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">City</label><input class="input" id="ap_present_city" placeholder="e.g. Chennai" style="border-color:#D4A0A8;background:#FFFAF9"></div>
            <div style="margin-bottom:13px"></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">State</label><select class="input" id="ap_present_state" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option></select></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">District</label><select class="input" id="ap_present_district" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option></select></div>
            <div></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Mobile <span style="color:#C41E3A">*</span></label><input class="input" id="ap_mobile" type="tel" maxlength="10" placeholder="10-digit" style="border-color:#D4A0A8;background:#FFFAF9"></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Mother Tongue</label><select class="input" id="ap_tongue" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option><option>Tamil</option><option>Telugu</option><option>Malayalam</option><option>Kannada</option><option>Hindi</option><option>English</option></select></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Marital Status</label><select class="input" id="ap_marital" style="border-color:#D4A0A8;background:#FFFAF9"><option>Unmarried</option><option>Divorced</option><option>Widowed</option><option>Separated</option></select></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Nationality</label><select class="input" id="ap_nationality" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option></select></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Own House</label><select class="input" id="ap_own_house" style="border-color:#D4A0A8;background:#FFFAF9"><option>Yes</option><option>No</option></select></div>
            <div></div><div></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Blood Group</label><select class="input" id="ap_blood" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>O+</option><option>O-</option><option>AB+</option><option>AB-</option></select></div>
          </div>
          <!-- Physical Attributes -->
          <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:white;background:linear-gradient(90deg,#8B0000,#C41E3A 60%,transparent);padding:7px 14px;border-radius:6px;margin:4px 0 14px">⚖️ Physical Attributes</div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Height</label><select class="input" id="ap_height" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option><option>4ft 5in</option><option>4ft 6in</option><option>4ft 7in</option><option>4ft 8in</option><option>4ft 9in</option><option>4ft 10in</option><option>4ft 11in</option><option>5ft 0in</option><option>5ft 1in</option><option>5ft 2in</option><option>5ft 3in</option><option>5ft 4in</option><option>5ft 5in</option><option>5ft 6in</option><option>5ft 7in</option><option>5ft 8in</option><option>5ft 9in</option><option>5ft 10in</option><option>5ft 11in</option><option>6ft 0in</option><option>6ft 1in</option><option>6ft 2in</option><option>6ft 3in</option><option>6ft 4in</option><option>6ft 5in</option></select></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Weight</label><select class="input" id="ap_weight" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option><option>40 kg</option><option>42 kg</option><option>45 kg</option><option>48 kg</option><option>50 kg</option><option>52 kg</option><option>55 kg</option><option>56 kg</option><option>58 kg</option><option>60 kg</option><option>62 kg</option><option>63 kg</option><option>65 kg</option><option>67 kg</option><option>68 kg</option><option>69 kg</option><option>70 kg</option><option>71 kg</option><option>72 kg</option><option>73 kg</option><option>75 kg</option><option>78 kg</option><option>80 kg</option><option>82 kg</option><option>85 kg</option><option>88 kg</option><option>90 kg</option><option>95 kg</option><option>100 kg</option><option>105 kg</option><option>110 kg</option></select></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Diet</label><select class="input" id="ap_diet" style="border-color:#D4A0A8;background:#FFFAF9"><option>Vegetarian</option><option>Non-Vegetarian</option><option>Eggetarian</option></select></div>
          </div>
          <!-- Education & Occupation -->
          <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:white;background:linear-gradient(90deg,#8B0000,#C41E3A 60%,transparent);padding:7px 14px;border-radius:6px;margin:4px 0 14px">🎓 Education & Occupation</div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Qualification</label><select class="input" id="ap_qual" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option><optgroup label="Below 10th"><option>Below 10th</option><option>10th / SSLC</option></optgroup><optgroup label="Higher Secondary"><option>12th / HSC</option><option>ITI</option><option>Diploma</option></optgroup><optgroup label="Undergraduate"><option>B.A</option><option>B.Sc</option><option>B.Com</option><option>B.E / B.Tech</option><option>B.B.A</option><option>B.C.A</option><option>B.Ed</option><option>B.L / L.L.B</option><option>B.Arch</option><option>B.Pharm</option><option>B.D.S</option><option>M.B.B.S</option><option>B.V.Sc</option><option>B.P.T</option><option>B.Sc (Nursing)</option><option>B.S.W</option><option>B.F.A</option><option>B.Des</option></optgroup><optgroup label="Postgraduate"><option>M.A</option><option>M.Sc</option><option>M.Com</option><option>M.E / M.Tech</option><option>M.B.A</option><option>M.C.A</option><option>M.Ed</option><option>M.L / L.L.M</option><option>M.Pharm</option><option>M.D</option><option>M.S (Medical)</option><option>M.D.S</option><option>M.P.T</option><option>M.S.W</option><option>M.Des</option></optgroup><optgroup label="Doctorate / Research"><option>M.Phil</option><option>Ph.D</option><option>D.M</option><option>D.Litt</option></optgroup><optgroup label="Professional / Other"><option>C.A</option><option>C.S</option><option>I.C.W.A / C.M.A</option><option>C.F.A</option><option>I.A.S / I.P.S / I.F.S</option><option>Others</option></optgroup></select></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Occupation</label><select class="input" id="ap_job" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option><optgroup label="Government / Public Sector"><option>Central Govt Employee</option><option>State Govt Employee</option><option>PSU Employee</option><option>Defense - Army</option><option>Defense - Navy</option><option>Defense - Air Force</option><option>Police / CRPF / BSF</option><option>IAS / IPS / IFS Officer</option><option>Railway Employee</option><option>Postal Employee</option><option>TNPSC Group Service</option></optgroup><optgroup label="IT / Software"><option>Software Engineer</option><option>Software Developer</option><option>Data Analyst</option><option>Data Scientist</option><option>System Administrator</option><option>Network Engineer</option><option>Web Developer</option><option>UI/UX Designer</option><option>IT Manager</option><option>Cyber Security Analyst</option></optgroup><optgroup label="Engineering / Manufacturing"><option>Mechanical Engineer</option><option>Civil Engineer</option><option>Electrical Engineer</option><option>Electronics Engineer</option><option>Chemical Engineer</option><option>Production Engineer</option><option>Site Engineer</option><option>Quality Engineer</option><option>Project Manager</option></optgroup><optgroup label="Medical / Healthcare"><option>Doctor</option><option>Surgeon</option><option>Dentist</option><option>Pharmacist</option><option>Nurse</option><option>Physiotherapist</option><option>Lab Technician</option><option>Ayurveda / Siddha / Homeopathy</option></optgroup><optgroup label="Education / Teaching"><option>Professor</option><option>Lecturer</option><option>School Teacher</option><option>Private Tutor</option><option>Research Scholar</option></optgroup><optgroup label="Banking / Finance"><option>Bank Manager</option><option>Bank Employee</option><option>Chartered Accountant</option><option>Financial Analyst</option><option>Insurance Agent</option><option>Auditor</option><option>Tax Consultant</option></optgroup><optgroup label="Legal"><option>Advocate / Lawyer</option><option>Judge</option><option>Legal Advisor</option><option>Notary</option></optgroup><optgroup label="Business / Entrepreneurship"><option>Business Owner</option><option>Shopkeeper</option><option>Trader / Merchant</option><option>Real Estate Business</option><option>Exporter / Importer</option><option>Contractor</option><option>Freelancer</option><option>Startup Founder</option></optgroup><optgroup label="Agriculture / Farming"><option>Farmer / Agriculturist</option><option>Dairy Farmer</option><option>Plantation Owner</option><option>Agricultural Officer</option></optgroup><optgroup label="Skilled Trades"><option>Electrician</option><option>Plumber</option><option>Carpenter</option><option>Welder</option><option>Mechanic</option><option>Tailor</option><option>Goldsmith</option><option>Mason</option></optgroup><optgroup label="Media / Creative"><option>Journalist</option><option>Content Writer</option><option>Photographer</option><option>Graphic Designer</option><option>Film / TV Professional</option></optgroup><optgroup label="Abroad / NRI"><option>Working in Gulf</option><option>Working in USA</option><option>Working in UK</option><option>Working in Canada</option><option>Working in Australia</option><option>Working in Singapore</option><option>Working in Malaysia</option><option>Merchant Navy</option></optgroup><optgroup label="Other"><option>Private Company Employee</option><option>Supervisor / Foreman</option><option>Driver</option><option>Chef / Cook</option><option>Security Guard</option><option>Home Maker</option><option>Retired</option><option>Student</option><option>Not Employed</option><option>Others</option></optgroup></select></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Monthly Income (₹)</label><input class="input" id="ap_income" placeholder="e.g. 35000" style="border-color:#D4A0A8;background:#FFFAF9"></div>
          </div>
          <!-- Astrology & Caste -->
          <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:white;background:linear-gradient(90deg,#8B0000,#C41E3A 60%,transparent);padding:7px 14px;border-radius:6px;margin:4px 0 14px">🪐 Astrology & Religion</div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Caste</label><select class="input" id="ap_caste" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option><option>Adi Dravidar</option><option>Agamudayar</option><option>Brahmin</option><option>Chettiar</option><option>Devendra Kula Vellalar</option><option>Gounder</option><option>Gramani</option><option>Iyengar</option><option>Iyer</option><option>Kallar</option><option>Kulalar</option><option>Labbai</option><option>Maravar</option><option>Maruthuvar</option><option>Mudaliar</option><option>Nadar</option><option>Naicker</option><option>Naidu</option><option>Nair</option><option>Parvatha Rajakulam</option><option>Pillai</option><option>Reddiar</option><option>Roman Catholic</option><option>Thevar</option><option>Udayar</option><option>Vanniyar</option><option>Vellalar</option><option>Vishwakarma</option><option>Yadav</option><option>Others</option></select></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Sub Caste</label><input class="input" id="ap_subcaste" placeholder="Sub caste" style="border-color:#D4A0A8;background:#FFFAF9"></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Star / Nakshatra</label><select class="input" id="ap_star" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option><option>Ashwini</option><option>Bharani</option><option>Karthigai</option><option>Rohini</option><option>Mirigasirisham</option><option>Thiruvathirai</option><option>Punarpoosam</option><option>Poosam</option><option>Ayilyam</option><option>Makam</option><option>Pooram</option><option>Uthiram</option><option>Hastham</option><option>Chithirai</option><option>Swathi</option><option>Visakam</option><option>Anusham</option><option>Kettai</option><option>Moolam</option><option>Pooradam</option><option>Uthradam</option><option>Thiruvonam</option><option>Avittam</option><option>Sadhayam</option><option>Puratathi</option><option>Uthirattathi</option><option>Revathi</option></select></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Raasi</label><select class="input" id="ap_raasi" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option><option>Mesham</option><option>Rishabam</option><option>Midhunam</option><option>Kadagam</option><option>Simham</option><option>Kanni</option><option>Thulam</option><option>Viruchigam</option><option>Dhanusu</option><option>Makaram</option><option>Kumbam</option><option>Meenam</option></select></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Lagnam</label><select class="input" id="ap_lagnam" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option><option>Mesham</option><option>Rishabam</option><option>Midhunam</option><option>Kadagam</option><option>Simham</option><option>Kanni</option><option>Thulam</option><option>Viruchigam</option><option>Dhanusu</option><option>Makaram</option><option>Kumbam</option><option>Meenam</option></select></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Dosham</label><select class="input" id="ap_dosham" style="border-color:#D4A0A8;background:#FFFAF9"><option>No</option><option>Yes</option><option>Partial</option></select></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px;display:none" id="ap_dosham_type_wrap"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Dosham Type</label><select class="input" id="ap_dosham_type" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select Dosham Type —</option></select></div>
            <div></div><div></div>
          </div>
          <!-- Plan Selection -->
          <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:white;background:linear-gradient(90deg,#8B0000,#C41E3A 60%,transparent);padding:7px 14px;border-radius:6px;margin:4px 0 14px">🛒 Select Plan</div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:20px">
            <div class="ap-plan-card" data-plan="free" onclick="apSelectPlan('free')" style="border:2.5px solid #E0C0C8;border-radius:12px;padding:16px 14px;cursor:pointer;background:#FFFAF9;transition:all 0.2s;position:relative">
              <div style="font-size:24px;margin-bottom:8px">🆓</div>
              <div style="font-family:'DM Serif Display',serif;font-size:1rem;font-weight:700;color:#5A0010;margin-bottom:3px">Free</div>
              <div style="font-size:1.3rem;font-weight:800;color:#78716c;line-height:1">₹0</div>
              <div style="font-size:0.7rem;color:#9A7080;margin-bottom:10px">3 months</div>
              <div style="font-size:0.75rem;color:#6A3040">✦ Basic listing<br>✦ 3 photos<br>✦ Limited contacts</div>
            </div>
            <div class="ap-plan-card" data-plan="silver" onclick="apSelectPlan('silver')" style="border:2.5px solid #E0C0C8;border-radius:12px;padding:16px 14px;cursor:pointer;background:#FFFAF9;transition:all 0.2s;position:relative">
              <div style="font-size:24px;margin-bottom:8px">🥈</div>
              <div style="font-family:'DM Serif Display',serif;font-size:1rem;font-weight:700;color:#5A0010;margin-bottom:3px">Silver</div>
              <div style="font-size:1.3rem;font-weight:800;color:#64748b;line-height:1">₹999</div>
              <div style="font-size:0.7rem;color:#9A7080;margin-bottom:10px">6 months</div>
              <div style="font-size:0.75rem;color:#6A3040">✦ Enhanced listing<br>✦ 5 contacts/day<br>✦ Priority support</div>
            </div>
            <div class="ap-plan-card" data-plan="gold" onclick="apSelectPlan('gold')" style="border:2.5px solid #E0C0C8;border-radius:12px;padding:16px 14px;cursor:pointer;background:#FFFAF9;transition:all 0.2s;position:relative">
              <div style="font-size:24px;margin-bottom:8px">🥇</div>
              <div style="font-family:'DM Serif Display',serif;font-size:1rem;font-weight:700;color:#5A0010;margin-bottom:3px">Gold</div>
              <div style="font-size:1.3rem;font-weight:800;color:#C9913A;line-height:1">₹1,999</div>
              <div style="font-size:0.7rem;color:#9A7080;margin-bottom:10px">1 year</div>
              <div style="font-size:0.75rem;color:#6A3040">✦ Top listing<br>✦ Unlimited contacts<br>✦ WhatsApp alerts</div>
            </div>
            <div class="ap-plan-card" data-plan="premium" onclick="apSelectPlan('premium')" style="border:2.5px solid #E0C0C8;border-radius:12px;padding:16px 14px;cursor:pointer;background:#FFFAF9;transition:all 0.2s;position:relative">
              <div style="font-size:24px;margin-bottom:8px">💎</div>
              <div style="font-family:'DM Serif Display',serif;font-size:1rem;font-weight:700;color:#5A0010;margin-bottom:3px">Premium</div>
              <div style="font-size:1.3rem;font-weight:800;color:#8B0000;line-height:1">₹2,999</div>
              <div style="font-size:0.7rem;color:#9A7080;margin-bottom:10px">2 years</div>
              <div style="font-size:0.75rem;color:#6A3040">✦ VIP listing<br>✦ Dedicated manager<br>✦ All features</div>
            </div>
          </div>
          <input type="hidden" id="ap_plan" value="free">
          <!-- Footer -->
          <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid rgba(196,30,58,0.12)">
            <button class="btn btn-outline" onclick="apReset()">↺ Reset</button>
            <button class="btn btn-primary" style="background:linear-gradient(135deg,#8B0000,#C41E3A);border-color:#8B0000;min-width:140px" onclick="apSubmit()">✦ Add Profile</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ADD ORDER SECTION -->
  <div class="section" id="addOrderSection">
    <div class="page-header">
      <div>
        <div class="page-title">Add Order</div>
        <div class="page-subtitle">Create a new plan order for a member</div>
      </div>
    </div>
    <div style="max-width:800px">
      <div id="aoResult" style="display:none;margin-bottom:20px"></div>
      <div style="background:white;border-radius:16px;box-shadow:0 2px 20px rgba(139,0,0,0.06);border:1px solid rgba(196,30,58,0.1);overflow:hidden">
        <div style="background:linear-gradient(135deg,#8B0000,#C41E3A);padding:20px 28px;display:flex;align-items:center;gap:14px">
          <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">🛒</div>
          <div>
            <h2 style="margin:0;color:white;font-family:'DM Serif Display',serif;font-size:1.2rem">New Order</h2>
            <div style="font-size:12px;color:rgba(255,255,255,0.7);margin-top:2px">Select a member and plan to create an order</div>
          </div>
        </div>
        <div style="padding:24px 28px">
          <!-- Member Search -->
          <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:white;background:linear-gradient(90deg,#8B0000,#C41E3A 60%,transparent);padding:7px 14px;border-radius:6px;margin-bottom:14px">👤 Member Details</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">CP ID / Mobile <span style="color:#C41E3A">*</span></label><div style="display:flex;gap:8px"><input class="input" id="ao_cpid" placeholder="e.g. CP1001 or mobile" style="border-color:#D4A0A8;background:#FFFAF9;flex:1;margin:0"><button class="btn btn-outline btn-sm" onclick="aoFetchMember()" style="flex-shrink:0;border-color:#D4A0A8">🔍 Find</button></div></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Member Name</label><input class="input" id="ao_name" placeholder="Auto-filled after search" readonly style="border-color:#D4A0A8;background:#f7f5f1"></div>
          </div>
          <!-- Plan Selection -->
          <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:white;background:linear-gradient(90deg,#8B0000,#C41E3A 60%,transparent);padding:7px 14px;border-radius:6px;margin:4px 0 14px">🛒 Select Plan</div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:20px">
            <div class="ao-plan-card" data-plan="free" onclick="aoSelectPlan(this,'free','₹0','3 months')" style="border:2.5px solid #E0C0C8;border-radius:12px;padding:14px;cursor:pointer;background:#FFFAF9;transition:all 0.2s;text-align:center">
              <div style="font-size:22px;margin-bottom:6px">🆓</div><div style="font-weight:700;color:#5A0010;font-size:0.9rem">Free</div><div style="font-size:1.1rem;font-weight:800;color:#78716c">₹0</div><div style="font-size:0.68rem;color:#9A7080">3 months</div>
            </div>
            <div class="ao-plan-card" data-plan="silver" onclick="aoSelectPlan(this,'silver','₹999','6 months')" style="border:2.5px solid #E0C0C8;border-radius:12px;padding:14px;cursor:pointer;background:#FFFAF9;transition:all 0.2s;text-align:center">
              <div style="font-size:22px;margin-bottom:6px">🥈</div><div style="font-weight:700;color:#5A0010;font-size:0.9rem">Silver</div><div style="font-size:1.1rem;font-weight:800;color:#64748b">₹999</div><div style="font-size:0.68rem;color:#9A7080">6 months</div>
            </div>
            <div class="ao-plan-card" data-plan="gold" onclick="aoSelectPlan(this,'gold','₹1,999','1 year')" style="border:2.5px solid #E0C0C8;border-radius:12px;padding:14px;cursor:pointer;background:#FFFAF9;transition:all 0.2s;text-align:center">
              <div style="font-size:22px;margin-bottom:6px">🥇</div><div style="font-weight:700;color:#5A0010;font-size:0.9rem">Gold</div><div style="font-size:1.1rem;font-weight:800;color:#C9913A">₹1,999</div><div style="font-size:0.68rem;color:#9A7080">1 year</div>
            </div>
            <div class="ao-plan-card" data-plan="premium" onclick="aoSelectPlan(this,'premium','₹2,999','2 years')" style="border:2.5px solid #E0C0C8;border-radius:12px;padding:14px;cursor:pointer;background:#FFFAF9;transition:all 0.2s;text-align:center">
              <div style="font-size:22px;margin-bottom:6px">💎</div><div style="font-weight:700;color:#5A0010;font-size:0.9rem">Premium</div><div style="font-size:1.1rem;font-weight:800;color:#8B0000">₹2,999</div><div style="font-size:0.68rem;color:#9A7080">2 years</div>
            </div>
          </div>
          <input type="hidden" id="ao_plan" value="">
          <input type="hidden" id="ao_amount" value="">
          <!-- Payment Info -->
          <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:white;background:linear-gradient(90deg,#8B0000,#C41E3A 60%,transparent);padding:7px 14px;border-radius:6px;margin:4px 0 14px">💳 Payment Details</div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px">
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Amount (₹)</label><input class="input" id="ao_amt_display" placeholder="Auto-filled" readonly style="border-color:#D4A0A8;background:#f7f5f1"></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Payment Method</label><select class="input" id="ao_payment_method" style="border-color:#D4A0A8;background:#FFFAF9"><option value="">— Select —</option><option>Cash</option><option>UPI</option><option>Bank Transfer</option><option>Online</option><option>Cheque</option></select></div>
            <div style="margin-bottom:13px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Transaction Ref</label><input class="input" id="ao_txn" placeholder="Transaction ID (optional)" style="border-color:#D4A0A8;background:#FFFAF9"></div>
          </div>
          <div style="margin-bottom:20px"><label style="display:block;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7A1020;margin-bottom:4px">Notes</label><textarea class="input" id="ao_notes" rows="2" placeholder="Any additional notes…" style="border-color:#D4A0A8;background:#FFFAF9;resize:vertical"></textarea></div>
          <!-- Footer -->
          <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid rgba(196,30,58,0.12)">
            <button class="btn btn-outline" onclick="aoReset()">↺ Reset</button>
            <button class="btn btn-primary" style="background:linear-gradient(135deg,#8B0000,#C41E3A);border-color:#8B0000;min-width:140px" onclick="aoSubmit()">✦ Place Order</button>
          </div>
        </div>
      </div>
    </div>
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
// Production: errors logged to console only
window.onerror = function(msg, url, line) { console.error('Error:', msg, 'line:', line); };
// ══════════════════════════════════════════════════════
// API HELPERS
// ══════════════════════════════════════════════════════
async function api(url, options = {}) {
  const resp = await fetch(url, {
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
    ...options,
  });
  const data = await resp.json();
  if (!resp.ok || data.error) throw new Error(data.error || data.message || 'API error');
  return data;
}
async function apiGet(endpoint) { return api(endpoint); }
async function apiPost(endpoint, body) {
  return api(endpoint, { method: 'POST', body: JSON.stringify(body) });
}

// ══════════════════════════════════════════════════════
// DATA ARRAYS - populated from API
// ══════════════════════════════════════════════════════
let profiles = [];
let bills = [];
let billHistory = [];
let followUps = [];
let deleted = [];
let actionLog = [];
let admins = [];
let stories = [];
let notifications = [];
let customPlans = [];
let planHistory = [];
let paymentOptions = [];
let userPanelControl = { global: {}, overrides: [] };
let upCtrlHistory = [];
let globalRestriction = { day: '', month: '', total: '', sessionViews: '', sessionHours: '' };
let casteList = [];
let subcasteList = [];
let profileReports = [];
let individualRestrictions = [];
let otpLogs = [];
let expiredProfiles = [];
let adminLog = [];
let usage = [];
let contactViewLog = [];
let mobileReqs = [];
let alertThresholds = { contactDay: 10, otpDay: 3, profileDay: 10 };
let rolePerms = {};

let idx = null;
let loginAdminObj = null;
let loginOtp = null;
let otpTimerInt = null;
let loginAttempts = 0;
let editFollowIdx = null;
let undoFollowIdx = null;
let billEditIdx = null;
let adminIdx = null;
let nextAdminId = 100;
let editPlanIdx = null;
let editPayOptIdx = null;
let editOverrideIdx = null;
let expireIdx = null;
let pwdVisible = {};
let usageExpanded = {};
let activeRoleTab = 'super';
let profileViewMode = 'table';
let _sharePayCpId = '';
let _sharePayName = '';
let _sharePayMobile = '';
let _upHistPage = 1;
const UP_HIST_PER_PAGE = 15;
const PER_PAGE = 15;
const _pg = {
  profile:1, manage:1, bill:1, billHistory:1,
  otp:1, adminLog:1, deleted:1, actionLog:1, admin:1
};

// ══════════════════════════════════════════════════════
// FIELD MAPPING: API returns snake_case, render uses camelCase
// ══════════════════════════════════════════════════════
const FIELD_MAP = {
  cp_id:'cpId', member_name:'memberName', billed_by:'billedBy', billed_date:'billedDate',
  plan_name:'planName', plan_type:'planType', created_by:'createdBy', created_at:'created',
  created_date:'createdDate', created_by:'createdBy', alt_mobile:'altMobile', place_of_job:'placeJob',
  contact_person:'contactPerson', father_job:'fatherJob', mother_job:'motherJob',
  perm_address:'permAddr', present_address:'presentAddr', rasi_chart:'rasiChart',
  nav_chart:'navChart', blood_group:'blood', mother_tongue:'tongue', sub_caste:'subcaste',
  otp_requested_at:'otpRequestedAt', last_login:'lastLogin', login_count:'loginCount',
  live_otp:'liveOtp', live_otp_expires:'liveOtpExpires', live_otp_verified:'liveOtpVerified',
  old_mobile:'oldMobile', new_mobile:'newMobile', requested_at:'requestedAt',
  admin_note:'adminNote', profile_snapshot:'profileSnapshot', expiry_date:'expiryDate',
  expired_on:'expiredOn', actioned_by:'actionedBy', deleted_at:'deletedAt',
  deleted_by:'deletedBy', profile_json:'profileJson', opt_id:'optId',
  plan_id:'planId', admin_name:'adminName', recorded_at:'recordedAt',
  recorded_by:'recordedBy', changed_by:'changedBy', time_label:'time',
  description:'desc', per_day:'day', per_month:'month',
  contact_day:'contactDay', otp_day:'otpDay', profile_day:'profileDay',
  has_photo:'hasPhoto', duplicate_photo:'duplicatePhoto', duplicate_with:'duplicateWith', req_id:'reqId', activity_type:'activityType', target_cp_id:'targetCpId', user_visible:'userVisible',
  birth_hour:'birthHour', birth_min:'birthMin', birth_ampm:'birthAmpm', place_birth:'placeBirth', own_house:'ownHouse', born_as:'bornAs',
  time_spent:'timeSpent', scroll_depth:'scrollDepth',
};
function mapRow(row) {
  if (!row || typeof row !== 'object') return row;
  if (Array.isArray(row)) return row.map(mapRow);
  const out = {};
  for (const [k, v] of Object.entries(row)) {
    const mk = FIELD_MAP[k] || k;
    out[mk] = (v && typeof v === 'object' && !Array.isArray(v)) ? mapRow(v) : v;
    if (mk !== k) out[k] = out[mk]; // keep snake_case alias too
  }
  return out;
}
function mapRows(arr) { return (arr || []).map(mapRow); }

// ══════════════════════════════════════════════════════
// LOAD ALL DATA FROM API
// ══════════════════════════════════════════════════════
let _profTotal = 0;  // total profile count from server
let _profLimit = 10000;
let _profOffset = 0;
let _profStats = { total:0, approved:0, pending:0, premium:0 };

function buildProfileQuery() {
  const p = new URLSearchParams();
  // Grab from whichever search bar is visible / has a value
  const q  = document.getElementById('manageSearch')?.value?.trim()
          || document.getElementById('profileSearch')?.value?.trim() || '';
  const st = document.getElementById('manageStatusFilter')?.value
          || document.getElementById('profileStatusFilter')?.value || '';
  const pl = document.getElementById('managePlanFilter')?.value
          || document.getElementById('profilePlanFilter')?.value || '';
  const df = document.getElementById('manageDateFrom')?.value
          || document.getElementById('profileDateFrom')?.value || '';
  const dt = document.getElementById('manageDateTo')?.value
          || document.getElementById('profileDateTo')?.value || '';
  const gd = document.getElementById('profileGenderFilter')?.value || '';
  const ph = document.getElementById('profilePhotoFilter')?.value || '';
  if (q)  p.set('search', q);
  if (gd) p.set('gender', gd);
  if (ph) p.set('photo', ph);
  if (st) p.set('status', st);
  if (pl) p.set('plan', pl);
  if (df) p.set('dateFrom', df);
  if (dt) p.set('dateTo', dt);
  p.set('limit', _profLimit);
  p.set('offset', _profOffset);
  return p.toString();
}

async function loadProfiles(query) {
  const qs = query || buildProfileQuery();
  const profData = await apiGet('api/admin/profiles.php?' + qs);
  profiles = mapRows(profData.profiles);
  _profTotal  = profData.total || profiles.length;
  _profLimit  = profData.limit || 200;
  _profOffset = profData.offset || 0;
  if (profData.stats) _profStats = profData.stats;
}

async function loadAll() {
  try {
    const [profData, billData, billHistData, followData, settData] = await Promise.all([
      apiGet('api/admin/profiles.php?limit=10000&offset=0'),
      apiGet('api/admin/bills.php'),
      apiGet('api/admin/bills.php?type=history'),
      apiGet('api/admin/followups.php'),
      apiGet('api/admin/settings.php?section=all'),
    ]);
    profiles    = mapRows(profData.profiles);
    _profTotal  = profData.total || profiles.length;
    _profLimit  = profData.limit || 200;
    _profOffset = profData.offset || 0;
    if (profData.stats) _profStats = profData.stats;
    bills       = mapRows(billData.bills);
    billHistory = mapRows(billHistData.billHistory || billHistData.bills);
    followUps   = mapRows(followData.followUps || followData.followups);
    const s = settData || {};
    if (s.plans) customPlans = mapRows(s.plans).map(p => {
      p.amount = parseFloat(p.amount) || 0;
      p.validity = parseInt(p.validity) || 365;
      if (!p.name && p.planName) p.name = p.planName;
      if (!p.type && p.planType) p.type = p.planType;
      if (!p.createdDate && p.created) p.createdDate = p.created;
      if (!p.createdBy && p.created_by) p.createdBy = p.created_by;
      p.userVisible = p.userVisible !== undefined ? (p.userVisible == 1 || p.userVisible === true) : (p.user_visible !== undefined ? (p.user_visible == 1 || p.user_visible === true) : true);
      return p;
    });
    if (s.admins) admins = mapRows(s.admins).map(a => {
      if (!a.created && a.created_at) a.created = a.created_at.split(' ')[0];
      a.password = a.password || '********';
      return a;
    });
    if (s.restrictions) {
      const g = s.restrictions.global ? mapRow(s.restrictions.global) : null;
      globalRestriction = g ? {
        day:          g.day          ?? g.per_day  ?? '',
        month:        g.month        ?? g.per_month ?? '',
        total:        g.total        ?? '',
        sessionViews: g.sessionViews ?? g.unverified_session_views ?? '',
        sessionHours: g.sessionHours ?? g.unverified_session_hours ?? '',
      } : { day:'', month:'', total:'', sessionViews:'', sessionHours:'' };
      individualRestrictions = mapRows(s.restrictions.individual).map(r => ({
        ...r, day: r.day ?? r.per_day ?? '', month: r.month ?? r.per_month ?? '', total: r.total ?? '',
      }));
    }
    if (s.paymentOpts) paymentOptions = mapRows(s.paymentOpts).map(o => {
      if (!o.id && o.optId) o.id = o.optId;
      if (typeof o.details === 'string') try { o.details = JSON.parse(o.details); } catch(e) {}
      return o;
    });
    if (s.panelCtrl) {
      // Normalize global
      let g = s.panelCtrl.global;
      if (!g || typeof g !== 'object' || Array.isArray(g)) g = {};
      userPanelControl.global = g;
      // Normalize overrides — map DB 'settings' key to JS 'pages' key
      userPanelControl.overrides = (s.panelCtrl.overrides || []).map(ov => {
        const pages = ov.settings || ov.pages || {};
        return {
          cpId: ov.cp_id || ov.cpId || '',
          mobile: ov.mobile || '',
          name: ov.name || ov.mobile || '',
          pages: typeof pages === 'object' && !Array.isArray(pages) ? pages : {},
          setBy: ov.changed_by || ov.setBy || '',
          setAt: ov.created_at || ov.setAt || '',
          id: ov.id,
        };
      });
    }
    if (s.mobileReqs) mobileReqs = mapRows(s.mobileReqs);
    if (s.stories) stories = mapRows(s.stories);
    if (s.notifications) notifications = mapRows(s.notifications).map(n => {
      if (!n.desc && n.description) n.desc = n.description;
      if (!n.time && n.time_label) n.time = n.time_label;
      return n;
    });
    if (s.otpLogs) otpLogs = mapRows(s.otpLogs);
    if (s.deleted) deleted = mapRows(s.deleted).map(d => {
      if (d.profileJson || d.profile_json) {
        try { d.profile = typeof d.profileJson === 'string' ? JSON.parse(d.profileJson) : d.profileJson; } catch(e) {}
      }
      return d;
    });
    if (s.expired) expiredProfiles = mapRows(s.expired);
    if (s.usage) {
      // Convert flat usage_activity rows into grouped usage format
      const rawUsage = mapRows(s.usage);
      const usageMap = {};
      rawUsage.forEach(r => {
        const key = r.mobile;
        if (!usageMap[key]) usageMap[key] = { mobile: r.mobile, name: r.name, cpId: r.cpId || r.cp_id, plan: r.plan, profileViews: [], contactViews: [] };
        const entry = { cpId: r.targetCpId || r.target_cp_id, datetime: r.datetime, timeSpent: parseInt(r.timeSpent||r.time_spent)||0, scrollDepth: parseInt(r.scrollDepth||r.scroll_depth)||0 };
        if (r.activityType === 'profile_view' || r.activity_type === 'profile_view') usageMap[key].profileViews.push(entry);
        else if (r.activityType === 'contact_view' || r.activity_type === 'contact_view') usageMap[key].contactViews.push(entry);
      });
      usage = Object.values(usageMap);
    }
    if (s.adminLog) adminLog = mapRows(s.adminLog);
    if (s.rolePerms) {
      ['super','manager','staff'].forEach(role => {
        if (s.rolePerms[role] && typeof s.rolePerms[role] === 'object' && !Array.isArray(s.rolePerms[role]) && Object.keys(s.rolePerms[role]).length > 0) {
          rolePerms[role] = s.rolePerms[role];
        }
        // else keep defaults
      });
    }
    if (s.alertThresholds) alertThresholds = mapRow(s.alertThresholds);
    if (s.planHistory) planHistory = mapRows(s.planHistory);
    if (s.upCtrlHistory) upCtrlHistory = mapRows(s.upCtrlHistory);
    if (s.billHistory) billHistory = mapRows(s.billHistory).map(b => {
      b._action = b.action || b._action || 'Created';
      b._recordedAt = b.recordedAt || b.recorded_at || b._recordedAt || '';
      return b;
    });
    if (s.castes) { casteList = s.castes; populateCasteDropdowns(); }
    if (s.profileReports) profileReports = mapRows(s.profileReports);
    if (s.subcastes) subcasteList = s.subcastes;
  } catch(e) {
    console.error('loadAll failed:', e);
  }
}

// saveState / loadState - no-ops (each operation saves via API)
function saveState() {}
function loadState() {}

// No-op stubs for functions that were localStorage-based
function savePayOptStore() {}
function savePlanHistory() {}
function saveUserPanelControlStore() {}
function saveMobileReqs() {}
function loadMobileReqs() {}
function loadRolePerms() {}
function loadUPCtrl() {}
function loadUserPanelControl() {
  if (typeof UP_PAGES !== 'undefined' && typeof UP_FEATURES !== 'undefined') {
    [...UP_PAGES, ...UP_FEATURES].forEach(i => {
      if (userPanelControl && userPanelControl.global && userPanelControl.global[i.id] === undefined) userPanelControl.global[i.id] = true;
    });
  }
}
function loadAlertThresholds() {
  ['th_contactDay','th_otpDay','th_profileDay'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.value = id === 'th_contactDay' ? (alertThresholds.contactDay||alertThresholds.contact_day||10)
               : id === 'th_otpDay'     ? (alertThresholds.otpDay||alertThresholds.otp_day||3)
               :                          (alertThresholds.profileDay||alertThresholds.profile_day||10);
    }
  });
}


// ══════════════════════════════════════════════════════
// NAV, MODAL, TOAST, BADGES - same as original
// ══════════════════════════════════════════════════════

// ══════════════════════════════════════════════════════
// ADD PROFILE SECTION
// ══════════════════════════════════════════════════════
let apSelectedPlan = 'free';

function apSelectPlan(plan) {
  apSelectedPlan = plan;
  document.getElementById('ap_plan').value = plan;
  document.querySelectorAll('.ap-plan-card').forEach(card => {
    const isSelected = card.dataset.plan === plan;
    card.style.border = isSelected ? '2.5px solid #8B0000' : '2.5px solid #E0C0C8';
    card.style.background = isSelected ? 'linear-gradient(135deg,#FFF0F2,#FFF8F5)' : '#FFFAF9';
    card.style.boxShadow = isSelected ? '0 4px 16px rgba(139,0,0,0.18)' : 'none';
    // Remove existing badge
    const existing = card.querySelector('.ap-sel-badge');
    if (existing) existing.remove();
    if (isSelected) {
      const badge = document.createElement('div');
      badge.className = 'ap-sel-badge';
      badge.style.cssText = 'position:absolute;top:-10px;right:12px;background:linear-gradient(135deg,#8B0000,#C41E3A);color:white;font-size:0.65rem;font-weight:700;padding:2px 10px;border-radius:20px;letter-spacing:.06em';
      badge.textContent = '✦ SELECTED';
      card.appendChild(badge);
    }
  });
}

function apReset() {
  ['ap_name','ap_mobile','ap_place_birth','ap_nativity','ap_qual','ap_job','ap_income','ap_subcaste','ap_present_area','ap_present_city'].forEach(id => {
    const el = document.getElementById(id); if (el) el.value = '';
  });
  ['ap_gender','ap_tongue','ap_marital','ap_blood','ap_height','ap_weight','ap_diet','ap_caste','ap_star','ap_raasi','ap_lagnam','ap_dosham'].forEach(id => {
    const el = document.getElementById(id); if (el) el.selectedIndex = 0;
  });
  document.getElementById('ap_dob').value = '';
  populateStateDropdown('ap_present_state', 'Tamil Nadu');
  populateDistrictDropdown('ap_present_district', '', 'ap_present_state');
  apSelectPlan('free');
  document.getElementById('apResult').style.display = 'none';
}

async function apSubmit() {
  const name = document.getElementById('ap_name').value.trim();
  const mobile = document.getElementById('ap_mobile').value.trim();
  const gender = document.getElementById('ap_gender').value;
  if (!name) { toast('Name is required', 'err'); return; }
  if (!mobile || !/^\d{10}$/.test(mobile)) { toast('Valid 10-digit mobile is required', 'err'); return; }
  if (profiles.some(p => p.mobile === mobile)) { toast('This mobile number already has a profile. One number = one profile only.', 'error'); return; }
  if (!gender) { toast('Gender is required', 'err'); return; }
  const apAgeErr = DobAge.validateAge('ap_dob', gender);
  if (apAgeErr) { toast(apAgeErr, 'error'); return; }
  // Input format validation
  const apValErrs = InputValidator.validateAll('ap_');
  if (apValErrs.length > 0) { toast(apValErrs[0].msg + ' (' + apValErrs[0].id.replace('ap_','') + ')', 'error'); document.getElementById(apValErrs[0].id)?.focus(); return; }
  const data = {
    action: 'create',
    name, mobile, gender,
    dob: DobAge.getIso('ap_dob'),
    place_birth: document.getElementById('ap_place_birth').value,
    nativity: document.getElementById('ap_nativity').value,
    present_area: document.getElementById('ap_present_area')?.value || '',
    present_city: document.getElementById('ap_present_city')?.value || '',
    present_state: document.getElementById('ap_present_state')?.value || '',
    present_district: document.getElementById('ap_present_district')?.value || '',
    mother_tongue: document.getElementById('ap_tongue').value,
    marital: document.getElementById('ap_marital').value,
    blood_group: document.getElementById('ap_blood').value,
    height: document.getElementById('ap_height').value,
    weight: document.getElementById('ap_weight').value,
    diet: document.getElementById('ap_diet').value,
    qualification: document.getElementById('ap_qual').value,
    job: document.getElementById('ap_job').value,
    income: document.getElementById('ap_income').value,
    caste: document.getElementById('ap_caste').value,
    sub_caste: document.getElementById('ap_subcaste').value,
    star: document.getElementById('ap_star').value,
    raasi: document.getElementById('ap_raasi').value,
    lagnam: document.getElementById('ap_lagnam').value,
    dosham: document.getElementById('ap_dosham').value,
    religion: document.getElementById('ap_religion')?.value || '',
  };
  // Calculate age from DOB
  if (data.dob) {
    const age = Math.floor((new Date() - new Date(data.dob)) / (365.25 * 24 * 60 * 60 * 1000));
    if (age > 0) data.age = age;
  }
  const resultEl = document.getElementById('apResult');
  try {
    resultEl.innerHTML = '<div style="text-align:center;padding:20px;color:#8B0000;font-weight:600">Saving profile...</div>';
    resultEl.style.display = 'block';
    const resp = await apiPost('api/admin/profiles.php', data);
    const cpId = resp.cpId || resp.cp_id || '';
    const rows = Object.entries(data).filter(([k,v]) => k !== 'action' && v).map(([k,v]) => `<tr style="background:${Object.keys(data).indexOf(k)%2===0?'white':'#FFFAF9'}"><td style="padding:9px 18px;font-size:0.78rem;font-weight:700;color:#7A1020;text-transform:uppercase;border-bottom:1px solid #F0E0E4">${k.replace(/_/g,' ')}</td><td style="padding:9px 18px;font-size:0.88rem;color:#2A0A0E;border-bottom:1px solid #F0E0E4">${v}</td></tr>`).join('');
    resultEl.innerHTML = `<div style="background:white;border-radius:16px;box-shadow:0 8px 32px rgba(139,0,0,0.1);border:1px solid rgba(196,30,58,0.1);overflow:hidden"><div style="background:linear-gradient(135deg,#8B0000,#C41E3A);padding:14px 22px;display:flex;align-items:center;gap:10px"><span style="font-size:18px">✅</span><h3 style="margin:0;color:white;font-family:'DM Serif Display',serif;font-size:1rem">Profile Created — ${cpId}</h3></div><div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse"><thead><tr style="background:#FFF0F2"><th style="padding:9px 18px;text-align:left;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B0000;border-bottom:1px solid #E0C0C8;width:35%">Field</th><th style="padding:9px 18px;text-align:left;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B0000;border-bottom:1px solid #E0C0C8">Value</th></tr></thead><tbody>${rows}</tbody></table></div></div>`;
    resultEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    FormAutoSave.clear('admin_quick_add');
    await loadAll();
    render(); postRender();
    toast('Profile added — ' + (cpId || ''));
  } catch(e) {
    resultEl.innerHTML = `<div style="background:#fee2e2;border:1px solid #fecaca;border-radius:12px;padding:16px;color:#dc2626;font-weight:600;text-align:center">${e.message || 'Failed to add profile'}</div>`;
    toast(e.message || 'Failed to add profile', 'error');
  }
}

// ══════════════════════════════════════════════════════
// ADD ORDER SECTION
// ══════════════════════════════════════════════════════
let aoSelectedPlan = '', aoSelectedAmount = '';

function aoSelectPlan(el, plan, price, period) {
  aoSelectedPlan = plan;
  aoSelectedAmount = price;
  document.getElementById('ao_plan').value = plan;
  document.getElementById('ao_amount').value = price;
  document.getElementById('ao_amt_display').value = price + ' / ' + period;
  document.querySelectorAll('.ao-plan-card').forEach(card => {
    const isSel = card.dataset.plan === plan;
    card.style.border = isSel ? '2.5px solid #8B0000' : '2.5px solid #E0C0C8';
    card.style.background = isSel ? 'linear-gradient(135deg,#FFF0F2,#FFF8F5)' : '#FFFAF9';
    card.style.boxShadow = isSel ? '0 4px 16px rgba(139,0,0,0.18)' : 'none';
  });
}

async function aoFetchMember() {
  const q = document.getElementById('ao_cpid').value.trim();
  if (!q) { toast('Enter CP ID or mobile', 'err'); return; }
  const found = profiles.find(p => p.cp_id === q || p.mobile === q);
  if (found) {
    document.getElementById('ao_name').value = found.name || '';
  } else {
    document.getElementById('ao_name').value = '';
    toast('Member not found', 'warn');
  }
}

function aoReset() {
  ['ao_cpid','ao_name','ao_txn','ao_notes'].forEach(id => { const el = document.getElementById(id); if(el) el.value=''; });
  ['ao_amt_display'].forEach(id => { const el = document.getElementById(id); if(el) el.value=''; });
  document.getElementById('ao_payment_method').selectedIndex = 0;
  document.getElementById('ao_plan').value = '';
  document.getElementById('ao_amount').value = '';
  aoSelectedPlan = ''; aoSelectedAmount = '';
  document.querySelectorAll('.ao-plan-card').forEach(c => {
    c.style.border = '2.5px solid #E0C0C8';
    c.style.background = '#FFFAF9';
    c.style.boxShadow = 'none';
  });
  document.getElementById('aoResult').style.display = 'none';
}

function aoSubmit() {
  const cpid = document.getElementById('ao_cpid').value.trim();
  const name = document.getElementById('ao_name').value.trim();
  const plan = document.getElementById('ao_plan').value;
  const method = document.getElementById('ao_payment_method').value;
  if (!cpid) { toast('CP ID / Mobile is required', 'err'); return; }
  if (!plan) { toast('Please select a plan', 'err'); return; }
  if (!method) { toast('Please select a payment method', 'err'); return; }
  const orderData = [
    ['CP ID / Mobile', cpid], ['Member Name', name || '-'], ['Plan', plan],
    ['Amount', document.getElementById('ao_amt_display').value],
    ['Payment Method', method],
    ['Transaction Ref', document.getElementById('ao_txn').value || '-'],
    ['Notes', document.getElementById('ao_notes').value || '-'],
    ['Order Date', new Date().toLocaleDateString('en-IN')],
  ];
  const rows = orderData.map(([k,v],i) => `<tr style="background:${i%2===0?'white':'#FFFAF9'}"><td style="padding:9px 18px;font-size:0.78rem;font-weight:700;color:#7A1020;text-transform:uppercase;border-bottom:1px solid #F0E0E4">${k}</td><td style="padding:9px 18px;font-size:0.88rem;color:#2A0A0E;border-bottom:1px solid #F0E0E4">${v}</td></tr>`).join('');
  const resultEl = document.getElementById('aoResult');
  resultEl.innerHTML = `<div style="background:white;border-radius:16px;box-shadow:0 8px 32px rgba(139,0,0,0.1);border:1px solid rgba(196,30,58,0.1);overflow:hidden"><div style="background:linear-gradient(135deg,#8B0000,#C41E3A);padding:14px 22px;display:flex;align-items:center;gap:10px"><span style="font-size:18px">✅</span><h3 style="margin:0;color:white;font-family:'DM Serif Display',serif;font-size:1rem">Order Placed — Order Summary</h3></div><div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse"><thead><tr style="background:#FFF0F2"><th style="padding:9px 18px;text-align:left;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B0000;border-bottom:1px solid #E0C0C8;width:38%">Field</th><th style="padding:9px 18px;text-align:left;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B0000;border-bottom:1px solid #E0C0C8">Value</th></tr></thead><tbody>${rows}</tbody></table></div></div>`;
  resultEl.style.display = 'block';
  resultEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
  toast('Order placed successfully!', 'ok');
}

// NAV
// Map section names to permission IDs
// Stubs — only define if external JS files failed to load
try { FormAutoSave; } catch(e) { window.FormAutoSave = { track:()=>{}, showRestoreBanner:()=>{}, clear:()=>{} }; }
try { PartnerCaste; } catch(e) { window.PartnerCaste = { build:()=>{}, linkSubCaste:()=>{} }; }
try { populateDoshamType; } catch(e) { window.populateDoshamType = ()=>{}; }
try { attachDoshamRadio; } catch(e) { window.attachDoshamRadio = ()=>{}; }
try { attachDoshamSelect; } catch(e) { window.attachDoshamSelect = ()=>{}; }

const SECTION_PERM_MAP = {
  profile:'view_profiles', manage:'view_manage', otp:'view_otp', alerts:'view_alerts',
  contactLog:'view_contactlog', profileViewLog:'view_contactlog', userResponse:'view_contactlog', userActivity:'view_contactlog', follow:'view_followups', interest:'view_reports',
  bill:'view_bills', usage:'view_usage', addProfile:'add_profile', addOrder:'create_bill',
  settings:'view_settings', deleted:'view_deleted', expired:'view_expired',
  reports:'view_reports', notifications:'view_notifs', stories:'view_stories',
  accounts:'view_bills', userOrders:'view_bills', directLogin:'view_settings', profileReports:'view_reports', updateHistory:'view_settings',
  basicMatches:'view_profiles', mutualMatches:'view_profiles'
};

function show(s, btn) {
  // Check permission
  const role = loginAdminObj?.role || 'staff';
  if (role !== 'super') {
    const permId = SECTION_PERM_MAP[s];
    const perms = rolePerms[role] || {};
    if (permId && !perms[permId]) {
      toast('Access denied for your role', 'error');
      return;
    }
  }
  document.querySelectorAll('.section').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(el => el.classList.remove('active'));
  const sec = document.getElementById(s + 'Section');
  if (sec) sec.classList.add('active');
  if (btn) btn.classList.add('active');
  if (['profile','manage','bill','usage','deleted'].includes(s)) render();
  if (s === 'bill')        { renderBills(); renderBillHistory(); }
  if (s === 'follow')      renderFollowTables();
  if (s === 'reports')     renderReports();
  if (s === 'accounts') loadAccounts();
  if (s === 'userOrders') loadUserOrders();
  if (s === 'directLogin') loadDirectLogins();
  if (s === 'profileViewLog') loadProfileViewLog();
  if (s === 'profileReports') renderProfileReports();
  if (s === 'notifications') renderNotifications();
  if (s === 'stories')     renderStories();
  if (s === 'otp')         { renderOtp(); startOtpLivePoll(); } else stopOtpLivePoll();
  if (s === 'expired')     renderExpired();
  if (s === 'alerts')      renderAlerts();
  if (s === 'contactLog')  renderContactLog();
  if (s === 'userResponse') loadUserResponse();
  if (s === 'userActivity') loadUserActivity();
  if (s === 'interest')    renderInterest();
  if (s === 'messages')    loadMessages();
  if (s === 'updateHistory') loadUpdateHistory();
  if (s === 'addProfile') FormAutoSave.showRestoreBanner('admin_quick_add', '#apResult', () => toast('Draft restored'));
}

// SIDEBAR: collapsible parent submenus (Matches)
function toggleAdminSubmenu(btn) {
  const sub = btn.nextElementSibling;
  if (!sub || !sub.classList.contains('nav-sub')) return;
  const open = btn.getAttribute('aria-expanded') === 'true';
  btn.setAttribute('aria-expanded', open ? 'false' : 'true');
  sub.hidden = open;
}

// MATCHES (Basic / Mutual) — admin selects a source profile by CP ID or mobile
async function loadAdminMatches(mode) {
  const isMutual = mode === 'mutual';
  const cpIdEl   = document.getElementById(isMutual ? 'mmSourceCpId'  : 'bmSourceCpId');
  const mobileEl = document.getElementById(isMutual ? 'mmSourceMobile': 'bmSourceMobile');
  const status   = document.getElementById(isMutual ? 'mmStatus'      : 'bmStatus');
  const results  = document.getElementById(isMutual ? 'mmResults'     : 'bmResults');
  const cpId   = (cpIdEl?.value || '').trim();
  const mobile = (mobileEl?.value || '').trim();
  if (!cpId && !mobile) { toast('Enter a CP ID or mobile number', 'error'); return; }
  status.textContent = 'Loading…';
  results.innerHTML = '';
  let data = null;
  try {
    const body = { action: isMutual ? 'mutual_matches' : 'basic_matches', limit: 60 };
    if (cpId) body.cp_id = cpId; else body.mobile = mobile;
    const resp = await fetch('api/public.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'same-origin', body: JSON.stringify(body) });
    data = await resp.json();
  } catch(e) {}
  if (!data || !data.ok) {
    status.textContent = '';
    results.innerHTML = `<div class="card" style="padding:24px;text-align:center;color:#dc2626">${(data && data.error) || 'Could not load matches.'}</div>`;
    return;
  }
  const list = data.profiles || [];
  status.textContent = `${list.length} of ${data.total||list.length} matches · source ${data.source?.cp_id||''} (${data.source?.gender||''})`;
  if (!list.length) {
    results.innerHTML = `<div class="card" style="padding:28px;text-align:center;color:var(--text-secondary)">No profiles match this user's preferences.</div>`;
    return;
  }
  const photoBase = 'api/uploads/';
  const escA = (s) => { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; };
  const card = (p) => {
    const src = p.photo1 && !p.photo1.startsWith('default_')
      ? (p.photo1.startsWith('uploads/') ? 'api/' + p.photo1 : photoBase + p.photo1) : '';
    const img = src
      ? `<img src="${src}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:1.5px solid var(--border)" onerror="this.outerHTML='<div style=&quot;width:48px;height:48px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-weight:700;color:#9ca3af&quot;>${escA((p.name||'?').charAt(0))}</div>'">`
      : `<div style="width:48px;height:48px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-weight:700;color:#9ca3af">${escA((p.name||'?').charAt(0))}</div>`;
    const where = [p.present_city, p.present_district, p.present_state].filter(Boolean).join(', ');
    return `<tr style="cursor:pointer" onclick="window.open('/detail/${escA(p.cp_id)}','_blank')">
      <td style="padding:8px 12px">${img}</td>
      <td style="padding:8px 12px"><b>${escA(p.name)}</b><div style="font-size:11px;color:var(--text-secondary)">${escA(p.cp_id)}</div></td>
      <td style="padding:8px 12px">${escA(p.age||'')} · ${escA(p.gender||'')}</td>
      <td style="padding:8px 12px">${escA(p.caste||'')}${p.sub_caste?(' / '+escA(p.sub_caste)):''}</td>
      <td style="padding:8px 12px">${escA(p.marital||'')}</td>
      <td style="padding:8px 12px">${escA(p.qualification||'')}<div style="font-size:11px;color:var(--text-secondary)">${escA(p.job||'')}</div></td>
      <td style="padding:8px 12px">${escA(p.star||'')}</td>
      <td style="padding:8px 12px">${escA(where)}</td>
    </tr>`;
  };
  results.innerHTML = `<div class="card">
    <div class="table-wrap"><table>
      <thead><tr><th>Photo</th><th>Name / CP</th><th>Age · Gender</th><th>Caste</th><th>Marital</th><th>Qualification</th><th>Star</th><th>Location</th></tr></thead>
      <tbody>${list.map(card).join('')}</tbody>
    </table></div>
  </div>`;
}

// MODAL
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// TOAST
function toast(msg, type='success') {
  const t = document.getElementById('toast');
  const icon = type === 'success' ? '✓' : '⚠';
  t.innerHTML = `<span style="font-size:16px">${icon}</span> ${msg}`;
  t.style.transform = 'translateY(0)';
  t.style.opacity = '1';
  setTimeout(() => { t.style.transform = 'translateY(80px)'; t.style.opacity = '0'; }, 2500);
}

function planBadge(plan) {
  if (!plan || plan === 'free') return `<span class="badge badge-gray">🔓 Free</span>`;
  if (plan === 'silver')  return `<span class="badge badge-blue">🥈 Silver</span>`;
  if (plan === 'gold')    return `<span class="badge badge-amber">🥇 Gold</span>`;
  if (plan === 'premium') return `<span class="badge badge-amber">💎 Premium</span>`;
  if (plan === 'paid')    return `<span class="badge badge-blue">💎 Paid</span>`;
  return `<span class="badge badge-blue">💎 ${plan.charAt(0).toUpperCase()+plan.slice(1)}</span>`;
}

function statusBadge(s) {
  if (s === 'Approved')    return `<span class="badge badge-green">● Approved</span>`;
  if (s === 'Preapproved') return `<span class="badge badge-amber">◌ Pre-approved</span>`;
  return `<span class="badge badge-gray">${s}</span>`;
}

function esc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }
function initials(name) {
  return name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0,2);
}
// ══════════════════════════════════════════════════════
// CASTE & SUBCASTE DROPDOWNS (from pm_caste / pm_subcaste)
// ══════════════════════════════════════════════════════
function populateCasteDropdowns() {
  const dbCastes = casteList.map(c => c.caste).filter(Boolean);
  ['a_caste','e_caste'].forEach(id => {
    populateCasteDropdown(id, '', dbCastes);
  });
}

function populateSubcasteFor(casteSelectId, subcasteSelectId, currentVal) {
  populateSubcaste(casteSelectId, subcasteSelectId, currentVal);
}

function onCasteChange(prefix) {
  populateSubcaste(prefix + '_caste', prefix + '_subcaste', '');
}

function photoSrc(raw) {
  if (!raw) return '';
  if (raw.startsWith('http')) return raw;
  if (raw.startsWith('uploads/')) return 'api/' + raw;
  if (raw.startsWith('default_')) return 'api/uploads/' + raw;
  // Any other filename (PM*, F*, M*, etc.) — in uploads folder
  return 'api/uploads/' + raw;
}

// ── Shared: mobile cell with red colour + dblclick follow-up ──
// mobile       : the mobile number string
// profileIdx   : real index in profiles[] (or -1 if not found / already deleted)
// showDblClick : true = show dblclick hint (profile is still active)
function mobileCellHtml(mobile, profileIdx, showDblClick) {
  // hasFollowUp: any follow-up of any type counts — used for red mobile & dblclick
  const hasFollowUp = profileIdx >= 0 &&
    followUps.some(f => f.cpId === profiles[profileIdx]?.cpId);

  if (!showDblClick || profileIdx < 0) {
    const linked   = profiles.find(p => p.mobile === mobile);
    const linkedFU = linked && followUps.some(f => f.cpId === linked.cpId);
    if (!linked || linkedFU) {
      return `<span style="font-size:13px;font-weight:500">${mobile}</span>`;
    }
    return `<span style="font-size:13px;font-weight:700;color:#dc2626" title="No follow-up for this member">${mobile}</span>`;
  }

  if (hasFollowUp) {
    return `<span style="font-size:13px;font-weight:500">${mobile}</span>`;
  }
  return `<span
    style="font-size:13px;font-weight:700;color:#dc2626;cursor:pointer;border-bottom:1.5px dashed #dc2626;"
    title="No follow-up — double-click to create one"
    ondblclick="openFollowUp(${profileIdx})"
  >${mobile}</span>`;
}

// RENDER
function createdByCell(p) {
  const raw = (p.createdBy || p.created_by || '').toString().trim();
  if (!raw) {
    return `<span style="color:#d1d5db;font-size:12px">—</span>`;
  }
  if (raw.toLowerCase() === 'user') {
    return `<span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#15803d;background:#f0fdf4;border:1px solid #bbf7d0;padding:3px 8px;border-radius:12px;font-weight:600">👤 User (Self)</span>`;
  }
  if (raw.toLowerCase() === 'admin') {
    return `<span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#c2410c;background:#fff7ed;border:1px solid #fed7aa;padding:3px 8px;border-radius:12px;font-weight:600">🛡 Admin</span>`;
  }
  // Specific admin name
  const esc = raw.replace(/</g, '&lt;').replace(/>/g, '&gt;');
  return `<span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#1e40af;background:#eff6ff;border:1px solid #bfdbfe;padding:3px 8px;border-radius:12px;font-weight:600" title="Created by admin: ${esc}">🛡 ${esc}</span>`;
}

function render() {
  // Stats from server (full DB counts, not just current page)
  document.getElementById('statsRow').innerHTML = `
    <div class="stat-card">
      <div class="stat-icon" style="background:#eff6ff;font-size:18px">👥</div>
      <div class="stat-body"><div class="val">${(_profStats.total||0).toLocaleString()}</div><div class="lbl">Total Profiles</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:#f0fdf4;font-size:18px">✅</div>
      <div class="stat-body"><div class="val">${(_profStats.approved||0).toLocaleString()}</div><div class="lbl">Approved</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:#fffbeb;font-size:18px">⏳</div>
      <div class="stat-body"><div class="val">${(_profStats.pending||0).toLocaleString()}</div><div class="lbl">Pending</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:#fdf4ff;font-size:18px">⭐</div>
      <div class="stat-body"><div class="val">${(_profStats.premium||0).toLocaleString()}</div><div class="lbl">Premium</div></div>
    </div>`;

  // Profile table — paginated (apply client-side photo filter using has_photo from server)
  const photoFilterVal = document.getElementById('profilePhotoFilter')?.value || '';
  let profFiltered = profiles;
  const hasRealPhoto = (p) => {
    if (p.hasPhoto === true || p.has_photo === true) return true;
    const ph = (p.photo1 || '').trim();
    return ph && !ph.startsWith('default_');
  };
  if (photoFilterVal === 'with') profFiltered = profiles.filter(p => hasRealPhoto(p));
  else if (photoFilterVal === 'without') profFiltered = profiles.filter(p => !hasRealPhoto(p));
  const profSorted = [...profFiltered].sort((a,b)=>(b.created||'').localeCompare(a.created||''));
  const profRows = profSorted.map((p, i) => {
    const closedTypes  = ['paid','not_interested'];
    const hasFollowUp  = followUps.some(f => f.cpId === p.cpId && !closedTypes.includes(f.type));
    const pi           = profiles.indexOf(p);
    const mobileCell   = mobileCellHtml(p.mobile, pi, true);
    const ph = (p.photo1||'').trim();
    const isDefault = ph.startsWith('default_');
    const hasRealPhoto = p.has_photo === true || p.hasPhoto === true;
    const isDupPhoto = p.duplicate_photo === true || p.duplicatePhoto === true;
    const dupWith = p.duplicate_with || p.duplicateWith || [];
    // Photo badge: Yes/No + Duplicate warning
    const photoBadge = isDupPhoto
      ? `<span style="color:#f59e0b;font-weight:600;font-size:11px;background:#fffbeb;padding:2px 6px;border-radius:10px;border:1px solid #fde68a;cursor:pointer" title="Same photo as ${dupWith.join(', ')}">Dup</span>`
      : hasRealPhoto
      ? `<span style="color:#16a34a;font-weight:600;font-size:12px;background:#f0fdf4;padding:2px 8px;border-radius:10px;border:1px solid #bbf7d0">Yes</span>`
      : `<span style="color:#dc2626;font-weight:600;font-size:12px;background:#fef2f2;padding:2px 8px;border-radius:10px;border:1px solid #fecaca">No</span>`;
    // Picture thumbnail — always show image (real or default)
    const picSrc = photoSrc(ph);
    const picBorder = isDefault ? '2px solid #e5e7eb' : '2px solid #3b82f6';
    const picCell = picSrc
      ? `<img src="${picSrc}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:${picBorder}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" alt=""><div style="display:none;width:40px;height:40px;border-radius:50%;background:#f3f4f6;align-items:center;justify-content:center;color:#9ca3af;font-size:16px">👤</div>`
      : `<div style="width:40px;height:40px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:16px">👤</div>`;
    const mandatoryFields = [p.name, p.gender, p.dob, p.mobile, p.religion, p.caste, p.tongue, p.marital];
    const mandatoryOk = mandatoryFields.every(v => v && v.toString().trim() !== '' && v !== '-Select-');
    const mandBadge = mandatoryOk
      ? `<span style="color:#16a34a;font-weight:600;font-size:12px;background:#f0fdf4;padding:2px 8px;border-radius:10px;border:1px solid #bbf7d0">Yes</span>`
      : `<span style="color:#dc2626;font-weight:600;font-size:12px;background:#fef2f2;padding:2px 8px;border-radius:10px;border:1px solid #fecaca">No</span>`;
    return `<tr data-created="${p.created||''}">
      <td>${i+1}</td>
      <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${p.cpId}</code></td>
      <td style="text-align:center">${picCell}</td>
      <td><div class="name-cell"><div class="avatar">${initials(p.name)}</div>${p.name}</div></td>
      <td>${createdByCell(p)}</td>
      <td>${p.age ? p.age+' yrs' : '-'}</td>
      <td>${mobileCell}</td>
      <td>${statusBadge(p.status)}</td>
      <td>${planBadge(p.plan)}</td>
      <td style="text-align:center">${photoBadge}</td>
      <td style="text-align:center">${mandBadge}</td>
      <td>
        <div class="actions">
          <button class="btn btn-sm" onclick="openView(${pi})" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe">View</button>
          <button class="btn btn-sm" onclick="openOfficeInfo(${pi})" style="${(p.createdBy||p.created_by||'admin')==='user' ? 'background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0' : 'background:#fff7ed;color:#ea580c;border:1px solid #fed7aa'}">Office Info</button>
          <button class="btn btn-outline btn-sm" onclick="openEdit(${pi})">Edit</button>
          <button class="btn btn-danger btn-sm" onclick="openDelete(${pi})">Delete</button>
        </div>
      </td>
    </tr>`;
  });
  const newProfPg = paginate(profRows, _pg.profile, PER_PAGE, 'profileTable', 'profilePg',
    `<tr><td colspan="12"><div class="empty-state"><div class="icon">👤</div><p>No profiles yet</p></div></td></tr>`);
  if (newProfPg) _pg.profile = newProfPg;

  // Manage table — paginated, newest first
  const manageSorted = [...profiles].sort((a,b)=>(b.created||'').localeCompare(a.created||''));
  const manageRows = manageSorted.map((p) => {
    const i = profiles.indexOf(p);

    const closedTypes   = ['paid','not_interested'];
    // hasFollowUp: TRUE if ANY follow-up exists for this profile (any type)
    // closedTypes only used for follow-up table display, not stepper state
    const hasFollowUp   = followUps.some(f => f.cpId === p.cpId);
    const isApproved    = p.status === 'Approved';
    const isPreapproved = p.status === 'Preapproved';
    const hasBill       = bills.some(b => b.cpId === p.cpId);
    const billIdx       = bills.findIndex(b => b.cpId === p.cpId);

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // WORKFLOW:
    //   Step 1 — Create Follow-up   (always first, rest locked)
    //   Step 2 — Create Bill        (after follow-up; once created, DISABLED — edit only in Bills page)
    //   Step 3 — Approve / Revert   (after bill)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    // ── STEP 1: Follow-up ──
    let step1Num, step1Btn;
    if (!hasFollowUp) {
      step1Num = `<div class="step-num active">1</div>`;
      step1Btn = `<button class="step-btn s-follow" onclick="openFollowUp(${i})">📞 Add Follow-up</button>`;
    } else {
      // Done — DISABLED in Manage page; edit only in Follow-ups page
      step1Num = `<div class="step-num done">✓</div>`;
      step1Btn = `<div class="step-btn s-inactive" title="Edit follow-ups in the Follow-ups page">📞 Follow-up Created</div>`;
    }

    // ── STEP 2: Create Bill ──
    let step2Num, step2Btn;
    if (!hasFollowUp) {
      // Locked — need follow-up first
      step2Num = `<div class="step-num locked">2</div>`;
      step2Btn = `<div class="step-btn s-locked">💳 Create Bill</div>`;
    } else if (!hasBill) {
      // Active — follow-up done, now create bill
      step2Num = `<div class="step-num active">2</div>`;
      step2Btn = `<button class="step-btn s-bill" onclick="openBill(${i})">💳 Create Bill</button>`;
    } else {
      // Done — bill exists; DISABLED in manage (edit only in Bills page)
      step2Num = `<div class="step-num done">✓</div>`;
      step2Btn = `<div class="step-btn s-inactive" title="Edit bills in the Bills page">✅ Bill Created</div>`;
    }

    // ── STEP 3: Approve / Revert ──
    let step3Num, step3Btn;
    if (!hasBill) {
      step3Num = `<div class="step-num locked">3</div>`;
      step3Btn = `<div class="step-btn s-locked">✓ Approve</div>`;
    } else if (!isApproved) {
      step3Num = `<div class="step-num active">3</div>`;
      step3Btn = `<button class="step-btn s-approve" onclick="toggle(${i})">✓ Approve Profile</button>`;
    } else {
      step3Num = `<div class="step-num done">✓</div>`;
      step3Btn = `<button class="step-btn s-revert" onclick="toggle(${i})">↩ Revert</button>`;
    }

    const manageMobileCell = mobileCellHtml(p.mobile, i, true);

    // ── ACTIONS COLUMN: based on status + plan ──────────────────────
    // State A: Not Approved (Preapproved) → Edit button
    // State B: Approved + Free plan       → Pay Now button
    // State C: Approved + Paid/Premium    → Edit / Print / View Bill
    const freePlans    = ['free'];
    const paidPlans    = ['paid','premium','vip','basic','custom','silver','gold'];
    const isPaidPlan   = paidPlans.includes(p.plan);
    const bill         = bills.find(b => b.cpId === p.cpId);

    let actionCell = '';
    if (!isApproved) {
      // ── NOT APPROVED: show Edit + mandatory fields reminder ──
      actionCell = `
        <div style="display:flex;flex-direction:column;gap:5px">
          <button class="btn btn-outline btn-sm" onclick="openEdit(${i})"
            style="display:flex;align-items:center;gap:5px;font-size:12px">
            ✏️ Edit Profile
          </button>
          <div style="font-size:10.5px;color:#d97706;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:3px 7px;line-height:1.4">
            ⚠️ Fill mandatory fields to proceed
          </div>
        </div>`;
    } else if (isApproved && !isPaidPlan) {
      // ── APPROVED + FREE: show Pay Now + Share Payment Link ──
      actionCell = `
        <div style="display:flex;flex-direction:column;gap:5px">
          <button class="btn btn-sm" onclick="openAdminPayment(${i})"
            style="background:var(--accent);color:#fff;border:none;display:flex;align-items:center;gap:5px;font-size:12px;font-weight:700">
            💳 Pay Now
          </button>
          <button class="btn btn-outline btn-sm" onclick="sharePaymentLink('${p.cpId}','${p.name}','${p.mobile}')"
            style="font-size:11.5px;display:flex;align-items:center;gap:4px;color:#16a34a;border-color:#86efac">
            📤 Share Payment Link
          </button>
        </div>`;
    } else if (isApproved && isPaidPlan) {
      // ── APPROVED + PAID: Edit / Print / View Bill ──
      const billDetails = bill
        ? `Plan: ${bill.planName||bill.plan||'—'} · ₹${Number(bill.amount||0).toLocaleString('en-IN')} · Exp: ${bill.expiry||'—'}`
        : 'No bill on file';
      actionCell = `
        <div style="display:flex;flex-direction:column;gap:5px">
          <div style="display:flex;gap:5px;flex-wrap:wrap">
            <button class="btn btn-outline btn-sm" onclick="openEdit(${i})"
              style="font-size:11.5px;padding:4px 9px;display:flex;align-items:center;gap:4px">
              ✏️ Edit
            </button>
            <button class="btn btn-outline btn-sm" onclick="printProfile(${i})"
              style="font-size:11.5px;padding:4px 9px;display:flex;align-items:center;gap:4px">
              🖨️ Print
            </button>
          </div>
          <button class="btn btn-sm" onclick="viewBillDetails(${i})"
            style="background:#eff6ff;color:#2563eb;border:1.5px solid #bfdbfe;font-size:11.5px;padding:4px 9px;display:flex;align-items:center;gap:4px;white-space:nowrap">
            👁 View Bill
          </button>
          <button class="btn btn-outline btn-sm" onclick="sharePaymentLink('${p.cpId}','${p.name}','${p.mobile}')"
            style="font-size:11.5px;padding:4px 9px;display:flex;align-items:center;gap:4px;color:#16a34a;border-color:#86efac">
            📤 Share Link
          </button>
          <div style="font-size:10.5px;color:var(--text-secondary);max-width:180px;line-height:1.4">${billDetails}</div>
        </div>`;
    }

    return `<tr data-created="${p.created||''}">
      <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${p.cpId}</code></td>
      <td><div class="name-cell"><div class="avatar">${initials(p.name)}</div>${p.name}</div></td>
      <td>${createdByCell(p)}</td>
      <td>${manageMobileCell}</td>
      <td>${statusBadge(p.status)}</td>
      <td>${planBadge(p.plan)}</td>
      <td style="font-size:12px">${p.created||'-'}</td>
      <td style="font-size:12px">${p.approved||'-'}</td>
      <td style="font-size:12px">${p.expiry||'-'}</td>
      <td>
        <div style="display:flex;gap:12px;align-items:flex-start;flex-wrap:wrap">
          <!-- Workflow stepper -->
          <div class="action-stepper">
            <div class="step-row">${step1Num}${step1Btn}</div>
            <div class="step-row">${step2Num}${step2Btn}</div>
            <div class="step-row">${step3Num}${step3Btn}</div>
          </div>
          <!-- Status/Plan based actions -->
          ${actionCell}
        </div>
      </td>
    </tr>`;
  });
  const newMngPg = paginate(manageRows, _pg.manage, PER_PAGE, 'manageTable', 'managePg',
    `<tr><td colspan="10"><div class="empty-state"><div class="icon">📋</div><p>No profiles</p></div></td></tr>`);
  if (newMngPg) _pg.manage = newMngPg;

  // Follow-ups — 4 tables
  renderFollowTables();

  // Bills rendered by dedicated function
  renderBills();
  renderBillHistory();

  // Usage
  // Usage — rendered separately via renderUsage()
  renderUsage();

  // Deleted archive
  document.getElementById('deletedCount').textContent = deleted.length + ' records';
  document.getElementById('deletedTable').innerHTML = deleted.length === 0
    ? `<tr><td colspan="7"><div class="empty-state"><div class="icon">🗑️</div><p>No deleted profiles</p></div></td></tr>`
    : [...deleted].reverse().map((d, ri) => {
      const i = deleted.length - 1 - ri;
      return `<tr data-deletedat="${(d.deletedAt||'').split(' ')[0]}">
        <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${d.cpId}</code></td>
        <td><div class="name-cell"><div class="avatar" style="background:#fee2e2;color:#dc2626">${initials(d.name)}</div>${d.name}</div></td>
        <td>${mobileCellHtml(d.mobile, -1, false)}</td>
        <td><span style="font-weight:500">${d.deletedBy||'—'}</span></td>
        <td><span style="color:var(--text-secondary);font-size:13px">${d.reason}</span></td>
        <td style="font-size:12px;color:var(--text-secondary)">${d.deletedAt}</td>
        <td>
          <button class="btn btn-green btn-sm" onclick="undoDelete(${i})" title="Restore to Profiles">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
            Undo
          </button>
        </td>
      </tr>`;
    }).join('');

  // Action log — newest first
  document.getElementById('logCount').textContent = actionLog.length + ' entries';
  document.getElementById('actionLogTable').innerHTML = actionLog.length === 0
    ? `<tr><td colspan="7"><div class="empty-state"><div class="icon">📋</div><p>No actions logged yet</p></div></td></tr>`
    : [...actionLog].reverse().map((log, i) => {
      const isDelete = log.action === 'DELETE';
      const actionBadge = isDelete
        ? `<span class="badge badge-gray" style="background:#fee2e2;color:#dc2626">🗑 Deleted</span>`
        : `<span class="badge badge-green">↩ Restored</span>`;
      return `<tr>
        <td style="font-size:12px;color:var(--text-secondary)">${actionLog.length - i}</td>
        <td style="font-size:12px;white-space:nowrap">${log.timestamp}</td>
        <td>${actionBadge}</td>
        <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${log.cpId}</code></td>
        <td><div class="name-cell"><div class="avatar" style="font-size:10px;width:26px;height:26px">${initials(log.memberName)}</div>${log.memberName}</div></td>
        <td><span style="font-weight:500;font-size:13px">${log.admin}</span></td>
        <td><span style="color:var(--text-secondary);font-size:13px">${log.note}</span></td>
      </tr>`;
    }).join('');
}

// Photo preview helper for add form
// Store processed photos for form submission
const _processedAdminPhotos = {};

async function previewAdminPhoto(input, prefix) {
  const file = input.files[0];
  if (!file) return;
  const preview = document.getElementById(prefix + '_preview');
  const placeholder = document.getElementById(prefix + '_placeholder');

  const isHoroscope = prefix.includes('rasi') || prefix.includes('amsam');
  const isProfilePhoto = prefix.includes('photo1');

  // Show loading state
  if (placeholder) { placeholder.textContent = 'Processing...'; placeholder.style.color = '#c2553d'; }

  try {
    const result = await PhotoUtils.processPhoto(file, { isProfilePhoto, isHoroscope });
    preview.src = result.previewUrl;
    preview.style.display = 'block';
    if (placeholder) placeholder.style.display = 'none';
    // Also update face circle if this is photo1
    if (prefix === 'e_photo1' || prefix === 'a_photo1') {
      const face = document.getElementById(prefix.charAt(0) + '_photo1_face');
      const facePh = document.getElementById(prefix.charAt(0) + '_photo1_face_ph');
      if (face) { face.src = result.previewUrl; face.style.display = 'block'; }
      if (facePh) facePh.style.display = 'none';
    }
    _processedAdminPhotos[prefix] = PhotoUtils.blobToFile(result.blob, file.name);
    toast((isProfilePhoto ? 'Face-cropped' : 'Compressed') + ' (' + result.sizeKB + ' KB)');
  } catch (e) {
    input.value = '';
    delete _processedAdminPhotos[prefix];
    preview.style.display = 'none';
    if (placeholder) { placeholder.textContent = 'Upload'; placeholder.style.display = ''; placeholder.style.color = ''; }
    toast(e.message, 'error');
  }
}

// ══════════════════════════════════════════════════════
// AUTO CALCULATE HOROSCOPE (Star, Raasi, Paadam, Lagnam)
// Based on Vedic/Tamil astrology — Moon's sidereal position
// ══════════════════════════════════════════════════════
function calcMoonPosition(dob, hour, min) {
  const y = dob.getFullYear(), m = dob.getMonth() + 1, d = dob.getDate();
  const dayFrac = (hour + min / 60) / 24;
  // Julian Day Number
  const a = Math.floor((14 - m) / 12);
  const yy = y + 4800 - a, mm = m + 12 * a - 3;
  const jd = (d + dayFrac) + Math.floor((153 * mm + 2) / 5) + 365 * yy
    + Math.floor(yy / 4) - Math.floor(yy / 100) + Math.floor(yy / 400) - 32045;
  const T = (jd - 2451545.0) / 36525.0;

  // Moon's mean longitude
  let Lm = 218.3164477 + 481267.88123421 * T - 0.0015786 * T * T
    + T * T * T / 538841 - T * T * T * T / 65194000;
  // Mean elongation
  const D = 297.8501921 + 445267.1114034 * T - 0.0018819 * T * T;
  // Sun's mean anomaly
  const Ms = 357.5291092 + 35999.0502909 * T - 0.0001536 * T * T;
  // Moon's mean anomaly
  const Mm = 134.9633964 + 477198.8675055 * T + 0.0087414 * T * T;
  // Moon's argument of latitude
  const F = 93.2720950 + 483202.0175233 * T - 0.0036539 * T * T;

  const rad = Math.PI / 180;
  // Main perturbation corrections (degrees)
  Lm += 6.289 * Math.sin(Mm * rad);
  Lm += 1.274 * Math.sin((2 * D - Mm) * rad);
  Lm += 0.658 * Math.sin(2 * D * rad);
  Lm += 0.214 * Math.sin(2 * Mm * rad);
  Lm -= 0.186 * Math.sin(Ms * rad);
  Lm -= 0.114 * Math.sin(2 * F * rad);
  Lm += 0.059 * Math.sin((2 * D - 2 * Mm) * rad);
  Lm += 0.057 * Math.sin((2 * D - Ms - Mm) * rad);
  Lm += 0.053 * Math.sin((2 * D + Mm) * rad);
  Lm += 0.046 * Math.sin((2 * D - Ms) * rad);
  Lm -= 0.041 * Math.sin((Ms - Mm) * rad);
  Lm -= 0.035 * Math.sin(D * rad);
  Lm -= 0.030 * Math.sin((Ms + Mm) * rad);

  // Normalize tropical longitude 0-360
  const tropLong = ((Lm % 360) + 360) % 360;

  // Lahiri Ayanamsa (approximate)
  const ayanamsa = 23.042 + (y - 1950) * 0.01397;

  // Sidereal longitude
  let sidLong = tropLong - ayanamsa;
  sidLong = ((sidLong % 360) + 360) % 360;

  // Sun's mean longitude for Lagnam calc
  let Ls = 280.46646 + 36000.76983 * T + 0.0003032 * T * T;
  Ls += 1.9146 * Math.sin(Ms * rad) + 0.0200 * Math.sin(2 * Ms * rad);
  const tropSun = ((Ls % 360) + 360) % 360;
  const sidSun = ((tropSun - ayanamsa) % 360 + 360) % 360;

  return { moonLong: sidLong, sunLong: sidSun, jd, T };
}

function calcLagnam(jd, sunLong, lat, lng) {
  // Local Sidereal Time
  const T = (jd - 2451545.0) / 36525.0;
  let gmst = 280.46061837 + 360.98564736629 * (jd - 2451545.0)
    + 0.000387933 * T * T;
  gmst = ((gmst % 360) + 360) % 360;
  const lst = ((gmst + lng) % 360 + 360) % 360;

  const rad = Math.PI / 180;
  const e = 23.4393 - 0.0130 * T; // obliquity
  // Ascendant formula
  const ascRad = Math.atan2(
    Math.cos(lst * rad),
    -(Math.sin(lst * rad) * Math.cos(e * rad) + Math.tan(lat * rad) * Math.sin(e * rad))
  );
  let asc = ((ascRad / rad) + 360) % 360;
  // Correct quadrant
  if (Math.cos(lst * rad) < 0) asc += 180;
  asc = ((asc % 360) + 360) % 360;

  // Apply ayanamsa
  const ayanamsa = 23.042 + (2000 + T * 100 - 1950) * 0.01397;
  let sidAsc = ((asc - ayanamsa) % 360 + 360) % 360;
  return sidAsc;
}

// Tamil star names matching the select options
const STAR_NAMES = [
  'Ashwini','Bharani','Karthigai','Rohini','Mirigasirisham','Thiruvathirai',
  'Punarpoosam','Poosam','Ayilyam','Makam','Pooram','Uthiram',
  'Hastham','Chithirai','Swathi','Visakam','Anusham','Kettai',
  'Moolam','Pooradam','Uthradam','Thiruvonam',
  'Avittam','Sadhayam','Puratathi','Uthirattathi','Revathi'
];
const RAASI_NAMES = [
  'Mesham','Rishabam','Mithunam','Katagam',
  'Simham','Kanni','Tula','Vrichigam',
  'Dhanus','Makaram','Kumbha','Meena'
];
const LAGNAM_NAMES = [
  'Mesham','Rishabam','Mithunam','Katagam','Simham','Kanni',
  'Tula','Vrichigam','Dhanus','Makaram','Kumbha','Meena'
];
const PAADAM_NAMES = ['1st Paadam','2nd Paadam','3rd Paadam','4th Paadam'];

// Known city coordinates for lagnam calculation (South India focus)
const CITY_COORDS = {
  'pondicherry':{lat:11.9416,lng:79.8083},'puducherry':{lat:11.9416,lng:79.8083},
  'chennai':{lat:13.0827,lng:80.2707},'madras':{lat:13.0827,lng:80.2707},
  'bangalore':{lat:12.9716,lng:77.5946},'bengaluru':{lat:12.9716,lng:77.5946},
  'coimbatore':{lat:11.0168,lng:76.9558},'madurai':{lat:9.9252,lng:78.1198},
  'trichy':{lat:10.7905,lng:78.7047},'tiruchirappalli':{lat:10.7905,lng:78.7047},
  'salem':{lat:11.6643,lng:78.1460},'tirunelveli':{lat:8.7139,lng:77.7567},
  'erode':{lat:11.3410,lng:77.7172},'vellore':{lat:12.9165,lng:79.1325},
  'thanjavur':{lat:10.7870,lng:79.1378},'tanjore':{lat:10.7870,lng:79.1378},
  'dindigul':{lat:10.3624,lng:77.9695},'kanchipuram':{lat:12.8342,lng:79.7036},
  'kumbakonam':{lat:10.9617,lng:79.3881},'nagercoil':{lat:8.1833,lng:77.4119},
  'cuddalore':{lat:11.7480,lng:79.7714},'villupuram':{lat:11.9401,lng:79.4861},
  'tiruvannamalai':{lat:12.2253,lng:79.0747},'karaikal':{lat:10.9254,lng:79.8380},
  'mumbai':{lat:19.0760,lng:72.8777},'delhi':{lat:28.7041,lng:77.1025},
  'hyderabad':{lat:17.3850,lng:78.4867},'kolkata':{lat:22.5726,lng:88.3639},
  'pune':{lat:18.5204,lng:73.8567},'ahmedabad':{lat:23.0225,lng:72.5714},
  'kochi':{lat:9.9312,lng:76.2673},'cochin':{lat:9.9312,lng:76.2673},
  'trivandrum':{lat:8.5241,lng:76.9366},'thiruvananthapuram':{lat:8.5241,lng:76.9366},
  'mysore':{lat:12.2958,lng:76.6394},'mysuru':{lat:12.2958,lng:76.6394},
  'mangalore':{lat:12.9141,lng:74.8560},'tirupati':{lat:13.6288,lng:79.4192},
  'vijayawada':{lat:16.5062,lng:80.6480},'visakhapatnam':{lat:17.6868,lng:83.2185},
  'ariyankuppam':{lat:11.9130,lng:79.7899},'sedarapet':{lat:11.9500,lng:79.7900},
  'tindivanam':{lat:12.2320,lng:79.6530},'chidambaram':{lat:11.3990,lng:79.6930},
  'nagapattinam':{lat:10.7672,lng:79.8449},'ramanathapuram':{lat:9.3639,lng:78.8395},
  'sivaganga':{lat:10.1300,lng:78.4800},'theni':{lat:10.0104,lng:77.4768},
  'karur':{lat:10.9571,lng:78.0766},'namakkal':{lat:11.2189,lng:78.1674},
  'perambalur':{lat:11.2340,lng:78.8800},'ariyalur':{lat:11.1400,lng:79.0800},
  'tirupur':{lat:11.1085,lng:77.3411},'tiruppur':{lat:11.1085,lng:77.3411},
  'krishnagiri':{lat:12.5186,lng:78.2138},'dharmapuri':{lat:12.1357,lng:78.1602},
  'thoothukudi':{lat:8.7642,lng:78.1348},'tuticorin':{lat:8.7642,lng:78.1348},
  'kanyakumari':{lat:8.0883,lng:77.5385},'nilgiris':{lat:11.4916,lng:76.7337},
  'ooty':{lat:11.4102,lng:76.6950},'kodaikanal':{lat:10.2381,lng:77.4892},
};

function lookupCoords(place) {
  if (!place) return null;
  const key = place.toLowerCase().trim().replace(/[^a-z]/g, '');
  // Try exact match
  if (CITY_COORDS[key]) return CITY_COORDS[key];
  // Try partial match
  for (const [k, v] of Object.entries(CITY_COORDS)) {
    if (key.includes(k) || k.includes(key)) return v;
  }
  return null;
}

function autoCalcHoroscope(prefix) {
  // prefix = 'e' for edit, 'a' for add
  const dob = document.getElementById(prefix + '_dob')?.value;
  if (!dob) { toast('Please enter Date of Birth first', 'error'); return; }

  const birthHour = document.getElementById(prefix + '_birth_hour')?.value || '';
  const birthMin  = document.getElementById(prefix + '_birth_min')?.value || '';
  const birthAmPm = document.getElementById(prefix + '_birth_ampm')?.value || 'AM';
  const pob = document.getElementById(prefix + '_pob')?.value || '';

  // Parse birth time
  let hour24 = parseInt(birthHour) || 6; // default 6 AM if not set
  if (birthAmPm === 'PM' && hour24 !== 12) hour24 += 12;
  if (birthAmPm === 'AM' && hour24 === 12) hour24 = 0;
  const minutes = parseInt(birthMin) || 0;

  const dobDate = new Date(dob);
  const { moonLong, sunLong, jd, T } = calcMoonPosition(dobDate, hour24, minutes);

  // Star index (27 stars, each 13.333°)
  const starDeg = 360 / 27;
  const starIdx = Math.floor(moonLong / starDeg);
  const star = STAR_NAMES[starIdx] || '';

  // Paadam (4 per star)
  const posInStar = moonLong - (starIdx * starDeg);
  const paadamIdx = Math.floor(posInStar / (starDeg / 4));
  const paadam = PAADAM_NAMES[paadamIdx] || '';

  // Raasi (12 signs, each 30°)
  const raasiIdx = Math.floor(moonLong / 30);
  const raasi = RAASI_NAMES[raasiIdx] || '';

  // Lagnam (if place of birth is known)
  let lagnam = '';
  const coords = lookupCoords(pob);
  if (coords && birthHour) {
    const lagLong = calcLagnam(jd, sunLong, coords.lat, coords.lng);
    const lagIdx = Math.floor(lagLong / 30);
    lagnam = LAGNAM_NAMES[lagIdx] || '';
  }

  // Set values
  const setSelect = (id, val) => {
    const el = document.getElementById(id);
    if (!el || !val) return;
    // Try exact match first
    for (let i = 0; i < el.options.length; i++) {
      if (el.options[i].value === val || el.options[i].text === val) { el.selectedIndex = i; return; }
    }
    // Try partial match
    for (let i = 0; i < el.options.length; i++) {
      if (el.options[i].value.includes(val) || el.options[i].text.includes(val) || val.includes(el.options[i].value)) { el.selectedIndex = i; return; }
    }
  };

  setSelect(prefix + '_star', star);
  setSelect(prefix + '_raasi', raasi);
  setSelect(prefix + '_paadam', paadam);
  if (lagnam) setSelect(prefix + '_lagnam', lagnam);

  let msg = `Star: ${star}, Raasi: ${raasi.split(' (')[0]}, Paadam: ${paadam}`;
  if (lagnam) msg += `, Lagnam: ${lagnam}`;
  if (!birthHour) msg += '\n(Set birth time for more accuracy)';
  if (!coords && pob) msg += '\n(Place not recognized for Lagnam)';
  if (!pob) msg += '\n(Set birth place for Lagnam)';
  toast(msg, 'success');
}

// ADD
function openAdd() {
  openModal('addOverlay');
  FormAutoSave.showRestoreBanner('admin_add', '#addFormBanner', () => toast('Draft restored'));
}
async function saveAdd() {
  const _btn = document.getElementById('addProfileBtn');
  const mobile = document.getElementById('a_mobile').value.trim();
  const name   = document.getElementById('a_name').value.trim();
  const gender = document.getElementById('a_gender').value;
  const dob    = DobAge.getIso('a_dob');
  const dobInput = document.getElementById('a_dob');
  const age = dobInput && dobInput.value.length === 10 ? DobAge.calcAge(dobInput.value) : '';
  const religion = document.getElementById('a_religion').value;
  const caste  = document.getElementById('a_caste').value;
  const tongue = document.getElementById('a_tongue').value;
  const marital = document.getElementById('a_marital').value;
  if (!mobile || mobile.length < 10) { toast('Enter valid 10-digit mobile', 'error'); return; }
  // Check duplicate mobile
  if (profiles.some(p => p.mobile === mobile)) { toast('This mobile number already has a profile. One number = one profile only.', 'error'); return; }
  if (!name)     { toast('Please enter name', 'error'); return; }
  if (!gender)   { toast('Please select gender', 'error'); return; }
  if (!dob)      { toast('Please enter date of birth', 'error'); return; }
  const aAgeErr = DobAge.validateAge('a_dob', gender);
  if (aAgeErr) { toast(aAgeErr, 'error'); return; }
  if (!religion) { toast('Please select religion', 'error'); return; }
  if (!caste)    { toast('Please select caste', 'error'); return; }
  if (!tongue)   { toast('Please select mother tongue', 'error'); return; }
  if (!marital)  { toast('Please select marital status', 'error'); return; }
  // Input format validation
  const aValErrs = InputValidator.validateAll('a_');
  if (aValErrs.length > 0) { toast(aValErrs[0].msg + ' (' + aValErrs[0].id.replace('a_','') + ')', 'error'); document.getElementById(aValErrs[0].id)?.focus(); return; }

  // Show loading
  if (_btn) { _btn.disabled = true; _btn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px"><svg width="14" height="14" viewBox="0 0 24 24" style="animation:spin 1s linear infinite"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="31 31" stroke-linecap="round"/></svg> Adding...</span>'; }

  const rasiChart = [];
  const navChart  = [];
  const radio = (n, def) => document.querySelector('input[name="'+n+'"]:checked')?.value || def;

  const payload = {
    action: 'create', mobile, name, age: parseInt(age), gender,
    dob:            DobAge.getIso('a_dob'),
    birth_hour:     document.getElementById('a_birth_hour').value || '',
    birth_min:      document.getElementById('a_birth_min').value || '',
    birth_ampm:     document.getElementById('a_birth_ampm').value || 'AM',
    place_birth:    document.getElementById('a_pob').value,
    nativity:       document.getElementById('a_nativity').value,
    workplace:      document.getElementById('a_workplace').value,
    mother_tongue:  document.getElementById('a_tongue').value,
    marital:        document.getElementById('a_marital').value,
    nationality:    document.getElementById('a_nationality').value || 'Indian',
    own_house:      document.getElementById('a_own_house').value || 'Yes',
    born_as:        (document.getElementById('a_born_as_num').value || '') + (document.getElementById('a_born_as_type').value ? ' ' + document.getElementById('a_born_as_type').value : ''),
    father:         document.getElementById('a_father').value,
    father_alive:   document.getElementById('a_father_alive')?.value || '',
    father_job:     document.getElementById('a_father_job').value,
    mother:         document.getElementById('a_mother').value,
    mother_alive:   document.getElementById('a_mother_alive')?.value || '',
    mother_job:     document.getElementById('a_mother_job').value,
    sib_married_eb:   document.getElementById('a_sib_eb_m').value,
    sib_married_yb:   document.getElementById('a_sib_yb_m').value,
    sib_married_es:   document.getElementById('a_sib_es_m').value,
    sib_married_ys:   document.getElementById('a_sib_ys_m').value,
    sib_unmarried_eb: document.getElementById('a_sib_eb_u').value,
    sib_unmarried_yb: document.getElementById('a_sib_yb_u').value,
    sib_unmarried_es: document.getElementById('a_sib_es_u').value,
    sib_unmarried_ys: document.getElementById('a_sib_ys_u').value,
    others:         document.getElementById('a_others').value,
    height:         document.getElementById('a_height').value,
    weight:         document.getElementById('a_weight').value,
    blood_group:    document.getElementById('a_blood').value,
    complexion:     document.getElementById('a_complexion').value,
    diet:           radio('a_diet', 'Vegetarian'),
    disability:     radio('a_disability', 'No'),
    qualification:  document.getElementById('a_qualification').value,
    job:            document.getElementById('a_job').value,
    place_of_job:   document.getElementById('a_place_job').value,
    income:         document.getElementById('a_income').value,
    caste:          document.getElementById('a_caste').value,
    sub_caste:      document.getElementById('a_subcaste').value,
    gothram:        document.getElementById('a_gothram').value,
    star:           document.getElementById('a_star').value,
    raasi:          document.getElementById('a_raasi').value,
    paadam:         document.getElementById('a_paadam').value,
    lagnam:         document.getElementById('a_lagnam').value,
    religion:       document.getElementById('a_religion').value,
    dosham:         radio('a_dosham', 'No'),
    dosham_type:    document.getElementById('a_dosham_type')?.value || '',
    perm_address:   document.getElementById('a_perm_addr').value,
    present_address:document.getElementById('a_present_addr').value,
    present_area:   document.getElementById('a_present_area').value,
    present_city:   document.getElementById('a_present_city').value,
    present_district:document.getElementById('a_present_district').value,
    present_state:  document.getElementById('a_present_state').value,
    contact_person: document.getElementById('a_contact_person').value,
    alt_mobile:     document.getElementById('a_alt_mobile').value,
    email:          document.getElementById('a_email').value,
    rasi_chart:     JSON.stringify(rasiChart),
    nav_chart:      JSON.stringify(navChart),
    partner_qualification:      document.getElementById('a_p_qualification')?.value || '',
    partner_job:                document.getElementById('a_p_job')?.value || '',
    partner_job_requirement:    document.getElementById('a_p_job_req')?.value || '',
    partner_income_month:       document.getElementById('a_p_income')?.value || '',
    partner_age_from:           document.getElementById('a_p_age_from')?.value || '',
    partner_age_to:             document.getElementById('a_p_age_to')?.value || '',
    partner_diet:               document.getElementById('a_p_diet')?.value || '',
    partner_horoscope_required: document.getElementById('a_p_horoscope')?.value || '',
    partner_marital_status:     document.getElementById('a_p_marital')?.value || '',
    partner_caste:              document.getElementById('a_p_caste')?.value || '',
    partner_sub_caste:          document.getElementById('a_p_subcaste')?.value || '',
    partner_other_requirement:  document.getElementById('a_p_other')?.value || '',
  };

  try {
    // Use FormData to support photo uploads via public.php
    const fd = new FormData();
    // Map payload fields to public.php expected keys
    fd.append('contactNumber', payload.mobile);
    fd.append('name', payload.name);
    fd.append('gender', payload.gender);
    fd.append('dob', payload.dob || '');
    fd.append('birthHour', payload.birth_hour || '');
    fd.append('birthMin', payload.birth_min || '');
    fd.append('birthAmPm', payload.birth_ampm || 'AM');
    fd.append('placeBirth', document.getElementById('a_pob')?.value || '');
    fd.append('nativity', document.getElementById('a_nativity')?.value || '');
    fd.append('motherTongue', payload.mother_tongue || '');
    fd.append('maritalStatus', payload.marital || '');
    fd.append('fatherName', payload.father || '');
    fd.append('fatherAlive', payload.father_alive || '');
    fd.append('fatherJob', payload.father_job || '');
    fd.append('motherName', payload.mother || '');
    fd.append('motherAlive', payload.mother_alive || '');
    fd.append('motherJob', payload.mother_job || '');
    ['sib_married_eb','sib_married_yb','sib_married_es','sib_married_ys',
     'sib_unmarried_eb','sib_unmarried_yb','sib_unmarried_es','sib_unmarried_ys'].forEach(k => {
      fd.append(k.replace(/sib_married_/,'sibMarried').replace(/sib_unmarried_/,'sibUnmarried')
        .replace(/_eb/,'EB').replace(/_yb/,'YB').replace(/_es/,'ES').replace(/_ys/,'YS'), payload[k] || '');
    });
    fd.append('others', payload.others || '');
    fd.append('height', payload.height || '');
    fd.append('weight', payload.weight || '');
    fd.append('bloodGroup', payload.blood_group || '');
    fd.append('complexion', payload.complexion || '');
    fd.append('diet', payload.diet || '');
    fd.append('disability', payload.disability || '');
    fd.append('qualification', payload.qualification || '');
    fd.append('job', payload.job || '');
    fd.append('placeJob', payload.place_of_job || '');
    fd.append('incomeMonth', payload.income || '');
    fd.append('caste', payload.caste || '');
    fd.append('subCaste', payload.sub_caste || '');
    fd.append('gothram', payload.gothram || '');
    fd.append('star', payload.star || '');
    fd.append('raasi', payload.raasi || '');
    fd.append('padam', payload.paadam || '');
    fd.append('laknam', payload.lagnam || '');
    fd.append('dosham', payload.dosham || '');
    fd.append('religion', payload.religion || '');
    fd.append('nationality', payload.nationality || 'Indian');
    fd.append('ownHouse', payload.own_house || 'Yes');
    fd.append('bornAs', payload.born_as || '');
    fd.append('workplace', payload.workplace || '');
    fd.append('doshamType', payload.dosham_type || '');
    fd.append('permanentAddress', payload.perm_address || '');
    fd.append('presentAddress', payload.present_address || '');
    fd.append('presentArea', payload.present_area || '');
    fd.append('presentCity', payload.present_city || '');
    fd.append('presentDistrict', payload.present_district || '');
    fd.append('presentState', payload.present_state || '');
    fd.append('contactPerson', payload.contact_person || '');
    fd.append('altMobile', payload.alt_mobile || '');
    fd.append('email', payload.email || '');
    fd.append('partnerQualification', payload.partner_qualification || '');
    fd.append('partnerJob', payload.partner_job || '');
    fd.append('partnerJobRequirement', payload.partner_job_requirement || '');
    fd.append('partnerIncomeMonth', payload.partner_income_month || '');
    fd.append('partnerAgeFrom', payload.partner_age_from || '');
    fd.append('partnerAgeTo', payload.partner_age_to || '');
    fd.append('partnerDiet', payload.partner_diet || '');
    fd.append('partnerHoroscopeRequired', payload.partner_horoscope_required || '');
    fd.append('partnerMaritalStatus', payload.partner_marital_status || '');
    fd.append('partnerCaste', payload.partner_caste || '');
    fd.append('partnerSubCaste', payload.partner_sub_caste || '');
    fd.append('partnerOtherRequirement', payload.partner_other_requirement || '');

    // Attach photo files (use processed versions if available)
    const ap_ = (prefix, key) => _processedAdminPhotos[prefix] || document.getElementById(prefix + '_file')?.files[0];
    if (ap_('a_photo1')) fd.append('photo1', ap_('a_photo1'));
    if (ap_('a_photo2')) fd.append('photo2', ap_('a_photo2'));
    if (ap_('a_photo3')) fd.append('photo3', ap_('a_photo3'));
    if (ap_('a_rasi_photo')) fd.append('rasiPhoto', ap_('a_rasi_photo'));
    if (ap_('a_amsam_photo')) fd.append('amsamPhoto', ap_('a_amsam_photo'));

    const resp = await fetch('api/public.php', { method: 'POST', body: fd, credentials: 'same-origin' });
    const data = await resp.json();
    if (!resp.ok || data.error) throw new Error(data.error || 'Failed');

    FormAutoSave.clear('admin_add');
    closeModal('addOverlay');
    // Reset photo previews
    ['a_photo1','a_photo2','a_photo3','a_rasi_photo','a_amsam_photo'].forEach(p => {
      const prev = document.getElementById(p+'_preview');
      const ph = document.getElementById(p+'_placeholder');
      if (prev) { prev.style.display='none'; prev.src=''; }
      if (ph) ph.style.display='';
    });
    await loadAll();
    render(); postRender();
    toast('Profile added — ' + (data.cp_id || ''));
  } catch(e) {
    toast(e.message || 'Failed to add profile', 'error');
  } finally {
    if (_btn) { _btn.disabled = false; _btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add Profile'; }
  }
}

// EDIT
// Fetch full profile detail and merge into profiles[i].
// The list endpoint only returns a trimmed column set for speed, so the
// View/Edit modals would otherwise see most fields as empty.
async function ensureProfileDetail(i) {
  const p = profiles[i];
  if (!p || !p.cpId) return;
  if (p._detailLoaded) return;
  try {
    const data = await apiGet('api/admin/profiles.php?cp_id=' + encodeURIComponent(p.cpId));
    if (data && data.profile) {
      const full = mapRow(data.profile);
      profiles[i] = Object.assign({}, p, full, { _detailLoaded: true });
    }
  } catch (e) {
    // Fall back to the list-cached row; modal will show what it has.
  }
}

async function openView(i) {
  await ensureProfileDetail(i);
  const p = profiles[i];
  if (!p) return;
  const v = (val) => val && val !== '' && val !== 'null' ? `<span style="color:#1a1a2e;font-weight:500">${val}</span>` : `<span style="color:#ccc;font-style:italic">—</span>`;
  const row = (label, val) => `<tr><td style="padding:8px 12px;color:#6b7280;font-weight:600;width:35%;font-size:13px;border-bottom:1px solid #f3f4f6">${label}</td><td style="padding:8px 12px;font-size:13px;border-bottom:1px solid #f3f4f6">${v(val)}</td></tr>`;
  const section = (icon, title) => `<tr><td colspan="2" style="padding:14px 12px 8px;background:linear-gradient(135deg,#f8fafc,#eff6ff);font-weight:700;color:#1e3a5f;font-size:13px;letter-spacing:0.5px;border-bottom:2px solid #dbeafe">${icon} ${title}</td></tr>`;
  const picSrc = photoSrc(p.photo1);

  const html = `
    <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:16px;flex-wrap:wrap">
      ${picSrc ? `<img src="${picSrc}" style="width:100px;height:120px;object-fit:cover;border-radius:12px;border:2px solid #dbeafe" onerror="this.style.display='none'">` : ''}
      <div>
        <div style="font-size:20px;font-weight:700;color:#1e3a5f">${p.name}</div>
        <div style="font-size:13px;color:#6b7280;margin-top:4px">${p.cpId} &middot; ${p.gender || ''} &middot; ${p.age || ''} yrs</div>
        <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap">
          ${statusBadge(p.status)} ${planBadge(p.plan)}
        </div>
      </div>
    </div>
    <table style="width:100%;border-collapse:collapse">
      ${section('👤','Personal Details')}
      ${row('Full Name', p.name)}
      ${row('Gender', p.gender)}
      ${row('Date of Birth', p.dob)}
      ${row('Age', p.age ? p.age + ' yrs' : '')}
      ${row('Birth Time', (p.birthHour||p.birth_hour) && (p.birthMin||p.birth_min) ? (p.birthHour||p.birth_hour)+':'+(p.birthMin||p.birth_min)+' '+(p.birthAmpm||p.birth_ampm||'') : '')}
      ${row('Place of Birth', p.placeBirth || p.place_birth)}
      ${row('Religion', p.religion)}
      ${row('Mother Tongue', p.tongue || p.mother_tongue)}
      ${row('Marital Status', p.marital)}
      ${row('Nativity', p.nativity)}
      ${row('Nationality', p.nationality)}
      ${row('Own House', p.ownHouse || p.own_house)}
      ${row('Born As', p.bornAs || p.born_as)}
      ${row('Present Country', p.workplace)}
      ${row('Complexion', p.complexion)}

      ${section('👨‍👩‍👧‍👦','Family Details')}
      ${row('Father Name', p.father)}
      ${row('Father Status', p.fatherAlive || p.father_alive)}
      ${row('Father Job', p.fatherJob || p.father_job)}
      ${row('Mother Name', p.mother)}
      ${row('Mother Status', p.motherAlive || p.mother_alive)}
      ${row('Mother Job', p.motherJob || p.mother_job)}
      ${row('Married Brothers', p.sibMarriedEb || p.sib_married_eb)}
      ${row('Unmarried Brothers', p.sibUnmarriedEb || p.sib_unmarried_eb)}
      ${row('Married Sisters', p.sibMarriedEs || p.sib_married_es)}
      ${row('Unmarried Sisters', p.sibUnmarriedEs || p.sib_unmarried_es)}

      ${section('⚖️','Physical Attributes')}
      ${row('Height', p.height)}
      ${row('Weight', p.weight)}
      ${row('Blood Group', p.blood || p.blood_group)}
      ${row('Diet', p.diet)}
      ${row('Disability', p.disability)}

      ${section('🎓','Education & Career')}
      ${row('Qualification', p.qualification)}
      ${row('Job / Occupation', p.job)}
      ${row('Place of Job', p.placeJob || p.place_of_job)}
      ${row('Income', p.income && /\\d/.test(p.income) ? 'Rs. ' + p.income : p.income)}

      ${section('🪐','Astrology')}
      ${row('Caste', p.caste)}
      ${row('Sub Caste', p.subcaste || p.sub_caste)}
      ${row('Gothram', p.gothram)}
      ${row('Star', p.star)}
      ${row('Raasi', p.raasi)}
      ${row('Paadam', p.paadam)}
      ${row('Lagnam', p.lagnam)}
      ${row('Dosham', p.dosham)}
      ${row('Dosham Type', p.doshamType || p.dosham_type)}

      ${section('💑','Partner Expectations')}
      ${row('Qualification', p.partnerQualification || p.partner_qualification)}
      ${row('Job', p.partnerJob || p.partner_job)}
      ${row('Job Requirement', p.partnerJobRequirement || p.partner_job_requirement)}
      ${row('Income', (() => { const v = p.partnerIncomeMonth || p.partner_income_month; return v && /\\d/.test(v) ? 'Rs. ' + v : v; })())}
      ${row('Age Range', (() => { const f=p.partnerAgeFrom||p.partner_age_from||''; const t=p.partnerAgeTo||p.partner_age_to||''; return f ? f + (t ? ' - '+t : '') + ' yrs' : ''; })())}
      ${row('Diet', p.partnerDiet || p.partner_diet)}
      ${row('Horoscope Required', p.partnerHoroscopeRequired || p.partner_horoscope_required)}
      ${row('Caste', p.partnerCaste || p.partner_caste)}
      ${row('Sub Caste', p.partnerSubCaste || p.partner_sub_caste)}
      ${row('Marital Status', p.partnerMaritalStatus || p.partner_marital_status)}

      ${section('🔯','Horoscope Photos')}
    </table>
    ${(() => {
      const rasiSrc  = photoSrc(p.rasi_photo  || p.rasiPhoto  || '');
      const amsamSrc = photoSrc(p.amsam_photo || p.amsamPhoto || '');
      if (!rasiSrc && !amsamSrc) return '<p style="padding:8px 12px;color:#9ca3af;font-size:13px">No horoscope photos uploaded.</p>';
      return '<div style="display:flex;gap:16px;flex-wrap:wrap;padding:12px">'
        + (rasiSrc  ? '<div style="text-align:center"><img src="'+rasiSrc+'"  style="max-width:220px;border-radius:10px;border:2px solid #dbeafe" onerror="this.parentElement.style.display=\'none\'"><div style="font-size:12px;color:#6b7280;margin-top:6px;font-weight:600">Rasi Chart</div></div>' : '')
        + (amsamSrc ? '<div style="text-align:center"><img src="'+amsamSrc+'" style="max-width:220px;border-radius:10px;border:2px solid #dbeafe" onerror="this.parentElement.style.display=\'none\'"><div style="font-size:12px;color:#6b7280;margin-top:6px;font-weight:600">Amsam Chart</div></div>' : '')
        + '</div>';
    })()}
    <table style="width:100%;border-collapse:collapse">
      ${section('📞','Contact Information')}
      ${row('Mobile', p.mobile)}
      ${row('Alt. Mobile', p.altMobile || p.alt_mobile)}
      ${row('Email', p.email)}
      ${row('Contact Person', p.contactPerson || p.contact_person)}
      ${row('Permanent Address', p.permAddr || p.perm_address)}
      ${row('Present Address', p.presentAddr || p.present_address)}

      ${section('📋','Profile Info')}
      ${row('CP ID', p.cpId)}
      ${row('Status', p.status)}
      ${row('Plan', p.plan)}
      ${row('Created', p.created)}
      ${row('Created By', (p.createdBy || p.created_by || 'admin') === 'user' ? '👤 User (Self)' : '🛡 Admin')}
      ${row('Approved', p.approved)}
      ${row('Expiry', p.expiry)}
    </table>
    <div style="display:flex;gap:10px;justify-content:center;margin-top:18px;padding-top:14px;border-top:1px solid #e5e7eb">
      <button class="btn btn-outline btn-sm" onclick="closeModal('viewOverlay');openEdit(${i})" style="padding:8px 24px">✏️ Edit</button>
      <button class="btn btn-sm" onclick="closeModal('viewOverlay');printProfile(${i})" style="padding:8px 24px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe">🖨️ Print</button>
      <button class="btn btn-sm" onclick="closeModal('viewOverlay')" style="padding:8px 24px">Close</button>
    </div>`;
  document.getElementById('viewContent').innerHTML = html;
  openModal('viewOverlay');
}

function openOfficeInfo(i) {
  const p = profiles[i];
  if (!p) return;

  // --- Data By (User or Office) ---
  const createdByRaw = p.createdBy || p.created_by || 'admin';
  const isUser = createdByRaw === 'user';
  const dataByLabel = isUser ? 'User (Self-Registered)' : 'Office (Admin)';
  const dataByIcon = isUser ? '👤' : '🛡️';
  const dataByName = isUser ? (p.name || '—') : (loginAdminObj ? loginAdminObj.name : 'Admin');
  const dataByRole = isUser ? 'Member' : (loginAdminObj ? (loginAdminObj.role || 'admin').charAt(0).toUpperCase() + (loginAdminObj.role || 'admin').slice(1) : 'Admin');

  // --- Mandatory Check ---
  const mandatoryFields = [p.name, p.gender, p.dob, p.mobile, p.religion, p.caste, p.tongue, p.marital];
  const mandatoryOk = mandatoryFields.every(v => v && v.toString().trim() !== '' && v !== '-Select-');
  const mandBadge = mandatoryOk
    ? '<span style="color:#16a34a;font-weight:700;font-size:13px;background:#f0fdf4;padding:3px 12px;border-radius:20px;border:1px solid #bbf7d0">✓ Yes — All filled</span>'
    : '<span style="color:#dc2626;font-weight:700;font-size:13px;background:#fef2f2;padding:3px 12px;border-radius:20px;border:1px solid #fecaca">✕ No — Incomplete</span>';

  // --- Approved By ---
  const approvedBy = p.approvedBy || p.approved_by || (p.status === 'Approved' ? (loginAdminObj ? loginAdminObj.name : 'Admin') : '—');
  const approvedDate = p.approved || p.approvedDate || p.approved_date || '—';

  // --- Bill Info ---
  const bill = bills.find(b => b.cpId === p.cpId);
  const billNumber = bill ? (bill.billNo || bill.billNumber || bill.bill_no || bill.id || 'BILL-' + p.cpId) : '—';
  const planName = bill ? (bill.planName || bill.plan || p.plan || '—') : (p.plan || '—');
  const billAmount = bill ? '₹' + Number(bill.amount || 0).toLocaleString('en-IN') : '—';
  const paymentType = bill ? (bill.payment || bill.paymentType || bill.payment_type || '—') : '—';
  const billDate = bill ? (bill.billedDate || bill.billed_date || '—') : '—';
  const billedBy = bill ? (bill.billedBy || bill.billed_by || '—') : '—';

  const row = (icon, label, value, highlight) => `
    <tr>
      <td style="padding:10px 14px;font-weight:600;font-size:12.5px;color:#6b7280;background:#f8f7f5;border-bottom:1px solid #e5e0d8;white-space:nowrap;width:38%">
        ${icon} ${label}
      </td>
      <td style="padding:10px 14px;font-size:13px;border-bottom:1px solid #e5e0d8;${highlight ? 'font-weight:600;color:'+highlight : ''}">
        ${value}
      </td>
    </tr>`;

  const sectionHead = (icon, title, color) => `
    <tr>
      <td colspan="2" style="padding:12px 14px 8px;background:${color || 'linear-gradient(135deg,#f8fafc,#f5f3ff)'};font-weight:700;color:#312e81;font-size:13px;letter-spacing:0.5px;border-bottom:2px solid #ddd6fe">
        ${icon} ${title}
      </td>
    </tr>`;

  const html = `
    <!-- Profile Header -->
    <div style="display:flex;gap:16px;align-items:center;margin-bottom:18px;padding:14px 16px;background:linear-gradient(135deg,#1a1a2e,#312e81);border-radius:12px">
      <div style="width:44px;height:44px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;color:#fff;flex-shrink:0">${initials(p.name)}</div>
      <div>
        <div style="font-weight:700;font-size:16px;color:#fff">${p.name}</div>
        <div style="font-size:12px;color:rgba(255,255,255,.55)">${p.cpId} · ${p.mobile} · ${p.gender || ''}</div>
      </div>
      <div style="margin-left:auto;display:flex;gap:6px">
        ${statusBadge(p.status)} ${planBadge(p.plan)}
      </div>
    </div>

    <table style="width:100%;border-collapse:collapse;border:1px solid #e5e0d8;border-radius:10px;overflow:hidden">
      ${sectionHead('📋', 'Profile Source & Verification')}
      ${row('📥', 'Data By', `${dataByIcon} <strong>${dataByLabel}</strong>`)}
      ${row('👤', 'Name', `<strong>${dataByName}</strong>`)}
      ${row('🏷️', 'Role', `<span style="background:#e0e7ff;color:#3730a3;padding:2px 10px;border-radius:12px;font-size:12px;font-weight:600">${dataByRole}</span>`)}
      ${row('📌', 'Auto Generated', '<span style="color:#6d28d9;font-weight:600">Yes</span>')}
      ${row('✅', 'Mandatory', mandBadge)}

      ${sectionHead('🛡️', 'Approval Details')}
      ${row('👤', 'Approved By', `<strong>${approvedBy}</strong>`)}
      ${row('📅', 'Approved Date', approvedDate, approvedDate !== '—' ? '#16a34a' : '')}

      ${sectionHead('💰', 'Billing Information')}
      ${row('🔢', 'Bill Number', `<code style="font-size:13px;background:#f3f4f6;padding:3px 10px;border-radius:5px;font-weight:600">${billNumber}</code>`)}
      ${row('📦', 'Plan Name', planName)}
      ${row('💵', 'Bill Amount', billAmount, billAmount !== '—' ? '#16a34a' : '')}
      ${row('💳', 'Payment Type', paymentType)}
      ${row('📅', 'Bill Date', billDate)}
      ${row('🧑‍💼', 'Billed By', billedBy)}
    </table>

    <div style="display:flex;gap:10px;justify-content:center;margin-top:18px;padding-top:14px;border-top:1px solid #e5e7eb">
      <button class="btn btn-sm" onclick="closeModal('officeInfoOverlay')" style="padding:8px 24px">Close</button>
    </div>`;

  document.getElementById('officeInfoContent').innerHTML = html;
  openModal('officeInfoOverlay');
}

async function openEdit(i) {
  await ensureProfileDetail(i);
  idx = i;
  const p = profiles[i];
  const f = (id, v) => { const el = document.getElementById(id); if (el) el.value = v || ''; };
  f('e_mobile', p.mobile); f('e_name', p.name); f('e_age', p.age);
  // Gender: capitalize first letter to match dropdown options
  const eGender = (p.gender || '').charAt(0).toUpperCase() + (p.gender || '').slice(1).toLowerCase();
  f('e_gender', eGender); DobAge.setFromIso('e_dob', p.dob);
  f('e_religion', p.religion);
  // Set caste — add to dropdown if not in list
  const eCasteEl = document.getElementById('e_caste');
  if (p.caste && eCasteEl && !Array.from(eCasteEl.options).some(o => o.value === p.caste)) {
    eCasteEl.innerHTML += `<option value="${p.caste}">${p.caste}</option>`;
  }
  f('e_caste', p.caste);
  populateSubcasteFor('e_caste', 'e_subcaste', p.subcaste || p.sub_caste || '');
  f('e_tongue', p.mother_tongue); f('e_marital', p.marital);
  populateNationality('e_nationality', p.nationality || 'Indian');
  f('e_own_house', p.own_house || 'Yes');
  // Born as: "2 Son" → num=2, type=Son
  const bornAs = (p.bornAs || p.born_as || '').trim();
  const bornMatch = bornAs.match(/^(\d+)\s*(Son|Daughter)?$/i);
  if (bornMatch) {
    document.getElementById('e_born_as_num').value = bornMatch[1] || '';
    document.getElementById('e_born_as_type').value = bornMatch[2] ? bornMatch[2].charAt(0).toUpperCase() + bornMatch[2].slice(1).toLowerCase() : '';
  } else {
    document.getElementById('e_born_as_num').value = '';
    document.getElementById('e_born_as_type').value = '';
  }
  f('e_birth_hour', p.birth_hour); f('e_birth_min', p.birth_min); f('e_birth_ampm', p.birth_ampm || 'AM');
  f('e_pob', p.place_birth); f('e_nativity', p.nativity); populateCountry('e_workplace', p.workplace || 'India');
  f('e_others', p.others);
  f('e_father', p.father); f('e_father_job', p.father_job);
  const setRadio = (n, v) => { const r = document.querySelector('input[name="'+n+'"][value="'+(v||'Yes')+'"]'); if (r) r.checked = true; };
  f('e_father_alive', p.father_alive); f('e_mother_alive', p.mother_alive);
  f('e_mother', p.mother); f('e_mother_job', p.mother_job);
  // Height & weight — add to dropdown if value not in list
  ['e_height','e_weight'].forEach((id, i) => {
    const val = i === 0 ? p.height : p.weight;
    const el = document.getElementById(id);
    if (val && el && !Array.from(el.options).some(o => o.value === val)) {
      el.innerHTML += `<option value="${val}">${val}</option>`;
    }
  });
  f('e_height', p.height); f('e_weight', p.weight); f('e_blood', p.blood_group);
  f('e_complexion', p.complexion);
  setRadio('e_diet', p.diet); setRadio('e_disability', p.disability); setRadio('e_dosham', p.dosham);
  populateDoshamType('e_dosham_type'); f('e_dosham_type', p.dosham_type || '');
  toggleDoshamType(p.dosham || 'No', 'e_dosham_type_wrap');
  f('e_qualification', p.qualification); f('e_job', p.job);
  f('e_place_job', p.place_of_job); f('e_income', p.income);
  f('e_gothram', p.gothram);
  f('e_star', p.star); f('e_raasi', p.raasi); f('e_paadam', p.paadam); f('e_lagnam', p.lagnam);
  f('e_p_qualification', p.partner_qualification); f('e_p_job', p.partner_job);
  f('e_p_job_req', p.partner_job_requirement); f('e_p_income', p.partner_income_month);
  f('e_p_age_from', p.partner_age_from); f('e_p_age_to', p.partner_age_to);
  f('e_p_diet', p.partner_diet); PartnerCaste.setValue('e_p_caste_box', 'e_p_caste', p.partner_caste);
  f('e_p_marital', p.partner_marital_status); f('e_p_horoscope', p.partner_horoscope_required);
  PartnerCaste.setSubValue('e_p_subcaste_box', 'e_p_subcaste', p.partner_sub_caste);
  PartnerCaste.updateSubCasteWidget('e_p_caste_box');
  f('e_p_other', p.partner_other_requirement);
  f('e_perm_addr', p.perm_address); f('e_present_addr', p.present_address);
  setAddressLocation('e', p.present_area, p.present_city, p.present_district, p.present_state);
  f('e_contact_person', p.contact_person); f('e_alt_mobile', p.alt_mobile); f('e_email', p.email);
  // Siblings
  f('e_sib_eb_m', p.sib_married_eb || '0'); f('e_sib_yb_m', p.sib_married_yb || '0');
  f('e_sib_es_m', p.sib_married_es || '0'); f('e_sib_ys_m', p.sib_married_ys || '0');
  f('e_sib_eb_u', p.sib_unmarried_eb || '0'); f('e_sib_yb_u', p.sib_unmarried_yb || '0');
  f('e_sib_es_u', p.sib_unmarried_es || '0'); f('e_sib_ys_u', p.sib_unmarried_ys || '0');
  // Horoscope chart photos
  ['e_rasi_photo','e_amsam_photo'].forEach((prefix, i) => {
    const key = i === 0 ? 'rasi_photo' : 'amsam_photo';
    const src = photoSrc(p[key] || '');
    const prev = document.getElementById(prefix+'_preview');
    const ph = document.getElementById(prefix+'_placeholder');
    if (src && prev) { prev.src = src; prev.style.display='block'; if(ph) ph.style.display='none'; }
    else if (prev) { prev.style.display='none'; prev.src=''; if(ph) ph.style.display=''; }
  });
  // Show existing photos
  ['e_photo1','e_photo2','e_photo3'].forEach((prefix, pi) => {
    const src = photoSrc(p['photo'+(pi+1)] || '');
    const prev = document.getElementById(prefix+'_preview');
    const ph = document.getElementById(prefix+'_placeholder');
    if (src && prev) { prev.src = src; prev.style.display='block'; if(ph) ph.style.display='none'; }
    else if (prev) { prev.style.display='none'; prev.src=''; if(ph) ph.style.display=''; }
  });
  // Face circle from photo1
  const faceSrc = photoSrc(p.photo1 || '');
  const faceImg = document.getElementById('e_photo1_face');
  const facePh  = document.getElementById('e_photo1_face_ph');
  if (faceSrc && faceImg) { faceImg.src = faceSrc; faceImg.style.display='block'; if(facePh) facePh.style.display='none'; }
  else if (faceImg) { faceImg.style.display='none'; faceImg.src=''; if(facePh) facePh.style.display=''; }
  openModal('editOverlay');
  // Clear stale draft since we're loading fresh data from server
  FormAutoSave.clear('admin_edit');
}
async function saveEdit() {
  const _btn = document.getElementById('editProfileBtn');
  const g = id => document.getElementById(id)?.value || '';
  const radio = (n, def) => document.querySelector('input[name="'+n+'"]:checked')?.value || def;
  const name = g('e_name').trim();
  if (!name) { toast('Please enter name', 'error'); return; }
  if (!g('e_gender')) { toast('Please select gender', 'error'); return; }
  if (!DobAge.getIso('e_dob')) { toast('Please enter date of birth (dd/mm/yyyy)', 'error'); return; }
  const eAgeErr = DobAge.validateAge('e_dob', g('e_gender'));
  if (eAgeErr) { toast(eAgeErr, 'error'); return; }
  if (!g('e_religion')) { toast('Please select religion', 'error'); return; }
  if (!g('e_caste')) { toast('Please select caste', 'error'); return; }
  if (!g('e_tongue')) { toast('Please select mother tongue', 'error'); return; }
  if (!g('e_marital')) { toast('Please select marital status', 'error'); return; }
  // Input format validation
  const eValErrs = InputValidator.validateAll('e_');
  if (eValErrs.length > 0) { toast(eValErrs[0].msg + ' (' + eValErrs[0].id.replace('e_','') + ')', 'error'); document.getElementById(eValErrs[0].id)?.focus(); return; }

  // Show loading
  if (_btn) { _btn.disabled = true; _btn.textContent = 'Saving...'; }

  const payload = {
    action: 'update', cp_id: profiles[idx].cpId, mobile: profiles[idx].mobile,
    name, gender: g('e_gender'), dob: DobAge.getIso('e_dob'),
    religion: g('e_religion'), caste: g('e_caste'),
    mother_tongue: g('e_tongue'), marital: g('e_marital'), nationality: g('e_nationality'), own_house: g('e_own_house'),
    born_as: (g('e_born_as_num') || '') + (document.getElementById('e_born_as_type').value ? ' ' + document.getElementById('e_born_as_type').value : ''),
    birth_hour: g('e_birth_hour'), birth_min: g('e_birth_min'), birth_ampm: g('e_birth_ampm'),
    place_birth: g('e_pob'), nativity: g('e_nativity'), workplace: g('e_workplace'), others: g('e_others'),
    father: g('e_father'), father_alive: g('e_father_alive'), father_job: g('e_father_job'),
    mother: g('e_mother'), mother_alive: g('e_mother_alive'), mother_job: g('e_mother_job'),
    sib_married_eb: g('e_sib_eb_m'), sib_married_yb: g('e_sib_yb_m'),
    sib_married_es: g('e_sib_es_m'), sib_married_ys: g('e_sib_ys_m'),
    sib_unmarried_eb: g('e_sib_eb_u'), sib_unmarried_yb: g('e_sib_yb_u'),
    sib_unmarried_es: g('e_sib_es_u'), sib_unmarried_ys: g('e_sib_ys_u'),
    height: g('e_height'), weight: g('e_weight'), blood_group: g('e_blood'),
    complexion: g('e_complexion'), diet: radio('e_diet','Vegetarian'), disability: radio('e_disability','No'),
    qualification: g('e_qualification'), job: g('e_job'), place_of_job: g('e_place_job'), income: g('e_income'),
    sub_caste: g('e_subcaste'), gothram: g('e_gothram'),
    star: g('e_star'), raasi: g('e_raasi'), paadam: g('e_paadam'), lagnam: g('e_lagnam'),
    dosham: radio('e_dosham','No'), dosham_type: g('e_dosham_type'),
    partner_qualification: g('e_p_qualification'), partner_job: g('e_p_job'),
    partner_job_requirement: g('e_p_job_req'), partner_income_month: g('e_p_income'),
    partner_age_from: g('e_p_age_from'), partner_age_to: g('e_p_age_to'),
    partner_diet: g('e_p_diet'), partner_caste: g('e_p_caste'),
    partner_marital_status: g('e_p_marital'), partner_horoscope_required: g('e_p_horoscope'),
    partner_sub_caste: g('e_p_subcaste'), partner_other_requirement: g('e_p_other'),
    perm_address: g('e_perm_addr'), present_address: g('e_present_addr'),
    present_area: g('e_present_area'), present_city: g('e_present_city'),
    present_district: g('e_present_district'), present_state: g('e_present_state'),
    contact_person: g('e_contact_person'), alt_mobile: g('e_alt_mobile'), email: g('e_email'),
  };
  try {
    // Use FormData so we can send photo files alongside the text fields
    const fd = new FormData();
    Object.keys(payload).forEach(k => fd.append(k, payload[k] == null ? '' : payload[k]));
    const ep_ = (prefix) => _processedAdminPhotos[prefix] || document.getElementById(prefix + '_file')?.files[0];
    if (ep_('e_photo1')) fd.append('photo1', ep_('e_photo1'));
    if (ep_('e_photo2')) fd.append('photo2', ep_('e_photo2'));
    if (ep_('e_photo3')) fd.append('photo3', ep_('e_photo3'));
    if (ep_('e_rasi_photo'))  fd.append('rasiPhoto',  ep_('e_rasi_photo'));
    if (ep_('e_amsam_photo')) fd.append('amsamPhoto', ep_('e_amsam_photo'));

    const resp = await fetch('api/admin/profiles.php', {
      method: 'POST', credentials: 'same-origin', body: fd
    });
    const data = await resp.json();
    if (!data.ok) throw new Error(data.error || 'Failed');
    // Clear processed photo cache for edit prefixes
    ['e_photo1','e_photo2','e_photo3','e_rasi_photo','e_amsam_photo'].forEach(p => delete _processedAdminPhotos[p]);
    FormAutoSave.clear('admin_edit');
    closeModal('editOverlay');
    await loadAll();
    render(); postRender();
    pushAdminLog('Edited Profile', name + ' · ' + profiles[idx]?.cpId, 'profile');
    toast('Profile updated');
  } catch(e) {
    toast(e.message || 'Failed to update profile', 'error');
  } finally {
    if (_btn) { _btn.disabled = false; _btn.textContent = 'Save Changes'; }
  }
}

// DELETE
function openDelete(i) {
  idx = i;
  document.getElementById('d_cpid').value   = profiles[i].cpId;
  document.getElementById('d_mobile').value = profiles[i].mobile;
  document.getElementById('d_reason').value = '';
  // Auto-fill from logged-in admin
  const activeAdmin = admins.find(a => a.status === 'active');
  document.getElementById('d_admin').value  = activeAdmin ? activeAdmin.name : '—';
  openModal('deleteOverlay');
}
function confirmDelete() {
  const reason    = document.getElementById('d_reason').value.trim();
  const adminName = document.getElementById('d_admin').value.trim();
  if (!reason) { toast('Please enter a reason for deletion', 'error'); return; }
  const now = nowStamp();
  deleted.push({ ...profiles[idx], reason, deletedBy: adminName, deletedAt: now });
  actionLog.push({
    action: 'DELETE', cpId: profiles[idx].cpId, memberName: profiles[idx].name,
    admin: adminName, note: reason, timestamp: now
  });
  profiles.splice(idx, 1);
  closeModal('deleteOverlay');
  render(); postRender();
  pushAdminLog('Deleted Profile', profiles[idx]?.cpId || cpId + ' — ' + reason, 'profile');
  saveState();
  pushNotif('🗑️ Profile deleted', 'A profile was moved to the deleted archive.');
  toast('Profile deleted');
}

// UNDO DELETE — restore profile back to profiles list
function undoDelete(i) {
  const d = deleted[i];
  const activeAdmin = admins.find(a => a.status === 'active');
  const adminName = activeAdmin ? activeAdmin.name : 'Admin';
  const now = nowStamp();
  const { reason, deletedBy, deletedAt, ...restored } = d;
  profiles.push(restored);
  actionLog.push({
    action: 'RESTORE', cpId: d.cpId, memberName: d.name,
    admin: adminName, note: 'Restored (was: ' + reason + ')', timestamp: now
  });
  deleted.splice(i, 1);
  render(); postRender();
  pushAdminLog('Restored Profile', d.name + ' · ' + d.cpId, 'profile');
  saveState();
  pushNotif('↩️ Profile restored', d.name + ' was restored from the archive.');
  toast('Profile restored — ' + d.name);
}

// TIMESTAMP HELPER
function nowStamp() {
  const n = new Date();
  return n.toISOString().split('T')[0] + ' ' +
    n.toTimeString().substring(0,8);
}

function toggle(i) {
  profiles[i].status = profiles[i].status === 'Preapproved' ? 'Approved' : 'Preapproved';
  if (profiles[i].status === 'Approved') profiles[i].approved = new Date().toISOString().split('T')[0];
  render(); postRender();
  saveState();
  const msg = profiles[i].status === 'Approved' ? 'Profile approved' : 'Status reverted';
  pushAdminLog(msg, profiles[i].name + ' · ' + profiles[i].cpId, 'profile');
  pushNotif('✅ Status changed', profiles[i].name + ': ' + msg);
  toast(msg);
}

// FOLLOW-UP
function openAddFollowUp() {
  idx = -1;
  _fuMobileOnly = null;
  const todayStr = new Date().toISOString().split('T')[0];
  const dateInput = document.getElementById('fu_date');
  dateInput.min = todayStr;
  dateInput.value = '';
  document.getElementById('fu_cpid').value = '';
  document.getElementById('fu_cpid').readOnly = false;
  document.getElementById('fu_cpid').style.background = '#fff';
  document.getElementById('fu_cpid').placeholder = 'Optional — auto-fills from mobile';
  document.getElementById('fu_member').value = '';
  document.getElementById('fu_member').readOnly = false;
  document.getElementById('fu_member').style.background = '#fff';
  document.getElementById('fu_member').placeholder = 'Optional name';
  document.getElementById('fu_type').value = '';
  document.getElementById('fu_reason').value = '';
  document.getElementById('fu_admin').value = loginAdminObj?.name || '—';
  // Add mobile field if not exists
  let mobField = document.getElementById('fu_mobile');
  if (!mobField) {
    const row = document.createElement('div');
    row.className = 'form-row';
    row.id = 'fu_mobile_row';
    row.innerHTML = '<label class="input-label">Mobile</label><input class="input" id="fu_mobile" placeholder="10-digit mobile" maxlength="10" type="tel">';
    document.getElementById('fu_cpid').parentNode.after(row);
    mobField = document.getElementById('fu_mobile');
  }
  document.getElementById('fu_mobile_row').style.display = '';
  mobField.value = '';
  // Auto-fill from profile when CP ID changes
  document.getElementById('fu_cpid').oninput = function() {
    const p = profiles.find(p => p.cpId === this.value);
    if (p) {
      document.getElementById('fu_member').value = p.name;
      mobField.value = p.mobile || '';
    }
  };
  // Auto-fill from profile when mobile changes
  mobField.oninput = function() {
    if (this.value.length === 10) {
      const p = profiles.find(p => p.mobile === this.value);
      if (p) {
        document.getElementById('fu_cpid').value = p.cpId;
        document.getElementById('fu_member').value = p.name;
      }
    }
  };
  openModal('followOverlay');
}

function openFollowUp(i) {
  idx = i;
  _fuMobileOnly = null;
  // Reset to readonly mode
  document.getElementById('fu_cpid').readOnly = true;
  document.getElementById('fu_cpid').style.background = '#f3f4f6';
  document.getElementById('fu_cpid').oninput = null;
  document.getElementById('fu_member').readOnly = true;
  document.getElementById('fu_member').style.background = '#f3f4f6';
  const mobRow = document.getElementById('fu_mobile_row');
  if (mobRow) mobRow.style.display = 'none';
  // Set today as minimum date (no past dates)
  const todayStr = new Date().toISOString().split('T')[0];
  const dateInput = document.getElementById('fu_date');
  dateInput.min = todayStr;
  dateInput.value = '';
  document.getElementById('fu_cpid').value    = profiles[i].cpId;
  document.getElementById('fu_member').value  = profiles[i].name;
  document.getElementById('fu_type').value    = '';
  document.getElementById('fu_reason').value  = '';
  document.getElementById('fu_admin').value   = loginAdminObj?.name || '—';
  openModal('followOverlay');
}
// Open follow-up for unregistered OTP mobile (no profile)
let _fuMobileOnly = null;
function openFollowUpByMobile(mobile, name) {
  idx = -1;
  _fuMobileOnly = { mobile, name: name || mobile };
  const todayStr = new Date().toISOString().split('T')[0];
  const dateInput = document.getElementById('fu_date');
  dateInput.min = todayStr;
  dateInput.value = '';
  document.getElementById('fu_cpid').value    = '—';
  document.getElementById('fu_member').value  = name || mobile;
  document.getElementById('fu_type').value    = '';
  document.getElementById('fu_reason').value  = '';
  document.getElementById('fu_admin').value   = loginAdminObj?.name || '—';
  openModal('followOverlay');
}
function saveFollow() {
  const type   = document.getElementById('fu_type').value;
  const date   = document.getElementById('fu_date').value;
  const reason = document.getElementById('fu_reason').value.trim();
  if (!type)   { toast('Please select a follow-up type', 'error'); return; }
  if (!date)   { toast('Please select a date', 'error'); return; }
  const today = new Date().toISOString().split('T')[0];
  if (date < today) { toast('Cannot select a past date', 'error'); return; }
  if (_fuMobileOnly) {
    // Follow-up for unregistered OTP mobile (no profile)
    followUps.push({
      cpId:       '—',
      memberName: _fuMobileOnly.name,
      mobile:     _fuMobileOnly.mobile,
      type,
      admin:      document.getElementById('fu_admin').value,
      date,
      reason
    });
    closeModal('followOverlay');
    render(); postRender();
    pushAdminLog('Scheduled Follow-up', _fuMobileOnly.mobile + ' · ' + type + ' on ' + date, 'followup');
    saveState();
    pushNotif('📞 Follow-up scheduled', 'A follow-up was added for ' + _fuMobileOnly.name + '.');
    toast('Follow-up scheduled');
    _fuMobileOnly = null;
  } else if (idx === -1) {
    // Manual add from Follow-ups page
    const cpId = document.getElementById('fu_cpid').value.trim();
    const member = document.getElementById('fu_member').value.trim();
    const mobile = document.getElementById('fu_mobile')?.value.trim() || '';
    if (!member && !cpId && !mobile) { toast('Enter CP ID, name or mobile', 'error'); return; }
    followUps.push({
      cpId: cpId || '—',
      memberName: member || mobile || cpId,
      mobile: mobile,
      type,
      admin: document.getElementById('fu_admin').value,
      date,
      reason
    });
    closeModal('followOverlay');
    render(); postRender();
    renderFollowTables();
    pushAdminLog('Scheduled Follow-up', (cpId||mobile) + ' · ' + type + ' on ' + date, 'followup');
    saveState();
    pushNotif('📞 Follow-up scheduled', 'A follow-up was added for ' + (member||mobile||cpId) + '.');
    toast('Follow-up scheduled');
  } else {
    followUps.push({
      cpId:       profiles[idx].cpId,
      memberName: profiles[idx].name,
      mobile:     profiles[idx].mobile,
      type,
      admin:      document.getElementById('fu_admin').value,
      date,
      reason
    });
    closeModal('followOverlay');
    render(); postRender();
    pushAdminLog('Scheduled Follow-up', profiles[idx].cpId + ' · ' + type + ' on ' + date, 'followup');
    saveState();
    pushNotif('📞 Follow-up scheduled', 'A follow-up was added for ' + profiles[idx].name + '.');
    toast('Follow-up scheduled');
  }
}

// FOLLOW TABLE RENDER HELPERS
const TYPE_MAP = {
  data:           {label:'Data',          bg:'#eff6ff', color:'#2563eb', icon:'📋'},
  payment:        {label:'Payment',       bg:'#fffbeb', color:'#d97706', icon:'💳'},
  not_interested: {label:'Not Interested',bg:'#fee2e2', color:'#dc2626', icon:'🚫'},
  paid:           {label:'Paid',          bg:'#f0fdf4', color:'#16a34a', icon:'✅'},
};

function typeBadge(type) {
  const t = TYPE_MAP[type] || {label:type||'—', bg:'#f3f4f6', color:'#6b7280', icon:'📌'};
  return `<span class="badge" style="background:${t.bg};color:${t.color}">${t.icon} ${t.label}</span>`;
}

function followRow(f, fi, showEdit=true, showUndo=false) {
  const editBtn  = showEdit  ? `<button class="btn btn-outline btn-sm" onclick="openEditFollow(${fi})">✏️ Edit</button>` : '';
  const undoBtn  = showUndo  ? `<button class="btn btn-green btn-sm" onclick="undoClosedFollow(${fi})">↩ Undo</button>` : '';
  return `<tr>
    <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${f.cpId}</code></td>
    <td><div class="name-cell"><div class="avatar" style="font-size:10px;width:26px;height:26px">${initials(f.memberName||'?')}</div>${f.memberName||f.mobile}</div></td>
    <td>${typeBadge(f.type)}</td>
    <td><span style="font-weight:500;font-size:13px">${f.admin||'—'}</span></td>
    <td style="font-size:13px">${f.date}</td>
    <td><span style="color:var(--text-secondary);font-size:12.5px">${f.reason||'—'}</span></td>
    <td><div class="actions">${editBtn}${undoBtn}</div></td>
  </tr>`;
}

function emptyRow(cols, icon, msg) {
  return `<tr><td colspan="${cols}"><div class="empty-state"><div class="icon">${icon}</div><p>${msg}</p></div></td></tr>`;
}

function renderFollowTables() {
  const today    = new Date().toISOString().split('T')[0];
  const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];

  const closedTypes = ['paid','not_interested'];

  const active = followUps.filter(f => !closedTypes.includes(f.type));
  const closed = followUps.filter(f =>  closedTypes.includes(f.type));

  // Apply search + date filter
  const fActive = applyFollowFilter(active);
  const fClosed = applyFollowFilter(closed);

  const todayList  = fActive.filter(f => f.date === today);
  const pastList   = fActive.filter(f => f.date <  today);
  const futureList = fActive.filter(f => f.date >= tomorrow);

  function renderTable(tbodyId, list, showEdit, showUndo, emptyIcon, emptyMsg) {
    document.getElementById(tbodyId).innerHTML = list.length === 0
      ? emptyRow(7, emptyIcon, emptyMsg)
      : list.map(f => {
          const fi = followUps.indexOf(f);
          return followRow(f, fi, showEdit, showUndo);
        }).join('');
  }

  // Sort past newest-first
  const sortedPast = [...pastList].sort((a,b) => b.date.localeCompare(a.date));
  // Sort closed newest-first
  const sortedClosed = [...fClosed].sort((a,b) => b.date.localeCompare(a.date));

  renderTable('todayFollowTable',  todayList,   true,  false, '📅', 'No follow-ups for today');
  renderTable('pastFollowTable',   sortedPast,  true,  false, '⏰', 'No past follow-ups');
  renderTable('futureFollowTable', futureList,  true,  false, '🔮', 'No upcoming follow-ups');
  renderTable('closedFollowTable', sortedClosed,false, true,  '🗂', 'No closed follow-ups yet');

  document.getElementById('todayCount').textContent  = todayList.length;
  document.getElementById('pastCount').textContent   = pastList.length;
  document.getElementById('futureCount').textContent = futureList.length;
  document.getElementById('closedCount').textContent = fClosed.length;
}

// EDIT FOLLOW-UP
function openEditFollow(fi) {
  editFollowIdx = fi;
  const f = followUps[fi];
  const todayStr = new Date().toISOString().split('T')[0];
  document.getElementById('ef_cpid').value   = f.cpId;
  document.getElementById('ef_member').value = f.memberName || f.mobile;
  document.getElementById('ef_type').value   = f.type;
  document.getElementById('ef_date').value   = f.date >= todayStr ? f.date : todayStr;
  document.getElementById('ef_date').min     = todayStr;
  document.getElementById('ef_reason').value = f.reason || '';
  openModal('editFollowOverlay');
}
function saveEditFollow() {
  const date = document.getElementById('ef_date').value;
  const today = new Date().toISOString().split('T')[0];
  if (!date)        { toast('Please select a date', 'error'); return; }
  if (date < today) { toast('Cannot set a past date', 'error'); return; }
  followUps[editFollowIdx].type   = document.getElementById('ef_type').value;
  followUps[editFollowIdx].date   = date;
  followUps[editFollowIdx].reason = document.getElementById('ef_reason').value.trim();
  closeModal('editFollowOverlay');
  renderFollowTables();
  render(); postRender();
  saveState();
  toast('Follow-up updated');
}

// UNDO CLOSED FOLLOW-UP — modal version
undoFollowIdx = null;
function undoClosedFollow(fi) {
  undoFollowIdx = fi;
  const f = followUps[fi];
  const todayStr = new Date().toISOString().split('T')[0];
  document.getElementById('uf_member').value = (f.memberName || f.mobile) + ' · ' + f.cpId;
  document.getElementById('uf_date').value   = todayStr;
  document.getElementById('uf_date').min     = todayStr;
  openModal('undoFollowOverlay');
}
function confirmUndoFollow() {
  const todayStr = new Date().toISOString().split('T')[0];
  const newDate  = document.getElementById('uf_date').value;
  if (!newDate)           { toast('Please select a date', 'error'); return; }
  if (newDate < todayStr) { toast('Date must be today or future', 'error'); return; }
  followUps[undoFollowIdx].date = newDate;
  followUps[undoFollowIdx].type = 'data';
  closeModal('undoFollowOverlay');
  renderFollowTables();
  render(); postRender();
  saveState();
  toast('Follow-up moved to ' + (newDate === todayStr ? "Today's" : 'Upcoming') + ' table');
}

// BILL
// Helper: populate plan dropdown from customPlans
function populateBillPlanDropdown(selectedName) {
  const sel = document.getElementById('bill_planname');
  sel.innerHTML = '<option value="">— Select Plan —</option>';
  customPlans.forEach(cp => {
    const opt = document.createElement('option');
    opt.value = cp.name;
    opt.textContent = cp.name + ' (₹' + cp.amount + ' / ' + cp.validity + 'd)';
    opt.dataset.type   = cp.type;
    opt.dataset.amount = cp.amount;
    if (cp.name === selectedName) opt.selected = true;
    sel.appendChild(opt);
  });
  // Also add generic options as fallback
  ['Free','Basic','Paid','Premium','VIP'].forEach(label => {
    if (!customPlans.find(c => c.name === label)) {
      const opt = document.createElement('option');
      opt.value = label; opt.textContent = label;
      opt.dataset.type = label.toLowerCase();
      opt.dataset.amount = '';
      if (label === selectedName) opt.selected = true;
      sel.appendChild(opt);
    }
  });
  onBillPlanChange();
}

function onBillPlanChange() {
  const sel = document.getElementById('bill_planname');
  const opt = sel.options[sel.selectedIndex];
  if (!opt || !opt.value) {
    document.getElementById('bill_plantype').value = '';
    return;
  }
  const cp = customPlans.find(c => c.name === opt.value);

  // Auto-fill Plan Type
  document.getElementById('bill_plantype').value = cp
    ? (cp.type.charAt(0).toUpperCase() + cp.type.slice(1))
    : (opt.dataset.type ? opt.dataset.type.charAt(0).toUpperCase() + opt.dataset.type.slice(1) : '');

  // Always auto-fill Amount from plan
  const amtEl = document.getElementById('bill_amount');
  if (cp && cp.amount != null) amtEl.value = cp.amount;

  // Always recalculate Expiry from plan validity (from today)
  const expEl = document.getElementById('bill_expiry');
  if (cp && cp.validity) {
    const d = new Date();
    d.setDate(d.getDate() + parseInt(cp.validity));
    expEl.value = d.toISOString().split('T')[0];
  } else {
    expEl.value = '';
  }
}

billEditIdx = null; // track if editing an existing bill

function openBill(i) {
  idx = i;
  billEditIdx = null;
  const p = profiles[i];
  document.getElementById('billModalTitle').textContent = '💳 Create Bill';
  document.getElementById('billSaveBtn').childNodes[1].textContent = ' Save Bill';
  document.getElementById('bill_cpid').value    = p.cpId;
  document.getElementById('bill_mobile').value  = p.mobile;
  document.getElementById('bill_name').value    = p.name;
  document.getElementById('bill_amount').value  = '';
  // Check if this profile has payment_status set or pending_plan — auto-set payment to Online
  const hasOnlinePayment = p.paymentStatus === 'payment_notified' || p.pendingPlan || p.pending_plan;
  document.getElementById('bill_type').value = hasOnlinePayment ? 'Online' : '';
  // Also try to find from userOrders if loaded
  if (typeof userOrders !== 'undefined' && userOrders.length > 0) {
    const uo = userOrders.find(o => o.mobile === p.mobile && o.status === 'approved');
    if (uo) {
      document.getElementById('bill_type').value = 'Online';
      if (uo.amount) document.getElementById('bill_amount').value = uo.amount.replace(/[^\d.]/g, '');
    }
  }
  // Fetch latest order status for this mobile
  fetch('api/admin/settings.php?section=userOrders', { credentials:'same-origin' })
    .then(r => r.json()).then(data => {
      if (data.ok && data.orders) {
        const uo = data.orders.find(o => o.mobile === p.mobile && o.status === 'approved');
        if (uo) {
          document.getElementById('bill_type').value = 'Online';
          if (uo.amount && !document.getElementById('bill_amount').value) {
            document.getElementById('bill_amount').value = uo.amount.replace(/[^\d.]/g, '');
          }
        }
      }
    }).catch(() => {});
  document.getElementById('bill_expiry').value  = ''; // cleared — plan will auto-fill
  document.getElementById('bill_date').value    = new Date().toISOString().split('T')[0];
  // Auto-fill Billed By from logged-in admin
  const billedBy = (loginAdminObj && loginAdminObj.name)
    ? loginAdminObj.name
    : (admins.find(a => a.status === 'active')?.name || '—');
  document.getElementById('bill_billedby').value = billedBy;
  populateBillPlanDropdown(p.plan); // triggers onBillPlanChange → sets expiry
  openModal('billOverlay');
}

// ══════════════════════════════════════════════════════
// ADMIN PAYMENT PAGE (like user panel)
// ══════════════════════════════════════════════════════
let _adminPayIdx = null, _adminPayPlan = null;

function openAdminPayment(i) {
  _adminPayIdx = i;
  _adminPayPlan = null;
  const p = profiles[i];
  document.getElementById('adminPayTitle').textContent = '💳 Payment — ' + p.name;
  document.getElementById('adminPayProfile').innerHTML = `
    <div class="avatar" style="width:40px;height:40px;font-size:14px;flex-shrink:0">${initials(p.name)}</div>
    <div style="flex:1"><div style="font-weight:700;font-size:14px">${esc(p.name)}</div>
    <div style="font-size:12px;color:#64748b">${p.cpId} · ${p.mobile} · ${p.gender||''} · ${p.age?p.age+' yrs':''}</div></div>`;
  // Show step 1
  document.getElementById('adminPayStep1').style.display = '';
  document.getElementById('adminPayStep2').style.display = 'none';
  document.getElementById('adminPayStep3').style.display = 'none';
  document.getElementById('adminPayNowWrap').style.display = 'none';
  // Render plans
  const plans = customPlans.filter(cp => cp.amount > 0);
  const icons = { basic:'📘', paid:'💳', premium:'⭐', vip:'👑', silver:'💳', gold:'💳' };
  document.getElementById('adminPayPlans').innerHTML = plans.length === 0
    ? '<div style="text-align:center;padding:20px;color:#94a3b8">No paid plans configured. Add plans in Settings.</div>'
    : plans.map((cp, pi) => `
      <div class="plan-card" id="apPlanCard_${pi}" onclick="adminPaySelectPlan(${pi})"
        style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:2px solid #e2e8f0;border-radius:12px;cursor:pointer;transition:all .15s">
        <div style="width:40px;height:40px;border-radius:10px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">${icons[cp.type]||'💳'}</div>
        <div style="flex:1"><div style="font-weight:700;font-size:14px">${esc(cp.name)}</div>
        <div style="font-size:12px;color:#64748b">${esc(cp.description||cp.type+' plan')}</div></div>
        <div style="text-align:right;flex-shrink:0"><div style="font-family:var(--mono);font-size:18px;font-weight:800">₹${Number(cp.amount).toLocaleString('en-IN')}</div>
        <div style="font-size:11px;color:#94a3b8">${cp.validity} days</div></div>
      </div>`).join('');
  openModal('adminPayOverlay');
}

function adminPaySelectPlan(pi) {
  const plans = customPlans.filter(cp => cp.amount > 0);
  _adminPayPlan = plans[pi];
  document.querySelectorAll('#adminPayPlans .plan-card').forEach((c,i) => {
    c.style.borderColor = i === pi ? '#8B0000' : '#e2e8f0';
    c.style.background = i === pi ? '#fef2f2' : '#fff';
  });
  const btn = document.getElementById('adminPayNowBtn');
  btn.textContent = 'Continue — ₹' + Number(_adminPayPlan.amount).toLocaleString('en-IN');
  document.getElementById('adminPayNowWrap').style.display = '';
}

function adminPayConfirm() {
  if (!_adminPayPlan) return;
  document.getElementById('adminPayStep1').style.display = 'none';
  document.getElementById('adminPayStep2').style.display = '';
  const amt = Number(_adminPayPlan.amount).toLocaleString('en-IN');
  document.getElementById('adminPaySelectedPlan').innerHTML = `
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
      <div><div style="font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.5);margin-bottom:3px">Selected Plan</div>
      <div style="font-size:17px;font-weight:700">${esc(_adminPayPlan.name)}</div></div>
      <div style="font-size:26px;font-weight:800;font-family:var(--mono)">₹${amt}</div></div>`;
  // Payment methods
  const activeOpts = paymentOptions.filter(o => o.status === 'active');
  const mIcon = { qr:'💳', upi:'📲', bank:'🏦', mobile:'📱' };
  const mLabel = { qr:'QR Code', upi:'UPI ID', bank:'Bank Transfer', mobile:'UPI Mobile' };
  if (activeOpts.length === 0) {
    document.getElementById('adminPayOpts').innerHTML = `
      <div style="text-align:center;padding:16px;color:#94a3b8">No payment options configured.</div>
      <button class="btn btn-primary" onclick="adminPayCreateBill('Cash')" style="width:100%;padding:12px;font-size:14px;font-weight:700">Create Bill (Cash Payment)</button>`;
    return;
  }
  document.getElementById('adminPayOpts').innerHTML = activeOpts.map((opt, i) => {
    const d = opt.details || {};
    let info = '';
    if (opt.method === 'upi') info = `<div style="font-family:var(--mono);font-size:15px;font-weight:700;background:#f8fafc;display:inline-block;padding:6px 14px;border-radius:8px">${esc(d.upi_id||d.upiId||'')}</div>`;
    else if (opt.method === 'bank') {
      const rows = [['Account',d.account_name||d.accountName],['Acc No',d.account_no||d.accountNo],['IFSC',d.ifsc],['Bank',d.bank_name||d.bankName]].filter(([,v])=>v);
      info = rows.map(([l,v])=>`<div style="font-size:12px"><span style="color:#64748b">${l}:</span> <strong>${esc(v)}</strong></div>`).join('');
    } else if (opt.method === 'qr') {
      const qrUrl = d.qr_url || d.qrUrl || '';
      info = qrUrl ? `<img src="${esc(qrUrl)}" alt="QR" style="width:140px;height:140px;object-fit:contain;border:1px solid #e2e8f0;border-radius:8px;padding:6px;background:#fff">` : '';
    } else if (opt.method === 'mobile') {
      info = `<div style="font-family:var(--mono);font-size:16px;font-weight:700">${esc(d.mobileNo||d.mobile_no||'')}</div><div style="font-size:12px;color:#64748b">${esc(d.holderName||d.holder_name||'')}</div>`;
    }
    return `<div style="border:1.5px solid #e2e8f0;border-radius:12px;overflow:hidden">
      <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f8fafc;cursor:pointer" onclick="document.getElementById('apOptBody_${i}').classList.toggle('open')">
        <span style="font-size:18px">${mIcon[opt.method]||'💳'}</span>
        <div style="flex:1"><div style="font-weight:700;font-size:13px">${esc(opt.label)}</div><div style="font-size:11px;color:#64748b">${mLabel[opt.method]||opt.method}</div></div>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </div>
      <div id="apOptBody_${i}" style="max-height:0;overflow:hidden;transition:max-height .3s ease;padding:0 16px">
        <div style="padding:12px 0;text-align:center">${info}
        <div style="font-size:12px;color:#64748b;margin-top:8px">Collect <strong>₹${amt}</strong> via ${esc(opt.label)}</div>
        <button class="btn btn-primary" onclick="adminPayCreateBill('${esc(opt.label)}')" style="width:100%;margin-top:12px;padding:10px;font-size:13px;font-weight:700">✅ Payment Received — Create Bill</button>
        </div>
      </div>
    </div>`;
  }).join('');
  // Add CSS for open state
  if (!document.getElementById('apOptStyle')) {
    const s = document.createElement('style');
    s.id = 'apOptStyle';
    s.textContent = '#adminPayOpts .open{max-height:400px!important;padding:0 16px!important}';
    document.head.appendChild(s);
  }
}

function adminPayBackToPlans() {
  document.getElementById('adminPayStep1').style.display = '';
  document.getElementById('adminPayStep2').style.display = 'none';
  _adminPayPlan = null;
  document.getElementById('adminPayNowWrap').style.display = 'none';
  document.querySelectorAll('#adminPayPlans .plan-card').forEach(c => { c.style.borderColor = '#e2e8f0'; c.style.background = '#fff'; });
}

async function adminPayCreateBill(paymentMethod) {
  if (!_adminPayPlan || _adminPayIdx === null) return;
  const p = profiles[_adminPayIdx];
  const plan = _adminPayPlan;
  const today = new Date().toISOString().split('T')[0];
  const expiry = new Date(Date.now() + (plan.validity||365)*86400000).toISOString().split('T')[0];
  const billedBy = loginAdminObj?.name || 'Admin';
  // Create bill via existing saveBill logic
  const billEntry = {
    cpId: p.cpId, mobile: p.mobile, name: p.name,
    planName: plan.name, planType: plan.type || plan.name.toLowerCase(),
    plan: (plan.type || plan.name).toLowerCase(),
    amount: parseFloat(plan.amount),
    payment: paymentMethod, billedBy, billedDate: today, expiry
  };
  // Update profile
  p.plan = billEntry.plan;
  p.expiry = expiry;
  // Archive & push bill
  const ex = bills.findIndex(b => b.cpId === p.cpId);
  if (ex > -1) { billHistory.unshift({ ...bills[ex], _action:'Updated', _recordedAt: nowStamp() }); bills.splice(ex,1); }
  billHistory.unshift({ ...billEntry, _action:'Created', _recordedAt: nowStamp() });
  bills.push(billEntry);
  // Save to server
  try {
    await apiPost('api/admin/bills.php', {
      action: 'create', cp_id: p.cpId, mobile: p.mobile, name: p.name,
      plan_name: plan.name, plan_type: plan.type||'paid', amount: plan.amount,
      payment: paymentMethod, billed_by: billedBy, billed_date: today, expiry
    });
  } catch(e) { console.warn('Bill API error:', e); }
  // Auto-add income to accounts
  if (parseFloat(plan.amount) > 0) {
    apiPost('api/admin/settings.php', {
      section:'accounts', action:'add', date: today, type:'income', category:'Registration Fee',
      amount: plan.amount, description: p.name+' - '+plan.name+' ('+p.cpId+')',
      payment_mode: paymentMethod, reference: p.cpId, related: p.cpId,
      admin_name: billedBy
    }).catch(() => {});
  }
  pushAdminLog('Created Bill', p.name+' · '+plan.name+' · ₹'+Number(plan.amount).toLocaleString('en-IN'), 'bill');
  saveState();
  // Show done step
  document.getElementById('adminPayStep1').style.display = 'none';
  document.getElementById('adminPayStep2').style.display = 'none';
  document.getElementById('adminPayStep3').style.display = '';
  document.getElementById('adminPayDoneMsg').innerHTML = `
    <strong>${esc(p.name)}</strong> (${p.cpId})<br>
    Plan: <strong>${esc(plan.name)}</strong> · ₹${Number(plan.amount).toLocaleString('en-IN')}<br>
    Payment: ${esc(paymentMethod)} · Expiry: ${expiry}`;
  toast('Bill created — ' + p.name + ' · ₹' + Number(plan.amount).toLocaleString('en-IN'));
}

function openBillEdit(i) {
  const b = bills[i];
  billEditIdx = i;
  const pi = profiles.findIndex(p => p.cpId === b.cpId);
  if (pi > -1) idx = pi;
  document.getElementById('billModalTitle').textContent = '✏️ Edit Bill';
  document.getElementById('bill_cpid').value     = b.cpId;
  document.getElementById('bill_mobile').value   = b.mobile;
  document.getElementById('bill_name').value     = b.name;
  document.getElementById('bill_amount').value   = b.amount || '';
  document.getElementById('bill_type').value     = b.payment || '';
  document.getElementById('bill_expiry').value   = b.expiry || '';
  document.getElementById('bill_date').value     = b.billedDate || new Date().toISOString().split('T')[0];
  const editBilledBy = (loginAdminObj && loginAdminObj.name)
    ? loginAdminObj.name
    : (b.billedBy || '—');
  document.getElementById('bill_billedby').value = editBilledBy;
  populateBillPlanDropdown(b.planName || b.plan);
  openModal('billOverlay');
}

function saveBill() {
  const planName  = document.getElementById('bill_planname').value;
  const planType  = document.getElementById('bill_plantype').value;
  const amount    = document.getElementById('bill_amount').value;
  const payment   = document.getElementById('bill_type').value;
  const expiry    = document.getElementById('bill_expiry').value;
  const billedDate= document.getElementById('bill_date').value;
  const billedBy  = document.getElementById('bill_billedby').value;
  const cpId      = document.getElementById('bill_cpid').value;
  const mobile    = document.getElementById('bill_mobile').value;
  const name      = document.getElementById('bill_name').value;

  if (!planName)  { toast('Please select a plan', 'error');        return; }
  if (amount==='') { toast('Please enter the billed amount', 'error'); return; }
  if (!payment)   { toast('Please select a payment type', 'error'); return; }

  // Update the profile plan & expiry
  if (idx !== null && profiles[idx]) {
    profiles[idx].plan   = planType.toLowerCase() || planName.toLowerCase();
    profiles[idx].expiry = expiry;
  }

  const billEntry = {
    cpId, mobile, name,
    planName, planType,
    plan: planType.toLowerCase() || planName.toLowerCase(),
    amount: parseFloat(amount),
    payment, billedBy, billedDate, expiry
  };

  if (billEditIdx !== null) {
    // Archive old version before overwriting
    const oldBill = { ...bills[billEditIdx], _action: 'Updated', _recordedAt: nowStamp() };
    billHistory.unshift(oldBill);
    bills[billEditIdx] = billEntry;
    toast('✅ Bill updated successfully');
  } else {
    // Archive any previous bill for same CP before replacing
    const ex = bills.findIndex(b => b.cpId === cpId);
    if (ex > -1) {
      const oldBill = { ...bills[ex], _action: 'Updated', _recordedAt: nowStamp() };
      billHistory.unshift(oldBill);
      bills.splice(ex, 1);
    }
    // Archive new creation event
    billHistory.unshift({ ...billEntry, _action: 'Created', _recordedAt: nowStamp() });
    bills.push(billEntry);
    toast('✅ Bill created successfully');
  }

  closeModal('billOverlay');
  render(); postRender();
  renderBills(); renderBillHistory();
  pushAdminLog(billEditIdx !== null ? 'Updated Bill' : 'Created Bill', name + ' · ' + planName + ' · ₹' + Number(amount).toLocaleString('en-IN'), 'bill');
  saveState();

  // Auto-add income entry to accounts for paid bills
  if (parseFloat(amount) > 0) {
    apiPost('api/admin/settings.php', {
      section:'accounts', action:'add',
      date: billedDate || new Date().toISOString().split('T')[0],
      type: 'income', category: 'Registration Fee',
      amount: amount,
      description: name + ' - ' + planName + ' (' + cpId + ')',
      payment_mode: payment || 'Cash',
      reference: cpId,
      related: cpId,
      admin_name: billedBy || loginAdminObj?.name || 'Admin'
    }).then(() => { if (document.getElementById('accountsSection')?.classList.contains('active')) loadAccounts(); })
      .catch(() => {});
  }
  pushNotif('💳 Bill ' + (billEditIdx !== null ? 'updated' : 'created'), name + ' — ₹' + Number(amount).toLocaleString('en-IN') + ' · ' + planName);
}

// SETTINGS TABS
function switchStab(id, btn) {
  document.querySelectorAll('.stab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.stab').forEach(b => b.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  btn.classList.add('active');
  if (id === 'subPlansPanel')  { resetPlanForm(); renderCustomPlans(); renderPlanHistory(); }
  if (id === 'adminPanel')     { renderAdmins(); }
  if (id === 'restrictPanel')  { renderRestrictions(); }
  if (id === 'adminLogPanel')  { renderAdminLog(); }
  if (id === 'roleDetailsPanel') { renderPermGrid(); }
  if (id === 'mobileReqPanel') { renderMobileReqs(); }
  if (id === 'paymentPanel')   { renderPaymentOptions(); }
  if (id === 'userCtrlPanel')  { renderUserCtrlPanel(); renderUPCtrlHistory(); }
  if (id === 'pointsPanel')    { loadPtsStats(); loadPtsUsers(); loadPtsPackages(); }
}

// ─── POINTS MANAGEMENT ─────────────────────────────────────────────────────
async function loadPtsStats() {
  try {
    const d = await fetch('api/admin/points.php?action=stats', { credentials:'same-origin' }).then(r => r.json());
    if (!d.ok) return;
    document.getElementById('aPtsUsers').textContent   = d.total_users   ?? 0;
    document.getElementById('aPtsBought').textContent  = d.total_bought  ?? 0;
    document.getElementById('aPtsUsed').textContent    = d.total_used    ?? 0;
    document.getElementById('aPtsBalance').textContent = d.total_balance ?? 0;
    const tb = document.getElementById('aPtsTxnTbody');
    if (!tb) return;
    if (!d.recent?.length) { tb.innerHTML='<tr><td colspan="7" style="text-align:center;color:#aaa;padding:16px">No transactions yet.</td></tr>'; return; }
    tb.innerHTML = d.recent.map(t => {
      const sign = t.points > 0 ? '+' : '';
      const col  = t.points > 0 ? '#166534' : '#991b1b';
      const bg   = t.points > 0 ? '#f0fdf4' : '#fef2f2';
      const tl   = {purchase:'Purchase',deduct:'Contact View',admin_credit:'Admin Credit',admin_debit:'Admin Debit'}[t.type]||t.type;
      return `<tr style="border-bottom:1px solid #f5f4f2">
        <td style="padding:8px 14px;font-family:monospace;font-size:12px">${t.mobile}</td>
        <td style="padding:8px 10px;color:#555">${t.name||'—'}</td>
        <td style="padding:8px 10px"><span style="background:#f5f3ef;border-radius:5px;padding:2px 8px;font-size:11.5px;font-weight:600;color:#555">${tl}</span></td>
        <td style="padding:8px 10px;text-align:right"><span style="background:${bg};color:${col};font-weight:700;border-radius:5px;padding:2px 8px;font-size:12px">${sign}${t.points}</span></td>
        <td style="padding:8px 10px;text-align:right;color:#888">${t.balance_after}</td>
        <td style="padding:8px 10px;color:#666;font-size:12px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${t.description||'—'}</td>
        <td style="padding:8px 14px;color:#aaa;font-size:11.5px;white-space:nowrap">${(t.created_at||'').slice(0,16)}</td>
      </tr>`;
    }).join('');
  } catch(e) {}
}

async function loadPtsUsers() {
  const q = document.getElementById('aPtsSearch')?.value || '';
  try {
    const d = await fetch('api/admin/points.php?action=users&q=' + encodeURIComponent(q), { credentials:'same-origin' }).then(r => r.json());
    const tb = document.getElementById('aPtsUserTbody');
    if (!tb) return;
    if (!d.ok || !d.users?.length) { tb.innerHTML='<tr><td colspan="6" style="text-align:center;color:#aaa;padding:16px">No users found.</td></tr>'; return; }
    tb.innerHTML = d.users.map(u =>
      `<tr style="border-bottom:1px solid #f5f4f2">
        <td style="padding:8px 14px;font-family:monospace;font-size:12px">${u.mobile}</td>
        <td style="padding:8px 10px;color:#555">${u.name||'—'}</td>
        <td style="padding:8px 10px;text-align:right"><span style="font-weight:800;font-size:14px;color:#8B0000">${u.balance}</span></td>
        <td style="padding:8px 10px;text-align:right;color:#166534;font-weight:600">${u.total_bought}</td>
        <td style="padding:8px 10px;text-align:right;color:#1e40af;font-weight:600">${u.total_used}</td>
        <td style="padding:8px 14px;color:#aaa;font-size:11.5px;white-space:nowrap">${(u.updated_at||'').slice(0,16)}</td>
      </tr>`
    ).join('');
  } catch(e) {}
}

async function adminPtsCredit() {
  const mobile = document.getElementById('aPtsMobile').value.trim();
  const pts    = parseInt(document.getElementById('aPtsAmount').value) || 0;
  const note   = document.getElementById('aPtsNote').value.trim() || 'Admin credit';
  const msg    = document.getElementById('aPtsMsgBox');
  if (!mobile || pts <= 0) { showMsg(msg, '⚠️ Mobile and points required.', 'warn'); return; }
  try {
    const d = await fetch('api/admin/points.php?action=credit', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ mobile, points: pts, note })
    }).then(r => r.json());
    showMsg(msg, d.ok ? '✅ ' + d.msg : '❌ ' + (d.error||'Error'), d.ok ? 'ok' : 'err');
    if (d.ok) { loadPtsStats(); loadPtsUsers(); }
  } catch(e) { showMsg(msg, '❌ Network error', 'err'); }
}

async function adminPtsDebit() {
  const mobile = document.getElementById('aPtsMobile').value.trim();
  const pts    = parseInt(document.getElementById('aPtsAmount').value) || 0;
  const note   = document.getElementById('aPtsNote').value.trim() || 'Admin debit';
  const msg    = document.getElementById('aPtsMsgBox');
  if (!mobile || pts <= 0) { showMsg(msg, '⚠️ Mobile and points required.', 'warn'); return; }
  try {
    const d = await fetch('api/admin/points.php?action=debit', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ mobile, points: pts, note })
    }).then(r => r.json());
    showMsg(msg, d.ok ? '✅ ' + d.msg : '❌ ' + (d.error||'Error'), d.ok ? 'ok' : 'err');
    if (d.ok) { loadPtsStats(); loadPtsUsers(); }
  } catch(e) { showMsg(msg, '❌ Network error', 'err'); }
}

function showMsg(el, text, type) {
  if (!el) return;
  const colors = { ok:'#166534', warn:'#92400e', err:'#991b1b' };
  const bgs    = { ok:'#f0fdf4', warn:'#fffbeb', err:'#fef2f2' };
  el.style.display = '';
  el.style.color   = colors[type] || '#333';
  el.style.background = bgs[type] || '#fff';
  el.style.padding = '8px 12px';
  el.style.borderRadius = '6px';
  el.textContent   = text;
}

// ─── POINT PACKAGES ──────────────────────────────────────────────────────────
let _pkgList = [];

function _esc(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

async function loadPtsPackages() {
  try {
    const d = await fetch('api/admin/points.php?action=packages', { credentials:'same-origin' }).then(r => r.json());
    _pkgList = d.packages || [];
    const tb = document.getElementById('pkgTbody');
    if (!tb) return;
    if (!_pkgList.length) {
      tb.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#aaa;padding:18px">No packages yet. Click "+ Add Package" to create one.</td></tr>';
      return;
    }
    tb.innerHTML = _pkgList.map(p => `
      <tr style="border-bottom:1px solid #f5f4f2">
        <td style="padding:8px 14px;font-family:monospace;font-size:12px;color:#8B0000;font-weight:700">${_esc(p.pkg_id)}</td>
        <td style="padding:8px 10px;color:#333;font-weight:500">${_esc(p.label)}</td>
        <td style="padding:8px 10px;text-align:right;font-weight:700;color:#1a1a2e">${p.points}</td>
        <td style="padding:8px 10px;text-align:right;color:#16a34a;font-weight:600">₹${parseFloat(p.price).toFixed(2)}</td>
        <td style="padding:8px 10px;color:#666">${p.badge ? `<span style="background:#fdf4ff;color:#9333ea;border-radius:5px;padding:2px 8px;font-size:11.5px;font-weight:600">${_esc(p.badge)}</span>` : '<span style="color:#ccc">—</span>'}</td>
        <td style="padding:8px 10px;text-align:center">${p.active==1
          ? '<span style="background:#f0fdf4;color:#16a34a;border-radius:5px;padding:2px 8px;font-size:11.5px;font-weight:600">✓ Active</span>'
          : '<span style="background:#fef2f2;color:#dc2626;border-radius:5px;padding:2px 8px;font-size:11.5px;font-weight:600">Off</span>'}</td>
        <td style="padding:8px 14px">
          <div style="display:flex;gap:6px">
            <button onclick="editPtsPackage('${_esc(p.pkg_id)}')" style="background:#eff6ff;color:#2563eb;border:none;border-radius:6px;padding:4px 10px;font-size:12px;cursor:pointer;font-weight:600">Edit</button>
            <button onclick="deletePtsPackage('${_esc(p.pkg_id)}')" style="background:#fef2f2;color:#dc2626;border:none;border-radius:6px;padding:4px 10px;font-size:12px;cursor:pointer;font-weight:600">Delete</button>
          </div>
        </td>
      </tr>`).join('');
  } catch(e) {}
}

function openAddPtsPackage() {
  document.getElementById('pkgFormTitle').textContent = 'Add Package';
  document.getElementById('pkgEditId').value = '';
  document.getElementById('pkgId').value = '';
  document.getElementById('pkgId').readOnly = false;
  document.getElementById('pkgLabel').value = '';
  document.getElementById('pkgBadge').value = '';
  document.getElementById('pkgPoints').value = '';
  document.getElementById('pkgPrice').value = '';
  document.getElementById('pkgFormMsg').style.display = 'none';
  document.getElementById('pkgForm').style.display = '';
  document.getElementById('pkgId').focus();
}

function editPtsPackage(pkgId) {
  const p = _pkgList.find(x => x.pkg_id === pkgId);
  if (!p) return;
  document.getElementById('pkgFormTitle').textContent = 'Edit Package';
  document.getElementById('pkgEditId').value = p.pkg_id;
  document.getElementById('pkgId').value = p.pkg_id;
  document.getElementById('pkgId').readOnly = true;
  document.getElementById('pkgLabel').value = p.label;
  document.getElementById('pkgBadge').value = p.badge || '';
  document.getElementById('pkgPoints').value = p.points;
  document.getElementById('pkgPrice').value = parseFloat(p.price).toFixed(2);
  document.getElementById('pkgFormMsg').style.display = 'none';
  document.getElementById('pkgForm').style.display = '';
}

function closePkgForm() {
  document.getElementById('pkgForm').style.display = 'none';
}

async function savePtsPackage() {
  const pkgId  = document.getElementById('pkgId').value.trim();
  const label  = document.getElementById('pkgLabel').value.trim();
  const badge  = document.getElementById('pkgBadge').value.trim();
  const points = parseInt(document.getElementById('pkgPoints').value) || 0;
  const price  = parseFloat(document.getElementById('pkgPrice').value) || 0;
  const msg    = document.getElementById('pkgFormMsg');
  if (!pkgId || !label || points <= 0 || price <= 0) { showMsg(msg, '⚠️ ID, label, points and price are required.', 'warn'); return; }
  try {
    const d = await fetch('api/admin/points.php?action=save_package', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ pkg_id: pkgId, label, badge, points, price, sort_order: 0, active: 1 })
    }).then(r => r.json());
    if (d.ok) { closePkgForm(); loadPtsPackages(); }
    else showMsg(msg, '❌ ' + (d.error || 'Save failed'), 'err');
  } catch(e) { showMsg(msg, '❌ Network error', 'err'); }
}

async function deletePtsPackage(pkgId) {
  if (!confirm('Delete package "' + pkgId + '"? This cannot be undone.')) return;
  try {
    const d = await fetch('api/admin/points.php?action=delete_package', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ pkg_id: pkgId })
    }).then(r => r.json());
    if (d.ok) loadPtsPackages();
    else alert('Error: ' + (d.error || 'Unknown error'));
  } catch(e) { alert('Network error'); }
}

// ADMIN DATA
admins = [
  { id: 1, name: 'Balasubramanian R', username: 'bala.admin', mobile: '9876543210', role: 'super',   password: 'Admin@123', status: 'active',   created: '2024-01-01' },
  { id: 2, name: 'Ravi Kumar',        username: 'ravi.mgr',   mobile: '9123456780', role: 'manager', password: 'Manager@1', status: 'active',   created: '2024-01-05' },
  { id: 3, name: 'Priya S',           username: 'priya.staff',mobile: '9988776655', role: 'staff',   password: 'Staff@123', status: 'inactive', created: '2024-01-10' },
];
adminIdx = null;
nextAdminId = 4;

function roleBadge(r) {
  if (r === 'super')   return `<span class="role-badge role-super">👑 Super Admin</span>`;
  if (r === 'manager') return `<span class="role-badge role-manager">🛡 Manager</span>`;
  return `<span class="role-badge role-staff">👤 Staff</span>`;
}

function renderAdmins() {
  const tbody = document.getElementById('adminTable');
  if (!tbody) return;
  tbody.innerHTML = admins.length === 0
    ? `<tr><td colspan="9"><div class="empty-state"><div class="icon">👤</div><p>No admin accounts yet</p></div></td></tr>`
    : admins.map((a, i) => {
      const dotColor = a.status === 'active' ? '#16a34a' : '#9ca3af';
      return `<tr>
        <td>${i+1}</td>
        <td><div class="name-cell"><div class="avatar" style="background:${a.role==='super'?'#fdf4ff':a.role==='manager'?'#eff6ff':'#f3f4f6'};color:${a.role==='super'?'#9333ea':a.role==='manager'?'#2563eb':'#6b7280'}">${initials(a.name)}</div>${a.name}</div></td>
        <td><code style="font-size:12px;background:#f3f4f6;padding:2px 8px;border-radius:5px">@${a.username}</code></td>
        <td>${a.mobile}</td>
        <td>${roleBadge(a.role)}</td>
        <td>
          <div class="pwd-cell">
            <span id="pwd_${a.id}" style="font-size:12px;font-family:var(--mono)">${'●'.repeat(8)}</span>
            <button class="eye-btn" onclick="toggleAdminPwd(${a.id}, this)" title="Show/Hide">👁</button>
          </div>
        </td>
        <td>
          <span style="display:inline-flex;align-items:center;font-size:12.5px;font-weight:600;color:${dotColor}">
            <span class="status-dot" style="background:${dotColor}"></span>
            ${a.status === 'active' ? 'Active' : 'Inactive'}
          </span>
        </td>
        <td style="font-size:12px;color:var(--text-secondary)">${a.created}</td>
        <td>
          <div class="actions">
            <button class="btn btn-outline btn-sm" onclick="openEditAdmin(${i})">Edit</button>
            <button class="btn btn-danger btn-sm" onclick="openDeleteAdmin(${i})" ${a.role==='super'?'title="Cannot delete Super Admin" disabled style=opacity:.4':''}>Remove</button>
          </div>
        </td>
      </tr>`;
    }).join('');
}

// Toggle admin password visibility
pwdVisible = {};
function toggleAdminPwd(id, btn) {
  const el = document.getElementById('pwd_' + id);
  const a = admins.find(x => x.id == id);
  const plainPwd = a?.plainPassword || a?.plain_password || '';
  pwdVisible[id] = !pwdVisible[id];
  if (pwdVisible[id] && plainPwd) {
    el.textContent = plainPwd;
  } else {
    el.textContent = '●'.repeat(8);
  }
  btn.textContent = pwdVisible[id] ? '🙈' : '👁';
}

// Toggle in modal
function togglePwd(inputId, btn) {
  const el = document.getElementById(inputId);
  el.type = el.type === 'password' ? 'text' : 'password';
  btn.textContent = el.type === 'password' ? '👁' : '🙈';
}

// Password strength indicator
document.addEventListener('DOMContentLoaded', () => {
  const pwdInput = document.getElementById('aa_password');
  if (pwdInput) {
    pwdInput.addEventListener('input', function() {
      const v = this.value;
      const el = document.getElementById('aa_pwd_strength');
      if (!v) { el.textContent = ''; return; }
      let score = 0;
      if (v.length >= 8) score++;
      if (/[A-Z]/.test(v)) score++;
      if (/[0-9]/.test(v)) score++;
      if (/[^A-Za-z0-9]/.test(v)) score++;
      const levels = ['','Weak 🔴','Fair 🟡','Good 🟢','Strong 💪'];
      el.textContent = 'Strength: ' + (levels[score] || levels[1]);
    });
  }
});

// ADD ADMIN
function openAddAdmin() {
  ['aa_name','aa_username','aa_mobile','aa_password','aa_confirm'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('aa_role').value = 'staff';
  document.getElementById('aa_pwd_strength').textContent = '';
  openModal('addAdminOverlay');
}
function saveAddAdmin() {
  const name     = document.getElementById('aa_name').value.trim();
  const username = document.getElementById('aa_username').value.trim();
  const mobile   = document.getElementById('aa_mobile').value.trim();
  const role     = document.getElementById('aa_role').value;
  const pwd      = document.getElementById('aa_password').value;
  const confirm  = document.getElementById('aa_confirm').value;
  if (!name || !username || !mobile || !pwd) { toast('Please fill all fields', 'error'); return; }
  if (pwd !== confirm) { toast('Passwords do not match', 'error'); return; }
  if (admins.find(a => a.username === username)) { toast('Username already exists', 'error'); return; }
  admins.push({
    id: nextAdminId++, name, username, mobile, role, password: pwd,
    status: 'active', created: new Date().toISOString().split('T')[0]
  });
  closeModal('addAdminOverlay');
  renderAdmins();
  pushAdminLog('Added Admin Account', name + ' (@' + username + ') · ' + role, 'admin');
  saveState();
  toast('Admin account created');
}

// EDIT ADMIN
function openEditAdmin(i) {
  adminIdx = i;
  const a = admins[i];
  document.getElementById('ea_name').value     = a.name;
  document.getElementById('ea_username').value = a.username;
  document.getElementById('ea_mobile').value   = a.mobile;
  document.getElementById('ea_role').value     = a.role;
  document.getElementById('ea_status').value   = a.status;
  document.getElementById('ea_password').value = '';
  openModal('editAdminOverlay');
}
function saveEditAdmin() {
  const a = admins[adminIdx];
  a.name     = document.getElementById('ea_name').value.trim();
  a.username = document.getElementById('ea_username').value.trim();
  a.mobile   = document.getElementById('ea_mobile').value.trim();
  a.role     = document.getElementById('ea_role').value;
  a.status   = document.getElementById('ea_status').value;
  const newPwd = document.getElementById('ea_password').value;
  if (newPwd) a.password = newPwd;
  closeModal('editAdminOverlay');
  renderAdmins();
  pushAdminLog('Edited Admin Account', a.name + ' (@' + a.username + ')', 'admin');
  saveState();
  toast('Admin account updated');
}

// DELETE ADMIN
function openDeleteAdmin(i) {
  if (admins[i].role === 'super') { toast('Cannot remove Super Admin', 'error'); return; }
  adminIdx = i;
  document.getElementById('da_name_display').textContent = admins[i].name + ' (@' + admins[i].username + ')';
  openModal('deleteAdminOverlay');
}
function confirmDeleteAdmin() {
  const removedName = admins[adminIdx].name;
  admins.splice(adminIdx, 1);
  closeModal('deleteAdminOverlay');
  renderAdmins();
  pushAdminLog('Removed Admin Account', removedName, 'admin');
  saveState();
  toast('Admin account removed');
}



// ──────────────────────────────────────────────
// MOBILE SIDEBAR TOGGLE
// ──────────────────────────────────────────────
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('open');
}

// ──────────────────────────────────────────────
// CSV EXPORT
// ──────────────────────────────────────────────
function toCSV(headers, rows) {
  const escape = v => '"' + String(v ?? '').replace(/"/g,'""') + '"';
  return [headers.map(escape).join(','), ...rows.map(r => r.map(escape).join(','))].join('\n');
}

function downloadCSV(filename, csv) {
  const blob = new Blob([csv], {type:'text/csv'});
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = filename;
  a.click();
}

function exportCSV(type) {
  if (type === 'profiles') {
    const csv = toCSV(
      ['CP ID','Name','Age','Gender','Mobile','Status','Plan','Created','Approved','Expiry'],
      profiles.map(p => [p.cpId,p.name,p.age,p.gender,p.mobile,p.status,p.plan,p.created,p.approved,p.expiry])
    );
    downloadCSV('profiles.csv', csv);
    toast('Profiles exported as CSV');
  } else if (type === 'bills') {
    const csv = toCSV(
      ['#','CP ID','Name','Mobile','Plan Name','Plan Type','Amount (₹)','Payment','Billed By','Billed Date','Expiry'],
      bills.map((b,i) => [i+1, b.cpId, b.name, b.mobile, b.planName||b.plan, b.planType||b.plan, b.amount, b.payment, b.billedBy, b.billedDate, b.expiry])
    );
    downloadCSV('bills.csv', csv);
    toast('Bills exported as CSV');
  } else if (type === 'billHistory') {
    const csv = toCSV(
      ['#','Action','CP ID','Name','Mobile','Plan Name','Amount (₹)','Payment','Billed By','Recorded At','Expiry'],
      billHistory.map((b,i)=>[i+1, b._action, b.cpId, b.name, b.mobile, b.planName||b.plan, b.amount, b.payment, b.billedBy, b._recordedAt, b.expiry])
    );
    downloadCSV('bill-history.csv', csv);
    toast('Bill history exported as CSV');
  } else if (type === 'expired') {
    const q = (document.getElementById('expiredSearch')?.value || '').toLowerCase();
    const df = document.getElementById('expiredDateFrom')?.value || '';
    const dt = document.getElementById('expiredDateTo')?.value || '';
    const filtered = expiredProfiles.filter(e => {
      const txt = (e.cpId + e.name + e.mobile + (e.planName||'')).toLowerCase();
      const d = (e.expiredOn||'').split(' ')[0];
      return (!q || txt.includes(q)) && (!df || d >= df) && (!dt || d <= dt);
    });
    const csv = toCSV(
      ['#','CP ID','Name','Mobile','Plan Name','Expired Date','Reason','Expired On','Actioned By'],
      filtered.map((e,i)=>[i+1, e.cpId, e.name, e.mobile, e.planName, e.expiryDate, e.reason, e.expiredOn, e.actionedBy])
    );
    downloadCSV('expired-profiles.csv', csv);
    toast('Exported ' + filtered.length + ' expired profiles');
  } else if (type === 'reports') {
    const approved = profiles.filter(p=>p.status==='Approved').length;
    const csv = toCSV(
      ['Metric','Value'],
      [
        ['Total Members', profiles.length],
        ['Approved', approved],
        ['Pending', profiles.length - approved],
        ['Expired', expiredProfiles.length],
        ['Free Plan', profiles.filter(p=>p.plan==='free').length],
        ['Paid Plan', profiles.filter(p=>p.plan==='paid').length],
        ['Premium Plan', profiles.filter(p=>p.plan==='premium').length],
        ['Total Bills', bills.length],
        ['Total Follow-ups', followUps.length],
        ['Success Stories', stories.length],
      ]
    );
    downloadCSV('report.csv', csv);
    toast('Report exported as CSV');
  } else if (type === 'plans') {
    const csv = toCSV(
      ['Plan Name','Type','Amount (₹)','Validity (days)','Description','Status','Created Date','Created By'],
      customPlans.map(p => [
        p.name,
        PLAN_TYPE_LABELS[p.type] || p.type,
        p.amount,
        p.validity,
        p.desc || '',
        p.status,
        p.createdDate,
        p.createdBy
      ])
    );
    downloadCSV('custom-plans.csv', csv);
    toast('Plans exported as CSV');
  } else if (type === 'otp') {
    const stLabel = v => {
      const n = v==='typing'?'web_in':v==='skip'?'web_out':v==='unverified'?'otp_request':v;
      return ({verified:'OTP Verified',otp_failed:'OTP Failed',otp_request:'OTP Request',web_in:'Web In',web_out:'Web Out'}[n]) || (v || '—');
    };
    const csv = toCSV(
      ['Sl.No','Mobile','CP ID','Name','OTP Requested At','Live OTP','Verification Status','Last Login','Login Count','User Status'],
      otpLogs.map((o, i) => {
        const lr = o.liveOtp || o.live_otp || '';
        const le = o.liveOtpExpires || o.live_otp_expires || '';
        const expired = le && (new Date(le.replace(' ','T')).getTime() < Date.now());
        return [
          i + 1, o.mobile, o.cpId || '—', o.name || 'Not registered',
          o.otpRequestedAt, (lr && !expired) ? lr : '—',
          stLabel(o.verified),
          o.lastLogin, o.loginCount, o.banned ? 'Banned' : 'Active'
        ];
      })
    );
    downloadCSV('otp-logs.csv', csv);
    toast('OTP logs exported as CSV');
  } else if (type === 'adminLog') {
    const csv = toCSV(
      ['#','Admin','Role','Action','Detail','Type','Timestamp'],
      adminLog.map((e,i)=>[i+1, e.adminName, e.role, e.action, e.detail, e.type, e.timestamp])
    );
    downloadCSV('admin-log.csv', csv);
    toast('Admin log exported as CSV');
  } else if (type === 'contactLog') {
    seedContactViewLog();
    const csv = toCSV(
      ['#','Viewer Mobile','Viewer CP ID','Viewer Name','Viewer Plan','Viewed Name','Viewed CP ID','Contact Type','Date & Time'],
      contactViewLog.map((r,i)=>[i+1,r.viewerMobile,r.viewerCpId,r.viewerName,r.viewerPlan,r.viewedName,r.viewedCpId,r.contactType,r.datetime])
    );
    downloadCSV('contact-view-log.csv', csv);
    toast('Contact view log exported as CSV');
  } else if (type === 'interest') {
    const rows = [];
    usage.filter(u=>(u.profileViews||[]).length>=3).forEach(u=>{
      const scored   = scoreEngagement(u.mobile);
      if (!scored.length) return;
      const attrs    = extractAttributes(scored);
      const patterns = buildPatterns(attrs, scored);
      rows.push([u.mobile, u.name||'—', (u.profileViews||[]).length, (u.contactViews||[]).length,
        patterns[0]?.desc||'—', patterns[0]?.pct+'%',
        patterns[1]?.desc||'—', patterns[1]?.pct+'%',
        patterns[2]?.desc||'—', patterns[2]?.pct+'%',
        sessionStyle(u)
      ]);
    });
    const csv = toCSV(
      ['Mobile','Name','Profile Views','Contact Views',
       'Pattern 1','P1 %','Pattern 2','P2 %','Pattern 3','P3 %','Session Style'],
      rows
    );
    downloadCSV('interest-patterns.csv', csv);
    toast('Interest patterns exported as CSV');
  }
}

// ──────────────────────────────────────────────
// SEARCH & FILTER — called after base render
// ──────────────────────────────────────────────
// ──────────────────────────────────────────────
// PRINT / PDF EXPORT FOR PROFILES
// ──────────────────────────────────────────────
function printProfile(i) {
  const p = profiles[i];
  const win = window.open('', '_blank');
  win.document.write(`
    <html><head><title>Profile - ${p.cpId}</title>
    <style>body{font-family:sans-serif;padding:30px;color:#1a1a2e}h2{margin-bottom:4px}table{border-collapse:collapse;width:100%;margin-top:16px}td{padding:8px 12px;border:1px solid #e5e0d8;font-size:14px}tr:nth-child(even){background:#f8f7f5}.label{font-weight:600;width:35%;background:#f0ede8}</style>
    </head><body>
    <h2>Chennai Profile Matrimony — Member Profile</h2>
    <p style="color:#6b7280;font-size:13px">Printed on ${new Date().toLocaleDateString()}</p>
    ${photoSrc(p.photo1) ? `<div style="text-align:center;margin:16px 0"><img src="${location.origin+'/matrimony/backend/'+photoSrc(p.photo1)}" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid #e5e7eb" onerror="this.style.display='none'"></div>` : ''}
    <table>
      <tr><td class="label">CP ID</td><td>${p.cpId}</td></tr>
      <tr><td class="label">Full Name</td><td>${p.name}</td></tr>
      <tr><td class="label">Age</td><td>${p.age}</td></tr>
      <tr><td class="label">Gender</td><td>${p.gender}</td></tr>
      <tr><td class="label">Mobile</td><td>${p.mobile}</td></tr>
      <tr><td class="label">Status</td><td>${p.status}</td></tr>
      <tr><td class="label">Plan</td><td>${p.plan}</td></tr>
      <tr><td class="label">Created</td><td>${p.created}</td></tr>
      <tr><td class="label">Approved</td><td>${p.approved || '—'}</td></tr>
      <tr><td class="label">Expiry</td><td>${p.expiry || '—'}</td></tr>
    </table>
    <script>window.onload=()=>{window.print();}<\/script>
    </body></html>`);
  win.document.close();
}

// ──────────────────────────────────────────────
// VIEW BILL DETAILS MODAL (Manage page — Approved + Paid plan)
// ──────────────────────────────────────────────
// ──────────────────────────────────────────────
// SHARE PAYMENT LINK
// ──────────────────────────────────────────────
_sharePayCpId   = '';
_sharePayName   = '';
_sharePayMobile = '';

function sharePaymentLink(cpId, name, mobile) {
  _sharePayCpId   = cpId;
  _sharePayName   = name;
  _sharePayMobile = mobile;

  // Build the payment link — user-panel.html with ?pay=CPID param
  // Works whether running locally or hosted
  const baseUrl = window.location.href
    .replace(/[^/]*$/, '')          // strip current filename
    .replace('matrimony-admin-upgraded', '')
    + 'user-panel.html';
  const payLink = baseUrl + '?pay=' + encodeURIComponent(cpId);

  // Fill modal
  document.getElementById('shareAvatar').textContent = initials(name);
  document.getElementById('shareName').textContent   = name;
  document.getElementById('shareMeta').textContent   = cpId + ' · ' + mobile;
  document.getElementById('sharePayLink').value      = payLink;

  openModal('sharePayOverlay');
  pushAdminLog('Shared Payment Link', name + ' · ' + cpId, 'bill');
}

function copyShareLink() {
  const input = document.getElementById('sharePayLink');
  const link  = input.value;
  const btn   = document.getElementById('shareCopyBtn') || document.querySelector('[onclick="copyShareLink()"]');
  navigator.clipboard?.writeText(link).then(() => {
    toast('✅ Payment link copied to clipboard!');
    if (btn) { const orig = btn.textContent; btn.textContent = '✓ Copied!'; setTimeout(() => btn.textContent = orig, 2000); }
  }).catch(() => {
    input.select(); document.execCommand('copy');
    toast('✅ Link copied!');
  });
}

function shareViaWhatsApp() {
  const link = document.getElementById('sharePayLink').value;
  const msg  = encodeURIComponent(
    'Hi ' + _sharePayName + ',\n\n' +
    'Your matrimony profile (' + _sharePayCpId + ') is ready! ' +
    'Please click the link below to complete your registration payment:\n\n' +
    link + '\n\n' +
    'Steps: Open link → Log in with your mobile → Choose a plan → Make payment.\n\n' +
    'Thank you!'
  );
  window.open('https://wa.me/' + _sharePayMobile.replace(/\D/g,'') + '?text=' + msg, '_blank');
}

function shareViaSMS() {
  const link = document.getElementById('sharePayLink').value;
  const msg  = encodeURIComponent(
    'Hi ' + _sharePayName + ', complete your matrimony registration payment: ' + link
  );
  window.open('sms:' + _sharePayMobile + '?body=' + msg, '_blank');
}

function viewBillDetails(i) {
  const p    = profiles[i];
  const bill = bills.find(b => b.cpId === p.cpId);
  const bi   = bills.findIndex(b => b.cpId === p.cpId);

  document.getElementById('viewBillTitle').textContent = '👁 Bill — ' + p.name + ' (' + p.cpId + ')';

  if (!bill) {
    document.getElementById('viewBillBody').innerHTML = `
      <div style="padding:32px;text-align:center">
        <div style="font-size:36px;margin-bottom:10px">📋</div>
        <div style="font-weight:600;font-size:15px;margin-bottom:6px">No Bill on File</div>
        <div style="font-size:13px;color:var(--text-secondary)">No billing record found for ${p.name}.</div>
      </div>`;
    document.getElementById('viewBillEditBtn').style.display = 'none';
    document.getElementById('viewBillPrintBtn').style.display = 'none';
  } else {
    const today   = new Date().toISOString().split('T')[0];
    const isActive = bill.expiry && bill.expiry >= today;
    const row = (l, v) => v ? `
      <tr>
        <td style="font-weight:600;font-size:12px;color:var(--text-secondary);background:#f8f7f5;padding:9px 14px;border-bottom:1px solid var(--border);white-space:nowrap;width:38%">${l}</td>
        <td style="padding:9px 14px;font-size:13px;border-bottom:1px solid var(--border)">${v}</td>
      </tr>` : '';

    document.getElementById('viewBillBody').innerHTML = `
      <!-- Member Info -->
      <div style="background:#1a1a2e;padding:14px 18px;display:flex;align-items:center;gap:12px">
        <div style="width:38px;height:38px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0">${initials(p.name)}</div>
        <div>
          <div style="font-weight:700;font-size:14px;color:#fff">${p.name}</div>
          <div style="font-size:12px;color:rgba(255,255,255,.55)">${p.cpId} · ${p.mobile} · ${planBadge(p.plan).replace(/class="[^"]*"/, 'style="background:rgba(255,255,255,.15);color:#fff;border-radius:20px;padding:2px 8px;font-size:11px;font-weight:600"')}</div>
        </div>
        <div style="margin-left:auto">
          <span style="background:${isActive?'#16a34a':'#6b7280'};color:#fff;padding:4px 12px;border-radius:20px;font-size:11.5px;font-weight:700">${isActive?'✓ Active':'Expired'}</span>
        </div>
      </div>
      <!-- Bill Details -->
      <table style="width:100%;border-collapse:collapse">
        ${row('Plan Name',   bill.planName || bill.plan || '—')}
        ${row('Plan Type',   bill.planType || '—')}
        ${row('Amount',      '₹' + Number(bill.amount||0).toLocaleString('en-IN'))}
        ${row('Payment Type',bill.payment   || '—')}
        ${row('Billed Date', bill.billedDate || '—')}
        ${row('Expiry Date', bill.expiry     || '—')}
        ${row('Billed By',   bill.billedBy   || '—')}
        ${row('Bill Status', isActive ? '✅ Active' : '⚠️ Expired')}
      </table>
      <!-- Bill History for this profile -->
      ${(() => {
        const hist = billHistory.filter(h => h.cpId === p.cpId);
        if (!hist.length) return '';
        return `<div style="border-top:2px solid var(--border);padding:12px 16px">
          <div style="font-weight:700;font-size:12px;margin-bottom:8px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">📋 Bill History (${hist.length} records)</div>
          <table style="width:100%;border-collapse:collapse;font-size:12px">
            <thead><tr style="background:#f8f7f5">
              <th style="padding:6px 10px;text-align:left;font-weight:600;color:var(--text-secondary);border-bottom:1px solid var(--border)">Action</th>
              <th style="padding:6px 10px;text-align:left;font-weight:600;color:var(--text-secondary);border-bottom:1px solid var(--border)">Plan</th>
              <th style="padding:6px 10px;text-align:left;font-weight:600;color:var(--text-secondary);border-bottom:1px solid var(--border)">Amount</th>
              <th style="padding:6px 10px;text-align:left;font-weight:600;color:var(--text-secondary);border-bottom:1px solid var(--border)">Recorded At</th>
            </tr></thead>
            <tbody>
              ${hist.map(h => `<tr>
                <td style="padding:6px 10px;border-bottom:1px solid var(--border)">
                  <span style="background:${h._action==='Created'?'#f0fdf4':'#fffbeb'};color:${h._action==='Created'?'#16a34a':'#d97706'};padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">${h._action||'—'}</span>
                </td>
                <td style="padding:6px 10px;border-bottom:1px solid var(--border)">${h.planName||h.plan||'—'}</td>
                <td style="padding:6px 10px;border-bottom:1px solid var(--border)">₹${Number(h.amount||0).toLocaleString('en-IN')}</td>
                <td style="padding:6px 10px;border-bottom:1px solid var(--border);color:var(--text-secondary)">${h._recordedAt||'—'}</td>
              </tr>`).join('')}
            </tbody>
          </table>
        </div>`;
      })()}`;

    // Wire footer buttons
    document.getElementById('viewBillEditBtn').style.display = '';
    document.getElementById('viewBillPrintBtn').style.display = '';
    document.getElementById('viewBillEditBtn').onclick  = () => { closeModal('viewBillOverlay'); openBillEdit(bi); };
    document.getElementById('viewBillPrintBtn').onclick = () => printProfile(i);
  }

  openModal('viewBillOverlay');
}

// ──────────────────────────────────────────────
// REPORTS PAGE
// ──────────────────────────────────────────────
// ══════════════════════════════════════════════════════
// DIRECT LOGIN
// ══════════════════════════════════════════════════════
// ══════════════════════════════════════════════════════
// USER ORDERS
// ══════════════════════════════════════════════════════
// ══════════════════════════════════════════════════════
// ACCOUNTS
// ══════════════════════════════════════════════════════
const ACC_INCOME_CATS = ['Plan Payment','Registration Fee','Renewal','Consultation Fee','Advertisement','Sponsorship','Other Income'];
const ACC_EXPENSE_CATS = ['Salary','Office Rent','Electricity','Internet','Phone/SMS','Software','Printing','Travel','Marketing','Advertisement','Maintenance','Stationery','Refreshments','Commission','Other Expense'];

let accountEntries = [];

function updateAccCategories() {
  const isIncome = document.getElementById('acc_type_income').checked;
  const sel = document.getElementById('acc_category');
  const cats = isIncome ? ACC_INCOME_CATS : ACC_EXPENSE_CATS;
  sel.innerHTML = '<option value="">— Select —</option>' + cats.map(c => '<option>'+c+'</option>').join('');
  // Style toggle
  document.getElementById('acc_type_income_wrap').style.border = isIncome ? '2px solid #16a34a' : '2px solid #e5e7eb';
  document.getElementById('acc_type_income_wrap').style.background = isIncome ? '#f0fdf4' : '#fff';
  document.getElementById('acc_type_expense_wrap').style.border = !isIncome ? '2px solid #dc2626' : '2px solid #e5e7eb';
  document.getElementById('acc_type_expense_wrap').style.background = !isIncome ? '#fef2f2' : '#fff';
}

async function loadAccounts() {
  try {
    const data = await apiGet('api/admin/settings.php?section=accounts');
    if (data.ok && data.entries) accountEntries = data.entries;
  } catch(e) {}
  updateAccCategories();
  populateAccCatFilter();
  document.getElementById('acc_date').value = new Date().toISOString().split('T')[0];
  renderAccounts();
}

function populateAccCatFilter() {
  const sel = document.getElementById('accCatFilter');
  const cats = [...new Set(accountEntries.map(e => e.category).filter(Boolean))].sort();
  sel.innerHTML = '<option value="">All Categories</option>' + cats.map(c => '<option>'+c+'</option>').join('');
}

function clearAccFilters() {
  ['accTypeFilter','accCatFilter'].forEach(id => { const e=document.getElementById(id); if(e) e.value=''; });
  ['accDateFrom','accDateTo'].forEach(id => { const e=document.getElementById(id); if(e) e.value=''; });
  renderAccounts();
}

function renderAccounts() {
  const typeF = document.getElementById('accTypeFilter')?.value || '';
  const catF = document.getElementById('accCatFilter')?.value || '';
  const df = document.getElementById('accDateFrom')?.value || '';
  const dt = document.getElementById('accDateTo')?.value || '';

  const filtered = accountEntries.filter(e => {
    return (!typeF || e.type === typeF) && (!catF || e.category === catF)
      && (!df || e.date >= df) && (!dt || e.date <= dt);
  });

  // Stats
  const totalIncome = filtered.filter(e => e.type === 'income').reduce((s,e) => s + parseFloat(e.amount||0), 0);
  const totalExpense = filtered.filter(e => e.type === 'expense').reduce((s,e) => s + parseFloat(e.amount||0), 0);
  const balance = totalIncome - totalExpense;

  document.getElementById('accStatsRow').innerHTML = `
    <div class="stat-card"><div class="stat-icon" style="background:#f0fdf4;font-size:18px">💰</div>
      <div class="stat-body"><div class="val" style="color:#16a34a">Rs. ${totalIncome.toLocaleString()}</div><div class="lbl">Total Income</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#fef2f2;font-size:18px">💸</div>
      <div class="stat-body"><div class="val" style="color:#dc2626">Rs. ${totalExpense.toLocaleString()}</div><div class="lbl">Total Expense</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:${balance>=0?'#f0fdf4':'#fef2f2'};font-size:18px">${balance>=0?'📈':'📉'}</div>
      <div class="stat-body"><div class="val" style="color:${balance>=0?'#16a34a':'#dc2626'}">Rs. ${balance.toLocaleString()}</div><div class="lbl">Balance</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#eff6ff;font-size:18px">📋</div>
      <div class="stat-body"><div class="val">${filtered.length}</div><div class="lbl">Entries</div></div></div>`;

  const tbody = document.getElementById('accTable');
  const countEl = document.getElementById('accCount');
  if (countEl) countEl.textContent = filtered.length;
  if (!tbody) return;

  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="10"><div class="empty-state"><div class="icon">💰</div><p>No entries</p></div></td></tr>';
    return;
  }

  tbody.innerHTML = filtered.map((e, i) => {
    const origIdx = accountEntries.indexOf(e);
    const typeBadge = e.type === 'income'
      ? '<span style="background:#dcfce7;color:#16a34a;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600">💰 Income</span>'
      : '<span style="background:#fee2e2;color:#dc2626;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600">💸 Expense</span>';
    const amtColor = e.type === 'income' ? '#16a34a' : '#dc2626';
    return `<tr>
      <td>${i+1}</td>
      <td style="font-size:12px;white-space:nowrap">${e.date||'-'}</td>
      <td>${typeBadge}</td>
      <td style="font-size:12px;font-weight:600">${e.category||'-'}</td>
      <td style="font-size:12px;color:var(--text-secondary)">${e.description||'-'}</td>
      <td style="font-weight:700;color:${amtColor}">Rs. ${parseFloat(e.amount||0).toLocaleString()}</td>
      <td style="font-size:12px">${e.payment_mode||'-'}</td>
      <td style="font-size:11px;color:var(--text-secondary)">${e.reference||'-'}</td>
      <td style="font-size:11px">${e.created_by||'-'}</td>
      <td>
        <div class="actions">
          <button class="btn btn-outline btn-sm" onclick="editAccountEntry(${origIdx})">Edit</button>
          <button class="btn btn-danger btn-sm" onclick="deleteAccountEntry(${origIdx})">Delete</button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

async function addAccountEntry() {
  const date = document.getElementById('acc_date').value;
  const type = document.getElementById('acc_type_income').checked ? 'income' : 'expense';
  const category = document.getElementById('acc_category').value;
  const amount = document.getElementById('acc_amount').value;
  if (!date || !category || !amount) { toast('Date, category and amount required', 'error'); return; }

  const payload = {
    section:'accounts',
    action: editAccIdx !== null ? 'update' : 'add',
    date, type, category, amount,
    description: document.getElementById('acc_desc').value,
    payment_mode: document.getElementById('acc_mode').value,
    reference: document.getElementById('acc_ref').value,
    related: document.getElementById('acc_related')?.value || '',
    admin_name: loginAdminObj?.name || 'Admin'
  };

  if (editAccIdx !== null && accountEntries[editAccIdx]) {
    payload.id = accountEntries[editAccIdx].id;
  }

  try {
    await apiPost('api/admin/settings.php', payload);
    toast(editAccIdx !== null ? 'Entry updated' : 'Entry added');
    editAccIdx = null;
    document.getElementById('acc_amount').value = '';
    document.getElementById('acc_desc').value = '';
    document.getElementById('acc_ref').value = '';
    const relEl = document.getElementById('acc_related');
    if (relEl) relEl.value = '';
    // Reset button text
    const btn = document.querySelector('#accountsSection .btn-green');
    if (btn) btn.innerHTML = 'Add Entry';
    loadAccounts();
  } catch(e) { toast('Error: ' + e.message, 'error'); }
}

let editAccIdx = null;

function editAccountEntry(idx) {
  const e = accountEntries[idx];
  if (!e) return;
  editAccIdx = idx;
  document.getElementById('acc_date').value = e.date || '';
  if (e.type === 'income') document.getElementById('acc_type_income').checked = true;
  else document.getElementById('acc_type_expense').checked = true;
  updateAccCategories();
  document.getElementById('acc_category').value = e.category || '';
  document.getElementById('acc_amount').value = e.amount || '';
  document.getElementById('acc_desc').value = e.description || '';
  document.getElementById('acc_mode').value = e.payment_mode || '';
  document.getElementById('acc_ref').value = e.reference || '';
  const relEl = document.getElementById('acc_related');
  if (relEl) relEl.value = e.related || '';
  // Change button text
  const btn = document.querySelector('#accountsSection .btn-green');
  if (btn) btn.innerHTML = 'Update Entry';
}

async function deleteAccountEntry(idx) {
  const e = accountEntries[idx];
  if (!e || !e.id) { toast('Cannot delete this entry', 'error'); return; }
  if (!confirm('Delete this entry? Rs. ' + parseFloat(e.amount||0).toLocaleString() + ' - ' + (e.description||e.category))) return;
  try {
    await apiPost('api/admin/settings.php', { section:'accounts', action:'delete', id: e.id });
    toast('Entry deleted');
    loadAccounts();
  } catch(e) { toast('Error: ' + e.message, 'error'); }
}

// ──────────────────────────────────────────────
// MESSAGES
// ──────────────────────────────────────────────
let contactMessages = [];

async function loadMessages() {
  try {
    const data = await apiGet('api/admin/settings.php?section=messages');
    if (data.ok && data.messages) contactMessages = data.messages;
  } catch(e) { console.error('loadMessages error:', e); contactMessages = []; }
  renderMessages();
  updateMsgBadge();
}

function updateMsgBadge() {
  const newCount = contactMessages.filter(m => m.status === 'new').length;
  const badge = document.getElementById('msgBadge');
  if (badge) {
    badge.textContent = newCount;
    badge.style.display = newCount > 0 ? 'inline' : 'none';
  }
}

function renderMessages() {
  const q = (document.getElementById('msgSearch')?.value || '').toLowerCase();
  const stF = document.getElementById('msgStatusFilter')?.value || '';
  const filtered = contactMessages.filter(m => {
    const txt = (m.name + m.phone + m.email + m.message + m.cp_id).toLowerCase();
    return (!q || txt.includes(q)) && (!stF || m.status === stF);
  });
  const list = document.getElementById('msgList');
  if (!list) return;
  if (filtered.length === 0) {
    list.innerHTML = '<div class="empty-state"><div class="icon">💬</div><p>No messages</p></div>';
    return;
  }
  const statusColors = { new:'#dc2626', read:'#d97706', replied:'#16a34a', closed:'#9ca3af' };
  const statusLabels = { new:'New', read:'Read', replied:'Replied', closed:'Closed' };
  list.innerHTML = filtered.map((m, i) => {
    const origIdx = contactMessages.indexOf(m);
    const sc = statusColors[m.status] || '#999';
    const sl = statusLabels[m.status] || m.status;
    const loggedBadge = m.is_logged_in == 1
      ? '<span style="background:#dcfce7;color:#16a34a;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600">Logged In</span>'
      : '<span style="background:#fee2e2;color:#dc2626;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600">Guest</span>';
    const cpBadge = m.cp_id ? '<code style="font-size:11px;background:#f3f4f6;padding:1px 6px;border-radius:4px;margin-left:6px">' + m.cp_id + '</code>' : '';
    return '<div class="card" style="padding:16px;border-left:4px solid '+sc+'">'
      + '<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;margin-bottom:10px">'
      + '<div>'
      + '<div style="font-weight:700;font-size:14px">' + esc(m.name) + ' ' + loggedBadge + cpBadge + '</div>'
      + '<div style="font-size:12.5px;color:var(--text-secondary);margin-top:3px">'
      + '📱 ' + esc(m.phone) + (m.email ? ' &nbsp;·&nbsp; ✉ ' + esc(m.email) : '') + (m.mobile && m.mobile !== m.phone ? ' &nbsp;·&nbsp; Session: ' + esc(m.mobile) : '')
      + '</div></div>'
      + '<div style="display:flex;align-items:center;gap:6px">'
      + '<span style="background:'+sc+'22;color:'+sc+';padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700">'+sl+'</span>'
      + '<span style="font-size:11px;color:var(--text-secondary)">🕐 ' + esc(m.created_at) + '</span>'
      + '</div></div>'
      + '<div style="background:#f9fafb;border-radius:8px;padding:12px 14px;font-size:13px;line-height:1.6;margin-bottom:10px">' + esc(m.message) + '</div>'
      + (m.admin_reply ? '<div style="background:#f0fdf4;border-radius:8px;padding:12px 14px;font-size:13px;line-height:1.6;margin-bottom:10px;border:1px solid #bbf7d0">'
        + '<div style="font-size:11px;font-weight:700;color:#16a34a;margin-bottom:4px">Admin Reply (' + esc(m.replied_by||'') + ' · ' + esc(m.replied_at||'') + ')</div>'
        + esc(m.admin_reply) + '</div>' : '')
      + '<div style="display:flex;gap:6px;flex-wrap:wrap">'
      + '<input id="msgReply_'+origIdx+'" class="input" placeholder="Type reply..." style="flex:1;min-width:200px;font-size:12px;padding:8px">'
      + '<button class="btn btn-green btn-sm" onclick="replyMessage('+origIdx+')">Reply</button>'
      + (m.status==='new' ? '<button class="btn btn-outline btn-sm" onclick="updateMsgStatus('+origIdx+',\'read\')">Mark Read</button>' : '')
      + (m.status!=='closed' ? '<button class="btn btn-outline btn-sm" onclick="updateMsgStatus('+origIdx+',\'closed\')">Close</button>' : '')
      + '<button class="btn btn-danger btn-sm" onclick="deleteMessage('+origIdx+')">Delete</button>'
      + '</div></div>';
  }).join('');
}

async function replyMessage(idx) {
  const m = contactMessages[idx];
  if (!m) return;
  const reply = document.getElementById('msgReply_'+idx)?.value?.trim();
  if (!reply) { toast('Type a reply first', 'error'); return; }
  try {
    await apiPost('api/admin/settings.php', { section:'messages', action:'reply', id: m.id, reply });
    toast('Reply saved');
    loadMessages();
  } catch(e) { toast('Error: '+e.message, 'error'); }
}

async function updateMsgStatus(idx, status) {
  const m = contactMessages[idx];
  if (!m) return;
  try {
    await apiPost('api/admin/settings.php', { section:'messages', action:'status', id: m.id, status });
    toast('Status updated');
    loadMessages();
  } catch(e) { toast('Error: '+e.message, 'error'); }
}

async function deleteMessage(idx) {
  const m = contactMessages[idx];
  if (!m || !confirm('Delete this message from '+m.name+'?')) return;
  try {
    await apiPost('api/admin/settings.php', { section:'messages', action:'delete', id: m.id });
    toast('Message deleted');
    loadMessages();
  } catch(e) { toast('Error: '+e.message, 'error'); }
}

async function syncBillsToAccounts() {
  if (!confirm('Sync all paid bills as income entries to Accounts?\nAlready synced bills will be skipped.')) return;
  try {
    const data = await apiPost('api/admin/settings.php', { section:'accounts', action:'sync_bills' });
    toast('✅ ' + (data.message || 'Synced'));
    loadAccounts();
  } catch(e) { toast('Error: ' + e.message, 'error'); }
}

let userOrders = [];
let orderArchive = [];

async function loadUserOrders() {
  try {
    const data = await apiGet('api/admin/settings.php?section=userOrders');
    if (data.ok && data.orders) userOrders = data.orders;
    if (data.ok && data.archive) orderArchive = data.archive;
  } catch(e) {}
  renderUserOrders();
  renderOrderArchive();
}

function _uoFilter() {
  const q = (document.getElementById('uoSearch')?.value || '').toLowerCase();
  const statusF = document.getElementById('uoStatusFilter')?.value || '';
  const planF = document.getElementById('uoPlanFilter')?.value || '';
  const df = document.getElementById('uoDateFrom')?.value || '';
  const dt = document.getElementById('uoDateTo')?.value || '';
  return userOrders.filter(o => {
    const txt = ((o.mobile||'') + (o.name||'') + (o.cp_id||'') + (o.plan||'') + (o.method||'') + (o.txn_ref||'')).toLowerCase();
    const d = (o.created_at||'').split(' ')[0];
    return (!q || txt.includes(q))
      && (!statusF || o.status === statusF)
      && (!planF || (o.plan||'').toLowerCase() === planF)
      && (!df || d >= df) && (!dt || d <= dt);
  });
}

function renderUserOrders() {
  const tbody = document.getElementById('userOrdersTable');
  if (!tbody) return;
  const filtered = _uoFilter();
  const countEl = document.getElementById('uoCount');
  if (countEl) countEl.textContent = filtered.length + ' / ' + userOrders.length;
  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="12"><div class="empty-state"><div class="icon">🛒</div><p>No orders yet</p></div></td></tr>';
    return;
  }
  tbody.innerHTML = filtered.map((o, i) => {
    const statusBadge = o.status === 'approved'
      ? '<span style="background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600">✓ Approved</span>'
      : o.status === 'rejected'
      ? '<span style="background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600">✕ Rejected</span>'
      : '<span style="background:#fef3c7;color:#d97706;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600">⏳ Pending</span>';
    const adminInfo = o.processed_by ? `<div style="font-size:10px;color:#6b7280;margin-top:2px">by ${o.processed_by}</div>` : '';
    return `<tr>
      <td>${i+1}</td>
      <td style="font-size:12px;white-space:nowrap">${o.created_at || '-'}</td>
      <td style="font-weight:600;color:#c0392b">${o.mobile}</td>
      <td>${o.name || '-'}</td>
      <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${o.cp_id || '-'}</code></td>
      <td style="font-weight:600">${o.plan}</td>
      <td>${o.amount || '-'}</td>
      <td style="font-size:12px">${o.method || '-'}</td>
      <td style="font-size:12px">${o.txn_ref || '-'}</td>
      <td>${o.payment_proof ? `<a href="api/uploads/${o.payment_proof}" target="_blank" style="color:#2563eb;font-size:11px;font-weight:600">📄 View Proof</a>` : '<span style="color:#9ca3af;font-size:11px">None</span>'}</td>
      <td>${statusBadge}${adminInfo}</td>
      <td>${o.status === 'pending' ? `<div style="display:flex;gap:4px">
        <button class="btn btn-sm" onclick="processOrder(${o.id},'approved')" style="background:#dcfce7;color:#16a34a;border:1px solid #bbf7d0;font-size:11px;padding:4px 8px">Approve</button>
        <button class="btn btn-sm" onclick="processOrder(${o.id},'rejected')" style="background:#fee2e2;color:#dc2626;border:1px solid #fecaca;font-size:11px;padding:4px 8px">Reject</button>
      </div>` : '<span style="font-size:11px;color:#9ca3af">Done</span>'}</td>
    </tr>`;
  }).join('');
}

async function processOrder(id, status) {
  const note = prompt(status === 'approved' ? 'Approval note (optional):' : 'Rejection reason:');
  if (note === null) return;
  try {
    await apiPost('api/admin/settings.php', {
      section:'userOrders', action:'process', id, status,
      admin_note: note, admin_name: loginAdminObj?.name || 'Admin'
    });
    const o = userOrders.find(o => o.id === id);
    if (o) { o.status = status; o.processed_by = loginAdminObj?.name || 'Admin'; }
    renderUserOrders();
    toast('Order ' + status);
  } catch(e) { toast('Error', 'error'); }
}

function clearUoFilters() {
  ['uoSearch','uoDateFrom','uoDateTo'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  ['uoStatusFilter','uoPlanFilter'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  renderUserOrders();
}

function exportUserOrders() {
  const filtered = _uoFilter();
  const csvRows = [['Date','Mobile','Name','CP ID','Plan','Amount','Method','Txn Ref','Proof','Status','Processed By']];
  filtered.forEach(o => {
    csvRows.push([o.created_at||'', o.mobile||'', o.name||'', o.cp_id||'', o.plan||'', o.amount||'', o.method||'',
      (o.txn_ref||'').replace(/"/g,'""'), o.payment_proof ? 'Yes' : 'No', o.status||'', o.processed_by||'']);
  });
  const csv = csvRows.map(row => row.map(c => '"' + String(c).replace(/"/g,'""') + '"').join(',')).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'user_orders_' + new Date().toISOString().split('T')[0] + '.csv';
  a.click(); URL.revokeObjectURL(a.href);
  toast('CSV exported — ' + filtered.length + ' rows');
}

function renderOrderArchive() {
  const tbody = document.getElementById('orderArchiveTable');
  const countEl = document.getElementById('orderArchiveCount');
  if (countEl) countEl.textContent = orderArchive.length;
  if (!tbody) return;
  if (orderArchive.length === 0) {
    tbody.innerHTML = '<tr><td colspan="12"><div class="empty-state"><div class="icon">📋</div><p>No archive records</p></div></td></tr>';
    return;
  }
  tbody.innerHTML = orderArchive.map((a, i) => {
    const actionBadge = a.action === 'Placed'
      ? '<span style="background:#dbeafe;color:#2563eb;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600">🛒 Placed</span>'
      : a.action === 'Approved'
      ? '<span style="background:#dcfce7;color:#16a34a;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600">✓ Approved</span>'
      : a.action === 'Rejected'
      ? '<span style="background:#fee2e2;color:#dc2626;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600">✕ Rejected</span>'
      : `<span style="background:#f3f4f6;color:#6b7280;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600">${a.action}</span>`;
    return `<tr>
      <td>${i+1}</td>
      <td style="font-size:12px;white-space:nowrap">${a.created_at||'-'}</td>
      <td style="font-size:12px;font-weight:600">#${a.order_id||'-'}</td>
      <td style="font-size:12px;color:#c0392b;font-weight:600">${a.mobile||'-'}</td>
      <td>${a.name||'-'}</td>
      <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${a.cp_id||'-'}</code></td>
      <td style="font-weight:600">${a.plan||'-'}</td>
      <td>${a.amount ? 'Rs. '+a.amount : '-'}</td>
      <td style="font-size:12px">${a.method||'-'}</td>
      <td>${actionBadge}</td>
      <td style="font-size:12px">${a.action_by||'-'}</td>
      <td style="font-size:11px;color:#6b7280">${a.admin_note||'-'}</td>
    </tr>`;
  }).join('');
}

// ══════════════════════════════════════════════════════
// PROFILE VIEW LOG
// ══════════════════════════════════════════════════════
let profileViewLogs = [];

async function loadProfileViewLog() {
  try {
    const resp = await apiGet('api/admin/settings.php?section=profileViewLog');
    if (resp.ok && resp.logs) profileViewLogs = resp.logs;
  } catch(e) {}
  renderProfileViewLog();
}

function renderProfileViewLog() {
  const q = (document.getElementById('pvlSearch')?.value || '').toLowerCase();
  const df = document.getElementById('pvlDateFrom')?.value || '';
  const dt = document.getElementById('pvlDateTo')?.value || '';

  const filtered = profileViewLogs.filter(l => {
    const txt = (l.mobile + l.name + l.cp_id + l.target_cp_id + l.target_name).toLowerCase();
    const d = (l.datetime || '').split(' ')[0];
    return (!q || txt.includes(q)) && (!df || d >= df) && (!dt || d <= dt);
  });

  const countEl = document.getElementById('pvlCount');
  if (countEl) countEl.textContent = filtered.length + ' records';

  const tbody = document.getElementById('pvlTable');
  if (!tbody) return;

  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="icon">👁</div><p>No profile views recorded</p></div></td></tr>';
    return;
  }

  tbody.innerHTML = filtered.slice(0, 500).map((l, i) => {
    const planBadge = l.plan === 'paid' ? '<span class="badge badge-blue">Paid</span>'
      : l.plan === 'premium' ? '<span class="badge badge-amber">Premium</span>'
      : '<span class="badge badge-gray">Free</span>';

    // Viewer mobile: follow-up logic
    const pvViewerIdx = profiles.findIndex(p => p.mobile === l.mobile);
    const pvViewerHasFU = pvViewerIdx >= 0
      ? followUps.some(f => f.cpId === profiles[pvViewerIdx]?.cpId)
      : followUps.some(f => f.mobile === l.mobile);
    let pvViewerCell;
    if (pvViewerHasFU) {
      pvViewerCell = `<span style="font-size:12px;font-weight:600;color:var(--text-primary)">${l.mobile || 'anonymous'}</span>`;
    } else if (pvViewerIdx >= 0) {
      pvViewerCell = `<span style="font-size:12px;font-weight:700;color:#dc2626;cursor:pointer;border-bottom:1.5px dashed #dc2626" title="No follow-up — double-click to create" ondblclick="openFollowUp(${pvViewerIdx})">${l.mobile}</span>`;
    } else {
      pvViewerCell = `<span style="font-size:12px;font-weight:700;color:#dc2626;cursor:pointer;border-bottom:1.5px dashed #dc2626" title="No follow-up — double-click to create" ondblclick="openFollowUpByMobile('${l.mobile}','${(l.name||'').replace(/'/g,"\\'")}')">${l.mobile || 'anonymous'}</span>`;
    }

    // Viewed profile: follow-up logic
    const pvViewedIdx = profiles.findIndex(p => p.cpId === l.target_cp_id);
    const pvViewedHasFU = pvViewedIdx >= 0
      ? followUps.some(f => f.cpId === l.target_cp_id)
      : false;
    let pvViewedCell;
    if (!l.target_cp_id || l.target_cp_id === '-') {
      pvViewedCell = `<span style="font-size:12px;color:var(--text-secondary)">-</span>`;
    } else if (pvViewedHasFU) {
      pvViewedCell = `<code style="font-size:12px;background:#f0fdf4;color:#16a34a;padding:2px 7px;border-radius:5px;font-weight:700">${l.target_cp_id}</code>`;
    } else if (pvViewedIdx >= 0) {
      pvViewedCell = `<code style="font-size:12px;background:#fee2e2;color:#dc2626;padding:2px 7px;border-radius:5px;font-weight:700;cursor:pointer;border-bottom:1.5px dashed #dc2626" title="No follow-up — double-click to create" ondblclick="openFollowUp(${pvViewedIdx})">${l.target_cp_id}</code>`;
    } else {
      pvViewedCell = `<code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${l.target_cp_id}</code>`;
    }

    return `<tr>
      <td>${i+1}</td>
      <td>${pvViewerCell}</td>
      <td style="font-size:12px">${l.name || '-'}</td>
      <td>${planBadge}</td>
      <td>${pvViewedCell}</td>
      <td style="font-size:12px">${l.target_name || '-'}</td>
      <td style="font-size:12px;white-space:nowrap;color:var(--text-secondary)">${l.datetime || '-'}</td>
    </tr>`;
  }).join('');
}

let directLogins = [];
let directLoginLogs = [];

async function loadDirectLogins() {
  try {
    const data = await apiGet('api/admin/settings.php?section=directLogin');
    if (data.ok && data.directLogins) directLogins = data.directLogins;
    if (data.ok && data.logs) directLoginLogs = data.logs;
  } catch(e) {}
  renderDirectLogins();
  renderDirectLoginLogs();
}

function _dlFilter() {
  const q = (document.getElementById('dlSearch')?.value || '').toLowerCase();
  const statusF = document.getElementById('dlStatusFilter')?.value || '';
  const df = document.getElementById('dlDateFrom')?.value || '';
  const dt = document.getElementById('dlDateTo')?.value || '';
  return directLogins.filter(d => {
    const txt = ((d.mobile||'') + (d.name||'') + (d.cp_id||'') + (d.created_by||'')).toLowerCase();
    const date = (d.created_at||'').split(' ')[0];
    return (!q || txt.includes(q))
      && (!statusF || d.status === statusF)
      && (!df || date >= df) && (!dt || date <= dt);
  });
}

function renderDirectLogins() {
  const tbody = document.getElementById('dlTable');
  const filtered = _dlFilter();
  const countEl = document.getElementById('dlCount');
  if (countEl) countEl.textContent = filtered.length + ' / ' + directLogins.length;
  if (!tbody) return;
  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="10"><div class="empty-state"><div class="icon">🔑</div><p>No direct login users</p></div></td></tr>';
    return;
  }
  tbody.innerHTML = filtered.map((d, i) => {
    const isActive = d.status === 'active';
    const statusBadge = isActive
      ? '<span style="background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600">Active</span>'
      : '<span style="background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600">Inactive</span>';
    return `<tr style="${!isActive?'opacity:0.6':''}">
      <td>${i+1}</td>
      <td style="font-weight:600;color:#c0392b">${d.mobile}</td>
      <td>${d.name||'-'}</td>
      <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${d.cp_id||'-'}</code></td>
      <td>${statusBadge}</td>
      <td style="font-size:12px">${d.created_by||'-'}</td>
      <td style="font-size:12px">${d.created_at||'-'}</td>
      <td style="font-size:12px">${d.last_used||'Never'}</td>
      <td style="text-align:center;font-weight:600">${d.use_count||0}</td>
      <td>
        <button class="btn btn-sm" onclick="toggleDirectLogin(${d.id},'${isActive?'inactive':'active'}')"
          style="background:${isActive?'#fee2e2':'#dcfce7'};color:${isActive?'#dc2626':'#16a34a'};border:1px solid ${isActive?'#fecaca':'#bbf7d0'};font-size:11px;padding:4px 10px">
          ${isActive?'Deactivate':'Activate'}
        </button>
      </td>
    </tr>`;
  }).join('');
}

function renderDirectLoginLogs() {
  const tbody = document.getElementById('dlLogTable');
  const countEl = document.getElementById('dlLogCount');
  if (countEl) countEl.textContent = directLoginLogs.length;
  if (!tbody) return;
  if (directLoginLogs.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="icon">📋</div><p>No activity yet</p></div></td></tr>';
    return;
  }
  tbody.innerHTML = directLoginLogs.map((l, i) => {
    const actionBadge = l.action === 'Added'
      ? '<span style="background:#dcfce7;color:#16a34a;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600">+ Added</span>'
      : l.action === 'Activated'
      ? '<span style="background:#dbeafe;color:#2563eb;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600">✓ Activated</span>'
      : l.action === 'Deactivated'
      ? '<span style="background:#fee2e2;color:#dc2626;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600">✕ Deactivated</span>'
      : l.action === 'Login Used'
      ? '<span style="background:#fef3c7;color:#92400e;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600">🔑 Login Used</span>'
      : `<span style="background:#f3f4f6;color:#6b7280;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600">${l.action}</span>`;
    return `<tr>
      <td>${i+1}</td>
      <td style="font-size:12px;white-space:nowrap">${l.created_at||'-'}</td>
      <td style="font-weight:600;color:#c0392b">${l.mobile||'-'}</td>
      <td>${l.name||'-'}</td>
      <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${l.cp_id||'-'}</code></td>
      <td>${actionBadge}</td>
      <td style="font-size:12px">${l.action_by||'-'}</td>
    </tr>`;
  }).join('');
}

function clearDlFilters() {
  ['dlSearch','dlDateFrom','dlDateTo'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  const sf = document.getElementById('dlStatusFilter'); if (sf) sf.value = '';
  renderDirectLogins();
}

function exportDirectLogins() {
  const filtered = _dlFilter();
  const csvRows = [['Mobile','Name','CP ID','Status','Created By','Created','Last Used','Uses']];
  filtered.forEach(d => {
    csvRows.push([d.mobile||'', d.name||'', d.cp_id||'', d.status||'', d.created_by||'', d.created_at||'', d.last_used||'Never', d.use_count||0]);
  });
  const csv = csvRows.map(row => row.map(c => '"' + String(c).replace(/"/g,'""') + '"').join(',')).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'direct_logins_' + new Date().toISOString().split('T')[0] + '.csv';
  a.click(); URL.revokeObjectURL(a.href);
  toast('CSV exported — ' + filtered.length + ' rows');
}

// Lookup profile on mobile input
document.addEventListener('DOMContentLoaded', () => {
  const dlMob = document.getElementById('dl_mobile');
  if (dlMob) {
    let debounce;
    dlMob.addEventListener('input', () => {
      clearTimeout(debounce);
      const m = dlMob.value.replace(/\D/g,'');
      if (m.length === 10) {
        debounce = setTimeout(async () => {
          const p = profiles.find(p => p.mobile === m);
          const info = document.getElementById('dl_profile_info');
          if (p) {
            info.style.display = 'block';
            info.innerHTML = `<strong>${p.name}</strong> · ${p.cpId} · ${p.gender} · ${p.age} yrs`;
          } else {
            info.style.display = 'block';
            info.style.background = '#fef3c7'; info.style.borderColor = '#fde68a';
            info.innerHTML = '⚠️ No profile found for this mobile';
          }
        }, 300);
      } else {
        document.getElementById('dl_profile_info').style.display = 'none';
      }
    });
  }
});

async function addDirectLogin() {
  const mobile = document.getElementById('dl_mobile').value.replace(/\D/g,'');
  if (mobile.length !== 10) { toast('Enter valid 10-digit mobile', 'error'); return; }
  try {
    const resp = await apiPost('api/admin/settings.php', {
      section:'directLogin', action:'add', mobile,
      admin_name: loginAdminObj?.name || 'Admin'
    });
    if (resp.ok) {
      toast('Direct login added for ' + mobile);
      document.getElementById('dl_mobile').value = '';
      document.getElementById('dl_profile_info').style.display = 'none';
      loadDirectLogins();
    } else {
      toast(resp.error || 'Failed', 'error');
    }
  } catch(e) { toast('Error', 'error'); }
}

async function toggleDirectLogin(id, newStatus) {
  try {
    const resp = await apiPost('api/admin/settings.php', {
      section:'directLogin', action:'toggle', id, status: newStatus,
      admin_name: loginAdminObj?.name || 'Admin'
    });
    if (resp.ok) {
      toast('Status updated to ' + newStatus);
      loadDirectLogins();
    }
  } catch(e) { toast('Error', 'error'); }
}

function _prFilter() {
  const q = (document.getElementById('prSearch')?.value || '').toLowerCase();
  const reasonF = document.getElementById('prReasonFilter')?.value || '';
  const statusF = document.getElementById('prStatusFilter')?.value || '';
  const df = document.getElementById('prDateFrom')?.value || '';
  const dt = document.getElementById('prDateTo')?.value || '';
  return profileReports.filter(r => {
    const txt = ((r.cpId||r.cp_id||'') + (r.profileName||r.profile_name||'') + (r.profileMobile||r.profile_mobile||'') + (r.reporterMobile||r.reporter_mobile||'') + (r.reason||'')).toLowerCase();
    const reason = (r.reason||'').toLowerCase().replace(/\s+/g,'_');
    const d = (r.reportedAt||r.reported_at||'').split(' ')[0];
    return (!q || txt.includes(q))
      && (!reasonF || reason === reasonF || r.reason === reasonF)
      && (!statusF || r.status === statusF)
      && (!df || d >= df) && (!dt || d <= dt);
  });
}

function renderProfileReports() {
  const tbody = document.getElementById('reportsTable');
  if (!tbody) return;
  const filtered = _prFilter();
  const countEl = document.getElementById('prCount');
  if (countEl) countEl.textContent = filtered.length + ' / ' + profileReports.length;
  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="9"><div class="empty-state"><div class="icon">📋</div><p>No reports</p></div></td></tr>';
    return;
  }
  tbody.innerHTML = filtered.map((r, i) => {
    const reasonBadge = r.reason === 'already_married' || r.reason === 'Already Married'
      ? '<span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600">💍 Already Married</span>'
      : r.reason === 'fraud'
      ? '<span style="background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600">🚨 Fraud</span>'
      : r.reason === 'misinformation'
      ? '<span style="background:#fff7ed;color:#c2410c;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600">⚠️ Misinformation</span>'
      : `<span style="background:#f3f4f6;color:#6b7280;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600">${r.reason}</span>`;
    const statusBadge = r.status === 'resolved'
      ? `<span style="background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600">✓ Resolved</span>`
      : r.status === 'dismissed'
      ? `<span style="background:#f3f4f6;color:#6b7280;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600">✕ Dismissed</span>`
      : '<span style="background:#fef3c7;color:#d97706;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600">⏳ Pending</span>';
    const adminNote = r.adminNote || r.admin_note || '';
    const resolvedBy = r.resolvedBy || r.resolved_by || '';
    const resolvedAt = r.resolvedAt || r.resolved_at || '';
    return `<tr>
      <td>${i+1}</td>
      <td style="font-size:12px;white-space:nowrap">${r.reportedAt || r.reported_at || '-'}</td>
      <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${r.cpId || r.cp_id || ''}</code></td>
      <td>${r.profileName || r.profile_name || '-'}</td>
      <td style="font-size:12px;color:#c0392b;font-weight:600">${r.profileMobile || r.profile_mobile || '-'}</td>
      <td style="font-size:12px">${r.reporterMobile || r.reporter_mobile || '-'}</td>
      <td>${reasonBadge}</td>
      <td>${statusBadge}${adminNote ? `<div style="font-size:10px;color:#6b7280;margin-top:3px">${adminNote}</div>` : ''}${resolvedBy ? `<div style="font-size:10px;color:#2563eb;margin-top:2px">by <b>${resolvedBy}</b></div>` : ''}${resolvedAt ? `<div style="font-size:10px;color:#9ca3af">${resolvedAt}</div>` : ''}</td>
      <td>
        ${r.status === 'pending' ? `<div style="display:flex;gap:4px">
          <button class="btn btn-sm" onclick="openResolvePopup(${r.id})" style="background:#dcfce7;color:#16a34a;border:1px solid #bbf7d0;font-size:11px;padding:4px 8px">Resolve</button>
          <button class="btn btn-sm" onclick="openResolvePopup(${r.id},'dismiss')" style="background:#f3f4f6;color:#6b7280;border:1px solid #e5e7eb;font-size:11px;padding:4px 8px">Dismiss</button>
        </div>` : '<span style="font-size:11px;color:#9ca3af">Done</span>'}
      </td>
    </tr>`;
  }).join('');
}

let _resolveReportId = null;
let _resolveReportAction = 'resolved';

function openResolvePopup(id, action) {
  _resolveReportId = id;
  _resolveReportAction = action === 'dismiss' ? 'dismissed' : 'resolved';
  const r = profileReports.find(r => r.id === id);
  const title = action === 'dismiss' ? 'Dismiss Report' : 'Resolve Report';
  const cpId = r ? (r.cpId || r.cp_id) : '';
  const name = r ? (r.profileName || r.profile_name) : '';

  document.getElementById('resolvePopupTitle').textContent = title;
  document.getElementById('resolvePopupInfo').textContent = `${cpId} — ${name}`;
  document.getElementById('resolveReason').value = '';
  openModal('resolvePopupOverlay');
}

async function submitResolve() {
  const reason = document.getElementById('resolveReason').value.trim();
  if (!reason) { toast('Please select a reason', 'error'); return; }
  try {
    await apiPost('api/admin/settings.php', {
      section:'profileReports', action:'resolve',
      id: _resolveReportId, status: _resolveReportAction, admin_note: reason
    });
    const r = profileReports.find(r => r.id === _resolveReportId);
    if (r) {
      r.status = _resolveReportAction;
      r.adminNote = reason; r.admin_note = reason;
      r.resolvedBy = loginAdminObj?.name || 'Admin'; r.resolved_by = r.resolvedBy;
      r.resolvedAt = new Date().toLocaleString(); r.resolved_at = r.resolvedAt;
    }
    renderProfileReports();
    closeModal('resolvePopupOverlay');
    toast('Report ' + _resolveReportAction);
  } catch (e) { toast('Error', 'error'); }
}

function clearPrFilters() {
  ['prSearch','prDateFrom','prDateTo'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  ['prReasonFilter','prStatusFilter'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  renderProfileReports();
}

function exportProfileReports() {
  const filtered = _prFilter();
  const csvRows = [['Reported Date','Profile ID','Profile Name','Profile Mobile','Reported By','Reason','Status','Admin Note','Resolved By','Resolved At']];
  filtered.forEach(r => {
    csvRows.push([
      r.reportedAt||r.reported_at||'', r.cpId||r.cp_id||'', r.profileName||r.profile_name||'',
      r.profileMobile||r.profile_mobile||'', r.reporterMobile||r.reporter_mobile||'',
      r.reason||'', r.status||'', (r.adminNote||r.admin_note||'').replace(/"/g,'""'),
      r.resolvedBy||r.resolved_by||'', r.resolvedAt||r.resolved_at||''
    ]);
  });
  const csv = csvRows.map(row => row.map(c => '"' + c + '"').join(',')).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'profile_reports_' + new Date().toISOString().split('T')[0] + '.csv';
  a.click(); URL.revokeObjectURL(a.href);
  toast('CSV exported — ' + filtered.length + ' rows');
}

// ═══════════════════════════════════════════════════════════════════════════════
// USER RESPONSE — aggregate what other members have done toward a given profile
// (who viewed them, who fetched their number, who reported them).
// Reuses the globally loaded contactViewLog, profileViewLogs, profileReports
// arrays — ensures both logs are loaded before the first render.
// ═══════════════════════════════════════════════════════════════════════════════
async function loadUserResponse() {
  // Ensure all three data sources are hydrated
  const ready = [];
  if (typeof contactViewLog !== 'undefined' && contactViewLog.length === 0 && typeof loadContactViewLog === 'function') ready.push(loadContactViewLog());
  if (profileViewLogs.length === 0 && typeof loadProfileViewLog === 'function') ready.push(loadProfileViewLog());
  // profileReports is populated by loadAll() via mapRows — refresh is cheap but optional
  try { await Promise.all(ready); } catch(e) {}
  renderUserResponse();
}

function renderUserResponse() {
  const q = (document.getElementById('urMobile')?.value || '').trim().toLowerCase();
  const header = document.getElementById('urHeader');
  const pvBody = document.getElementById('urProfileViewTable');
  const cvBody = document.getElementById('urContactViewTable');
  const rptBody = document.getElementById('urReportTable');
  const pvCount = document.getElementById('urProfileViewCount');
  const cvCount = document.getElementById('urContactViewCount');
  const rptCount = document.getElementById('urReportCount');
  if (!pvBody || !cvBody || !rptBody) return;

  if (!q) {
    if (header) header.innerHTML = '<div style="padding:18px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;text-align:center;color:var(--text-secondary);font-size:13px;margin-bottom:16px">Type a mobile number or CP ID above to see that member\'s received responses.</div>';
    pvBody.innerHTML = cvBody.innerHTML = rptBody.innerHTML = '';
    pvCount.textContent = cvCount.textContent = rptCount.textContent = '0';
    return;
  }

  // Resolve target profile: match by mobile (any length) or CP ID
  const target = profiles.find(p => (p.mobile || '').toLowerCase() === q || (p.cpId || '').toLowerCase() === q)
              || profiles.find(p => (p.mobile || '').toLowerCase().includes(q) || (p.cpId || '').toLowerCase().includes(q));

  if (!target) {
    if (header) header.innerHTML = '<div style="padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;color:#991b1b;font-size:13px;margin-bottom:16px">No profile found matching <strong>' + q + '</strong>.</div>';
    pvBody.innerHTML = cvBody.innerHTML = rptBody.innerHTML = '';
    pvCount.textContent = cvCount.textContent = rptCount.textContent = '0';
    return;
  }

  // Header card with target info
  if (header) {
    const photo = target.photo1 && !target.photo1.startsWith('default_')
      ? (target.photo1.startsWith('uploads/') ? 'api/' + target.photo1 : 'api/uploads/' + target.photo1)
      : '';
    const avatar = photo
      ? '<img src="' + photo + '" style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb" onerror="this.outerHTML=\'<div class=avatar>' + initials(target.name) + '</div>\'">'
      : '<div class="avatar" style="width:52px;height:52px;font-size:18px">' + initials(target.name) + '</div>';
    header.innerHTML = '<div style="display:flex;align-items:center;gap:14px;padding:14px 18px;background:linear-gradient(135deg,#fff,#faf9f7);border:1px solid var(--border);border-radius:10px;margin-bottom:16px">'
      + avatar
      + '<div style="flex:1;min-width:0"><div style="font-size:15px;font-weight:700">' + (target.name || '-') + '</div>'
      + '<div style="font-size:12px;color:var(--text-secondary);margin-top:2px">'
      + '<code style="font-size:11.5px;background:#f3f4f6;padding:2px 7px;border-radius:5px;margin-right:6px">' + (target.cpId || '-') + '</code>'
      + '📞 ' + (target.mobile || '-') + ' · ' + (target.gender || '-') + ' · ' + (target.age ? target.age + ' yrs' : '-')
      + '</div></div>'
      + '<span class="badge ' + (target.status === 'Approved' ? 'badge-green' : 'badge-amber') + '">' + (target.status || '-') + '</span>'
      + '</div>';
  }

  const targetCpId = target.cpId;
  const targetMobile = target.mobile;

  // ── Profile views received ─────────────────────────────────────────
  const pvList = (profileViewLogs || []).filter(l =>
    (l.target_cp_id && l.target_cp_id === targetCpId) ||
    (l.target_mobile && l.target_mobile === targetMobile)
  );
  pvCount.textContent = pvList.length;
  pvBody.innerHTML = pvList.length === 0
    ? '<tr><td colspan="6"><div class="empty-state"><div class="icon">👁</div><p>No one has viewed this profile yet</p></div></td></tr>'
    : pvList.slice(0, 500).map((l, i) => {
        const planBadge = l.plan === 'paid' ? '<span class="badge badge-blue">Paid</span>'
          : l.plan === 'premium' ? '<span class="badge badge-amber">Premium</span>'
          : '<span class="badge badge-gray">Free</span>';
        return '<tr>'
          + '<td style="font-size:12px;color:var(--text-secondary)">' + (i + 1) + '</td>'
          + '<td style="font-weight:600;font-size:12.5px">' + (l.mobile || 'anonymous') + '</td>'
          + '<td>' + (l.cp_id ? '<code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">' + l.cp_id + '</code>' : '<span style="color:var(--text-secondary);font-size:11.5px">—</span>') + '</td>'
          + '<td style="font-size:12.5px">' + (l.name || '—') + '</td>'
          + '<td>' + planBadge + '</td>'
          + '<td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">' + (l.datetime || '—') + '</td>'
          + '</tr>';
      }).join('');

  // ── Contact views received ─────────────────────────────────────────
  const cvList = (typeof contactViewLog !== 'undefined' ? contactViewLog : []).filter(r =>
    ((r.viewedCpId || r.target_cp_id) === targetCpId) ||
    ((r.viewedMobile || r.target_mobile) === targetMobile)
  );
  cvCount.textContent = cvList.length;
  cvBody.innerHTML = cvList.length === 0
    ? '<tr><td colspan="6"><div class="empty-state"><div class="icon">📞</div><p>No one has revealed this contact yet</p></div></td></tr>'
    : cvList.slice(0, 500).map((r, i) => {
        const viewerMobile = r.viewerMobile || r.mobile || 'anonymous';
        const viewerCpId = r.viewerCpId || r.cp_id || '';
        const viewerName = r.viewerName || r.name || '—';
        const viewerPlan = r.viewerPlan || r.plan || 'free';
        const planBadge = viewerPlan === 'paid' ? '<span class="badge badge-blue">Paid</span>'
          : viewerPlan === 'premium' ? '<span class="badge badge-amber">Premium</span>'
          : '<span class="badge badge-gray">Free</span>';
        return '<tr>'
          + '<td style="font-size:12px;color:var(--text-secondary)">' + (i + 1) + '</td>'
          + '<td style="font-weight:600;font-size:12.5px">' + viewerMobile + '</td>'
          + '<td>' + (viewerCpId ? '<code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">' + viewerCpId + '</code>' : '<span style="color:var(--text-secondary);font-size:11.5px">Visitor</span>') + '</td>'
          + '<td style="font-size:12.5px">' + viewerName + '</td>'
          + '<td>' + planBadge + '</td>'
          + '<td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">' + (r.datetime || '—') + '</td>'
          + '</tr>';
      }).join('');

  // ── Reports against this profile ───────────────────────────────────
  const rptList = (profileReports || []).filter(r =>
    (r.cpId || r.cp_id) === targetCpId ||
    (r.profileMobile || r.profile_mobile) === targetMobile
  );
  rptCount.textContent = rptList.length;
  rptBody.innerHTML = rptList.length === 0
    ? '<tr><td colspan="6"><div class="empty-state"><div class="icon">⚠️</div><p>No reports against this profile</p></div></td></tr>'
    : rptList.map((r, i) => {
        const reporterMobile = r.reporterMobile || r.reporter_mobile || '—';
        const reporterCpId = r.reporterCpId || r.reporter_cp_id || '';
        const statusBadge = r.status === 'resolved' ? '<span class="badge badge-green">Resolved</span>'
          : r.status === 'dismissed' ? '<span class="badge badge-gray">Dismissed</span>'
          : '<span class="badge badge-amber">Pending</span>';
        return '<tr>'
          + '<td style="font-size:12px;color:var(--text-secondary)">' + (i + 1) + '</td>'
          + '<td style="font-weight:600;font-size:12.5px">' + reporterMobile + '</td>'
          + '<td>' + (reporterCpId ? '<code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">' + reporterCpId + '</code>' : '<span style="color:var(--text-secondary);font-size:11.5px">—</span>') + '</td>'
          + '<td style="font-size:12.5px">' + (r.reason || '—') + '</td>'
          + '<td>' + statusBadge + '</td>'
          + '<td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">' + (r.reportedAt || r.reported_at || '—') + '</td>'
          + '</tr>';
      }).join('');
}

// ═══════════════════════════════════════════════════════════════════════════════
// USER ACTIVITY — outgoing actions a member has taken (whose profile they
// viewed, whose contact they revealed, whom they reported). Mirror image of
// User Response; filters the same three log arrays by viewer/reporter side.
// ═══════════════════════════════════════════════════════════════════════════════
async function loadUserActivity() {
  const ready = [];
  if (typeof contactViewLog !== 'undefined' && contactViewLog.length === 0 && typeof loadContactViewLog === 'function') ready.push(loadContactViewLog());
  if (profileViewLogs.length === 0 && typeof loadProfileViewLog === 'function') ready.push(loadProfileViewLog());
  try { await Promise.all(ready); } catch(e) {}
  renderUserActivity();
}

function renderUserActivity() {
  const q = (document.getElementById('uaMobile')?.value || '').trim().toLowerCase();
  const header = document.getElementById('uaHeader');
  const pvBody = document.getElementById('uaProfileViewTable');
  const cvBody = document.getElementById('uaContactViewTable');
  const rptBody = document.getElementById('uaReportTable');
  const pvCount = document.getElementById('uaProfileViewCount');
  const cvCount = document.getElementById('uaContactViewCount');
  const rptCount = document.getElementById('uaReportCount');
  if (!pvBody || !cvBody || !rptBody) return;

  if (!q) {
    if (header) header.innerHTML = '<div style="padding:18px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;text-align:center;color:var(--text-secondary);font-size:13px;margin-bottom:16px">Type a mobile number or CP ID above to see that member\'s outgoing activity.</div>';
    pvBody.innerHTML = cvBody.innerHTML = rptBody.innerHTML = '';
    pvCount.textContent = cvCount.textContent = rptCount.textContent = '0';
    return;
  }

  const target = profiles.find(p => (p.mobile || '').toLowerCase() === q || (p.cpId || '').toLowerCase() === q)
              || profiles.find(p => (p.mobile || '').toLowerCase().includes(q) || (p.cpId || '').toLowerCase().includes(q));

  if (!target) {
    if (header) header.innerHTML = '<div style="padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;color:#991b1b;font-size:13px;margin-bottom:16px">No profile found matching <strong>' + q + '</strong>. (For visitors without a registered profile, activity is tracked by mobile only — the search still works by mobile.)</div>';
    // Continue rendering anyway — the mobile might still match log entries even if no profile row exists
  } else if (header) {
    const photo = target.photo1 && !target.photo1.startsWith('default_')
      ? (target.photo1.startsWith('uploads/') ? 'api/' + target.photo1 : 'api/uploads/' + target.photo1)
      : '';
    const avatar = photo
      ? '<img src="' + photo + '" style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb" onerror="this.outerHTML=\'<div class=avatar>' + initials(target.name) + '</div>\'">'
      : '<div class="avatar" style="width:52px;height:52px;font-size:18px">' + initials(target.name) + '</div>';
    header.innerHTML = '<div style="display:flex;align-items:center;gap:14px;padding:14px 18px;background:linear-gradient(135deg,#fff,#faf9f7);border:1px solid var(--border);border-radius:10px;margin-bottom:16px">'
      + avatar
      + '<div style="flex:1;min-width:0"><div style="font-size:15px;font-weight:700">' + (target.name || '-') + '</div>'
      + '<div style="font-size:12px;color:var(--text-secondary);margin-top:2px">'
      + '<code style="font-size:11.5px;background:#f3f4f6;padding:2px 7px;border-radius:5px;margin-right:6px">' + (target.cpId || '-') + '</code>'
      + '📞 ' + (target.mobile || '-') + ' · ' + (target.gender || '-') + ' · ' + (target.age ? target.age + ' yrs' : '-')
      + '</div></div>'
      + '<span class="badge ' + (target.status === 'Approved' ? 'badge-green' : 'badge-amber') + '">' + (target.status || '-') + '</span>'
      + '</div>';
  }

  const actorMobile = target ? target.mobile : q;
  const actorCpId = target ? target.cpId : '';

  // Look up a profile name by CP ID for each "viewed" row
  const nameByCpId = {};
  profiles.forEach(p => { if (p.cpId) nameByCpId[p.cpId] = p.name; });

  // ── Profiles they viewed ───────────────────────────────────────────
  const pvList = (profileViewLogs || []).filter(l =>
    (l.mobile && l.mobile === actorMobile) ||
    (actorCpId && l.cp_id && l.cp_id === actorCpId)
  );
  pvCount.textContent = pvList.length;
  pvBody.innerHTML = pvList.length === 0
    ? '<tr><td colspan="4"><div class="empty-state"><div class="icon">👁</div><p>This member hasn\'t viewed any profiles yet</p></div></td></tr>'
    : pvList.slice(0, 500).map((l, i) => {
        const targetName = l.target_name || nameByCpId[l.target_cp_id] || '—';
        return '<tr>'
          + '<td style="font-size:12px;color:var(--text-secondary)">' + (i + 1) + '</td>'
          + '<td>' + (l.target_cp_id ? '<code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">' + l.target_cp_id + '</code>' : '<span style="color:var(--text-secondary);font-size:11.5px">—</span>') + '</td>'
          + '<td style="font-size:12.5px">' + targetName + '</td>'
          + '<td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">' + (l.datetime || '—') + '</td>'
          + '</tr>';
      }).join('');

  // ── Contacts they revealed ─────────────────────────────────────────
  const cvList = (typeof contactViewLog !== 'undefined' ? contactViewLog : []).filter(r =>
    ((r.viewerMobile || r.mobile) === actorMobile) ||
    (actorCpId && (r.viewerCpId || r.cp_id) === actorCpId)
  );
  cvCount.textContent = cvList.length;
  cvBody.innerHTML = cvList.length === 0
    ? '<tr><td colspan="5"><div class="empty-state"><div class="icon">📞</div><p>No contact reveals by this member</p></div></td></tr>'
    : cvList.slice(0, 500).map((r, i) => {
        const viewedCpId = r.viewedCpId || r.target_cp_id || '';
        const viewedName = r.viewedName || r.target_name || nameByCpId[viewedCpId] || '—';
        const viewedMob = r.viewedMobile || r.target_mobile || '—';
        return '<tr>'
          + '<td style="font-size:12px;color:var(--text-secondary)">' + (i + 1) + '</td>'
          + '<td>' + (viewedCpId ? '<code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">' + viewedCpId + '</code>' : '<span style="color:var(--text-secondary);font-size:11.5px">—</span>') + '</td>'
          + '<td style="font-size:12.5px">' + viewedName + '</td>'
          + '<td style="font-weight:600;font-size:12.5px">' + viewedMob + '</td>'
          + '<td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">' + (r.datetime || '—') + '</td>'
          + '</tr>';
      }).join('');

  // ── Reports they filed ─────────────────────────────────────────────
  const rptList = (profileReports || []).filter(r =>
    (r.reporterMobile || r.reporter_mobile) === actorMobile ||
    (actorCpId && (r.reporterCpId || r.reporter_cp_id) === actorCpId)
  );
  rptCount.textContent = rptList.length;
  rptBody.innerHTML = rptList.length === 0
    ? '<tr><td colspan="6"><div class="empty-state"><div class="icon">⚠️</div><p>No reports filed by this member</p></div></td></tr>'
    : rptList.map((r, i) => {
        const reportedCpId = r.cpId || r.cp_id || '';
        const reportedName = r.profileName || r.profile_name || nameByCpId[reportedCpId] || '—';
        const statusBadge = r.status === 'resolved' ? '<span class="badge badge-green">Resolved</span>'
          : r.status === 'dismissed' ? '<span class="badge badge-gray">Dismissed</span>'
          : '<span class="badge badge-amber">Pending</span>';
        return '<tr>'
          + '<td style="font-size:12px;color:var(--text-secondary)">' + (i + 1) + '</td>'
          + '<td>' + (reportedCpId ? '<code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">' + reportedCpId + '</code>' : '<span style="color:var(--text-secondary);font-size:11.5px">—</span>') + '</td>'
          + '<td style="font-size:12.5px">' + reportedName + '</td>'
          + '<td style="font-size:12.5px">' + (r.reason || '—') + '</td>'
          + '<td>' + statusBadge + '</td>'
          + '<td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">' + (r.reportedAt || r.reported_at || '—') + '</td>'
          + '</tr>';
      }).join('');
}

function renderReports() {
  const total    = profiles.length;
  const approved = profiles.filter(p=>p.status==='Approved').length;
  const pending  = profiles.filter(p=>p.status==='Preapproved').length;
  const free     = profiles.filter(p=>p.plan==='free').length;
  const paid     = profiles.filter(p=>p.plan==='paid').length;
  const premium  = profiles.filter(p=>p.plan==='premium').length;
  const followDone = followUps.filter(f=>f.type==='paid'||f.type==='not_interested').length;
  const followAll  = followUps.length;
  const followRate = followAll ? Math.round(followDone/followAll*100) : 0;

  // Monthly registrations (last 6 months)
  const monthLabels = [];
  const monthCounts = [];
  for (let i=5; i>=0; i--) {
    const d = new Date(); d.setMonth(d.getMonth()-i);
    const ym = d.toISOString().slice(0,7);
    monthLabels.push(d.toLocaleString('default',{month:'short',year:'2-digit'}));
    monthCounts.push(profiles.filter(p=>p.created&&p.created.startsWith(ym)).length);
  }
  const maxMonth = Math.max(...monthCounts, 1);

  const grid = document.getElementById('reportsGrid');
  grid.innerHTML = `
    <!-- KPI boxes -->
    <div class="chart-card">
      <div class="chart-title">📊 Overview</div>
      <div class="kpi-row">
        <div class="kpi-box"><div class="kval">${total}</div><div class="klbl">Total Members</div></div>
        <div class="kpi-box"><div class="kval" style="color:#16a34a">${approved}</div><div class="klbl">Approved</div></div>
        <div class="kpi-box"><div class="kval" style="color:#d97706">${pending}</div><div class="klbl">Pending</div></div>
        <div class="kpi-box"><div class="kval" style="color:#e8624a">${stories.length}</div><div class="klbl">Successes</div></div>
      </div>
    </div>

    <!-- Plan distribution -->
    <div class="chart-card">
      <div class="chart-title">💳 Plan-wise Billing Summary</div>
      <div class="bar-chart">
        <div class="bar-row">
          <div class="bar-label">Free</div>
          <div class="bar-track"><div class="bar-fill" style="width:${total?Math.round(free/total*100):0}%;background:#9ca3af"></div></div>
          <div class="bar-val">${free}</div>
        </div>
        <div class="bar-row">
          <div class="bar-label">Paid</div>
          <div class="bar-track"><div class="bar-fill" style="width:${total?Math.round(paid/total*100):0}%;background:#2563eb"></div></div>
          <div class="bar-val">${paid}</div>
        </div>
        <div class="bar-row">
          <div class="bar-label">Premium</div>
          <div class="bar-track"><div class="bar-fill" style="width:${total?Math.round(premium/total*100):0}%;background:#d97706"></div></div>
          <div class="bar-val">${premium}</div>
        </div>
      </div>
      <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);font-size:13px;color:var(--text-secondary)">
        Total billed members: <strong style="color:var(--text-primary)">${bills.length}</strong>
      </div>
    </div>

    <!-- Monthly registrations -->
    <div class="chart-card">
      <div class="chart-title">📅 Monthly Registrations (last 6 months)</div>
      <div class="bar-chart">
        ${monthLabels.map((lbl,i)=>`
          <div class="bar-row">
            <div class="bar-label">${lbl}</div>
            <div class="bar-track"><div class="bar-fill" style="width:${Math.round(monthCounts[i]/maxMonth*100)}%;background:#e8624a"></div></div>
            <div class="bar-val">${monthCounts[i]}</div>
          </div>`).join('')}
      </div>
    </div>

    <!-- Follow-up completion rate -->
    <div class="chart-card">
      <div class="chart-title">📞 Follow-up Completion Rate</div>
      <div style="display:flex;align-items:center;gap:28px;margin-top:8px">
        <svg viewBox="0 0 120 120" width="120" height="120">
          <circle cx="60" cy="60" r="50" fill="none" stroke="#f3f4f6" stroke-width="14"/>
          <circle cx="60" cy="60" r="50" fill="none" stroke="#16a34a" stroke-width="14"
            stroke-dasharray="${followRate * 3.14} 314"
            stroke-dashoffset="78.5"
            stroke-linecap="round"
            transform="rotate(-90 60 60)"/>
          <text x="60" y="65" text-anchor="middle" font-size="22" font-weight="700" fill="#1a1a2e">${followRate}%</text>
        </svg>
        <div>
          <div class="donut-legend">
            <div class="legend-item"><div class="legend-dot" style="background:#16a34a"></div> Closed (${followDone})</div>
            <div class="legend-item"><div class="legend-dot" style="background:#f3f4f6;border:1.5px solid #ddd"></div> Active (${followAll-followDone})</div>
          </div>
          <div style="margin-top:12px;font-size:12.5px;color:var(--text-secondary)">Total follow-ups: <strong>${followAll}</strong></div>
        </div>
      </div>
    </div>
  `;
}

// ──────────────────────────────────────────────
// NOTIFICATIONS
// ──────────────────────────────────────────────
notifications = [
  { icon:'👥', bg:'#eff6ff', title:'New member registered', desc:'CP1004 Anitha R joined today.', time:'Just now', unread:true },
  { icon:'💳', bg:'#f0fdf4', title:'Billing updated', desc:'CP1002 Ravi Shankar upgraded to Paid plan.', time:'2 hours ago', unread:true },
  { icon:'📞', bg:'#fffbeb', title:'Follow-up due today', desc:'3 follow-ups are scheduled for today.', time:'This morning', unread:false },
  { icon:'✅', bg:'#f0fdf4', title:'Profile approved', desc:'CP1003 Priya Devi has been approved.', time:'Yesterday', unread:false },
];

function pushNotif(icon, desc) {
  notifications.unshift({ icon, bg:'#fdf1ee', title: icon.replace(/\p{Emoji}/gu,'').trim() || 'Update', desc, time:'Just now', unread:true });
  saveState();
}

function renderNotifications() {
  const list = document.getElementById('notifList');
  const unreadCount = notifications.filter(n=>n.unread).length;
  list.innerHTML = notifications.length === 0
    ? `<div class="empty-state"><div class="icon">🔔</div><p>No notifications</p></div>`
    : notifications.map((n,i) => `
      <div class="notif-item ${n.unread?'unread':''}" onclick="markRead(${i})">
        <div class="notif-icon" style="background:${n.bg}">${n.icon}</div>
        <div class="notif-body">
          <div class="notif-title">${n.title}</div>
          <div class="notif-desc">${n.desc}</div>
          <div class="notif-time">${n.time}</div>
        </div>
        ${n.unread ? '<div class="notif-dot"></div>' : ''}
      </div>`).join('');
  // Update sidebar badge
  const badge = document.getElementById('notifBadge');
  if (badge) badge.textContent = unreadCount > 0 ? unreadCount : '';
}

function markRead(i) {
  notifications[i].unread = false;
  renderNotifications();
  saveState();
}

function markAllRead() {
  notifications.forEach(n => n.unread = false);
  renderNotifications();
  saveState();
  toast('All notifications marked as read');
}

// ──────────────────────────────────────────────
// SUCCESS STORIES
// ──────────────────────────────────────────────
stories = [
  { groom:'Ravi Kumar (CP1002)', bride:'Priya Devi (CP1003)', date:'2024-11-15', quote:'We found each other through this platform and could not be happier. Thank you!' },
  { groom:'Suresh M (CP1008)',  bride:'Kavitha R (CP1011)',  date:'2024-09-22', quote:'A wonderful experience. The team was very helpful throughout the process.' },
];

function renderStories() {
  const grid = document.getElementById('storiesGrid');
  grid.innerHTML = stories.length === 0
    ? `<div class="empty-state" style="grid-column:1/-1"><div class="icon">❤️</div><p>No success stories yet</p></div>`
    : stories.map((s,i) => `
      <div class="story-card">
        <div class="story-couple">${s.groom.split('(')[0].trim()} ❤️ ${s.bride.split('(')[0].trim()}</div>
        <div class="story-date">💍 Married on ${s.date}</div>
        <div class="story-quote">"${s.quote}"</div>
        <div class="story-ids">
          <span class="badge badge-blue" style="font-size:11px">${s.groom.match(/\(([^)]+)\)/)?.[1]||''}</span>
          <span class="badge badge-amber" style="font-size:11px">${s.bride.match(/\(([^)]+)\)/)?.[1]||''}</span>
        </div>
        <button class="btn btn-danger btn-sm" style="margin-top:12px;float:right" onclick="deleteStory(${i})">Remove</button>
      </div>`).join('');
}

function openAddStory() { openModal('addStoryOverlay'); }
function saveStory() {
  const groom = document.getElementById('st_groom').value.trim();
  const bride = document.getElementById('st_bride').value.trim();
  const date  = document.getElementById('st_date').value;
  const quote = document.getElementById('st_quote').value.trim();
  if (!groom || !bride || !date) { toast('Please fill Groom, Bride and Date', 'error'); return; }
  stories.push({ groom, bride, date, quote: quote || 'A beautiful love story.' });
  closeModal('addStoryOverlay');
  renderStories();
  pushAdminLog('Added Success Story', groom.split('(')[0].trim() + ' ❤️ ' + bride.split('(')[0].trim() + ' · ' + date, 'story');
  saveState();
  toast('Success story added ❤️');
}

function deleteStory(i) {
  if (!confirm('Remove this success story?')) return;
  pushAdminLog('Removed Story', stories[i].groom.split('(')[0].trim() + ' ❤️ ' + stories[i].bride.split('(')[0].trim(), 'story');
  stories.splice(i, 1);
  renderStories();
  saveState();
  toast('Story removed');
}

// ──────────────────────────────────────────────
// POST-RENDER: add Print buttons + apply filters
// ──────────────────────────────────────────────
function postRender() {
  applyProfileFilter();
  applyManageFilter();
  applyDeletedFilter();
  // Add Print button to each profile table row
  document.querySelectorAll('#profileTable tr').forEach((row) => {
    const actCell = row.querySelector('.actions');
    if (actCell && !actCell.querySelector('.print-btn')) {
      const cpid = row.querySelector('code')?.textContent;
      const pi   = profiles.findIndex(p => p.cpId === cpid);
      if (pi < 0) return;
      const pb = document.createElement('button');
      pb.className = 'btn btn-outline btn-sm print-btn';
      pb.innerHTML = '🖨️ Print';
      pb.onclick = () => printProfile(pi);
      actCell.appendChild(pb);
    }
  });
}

// ──────────────────────────────────────────────
// CUSTOM / SUBSCRIPTION PLANS
// ──────────────────────────────────────────────
customPlans = [
  { name:"Free Registration", type:"free",    amount:0,    validity:180, desc:"Basic profile listing — admin review required for activation", createdBy:"Balasubramanian R", status:"active", createdDate:"2024-01-01" },
  { name:"Gold Paid Plan",    type:"paid",    amount:2500, validity:365, desc:"1-year profile with enhanced visibility and priority matching",  createdBy:"Balasubramanian R", status:"active", createdDate:"2024-01-01" },
  { name:"Premium Annual",    type:"premium", amount:4500, validity:365, desc:"Full-year premium access with top listing and dedicated support", createdBy:"Balasubramanian R", status:"active", createdDate:"2024-01-01" },
  { name:"Silver Basic",      type:"basic",   amount:1500, validity:180, desc:"6-month listing with standard features",                         createdBy:"Ravi Kumar",        status:"active", createdDate:"2024-01-05" },
  { name:"VIP Lifetime",      type:"vip",     amount:9999, validity:1825,desc:"5-year VIP membership with maximum visibility and support",      createdBy:"Balasubramanian R", status:"active", createdDate:"2024-01-10" },
];
planHistory  = [
  { name:"Free Registration", type:"free",    amount:0,    validity:180, desc:"Basic profile listing", createdBy:"Balasubramanian R", status:"active", createdDate:"2024-01-01", action:"Created", recordedAt:"2024-01-01 09:00:00", recordedBy:"Balasubramanian R" },
  { name:"Gold Paid Plan",    type:"paid",    amount:2500, validity:365, desc:"1-year profile",        createdBy:"Balasubramanian R", status:"active", createdDate:"2024-01-01", action:"Created", recordedAt:"2024-01-01 09:05:00", recordedBy:"Balasubramanian R" },
  { name:"Premium Annual",    type:"premium", amount:4500, validity:365, desc:"Full-year premium",     createdBy:"Balasubramanian R", status:"active", createdDate:"2024-01-01", action:"Created", recordedAt:"2024-01-01 09:10:00", recordedBy:"Balasubramanian R" },
];
paymentOptions = [
  { id:"PAY_001", method:"upi",    label:"GPay / UPI",    details:{ upiId:"matrimony.pondicherry@hdfcbank", upiApp:"GPay"     }, notes:"Add your CP ID in payment remarks", status:"active", createdAt:"2024-01-01 09:00:00", updatedAt:"2024-01-01 09:00:00", createdBy:"Balasubramanian R" },
  { id:"PAY_002", method:"bank",   label:"Bank Transfer", details:{ accountName:"Pondicherry Matrimony Services", accountNo:"15855555541111", ifsc:"HDFC0001234", accountType:"Current", bankName:"HDFC Bank", branch:"Pondicherry" }, notes:"Add CP ID in description/remarks field", status:"active", createdAt:"2024-01-01 09:05:00", updatedAt:"2024-01-01 09:05:00", createdBy:"Balasubramanian R" },
  { id:"PAY_003", method:"mobile", label:"PhonePe",       details:{ mobileNo:"9876543210", holderName:"Balasubramanian R", upiApp:"PhonePe" }, notes:"Mention your name and CP ID while paying", status:"active", createdAt:"2024-01-01 09:10:00", updatedAt:"2024-01-01 09:10:00", createdBy:"Balasubramanian R" },
];

// ── USER PANEL CONTROL ──────────────────────────────────────
// Defines what pages/features each member sees in the user panel
const UP_PAGES = [
  { id:'page_profile',      label:'My Profile',      icon:'👤', desc:'View profile details page' },
  { id:'page_bills',        label:'My Bills',         icon:'💳', desc:'Bill history and active plan' },
  { id:'page_addorder',     label:'Add Order',        icon:'🛒', desc:'Place new subscription order' },
  { id:'page_activity',     label:'My Activity',      icon:'📊', desc:'Profile views and contact requests' },
  { id:'page_loginhistory', label:'Login History',    icon:'🔐', desc:'OTP log and session history' },
  { id:'page_settings',     label:'Settings',         icon:'⚙️', desc:'Mobile number change requests' },
];
const UP_FEATURES = [
  { id:'feat_create_profile',label:'Create New Profile', icon:'➕', desc:'Allow unregistered users to create profile' },
  { id:'feat_edit_profile',  label:'Edit Profile',       icon:'✏️', desc:'Allow members to edit their own profile' },
  { id:'feat_delete_profile',label:'Delete Profile',     icon:'🗑', desc:'Allow members to delete their profile' },
  { id:'feat_pay_now',       label:'Pay Now / Upgrade',  icon:'💰', desc:'Show payment upgrade button on free profiles' },
  { id:'feat_view_contact',  label:'View Contact Info',  icon:'📞', desc:'Allow viewing other profiles contact details' },
  { id:'feat_view_bill',     label:'View Bill Details',  icon:'👁', desc:'Show full bill details in profile' },
  { id:'feat_req_mobile',    label:'Request Mobile Change',icon:'📱',desc:'Allow mobile number change requests' },
  { id:'feat_print_profile', label:'Print Profile',      icon:'🖨', desc:'Allow printing profile page' },
  { id:'feat_sign_out',      label:'Sign Out',           icon:'🚪', desc:'Show sign out option in sidebar' },
];

// Default: all pages/features shown for everyone
const UP_DEFAULT_GLOBAL = Object.fromEntries(
  [...UP_PAGES, ...UP_FEATURES].map(i => [i.id, true])
);

userPanelControl = {
  global:    { ...UP_DEFAULT_GLOBAL },
  overrides: [],
};
upCtrlHistory   = []; // permanent audit trail of every user panel control change
editOverrideIdx = null;

// loadUserPanelControl defined above

function saveUserPanelControlStore() { /* no-op: replaced by API */ }

function renderUserCtrlPanel() {
  loadUserPanelControl();
  renderGlobalToggles();
  renderUserOverrideTable();
}

function makeToggleRow(id, label, icon, desc, checked, onChangeFn) {
  const uid = 'toggle_' + id;
  return `
    <div class="up-toggle-row" id="trow_${id}">
      <div>
        <div class="up-toggle-label">${icon} ${label}</div>
        ${desc ? `<div class="up-toggle-sub">${desc}</div>` : ''}
      </div>
      <label class="toggle-switch" title="${checked?'Visible — click to hide':'Hidden — click to show'}">
        <input type="checkbox" id="${uid}" ${checked?'checked':''} onchange="${onChangeFn}">
        <div class="toggle-track"></div>
        <div class="toggle-knob"></div>
      </label>
    </div>`;
}

function renderGlobalToggles() {
  const pg = document.getElementById('globalPageToggles');
  const ft = document.getElementById('globalFeatureToggles');
  if (!pg || !ft) return;
  const g = userPanelControl.global || {};
  pg.innerHTML = UP_PAGES.map(p =>
    makeToggleRow(p.id, p.label, p.icon, p.desc,
      g[p.id] !== false,
      `toggleGlobalUP('${p.id}',this)`)
  ).join('');
  ft.innerHTML = UP_FEATURES.map(f =>
    makeToggleRow(f.id, f.label, f.icon, f.desc,
      g[f.id] !== false,
      `toggleGlobalUP('${f.id}',this)`)
  ).join('');
}

function toggleGlobalUP(id, el) {
  if (!userPanelControl.global) userPanelControl.global = {};
  userPanelControl.global[id] = el.checked;
}

function saveUserPanelControl() {
  // Record which pages/features changed
  const hiddenPages = [...UP_PAGES, ...UP_FEATURES]
    .filter(i => (userPanelControl.global || {})[i.id] === false)
    .map(i => i.icon + ' ' + i.label);
  const detail = hiddenPages.length
    ? 'Hidden: ' + hiddenPages.join(', ')
    : 'All pages/features visible';
  pushUPCtrlHistory('Global Save', '🌐 All Users', detail);
  saveUserPanelControlStore();
  /* persisted via API */
  pushAdminLog('Updated User Panel Control', detail, 'setting');
  saveState();
  renderUPCtrlHistory();
  toast('✅ User panel control settings saved');
}

function pushUPCtrlHistory(action, scope, detail) {
  upCtrlHistory.unshift({
    action, scope, detail,
    changedBy:  getActiveAdminName(),
    recordedAt: nowStamp(),
  });
  if (upCtrlHistory.length > 500) upCtrlHistory.length = 500;
}

// ── Pagination helper ──────────────────────────────────────

function renderUPCtrlHistory() {
  const tbody   = document.getElementById('upCtrlHistTable');
  const countEl = document.getElementById('upHistCount');
  const pageInfo= document.getElementById('upHistPageInfo');
  const pageBtns= document.getElementById('upHistPageBtns');
  if (!tbody) return;

  const q    = (document.getElementById('upHistSearch')?.value   || '').toLowerCase();
  const type = (document.getElementById('upHistTypeFilter')?.value|| '');
  let rows = upCtrlHistory.filter(r =>
    (!q    || (r.changedBy||'').toLowerCase().includes(q) || (r.scope||'').toLowerCase().includes(q) || (r.detail||'').toLowerCase().includes(q)) &&
    (!type || r.action === type)
  );

  if (countEl) countEl.textContent = upCtrlHistory.length + ' records';
  if (rows.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><div class="icon">📋</div><p>No history yet. Every save and override change will be recorded here.</p></div></td></tr>`;
    if (pageInfo) pageInfo.textContent = '';
    if (pageBtns) pageBtns.innerHTML = '';
    return;
  }

  const totalPages = Math.ceil(rows.length / UP_HIST_PER_PAGE);
  if (_upHistPage > totalPages) _upHistPage = totalPages;
  const start = (_upHistPage - 1) * UP_HIST_PER_PAGE;
  const slice = rows.slice(start, start + UP_HIST_PER_PAGE);

  const actionBadge = a => {
    const colors = {
      'Global Save':      'badge-blue',
      'Override Added':   'badge-green',
      'Override Updated': 'badge-amber',
      'Override Removed': 'badge-gray',
    };
    return `<span class="badge ${colors[a]||'badge-gray'}">${a}</span>`;
  };

  tbody.innerHTML = slice.map((r, i) => `
    <tr>
      <td style="font-size:12px;color:var(--text-secondary)">${start+i+1}</td>
      <td>${actionBadge(r.action)}</td>
      <td style="font-size:12.5px;font-weight:600">${r.scope||'—'}</td>
      <td style="font-size:12px;color:var(--text-secondary);max-width:260px;white-space:normal;line-height:1.5">${r.detail||'—'}</td>
      <td>
        <div class="name-cell">
          <div class="avatar" style="width:22px;height:22px;font-size:9px">${initials(r.changedBy||'?')}</div>
          <span style="font-size:12px">${r.changedBy||'—'}</span>
        </div>
      </td>
      <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">${r.recordedAt||'—'}</td>
    </tr>`).join('');

  if (pageInfo) pageInfo.textContent = `Showing ${start+1}–${Math.min(start+UP_HIST_PER_PAGE,rows.length)} of ${rows.length}`;
  renderPagination('upHistPageBtns', _upHistPage, totalPages, p => { _upHistPage=p; renderUPCtrlHistory(); });
}

function clearUPCtrlHistory() {
  if (!confirm('Clear all User Panel Control history? This cannot be undone.')) return;
  upCtrlHistory = [];
  /* persisted via API */
  renderUPCtrlHistory();
  toast('History cleared');
}

// ── PAGINATION STATE ─────────────────────────────────────────

// Attach a pagination bar after a table's .table-wrap parent
function attachPagination(tableId, barId) {
  const tableWrap = document.getElementById(tableId)?.closest('.table-wrap');
  if (!tableWrap) return;
  let bar = document.getElementById(barId);
  if (!bar) {
    bar = document.createElement('div');
    bar.id = barId;
    bar.style.cssText = 'padding:9px 18px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:#faf9f7;flex-wrap:wrap;gap:6px';
    tableWrap.insertAdjacentElement('afterend', bar);
  }
}

// Paginate an array and render rows into a tbody; attach pagination bar
function paginate(allRows, page, perPage, tbodyId, barId, emptyHtml) {
  const tbody = document.getElementById(tbodyId);
  if (!tbody) return;
  if (allRows.length === 0) { tbody.innerHTML = emptyHtml; paginationBar(barId,'',''); return; }
  const total = Math.ceil(allRows.length / perPage);
  const cur   = Math.max(1, Math.min(page, total));
  const start = (cur - 1) * perPage;
  const slice = allRows.slice(start, start + perPage);
  tbody.innerHTML = slice.join('');
  const info = `Showing ${start+1}–${Math.min(start+perPage,allRows.length)} of ${allRows.length}`;
  paginationBar(barId, info, cur, total);
  return cur;
}

function paginationBar(barId, info, cur, total) {
  const bar = document.getElementById(barId);
  if (!bar) return;
  if (!total || total <= 1) { bar.innerHTML = info ? `<span style="font-size:12px;color:var(--text-secondary)">${info}</span>` : ''; return; }
  const callbacks = {
    profile: p=>{_pg.profile=p;applyProfileFilter();},
    manage:  p=>{_pg.manage=p;applyManageFilter();},
    bill:    p=>{_pg.bill=p;renderBills();},
    billHistory:p=>{_pg.billHistory=p;renderBillHistory();},
    otp:     p=>{_pg.otp=p;renderOtp();},
    adminLog:p=>{_pg.adminLog=p;renderAdminLog();},
    deleted: p=>{_pg.deleted=p;applyDeletedFilter();},
    actionLog:p=>{_pg.actionLog=p;applyDeletedFilter();},
    admin:   p=>{_pg.admin=p;renderAdmins();},
  };
  const key = barId.replace('Pg','');
  const fn  = callbacks[key] || (p=>{});
  bar.innerHTML = `
    <span style="font-size:12px;color:var(--text-secondary)">${info}</span>
    <div style="display:flex;gap:5px" id="${barId}_btns"></div>`;
  renderPagination(barId+'_btns', cur, total, fn);
}

// Ensure pagination bars exist next to all key tables
function initPaginationBars() {
  [
    ['profileTable','profilePg'],['manageTable','managePg'],
    ['billTable','billPg'],['billHistoryTable','billHistoryPg'],
    ['otpTable','otpPg'],['adminLogTable','adminLogPg'],
    ['deletedTable','deletedPg'],['actionLogTable','actionLogPg'],
    ['adminTable','adminPg'],
  ].forEach(([tid,bid])=>attachPagination(tid,bid));
}

function renderPagination(containerId, currentPage, totalPages, onPageClick) {
  const container = document.getElementById(containerId);
  if (!container) return;
  if (totalPages <= 1) { container.innerHTML=''; return; }
  let html = '';
  html += `<button class="pg-btn" ${currentPage===1?'disabled':''} onclick="(${onPageClick})(${currentPage-1})">‹</button>`;
  const range = buildPageRange(currentPage, totalPages);
  range.forEach(p => {
    if (p === '...') {
      html += `<span style="padding:4px 6px;font-size:12px;color:var(--text-secondary)">…</span>`;
    } else {
      html += `<button class="pg-btn ${p===currentPage?'active':''}" onclick="(${onPageClick})(${p})">${p}</button>`;
    }
  });
  html += `<button class="pg-btn" ${currentPage===totalPages?'disabled':''} onclick="(${onPageClick})(${currentPage+1})">›</button>`;
  container.innerHTML = html;
}

function buildPageRange(cur, total) {
  if (total <= 7) return Array.from({length:total},(_,i)=>i+1);
  const pages = [];
  pages.push(1);
  if (cur > 3) pages.push('...');
  for (let p = Math.max(2,cur-1); p <= Math.min(total-1,cur+1); p++) pages.push(p);
  if (cur < total-2) pages.push('...');
  pages.push(total);
  return pages;
}

// ── PER-USER OVERRIDE ────────────────────────────────────────
function openAddUserOverride() {
  editOverrideIdx = null;
  document.getElementById('overrideFormTitle').textContent = '➕ Add User Override';
  document.getElementById('uov_cpid').value = '';
  document.getElementById('uov_name').value = '';
  renderOverrideToggles(null);
  document.getElementById('userOverrideForm').style.display = '';
  document.getElementById('uov_cpid').focus();
}

function closeUserOverrideForm() {
  document.getElementById('userOverrideForm').style.display = 'none';
  editOverrideIdx = null;
}

function lookupOverrideUser(val) {
  val = val.trim();
  // Search profiles first
  const p = profiles.find(p => (p.cpId && p.cpId.toLowerCase() === val.toLowerCase()) || p.mobile === val);
  if (p) { document.getElementById('uov_name').value = p.name; return; }
  // Search OTP logs (users who logged in but may not have a profile)
  const o = otpLogs.find(o => o.mobile === val);
  if (o && o.name) { document.getElementById('uov_name').value = o.name + ' (no profile)'; return; }
  // Valid mobile but not found in any table — still allow override
  if (/^\d{10}$/.test(val)) { document.getElementById('uov_name').value = 'Mobile user (no profile)'; return; }
  document.getElementById('uov_name').value = val ? '— Not found' : '';
}

function renderOverrideToggles(overridePages) {
  // overridePages = null (new) → inherit global; or existing {page_id: bool} map
  const container = document.getElementById('userOverrideToggles');
  if (!container) return;
  const allItems = [...UP_PAGES, ...UP_FEATURES];
  container.innerHTML = allItems.map(item => {
    const global  = (userPanelControl.global || {})[item.id] !== false;
    const checked = overridePages ? (overridePages[item.id] !== false) : global;
    return `
      <label class="up-toggle-row" style="cursor:pointer;margin:0">
        <div>
          <div class="up-toggle-label" style="font-size:12.5px">${item.icon} ${item.label}</div>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" id="uov_${item.id}" ${checked?'checked':''}>
          <div class="toggle-track"></div>
          <div class="toggle-knob"></div>
        </label>
      </label>`;
  }).join('');
}

function saveUserOverride() {
  const raw    = document.getElementById('uov_cpid').value.trim();
  const nameEl = document.getElementById('uov_name').value.trim();
  if (!raw) { toast('Enter mobile number or CP ID', 'error'); return; }

  // Resolve mobile number — accept mobile directly or lookup from CP ID
  let mobile = raw, name = nameEl;
  const p = profiles.find(pr => (pr.cpId && pr.cpId.toLowerCase() === raw.toLowerCase()) || pr.mobile === raw);
  if (p) { mobile = p.mobile; name = p.name; }
  else if (!/^\d{10}$/.test(raw)) { toast('Enter a valid 10-digit mobile number', 'error'); return; }

  const pages = {};
  [...UP_PAGES, ...UP_FEATURES].forEach(item => {
    const el = document.getElementById('uov_' + item.id);
    if (el) pages[item.id] = el.checked;
  });

  const cpId = p ? p.cpId : '';
  const displayName = name && !name.startsWith('—') ? name : mobile;

  const entry = {
    cpId,
    mobile,
    name: displayName,
    pages,
    setBy:  getActiveAdminName(),
    setAt:  nowStamp(),
  };

  if (editOverrideIdx !== null) {
    userPanelControl.overrides[editOverrideIdx] = entry;
    toast('Override updated for ' + displayName);
  } else {
    userPanelControl.overrides = userPanelControl.overrides.filter(o => o.mobile !== mobile);
    userPanelControl.overrides.push(entry);
    toast('Override added for ' + displayName);
  }
  const hiddenItems = [...UP_PAGES,...UP_FEATURES].filter(i=>pages[i.id]===false).map(i=>i.icon+' '+i.label);
  const histDetail  = hiddenItems.length ? 'Hidden: '+hiddenItems.join(', ') : 'All visible';
  pushUPCtrlHistory(editOverrideIdx!==null?'Override Updated':'Override Added', '👤 '+displayName+' ('+mobile+')', histDetail);
  pushAdminLog('User Panel Override', displayName + ' · ' + mobile, 'setting');
  saveUserPanelControlStore();
  saveState();
  closeUserOverrideForm();
  renderUserOverrideTable();
  renderUPCtrlHistory();
}

function editUserOverride(i) {
  editOverrideIdx = i;
  const ov = userPanelControl.overrides[i];
  document.getElementById('overrideFormTitle').textContent = '✏️ Edit Override — ' + (ov.name || ov.mobile);
  document.getElementById('uov_cpid').value = ov.mobile || ov.cpId;
  document.getElementById('uov_name').value = ov.name || '';
  renderOverrideToggles(ov.pages || {});
  document.getElementById('userOverrideForm').style.display = '';
  document.getElementById('userOverrideForm').scrollIntoView({ behavior:'smooth', block:'nearest' });
}

function deleteUserOverride(i) {
  const ov = userPanelControl.overrides[i];
  if (!confirm('Remove override for ' + ov.name + '?\nThey will revert to global settings.')) return;
  pushUPCtrlHistory('Override Removed', '👤 '+ov.name+' ('+ov.cpId+')', 'Override removed — reverts to global settings');
  /* persisted via API */
  userPanelControl.overrides.splice(i, 1);
  saveUserPanelControlStore();
  renderUserOverrideTable();
  renderUPCtrlHistory();
  toast('Override removed — ' + ov.name + ' now uses global settings');
}

function renderUserOverrideTable() {
  const tbody = document.getElementById('userOverrideTable');
  if (!tbody) return;
  if (!userPanelControl.overrides.length) {
    tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state">
      <div class="icon">👤</div>
      <p>No per-user overrides yet.<br>Click <strong>+ Add Override</strong> to customise a specific member's panel.</p>
    </div></td></tr>`;
    return;
  }
  const allItems = [...UP_PAGES, ...UP_FEATURES];
  tbody.innerHTML = userPanelControl.overrides.map((ov, i) => {
    const hiddenCount  = allItems.filter(item => ov.pages[item.id] === false).length;
    const visibleCount = allItems.length - hiddenCount;
    const enabledBadges = allItems.filter(item => ov.pages[item.id] !== false)
      .map(item => `<span class="ov-page-badge">${item.icon}</span>`).join('');
    const disabledBadges = allItems.filter(item => ov.pages[item.id] === false)
      .map(item => `<span class="ov-page-badge hidden">${item.icon}</span>`).join('');
    return `<tr>
      <td>
        <div class="name-cell">
          <div class="avatar" style="width:26px;height:26px;font-size:10px">${initials(ov.name)}</div>
          <div><div style="font-size:13px;font-weight:600">${ov.name}</div><div style="font-size:11px;color:var(--text-secondary)">${ov.mobile}</div></div>
        </div>
      </td>
      <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${ov.mobile || ov.cpId}</code></td>
      <td>
        <div style="display:flex;flex-wrap:wrap;gap:3px;align-items:center">${enabledBadges}${disabledBadges}</div>
        <div style="font-size:10px;color:var(--text-secondary);margin-top:3px">${visibleCount} on · ${hiddenCount} off</div>
      </td>
      <td>
        <div style="font-size:12px">${ov.setBy||'—'}</div>
        <div style="font-size:11px;color:var(--text-secondary)">${ov.setAt||''}</div>
      </td>
      <td>
        <div class="actions">
          <button class="btn btn-outline btn-sm" onclick="editUserOverride(${i})">✏️ Edit</button>
          <button class="btn btn-danger btn-sm" onclick="deleteUserOverride(${i})">Remove</button>
        </div>
      </td>
    </tr>`;
  }).join('');
}
editPayOptIdx  = null;
editPlanIdx = null;

const PLAN_TYPE_LABELS = {
  free:'🔓 Free', basic:'📄 Basic', paid:'💎 Paid',
  premium:'⭐ Premium', vip:'👑 VIP', custom:'🔧 Custom'
};
const PAYMENT_TYPE_LABELS = {
  free:'🆓 Free', cash:'💵 Cash', upi:'📲 UPI',
  qr:'🔳 QR Code', pay_link:'🔗 Pay Link',
  payment_gateway:'🏦 Payment Gateway', others:'🔹 Others',
  online:'🌐 Online'
};

function planTypeBadge(t) {
  const colors = {
    free:    {bg:'#f3f4f6', color:'#6b7280'},
    basic:   {bg:'#eff6ff', color:'#2563eb'},
    paid:    {bg:'#eff6ff', color:'#2563eb'},
    premium: {bg:'#fffbeb', color:'#d97706'},
    vip:     {bg:'#fdf4ff', color:'#9333ea'},
    custom:  {bg:'#f0fdf4', color:'#16a34a'},
  };
  const c = colors[t] || colors.custom;
  return `<span class="badge" style="background:${c.bg};color:${c.color}">${PLAN_TYPE_LABELS[t]||t}</span>`;
}

function payTypeBadge(t) {
  const bgs = {
    free:'#f0fdf4', cash:'#fffbeb', upi:'#eff6ff',
    qr:'#fdf4ff', pay_link:'#f0fdf4', payment_gateway:'#eff6ff',
    others:'#f3f4f6', online:'#eff6ff'
  };
  return `<span class="badge" style="background:${bgs[t]||'#f3f4f6'};color:var(--text-primary)">${PAYMENT_TYPE_LABELS[t]||t}</span>`;
}

function setValidity(days) {
  document.getElementById('cp_validity').value = days;
  document.querySelectorAll('.sp-preset').forEach(b => {
    b.classList.toggle('active', parseInt(b.textContent) === days || b.textContent.includes('year') && days===365 || b.textContent.includes('month') && days===180);
  });
}

function resetPlanForm() {
  editPlanIdx = null;
  ['cp_name','cp_desc'].forEach(id => document.getElementById(id).value = '');
  ['cp_type'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('cp_amount').value   = '';
  document.getElementById('cp_validity').value = '';
  document.getElementById('cp_status').value   = 'active';
  document.querySelectorAll('.sp-preset').forEach(b => b.classList.remove('active'));
  document.getElementById('spFormIcon').textContent   = '➕';
  document.getElementById('spFormTitle').textContent  = 'Create Subscription Plan';
  document.getElementById('cpSaveTxt').textContent    = 'Create Plan';
  document.getElementById('cpCancelBtn').style.display = 'none';
  // Auto-fill Created By from logged-in admin (loginAdminObj), first active admin, or sidebar
  const sidebarName = document.getElementById('sidebarAdminName')?.textContent?.trim() || '';
  const adminName = (loginAdminObj && loginAdminObj.name)
    ? loginAdminObj.name
    : (admins.find(a => a.status === 'active')?.name
      || (sidebarName && sidebarName !== 'Admin' ? sidebarName : '')
      || 'Admin');
  document.getElementById('cp_createdby').value = adminName;
}

function openCreatePlan() { resetPlanForm(); }

function openEditPlan(i) {
  editPlanIdx = i;
  const p = customPlans[i];
  document.getElementById('cp_name').value      = p.name;
  document.getElementById('cp_type').value      = p.type;
  document.getElementById('cp_amount').value    = p.amount;
  document.getElementById('cp_validity').value  = p.validity;
  document.getElementById('cp_desc').value      = p.desc || '';
  document.getElementById('cp_createdby').value = p.createdBy;
  document.getElementById('cp_status').value    = p.status;
  document.getElementById('spFormIcon').textContent    = '✏️';
  document.getElementById('spFormTitle').textContent   = 'Edit Plan — ' + p.name;
  document.getElementById('cpSaveTxt').textContent     = 'Save Changes';
  document.getElementById('cpCancelBtn').style.display = '';
  document.querySelectorAll('.sp-preset').forEach(b => b.classList.remove('active'));
  const presets = {30:true,60:true,90:true,180:true,365:true};
  if (presets[p.validity]) setValidity(p.validity);
  document.getElementById('cp_name').scrollIntoView({behavior:'smooth', block:'center'});
  document.getElementById('cp_name').focus();
}

function cancelEditPlan() { resetPlanForm(); }

function saveCustomPlan() {
  const name      = document.getElementById('cp_name').value.trim();
  const type      = document.getElementById('cp_type').value;
  const amount    = document.getElementById('cp_amount').value;
  const validity  = document.getElementById('cp_validity').value;
  const desc      = document.getElementById('cp_desc').value.trim();
  const createdBy = document.getElementById('cp_createdby').value.trim();
  const status    = document.getElementById('cp_status').value;

  if (!name)                   { toast('Plan name is required', 'error');              return; }
  if (!type)                   { toast('Please select a plan type', 'error');          return; }
  if (amount === '')           { toast('Enter an amount (0 for Free)', 'error');       return; }
  if (!validity || validity<1) { toast('Enter validity in days (min 1)', 'error');     return; }
  if (!desc)                   { toast('Description is required', 'error');            return; }
  if (!createdBy)              { toast('Created By admin name is required', 'error'); return; }

  const planObj = {
    name, type,
    amount:   parseFloat(amount),
    validity: parseInt(validity),
    desc, createdBy, status,
    createdDate: editPlanIdx !== null
      ? customPlans[editPlanIdx].createdDate
      : new Date().toISOString().split('T')[0]
  };

  // Record in plan history (permanent audit trail)
  const histEntry = {
    ...planObj,
    action:     editPlanIdx !== null ? 'Updated' : 'Created',
    recordedAt: nowStamp(),
    recordedBy: createdBy,
    planIdx:    editPlanIdx !== null ? editPlanIdx : customPlans.length,
  };
  planHistory.push(histEntry);
  savePlanHistory();

  if (editPlanIdx !== null) {
    customPlans[editPlanIdx] = planObj;
    pushAdminLog('Updated Plan', name + ' · ₹' + amount + ' · ' + validity + 'd', 'plan');
    toast('✅ Plan updated successfully');
  } else {
    customPlans.push(planObj);
    pushAdminLog('Created Plan', name + ' · ₹' + amount + ' · ' + validity + 'd', 'plan');
    toast('✅ Subscription plan created');
  }

  resetPlanForm();
  renderCustomPlans();
  renderPlanHistory();
  saveState();
}

function deleteCustomPlan(i) {
  if (!confirm(`Delete plan "${customPlans[i].name}"?\nThis cannot be undone.`)) return;
  pushAdminLog('Deleted Plan', customPlans[i].name, 'plan');
  customPlans.splice(i, 1);
  renderCustomPlans();
  saveState();
  toast('Plan deleted');
}

// ──────────────────────────────────────────────────────────
// PLAN UPDATE HISTORY (permanent audit trail)
// ──────────────────────────────────────────────────────────
function savePlanHistory() { /* no-op: replaced by API */ }

function renderPlanHistory() {
  const tbody   = document.getElementById('planHistoryTable');
  const countEl = document.getElementById('planHistCount');
  if (!tbody) return;

  const q      = (document.getElementById('phSearch')?.value || '').toLowerCase();
  const action = document.getElementById('phActionFilter')?.value || '';

  let rows = [...planHistory].reverse(); // newest first
  if (q)      rows = rows.filter(r => (r.name||'').toLowerCase().includes(q) || (r.recordedBy||'').toLowerCase().includes(q));
  if (action) rows = rows.filter(r => r.action === action);

  if (countEl) countEl.textContent = planHistory.length + ' records';

  if (rows.length === 0) {
    tbody.innerHTML = `<tr><td colspan="10">
      <div class="empty-state">
        <div class="icon">📋</div>
        <p>No plan history yet. Every plan creation or edit will be recorded here.</p>
      </div></td></tr>`;
    return;
  }

  const actionBadge = a => a === 'Created'
    ? `<span class="badge badge-green">✚ Created</span>`
    : `<span class="badge badge-amber">✎ Updated</span>`;

  tbody.innerHTML = rows.map((r, i) => `
    <tr style="${r.action==='Created'?'':'background:#fffdf5'}">
      <td style="font-size:12px;color:var(--text-secondary)">${i+1}</td>
      <td>${actionBadge(r.action)}</td>
      <td>
        <div style="font-weight:600;font-size:13px">${r.name}</div>
        ${r.desc ? `<div style="font-size:11px;color:var(--text-secondary)">${r.desc}</div>` : ''}
      </td>
      <td>${planTypeBadge(r.type)}</td>
      <td style="font-weight:600">${r.amount == 0 ? '<span style="color:#16a34a">Free</span>' : '₹'+Number(r.amount).toLocaleString('en-IN')}</td>
      <td><span style="font-weight:600">${r.validity}</span><span style="font-size:11px;color:var(--text-secondary)">d</span></td>
      <td style="font-size:12px;color:var(--text-secondary);max-width:160px;white-space:normal">${r.desc||'—'}</td>
      <td>
        <span style="font-size:12px;font-weight:600;color:${r.status==='active'?'#16a34a':'#9ca3af'}">
          <span class="status-dot" style="background:${r.status==='active'?'#16a34a':'#9ca3af'}"></span>
          ${r.status==='active'?'Active':'Inactive'}
        </span>
      </td>
      <td>
        <div class="name-cell">
          <div class="avatar" style="width:22px;height:22px;font-size:9px">${initials(r.recordedBy||'?')}</div>
          <span style="font-size:12px">${r.recordedBy||'—'}</span>
        </div>
      </td>
      <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">${r.recordedAt||r.createdDate||'—'}</td>
    </tr>`).join('');
}

function clearPlanHistory() {
  if (!confirm('Clear all plan history? This will permanently delete all historical records.')) return;
  planHistory = [];
  savePlanHistory();
  renderPlanHistory();
  toast('Plan history cleared');
}

// ══════════════════════════════════════════════════════
// PAYMENT OPTIONS — Settings → Payment Options tab
// ══════════════════════════════════════════════════════

function savePayOptStore() { /* no-op: replaced by API */ }

// Single click handler for payment method cards
function selectPayMethod(method) {
  // Store selected method in hidden input
  document.getElementById('pay_selected_method').value = method;

  // Map method value → actual field group element ID suffix
  const fieldMap = { qr:'qr', upi:'upi', bank:'bank', mobile:'mob' };

  // Show only the relevant field group
  ['qr','upi','bank','mob'].forEach(k => {
    const el = document.getElementById('pay_' + k + '_fields');
    if (el) el.style.display = (fieldMap[method] === k) ? 'block' : 'none';
  });

  // Update card visuals
  ['qr','upi','bank','mob'].forEach(k => {
    const lbl = document.getElementById('pm_' + k + '_lbl');
    if (lbl) lbl.classList.remove('selected');
  });
  const cardKey = fieldMap[method] || method;
  const card = document.getElementById('pm_' + cardKey + '_lbl');
  if (card) card.classList.add('selected');
}

// Keep onPayMethodChange as alias for backwards compat (edit flow)
function onPayMethodChange(method) { selectPayMethod(method); }

function getSelectedPayMethod() {
  return document.getElementById('pay_selected_method')?.value || null;
}

function previewQR(url) {
  const box = document.getElementById('pay_qr_preview');
  const img = document.getElementById('pay_qr_img');
  if (url && url.trim()) {
    img.src = url.trim(); box.style.display = '';
    document.getElementById('pay_qr_data').value = url.trim();
  } else {
    box.style.display = 'none';
    document.getElementById('pay_qr_data').value = '';
  }
}

function switchQrTab(tab) {
  const uploadTab = document.getElementById('qr_upload_tab');
  const urlTab = document.getElementById('qr_url_tab');
  const btnUpload = document.getElementById('qrTabUpload');
  const btnUrl = document.getElementById('qrTabUrl');
  if (tab === 'upload') {
    uploadTab.style.display = ''; urlTab.style.display = 'none';
    btnUpload.style.background = 'var(--accent)'; btnUpload.style.color = '#fff'; btnUpload.style.borderColor = 'var(--accent)';
    btnUrl.style.background = ''; btnUrl.style.color = ''; btnUrl.style.borderColor = '';
  } else {
    uploadTab.style.display = 'none'; urlTab.style.display = '';
    btnUrl.style.background = 'var(--accent)'; btnUrl.style.color = '#fff'; btnUrl.style.borderColor = 'var(--accent)';
    btnUpload.style.background = ''; btnUpload.style.color = ''; btnUpload.style.borderColor = '';
  }
}

function handleQrFileSelect(input) {
  const file = input.files[0];
  if (!file) return;
  processQrFile(file);
}

function handleQrDrop(e) {
  const file = e.dataTransfer.files[0];
  if (!file) return;
  processQrFile(file);
}

function processQrFile(file) {
  if (!file.type.startsWith('image/')) { toast('Please select an image file', 'error'); return; }
  if (file.size > 5 * 1024 * 1024) { toast('Image must be under 5MB', 'error'); return; }
  const reader = new FileReader();
  reader.onload = function(e) {
    const base64 = e.target.result;
    document.getElementById('pay_qr_data').value = base64;
    document.getElementById('pay_qr_img').src = base64;
    document.getElementById('pay_qr_preview').style.display = '';
    // Update dropzone to show success
    const dz = document.getElementById('pay_qr_dropzone');
    if (dz) {
      dz.innerHTML = '<div style="font-size:32px;margin-bottom:8px">✅</div>'
        + '<div style="font-weight:600;font-size:13.5px;color:#16a34a">' + file.name + '</div>'
        + '<div style="font-size:11.5px;color:var(--text-secondary);margin-top:4px">Click to change image</div>';
    }
  };
  reader.readAsDataURL(file);
}

function removeQrImage() {
  document.getElementById('pay_qr_data').value = '';
  document.getElementById('pay_qr_url').value = '';
  document.getElementById('pay_qr_preview').style.display = 'none';
  document.getElementById('pay_qr_file').value = '';
  // Reset dropzone
  const dz = document.getElementById('pay_qr_dropzone');
  if (dz) {
    dz.innerHTML = '<div style="font-size:32px;margin-bottom:8px">📤</div>'
      + '<div style="font-weight:600;font-size:13.5px;color:var(--text-primary)">Click to upload or drag & drop</div>'
      + '<div style="font-size:11.5px;color:var(--text-secondary);margin-top:4px">PNG, JPG, WEBP — Max 5MB</div>';
  }
}
function setUpiApp(val) { document.getElementById('pay_upi_app').value = val; }
function setMobApp(val) { document.getElementById('pay_mob_app').value = val; }

function openAddPaymentOption() {
  switchStab('paymentPanel', document.getElementById('stab_payment'));
  resetPaymentForm();
  document.getElementById('pay_label').focus();
}

function resetPaymentForm() {
  editPayOptIdx = null;
  // Clear selected method
  const hiddenMethod = document.getElementById('pay_selected_method');
  if (hiddenMethod) hiddenMethod.value = '';
  // Remove selected class from all cards
  ['qr','upi','bank','mob'].forEach(k => {
    const lbl = document.getElementById('pm_' + k + '_lbl');
    if (lbl) lbl.classList.remove('selected');
  });
  // Hide all field groups (use correct element ID suffixes: mob not mobile)
  ['qr','upi','bank','mob'].forEach(k => {
    const el = document.getElementById('pay_' + k + '_fields');
    if (el) el.style.display = 'none';
  });
  // Clear all input values
  ['pay_label','pay_qr_url','pay_qr_upi','pay_qr_data','pay_upi_id','pay_upi_app',
   'pay_bank_name','pay_bank_acno','pay_bank_ifsc','pay_bank_bank','pay_bank_branch',
   'pay_mob_num','pay_mob_holder','pay_mob_app','pay_notes'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  // Reset QR upload
  const qrFile = document.getElementById('pay_qr_file');
  if (qrFile) qrFile.value = '';
  switchQrTab('upload');
  const dz = document.getElementById('pay_qr_dropzone');
  if (dz) { dz.innerHTML = '<div style="font-size:32px;margin-bottom:8px">📤</div><div style="font-weight:600;font-size:13.5px;color:var(--text-primary)">Click to upload or drag & drop</div><div style="font-size:11.5px;color:var(--text-secondary);margin-top:4px">PNG, JPG, WEBP — Max 5MB</div>'; }
  const bankType = document.getElementById('pay_bank_type');
  if (bankType) bankType.value = 'Savings';
  const status = document.getElementById('pay_status');
  if (status) status.value = 'active';
  const preview = document.getElementById('pay_qr_preview');
  if (preview) preview.style.display = 'none';
  document.getElementById('payFormIcon').textContent  = '➕';
  document.getElementById('payFormTitle').textContent = 'Add Payment Option';
  document.getElementById('payBtnTxt').textContent    = 'Add Option';
  document.getElementById('payCancelBtn').style.display = 'none';
}

function savePaymentOption() {
  const method = getSelectedPayMethod();
  const label  = document.getElementById('pay_label').value.trim();
  if (!method) { toast('Please select a payment method', 'error'); return; }
  if (!label)  { toast('Display label is required', 'error');       return; }

  // Collect method-specific fields
  let details = {};
  if (method === 'qr') {
    const qrData = document.getElementById('pay_qr_data').value.trim();
    const qrUrl = document.getElementById('pay_qr_url').value.trim();
    const finalQr = qrData || qrUrl;
    if (!finalQr) { toast('Please upload a QR code image or paste URL', 'error'); return; }
    details = {
      qrUrl: finalQr,
      linkedUpi: document.getElementById('pay_qr_upi').value.trim(),
    };
  } else if (method === 'upi') {
    const upiId = document.getElementById('pay_upi_id').value.trim();
    if (!upiId) { toast('UPI ID is required', 'error'); return; }
    details = {
      upiId,
      upiApp: document.getElementById('pay_upi_app').value.trim(),
    };
  } else if (method === 'bank') {
    const acName = document.getElementById('pay_bank_name').value.trim();
    const acNo   = document.getElementById('pay_bank_acno').value.trim();
    const ifsc   = document.getElementById('pay_bank_ifsc').value.trim();
    if (!acName) { toast('Account holder name is required', 'error'); return; }
    if (!acNo)   { toast('Account number is required', 'error');       return; }
    if (!ifsc)   { toast('IFSC code is required', 'error');            return; }
    details = {
      accountName: acName,
      accountNo:   acNo,
      ifsc:        ifsc.toUpperCase(),
      accountType: document.getElementById('pay_bank_type').value,
      bankName:    document.getElementById('pay_bank_bank').value.trim(),
      branch:      document.getElementById('pay_bank_branch').value.trim(),
    };
  } else if (method === 'mobile') {
    const mobNum    = document.getElementById('pay_mob_num').value.trim();
    const mobHolder = document.getElementById('pay_mob_holder').value.trim();
    if (!/^\d{10}$/.test(mobNum)) { toast('Enter a valid 10-digit mobile number', 'error'); return; }
    if (!mobHolder) { toast('Registered name is required', 'error'); return; }
    details = {
      mobileNo: mobNum,
      holderName: mobHolder,
      upiApp:     document.getElementById('pay_mob_app').value.trim(),
    };
  }

  const opt = {
    id:        editPayOptIdx !== null ? paymentOptions[editPayOptIdx].id : 'PAY_' + Date.now(),
    method,
    label,
    details,
    notes:     document.getElementById('pay_notes').value.trim(),
    status:    document.getElementById('pay_status').value,
    createdAt: editPayOptIdx !== null ? paymentOptions[editPayOptIdx].createdAt : nowStamp(),
    updatedAt: nowStamp(),
    createdBy: getActiveAdminName(),
  };

  if (editPayOptIdx !== null) {
    paymentOptions[editPayOptIdx] = opt;
    pushAdminLog('Updated Payment Option', label + ' · ' + method, 'setting');
    toast('✅ Payment option updated');
  } else {
    paymentOptions.push(opt);
    pushAdminLog('Added Payment Option', label + ' · ' + method, 'setting');
    toast('✅ Payment option added');
  }

  savePayOptStore();
  resetPaymentForm();
  renderPaymentOptions();
}

function editPaymentOption(i) {
  editPayOptIdx = i;
  const opt = paymentOptions[i];
  // Select method and show relevant fields
  selectPayMethod(opt.method);
  document.getElementById('pay_label').value  = opt.label || '';
  document.getElementById('pay_notes').value  = opt.notes || '';
  document.getElementById('pay_status').value = opt.status || 'active';

  // Fill method fields
  const d = opt.details || {};
  if (opt.method === 'qr') {
    const qrVal = d.qrUrl || d.qr_url || '';
    document.getElementById('pay_qr_data').value = qrVal;
    document.getElementById('pay_qr_upi').value = d.linkedUpi || '';
    if (qrVal) {
      if (qrVal.startsWith('data:') || qrVal.startsWith('blob:')) {
        switchQrTab('upload');
        document.getElementById('pay_qr_img').src = qrVal;
        document.getElementById('pay_qr_preview').style.display = '';
        const dz = document.getElementById('pay_qr_dropzone');
        if (dz) { dz.innerHTML = '<div style="font-size:32px;margin-bottom:8px">✅</div><div style="font-weight:600;font-size:13.5px;color:#16a34a">QR image loaded</div><div style="font-size:11.5px;color:var(--text-secondary);margin-top:4px">Click to change image</div>'; }
      } else {
        switchQrTab('url');
        document.getElementById('pay_qr_url').value = qrVal;
        previewQR(qrVal);
      }
    }
  } else if (opt.method === 'upi') {
    document.getElementById('pay_upi_id').value  = d.upiId  || '';
    document.getElementById('pay_upi_app').value = d.upiApp || '';
  } else if (opt.method === 'bank') {
    document.getElementById('pay_bank_name').value   = d.accountName || '';
    document.getElementById('pay_bank_acno').value   = d.accountNo   || '';
    document.getElementById('pay_bank_ifsc').value   = d.ifsc        || '';
    document.getElementById('pay_bank_type').value   = d.accountType || 'Savings';
    document.getElementById('pay_bank_bank').value   = d.bankName    || '';
    document.getElementById('pay_bank_branch').value = d.branch      || '';
  } else if (opt.method === 'mobile') {
    document.getElementById('pay_mob_num').value    = d.mobileNo   || '';
    document.getElementById('pay_mob_holder').value = d.holderName || '';
    document.getElementById('pay_mob_app').value    = d.upiApp     || '';
  }

  document.getElementById('payFormIcon').textContent    = '✏️';
  document.getElementById('payFormTitle').textContent   = 'Edit — ' + opt.label;
  document.getElementById('payBtnTxt').textContent      = 'Save Changes';
  document.getElementById('payCancelBtn').style.display = '';
  // Scroll form into view
  document.getElementById('pay_label').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function deletePaymentOption(i) {
  if (!confirm(`Delete payment option "${paymentOptions[i].label}"?`)) return;
  pushAdminLog('Deleted Payment Option', paymentOptions[i].label, 'setting');
  paymentOptions.splice(i, 1);
  savePayOptStore();
  renderPaymentOptions();
  toast('Payment option deleted');
}

function togglePayOptStatus(i) {
  paymentOptions[i].status = paymentOptions[i].status === 'active' ? 'inactive' : 'active';
  savePayOptStore();
  renderPaymentOptions();
  toast(paymentOptions[i].status === 'active' ? 'Payment option activated' : 'Payment option deactivated');
}

function renderPaymentOptions() {
  const tbody   = document.getElementById('paymentOptsTable');
  const countEl = document.getElementById('payOptCount');
  if (!tbody) return;
  if (countEl) countEl.textContent = paymentOptions.length + ' option' + (paymentOptions.length !== 1 ? 's' : '');

  if (paymentOptions.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state">
      <div class="icon">💳</div>
      <p>No payment options yet.<br>Add QR Code, UPI ID, Bank Details or UPI Mobile number.</p>
    </div></td></tr>`;
    return;
  }

  const methodIcon  = { qr:'🔳', upi:'📲', bank:'🏦', mobile:'📱' };
  const methodClass = { qr:'pay-type-qr', upi:'pay-type-upi', bank:'pay-type-bank', mobile:'pay-type-mobile' };
  const methodName  = { qr:'QR Code', upi:'UPI ID', bank:'Bank', mobile:'UPI Mobile' };

  tbody.innerHTML = paymentOptions.map((opt, i) => {
    const d = opt.details || {};
    let detailStr = '';
    if (opt.method === 'qr') {
      detailStr = d.qrUrl
        ? `<img src="${d.qrUrl}" alt="QR" style="width:40px;height:40px;object-fit:contain;border:1px solid var(--border);border-radius:5px;vertical-align:middle;margin-right:6px">`
        + (d.linkedUpi ? `<span style="font-size:12px">${d.linkedUpi}</span>` : '')
        : '—';
    } else if (opt.method === 'upi') {
      detailStr = `<span style="font-weight:600;font-size:13px">${d.upiId||'—'}</span>` +
        (d.upiApp ? `<br><span style="font-size:11px;color:var(--text-secondary)">${d.upiApp}</span>` : '');
    } else if (opt.method === 'bank') {
      detailStr = `<div style="font-size:12.5px">
        <strong>${d.accountName||'—'}</strong><br>
        <span style="font-family:monospace">${d.accountNo||'—'}</span><br>
        <span style="color:var(--text-secondary)">IFSC: ${d.ifsc||'—'} · ${d.accountType||''}</span>
        ${d.bankName ? `<br><span style="color:var(--text-secondary)">${d.bankName}${d.branch?', '+d.branch:''}</span>` : ''}
      </div>`;
    } else if (opt.method === 'mobile') {
      detailStr = `<span style="font-weight:600;font-size:13px;font-family:monospace">${d.mobileNo||'—'}</span>` +
        `<br><span style="font-size:12px">${d.holderName||'—'}</span>` +
        (d.upiApp ? `<br><span style="font-size:11px;color:var(--text-secondary)">${d.upiApp}</span>` : '');
    }

    const isActive = opt.status === 'active';
    return `<tr>
      <td style="font-size:12px;color:var(--text-secondary)">${i+1}</td>
      <td>
        <span class="pay-type-badge ${methodClass[opt.method]||''}">
          ${methodIcon[opt.method]||'💳'} ${methodName[opt.method]||opt.method}
        </span>
      </td>
      <td style="font-weight:600;font-size:13px">${opt.label}</td>
      <td style="max-width:220px">${detailStr}</td>
      <td style="font-size:12px;color:var(--text-secondary);max-width:140px">${opt.notes||'—'}</td>
      <td>
        <span style="display:inline-flex;align-items:center;font-size:12px;font-weight:600;color:${isActive?'#16a34a':'#9ca3af'}">
          <span class="status-dot" style="background:${isActive?'#16a34a':'#9ca3af'}"></span>
          ${isActive?'Active':'Inactive'}
        </span>
      </td>
      <td>
        <div class="actions">
          <button class="btn btn-outline btn-sm" onclick="editPaymentOption(${i})">✏️ Edit</button>
          <button class="btn btn-outline btn-sm" onclick="togglePayOptStatus(${i})"
            style="${isActive?'color:#d97706':'color:#16a34a'}">
            ${isActive?'⛔ Disable':'✅ Enable'}
          </button>
          <button class="btn btn-danger btn-sm" onclick="deletePaymentOption(${i})">Delete</button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

function renderCustomPlans() {
  const tbody = document.getElementById('customPlanTable');
  const countEl = document.getElementById('planCount');
  if (!tbody) return;
  if (countEl) countEl.textContent = customPlans.length + (customPlans.length === 1 ? ' plan' : ' plans');

  if (customPlans.length === 0) {
    tbody.innerHTML = `<tr><td colspan="10"><div class="empty-state">
      <div class="icon">📦</div>
      <p>No subscription plans yet.<br>Fill in the form and click <strong>Create Plan</strong>.</p>
    </div></td></tr>`;
    return;
  }

  tbody.innerHTML = customPlans.map((p, i) => {
    const dotColor  = p.status === 'active' ? '#16a34a' : '#9ca3af';
    const statusTxt = p.status === 'active' ? 'Active' : 'Inactive';
    const amount    = p.amount == 0
      ? `<span style="color:#16a34a;font-weight:600">Free</span>`
      : `<span style="font-weight:600">₹${Number(p.amount).toLocaleString('en-IN')}</span>`;
    return `<tr>
      <td style="font-size:12px;color:var(--text-secondary)">${i+1}</td>
      <td>
        <div style="font-weight:600;font-size:13px">${p.name}</div>
        ${p.desc ? `<div style="font-size:11.5px;color:var(--text-secondary);margin-top:1px">${p.desc}</div>` : ''}
      </td>
      <td>${planTypeBadge(p.type)}</td>
      <td>${amount}</td>
      <td>
        <span style="font-weight:600">${p.validity}</span>
        <span style="font-size:11.5px;color:var(--text-secondary)">d</span>
      </td>
      <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">${p.createdDate}</td>
      <td>
        <div class="name-cell">
          <div class="avatar" style="width:24px;height:24px;font-size:9px">${initials(p.createdBy)}</div>
          <span style="font-size:12.5px">${p.createdBy}</span>
        </div>
      </td>
      <td>
        <span style="display:inline-flex;align-items:center;font-size:12px;font-weight:600;color:${dotColor}">
          <span class="status-dot" style="background:${dotColor}"></span>${statusTxt}
        </span>
      </td>
      <td style="text-align:center">
        <label style="cursor:pointer;display:inline-flex;align-items:center;gap:4px" title="${p.userVisible === false ? 'Hidden from users — click to show' : 'Visible to users — click to hide'}">
          <input type="checkbox" ${p.userVisible !== false ? 'checked' : ''} onchange="togglePlanUserVisible(${i}, this.checked)"
            style="accent-color:#16a34a;width:16px;height:16px;cursor:pointer">
          <span style="font-size:11px;font-weight:600;color:${p.userVisible !== false ? '#16a34a' : '#dc2626'}">${p.userVisible !== false ? 'Show' : 'Hide'}</span>
        </label>
      </td>
      <td>
        <div class="actions">
          <button class="btn btn-outline btn-sm" onclick="openEditPlan(${i})">✏️ Edit</button>
          <button class="btn btn-danger btn-sm" onclick="deleteCustomPlan(${i})">Delete</button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

// Toggle plan visibility for user side
function togglePlanUserVisible(idx, visible) {
  const plan = customPlans[idx];
  customPlans[idx].userVisible = visible;
  // Save via API using the existing plan save endpoint
  fetch('api/admin/settings.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      section: 'plans', action: 'save',
      plan_id: plan.planId || plan.plan_id,
      name: plan.name, type: plan.type,
      amount: plan.amount, validity: plan.validity,
      description: plan.desc || plan.description || '',
      status: plan.status || 'active',
      user_visible: visible ? 1 : 0
    })
  }).then(r => r.json()).then(d => {
    if (d.ok) toast(visible ? 'Plan visible to users' : 'Plan hidden from users');
    else toast(d.error || 'Failed to update', 'error');
  }).catch(() => toast('Network error', 'error'));
  renderCustomPlans();
  pushAdminLog('Updated Plan Visibility', `${plan.name} — ${visible ? 'Shown' : 'Hidden'} for users`, 'setting');
}

// ──────────────────────────────────────────────
// RESTRICTIONS
// ──────────────────────────────────────────────
globalRestriction   = { day: '', month: '', total: '', sessionViews: '', sessionHours: '' };
individualRestrictions = []; // [{mobile, name, day, month, total}]

function renderRestrictions() {
  // Global display
  const gd = document.getElementById('globalRestrictionDisplay');
  const gc = document.getElementById('globalRestrictionCard');
  if (!gd || !gc) return;
  const hasGlobal = globalRestriction.day !== '' || globalRestriction.month !== '' || globalRestriction.total !== ''
    || globalRestriction.sessionViews !== '' || globalRestriction.sessionHours !== '';
  gd.style.display = hasGlobal ? '' : 'none';
  // Pre-fill form with current values
  if (hasGlobal) {
    document.getElementById('gl_day').value      = globalRestriction.day || '';
    document.getElementById('gl_month').value    = globalRestriction.month || '';
    document.getElementById('gl_total').value    = globalRestriction.total || '';
    document.getElementById('gl_uv_views').value = globalRestriction.sessionViews || '';
    document.getElementById('gl_uv_hours').value = globalRestriction.sessionHours || '';
    gc.innerHTML = restrictionChip(
      'All Users (Global)',
      globalRestriction.day, globalRestriction.month, globalRestriction.total,
      '#eff6ff', '#2563eb', null,
      globalRestriction.sessionViews, globalRestriction.sessionHours
    );
  }

  // Individual list
  const list   = document.getElementById('indRestrictionList');
  const count  = document.getElementById('indRestCount');
  if (!list) return;
  if (count) count.textContent = individualRestrictions.length;
  if (individualRestrictions.length === 0) {
    list.innerHTML = `<div style="text-align:center;padding:20px;color:var(--text-secondary);font-size:13px">No individual restrictions set</div>`;
    return;
  }
  list.innerHTML = individualRestrictions.map((r, i) => {
    // Try to get latest name from profiles
    const p = profiles.find(pr => pr.mobile === r.mobile);
    const displayName = p ? p.name : (r.name || '');
    const label = displayName ? `${displayName} · ${r.mobile}` : r.mobile;
    return restrictionChip(label, r.day, r.month, r.total, '#fdf4ff', '#9333ea', i);
  }).join('');
}

function restrictionChip(label, day, month, total, bg, color, idx, sessionViews, sessionHours) {
  const fmt = v => (v === '' || v == null) ? '∞' : (v == 0 ? '∞' : v);
  const isGlobal = idx === null;
  const editBtn = isGlobal
    ? `<button onclick="editGlobalRestriction()" class="btn btn-outline btn-sm" style="font-size:10px;padding:3px 8px" title="Edit">Edit</button>`
    : `<div style="display:flex;gap:4px">
        <button onclick="editIndRestriction(${idx})" class="btn btn-outline btn-sm" style="font-size:10px;padding:3px 8px" title="Edit">Edit</button>
        <button onclick="deleteIndRestriction(${idx})" style="background:none;border:1px solid #fecaca;border-radius:5px;cursor:pointer;color:#dc2626;font-size:10px;padding:3px 8px;font-weight:600" title="Remove">Delete</button>
       </div>`;
  // Optional unverified-session row only renders for the global chip when set.
  const hasSession = isGlobal && (sessionViews !== '' && sessionViews != null) || (sessionHours !== '' && sessionHours != null);
  const sessionRow = hasSession ? `
    <div style="margin-top:8px;padding-top:8px;border-top:1px dashed rgba(0,0,0,.10);display:flex;gap:12px">
      <div style="text-align:center">
        <div style="font-size:14px;font-weight:700;color:#c2410c">${fmt(sessionViews)}</div>
        <div style="font-size:10px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">Unverif / Session</div>
      </div>
      <div style="width:1px;background:rgba(0,0,0,.08)"></div>
      <div style="text-align:center">
        <div style="font-size:14px;font-weight:700;color:#c2410c">${fmt(sessionHours)}${(sessionHours===''||sessionHours==null||sessionHours==0)?'':'h'}</div>
        <div style="font-size:10px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">Session Window</div>
      </div>
    </div>` : '';
  return `<div style="background:${bg};border-radius:9px;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;gap:10px">
    <div>
      <div style="font-size:12.5px;font-weight:600;color:${color};margin-bottom:6px">${label}</div>
      <div style="display:flex;gap:12px">
        <div style="text-align:center">
          <div style="font-size:16px;font-weight:700;color:var(--text-primary)">${fmt(day)}</div>
          <div style="font-size:10px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">Per Day</div>
        </div>
        <div style="width:1px;background:rgba(0,0,0,.08)"></div>
        <div style="text-align:center">
          <div style="font-size:16px;font-weight:700;color:var(--text-primary)">${fmt(month)}</div>
          <div style="font-size:10px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">Per Month</div>
        </div>
        <div style="width:1px;background:rgba(0,0,0,.08)"></div>
        <div style="text-align:center">
          <div style="font-size:16px;font-weight:700;color:var(--text-primary)">${fmt(total)}</div>
          <div style="font-size:10px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">Total</div>
        </div>
      </div>
      ${sessionRow}
    </div>
    ${editBtn}
  </div>`;
}

function editGlobalRestriction() {
  document.getElementById('gl_day').value      = globalRestriction.day || '';
  document.getElementById('gl_month').value    = globalRestriction.month || '';
  document.getElementById('gl_total').value    = globalRestriction.total || '';
  document.getElementById('gl_uv_views').value = globalRestriction.sessionViews || '';
  document.getElementById('gl_uv_hours').value = globalRestriction.sessionHours || '';
  document.getElementById('gl_day').focus();
  toast('Global restriction loaded for editing');
}

function saveGlobalRestriction() {
  const day          = document.getElementById('gl_day').value.trim();
  const month        = document.getElementById('gl_month').value.trim();
  const total        = document.getElementById('gl_total').value.trim();
  const sessionViews = document.getElementById('gl_uv_views').value.trim();
  const sessionHours = document.getElementById('gl_uv_hours').value.trim();
  if (day === '' && month === '' && total === '' && sessionViews === '' && sessionHours === '') {
    toast('Please enter at least one limit', 'error'); return;
  }
  globalRestriction = { day, month, total, sessionViews, sessionHours };
  pushAdminLog('Set Global Restriction',
    'Day:' + (day||'∞') + ' Month:' + (month||'∞') + ' Total:' + (total||'∞')
    + ' Unverif:' + (sessionViews||'∞') + '/' + (sessionHours||'∞') + 'h',
    'setting');
  saveState();
  renderRestrictions();
  toast('Global restriction saved');
}

function lookupIndividualUser() {
  const mobile = document.getElementById('ind_mobile').value.trim();
  const hint   = document.getElementById('ind_user_hint');
  if (mobile.length === 10) {
    const p = profiles.find(pr => pr.mobile === mobile);
    if (p) {
      hint.innerHTML = `<span style="color:#16a34a;font-weight:500">✓ ${p.name} — ${p.cpId}</span>`;
    } else {
      const o = otpLogs.find(o => o.mobile === mobile);
      if (o) {
        hint.innerHTML = `<span style="color:#2563eb;font-weight:500">✓ ${o.name || 'User'} — logged in (no profile)</span>`;
      } else {
        hint.innerHTML = `<span style="color:#d97706">⚠ New mobile number — not yet registered</span>`;
      }
    }
  } else {
    hint.innerHTML = '';
  }
}

function saveIndividualRestriction() {
  const mobile = document.getElementById('ind_mobile').value.trim();
  const day    = document.getElementById('ind_day').value.trim();
  const month  = document.getElementById('ind_month').value.trim();
  const total  = document.getElementById('ind_total').value.trim();

  if (!/^\d{10}$/.test(mobile)) { toast('Enter a valid 10-digit mobile number', 'error'); return; }
  if (day === '' && month === '' && total === '') { toast('Enter at least one limit', 'error'); return; }

  const profile = profiles.find(p => p.mobile === mobile);
  const name    = profile ? profile.name : '';

  // Update if mobile already has a restriction, else add new
  const existing = individualRestrictions.findIndex(r => r.mobile === mobile);
  const entry = { mobile, name, day, month, total };
  if (existing > -1) {
    individualRestrictions[existing] = entry;
    pushAdminLog('Updated Individual Restriction', mobile + (name?' · '+name:'') + ' Day:' + (day||'∞') + ' Month:' + (month||'∞'), 'setting');
    toast('✅ Individual restriction updated');
  } else {
    individualRestrictions.push(entry);
    pushAdminLog('Set Individual Restriction', mobile + (name?' · '+name:'') + ' Day:' + (day||'∞') + ' Month:' + (month||'∞'), 'setting');
    toast('✅ Individual restriction saved');
  }

  // Clear form
  ['ind_mobile','ind_day','ind_month','ind_total'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('ind_user_hint').innerHTML = '';
  saveState();
  renderRestrictions();
}

function editIndRestriction(i) {
  const r = individualRestrictions[i];
  document.getElementById('ind_mobile').value = r.mobile || '';
  document.getElementById('ind_day').value = r.day || '';
  document.getElementById('ind_month').value = r.month || '';
  document.getElementById('ind_total').value = r.total || '';
  lookupIndividualUser();
  document.getElementById('ind_day').focus();
  toast('Restriction loaded for ' + (r.name || r.mobile));
}

function deleteIndRestriction(i) {
  const r = individualRestrictions[i];
  if (!confirm('Delete restriction for ' + (r.name || r.mobile) + '?')) return;
  pushAdminLog('Removed Individual Restriction', r.mobile + (r.name?' · '+r.name:''), 'setting');
  individualRestrictions.splice(i, 1);
  saveState();
  renderRestrictions();
  toast('Restriction removed');
}

// ──────────────────────────────────────────────
// USER USAGE — EXPAND / COLLAPSE
// ──────────────────────────────────────────────
usageExpanded = {}; // {mobile: true/false}

function renderUsage() {
  const container = document.getElementById('usageCards');
  if (!container) return;

  const mq = (document.getElementById('usageMobileSearch')?.value || '').trim().toLowerCase();
  const filteredUsage = mq ? usage.filter(u => u.mobile.includes(mq) || (u.name||'').toLowerCase().includes(mq) || (u.cpId||'').toLowerCase().includes(mq)) : usage;

  if (filteredUsage.length === 0) {
    container.innerHTML = `<div class="card"><div class="empty-state"><div class="icon">🔍</div><p>No usage records match <strong>${mq}</strong></p></div></div>`;
    return;
  }

  container.innerHTML = filteredUsage.map(u => {
    const pTotal   = u.profileViews.length;
    const cTotal   = u.contactViews.length;
    const isOpen   = !!usageExpanded[u.mobile];
    const iconCls  = isOpen ? 'open' : '';
    const detailCls= isOpen ? 'open' : '';

    // Profile views log rows
    const profileRows = pTotal === 0
      ? `<div class="usage-empty-col">No profiles viewed yet</div>`
      : u.profileViews.map(v => `
          <div class="usage-log-row">
            <span class="usage-log-cpid">${v.cpId}</span>
            <span class="usage-log-time">🕐 ${v.datetime}</span>
          </div>`).join('');

    // Contact views log rows
    const contactRows = cTotal === 0
      ? `<div class="usage-empty-col">No contacts viewed yet</div>`
      : u.contactViews.map(v => `
          <div class="usage-log-row">
            <span class="usage-log-cpid">${v.cpId}</span>
            <span class="usage-log-time">🕐 ${v.datetime}</span>
          </div>`).join('');

    const usageProfileIdx = profiles.findIndex(p => p.mobile === u.mobile);
    const closedTypesU    = ['paid','not_interested'];
    const usageFU         = usageProfileIdx >= 0 && followUps.some(f => f.cpId === profiles[usageProfileIdx]?.cpId && !closedTypesU.includes(f.type));
    const usageMobileStyle= usageFU ? 'color:var(--text-secondary)' : 'color:#dc2626;font-weight:700';

    return `
    <div class="usage-card">
      <!-- COLLAPSED HEADER (always visible) -->
      <div class="usage-card-header" onclick="toggleUsage('${u.mobile}')">
        <span class="usage-toggle-icon ${iconCls}">▶</span>
        <div class="usage-meta">
          <div class="avatar" style="width:34px;height:34px;font-size:13px;flex-shrink:0">${initials(u.name||u.mobile)}</div>
          <div>
            <div class="usage-name">${u.name||u.mobile}</div>
            <div class="usage-mobile" style="${usageMobileStyle}"
              ${!usageFU && usageProfileIdx >= 0 ? `ondblclick="openFollowUp(${usageProfileIdx});event.stopPropagation();" title="No follow-up — double-click to create" style="${usageMobileStyle};cursor:pointer;border-bottom:1px dashed #dc2626"` : ''}
            >${u.mobile} &nbsp;·&nbsp; ${u.cpId||''}${!usageFU && usageProfileIdx >= 0 ? ' ↑dbl' : ''}</div>
          </div>
          <div style="margin-left:12px">${planBadge(u.plan)}</div>
        </div>
        <div class="usage-totals">
          <div class="usage-total-chip" style="background:#eff6ff">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span style="color:#2563eb;font-size:11.5px">Profiles Viewed</span>
            <span class="chip-num" style="color:#2563eb">${pTotal}</span>
          </div>
          <div class="usage-total-chip" style="background:#fdf4ff">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9333ea" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.36 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.27 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z"/></svg>
            <span style="color:#9333ea;font-size:11.5px">Contacts Viewed</span>
            <span class="chip-num" style="color:#9333ea">${cTotal}</span>
          </div>
        </div>
      </div>

      <!-- EXPANDED DETAIL -->
      <div class="usage-detail ${detailCls}">
        <div class="usage-detail-inner">
          <!-- Left: Profile views -->
          <div class="usage-detail-col">
            <div class="usage-col-head" style="color:#2563eb">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              Profiles Viewed &nbsp;<span class="badge" style="background:#dbeafe;color:#2563eb;padding:1px 8px">${pTotal}</span>
            </div>
            ${profileRows}
          </div>
          <!-- Right: Contact views -->
          <div class="usage-detail-col">
            <div class="usage-col-head" style="color:#9333ea">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9333ea" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.36 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.27 2h3a2 2 0 0 1 2 1.72"/></svg>
              Contacts Viewed &nbsp;<span class="badge" style="background:#f3e8ff;color:#9333ea;padding:1px 8px">${cTotal}</span>
            </div>
            ${contactRows}
          </div>
        </div>
      </div>
    </div>`;
  }).join('');
}

function toggleUsage(mobile) {
  usageExpanded[mobile] = !usageExpanded[mobile];
  renderUsage();
}

function expandAllUsage() {
  usage.forEach(u => usageExpanded[u.mobile] = true);
  renderUsage();
}

function collapseAllUsage() {
  usage.forEach(u => usageExpanded[u.mobile] = false);
  renderUsage();
}

// ──────────────────────────────────────────────
// OTP LOGS — loaded from API via loadAll()
// ──────────────────────────────────────────────
/* DUMMY DATA REMOVED — otpLogs loaded from s.otpLogs in loadAll() */
/*
*/

function renderOtp() {
  const q       = (document.getElementById('otpSearch')?.value      || '').toLowerCase();
  const stFilt  =  document.getElementById('otpStatusFilter')?.value || '';
  const banFilt =  document.getElementById('otpBanFilter')?.value    || '';
  const dateFrom=  document.getElementById('otpDateFrom')?.value     || '';
  const dateTo  =  document.getElementById('otpDateTo')?.value       || '';
  const sorted  = [...otpLogs].sort((a,b)=>(b.otpRequestedAt||'').localeCompare(a.otpRequestedAt||''));
  // Normalize legacy status values to the current taxonomy
  const normStatus = v => v==='typing'?'web_in':v==='skip'?'web_out':v==='unverified'?'otp_request':(v||'');
  const filtered= sorted.filter(o=>{
    const txt=(o.mobile+o.cpId+o.name).toLowerCase();
    const d=(o.otpRequestedAt||'').split(' ')[0];
    return(!q||txt.includes(q))&&(!stFilt||normStatus(o.verified)===stFilt)&&
           (!banFilt||(banFilt==='banned'&&o.banned)||(banFilt==='active'&&!o.banned))&&
           (!dateFrom||d>=dateFrom)&&(!dateTo||d<=dateTo);
  });
  const rows=filtered.map((o,i)=>{
    const realIdx=otpLogs.indexOf(o);
    const otpPIdx=profiles.findIndex(p=>p.mobile===o.mobile);
    const cpCell=o.cpId?`<code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${o.cpId}</code>`:`<span style="color:#d1d5db;font-size:12px">—</span>`;
    const nameCell=o.name?`<div class="name-cell"><div class="avatar" style="width:26px;height:26px;font-size:10px">${initials(o.name)}</div>${o.name}</div>`:`<span style="color:#d1d5db;font-size:12px">Not registered</span>`;
    const st = normStatus(o.verified);
    const vBadge = st === 'verified'
      ? `<span class="badge otp-verified">✅ OTP Verified</span>`
      : st === 'otp_failed'
        ? `<span class="badge otp-failed" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca">❌ OTP Failed</span>`
        : st === 'otp_request'
          ? `<span class="badge otp-request" style="background:#fef9c3;color:#854d0e;border:1px solid #fde68a">⏳ OTP Request</span>`
          : st === 'web_in'
            ? `<span class="badge otp-webin" style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa">⌨️ Web In</span>`
            : st === 'web_out'
              ? `<span class="badge otp-webout" style="background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe">⤼ Web Out</span>`
              : `<span class="badge otp-unverified">— ${o.verified || 'Unknown'}</span>`;
    const sBadge=o.banned?`<span class="badge otp-banned">🚫 Banned</span>`:`<span class="badge otp-active">✓ Active</span>`;
    const cc=o.loginCount>=15?'#dc2626':o.loginCount>=8?'#d97706':'var(--text-primary)';
    const banBtn=o.banned?`<button class="btn unban-btn btn-sm" onclick="toggleBan(${realIdx})">↩ Unban</button>`:`<button class="btn ban-btn btn-sm" onclick="toggleBan(${realIdx})">🚫 Ban</button>`;
    // Follow-up check: black if follow-up exists, red if not
    const otpHasFU = otpPIdx >= 0
      ? followUps.some(f => f.cpId === profiles[otpPIdx]?.cpId)
      : followUps.some(f => f.mobile === o.mobile);
    let mobileCell;
    if (otpHasFU) {
      mobileCell = `<span style="font-size:13px;font-weight:500;color:var(--text-primary)">${o.mobile}</span>`;
    } else if (otpPIdx >= 0) {
      mobileCell = `<span style="font-size:13px;font-weight:700;color:#dc2626;cursor:pointer;border-bottom:1.5px dashed #dc2626" title="No follow-up — double-click to create" ondblclick="openFollowUp(${otpPIdx})">${o.mobile}</span>`;
    } else {
      mobileCell = `<span style="font-size:13px;font-weight:700;color:#dc2626;cursor:pointer;border-bottom:1.5px dashed #dc2626" title="No follow-up — double-click to create" ondblclick="openFollowUpByMobile('${o.mobile}','${(o.name||'').replace(/'/g,"\\'")}')">${o.mobile}</span>`;
    }
    const liveOtpRaw = o.liveOtp || o.live_otp || '';
    const liveExp = o.liveOtpExpires || o.live_otp_expires || '';
    const liveOtpExpiredClient = liveExp && (new Date(liveExp.replace(' ','T')).getTime() < Date.now());
    const liveOtpCell = liveOtpRaw && !liveOtpExpiredClient
      ? `<span style="font-family:var(--mono,monospace);font-weight:700;font-size:14px;color:#047857;background:#ecfdf5;padding:3px 9px;border-radius:6px;letter-spacing:2px" title="Expires ${liveExp}">${liveOtpRaw}</span>`
      : `<span style="color:#d1d5db;font-size:12px">—</span>`;
    return `<tr style="${o.banned?'background:#fff8f8;':''}">
      <td style="font-size:12px;color:var(--text-secondary);text-align:center">${i+1}</td>
      <td>${mobileCell}</td>
      <td>${cpCell}</td><td>${nameCell}</td>
      <td style="font-size:12px;white-space:nowrap;color:var(--text-secondary)">🕐 ${o.otpRequestedAt}</td>
      <td style="text-align:center">${liveOtpCell}</td>
      <td>${vBadge}</td>
      <td style="font-size:12px;white-space:nowrap;color:var(--text-secondary)">${!o.lastLogin||o.lastLogin==='—'?'—':'🕐 '+o.lastLogin}</td>
      <td style="text-align:center"><span style="font-weight:700;color:${cc}">${o.loginCount}</span></td>
      <td>${sBadge}</td><td>${banBtn}</td>
    </tr>`;
  });
  const np=paginate(rows,_pg.otp,PER_PAGE,'otpTable','otpPg',
    `<tr><td colspan="11"><div class="empty-state"><div class="icon">🔐</div><p>No OTP records found</p></div></td></tr>`);
  if(np) _pg.otp=np;
}

let _otpLivePollInt = null;
function startOtpLivePoll() {
  stopOtpLivePoll();
  _otpLivePollInt = setInterval(async () => {
    try {
      const r = await fetch('api/admin/settings.php?section=otpLogs').then(r => r.json());
      if (r && r.ok && Array.isArray(r.otpLogs)) {
        otpLogs = mapRows(r.otpLogs);
        renderOtp();
      }
    } catch(e) {}
  }, 5000);
}
function stopOtpLivePoll() {
  if (_otpLivePollInt) { clearInterval(_otpLivePollInt); _otpLivePollInt = null; }
}

function toggleBan(i) {
  const o = otpLogs[i];
  const action = o.banned ? 'unban' : 'ban';
  if (!confirm(`${action === 'ban' ? '🚫 Ban' : '↩ Unban'} user ${o.mobile}${o.name ? ' (' + o.name + ')' : ''}?`)) return;
  otpLogs[i].banned = !otpLogs[i].banned;
  pushAdminLog(action === 'ban' ? 'Banned User' : 'Unbanned User', o.mobile + (o.name ? ' · ' + o.name : '') + (o.cpId ? ' · ' + o.cpId : ''), 'ban');
  saveState();
  renderOtp();
  toast(action === 'ban' ? '🚫 User banned' : '✅ User unbanned');
  pushNotif(
    action === 'ban' ? '🚫 User banned' : '✅ User unbanned',
    o.mobile + (o.name ? ' · ' + o.name : '') + ' has been ' + action + 'ned.'
  );
}

// ──────────────────────────────────────────────
// BILLS — DEDICATED RENDER + FILTER
// ──────────────────────────────────────────────
function renderBills() {
  const q       = (document.getElementById('billSearch')?.value    || '').toLowerCase();
  const planF   = (document.getElementById('billPlanFilter')?.value|| '').toLowerCase();
  const payF    =  document.getElementById('billPaymentFilter')?.value || '';
  const dateFrom=  document.getElementById('billDateFrom')?.value  || '';
  const dateTo  =  document.getElementById('billDateTo')?.value    || '';
  const sorted  = [...bills].sort((a,b)=>(b.billedDate||'').localeCompare(a.billedDate||''));
  const filtered= sorted.filter(b=>{
    const txt=(b.cpId+b.name+b.mobile).toLowerCase();
    return (!q||txt.includes(q))&&(!planF||(b.planType||b.plan||'').toLowerCase().includes(planF))&&
           (!payF||(b.payment||'')===payF)&&(!dateFrom||(b.billedDate||'')>=dateFrom)&&(!dateTo||(b.billedDate||'')<=dateTo);
  });
  const countEl=document.getElementById('billCount');
  if(countEl) countEl.textContent=filtered.length+' / '+bills.length;
  const rows=filtered.map((b,i)=>`<tr>
    <td style="font-size:12px;color:var(--text-secondary)">${i+1}</td>
    <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${b.cpId}</code></td>
    <td><div class="name-cell"><div class="avatar" style="width:26px;height:26px;font-size:10px">${initials(b.name)}</div>${b.name}</div></td>
    <td style="font-size:13px">${b.mobile}</td>
    <td style="font-weight:600;font-size:13px">${b.planName||b.plan||'—'}</td>
    <td>${planBadge(b.planType||b.plan||'free')}</td>
    <td style="font-weight:700;font-size:13px;color:var(--accent)">₹${b.amount!=null?Number(b.amount).toLocaleString('en-IN'):'—'}</td>
    <td><span class="badge badge-gray">${b.payment||'—'}</span></td>
    <td><div class="name-cell"><div class="avatar" style="width:24px;height:24px;font-size:9px">${initials(b.billedBy||'?')}</div><span style="font-size:12.5px">${b.billedBy||'—'}</span></div></td>
    <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">${b.billedDate||'—'}</td>
    <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">${b.expiry||'—'}</td>
    <td>
      <div style="display:flex;gap:5px;align-items:center">
        <button class="btn btn-outline btn-sm" onclick="openBillEdit(${bills.indexOf(b)})">✏️ Edit</button>
        <button class="btn btn-outline btn-sm" onclick="sharePaymentLink('${b.cpId}','${b.name}','${b.mobile}')" style="color:#16a34a;border-color:#86efac;font-size:11.5px">📤</button>
      </div>
    </td>
  </tr>`);
  const np=paginate(rows,_pg.bill,PER_PAGE,'billTable','billPg',
    `<tr><td colspan="12"><div class="empty-state"><div class="icon">💳</div><p>No bills match the filter</p></div></td></tr>`);
  if(np) _pg.bill=np;
}

function renderBillHistory() {
  const q      = (document.getElementById('bhSearch')?.value      || '').toLowerCase();
  const actF   =  document.getElementById('bhActionFilter')?.value|| '';
  const dateFrom=  document.getElementById('bhDateFrom')?.value   || '';
  const dateTo  =  document.getElementById('bhDateTo')?.value     || '';
  const filtered=billHistory.filter(b=>{
    const txt=(b.cpId+b.name+b.mobile).toLowerCase();
    const d=(b._recordedAt||'').split(' ')[0];
    return(!q||txt.includes(q))&&(!actF||b._action===actF)&&(!dateFrom||d>=dateFrom)&&(!dateTo||d<=dateTo);
  });
  const countEl=document.getElementById('billHistoryCount');
  if(countEl) countEl.textContent=filtered.length+' records';
  const rows=filtered.map((b,i)=>{
    const ab=b._action==='Created'?`<span class="badge bh-created">➕ Created</span>`:`<span class="badge bh-updated">✏️ Updated</span>`;
    return `<tr>
      <td style="font-size:12px;color:var(--text-secondary)">${i+1}</td>
      <td>${ab}</td>
      <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${b.cpId}</code></td>
      <td><div class="name-cell"><div class="avatar" style="width:24px;height:24px;font-size:9px">${initials(b.name||'?')}</div>${b.name||'—'}</div></td>
      <td style="font-size:13px">${b.mobile||'—'}</td>
      <td style="font-weight:600;font-size:13px">${b.planName||b.plan||'—'}</td>
      <td style="font-weight:700;font-size:13px;color:var(--accent)">₹${b.amount!=null?Number(b.amount).toLocaleString('en-IN'):'—'}</td>
      <td><span class="badge badge-gray">${b.payment||'—'}</span></td>
      <td><div class="name-cell"><div class="avatar" style="width:24px;height:24px;font-size:9px">${initials(b.billedBy||'?')}</div><span style="font-size:12.5px">${b.billedBy||'—'}</span></div></td>
      <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">🕐 ${b._recordedAt||'—'}</td>
      <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">${b.expiry||'—'}</td>
    </tr>`;
  });
  const np=paginate(rows,_pg.billHistory,PER_PAGE,'billHistoryTable','billHistoryPg',
    `<tr><td colspan="11"><div class="empty-state"><div class="icon">🗂</div><p>No bill history yet</p></div></td></tr>`);
  if(np) _pg.billHistory=np;
}

function clearBillFilters() {
  ['billSearch','billDateFrom','billDateTo'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  ['billPlanFilter','billPaymentFilter'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  renderBills();
}
function clearBhFilters() {
  ['bhSearch','bhDateFrom','bhDateTo'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  document.getElementById('bhActionFilter').value='';
  renderBillHistory();
}

// ──────────────────────────────────────────────
// PROFILES PAGE — FILTER
// ──────────────────────────────────────────────
let _profileSearchTimer = null;
function applyProfileFilter() {
  clearTimeout(_profileSearchTimer);
  _profileSearchTimer = setTimeout(async () => {
    _profOffset = 0;
    await loadProfiles();
    render();
    updateProfilePagination();
    if (typeof renderInstaCards === 'function' && profileViewMode === 'cards') renderInstaCards();
  }, 350);
}
function clearProfileFilters() {
  ['profileSearch','profileDateFrom','profileDateTo'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  ['profileStatusFilter','profilePlanFilter','profileGenderFilter','profilePhotoFilter'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  _profOffset = 0;
  loadProfiles('limit=10000&offset=0').then(() => { render(); updateProfilePagination(); });
}

// ──────────────────────────────────────────────
// MANAGE PAGE — SERVER-SIDE FILTER
// ──────────────────────────────────────────────
let _manageSearchTimer = null;
function applyManageFilter() {
  clearTimeout(_manageSearchTimer);
  _manageSearchTimer = setTimeout(async () => {
    _profOffset = 0;
    await loadProfiles();
    render();
    updateProfilePagination();
  }, 350);
}
function clearManageFilters() {
  ['manageSearch','manageDateFrom','manageDateTo'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  ['manageStatusFilter','managePlanFilter'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  _profOffset = 0;
  loadProfiles('limit=10000&offset=0').then(() => { render(); updateProfilePagination(); });
}

// Server-side pagination controls
function updateProfilePagination() {
  // Use filtered count if photo filter is active
  const photoFilterVal = document.getElementById('profilePhotoFilter')?.value || '';
  let displayTotal = _profTotal;
  if (photoFilterVal === 'with') displayTotal = profiles.filter(p => p.hasPhoto === true || p.has_photo === true).length;
  else if (photoFilterVal === 'without') displayTotal = profiles.filter(p => !(p.hasPhoto === true || p.has_photo === true)).length;
  const from = displayTotal ? _profOffset + 1 : 0;
  const to   = Math.min(_profOffset + displayTotal, displayTotal);
  const info = `Showing <b>${from}–${to}</b> of <b>${displayTotal.toLocaleString()}</b> profiles`;
  ['serverPagInfo','serverPagInfo2'].forEach(id => { const e=document.getElementById(id); if(e) e.innerHTML=info; });
  const prevDis = _profOffset === 0;
  const nextDis = (_profOffset + _profLimit) >= _profTotal;
  ['serverPagPrev','serverPagPrev2'].forEach(id => { const e=document.getElementById(id); if(e) e.disabled=prevDis; });
  ['serverPagNext','serverPagNext2'].forEach(id => { const e=document.getElementById(id); if(e) e.disabled=nextDis; });
}
async function profPagePrev() {
  _profOffset = Math.max(0, _profOffset - _profLimit);
  await loadProfiles(); render(); updateProfilePagination();
}
async function profPageNext() {
  if ((_profOffset + _profLimit) < _profTotal) {
    _profOffset += _profLimit;
    await loadProfiles(); render(); updateProfilePagination();
  }
}

// ──────────────────────────────────────────────
// FOLLOW-UPS — FILTER (patch renderFollowTables)
// ──────────────────────────────────────────────
function getFollowFilters() {
  return {
    q:       (document.getElementById('followSearch')?.value    || '').toLowerCase(),
    typeF:    document.getElementById('followTypeFilter')?.value || '',
    dateFrom: document.getElementById('followDateFrom')?.value  || '',
    dateTo:   document.getElementById('followDateTo')?.value    || '',
  };
}
function applyFollowFilter(list) {
  const { q, typeF, dateFrom, dateTo } = getFollowFilters();
  return list.filter(f => {
    const txt = (f.cpId+f.memberName+f.mobile).toLowerCase();
    return (!q       || txt.includes(q))
        && (!typeF   || f.type === typeF)
        && (!dateFrom|| f.date >= dateFrom)
        && (!dateTo  || f.date <= dateTo);
  });
}
function clearFollowFilters() {
  ['followSearch','followDateFrom','followDateTo'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  document.getElementById('followTypeFilter').value='';
  renderFollowTables();
}

// ──────────────────────────────────────────────
// DELETED PAGE — FILTER
// ──────────────────────────────────────────────
function applyDeletedFilter() {
  const q       = (document.getElementById('deletedSearch')?.value  || '').toLowerCase();
  const dateFrom=  document.getElementById('deletedDateFrom')?.value || '';
  const dateTo  =  document.getElementById('deletedDateTo')?.value   || '';
  document.querySelectorAll('#deletedTable tr').forEach(row => {
    const txt = row.textContent.toLowerCase();
    const d   = row.dataset.deletedat || '';
    const ok  = (!q       || txt.includes(q))
             && (!dateFrom|| d >= dateFrom)
             && (!dateTo  || d <= dateTo);
    row.style.display = ok ? '' : 'none';
  });
}
function clearDeletedFilters() {
  ['deletedSearch','deletedDateFrom','deletedDateTo'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  applyDeletedFilter();
}

// ──────────────────────────────────────────────
// OTP PAGE — FILTER (clear helper)
// ──────────────────────────────────────────────
function clearOtpFilters() {
  ['otpSearch','otpDateFrom','otpDateTo'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  ['otpStatusFilter','otpBanFilter'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  renderOtp();
}

// ──────────────────────────────────────────────
// EXPIRED PROFILES
// ──────────────────────────────────────────────
expiredProfiles = [
  { cpId:"CP0995", name:"Deepa N",    mobile:"9500099001", planName:"Gold Paid Plan", expiryDate:"2024-12-31", reason:"Plan expired — not renewed", expiredOn:"2025-01-05 10:00:00", actionedBy:"Balasubramanian R",
    profile:{ cpId:"CP0995", mobile:"9500099001", name:"Deepa N", age:27, gender:"Female", status:"Approved", plan:"paid", created:"2024-01-01", approved:"2024-01-02", expiry:"2024-12-31" }},
  { cpId:"CP0996", name:"Karthik V",  mobile:"9500099002", planName:"Silver Basic",   expiryDate:"2025-01-01", reason:"6-month plan lapsed, member did not renew", expiredOn:"2025-01-10 11:00:00", actionedBy:"Ravi Kumar",
    profile:{ cpId:"CP0996", mobile:"9500099002", name:"Karthik V", age:29, gender:"Male", status:"Approved", plan:"basic", created:"2024-07-01", approved:"2024-07-02", expiry:"2025-01-01" }},
];
expireIdx = null; // profile index being expired

function renderExpired() {
  const q        = (document.getElementById('expiredSearch')?.value    || '').toLowerCase();
  const reasonF  =  document.getElementById('expiredReasonFilter')?.value || '';
  const dateFrom =  document.getElementById('expiredDateFrom')?.value  || '';
  const dateTo   =  document.getElementById('expiredDateTo')?.value    || '';

  // Newest first by expiredOn
  const sorted = [...expiredProfiles].sort((a,b) => (b.expiredOn||'').localeCompare(a.expiredOn||''));

  const filtered = sorted.filter(e => {
    const txt = (e.cpId + e.name + e.mobile + (e.planName||'')).toLowerCase();
    const d   = (e.expiredOn||'').split(' ')[0];
    return (!q        || txt.includes(q))
        && (!reasonF  || (e.reason||'') === reasonF)
        && (!dateFrom || d >= dateFrom)
        && (!dateTo   || d <= dateTo);
  });

  const countEl = document.getElementById('expiredCount');
  if (countEl) countEl.textContent = filtered.length + ' records';

  const tbody = document.getElementById('expiredTable');
  if (!tbody) return;

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="10"><div class="empty-state">
      <div class="icon">⏰</div>
      <p>No expired profiles yet</p>
    </div></td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map((e, i) => {
    const realIdx = expiredProfiles.indexOf(e);
    return `<tr style="background:#fffdf9">
      <td style="font-size:12px;color:var(--text-secondary)">${i + 1}</td>
      <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${e.cpId}</code></td>
      <td><div class="name-cell">
        <div class="avatar" style="background:#fff8f0;color:#d97706;width:28px;height:28px;font-size:10px">${initials(e.name)}</div>
        ${e.name}
      </div></td>
      <td style="font-size:13px">${mobileCellHtml(e.mobile, -1, false)}</td>
      <td style="font-weight:600;font-size:13px">${e.planName||'—'}</td>
      <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">${e.expiryDate||'—'}</td>
      <td><span class="badge badge-amber" style="font-size:11.5px">${e.reason||'—'}</span></td>
      <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">🕐 ${e.expiredOn||'—'}</td>
      <td><div class="name-cell">
        <div class="avatar" style="width:24px;height:24px;font-size:9px">${initials(e.actionedBy||'?')}</div>
        <span style="font-size:12.5px">${e.actionedBy||'—'}</span>
      </div></td>
      <td>
        <button class="btn btn-green btn-sm" onclick="undoExpire(${realIdx})" title="Restore to Pre-approved">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
          Undo
        </button>
      </td>
    </tr>`;
  }).join('');
}

function openExpire(i) {
  expireIdx = i;
  const p = profiles[i];
  // Find the bill for this profile to get plan name
  const bill = bills.find(b => b.cpId === p.cpId);
  document.getElementById('exp_cpid').value  = p.cpId;
  document.getElementById('exp_mobile').value= p.mobile;
  document.getElementById('exp_name').value  = p.name;
  document.getElementById('exp_plan').value  = bill ? (bill.planName||bill.plan||'—') : (p.plan||'—');
  document.getElementById('exp_date').value  = p.expiry || '—';
  document.getElementById('exp_reason').value= '';
  const activeAdmin = admins.find(a => a.status === 'active');
  document.getElementById('exp_by').value    = activeAdmin ? activeAdmin.name : '—';
  openModal('expireOverlay');
}

function confirmExpire() {
  const reason = document.getElementById('exp_reason').value;
  if (!reason) { toast('Please select a reason', 'error'); return; }

  const p          = profiles[expireIdx];
  const bill       = bills.find(b => b.cpId === p.cpId);
  const actionedBy = document.getElementById('exp_by').value;
  const now        = nowStamp();

  expiredProfiles.unshift({
    cpId:       p.cpId,
    name:       p.name,
    mobile:     p.mobile,
    planName:   bill ? (bill.planName || bill.plan) : (p.plan || '—'),
    expiryDate: p.expiry || '—',
    reason,
    expiredOn:  now,
    actionedBy,
    // snapshot full profile for undo
    _snapshot:  { ...p }
  });

  // Move profile back to Preapproved, clear approval + expiry
  profiles[expireIdx].status   = 'Preapproved';
  profiles[expireIdx].approved = '';
  profiles[expireIdx].expiry   = '';

  closeModal('expireOverlay');
  render(); postRender();
  renderExpired();
  pushAdminLog('Marked Profile Expired', p.name + ' · ' + p.cpId + ' — ' + reason, 'expired');
  saveState();
  pushNotif('⏰ Profile expired', p.name + ' (' + p.cpId + ') moved to Expired archive.');
  toast('⏰ Profile marked as expired');
}

function undoExpire(i) {
  const e = expiredProfiles[i];
  if (!confirm('Restore ' + e.name + ' (' + e.cpId + ') to Pre-approved status?')) return;

  // Restore profile from snapshot — set back to Preapproved
  const pi = profiles.findIndex(p => p.cpId === e.cpId);
  if (pi > -1) {
    profiles[pi].status   = 'Preapproved';
    profiles[pi].approved = '';
    profiles[pi].expiry   = '';
  } else {
    // Profile was removed — restore from snapshot
    const restored = { ...e._snapshot, status: 'Preapproved', approved: '', expiry: '' };
    delete restored._snapshot;
    profiles.push(restored);
  }

  expiredProfiles.splice(i, 1);
  render(); postRender();
  renderExpired();
  pushAdminLog('Restored Expired Profile', e.name + ' · ' + e.cpId, 'expired');
  saveState();
  pushNotif('↩️ Expired profile restored', e.name + ' restored to Pre-approved.');
  toast('↩ Profile restored to Pre-approved');
}

function clearExpiredFilters() {
  ['expiredSearch','expiredDateFrom','expiredDateTo'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  const rf = document.getElementById('expiredReasonFilter'); if (rf) rf.value = '';
  renderExpired();
}

// ──────────────────────────────────────────────
// ADMIN LOG
// ──────────────────────────────────────────────
adminLog = [
  { admin:"Balasubramanian R", role:"super",   action:"Logged In",          detail:"bala.admin — OTP verified",                                    type:"login",   timestamp:"2026-01-20 09:00:00" },
  { admin:"Balasubramanian R", role:"super",   action:"Added Profile",      detail:"Bala Kumar · 9876543210",                                      type:"profile", timestamp:"2026-01-20 09:05:00" },
  { admin:"Balasubramanian R", role:"super",   action:"Scheduled Follow-up",detail:"CP1001 · data on 2026-01-12",                                  type:"followup",timestamp:"2026-01-20 09:06:00" },
  { admin:"Ravi Kumar",        role:"manager", action:"Logged In",          detail:"ravi.mgr — OTP verified",                                      type:"login",   timestamp:"2026-01-19 10:00:00" },
  { admin:"Ravi Kumar",        role:"manager", action:"Approved Profile",   detail:"Priya Devi · CP1003",                                          type:"profile", timestamp:"2026-01-19 10:15:00" },
  { admin:"Ravi Kumar",        role:"manager", action:"Created Bill",       detail:"Priya Devi · Premium Annual · ₹4500",                          type:"bill",    timestamp:"2026-01-19 10:20:00" },
  { admin:"Balasubramanian R", role:"super",   action:"Approved Profile",   detail:"Ravi Shankar · CP1002",                                        type:"profile", timestamp:"2026-01-18 11:00:00" },
  { admin:"Balasubramanian R", role:"super",   action:"Created Bill",       detail:"Ravi Shankar · Gold Paid Plan · ₹2500",                        type:"bill",    timestamp:"2026-01-18 11:10:00" },
  { admin:"Balasubramanian R", role:"super",   action:"Created Plan",       detail:"Gold Paid Plan · ₹2500 · 365d",                                type:"plan",    timestamp:"2026-01-01 09:05:00" },
  { admin:"Balasubramanian R", role:"super",   action:"Created Plan",       detail:"Premium Annual · ₹4500 · 365d",                                type:"plan",    timestamp:"2026-01-01 09:10:00" },
  { admin:"Balasubramanian R", role:"super",   action:"Added Payment Option",detail:"GPay / UPI · upi",                                            type:"setting", timestamp:"2026-01-01 09:15:00" },
  { admin:"Balasubramanian R", role:"super",   action:"Added Payment Option",detail:"Bank Transfer · bank",                                        type:"setting", timestamp:"2026-01-01 09:20:00" },
  { admin:"Ravi Kumar",        role:"manager", action:"Approved Profile",   detail:"Kavitha S · CP1005",                                           type:"profile", timestamp:"2026-01-13 09:00:00" },
  { admin:"Ravi Kumar",        role:"manager", action:"Created Bill",       detail:"Kavitha S · Gold Paid Plan · ₹2500",                           type:"bill",    timestamp:"2026-01-13 09:30:00" },
  { admin:"Balasubramanian R", role:"super",   action:"Approved Profile",   detail:"Lakshmi R · CP1008",                                           type:"profile", timestamp:"2026-01-02 09:00:00" },
  { admin:"Balasubramanian R", role:"super",   action:"Created Bill",       detail:"Lakshmi R · Premium Annual · ₹4500",                          type:"bill",    timestamp:"2026-01-02 09:30:00" },
];

function getActiveAdminName() {
  const a = admins.find(a => a.status === 'active');
  return a ? a.name : 'Admin';
}
function getActiveAdminRole() {
  const a = admins.find(a => a.status === 'active');
  return a ? a.role : 'staff';
}

function pushAdminLog(action, detail, type) {
  adminLog.unshift({
    adminName: getActiveAdminName(),
    role:      getActiveAdminRole(),
    action, detail, type,
    timestamp: nowStamp()
  });
  // Keep last 500 entries
  if (adminLog.length > 500) adminLog.length = 500;
  saveState();
  // Refresh log table if visible
  if (document.getElementById('adminLogTable')) renderAdminLog();
}

function renderAdminLog() {
  const q       = (document.getElementById('alSearch')?.value    || '').toLowerCase();
  const typeF   =  document.getElementById('alTypeFilter')?.value || '';
  const dateFrom=  document.getElementById('alDateFrom')?.value  || '';
  const dateTo  =  document.getElementById('alDateTo')?.value    || '';
  const filtered=adminLog.filter(e=>{
    const txt=(e.adminName+e.action+e.detail).toLowerCase();
    const d=(e.timestamp||'').split(' ')[0];
    return(!q||txt.includes(q))&&(!typeF||e.type===typeF)&&(!dateFrom||d>=dateFrom)&&(!dateTo||d<=dateTo);
  });
  const countEl=document.getElementById('adminLogCount');
  if(countEl) countEl.textContent=filtered.length+' / '+adminLog.length+' entries';
  const TYPE_ICONS={login:'🔐',profile:'👤',bill:'💳',followup:'📞',admin:'⚙️',plan:'📦',setting:'🔧',expired:'⏰',story:'❤️',ban:'🚫'};
  const rows=filtered.map((e,i)=>{
    const icon=TYPE_ICONS[e.type]||'📌';
    const roleB=e.role==='super'?`<span class="role-badge role-super" style="font-size:11px">👑 Super</span>`:e.role==='manager'?`<span class="role-badge role-manager" style="font-size:11px">🛡 Mgr</span>`:`<span class="role-badge role-staff" style="font-size:11px">👤 Staff</span>`;
    const typeB=`<span class="badge al-${e.type}" style="font-size:11px">${icon} ${(e.type||'').charAt(0).toUpperCase()+(e.type||'').slice(1)}</span>`;
    const rowBg=e.type==='login'?'background:#fafffa':e.type==='ban'?'background:#fff8f8':'';
    return `<tr style="${rowBg}">
      <td style="font-size:12px;color:var(--text-secondary);text-align:center">${i+1}</td>
      <td><div class="name-cell"><div class="avatar" style="width:28px;height:28px;font-size:10px">${initials(e.adminName)}</div><span style="font-size:13px;font-weight:600">${e.adminName}</span></div></td>
      <td>${roleB}</td>
      <td style="font-size:13px;font-weight:500">${e.action}</td>
      <td style="font-size:12.5px;color:var(--text-secondary);max-width:220px">${e.detail}</td>
      <td>${typeB}</td>
      <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">🕐 ${e.timestamp}</td>
    </tr>`;
  });
  const np=paginate(rows,_pg.adminLog,PER_PAGE,'adminLogTable','adminLogPg',
    `<tr><td colspan="7"><div class="empty-state"><div class="icon">📋</div><p>No log entries match the filter</p></div></td></tr>`);
  if(np) _pg.adminLog=np;
}

function clearAdminLog() {
  if (!confirm('Clear all admin log entries? This cannot be undone.')) return;
  adminLog = [];
  saveState();
  renderAdminLog();
  toast('Admin log cleared');
}

function clearAlFilters() {
  ['alSearch','alDateFrom','alDateTo'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
  document.getElementById('alTypeFilter').value = '';
  renderAdminLog();
}

// ──────────────────────────────────────────────
// ALERT THRESHOLDS — loaded from API via loadAll()
// ──────────────────────────────────────────────

async function saveAlertThresholds() {
  alertThresholds.contactDay  = parseInt(document.getElementById('th_contactDay').value)  || 10;
  alertThresholds.otpDay      = parseInt(document.getElementById('th_otpDay').value)      || 3;
  alertThresholds.profileDay  = parseInt(document.getElementById('th_profileDay').value)  || 10;
  try {
    await apiPost('api/admin/settings.php', {
      section: 'alertThresholds', action: 'save',
      contact_day: alertThresholds.contactDay,
      otp_day: alertThresholds.otpDay,
      profile_day: alertThresholds.profileDay
    });
  } catch(e) { console.warn('Save thresholds error:', e); }
  renderAlerts();
  toast('Thresholds saved');
}

// loadAlertThresholds defined above

// ──────────────────────────────────────────────
// SMART ALERTS — auto-generated from usage data
// ──────────────────────────────────────────────
function buildAlerts() {
  const alerts = [];
  // Check each usage record
  usage.forEach(u => {
    const profile = profiles.find(p => p.mobile === u.mobile);
    const cpId    = profile ? profile.cpId : u.cpId || '—';
    const name    = profile ? profile.name : u.name  || '—';
    const plan    = profile ? profile.plan : u.plan;

    // 1. High contact views
    const cTotal = u.contactViews ? u.contactViews.length : (u.contact || 0);
    // Count today's contact views
    const todayStr = new Date().toISOString().split('T')[0];
    const cToday = u.contactViews
      ? u.contactViews.filter(v => (v.datetime||'').startsWith(todayStr)).length
      : 0;
    if (cToday >= alertThresholds.contactDay) {
      alerts.push({
        severity: cToday >= alertThresholds.contactDay * 2 ? 'high' : 'medium',
        mobile: u.mobile, cpId, name,
        reason: 'Excessive contact views today',
        value: cToday + ' today',
        threshold: alertThresholds.contactDay + '/day',
        type: 'contact'
      });
    }

    // 2. Free user high total contact views
    if (plan === 'free' && cTotal > alertThresholds.contactDay) {
      alerts.push({
        severity: 'medium',
        mobile: u.mobile, cpId, name,
        reason: 'Free user high total contacts viewed',
        value: cTotal + ' total',
        threshold: alertThresholds.contactDay + ' limit',
        type: 'contact'
      });
    }

    // 3. Free user profile view limit
    const pTotal = u.profileViews ? u.profileViews.length : (u.profile || 0);
    const pToday = u.profileViews
      ? u.profileViews.filter(v => (v.datetime||'').startsWith(todayStr)).length
      : 0;
    if (plan === 'free' && pToday >= alertThresholds.profileDay) {
      alerts.push({
        severity: 'low',
        mobile: u.mobile, cpId, name,
        reason: 'Free user profile view limit reached',
        value: pToday + ' today',
        threshold: alertThresholds.profileDay + '/day (free)',
        type: 'profile'
      });
    }
  });

  // 4. OTP abuse — high OTP request count
  otpLogs.forEach(o => {
    if (o.loginCount >= alertThresholds.otpDay * 3) {
      alerts.push({
        severity: o.loginCount >= 15 ? 'high' : 'medium',
        mobile: o.mobile, cpId: o.cpId || '—', name: o.name || '—',
        reason: 'High login count — possible shared account',
        value: o.loginCount + ' logins',
        threshold: alertThresholds.otpDay * 3 + '+ logins',
        type: 'otp'
      });
    }
  });

  // Sort: high → medium → low
  const order = { high:0, medium:1, low:2 };
  return alerts.sort((a,b) => order[a.severity] - order[b.severity]);
}

function renderAlerts() {
  loadAlertThresholds();
  const alerts = buildAlerts();

  // Update nav badge
  const badge = document.getElementById('alertBadge');
  if (badge) {
    badge.textContent = alerts.length;
    badge.style.display = alerts.length > 0 ? '' : 'none';
  }

  const countEl = document.getElementById('alertCount');
  if (countEl) countEl.textContent = alerts.length + ' alerts';

  // Stats row
  const statsEl = document.getElementById('alertStatsRow');
  if (statsEl) {
    const high   = alerts.filter(a=>a.severity==='high').length;
    const medium = alerts.filter(a=>a.severity==='medium').length;
    const low    = alerts.filter(a=>a.severity==='low').length;
    statsEl.innerHTML = `
      <div class="stat-card"><div class="stat-icon" style="background:#fee2e2;font-size:18px">🔴</div>
        <div class="stat-body"><div class="val" style="color:#dc2626">${high}</div><div class="lbl">High Severity</div></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#fff8f0;font-size:18px">🟠</div>
        <div class="stat-body"><div class="val" style="color:#d97706">${medium}</div><div class="lbl">Medium Severity</div></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#fefce8;font-size:18px">🟡</div>
        <div class="stat-body"><div class="val" style="color:#ca8a04">${low}</div><div class="lbl">Low Severity</div></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#eff6ff;font-size:18px">🛡</div>
        <div class="stat-body"><div class="val">${alerts.length}</div><div class="lbl">Total Alerts</div></div></div>`;
  }

  const tbody = document.getElementById('alertTable');
  if (!tbody) return;

  if (alerts.length === 0) {
    tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state">
      <div class="icon">✅</div><p>No suspicious activity detected — all clear!</p>
    </div></td></tr>`;
    return;
  }

  tbody.innerHTML = alerts.map((a, i) => {
    const sevBadge = a.severity === 'high'
      ? `<span class="badge sev-high">🔴 High</span>`
      : a.severity === 'medium'
      ? `<span class="badge sev-medium">🟠 Medium</span>`
      : `<span class="badge sev-low">🟡 Low</span>`;
    const isBanned = otpLogs.some(o => o.mobile === a.mobile && o.banned);
    const banBtn = isBanned
      ? `<span class="badge otp-banned" style="font-size:11px">🚫 Banned</span>`
      : `<button class="btn ban-btn btn-sm" onclick="quickBan('${a.mobile}')">🚫 Ban</button>`;
    return `<tr style="${a.severity==='high'?'background:#fff8f8':a.severity==='medium'?'background:#fffdf9':''}">
      <td style="font-size:12px;color:var(--text-secondary)">${i+1}</td>
      <td>${sevBadge}</td>
      <td style="font-weight:600">${a.mobile}</td>
      <td><code style="font-size:12px;background:#f3f4f6;padding:2px 7px;border-radius:5px">${a.cpId}</code></td>
      <td>${a.name !== '—' ? `<div class="name-cell"><div class="avatar" style="width:24px;height:24px;font-size:9px">${initials(a.name)}</div>${a.name}</div>` : '—'}</td>
      <td style="font-size:13px;font-weight:500">${a.reason}</td>
      <td><span class="badge badge-amber">${a.value}</span></td>
      <td style="font-size:12px;color:var(--text-secondary)">${a.threshold}</td>
      <td>${banBtn}</td>
    </tr>`;
  }).join('');
}

function quickBan(mobile) {
  const idx = otpLogs.findIndex(o => o.mobile === mobile);
  if (idx === -1) {
    // Add to OTP logs with banned status
    otpLogs.push({
      mobile, cpId:'', name:'', otpRequestedAt: nowStamp(),
      verified:'unverified', lastLogin:'—', loginCount:0, banned:true
    });
  } else {
    otpLogs[idx].banned = true;
  }
  pushAdminLog('Quick Ban', mobile + ' — banned from Alerts page', 'ban');
  pushNotif('🚫 User banned', mobile + ' has been banned via Alerts.');
  saveState();
  renderAlerts();
  renderOtp();
  toast('🚫 User ' + mobile + ' banned');
}

// ──────────────────────────────────────────────
// AUTO-FLAG SUSPICIOUS USERS in OTP table
// ──────────────────────────────────────────────
// Patch renderOtp to add suspicious flag badge
// (Suspicious: loginCount >= threshold*3 OR high contact views)
function isSuspicious(mobile) {
  const otp = otpLogs.find(o => o.mobile === mobile);
  if (otp && otp.loginCount >= alertThresholds.otpDay * 3) return true;
  const u = usage.find(u => u.mobile === mobile);
  if (u) {
    const todayStr = new Date().toISOString().split('T')[0];
    const cToday = u.contactViews
      ? u.contactViews.filter(v=>(v.datetime||'').startsWith(todayStr)).length
      : (u.contact || 0);
    if (cToday >= alertThresholds.contactDay) return true;
  }
  return false;
}

// ──────────────────────────────────────────────
// CONTACT VIEW LOG
// ──────────────────────────────────────────────
contactViewLog = [];

let contactViewLogTotals = [];
async function loadContactViewLog() {
  try {
    const resp = await apiGet('api/admin/settings.php?section=contactViewLog');
    if (resp.ok && resp.logs) contactViewLog = resp.logs;
    if (resp.ok && Array.isArray(resp.activity_totals)) contactViewLogTotals = resp.activity_totals;
  } catch(e) {}
}

function seedContactViewLog() {
  // now loaded via API in loadContactViewLog()
}

async function renderContactLog() {
  if (contactViewLog.length === 0) await loadContactViewLog();
  const q       = (document.getElementById('clSearch')?.value    || '').toLowerCase();
  const dateFrom=  document.getElementById('clDateFrom')?.value  || '';
  const dateTo  =  document.getElementById('clDateTo')?.value    || '';

  const filtered = contactViewLog.filter(r => {
    const txt = ((r.viewerMobile||r.mobile||'') + (r.viewerCpId||r.cp_id||'') + (r.viewedCpId||r.target_cp_id||'') + (r.viewerName||r.name||'') + (r.viewedName||r.target_name||'')).toLowerCase();
    const d   = (r.datetime || '').split(' ')[0];
    return (!q       || txt.includes(q))
        && (!dateFrom|| d >= dateFrom)
        && (!dateTo  || d <= dateTo);
  });

  const countEl = document.getElementById('clCount');
  if (countEl) countEl.textContent = filtered.length + ' records';

  const tbody = document.getElementById('contactLogTable');
  if (!tbody) return;
  if (filtered.length === 0) {
    // Surface the activity_type totals from the server so the admin can
    // tell whether 0 rows means "nobody has revealed a contact yet" or
    // "data exists in the table but isn't being surfaced". If contactViewLog
    // came back empty AND no contact_view rows exist in usage_activity at
    // all, the only honest message is the first one.
    const totalRows = contactViewLogTotals.reduce((s, r) => s + (parseInt(r.n,10)||0), 0);
    const cvRow = contactViewLogTotals.find(r => (r.activity_type||'').toLowerCase() === 'contact_view');
    const cvCount = cvRow ? (parseInt(cvRow.n,10)||0) : 0;
    let diag = '';
    if (totalRows > 0 || contactViewLogTotals.length > 0) {
      const breakdown = contactViewLogTotals.map(r => `${r.activity_type||'(blank)'}: ${r.n}`).join(' · ');
      diag = `<div style="margin-top:8px;font-size:12px;color:var(--text-secondary)">
                usage_activity totals — ${breakdown || 'none'}
                ${cvCount === 0 && (q || dateFrom || dateTo) ? '<br>Try clearing the search/date filters.' : ''}
              </div>`;
    }
    const empty = (q || dateFrom || dateTo)
      ? 'No records match the current filters.'
      : (cvCount === 0
          ? 'No contact reveals have been logged yet. Records will appear here as visitors reveal contacts.'
          : 'No contact view records found');
    tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state"><div class="icon">📞</div><p>${empty}</p>${diag}</div></td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map((r, i) => {
    // Normalize field names (API returns snake_case)
    const viewerMobile = r.viewerMobile || r.mobile || 'anonymous';
    const viewerCpId = r.viewerCpId || r.cp_id || '';
    const viewerName = r.viewerName || r.name || '—';
    const viewerPlan = r.viewerPlan || r.plan || 'free';
    const viewedCpId = r.viewedCpId || r.target_cp_id || '';
    const viewedName = r.viewedName || r.target_name || '—';
    const viewedMobile = r.viewedMobile || r.target_mobile || '—';
    const datetime = r.datetime || '';

    const susp = isSuspicious(viewerMobile);
    const suspFlag = susp ? `<span class="badge sev-high" style="font-size:10px;margin-left:4px">⚠️</span>` : '';
    const clProfileIdx = profiles.findIndex(p => p.mobile === viewerMobile);

    // ── Viewer CP ID: show "Visitor" if no CP ID ──
    const isVisitor = !viewerCpId || viewerCpId === '—' || viewerCpId === '';
    const cpIdCell = isVisitor
      ? `<span class="badge" style="background:#fff8f0;color:#d97706;font-size:11.5px">👤 Visitor</span>`
      : `<code style="font-size:12px;background:#f3f4f6;padding:2px 6px;border-radius:5px">${viewerCpId}</code>`;

    // ── Contact Type: show actual viewed mobile with follow-up colour logic ──
    const viewedProfileObj = profiles.find(p => p.cpId === viewedCpId);
    const viewedPIdx = viewedProfileObj ? profiles.indexOf(viewedProfileObj) : -1;
    const viewedMob = viewedMobile !== '—' ? viewedMobile : (viewedProfileObj ? viewedProfileObj.mobile : '—');
    let contactCell;
    if (viewedMob === '—') {
      contactCell = `<span style="color:var(--text-secondary);font-size:12px">—</span>`;
    } else {
      const viewedHasFU = viewedPIdx >= 0
        ? followUps.some(f => f.cpId === profiles[viewedPIdx]?.cpId)
        : followUps.some(f => f.mobile === viewedMob);
      if (viewedHasFU) {
        contactCell = `<span style="display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;color:#16a34a;
             border-radius:7px;padding:4px 10px;font-size:12.5px;font-weight:700;letter-spacing:.5px">
             📞 ${viewedMob}
           </span>`;
      } else if (viewedPIdx >= 0) {
        contactCell = `<span style="display:inline-flex;align-items:center;gap:5px;background:#fee2e2;color:#dc2626;
             border-radius:7px;padding:4px 10px;font-size:12.5px;font-weight:700;letter-spacing:.5px;cursor:pointer"
             title="No follow-up — double-click to create" ondblclick="openFollowUp(${viewedPIdx})">
             📞 ${viewedMob}
           </span>`;
      } else {
        contactCell = `<span style="display:inline-flex;align-items:center;gap:5px;background:#fee2e2;color:#dc2626;
             border-radius:7px;padding:4px 10px;font-size:12.5px;font-weight:700;letter-spacing:.5px;cursor:pointer"
             title="No follow-up — double-click to create" ondblclick="openFollowUpByMobile('${viewedMob}','${(viewedName||'').replace(/'/g,"\\'")}')">
             📞 ${viewedMob}
           </span>`;
      }
    }

    // Follow-up check for viewer mobile: black if exists, red if not
    const clHasFU = clProfileIdx >= 0
      ? followUps.some(f => f.cpId === profiles[clProfileIdx]?.cpId)
      : followUps.some(f => f.mobile === viewerMobile);
    let clMobileCell;
    if (clHasFU) {
      clMobileCell = `<span style="font-size:13px;font-weight:500;color:var(--text-primary)">${viewerMobile}</span>`;
    } else if (clProfileIdx >= 0) {
      clMobileCell = `<span style="font-size:13px;font-weight:700;color:#dc2626;cursor:pointer;border-bottom:1.5px dashed #dc2626" title="No follow-up — double-click to create" ondblclick="openFollowUp(${clProfileIdx})">${viewerMobile}</span>`;
    } else {
      clMobileCell = `<span style="font-size:13px;font-weight:700;color:#dc2626;cursor:pointer;border-bottom:1.5px dashed #dc2626" title="No follow-up — double-click to create" ondblclick="openFollowUpByMobile('${viewerMobile}','${(viewerName||'').replace(/'/g,"\\'")}')">${viewerMobile}</span>`;
    }

    return `<tr style="${susp?'background:#fff8f8':''}" >
      <td style="font-size:12px;color:var(--text-secondary)">${i+1}</td>
      <td>${clMobileCell}${suspFlag}</td>
      <td>${cpIdCell}</td>
      <td>${planBadge(viewerPlan)}</td>
      <td style="font-size:13px">${viewedName}</td>
      <td><code style="font-size:12px;background:#f3f4f6;padding:2px 6px;border-radius:5px">${viewedCpId}</code></td>
      <td>${contactCell}</td>
      <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap">🕐 ${datetime}</td>
      <td>
        <div class="watermark-preview" data-wm="${viewerMobile}" title="Watermark">
          ${viewedMob !== '—' ? viewedMob : viewedCpId}
        </div>
      </td>
    </tr>`;
  }).join('');
}

function clearClFilters() {
  ['clSearch','clDateFrom','clDateTo'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  renderContactLog();
}

// ──────────────────────────────────────────────
// FREE PROFILE VIEW LIMIT ENFORCER
// ──────────────────────────────────────────────
function checkFreeViewLimit(mobile) {
  // Returns true if user has hit the free daily limit
  const u = usage.find(u => u.mobile === mobile);
  if (!u) return false;
  const viewer = profiles.find(p => p.mobile === mobile);
  if (!viewer || viewer.plan !== 'free') return false;
  const todayStr = new Date().toISOString().split('T')[0];
  const pToday = u.profileViews
    ? u.profileViews.filter(v=>(v.datetime||'').startsWith(todayStr)).length
    : (u.profile || 0);
  return pToday >= alertThresholds.profileDay;
}

// ──────────────────────────────────────────────
// WIRE show() FOR NEW PAGES
// ──────────────────────────────────────────────
// ROLE PERMISSIONS
// ──────────────────────────────────────────────
const PERM_MENUS = [
  { section:'📋 Main', items:[
    { id:'view_profiles',   label:'Profiles' },
    { id:'view_manage',     label:'Manage' },
    { id:'add_profile',     label:'Add Profile' },
    { id:'create_bill',     label:'Add Order' },
  ]},
  { section:'📞 Operations', items:[
    { id:'view_followups',  label:'Follow-ups' },
    { id:'view_bills',      label:'Bills' },
    { id:'view_usage',      label:'Usage' },
    { id:'view_reports',    label:'Interest Patterns' },
  ]},
  { section:'📊 Insights', items:[
    { id:'view_reports',    label:'Reports' },
    { id:'view_notifs',     label:'Notifications' },
    { id:'view_stories',    label:'Success Stories' },
  ]},
  { section:'🔐 Security', items:[
    { id:'view_otp',        label:'OTP Logs' },
    { id:'view_alerts',     label:'Alerts' },
    { id:'view_contactlog', label:'Contact Log' },
  ]},
  { section:'⚙️ Config', items:[
    { id:'view_settings',   label:'Settings' },
    { id:'view_deleted',    label:'Deleted' },
    { id:'view_expired',    label:'Expired' },
  ]},
];

// Action Controls — granular actions within pages
const ACTION_PERMS = [
  { section:'👤 Profile Actions', items:[
    { id:'act_add_profile',     label:'Add New Profile' },
    { id:'act_edit_profile',    label:'Edit Profile' },
    { id:'act_delete_profile',  label:'Delete Profile' },
    { id:'act_approve_profile', label:'Approve / Revert Profile' },
    { id:'act_print_profile',   label:'Print Profile' },
    { id:'act_export_profiles', label:'Export Profiles CSV' },
  ]},
  { section:'💳 Billing Actions', items:[
    { id:'act_create_bill',   label:'Create Bill / Order' },
    { id:'act_edit_bill',     label:'Edit Bill' },
    { id:'act_delete_bill',   label:'Delete Bill' },
    { id:'act_view_bill_history', label:'View Bill History' },
  ]},
  { section:'📞 Follow-up Actions', items:[
    { id:'act_add_followup',  label:'Add Follow-up' },
    { id:'act_edit_followup', label:'Edit Follow-up' },
    { id:'act_close_followup',label:'Close Follow-up' },
    { id:'act_expire_profile',label:'Expire Profile' },
  ]},
  { section:'🔐 User & Security Actions', items:[
    { id:'act_ban_user',       label:'Ban / Unban Users' },
    { id:'act_view_contact',   label:'View Contact Details' },
    { id:'act_send_otp',       label:'Send OTP to Users' },
    { id:'act_change_mobile',  label:'Approve Mobile Change' },
  ]},
  { section:'⚙️ Settings Actions', items:[
    { id:'act_manage_plans',    label:'Create / Edit Plans' },
    { id:'act_manage_admins',   label:'Add / Edit Admin Accounts' },
    { id:'act_manage_payments', label:'Manage Payment Options' },
    { id:'act_manage_restrict', label:'Edit Restrictions' },
    { id:'act_manage_roles',    label:'Edit Role Permissions' },
    { id:'act_manage_panel',    label:'User Panel Control' },
  ]},
  { section:'🗑️ Archive Actions', items:[
    { id:'act_restore_deleted', label:'Restore Deleted Profile' },
    { id:'act_restore_expired', label:'Restore Expired Profile' },
    { id:'act_permanent_delete',label:'Permanent Delete' },
  ]},
  { section:'📊 Data & Reports', items:[
    { id:'act_export_data',    label:'Export CSV / Data' },
    { id:'act_view_reports',   label:'View Reports & Analytics' },
    { id:'act_add_story',      label:'Add Success Story' },
    { id:'act_edit_story',     label:'Edit / Delete Story' },
  ]},
];

// All perm IDs — sidebar menus + action controls
const ALL_PERM_IDS = [
  ...PERM_MENUS.flatMap(s => s.items.map(i => i.id)),
  ...ACTION_PERMS.flatMap(s => s.items.map(i => i.id)),
];

// Default permissions per role
const STAFF_ACTIONS = ['act_view_contact','act_add_followup','act_print_profile'];
const MANAGER_DENY  = ['act_manage_admins','act_manage_roles','act_permanent_delete','view_settings','view_deleted','view_expired'];
const DEFAULT_PERMS = {
  super:   Object.fromEntries(ALL_PERM_IDS.map(id => [id, true])),
  manager: Object.fromEntries(ALL_PERM_IDS.map(id => [id, !MANAGER_DENY.includes(id)])),
  staff:   Object.fromEntries(ALL_PERM_IDS.map(id => [id,
    ['view_profiles','view_manage','view_followups','view_bills','view_notifs', ...STAFF_ACTIONS].includes(id)
  ])),
};

rolePerms = {
  super:   { ...DEFAULT_PERMS.super },
  manager: { ...DEFAULT_PERMS.manager },
  staff:   { ...DEFAULT_PERMS.staff },
};
activeRoleTab = 'super';

// loadRolePerms defined above

function saveRolePerms() {
  /* persisted via API */
  pushAdminLog('Updated Role Permissions', activeRoleTab + ' role permissions saved', 'setting');
  saveState();
  toast('✅ Permissions saved for ' + activeRoleTab);
}

function resetRolePerms() {
  rolePerms[activeRoleTab] = { ...DEFAULT_PERMS[activeRoleTab] };
  renderPermGrid();
  toast('Role reset to defaults');
}

function selectAllPerms() {
  if (activeRoleTab === 'super') return;
  ALL_PERM_IDS.forEach(id => rolePerms[activeRoleTab][id] = true);
  renderPermGrid();
}
function clearAllPerms() {
  if (activeRoleTab === 'super') return;
  ALL_PERM_IDS.forEach(id => rolePerms[activeRoleTab][id] = false);
  renderPermGrid();
}

function selectRoleTab(role, btn) {
  activeRoleTab = role;
  document.querySelectorAll('.role-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const note = document.getElementById('roleDetailsNote');
  if (note) note.style.display = role === 'super' ? '' : 'none';
  // Ensure defaults exist for this role
  if (!rolePerms[role]) rolePerms[role] = { ...DEFAULT_PERMS[role] };
  renderPermGrid();
}

function togglePerm(id) {
  if (activeRoleTab === 'super') return;
  if (!rolePerms[activeRoleTab]) rolePerms[activeRoleTab] = {};
  rolePerms[activeRoleTab][id] = !rolePerms[activeRoleTab][id];
  renderPermGrid();
}

function renderPermGrid() {
  const grid = document.getElementById('permGrid');
  if (!grid) return;
  const isSuper = activeRoleTab === 'super';
  const perms   = rolePerms[activeRoleTab] || {};

  const renderSection = (sections) => sections.map(section => `
    <div class="perm-section">
      <div class="perm-section-title">${section.section}</div>
      <div class="perm-grid">
        ${section.items.map(item => {
          const checked = isSuper ? true : !!perms[item.id];
          return `
          <div class="perm-row" onclick="${isSuper ? '' : "togglePerm('"+item.id+"')"}">
            <div class="perm-checkbox ${checked?'checked':''} ${isSuper?'disabled':''}" id="pbox_${item.id}"></div>
            <span class="perm-label ${isSuper?'disabled':''}">${item.label}</span>
          </div>`;
        }).join('')}
      </div>
    </div>`).join('');

  grid.innerHTML = renderSection(PERM_MENUS);

  // Render Action Controls
  const actionGrid = document.getElementById('actionPermGrid');
  if (actionGrid) actionGrid.innerHTML = renderSection(ACTION_PERMS);
}

// ──────────────────────────────────────────────
// ADMIN LOGIN SYSTEM
// ──────────────────────────────────────────────
loginAdminObj = null;
loginOtp      = null;
otpTimerInt   = null;
loginAttempts = 0;

function showLoginStep(n) {
  document.querySelectorAll('.login-step').forEach(s => s.classList.remove('active'));
  document.getElementById('loginStep' + n).classList.add('active');
  hideLoginMsg();
}

// ── Login popup notifications ──
let loginPopupTimer = null;
function showLoginPopup(type, title, msg, duration=5000) {
  const popup = document.getElementById('loginPopup');
  document.getElementById('lpTitle').textContent = title;
  document.getElementById('lpMsg').textContent   = msg;
  const icons = { error:'🚫', warning:'⚠️', success:'✅' };
  document.getElementById('lpIcon').textContent  = icons[type] || '💬';
  popup.className = 'login-popup login-popup-' + type;
  // Force reflow then show
  popup.classList.remove('show');
  void popup.offsetWidth;
  popup.classList.add('show');
  clearTimeout(loginPopupTimer);
  if (duration > 0) {
    loginPopupTimer = setTimeout(hideLoginPopup, duration);
  }
}
function hideLoginPopup() {
  const popup = document.getElementById('loginPopup');
  if (popup) popup.classList.remove('show');
  clearTimeout(loginPopupTimer);
}

function showLoginErr(msg) {
  // Also show inline for accessibility
  const el = document.getElementById('loginError');
  if (el) { el.textContent = msg; el.style.display = ''; }
  const el2 = document.getElementById('loginSuccess');
  if (el2) el2.style.display = 'none';
  // Show popup
  showLoginPopup('error', 'Login Failed', msg);
}
function showLoginOk(msg) {
  const el = document.getElementById('loginSuccess');
  if (el) { el.textContent = msg; el.style.display = ''; }
  const el2 = document.getElementById('loginError');
  if (el2) el2.style.display = 'none';
  showLoginPopup('success', 'OTP Ready', msg, 0); // 0 = keep visible until dismissed
}
function hideLoginMsg() {
  const el1 = document.getElementById('loginError');
  const el2 = document.getElementById('loginSuccess');
  if (el1) el1.style.display = 'none';
  if (el2) el2.style.display = 'none';
  hideLoginPopup();
}

function generateOtp() { /* no-op: replaced by API */ }

function startOtpTimer(seconds) {
  clearInterval(otpTimerInt);
  const timerEl  = document.getElementById('otpTimer');
  const resendEl = document.getElementById('resendBtn');
  resendEl.style.display = 'none';
  let remaining = seconds;
  function tick() {
    if (remaining <= 0) {
      clearInterval(otpTimerInt);
      timerEl.textContent  = 'OTP expired.';
      resendEl.style.display = '';
      return;
    }
    timerEl.textContent = 'OTP expires in ' + remaining + 's';
    remaining--;
  }
  tick();
  otpTimerInt = setInterval(tick, 1000);
}

// ══════════════════════════════════════════════════════
// ADMIN LOGIN - async, calls API
// ══════════════════════════════════════════════════════
async function doLogin() {
  hideLoginMsg();
  const username = document.getElementById('lg_user').value.trim();
  const password = document.getElementById('lg_pass').value;
  if (!username || !password) {
    showLoginPopup('warning', 'Fields Required', 'Please enter both username and password.');
    return;
  }
  try {
    const data = await apiPost('api/admin/auth.php', { action: 'login', username, password });

    // Direct login (no mobile on admin account)
    if (data.direct && data.admin) {
      loginAdminObj = data.admin;
      await completeLogin();
      return;
    }

    // OTP required
    if (data.needOtp) {
      loginAdminObj = { name: data.name, mobile: data.mobile };
      loginOtp = data.otp || null;
      showLoginStep(2);
      let otpHtml = 'OTP sent to ••••••' + (data.mobile || '').slice(-4);
      if (data.otp) {
        otpHtml += '<br><div style="margin-top:10px;background:#f0fdf4;border:1.5px dashed #86efac;border-radius:10px;padding:8px 12px;text-align:center">'
          + '<div style="font-size:10px;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:.1em;margin-bottom:3px">Your OTP</div>'
          + '<div style="font-size:28px;font-weight:800;letter-spacing:6px;color:#1a1a2e;font-family:monospace">' + data.otp + '</div></div>';
      } else {
        otpHtml += '<br><div style="margin-top:8px;font-size:12.5px;color:#16a34a">Check your mobile for the OTP</div>';
      }
      document.getElementById('otpSentTo').innerHTML = otpHtml;
      startOtpTimer(120);
      ['otp1','otp2','otp3','otp4'].forEach(id => {
        const el = document.getElementById(id); if (el) el.value = '';
      });
      setTimeout(() => document.getElementById('otp1')?.focus(), 100);
      return;
    }

    // Fallback — old direct login
    loginAdminObj = data.admin || {};
    await completeLogin();
  } catch(e) {
    loginAttempts++;
    showLoginPopup('error', 'Login Failed', e.message || 'Invalid credentials.');
  }
}

async function verifyOtp() {
  hideLoginMsg();
  const digits = ['otp1','otp2','otp3','otp4']
    .map(id => (document.getElementById(id)?.value || '').trim()).join('');
  if (digits.length < 4) {
    showLoginPopup('warning', 'Incomplete OTP', 'Please enter all 4 digits.');
    return;
  }
  let verifyData;
  try {
    verifyData = await apiPost('api/admin/auth.php', { action: 'verify', otp: digits });
  } catch(e) {
    const boxes = document.getElementById('otpBoxes') || document.querySelector('.otp-inputs');
    if (boxes) { boxes.classList.remove('shake'); void boxes.offsetWidth; boxes.classList.add('shake'); }
    showLoginPopup('error', 'Incorrect OTP', e.message || 'Wrong OTP entered.');
    ['otp1','otp2','otp3','otp4'].forEach(id => {
      const el = document.getElementById(id); if (el) el.value = '';
    });
    document.getElementById('otp1')?.focus();
    return;
  }
  clearInterval(otpTimerInt);
  loginAdminObj = verifyData.admin || loginAdminObj;
  try { await completeLogin(); } catch(e) { console.warn('Post-login render:', e); }
}

async function resendOtp() {
  try {
    const username = document.getElementById('lg_user').value.trim();
    const password = document.getElementById('lg_pass').value;
    const data = await apiPost('api/admin/auth.php', { action: 'login', username, password });
    loginOtp = data.otp || null;
    let otpHtml = 'OTP sent to ••••••' + (data.mobile || '').slice(-4);
    if (data.otp) {
      otpHtml += '<br><div style="margin-top:10px;background:#f0fdf4;border:1.5px dashed #86efac;border-radius:8px;padding:8px 12px;text-align:center">' +
        '<div style="font-size:10px;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:.1em;margin-bottom:3px">Your OTP</div>' +
        '<div style="font-size:28px;font-weight:800;letter-spacing:6px;color:#1a1a2e;font-family:monospace">' + data.otp + '</div></div>';
    } else {
      otpHtml += '<br><div style="margin-top:8px;font-size:12.5px;color:#16a34a">Check your mobile for the OTP</div>';
    }
    document.getElementById('otpSentTo').innerHTML = otpHtml;
    ['otp1','otp2','otp3','otp4'].forEach(id => {
      const el = document.getElementById(id); if (el) el.value = '';
    });
    startOtpTimer(120);
    document.getElementById('otp1')?.focus();
  } catch(e) {
    showLoginPopup('error', 'Resend Failed', e.message);
  }
}

function showForgot() { hideLoginMsg(); showLoginStep(3); }

async function doForgotPassword() {
  hideLoginMsg();
  const username = document.getElementById('lg_fp_user').value.trim();
  const mobile   = document.getElementById('lg_fp_mobile').value.trim();
  if (!username || !mobile) {
    showLoginPopup('warning', 'Fields Required', 'Please enter both username and mobile.');
    return;
  }
  try {
    const data = await apiPost('api/admin/auth.php', { action: 'forgot', username, mobile });
    loginAdminObj = { name: data.name, mobile: data.mobile };
    loginOtp = data.otp || null;
    showLoginStep(2);
    let resetHtml = 'Reset OTP sent to ••••••' + (data.mobile || '').slice(-4);
    if (data.otp) {
      resetHtml += '<br><div style="margin-top:10px;background:#fff8f0;border:1.5px dashed #fde68a;border-radius:8px;padding:8px 12px;text-align:center">' +
        '<div style="font-size:10px;font-weight:700;color:#d97706;text-transform:uppercase;letter-spacing:.1em;margin-bottom:3px">Reset OTP</div>' +
        '<div style="font-size:28px;font-weight:800;letter-spacing:6px;color:#1a1a2e;font-family:monospace">' + data.otp + '</div></div>';
    } else {
      resetHtml += '<br><div style="margin-top:8px;font-size:12.5px;color:#d97706">Check your mobile for the reset OTP</div>';
    }
    document.getElementById('otpSentTo').innerHTML = resetHtml;
    startOtpTimer(120);
    ['otp1','otp2','otp3','otp4'].forEach(id => {
      const el = document.getElementById(id); if (el) el.value = '';
    });
    setTimeout(() => document.getElementById('otp1')?.focus(), 100);
  } catch(e) {
    showLoginPopup('error', 'Not Found', e.message);
  }
}

async function completeLogin() {
  const lp = document.getElementById('loginPage');
  if (lp) lp.style.display = 'none';
  const shell = document.getElementById('appShell');
  if (shell) shell.style.display = '';
  const hb = document.getElementById('hamburger');
  if (hb) hb.style.display = '';
  // Update sidebar admin info FIRST (before render calls that might error)
  const aName = loginAdminObj?.name || 'Admin';
  const aRole = loginAdminObj?.role || 'staff';
  const nameEl = document.getElementById('sidebarAdminName');
  const roleEl = document.getElementById('sidebarAdminRole');
  const avatarEl = document.getElementById('sidebarAdminAvatar');
  if (nameEl) nameEl.textContent = aName;
  if (roleEl) roleEl.textContent = aRole === 'super' ? 'Super Admin' : aRole === 'manager' ? 'Manager' : 'Staff';
  if (avatarEl) avatarEl.textContent = aName.charAt(0).toUpperCase();
  const mobEl = document.getElementById('sidebarAdminMobile');
  if (mobEl) mobEl.textContent = loginAdminObj?.mobile ? '📱 ' + loginAdminObj.mobile : '';

  // Load all data from backend
  await loadAll();
  // Set logged-in admin from loaded admins list
  if (loginAdminObj) {
    const a = admins.find(a => a.username === loginAdminObj.username || a.name === loginAdminObj.name);
    if (a) { loginAdminObj = a; if (nameEl) nameEl.textContent = a.name; }
  }
  // Render everything (errors won't block sidebar/role setup)
  try {
    render(); postRender(); initPaginationBars();
    updateProfilePagination();
    renderAdmins(); renderNotifications(); renderRestrictions();
    renderOtp(); renderBills(); renderBillHistory();
    renderExpired(); renderAdminLog(); renderAlerts();
    renderCustomPlans(); renderPlanHistory(); resetPlanForm(); renderPaymentOptions();
    loadMessages();
  } catch(e) { console.warn('Render error:', e); }

  // Apply role-based restrictions
  applyRoleRestrictions();
  toast('Welcome, ' + aName + '!');
}

// Apply role-based restrictions to sidebar and action buttons
function applyRoleRestrictions() {
  const role = loginAdminObj?.role || 'staff';
  // Super admin has full access
  if (role === 'super') {
    document.querySelectorAll('.nav-btn[data-perm]').forEach(btn => btn.style.display = '');
    document.querySelectorAll('.nav-section-label').forEach(lbl => lbl.style.display = '');
    return;
  }

  const perms = rolePerms[role] || {};

  // Helper: check if a permission is granted
  function hasPerm(id) {
    return !!perms[id];
  }

  // Hide/show sidebar buttons based on permissions
  document.querySelectorAll('.nav-btn[data-perm]').forEach(btn => {
    const perm = btn.getAttribute('data-perm');
    btn.style.display = hasPerm(perm) ? '' : 'none';
  });

  // Hide section labels if all their children are hidden
  document.querySelectorAll('.nav-section-label').forEach(lbl => {
    let next = lbl.nextElementSibling;
    let anyVisible = false;
    while (next && !next.classList.contains('nav-section-label')) {
      if (next.classList.contains('nav-btn') && next.style.display !== 'none') {
        anyVisible = true;
        break;
      }
      next = next.nextElementSibling;
    }
    lbl.style.display = anyVisible ? '' : 'none';
  });

  // If current active section is hidden, switch to first visible
  const activeBtn = document.querySelector('.nav-btn.active');
  if (activeBtn && activeBtn.style.display === 'none') {
    const firstVisible = document.querySelector('.nav-btn[data-perm]:not([style*="display: none"])');
    if (firstVisible) firstVisible.click();
  }

  // Hide action buttons (Edit, Delete, Add Profile, Export) based on perms
  document.querySelectorAll('[data-action-perm]').forEach(el => {
    el.style.display = hasPerm(el.getAttribute('data-action-perm')) ? '' : 'none';
  });
}

function otpMove(el, nextId) {
  // Keep only the last digit typed
  el.value = el.value.replace(/\D/g, '').slice(-1);
  if (el.value && nextId) {
    document.getElementById(nextId)?.focus();
  }
  // Check if all 6 filled — auto highlight Verify button
  const all = ['otp1','otp2','otp3','otp4'].map(id => document.getElementById(id)?.value || '').join('');
  const btn = document.querySelector('#loginStep2 .login-btn');
  if (btn) btn.style.opacity = all.length === 4 ? '1' : '0.7';
}

function otpBack(e, el, prevId) {
  if (e.key === 'Backspace' && !el.value && prevId) {
    document.getElementById(prevId)?.focus();
  }
}

// Allow pasting full 6-digit OTP into first box
document.addEventListener('paste', function(e) {
  const step2 = document.getElementById('loginStep2');
  if (!step2 || !step2.classList.contains('active')) return;
  const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,4);
  if (pasted.length === 4) {
    e.preventDefault();
    ['otp1','otp2','otp3','otp4'].forEach((id, i) => {
      const el = document.getElementById(id);
      if (el) el.value = pasted[i];
    });
    document.getElementById('otp4')?.focus();
  }
});

// verifyOtp, resendOtp, showForgot, doForgotPassword, completeLogin
// are defined above as async functions that call the API

// ──────────────────────────────────────────────
// INTEREST PATTERN ANALYSIS ENGINE
// ──────────────────────────────────────────────

// Score weights
const SCORE_WEIGHTS = {
  timeSpent:    0.40,  // 40% — seconds spent on profile
  scrollDepth:  0.25,  // 25% — how far they scrolled
  contactViewed:0.25,  // 25% — requested contact (strongest intent)
  repeatViews:  0.10   // 10% — revisited same profile
};

// Max normalisation values
const MAX_TIME = 400;   // cap at 400 seconds
const MAX_SCROLL = 100; // 0–100%

// ── Step 1: Score each (user → viewed profile) engagement ──
function scoreEngagement(viewerMobile) {
  const u = usage.find(u => u.mobile === viewerMobile);
  if (!u || !u.profileViews || u.profileViews.length === 0) return [];

  // Group views by cpId — count repeats
  const viewMap = {};
  u.profileViews.forEach(v => {
    if (!viewMap[v.cpId]) viewMap[v.cpId] = { cpId:v.cpId, views:[], contactViewed:false };
    viewMap[v.cpId].views.push(v);
  });
  (u.contactViews || []).forEach(v => {
    if (viewMap[v.cpId]) viewMap[v.cpId].contactViewed = true;
  });

  const scored = [];
  Object.values(viewMap).forEach(entry => {
    const vs = entry.views;
    // Use best (highest time) view for time + scroll
    const best = vs.reduce((a,b) => ((b.timeSpent||0) > (a.timeSpent||0) ? b : a), vs[0]);
    const timeSpent  = Math.min(best.timeSpent  || 0, MAX_TIME);
    const scrollDepth= best.scrollDepth || 0;
    const repeatBonus= Math.min(vs.length - 1, 5) / 5; // 0–1, caps at 5 repeats

    const score =
      (timeSpent   / MAX_TIME)  * SCORE_WEIGHTS.timeSpent   * 100 +
      (scrollDepth / MAX_SCROLL)* SCORE_WEIGHTS.scrollDepth * 100 +
      (entry.contactViewed ? 1 : 0) * SCORE_WEIGHTS.contactViewed * 100 +
      repeatBonus             * SCORE_WEIGHTS.repeatViews   * 100;

    scored.push({
      cpId:          entry.cpId,
      score:         Math.round(score * 10) / 10,
      timeSpent,
      scrollDepth,
      contactViewed: entry.contactViewed,
      viewCount:     vs.length,
      views:         vs
    });
  });
  return scored.sort((a,b) => b.score - a.score);
}

// ── Step 2: Extract attribute clusters from high-scoring profiles ──
function extractAttributes(scoredList) {
  // Only take profiles with score ≥ median
  const totalScore = scoredList.reduce((s, x) => s + x.score, 0);
  const attrs = {};  // { attrKey: { label, totalScore, count } }

  scoredList.forEach(entry => {
    const p = profiles.find(pr => pr.cpId === entry.cpId);
    if (!p) return;
    const w = entry.score; // use score as weight

    const add = (key, label) => {
      if (!attrs[key]) attrs[key] = { label, totalScore:0, count:0 };
      attrs[key].totalScore += w;
      attrs[key].count++;
    };

    // Gender
    add('gender_' + p.gender, p.gender);

    // Age bucket
    const age = parseInt(p.age);
    const ageBucket = age <= 22 ? '18-22'
                    : age <= 26 ? '23-26'
                    : age <= 30 ? '27-30'
                    : age <= 35 ? '31-35' : '36+';
    add('age_' + ageBucket, 'Age ' + ageBucket);

    // Qualification
    if (p.qualification) add('qual_' + p.qualification, p.qualification);

    // Star
    if (p.star) add('star_' + p.star, 'Star: ' + p.star);

    // Religion
    if (p.religion) add('rel_' + p.religion, p.religion);

    // Caste
    if (p.caste) add('caste_' + p.caste, p.caste);
  });

  // Normalise to 0–100
  return Object.values(attrs)
    .map(a => ({ ...a, pct: Math.round((a.totalScore / (totalScore || 1)) * 100) }))
    .sort((a,b) => b.pct - a.pct);
}

// ── Step 3: Build top-3 interest patterns per user ──
function buildPatterns(attrs, scoredList) {
  // Pattern = a cohesive cluster of the highest-scoring attributes
  // We build 3 patterns by grouping: primary (gender+age+qual), secondary (star+caste), tertiary (other combos)

  const get = (prefix) => attrs
    .filter(a => a.label.startsWith(prefix.replace('_','')) || attrs.find(x => x === a)?.label?.includes(prefix))
    .sort((a,b) => b.pct - a.pct);

  // Pull top attrs by category
  const topGender = attrs.filter(a => a.label === 'Male' || a.label === 'Female').sort((a,b)=>b.pct-a.pct);
  const topAge    = attrs.filter(a => a.label.startsWith('Age ')).sort((a,b)=>b.pct-a.pct);
  const topQual   = attrs.filter(a => ['B.E','M.E','M.Sc','B.Tech','M.B.A','B.Sc','M.C.A'].includes(a.label)).sort((a,b)=>b.pct-a.pct);
  const topStar   = attrs.filter(a => a.label.startsWith('Star:')).sort((a,b)=>b.pct-a.pct);
  const topCaste  = attrs.filter(a => ['Mudaliar','Nadar','Pillai','Gounder','Thevar','Chettiar'].includes(a.label)).sort((a,b)=>b.pct-a.pct);

  // Contact-viewed profiles — strongest intent signal
  const contactCpIds = scoredList.filter(s => s.contactViewed).map(s => s.cpId);
  const contactProfiles = contactCpIds.map(id => profiles.find(p => p.cpId === id)).filter(Boolean);

  function buildDesc(gender, age, qual, star, caste) {
    const parts = [];
    if (gender) parts.push(gender);
    if (age)    parts.push(age.replace('Age ','Age '));
    if (qual)   parts.push(qual);
    if (caste)  parts.push(caste);
    if (star)   parts.push(star);
    return parts.join(' · ');
  }

  // Pattern 1 — dominant: top gender + top age + top qual
  const p1gender = topGender[0]?.label || '';
  const p1age    = topAge[0]?.label || '';
  const p1qual   = topQual[0]?.label || '';
  const p1star   = topStar[0]?.label || '';
  const p1caste  = topCaste[0]?.label || '';
  const p1pct    = Math.round(((topGender[0]?.pct||0)+(topAge[0]?.pct||0)+(topQual[0]?.pct||0))/3 * 1.1);

  // Pattern 2 — secondary star/caste combo with alternate qual
  const p2gender = p1gender;
  const p2age    = topAge[1]?.label || topAge[0]?.label || '';
  const p2qual   = topQual[1]?.label || topQual[0]?.label || '';
  const p2star   = topStar[1]?.label || topStar[0]?.label || '';
  const p2caste  = topCaste[1]?.label || topCaste[0]?.label || '';
  const p2pct    = Math.round(((topGender[0]?.pct||0)+(topAge[1]?.pct||topAge[0]?.pct||0)+(topQual[1]?.pct||0))/3 * 0.85);

  // Pattern 3 — tertiary: what contact-viewed profiles have in common
  let p3desc = '';
  let p3pct  = 0;
  if (contactProfiles.length >= 2) {
    const genders = [...new Set(contactProfiles.map(p=>p.gender))];
    const stars   = [...new Set(contactProfiles.map(p=>p.star).filter(Boolean))];
    const quals   = [...new Set(contactProfiles.map(p=>p.qualification).filter(Boolean))];
    const ages    = contactProfiles.map(p=>parseInt(p.age));
    const ageMin  = Math.min(...ages), ageMax = Math.max(...ages);
    const parts   = [];
    if (genders.length === 1) parts.push(genders[0]);
    parts.push('Age ' + ageMin + '-' + ageMax);
    if (quals.length <= 2) parts.push(quals.join('/'));
    if (stars.length <= 2) parts.push(stars.join('/'));
    p3desc = parts.join(' · ');
    p3pct  = Math.round(p1pct * 0.45);
  } else {
    p3desc = buildDesc(p1gender, topAge[1]?.label || p1age, topQual[2]?.label || p1qual, topStar[1]?.label || '', topCaste[1]?.label || '');
    p3pct  = Math.round(p1pct * 0.4);
  }

  return [
    { rank:1, desc: buildDesc(p1gender, p1age, p1qual, p1star, p1caste), pct: Math.min(p1pct, 95) },
    { rank:2, desc: buildDesc(p2gender, p2age, p2qual, p2star, p2caste), pct: Math.min(p2pct, 75) },
    { rank:3, desc: p3desc || buildDesc(p1gender, p1age, p1qual, topStar[1]?.label||'', topCaste[1]?.label||''), pct: Math.min(p3pct, 55) }
  ];
}

// ── Step 4: Detect session style ──
function sessionStyle(u) {
  if (!u.profileViews || u.profileViews.length === 0) return 'Inactive';
  const hours = u.profileViews.map(v => parseInt((v.datetime||'').split(' ')[1]?.split(':')[0]||12));
  const avgHour = hours.reduce((a,b)=>a+b,0)/hours.length;
  const avgTime = u.profileViews.reduce((a,v)=>a+(v.timeSpent||0),0)/u.profileViews.length;
  const contactRate = (u.contactViews?.length||0) / u.profileViews.length;

  if (contactRate > 0.5) return '🎯 High Intent';
  if (avgTime > 200)     return '📖 Deep Reader';
  if (avgHour < 10)      return '🌅 Early Browser';
  if (avgHour > 18)      return '🌙 Night Browser';
  if (u.profileViews.length > 10) return '🔍 Active Searcher';
  return '👀 Casual Viewer';
}

// ── Main render function ──
function renderInterest() {
  const q       = (document.getElementById('interestSearch')?.value || '').trim();
  const minViews = parseInt(document.getElementById('interestMinViews')?.value || '3');

  // Only users with enough data
  const eligible = usage.filter(u =>
    (u.profileViews || []).length >= minViews &&
    u.mobile !== u.cpId &&  // exclude self-loops
    (!q || u.mobile.includes(q) || (u.name||'').toLowerCase().includes(q.toLowerCase()))
  );

  const countEl = document.getElementById('interestCount');
  if (countEl) countEl.textContent = eligible.length + ' users';

  const tbody = document.getElementById('interestTableBody');
  if (!tbody) return;

  if (eligible.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state">
      <div class="icon">🧠</div>
      <p>Not enough data to analyse. Need at least ${minViews} profile views per user.</p>
    </div></td></tr>`;
    return;
  }

  tbody.innerHTML = eligible.map((u, i) => {
    const scored  = scoreEngagement(u.mobile);
    if (scored.length === 0) return '';
    const attrs   = extractAttributes(scored);
    const patterns= buildPatterns(attrs, scored);
    const style   = sessionStyle(u);
    const totalViews    = (u.profileViews||[]).length;
    const totalContacts = (u.contactViews||[]).length;

    const patternCell = (p, cls) => {
      if (!p.desc) return '—';
      const barColor = cls === 'pattern-1' ? '#6d28d9' : cls === 'pattern-2' ? '#c2410c' : '#15803d';
      return `<div class="pattern-pill ${cls}">${p.desc}</div>
        <div style="margin-top:6px;display:flex;align-items:center;gap:6px">
          <div style="flex:1;height:5px;border-radius:3px;background:#f3f4f6;overflow:hidden">
            <div style="height:100%;width:${p.pct}%;background:${barColor};border-radius:3px;transition:width .5s"></div>
          </div>
          <span style="font-size:11.5px;font-weight:700;color:${barColor}">${p.pct}%</span>
        </div>`;
    };

    const pIdx = profiles.findIndex(p => p.mobile === u.mobile);
    return `<tr class="interest-row">
      <td style="font-size:12px;color:var(--text-secondary)">${i+1}</td>
      <td>
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
          ${mobileCellHtml(u.mobile, pIdx, pIdx >= 0)}
          <button onclick="reAnalyseUser('${u.mobile}',this)" title="Re-analyse this user"
            style="background:#ede9fe;color:#7c3aed;border:none;border-radius:6px;
                   padding:3px 8px;font-size:11px;font-weight:700;cursor:pointer;
                   font-family:'DM Sans',sans-serif;transition:all .15s;white-space:nowrap"
            onmouseover="this.style.background='#7c3aed';this.style.color='#fff'"
            onmouseout="this.style.background='#ede9fe';this.style.color='#7c3aed'">
            🔄 Re-analyse
          </button>
        </div>
      </td>
      <td onclick="showInterestDetail('${u.mobile}')"><div class="name-cell">
        <div class="avatar" style="width:26px;height:26px;font-size:10px">${initials(u.name||u.mobile)}</div>
        <span style="font-size:13px">${u.name||'—'}</span>
      </div></td>
      <td onclick="showInterestDetail('${u.mobile}')" style="text-align:center">
        <div style="font-weight:700;font-size:14px">${totalViews}</div>
        <div style="font-size:11px;color:var(--text-secondary)">${totalContacts} contacts</div>
      </td>
      <td onclick="showInterestDetail('${u.mobile}')">${patternCell(patterns[0],'pattern-1')}</td>
      <td onclick="showInterestDetail('${u.mobile}')">${patternCell(patterns[1],'pattern-2')}</td>
      <td onclick="showInterestDetail('${u.mobile}')">${patternCell(patterns[2],'pattern-3')}</td>
      <td onclick="showInterestDetail('${u.mobile}')"><span class="session-badge">${style}</span></td>
    </tr>`;
  }).filter(Boolean).join('');
}

// ── Detail drawer ──
function showInterestDetail(mobile) {
  const u = usage.find(u => u.mobile === mobile);
  if (!u) return;

  const scored = scoreEngagement(mobile);
  const attrs  = extractAttributes(scored);

  const drawer = document.getElementById('interestDrawer');
  document.getElementById('drawerMobile').textContent = mobile + (u.name ? ' — ' + u.name : '');

  const topProfiles = scored.slice(0,5).map(s => {
    const p = profiles.find(pr => pr.cpId === s.cpId);
    if (!p) return '';
    return `<tr style="background:${s.contactViewed?'#f0fdf4':''}">
      <td><code style="font-size:11px;background:#f3f4f6;padding:2px 6px;border-radius:4px">${p.cpId}</code></td>
      <td>${p.name}</td>
      <td>${p.gender}</td>
      <td>${p.age}</td>
      <td>${p.qualification||'—'}</td>
      <td>${p.star||'—'}</td>
      <td>${p.caste||'—'}</td>
      <td><div style="display:flex;align-items:center;gap:6px">
        <div style="width:60px;height:5px;border-radius:3px;background:#f3f4f6;overflow:hidden">
          <div style="height:100%;width:${s.score}%;background:#7c3aed;border-radius:3px"></div>
        </div>
        <span style="font-size:12px;font-weight:700;color:#7c3aed">${s.score}</span>
      </div></td>
      <td>${s.contactViewed ? '<span class="badge" style="background:#dcfce7;color:#16a34a;font-size:11px">✓ Contacted</span>' : '—'}</td>
      <td style="font-size:12px">${s.timeSpent}s / ${s.scrollDepth}%</td>
    </tr>`;
  }).join('');

  const attrRows = attrs.slice(0,12).map(a =>
    `<div style="display:flex;align-items:center;gap:10px;padding:6px 0;border-bottom:1px solid #f3f4f6">
      <div style="width:110px;font-size:12.5px;font-weight:500">${a.label}</div>
      <div style="flex:1;height:6px;border-radius:3px;background:#f3f4f6;overflow:hidden">
        <div style="height:100%;width:${a.pct}%;background:linear-gradient(90deg,#7c3aed,#c026d3);border-radius:3px"></div>
      </div>
      <div style="font-size:12px;font-weight:700;color:#7c3aed;width:36px;text-align:right">${a.pct}%</div>
      <div style="font-size:11px;color:var(--text-secondary);width:50px">${a.count} views</div>
    </div>`
  ).join('');

  document.getElementById('drawerBody').innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
      <div>
        <div style="font-weight:700;font-size:13px;margin-bottom:12px;color:#7c3aed">📐 Attribute Affinity Scores</div>
        ${attrRows}
      </div>
      <div>
        <div style="font-weight:700;font-size:13px;margin-bottom:12px;color:#7c3aed">🏆 Top Engaged Profiles</div>
        <div class="table-wrap">
          <table style="font-size:12px">
            <thead><tr><th>CP ID</th><th>Name</th><th>Gender</th><th>Age</th><th>Qual</th><th>Star</th><th>Caste</th><th>Score</th><th>Contact</th><th>Time/Scroll</th></tr></thead>
            <tbody>${topProfiles}</tbody>
          </table>
        </div>
      </div>
    </div>`;

  drawer.style.display = '';
  drawer.scrollIntoView({ behavior:'smooth', block:'nearest' });
}

// ──────────────────────────────────────────────
// INSTAGRAM-STYLE PROFILE CARDS
// ──────────────────────────────────────────────
profileViewMode = 'table'; // 'table' | 'cards'

// Card gradient palettes by gender + plan
const CARD_PALETTES = {
  'Male_free':    ['#667eea','#764ba2'],
  'Male_paid':    ['#2196F3','#1565C0'],
  'Male_premium': ['#f093fb','#f5576c'],
  'Female_free':  ['#ffecd2','#fcb69f'],
  'Female_paid':  ['#a1c4fd','#c2e9fb'],
  'Female_premium':['#fd7043','#e91e63'],
  'default':      ['#1a1a2e','#2d2d5e'],
};

function getCardPalette(p) {
  const key = p.gender + '_' + p.plan;
  return CARD_PALETTES[key] || CARD_PALETTES['default'];
}

function switchProfileView(mode, btn) {
  profileViewMode = mode;
  document.querySelectorAll('.view-toggle-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const tableCard = document.querySelector('#profileSection .card');
  const instaDiv  = document.getElementById('instaCards');
  if (mode === 'table') {
    tableCard.style.display = '';
    instaDiv.style.display  = 'none';
  } else {
    tableCard.style.display = 'none';
    instaDiv.style.display  = '';
    renderInstaCards();
  }
}

function renderInstaCards() {
  const container = document.getElementById('instaCards');
  if (!container) return;

  // Get currently filtered profiles
  const q          = (document.getElementById('profileSearch')?.value || '').toLowerCase();
  const statusFilt = document.getElementById('profileStatusFilter')?.value || '';
  const planFilt   = document.getElementById('profilePlanFilter')?.value   || '';
  const genderFilt = document.getElementById('profileGenderFilter')?.value || '';

  const filtered = profiles.filter(p => {
    const txt = (p.name+p.mobile+p.cpId+(p.qualification||'')+(p.caste||'')).toLowerCase();
    return (!q          || txt.includes(q))
        && (!statusFilt || p.status === statusFilt)
        && (!planFilt   || p.plan === planFilt)
        && (!genderFilt || p.gender === genderFilt);
  });

  if (filtered.length === 0) {
    container.innerHTML = `<div class="card"><div class="empty-state"><div class="icon">👤</div><p>No profiles match the filter</p></div></div>`;
    return;
  }

  container.innerHTML = `<div class="insta-grid">${filtered.map((p, i) => {
    const [c1, c2]  = getCardPalette(p);
    const pi        = profiles.indexOf(p);
    const closedTypes = ['paid','not_interested'];
    const hasFU     = followUps.some(f => f.cpId === p.cpId && !closedTypes.includes(f.type));
    const fuDot     = hasFU
      ? `<span style="position:absolute;top:8px;left:10px;width:8px;height:8px;background:#22c55e;border-radius:50%;border:1.5px solid #fff" title="Follow-up exists"></span>`
      : `<span style="position:absolute;top:8px;left:10px;width:8px;height:8px;background:#dc2626;border-radius:50%;border:1.5px solid #fff" title="No follow-up — dbl-click mobile" ondblclick="openFollowUp(${pi})"></span>`;

    const initials2 = initials(p.name);
    const planColor = p.plan==='premium'?'#f59e0b':p.plan==='paid'?'#2563eb':'#6b7280';
    const planBg    = p.plan==='premium'?'#fef3c7':p.plan==='paid'?'#dbeafe':'#f3f4f6';
    const statusColor= p.status==='Approved'?'#16a34a':'#d97706';
    const statusBg   = p.status==='Approved'?'#dcfce7':'#fef9c3';

    // Usage info
    const u = usage.find(u => u.mobile === p.mobile);
    const viewCount    = u ? (u.profileViews||[]).length : 0;
    const contactCount = u ? (u.contactViews||[]).length : 0;

    return `<div class="insta-card" style="--c1:${c1};--c2:${c2}" onclick="openEdit(${pi})">
      ${fuDot}
      <span class="insta-card-badge" style="background:${statusBg};color:${statusColor}">${p.status==='Approved'?'✓ Approved':'⏳ Pending'}</span>
      <div class="insta-card-cover">
        ${photoSrc(p.photo1) ? `<img src="${photoSrc(p.photo1)}" style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.15)" onerror="this.outerHTML='<div class=\\'insta-card-avatar\\' style=\\'color:${c1};font-size:22px\\'>${initials2}</div>'" alt="">` : `<div class="insta-card-avatar" style="color:${c1};font-size:22px">${initials2}</div>`}
      </div>
      <div class="insta-card-body">
        <div class="insta-card-name">${p.name}</div>
        <div class="insta-card-cpid">${p.cpId} · ${p.age}y · ${p.gender}</div>
        <div class="insta-card-tags">
          <span class="insta-tag" style="background:${planBg};color:${planColor}">${p.plan.charAt(0).toUpperCase()+p.plan.slice(1)}</span>
          ${p.star?`<span class="insta-tag" style="background:#fdf4ff;color:#9333ea">⭐ ${p.star}</span>`:''}
          ${p.qualification?`<span class="insta-tag" style="background:#eff6ff;color:#2563eb">${p.qualification}</span>`:''}
          ${p.caste?`<span class="insta-tag" style="background:#fff7ed;color:#c2410c">${p.caste}</span>`:''}
        </div>
        ${p.job?`<div style="font-size:11.5px;color:var(--text-secondary);margin-bottom:6px">💼 ${p.job}${p.placeJob?' · '+p.placeJob:''}</div>`:''}
        <div class="insta-card-stats">
          <div class="insta-stat"><div class="insta-stat-num">${viewCount}</div><div class="insta-stat-lbl">Views</div></div>
          <div class="insta-stat"><div class="insta-stat-num">${contactCount}</div><div class="insta-stat-lbl">Contacts</div></div>
          <div class="insta-stat"><div class="insta-stat-num">${p.height||'—'}</div><div class="insta-stat-lbl">Height</div></div>
        </div>
      </div>
    </div>`;
  }).join('')}</div>`;
}

// ── Per-user re-analyse: update single row with fresh patterns ──
function reAnalyseUser(mobile, btn) {
  const u = usage.find(u => u.mobile === mobile);
  if (!u) { toast('No usage data for ' + mobile, 'error'); return; }

  // Visual feedback
  const origText = btn.innerHTML;
  btn.innerHTML = '⏳ Analysing…';
  btn.disabled  = true;

  setTimeout(() => {
    // Re-run the full scoring pipeline for this user
    const scored   = scoreEngagement(mobile);
    const attrs    = extractAttributes(scored);
    const patterns = buildPatterns(attrs, scored);
    const style    = sessionStyle(u);

    const barColor = (p, c1, c2, c3) => p.rank===1?c1:p.rank===2?c2:c3;
    const patternHtml = (p, cls) => {
      if (!p.desc) return '—';
      const color = cls==='pattern-1'?'#6d28d9':cls==='pattern-2'?'#c2410c':'#15803d';
      return `<div class="pattern-pill ${cls}">${p.desc}</div>
        <div style="margin-top:6px;display:flex;align-items:center;gap:6px">
          <div style="flex:1;height:5px;border-radius:3px;background:#f3f4f6;overflow:hidden">
            <div style="height:100%;width:${p.pct}%;background:${color};border-radius:3px"></div>
          </div>
          <span style="font-size:11.5px;font-weight:700;color:${color}">${p.pct}%</span>
        </div>`;
    };

    // Find this row and update pattern cells
    const row = btn.closest('tr');
    if (row) {
      const cells = row.querySelectorAll('td');
      // cells: 0=#, 1=mobile+btn, 2=name, 3=views, 4=p1, 5=p2, 6=p3, 7=session
      if (cells[4]) cells[4].innerHTML = patternHtml(patterns[0],'pattern-1');
      if (cells[5]) cells[5].innerHTML = patternHtml(patterns[1],'pattern-2');
      if (cells[6]) cells[6].innerHTML = patternHtml(patterns[2],'pattern-3');
      if (cells[7]) cells[7].innerHTML = `<span class="session-badge">${style}</span>`;
      // Flash row to indicate update
      row.style.background = '#f3e8ff';
      setTimeout(() => { row.style.background = ''; }, 800);
    }

    btn.innerHTML = '✓ Done';
    btn.style.background = '#dcfce7'; btn.style.color = '#16a34a';
    setTimeout(() => {
      btn.innerHTML = origText;
      btn.disabled  = false;
      btn.style.background = ''; btn.style.color = '';
    }, 1800);
    toast('🔄 Re-analysed: ' + (u.name || mobile));
  }, 400); // small delay for UX feel
}

// ──────────────────────────────────────────────
// MOBILE NUMBER CHANGE REQUESTS (admin side)
// ──────────────────────────────────────────────
mobileReqs = [];

function loadMobileReqs() { /* no-op: replaced by API */ }

function saveMobileReqs() { /* no-op: replaced by API */ }

function renderMobileReqs() {
  loadMobileReqs();
  const pending = mobileReqs.filter(r => r.status === 'pending').length;
  const countEl = document.getElementById('mobileReqCount');
  if (countEl) countEl.textContent = pending + ' pending';

  const tbody = document.getElementById('mobileReqTable');
  if (!tbody) return;

  if (mobileReqs.length === 0) {
    tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state">
      <div class="icon">📱</div><p>No mobile change requests yet</p>
    </div></td></tr>`;
    return;
  }

  tbody.innerHTML = [...mobileReqs].reverse().map((r, i) => {
    const profile = profiles.find(p => p.mobile === r.oldMobile);
    const statusBadge =
      r.status === 'pending'  ? `<span class="badge badge-amber">⏳ Pending</span>` :
      r.status === 'approved' ? `<span class="badge badge-green">✅ Approved</span>` :
                                `<span class="badge badge-red">❌ Rejected</span>`;
    const actions = r.status === 'pending'
      ? `<div style="display:flex;gap:6px">
           <button class="btn btn-green btn-sm" onclick="approveMobileReq('${r.id}')">✓ Approve</button>
           <button class="btn btn-danger btn-sm" onclick="rejectMobileReq('${r.id}')">✕ Reject</button>
         </div>`
      : `<span style="font-size:12px;color:var(--text-secondary)">Actioned on ${r.actionedAt||'—'}</span>`;

    return `<tr style="${r.status==='pending'?'background:#fffdf5':''}">
      <td style="font-size:12px;color:var(--text-secondary)">${mobileReqs.length - i}</td>
      <td style="font-weight:600">${r.oldMobile}</td>
      <td style="font-weight:600;color:#2563eb">${r.newMobile}</td>
      <td><div class="name-cell">
        <div class="avatar" style="width:24px;height:24px;font-size:9px">${initials(profile?.name || r.profileSnapshot?.name || '?')}</div>
        <span style="font-size:12.5px">${profile?.name || r.profileSnapshot?.name || '—'}</span>
      </div></td>
      <td style="font-size:12.5px">${r.reason}</td>
      <td style="font-size:12px;color:var(--text-secondary)">${r.requestedAt}</td>
      <td style="text-align:center">${r.otpVerified ? '<span class="badge badge-green">✓ Yes</span>' : '<span class="badge badge-red">✗ No</span>'}</td>
      <td>${statusBadge}</td>
      <td>${actions}</td>
    </tr>`;
  }).join('');
}

function approveMobileReq(id) {
  loadMobileReqs();
  const req = mobileReqs.find(r => r.id === id);
  if (!req || req.status !== 'pending') return;
  if (!confirm(`Approve mobile change for ${req.oldMobile} → ${req.newMobile}?\n\nThis will:\n✓ Archive profile data for old number\n✓ Update profile mobile to ${req.newMobile}\n✓ Update all related records`)) return;

  // Archive old profile data
  const pIdx = profiles.findIndex(p => p.mobile === req.oldMobile);
  if (pIdx > -1) {
    // Archive snapshot in the request
    req.profileSnapshot = { ...profiles[pIdx] };
    // Update mobile in profiles
    profiles[pIdx].mobile = req.newMobile;
    // Update related bills
    bills.forEach(b => { if (b.mobile === req.oldMobile) b.mobile = req.newMobile; });
    billHistory.forEach(b => { if (b.mobile === req.oldMobile) b.mobile = req.newMobile; });
    // Update OTP logs
    otpLogs.forEach(o => { if (o.mobile === req.oldMobile) o.mobile = req.newMobile; });
    // Update usage
    const uEntry = usage.find(u => u.mobile === req.oldMobile);
    if (uEntry) uEntry.mobile = req.newMobile;
  }

  req.status     = 'approved';
  req.actionedAt = nowStamp();
  req.actionedBy = getActiveAdminName();
  req.adminNote  = 'Approved and applied by ' + getActiveAdminName();

  saveMobileReqs();
  saveState();
  pushAdminLog('Approved Mobile Change', req.oldMobile + ' → ' + req.newMobile, 'admin');
  render(); postRender();
  renderMobileReqs();
  toast('✅ Mobile number changed: ' + req.oldMobile + ' → ' + req.newMobile);
}

function rejectMobileReq(id) {
  loadMobileReqs();
  const req = mobileReqs.find(r => r.id === id);
  if (!req || req.status !== 'pending') return;
  const note = prompt('Reason for rejection (optional):') || 'Request rejected by admin';
  req.status     = 'rejected';
  req.actionedAt = nowStamp();
  req.actionedBy = getActiveAdminName();
  req.adminNote  = note;
  saveMobileReqs();
  pushAdminLog('Rejected Mobile Change', req.oldMobile + ' → ' + req.newMobile + ' — ' + note, 'admin');
  renderMobileReqs();
  toast('Request rejected');
}

// ──────────────────────────────────────────────
// ORIGINAL BOOT SECTION REMOVED
// Boot is now handled by the async boot() IIFE below
// which checks server-side auth, loads data via API,
// then calls render/postRender/initPaginationBars etc.
// ──────────────────────────────────────────────




// ══════════════════════════════════════════════════════
// OVERRIDES: DATA-MODIFYING FUNCTIONS -> ASYNC + API
// ══════════════════════════════════════════════════════

// Override saveAdd removed — using the main saveAdd function directly (defined earlier)
// which handles all fields including new ones (nationality, workplace, dosham_type, address location)

// Override saveEdit removed — using the main saveEdit function directly (defined earlier)

// Override confirmDelete to use API
const _orig_confirmDelete = confirmDelete;
confirmDelete = async function() {
  const reason    = document.getElementById('d_reason').value.trim();
  const adminName = document.getElementById('d_admin').value.trim();
  if (!reason) { toast('Please enter a reason for deletion', 'error'); return; }
  const p = profiles[idx];
  try {
    await apiPost('api/admin/profiles.php', { action: 'delete', cpId: p.cpId, reason, deletedBy: adminName });
    closeModal('deleteOverlay');
    await loadAll();
    render(); postRender();
    toast('Profile deleted');
  } catch(e) { toast(e.message, 'error'); }
};

// Override undoDelete to use API
const _orig_undoDelete = undoDelete;
undoDelete = async function(i) {
  const d = deleted[i];
  try {
    await apiPost('api/admin/profiles.php', { action: 'restore', cpId: d.cpId });
    await loadAll();
    render(); postRender();
    toast('Profile restored - ' + d.name);
  } catch(e) { toast(e.message, 'error'); }
};

// Override toggle (approve/revert) to use API
const _orig_toggle = toggle;
toggle = async function(i) {
  const p = profiles[i];
  const newStatus = p.status === 'Preapproved' ? 'Approved' : 'Preapproved';
  const action = newStatus === 'Approved' ? 'approve' : 'revert';
  try {
    await apiPost('api/admin/profiles.php', { action, cpId: p.cpId });
    await loadAll();
    render(); postRender();
    toast(newStatus === 'Approved' ? 'Profile approved' : 'Status reverted');
  } catch(e) { toast(e.message, 'error'); }
};

// Override saveFollow to use API
const _orig_saveFollow = saveFollow;
saveFollow = async function() {
  const type   = document.getElementById('fu_type').value;
  const date   = document.getElementById('fu_date').value;
  const reason = document.getElementById('fu_reason').value.trim();
  if (!type)   { toast('Please select a follow-up type', 'error'); return; }
  if (!date)   { toast('Please select a date', 'error'); return; }
  const today = new Date().toISOString().split('T')[0];
  if (date < today) { toast('Cannot select a past date', 'error'); return; }
  let p = profiles[idx];
  if (!p) {
    // Manual add — mobile is required (user may not have a profile yet)
    const cpIdVal  = document.getElementById('fu_cpid').value.trim();
    const mobileVal = (document.getElementById('fu_mobile')?.value || '').replace(/\D/g,'');
    if (!mobileVal && !cpIdVal) { toast('Enter a mobile number or CP ID', 'error'); return; }
    if (mobileVal && mobileVal.length !== 10) { toast('Enter a valid 10-digit mobile number', 'error'); return; }
    // If a matching profile exists use it, otherwise treat as new lead
    const byId  = cpIdVal  ? profiles.find(pr => pr.cpId === cpIdVal) : null;
    const byMob = mobileVal ? profiles.find(pr => pr.mobile === mobileVal) : null;
    p = byId || byMob || { cpId: cpIdVal || '—', name: document.getElementById('fu_member').value.trim() || mobileVal, mobile: mobileVal };
  }
  try {
    await apiPost('api/admin/followups.php', {
      action: 'create',
      cpId: p.cpId, memberName: p.name, mobile: p.mobile,
      type, admin: document.getElementById('fu_admin').value,
      date, reason
    });
    closeModal('followOverlay');
    await loadAll();
    render(); postRender();
    toast('Follow-up scheduled');
  } catch(e) { toast(e.message, 'error'); }
};

// Override saveEditFollow to use API
const _orig_saveEditFollow = saveEditFollow;
saveEditFollow = async function() {
  const date = document.getElementById('ef_date').value;
  const today = new Date().toISOString().split('T')[0];
  if (!date)        { toast('Please select a date', 'error'); return; }
  if (date < today) { toast('Cannot set a past date', 'error'); return; }
  const f = followUps[editFollowIdx];
  try {
    await apiPost('api/admin/followups.php', {
      action: 'update',
      id: f.id, cpId: f.cpId,
      type: document.getElementById('ef_type').value,
      date,
      reason: document.getElementById('ef_reason').value.trim()
    });
    closeModal('editFollowOverlay');
    await loadAll();
    renderFollowTables();
    render(); postRender();
    toast('Follow-up updated');
  } catch(e) { toast(e.message, 'error'); }
};

// Override confirmUndoFollow to use API
const _orig_confirmUndoFollow = confirmUndoFollow;
confirmUndoFollow = async function() {
  const todayStr = new Date().toISOString().split('T')[0];
  const newDate  = document.getElementById('uf_date').value;
  if (!newDate)           { toast('Please select a date', 'error'); return; }
  if (newDate < todayStr) { toast('Date must be today or future', 'error'); return; }
  const f = followUps[undoFollowIdx];
  try {
    await apiPost('api/admin/followups.php', {
      action: 'update', id: f.id, cpId: f.cpId,
      date: newDate, type: 'data'
    });
    closeModal('undoFollowOverlay');
    await loadAll();
    renderFollowTables();
    render(); postRender();
    toast('Follow-up reopened');
  } catch(e) { toast(e.message, 'error'); }
};

// Override saveBill to use API
const _orig_saveBill = saveBill;
saveBill = async function() {
  const planName  = document.getElementById('bill_planname').value;
  const planType  = document.getElementById('bill_plantype').value;
  const amount    = document.getElementById('bill_amount').value;
  const payment   = document.getElementById('bill_type').value;
  const expiry    = document.getElementById('bill_expiry').value;
  const billedDate= document.getElementById('bill_date').value;
  const billedBy  = document.getElementById('bill_billedby').value;
  const cpId      = document.getElementById('bill_cpid').value;
  const mobile    = document.getElementById('bill_mobile').value;
  const name      = document.getElementById('bill_name').value;
  if (!planName)  { toast('Please select a plan', 'error');        return; }
  if (amount==='') { toast('Please enter the billed amount', 'error'); return; }
  if (!payment)   { toast('Please select a payment type', 'error'); return; }
  const action = billEditIdx !== null ? 'update' : 'create';
  try {
    await apiPost('api/admin/bills.php', {
      action, cpId, mobile, name,
      planName, planType,
      amount: parseFloat(amount),
      payment, billedBy, billedDate, expiry,
      billIndex: billEditIdx
    });
    closeModal('billOverlay');
    await loadAll();
    render(); postRender();
    renderBills(); renderBillHistory();
    toast(billEditIdx !== null ? 'Bill updated' : 'Bill created');
  } catch(e) { toast(e.message, 'error'); }
};

// Override saveAddAdmin to use API
const _orig_saveAddAdmin = saveAddAdmin;
saveAddAdmin = async function() {
  const name     = document.getElementById('aa_name').value.trim();
  const username = document.getElementById('aa_username').value.trim();
  const mobile   = document.getElementById('aa_mobile').value.trim();
  const role     = document.getElementById('aa_role').value;
  const pwd      = document.getElementById('aa_password').value;
  const confirm  = document.getElementById('aa_confirm').value;
  if (!name || !username || !mobile || !pwd) { toast('Please fill all fields', 'error'); return; }
  if (pwd !== confirm) { toast('Passwords do not match', 'error'); return; }
  try {
    await apiPost('api/admin/settings.php', {
      section: 'admins', action: 'create',
      name, username, mobile, role, password: pwd
    });
    closeModal('addAdminOverlay');
    await loadAll();
    renderAdmins();
    toast('Admin account created');
  } catch(e) { toast(e.message, 'error'); }
};

// Override saveEditAdmin to use API
const _orig_saveEditAdmin = saveEditAdmin;
saveEditAdmin = async function() {
  const a = admins[adminIdx];
  const name     = document.getElementById('ea_name').value.trim();
  const username = document.getElementById('ea_username').value.trim();
  const mobile   = document.getElementById('ea_mobile').value.trim();
  const role     = document.getElementById('ea_role').value;
  const status   = document.getElementById('ea_status').value;
  const newPwd   = document.getElementById('ea_password').value;
  try {
    await apiPost('api/admin/settings.php', {
      section: 'admins', action: 'update',
      id: a.id, name, username, mobile, role, status,
      password: newPwd || undefined
    });
    closeModal('editAdminOverlay');
    await loadAll();
    renderAdmins();
    toast('Admin account updated');
  } catch(e) { toast(e.message, 'error'); }
};

// Override confirmDeleteAdmin to use API
const _orig_confirmDeleteAdmin = confirmDeleteAdmin;
confirmDeleteAdmin = async function() {
  const a = admins[adminIdx];
  try {
    await apiPost('api/admin/settings.php', {
      section: 'admins', action: 'delete', id: a.id
    });
    closeModal('deleteAdminOverlay');
    await loadAll();
    renderAdmins();
    toast('Admin account removed');
  } catch(e) { toast(e.message, 'error'); }
};

// Override saveCustomPlan to use API
const _orig_saveCustomPlan = saveCustomPlan;
saveCustomPlan = async function() {
  const name      = document.getElementById('cp_name').value.trim();
  const type      = document.getElementById('cp_type').value;
  const amount    = document.getElementById('cp_amount').value;
  const validity  = document.getElementById('cp_validity').value;
  const desc      = document.getElementById('cp_desc').value.trim();
  const createdBy = document.getElementById('cp_createdby').value.trim();
  const status    = document.getElementById('cp_status').value;
  if (!name)                   { toast('Plan name is required', 'error');              return; }
  if (!type)                   { toast('Please select a plan type', 'error');          return; }
  if (amount === '')           { toast('Enter an amount (0 for Free)', 'error');       return; }
  if (!validity || validity<1) { toast('Enter validity in days (min 1)', 'error');     return; }
  if (!desc)                   { toast('Description is required', 'error');            return; }
  if (!createdBy)              { toast('Created By admin name is required', 'error'); return; }
  const plan_id = editPlanIdx !== null ? (customPlans[editPlanIdx]?.planId || customPlans[editPlanIdx]?.plan_id || '') : '';
  try {
    await apiPost('api/admin/settings.php', {
      section: 'plans', action: 'save',
      plan_id,
      name, type, amount: parseFloat(amount), validity: parseInt(validity),
      description: desc, createdBy, status
    });
    resetPlanForm();
    await loadAll();
    renderCustomPlans();
    renderPlanHistory();
    toast(editPlanIdx !== null ? 'Plan updated' : 'Plan created');
  } catch(e) { toast(e.message, 'error'); }
};

// Override deleteCustomPlan to use API
const _orig_deleteCustomPlan = deleteCustomPlan;
deleteCustomPlan = async function(i) {
  if (!confirm('Delete plan "' + customPlans[i].name + '"?')) return;
  const plan_id = customPlans[i]?.planId || customPlans[i]?.plan_id || '';
  try {
    await apiPost('api/admin/settings.php', { section: 'plans', action: 'delete', plan_id });
    await loadAll();
    renderCustomPlans();
    toast('Plan deleted');
  } catch(e) { toast(e.message, 'error'); }
};

// Override saveStory to use API
const _orig_saveStory = saveStory;
saveStory = async function() {
  const groom = document.getElementById('st_groom').value.trim();
  const bride = document.getElementById('st_bride').value.trim();
  const date  = document.getElementById('st_date').value;
  const quote = document.getElementById('st_quote').value.trim();
  if (!groom || !bride || !date) { toast('Please fill Groom, Bride and Date', 'error'); return; }
  try {
    await apiPost('api/admin/settings.php', {
      section: 'stories', action: 'create',
      groom, bride, date, quote: quote || 'A beautiful love story.'
    });
    closeModal('addStoryOverlay');
    await loadAll();
    renderStories();
    toast('Success story added');
  } catch(e) { toast(e.message, 'error'); }
};

// Override deleteStory to use API
const _orig_deleteStory = deleteStory;
deleteStory = async function(i) {
  if (!confirm('Remove this success story?')) return;
  try {
    await apiPost('api/admin/settings.php', { section: 'stories', action: 'delete', storyIndex: i });
    await loadAll();
    renderStories();
    toast('Story removed');
  } catch(e) { toast(e.message, 'error'); }
};

// Override markRead and markAllRead
const _orig_markRead = markRead;
markRead = async function(i) {
  try {
    await apiPost('api/admin/settings.php', { section: 'notifications', action: 'markRead', index: i });
    notifications[i].unread = false;
    renderNotifications();
  } catch(e) { console.error(e); }
};

const _orig_markAllRead = markAllRead;
markAllRead = async function() {
  try {
    await apiPost('api/admin/settings.php', { section: 'notifications', action: 'markAllRead' });
    notifications.forEach(n => n.unread = false);
    renderNotifications();
    toast('All notifications marked as read');
  } catch(e) { toast(e.message, 'error'); }
};

// Override saveGlobalRestriction to use API
const _orig_saveGlobalRestriction = saveGlobalRestriction;
saveGlobalRestriction = async function() {
  const day          = document.getElementById('gl_day').value.trim();
  const month        = document.getElementById('gl_month').value.trim();
  const total        = document.getElementById('gl_total').value.trim();
  const sessionViews = document.getElementById('gl_uv_views').value.trim();
  const sessionHours = document.getElementById('gl_uv_hours').value.trim();
  if (day === '' && month === '' && total === '' && sessionViews === '' && sessionHours === '') {
    toast('Please enter at least one limit', 'error'); return;
  }
  try {
    await apiPost('api/admin/settings.php', {
      section: 'restrictions', action: 'saveGlobal',
      day, month, total,
      unverified_session_views: sessionViews,
      unverified_session_hours: sessionHours
    });
    globalRestriction = { day, month, total, sessionViews, sessionHours };
    renderRestrictions();
    toast('Global restriction saved');
  } catch(e) { toast(e.message, 'error'); }
};

// Override saveIndividualRestriction to use API
const _orig_saveIndividualRestriction = saveIndividualRestriction;
saveIndividualRestriction = async function() {
  const mobile = document.getElementById('ind_mobile').value.trim();
  const day    = document.getElementById('ind_day').value.trim();
  const month  = document.getElementById('ind_month').value.trim();
  const total  = document.getElementById('ind_total').value.trim();
  if (!/^\d{10}$/.test(mobile)) { toast('Enter a valid 10-digit mobile number', 'error'); return; }
  if (day === '' && month === '' && total === '') { toast('Enter at least one limit', 'error'); return; }
  try {
    await apiPost('api/admin/settings.php', {
      section: 'restrictions', action: 'saveIndividual',
      mobile, day, month, total
    });
    ['ind_mobile','ind_day','ind_month','ind_total'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('ind_user_hint').innerHTML = '';
    await loadAll();
    renderRestrictions();
    toast('Individual restriction saved');
  } catch(e) { toast(e.message, 'error'); }
};

// Override deleteIndRestriction to use API
const _orig_deleteIndRestriction = deleteIndRestriction;
deleteIndRestriction = async function(i) {
  try {
    await apiPost('api/admin/settings.php', {
      section: 'restrictions', action: 'deleteIndividual', index: i
    });
    await loadAll();
    renderRestrictions();
    toast('Restriction removed');
  } catch(e) { toast(e.message, 'error'); }
};

// Override toggleBan to use API
const _orig_toggleBan = toggleBan;
toggleBan = async function(i) {
  const o = otpLogs[i];
  const action = o.banned ? 'unban' : 'ban';
  if (!confirm((action === 'ban' ? 'Ban' : 'Unban') + ' user ' + o.mobile + '?')) return;
  try {
    await apiPost('api/admin/settings.php', {
      section: 'otpLogs', action: action, index: i, mobile: o.mobile
    });
    await loadAll();
    renderOtp();
    toast(action === 'ban' ? 'User banned' : 'User unbanned');
  } catch(e) { toast(e.message, 'error'); }
};

// Override quickBan to use API
const _orig_quickBan = quickBan;
quickBan = async function(mobile) {
  try {
    await apiPost('api/admin/settings.php', {
      section: 'otpLogs', action: 'ban', mobile
    });
    await loadAll();
    renderAlerts();
    renderOtp();
    toast('User ' + mobile + ' banned');
  } catch(e) { toast(e.message, 'error'); }
};

// Override confirmExpire to use API
const _orig_confirmExpire = confirmExpire;
confirmExpire = async function() {
  const reason = document.getElementById('exp_reason').value;
  if (!reason) { toast('Please select a reason', 'error'); return; }
  const p = profiles[expireIdx];
  try {
    await apiPost('api/admin/profiles.php', {
      action: 'expire', cpId: p.cpId, reason,
      actionedBy: document.getElementById('exp_by').value
    });
    closeModal('expireOverlay');
    await loadAll();
    render(); postRender();
    renderExpired();
    toast('Profile marked as expired');
  } catch(e) { toast(e.message, 'error'); }
};

// Override undoExpire to use API
const _orig_undoExpire = undoExpire;
undoExpire = async function(i) {
  const e = expiredProfiles[i];
  if (!confirm('Restore ' + e.name + ' (' + e.cpId + ') to Pre-approved status?')) return;
  try {
    await apiPost('api/admin/settings.php', {
      section: 'expired', action: 'restore', index: i, cpId: e.cpId
    });
    await loadAll();
    render(); postRender();
    renderExpired();
    toast('Profile restored to Pre-approved');
  } catch(e2) { toast(e2.message, 'error'); }
};

// Override savePaymentOption to use API
const _orig_savePaymentOption = savePaymentOption;
savePaymentOption = async function() {
  const method = getSelectedPayMethod();
  const label  = document.getElementById('pay_label').value.trim();
  if (!method) { toast('Please select a payment method', 'error'); return; }
  if (!label)  { toast('Display label is required', 'error');       return; }
  let details = {};
  if (method === 'qr') {
    const qrData = document.getElementById('pay_qr_data').value.trim();
    const qrUrl = document.getElementById('pay_qr_url').value.trim();
    const finalQr = qrData || qrUrl;
    if (!finalQr) { toast('Please upload a QR code image or paste URL', 'error'); return; }
    details = { qrUrl: finalQr, linkedUpi: document.getElementById('pay_qr_upi').value.trim() };
  } else if (method === 'upi') {
    const upiId = document.getElementById('pay_upi_id').value.trim();
    if (!upiId) { toast('UPI ID is required', 'error'); return; }
    details = { upiId, upiApp: document.getElementById('pay_upi_app').value.trim() };
  } else if (method === 'bank') {
    const acName = document.getElementById('pay_bank_name').value.trim();
    const acNo   = document.getElementById('pay_bank_acno').value.trim();
    const ifsc   = document.getElementById('pay_bank_ifsc').value.trim();
    if (!acName) { toast('Account holder name is required', 'error'); return; }
    if (!acNo)   { toast('Account number is required', 'error');       return; }
    if (!ifsc)   { toast('IFSC code is required', 'error');            return; }
    details = { accountName:acName, accountNo:acNo, ifsc:ifsc.toUpperCase(),
      accountType:document.getElementById('pay_bank_type').value,
      bankName:document.getElementById('pay_bank_bank').value.trim(),
      branch:document.getElementById('pay_bank_branch').value.trim() };
  } else if (method === 'mobile') {
    const mobNum    = document.getElementById('pay_mob_num').value.trim();
    const mobHolder = document.getElementById('pay_mob_holder').value.trim();
    if (!/^\d{10}$/.test(mobNum)) { toast('Enter a valid 10-digit mobile number', 'error'); return; }
    if (!mobHolder) { toast('Registered name is required', 'error'); return; }
    details = { mobileNo:mobNum, holderName:mobHolder, upiApp:document.getElementById('pay_mob_app').value.trim() };
  }
  const optId = editPayOptIdx !== null ? paymentOptions[editPayOptIdx].opt_id : '';
  try {
    await apiPost('api/admin/settings.php', {
      section: 'paymentOpts', action: 'save',
      opt_id: optId,
      method, label, details,
      notes: document.getElementById('pay_notes').value.trim(),
      status: document.getElementById('pay_status').value
    });
    const wasEdit = editPayOptIdx !== null;
    resetPaymentForm();
    await loadAll();
    renderPaymentOptions();
    toast(wasEdit ? 'Payment option updated' : 'Payment option added');
  } catch(e) { toast(e.message, 'error'); }
};

// Override deletePaymentOption to use API
const _orig_deletePaymentOption = deletePaymentOption;
deletePaymentOption = async function(i) {
  if (!confirm('Delete payment option "' + paymentOptions[i].label + '"?')) return;
  try {
    await apiPost('api/admin/settings.php', { section: 'paymentOpts', action: 'delete', opt_id: paymentOptions[i].opt_id });
    await loadAll();
    renderPaymentOptions();
    toast('Payment option deleted');
  } catch(e) { toast(e.message, 'error'); }
};

// Override togglePayOptStatus to use API
const _orig_togglePayOptStatus = togglePayOptStatus;
togglePayOptStatus = async function(i) {
  const newStatus = paymentOptions[i].status === 'active' ? 'inactive' : 'active';
  try {
    await apiPost('api/admin/settings.php', {
      section: 'paymentOpts', action: 'toggle', opt_id: paymentOptions[i].opt_id
    });
    await loadAll();
    renderPaymentOptions();
    toast(newStatus === 'active' ? 'Payment option activated' : 'Payment option deactivated');
  } catch(e) { toast(e.message, 'error'); }
};

// Override saveUserPanelControl to use API
const _orig_saveUserPanelControl = saveUserPanelControl;
saveUserPanelControl = async function() {
  try {
    await apiPost('api/admin/settings.php', {
      section: 'panelCtrl', action: 'saveGlobal',
      settings: userPanelControl.global || {}
    });
    await loadAll();
    renderGlobalToggles();
    renderUPCtrlHistory();
    toast('User panel control settings saved');
  } catch(e) { toast(e.message, 'error'); }
};

// Override saveUserOverride to use API
const _orig_saveUserOverride = saveUserOverride;
saveUserOverride = async function() {
  const raw    = document.getElementById('uov_cpid').value.trim();
  const nameEl = document.getElementById('uov_name').value.trim();
  if (!raw) { toast('Enter mobile number', 'error'); return; }
  // Resolve mobile — accept mobile directly or lookup from CP ID
  let mobile = raw, name = nameEl;
  const p = profiles.find(pr => (pr.cpId && pr.cpId.toLowerCase() === raw.toLowerCase()) || pr.mobile === raw);
  if (p) { mobile = p.mobile; name = p.name; }
  else if (!/^\d{10}$/.test(raw)) { toast('Enter a valid 10-digit mobile number', 'error'); return; }
  const displayName = name && !name.startsWith('—') ? name : mobile;
  const cpId = p ? p.cpId : '';
  const pages = {};
  [...UP_PAGES, ...UP_FEATURES].forEach(item => {
    const el = document.getElementById('uov_' + item.id);
    if (el) pages[item.id] = el.checked;
  });
  try {
    await apiPost('api/admin/settings.php', {
      section: 'panelCtrl', action: 'saveOverride',
      cp_id: cpId, mobile, name: displayName, settings: pages
    });
    closeUserOverrideForm();
    await loadAll();
    renderUserOverrideTable();
    renderUPCtrlHistory();
    toast(editOverrideIdx !== null ? 'Override updated' : 'Override added');
  } catch(e) { toast(e.message, 'error'); }
};

// Override deleteUserOverride to use API
const _orig_deleteUserOverride = deleteUserOverride;
deleteUserOverride = async function(i) {
  const ov = userPanelControl.overrides[i];
  if (!confirm('Remove override for ' + (ov.name || ov.mobile) + '?')) return;
  try {
    await apiPost('api/admin/settings.php', {
      section: 'panelCtrl', action: 'deleteOverride', id: ov.id, mobile: ov.mobile
    });
    await loadAll();
    renderUserOverrideTable();
    renderUPCtrlHistory();
    toast('Override removed');
  } catch(e) { toast(e.message, 'error'); }
};

// ══════════════════════════════════════════════════════
// UPDATE HISTORY
// ══════════════════════════════════════════════════════
let _historyRows = [];

async function loadUpdateHistory() {
  const tbody = document.getElementById('updateHistoryTbody');
  if (!tbody) return;
  tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:20px;color:var(--text-secondary)">Loading...</td></tr>';
  try {
    const data = await apiGet('api/admin/settings.php?section=updateHistory');
    _historyRows = data.history || [];
    renderUpdateHistory();
  } catch(e) {
    tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:20px;color:#dc2626">Failed to load: ' + e.message + '</td></tr>';
  }
}

function renderUpdateHistory() {
  const tbody = document.getElementById('updateHistoryTbody');
  if (!tbody) return;
  const q = (document.getElementById('historySearch')?.value || '').toLowerCase();
  const typeF = document.getElementById('historyFilter')?.value || '';
  const actF = document.getElementById('historyActionFilter')?.value || '';
  const df = document.getElementById('historyDateFrom')?.value || '';
  const dt = document.getElementById('historyDateTo')?.value || '';

  const filtered = _historyRows.filter(r => {
    const txt = ((r.entity_id||'') + (r.entity_type||'') + (r.action||'') + (r.field_name||'') + (r.old_value||'') + (r.new_value||'') + (r.changed_by||'') + (r.role||'')).toLowerCase();
    const d = (r.created_at || '').split(' ')[0];
    return (!q || txt.includes(q))
      && (!typeF || r.entity_type === typeF)
      && (!actF || r.action === actF)
      && (!df || d >= df)
      && (!dt || d <= dt);
  });

  const countEl = document.getElementById('historyCount');
  if (countEl) countEl.textContent = filtered.length + ' / ' + _historyRows.length;

  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:40px;color:var(--text-secondary)"><div style="font-size:28px;margin-bottom:8px">📋</div>No update history found</td></tr>';
    return;
  }
  const typeBadge = { profile:'#8B0000', admin:'#2563eb', plan:'#d97706', payment_option:'#16a34a', role_permission:'#9333ea', bill:'#0891b2', follow_up:'#ea580c', story:'#db2777' };
  const actionBadge = { created:'#16a34a', updated:'#2563eb', deleted:'#dc2626', approved:'#16a34a', rejected:'#dc2626', saved:'#d97706' };
  tbody.innerHTML = filtered.map((r, i) => {
    const tc = typeBadge[r.entity_type] || '#6b7280';
    const ac = actionBadge[r.action] || '#6b7280';
    const truncate = (v, len) => { if (!v) return '<span style="color:#ccc">—</span>'; const s = String(v); return s.length > len ? '<span title="'+s.replace(/"/g,'&quot;')+'">'+s.slice(0,len)+'…</span>' : s; };
    return `<tr>
      <td style="font-size:11px;color:var(--text-secondary)">${i+1}</td>
      <td style="font-size:11.5px;white-space:nowrap">${r.created_at || ''}</td>
      <td><span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:10.5px;font-weight:700;background:${tc}15;color:${tc};text-transform:uppercase">${r.entity_type}</span></td>
      <td style="font-size:12px;font-weight:600">${r.entity_id || '—'}</td>
      <td><span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:10.5px;font-weight:700;background:${ac}15;color:${ac};text-transform:uppercase">${r.action}</span></td>
      <td style="font-size:12px;color:var(--text-secondary)">${r.field_name || '—'}</td>
      <td style="font-size:11.5px;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${truncate(r.old_value, 40)}</td>
      <td style="font-size:11.5px;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${truncate(r.new_value, 40)}</td>
      <td style="font-size:12px;font-weight:600">${r.changed_by || ''}</td>
      <td style="font-size:11px;color:var(--text-secondary)">${r.role || ''}</td>
    </tr>`;
  }).join('');
}

function clearHistoryFilters() {
  ['historySearch','historyDateFrom','historyDateTo'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  ['historyFilter','historyActionFilter'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  renderUpdateHistory();
}

function exportUpdateHistory() {
  const q = (document.getElementById('historySearch')?.value || '').toLowerCase();
  const typeF = document.getElementById('historyFilter')?.value || '';
  const actF = document.getElementById('historyActionFilter')?.value || '';
  const df = document.getElementById('historyDateFrom')?.value || '';
  const dt = document.getElementById('historyDateTo')?.value || '';

  const filtered = _historyRows.filter(r => {
    const txt = ((r.entity_id||'') + (r.entity_type||'') + (r.action||'') + (r.field_name||'') + (r.old_value||'') + (r.new_value||'') + (r.changed_by||'') + (r.role||'')).toLowerCase();
    const d = (r.created_at || '').split(' ')[0];
    return (!q || txt.includes(q)) && (!typeF || r.entity_type === typeF) && (!actF || r.action === actF) && (!df || d >= df) && (!dt || d <= dt);
  });

  const csvRows = [['Date & Time','Type','Entity','Action','Field','Old Value','New Value','Changed By','Role']];
  filtered.forEach(r => {
    csvRows.push([r.created_at||'', r.entity_type||'', r.entity_id||'', r.action||'', r.field_name||'', (r.old_value||'').replace(/"/g,'""'), (r.new_value||'').replace(/"/g,'""'), r.changed_by||'', r.role||'']);
  });
  const csv = csvRows.map(row => row.map(c => '"' + c + '"').join(',')).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = 'update_history_' + new Date().toISOString().split('T')[0] + '.csv';
  a.click(); URL.revokeObjectURL(url);
  toast('CSV exported — ' + filtered.length + ' rows');
}

// Override saveRolePerms to use API
const _orig_saveRolePerms = saveRolePerms;
saveRolePerms = async function() {
  try {
    await apiPost('api/admin/settings.php', {
      section: 'rolePerms', action: 'save',
      role: activeRoleTab, permissions: rolePerms[activeRoleTab]
    });
    toast('Permissions saved for ' + activeRoleTab);
  } catch(e) { toast(e.message, 'error'); }
};

// Override saveAlertThresholds to use API
const _orig_saveAlertThresholds = saveAlertThresholds;
saveAlertThresholds = async function() {
  alertThresholds.contactDay  = parseInt(document.getElementById('th_contactDay').value)  || 10;
  alertThresholds.otpDay      = parseInt(document.getElementById('th_otpDay').value)      || 3;
  alertThresholds.profileDay  = parseInt(document.getElementById('th_profileDay').value)  || 10;
  try {
    await apiPost('api/admin/settings.php', {
      section: 'alertThresholds', action: 'save',
      contactDay: alertThresholds.contactDay,
      otpDay: alertThresholds.otpDay,
      profileDay: alertThresholds.profileDay
    });
    renderAlerts();
    toast('Thresholds saved');
  } catch(e) { toast(e.message, 'error'); }
};

// Override clearAdminLog to use API
const _orig_clearAdminLog = clearAdminLog;
clearAdminLog = async function() {
  if (!confirm('Clear all admin log entries? This cannot be undone.')) return;
  try {
    await apiPost('api/admin/settings.php', { section: 'adminLog', action: 'clear' });
    adminLog = [];
    renderAdminLog();
    toast('Admin log cleared');
  } catch(e) { toast(e.message, 'error'); }
};

// Override clearPlanHistory to use API
const _orig_clearPlanHistory = clearPlanHistory;
clearPlanHistory = async function() {
  if (!confirm('Clear all plan history?')) return;
  try {
    await apiPost('api/admin/settings.php', { section: 'planHistory', action: 'clear' });
    planHistory = [];
    renderPlanHistory();
    toast('Plan history cleared');
  } catch(e) { toast(e.message, 'error'); }
};

// Override clearUPCtrlHistory to use API
const _orig_clearUPCtrlHistory = clearUPCtrlHistory;
clearUPCtrlHistory = async function() {
  if (!confirm('Clear all User Panel Control history?')) return;
  try {
    await apiPost('api/admin/settings.php', { section: 'upCtrlHistory', action: 'clear' });
    upCtrlHistory = [];
    renderUPCtrlHistory();
    toast('History cleared');
  } catch(e) { toast(e.message, 'error'); }
};

// Override approveMobileReq to use API
const _orig_approveMobileReq = approveMobileReq;
approveMobileReq = async function(id) {
  // Template-literal onclick passes id as a string; PDO returns it as a number.
  // Compare stringified values so find() actually matches.
  const req = mobileReqs.find(r => String(r.id) === String(id));
  if (!req || req.status !== 'pending') return;
  if (!confirm('Approve mobile change ' + req.oldMobile + ' -> ' + req.newMobile + '?')) return;
  try {
    await apiPost('api/admin/settings.php', {
      section: 'mobileReqs', action: 'approve', id: req.id
    });
    await loadAll();
    render(); postRender();
    renderMobileReqs();
    toast('Mobile number changed');
  } catch(e) { toast(e.message, 'error'); }
};

// Override rejectMobileReq to use API
const _orig_rejectMobileReq = rejectMobileReq;
rejectMobileReq = async function(id) {
  const req = mobileReqs.find(r => String(r.id) === String(id));
  if (!req || req.status !== 'pending') return;
  const note = prompt('Reason for rejection (optional):') || 'Request rejected by admin';
  try {
    await apiPost('api/admin/settings.php', {
      section: 'mobileReqs', action: 'reject', id: req.id, note
    });
    await loadAll();
    renderMobileReqs();
    toast('Request rejected');
  } catch(e) { toast(e.message, 'error'); }
};

// Override pushAdminLog - fire and forget to API
const _orig_pushAdminLog = typeof pushAdminLog === 'function' ? pushAdminLog : null;
pushAdminLog = function(action, detail, type) {
  adminLog.unshift({
    adminName: getActiveAdminName(),
    role: getActiveAdminRole(),
    action, detail, type,
    timestamp: nowStamp()
  });
  if (adminLog.length > 500) adminLog.length = 500;
  if (document.getElementById('adminLogTable')) renderAdminLog();
  // Fire and forget
  apiPost('api/admin/settings.php', {
    section: 'adminLog', action: 'push',
    entry: { action, detail, type }
  }).catch(() => {});
};

// Override pushNotif - fire and forget to API
const _orig_pushNotif = typeof pushNotif === 'function' ? pushNotif : null;
pushNotif = function(icon, desc) {
  notifications.unshift({ icon, bg:'#fdf1ee', title: icon.replace(/\p{Emoji}/gu,'').trim() || 'Update', desc, time:'Just now', unread:true });
  apiPost('api/admin/settings.php', {
    section: 'notifications', action: 'push',
    icon, desc
  }).catch(() => {});
};

// ══════════════════════════════════════════════════════
// BOOT - check auth, load data, render
// ══════════════════════════════════════════════════════
(async function boot() {
  const removeSplash = () => {
    const s = document.getElementById('sessionCheckSplash');
    if (s) s.remove();
  };
  const showLogin = () => {
    const lp = document.getElementById('loginPage');
    if (lp) lp.style.display = '';
  };
  // Safety net: never leave the splash stuck, even if network hangs.
  const failSafe = setTimeout(() => { removeSplash(); showLogin(); }, 10000);

  try {
    const authCheck = await apiPost('api/admin/auth.php', { action: 'check' });
    clearTimeout(failSafe);
    if (authCheck && authCheck.loggedIn) {
      loginAdminObj = authCheck.admin || {};
      await completeLogin();
      removeSplash();
    } else {
      removeSplash();
      showLogin();
    }
  } catch(e) {
    clearTimeout(failSafe);
    console.log('Auth check failed, showing login page');
    removeSplash();
    showLogin();
  }
})();

// Open user-panel ("My Account") in a new tab without OTP.
// Flow: request one-time token → open /backend/user-panel.php?admin_token=XXX.
// The user-panel redeems the token server-side and hard-expires after 30 min.
async function openMyAccount() {
  const btn = document.getElementById('myAccountBtn');
  const orig = btn ? btn.innerHTML : '';
  if (btn) { btn.disabled = true; btn.innerHTML = 'Opening…'; }
  try {
    const r = await apiPost('api/admin/auth.php', { action: 'my_account_token' });
    if (r && r.url) {
      // Open immediately so popup blockers accept it as a user-gesture window.
      const w = window.open(r.url, '_blank', 'noopener');
      if (!w) toast('Please allow pop-ups to open My Account');
    } else {
      toast('Could not open My Account');
    }
  } catch (e) {
    toast((e && e.message) || 'Failed: check your admin mobile number');
  } finally {
    if (btn) { btn.disabled = false; btn.innerHTML = orig; }
  }
}

// Sign out - clears server session and shows login page
async function doSignOut() {
  try {
    await apiPost('api/admin/auth.php', { action: 'logout' });
  } catch(e) {}
  loginAdminObj = null;
  const shell = document.getElementById('appShell');
  if (shell) shell.style.display = 'none';
  const hb = document.getElementById('hamburger');
  if (hb) hb.style.display = 'none';
  const lp = document.getElementById('loginPage');
  if (lp) lp.style.display = '';
  // Remove splash just in case it's still around from an aborted boot
  const s = document.getElementById('sessionCheckSplash');
  if (s) s.remove();
  // Clear login fields
  ['lg_user','lg_pass'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  showLoginStep(1);
  toast('Signed out successfully');
}

// ===== DOB FORMAT INITIALIZATION =====
DobAge.init('a_dob', 'a_age_display', 'a_age', 'a_gender', 'a_age_input');
DobAge.init('e_dob', 'e_age_display', 'e_age', 'e_gender', 'e_age_input');
DobAge.init('ap_dob', 'ap_age_display', null, 'ap_gender', 'ap_age_input');

// ===== NATIONALITY & COUNTRY DROPDOWN INITIALIZATION =====
['a_nationality','e_nationality','ap_nationality'].forEach(id => populateNationality(id, 'Indian'));
['a_workplace','e_workplace','ap_workplace'].forEach(id => populateCountry(id, 'India'));

// ===== PLACE AUTOCOMPLETE INITIALIZATION =====
['a_pob','a_nativity','a_place_job','e_pob','e_nativity','e_place_job','ap_place_birth','ap_nativity','ap_present_area','ap_present_city'].forEach(id => PlaceSuggest.attach(id));
populateStateDropdown('ap_present_state', 'Tamil Nadu');
populateDistrictDropdown('ap_present_district', '', 'ap_present_state');
bindStateToDistrict('ap_present_state', 'ap_present_district');

// ===== GOTHRAM AUTOCOMPLETE INITIALIZATION =====
['a_gothram','e_gothram'].forEach(id => GothramSuggest.attach(id));

// ===== MOBILE DUPLICATE CHECK =====
MobileCheck.attach('a_mobile', () => profiles);
MobileCheck.attach('ap_mobile', () => profiles);

// ===== ADDRESS LOCATION INITIALIZATION =====
setupAddressExtract('a', 'a_present_addr');
setupAddressExtract('e', 'e_present_addr');

// ===== PARTNER CASTE PREFERENCE INITIALIZATION =====
PartnerCaste.build('a_p_caste_box', 'a_p_caste');
PartnerCaste.linkSubCaste('a_p_caste_box', 'a_p_subcaste_box', 'a_p_subcaste', 'a_caste');
PartnerCaste.build('e_p_caste_box', 'e_p_caste');
PartnerCaste.linkSubCaste('e_p_caste_box', 'e_p_subcaste_box', 'e_p_subcaste', 'e_caste');

// ===== DOSHAM TYPE INITIALIZATION =====
['a_dosham_type','e_dosham_type','ap_dosham_type'].forEach(id => populateDoshamType(id));
attachDoshamRadio('a_dosham', 'a_dosham_type_wrap');
attachDoshamRadio('e_dosham', 'e_dosham_type_wrap');
attachDoshamSelect('ap_dosham', 'ap_dosham_type_wrap');

// ===== FORM AUTO-SAVE INITIALIZATION =====
FormAutoSave.track('admin_add', { container: '#addOverlay', fieldPrefix: 'a_', excludeIds: [] });
FormAutoSave.track('admin_edit', { container: '#editOverlay', fieldPrefix: 'e_', excludeIds: ['e_mobile'] });
FormAutoSave.track('admin_quick_add', { container: '#addProfileSection', fieldPrefix: 'ap_', excludeIds: [] });
</script>
</body>
</html>
