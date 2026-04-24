<?php
/**
 * Database Structure Comparison Tool
 * Compares LOCAL vs ONLINE database tables and columns
 *
 * Usage:
 *   1. Update the ONLINE DB credentials below
 *   2. Open in browser: http://localhost/matrimony/backend/db-compare.php
 */

// ── LOCAL DB ──
$localHost = 'localhost';
$localDb   = 'matrimony';
$localUser = 'root';
$localPass = '';

// ── ONLINE DB (UPDATE THESE) ──
$onlineHost = '';  // e.g., 'sql123.epizy.com' or 'localhost'
$onlineDb   = '';  // e.g., 'epiz_12345678_matrimony'
$onlineUser = '';  // e.g., 'epiz_12345678'
$onlinePass = '';  // your online DB password

// ──────────────────────────────────────────────
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>DB Compare - Local vs Online</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, Arial, sans-serif; background: #f5f5f5; padding: 20px; color: #333; }
h1 { font-size: 20px; color: #8B0000; margin-bottom: 4px; }
.sub { font-size: 12px; color: #999; margin-bottom: 20px; }
.config-form { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #e0e0e0; }
.config-form label { font-size: 12px; font-weight: 700; color: #666; display: block; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
.config-form input { width: 100%; padding: 8px 12px; border: 1.5px solid #ddd; border-radius: 6px; font-size: 14px; margin-bottom: 12px; }
.config-form input:focus { border-color: #8B0000; outline: none; }
.config-form .row { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; }
.config-form button { padding: 10px 24px; background: linear-gradient(135deg, #8B0000, #C41E3A); color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.card { background: #fff; border-radius: 12px; padding: 16px 18px; margin-bottom: 12px; border: 1px solid #e0e0e0; }
.card-title { font-size: 14px; font-weight: 700; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
.badge { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 10px; }
.badge-green { background: #dcfce7; color: #16a34a; }
.badge-red { background: #fee2e2; color: #dc2626; }
.badge-amber { background: #fef3c7; color: #92400e; }
.badge-blue { background: #dbeafe; color: #1d4ed8; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { background: #f8f8f8; padding: 8px 10px; text-align: left; font-weight: 700; color: #666; font-size: 11px; text-transform: uppercase; border-bottom: 2px solid #e0e0e0; }
td { padding: 8px 10px; border-bottom: 1px solid #f0f0f0; }
tr:hover td { background: #fafafa; }
.added { background: #f0fdf4; }
.removed { background: #fef2f2; }
.changed { background: #fffbeb; }
.summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; }
.stat { background: #fff; border-radius: 10px; padding: 14px 16px; border: 1px solid #e0e0e0; text-align: center; }
.stat-num { font-size: 24px; font-weight: 800; }
.stat-label { font-size: 11px; color: #999; margin-top: 2px; }
.local-only { color: #16a34a; font-weight: 600; }
.online-only { color: #dc2626; font-weight: 600; }
.diff { color: #d97706; font-weight: 600; }
pre { background: #f8f8f8; padding: 10px; border-radius: 6px; font-size: 12px; overflow-x: auto; white-space: pre-wrap; margin-top: 10px; border: 1px solid #e0e0e0; }
.sql-block { background: #1a1a2e; color: #e0e0e0; padding: 14px; border-radius: 8px; font-family: monospace; font-size: 12px; white-space: pre-wrap; margin-top: 10px; }
.sql-block .keyword { color: #569cd6; }
.sql-block .table-name { color: #4ec9b0; }
.sql-block .col-name { color: #ce9178; }
</style>
</head>
<body>

<h1>Database Structure Comparison</h1>
<div class="sub">Compare LOCAL database with ONLINE production database</div>

<?php
// Check if online credentials are provided via POST or hardcoded
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $onlineHost = $_POST['host'] ?? '';
    $onlineDb   = $_POST['db'] ?? '';
    $onlineUser = $_POST['user'] ?? '';
    $onlinePass = $_POST['pass'] ?? '';
}

// Handle JSON upload mode
$jsonMode = false;
$onlineStructFromJson = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'json' && isset($_FILES['jsonfile'])) {
    $jsonContent = file_get_contents($_FILES['jsonfile']['tmp_name']);
    $jsonData = json_decode($jsonContent, true);
    if ($jsonData && !empty($jsonData['tables'])) {
        $jsonMode = true;
        $onlineStructFromJson = [];
        foreach ($jsonData['tables'] as $tbl => $info) {
            $onlineStructFromJson[$tbl] = $info['columns'] ?? $info;
        }
        $onlineHost = $jsonData['host'] ?? 'remote';
        $onlineDb = $jsonData['database'] ?? 'unknown';
        $onlineUser = 'json-import';
    } else {
        echo '<div class="card" style="background:#fef2f2;border-color:#fecaca"><div class="badge badge-red">Invalid JSON file. Make sure you uploaded the file from db-export-structure.php</div></div>';
    }
}

if (!$jsonMode && (empty($onlineHost) || empty($onlineDb) || empty($onlineUser))):
?>

<div class="config-form">
    <div style="font-size:14px;font-weight:700;margin-bottom:14px">Option 1: Direct Connection</div>
    <form method="POST">
        <div class="row">
            <div><label>Host</label><input name="host" placeholder="e.g., sql123.epizy.com"></div>
            <div><label>Database</label><input name="db" placeholder="e.g., matrimony_prod"></div>
            <div><label>Username</label><input name="user" placeholder="e.g., root"></div>
            <div><label>Password</label><input name="pass" type="password" placeholder="Password"></div>
        </div>
        <button type="submit">Compare via Connection</button>
    </form>

    <div style="border-top:1px solid #eee;margin:20px 0;padding-top:16px">
        <div style="font-size:14px;font-weight:700;margin-bottom:6px">Option 2: Upload JSON (Recommended for remote hosts)</div>
        <div style="font-size:12px;color:#999;margin-bottom:12px">
            1. Upload <code>db-export-structure.php</code> to your online server<br>
            2. Open it in browser — it downloads <code>db-structure-online.json</code><br>
            3. Upload that JSON file here:
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="mode" value="json">
            <input type="file" name="jsonfile" accept=".json" required style="margin-bottom:10px">
            <button type="submit">Compare via JSON</button>
        </form>
    </div>
</div>

<?php
    // Also show local DB structure
    try {
        $local = new PDO("mysql:host=$localHost;dbname=$localDb", $localUser, $localPass);
        $local->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $tables = $local->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        echo '<div class="card"><div class="card-title">Local Database: <span class="badge badge-blue">' . count($tables) . ' tables</span></div>';
        echo '<table><thead><tr><th>#</th><th>Table Name</th><th>Columns</th><th>Rows</th></tr></thead><tbody>';
        foreach ($tables as $i => $t) {
            $cols = $local->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='$localDb' AND table_name='$t'")->fetchColumn();
            $rows = $local->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            echo "<tr><td>" . ($i+1) . "</td><td><strong>$t</strong></td><td>$cols</td><td>$rows</td></tr>";
        }
        echo '</tbody></table></div>';
    } catch (Exception $e) {
        echo '<div class="card"><div class="badge badge-red">Local DB Error: ' . $e->getMessage() . '</div></div>';
    }

else:
    // ── COMPARE ──
    try {
        $local = new PDO("mysql:host=$localHost;dbname=$localDb", $localUser, $localPass);
        $local->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        die('<div class="card badge badge-red">Local DB Error: ' . $e->getMessage() . '</div>');
    }

    if (!$jsonMode) {
        try {
            $online = new PDO("mysql:host=$onlineHost;dbname=$onlineDb", $onlineUser, $onlinePass);
            $online->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            die('<div class="card" style="background:#fef2f2;border-color:#fecaca;padding:20px"><strong style="color:#dc2626">Online DB Error:</strong> ' . $e->getMessage() . '<br><br><strong>Tip:</strong> Most hosting providers block remote MySQL connections. Use <strong>Option 2 (JSON upload)</strong> instead:<br>1. Upload <code>db-export-structure.php</code> to your online server<br>2. Open it in browser to download the JSON<br>3. Upload the JSON here</div>');
        }
    }

    function getTableStructure($pdo, $dbName) {
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $structure = [];
        foreach ($tables as $t) {
            $cols = $pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
            $structure[$t] = [];
            foreach ($cols as $c) {
                $structure[$t][$c['Field']] = [
                    'type' => $c['Type'],
                    'null' => $c['Null'],
                    'key' => $c['Key'],
                    'default' => $c['Default'],
                    'extra' => $c['Extra'],
                ];
            }
        }
        return $structure;
    }

    $localStruct = getTableStructure($local, $localDb);
    $onlineStruct = $jsonMode ? $onlineStructFromJson : getTableStructure($online, $onlineDb);

    $allTables = array_unique(array_merge(array_keys($localStruct), array_keys($onlineStruct)));
    sort($allTables);

    // Stats
    $localOnly = $onlineOnly = $matched = $diffTables = 0;
    $newCols = $removedCols = $changedCols = 0;
    $sqlStatements = [];

    foreach ($allTables as $table) {
        $inLocal = isset($localStruct[$table]);
        $inOnline = isset($onlineStruct[$table]);
        if ($inLocal && !$inOnline) $localOnly++;
        elseif (!$inLocal && $inOnline) $onlineOnly++;
        else {
            $localCols = $localStruct[$table];
            $onlineCols = $onlineStruct[$table];
            $hasDiff = false;
            foreach ($localCols as $col => $def) {
                if (!isset($onlineCols[$col])) { $newCols++; $hasDiff = true; }
                elseif ($def['type'] !== $onlineCols[$col]['type'] || $def['null'] !== $onlineCols[$col]['null']) { $changedCols++; $hasDiff = true; }
            }
            foreach ($onlineCols as $col => $def) {
                if (!isset($localCols[$col])) { $removedCols++; $hasDiff = true; }
            }
            if ($hasDiff) $diffTables++;
            else $matched++;
        }
    }

    // Summary
    echo '<div class="summary">';
    echo '<div class="stat"><div class="stat-num" style="color:#16a34a">' . $matched . '</div><div class="stat-label">Tables Match</div></div>';
    echo '<div class="stat"><div class="stat-num" style="color:#d97706">' . $diffTables . '</div><div class="stat-label">Tables Different</div></div>';
    echo '<div class="stat"><div class="stat-num" style="color:#2563eb">' . $localOnly . '</div><div class="stat-label">Local Only</div></div>';
    echo '<div class="stat"><div class="stat-num" style="color:#dc2626">' . $onlineOnly . '</div><div class="stat-label">Online Only</div></div>';
    echo '</div>';

    echo '<div class="summary">';
    echo '<div class="stat"><div class="stat-num local-only">' . $newCols . '</div><div class="stat-label">New Columns (Local)</div></div>';
    echo '<div class="stat"><div class="stat-num online-only">' . $removedCols . '</div><div class="stat-label">Online Only Columns</div></div>';
    echo '<div class="stat"><div class="stat-num diff">' . $changedCols . '</div><div class="stat-label">Changed Columns</div></div>';
    echo '<div class="stat"><div class="stat-num">' . count($allTables) . '</div><div class="stat-label">Total Tables</div></div>';
    echo '</div>';

    // Detail per table
    foreach ($allTables as $table) {
        $inLocal = isset($localStruct[$table]);
        $inOnline = isset($onlineStruct[$table]);

        if ($inLocal && !$inOnline) {
            echo '<div class="card added"><div class="card-title">' . $table . ' <span class="badge badge-green">LOCAL ONLY — needs CREATE on online</span></div>';
            // Generate CREATE TABLE SQL
            $createSql = $local->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            $sqlStatements[] = $createSql['Create Table'] . ';';
            echo '<pre>' . htmlspecialchars($createSql['Create Table']) . '</pre></div>';
            continue;
        }
        if (!$inLocal && $inOnline) {
            echo '<div class="card removed"><div class="card-title">' . $table . ' <span class="badge badge-red">ONLINE ONLY — not in local</span></div></div>';
            continue;
        }

        // Both exist — compare columns
        $localCols = $localStruct[$table];
        $onlineCols = $onlineStruct[$table];
        $diffs = [];

        foreach ($localCols as $col => $def) {
            if (!isset($onlineCols[$col])) {
                $diffs[] = ['col' => $col, 'status' => 'added', 'local' => $def, 'online' => null];
                $afterCol = '';
                $colNames = array_keys($localCols);
                $idx = array_search($col, $colNames);
                if ($idx > 0) $afterCol = ' AFTER `' . $colNames[$idx - 1] . '`';
                $nullable = $def['null'] === 'YES' ? 'NULL' : 'NOT NULL';
                $default = $def['default'] !== null ? " DEFAULT '" . $def['default'] . "'" : '';
                $extra = $def['extra'] ? ' ' . $def['extra'] : '';
                $sqlStatements[] = "ALTER TABLE `$table` ADD COLUMN `$col` {$def['type']} $nullable$default$extra$afterCol;";
            } elseif ($def['type'] !== $onlineCols[$col]['type'] || $def['null'] !== $onlineCols[$col]['null']) {
                $diffs[] = ['col' => $col, 'status' => 'changed', 'local' => $def, 'online' => $onlineCols[$col]];
                $nullable = $def['null'] === 'YES' ? 'NULL' : 'NOT NULL';
                $default = $def['default'] !== null ? " DEFAULT '" . $def['default'] . "'" : '';
                $sqlStatements[] = "ALTER TABLE `$table` MODIFY COLUMN `$col` {$def['type']} $nullable$default;";
            }
        }
        foreach ($onlineCols as $col => $def) {
            if (!isset($localCols[$col])) {
                $diffs[] = ['col' => $col, 'status' => 'removed', 'local' => null, 'online' => $def];
            }
        }

        if (empty($diffs)) continue; // Skip matched tables

        echo '<div class="card"><div class="card-title">' . $table . ' <span class="badge badge-amber">' . count($diffs) . ' differences</span></div>';
        echo '<table><thead><tr><th>Column</th><th>Status</th><th>Local Type</th><th>Online Type</th><th>Local Null</th><th>Online Null</th><th>Local Default</th><th>Online Default</th></tr></thead><tbody>';
        foreach ($diffs as $d) {
            $cls = $d['status'] === 'added' ? 'added' : ($d['status'] === 'removed' ? 'removed' : 'changed');
            $statusBadge = $d['status'] === 'added' ? '<span class="badge badge-green">NEW in Local</span>'
                : ($d['status'] === 'removed' ? '<span class="badge badge-red">Online Only</span>'
                : '<span class="badge badge-amber">Changed</span>');
            echo "<tr class='$cls'>";
            echo "<td><strong>{$d['col']}</strong></td>";
            echo "<td>$statusBadge</td>";
            echo "<td>" . ($d['local']['type'] ?? '—') . "</td>";
            echo "<td>" . ($d['online']['type'] ?? '—') . "</td>";
            echo "<td>" . ($d['local']['null'] ?? '—') . "</td>";
            echo "<td>" . ($d['online']['null'] ?? '—') . "</td>";
            echo "<td>" . ($d['local']['default'] ?? '—') . "</td>";
            echo "<td>" . ($d['online']['default'] ?? '—') . "</td>";
            echo "</tr>";
        }
        echo '</tbody></table></div>';
    }

    // SQL Migration Script
    if (!empty($sqlStatements)) {
        echo '<div class="card"><div class="card-title">SQL Migration Script <span class="badge badge-blue">Run on ONLINE DB to sync</span></div>';
        echo '<div class="sql-block">';
        echo "-- Migration SQL: Local → Online\n";
        echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        echo "-- Run these on your ONLINE database to match LOCAL\n\n";
        foreach ($sqlStatements as $sql) {
            echo htmlspecialchars($sql) . "\n\n";
        }
        echo '</div>';
        echo '<button onclick="navigator.clipboard.writeText(document.querySelector(\'.sql-block\').textContent).then(()=>alert(\'Copied!\'))" style="margin-top:10px;padding:8px 18px;background:#8B0000;color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer">Copy SQL</button>';
        echo '</div>';
    }

endif;
?>

</body>
</html>
