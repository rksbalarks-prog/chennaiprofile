<?php
/**
 * Database Structure Export
 * Upload this file to your ONLINE server, open in browser to download structure JSON.
 * Then use db-compare.php locally with the downloaded JSON.
 */

// Auto-detect credentials from config file
$configFile = __DIR__ . '/config.php';
$prodConfig = __DIR__ . '/config.production.php';

if (file_exists($prodConfig)) {
    require $prodConfig;
} elseif (file_exists($configFile)) {
    require $configFile;
}

// Try to get DB connection
$host = defined('DB_HOST') ? DB_HOST : 'localhost';
$dbname = defined('DB_NAME') ? DB_NAME : '';
$user = defined('DB_USER') ? DB_USER : '';
$pass = defined('DB_PASS') ? DB_PASS : '';

if (empty($dbname)) {
    die(json_encode(['error' => 'No database config found. Set DB_HOST, DB_NAME, DB_USER, DB_PASS in config.php']));
}

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $structure = [];

    foreach ($tables as $t) {
        $cols = $db->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
        $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        $structure[$t] = [
            'columns' => [],
            'rows' => (int)$count
        ];
        foreach ($cols as $c) {
            $structure[$t]['columns'][$c['Field']] = [
                'type' => $c['Type'],
                'null' => $c['Null'],
                'key' => $c['Key'],
                'default' => $c['Default'],
                'extra' => $c['Extra'],
            ];
        }
    }

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="db-structure-online.json"');
    echo json_encode([
        'ok' => true,
        'host' => $host,
        'database' => $dbname,
        'exported_at' => date('Y-m-d H:i:s'),
        'tables' => $structure
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    die(json_encode(['error' => $e->getMessage()]));
}
