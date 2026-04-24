<?php
// Admin blocklist manager.
//   GET                      → list all active bans + recent rate_limit events
//   POST { ip, ttl?, reason } → ban an IP (ttl defaults to 24h)
//   DELETE ?ip=x.x.x.x       → unban

require_once __DIR__ . '/../../admin-config.php';
cors();
$admin = adminRequired();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Active bans from disk (source of truth).
    $now = time();
    $bans = [];
    foreach (glob(BLOCKLIST_DIR . '/*.json') ?: [] as $f) {
        $entry = json_decode((string)@file_get_contents($f), true);
        if (!$entry) continue;
        if (($entry['until'] ?? 0) < $now) { @unlink($f); continue; }
        $bans[] = [
            'ip'      => $entry['ip'],
            'until'   => $entry['until'],
            'expires' => date('c', $entry['until']),
            'reason'  => $entry['reason'] ?? '',
            'added'   => date('c', $entry['added'] ?? 0),
        ];
    }
    usort($bans, fn($a, $b) => $b['added'] <=> $a['added']);

    // Recent rate-limit events from today's log for context (top 20 offenders).
    $offenders = [];
    $logFile = LOG_DIR . '/errors-' . date('Y-m-d') . '.log';
    if (is_file($logFile) && filesize($logFile) > 0) {
        $fh = fopen($logFile, 'r');
        $readLen = min(filesize($logFile), 2097152); // last 2 MB
        fseek($fh, -$readLen, SEEK_END);
        $chunk = fread($fh, $readLen);
        fclose($fh);
        foreach (explode("\n", $chunk) as $line) {
            if ($line === '' || strpos($line, 'rate_limited') === false) continue;
            $e = json_decode($line, true);
            if (!$e || !isset($e['ctx']['ip'])) continue;
            $ip = $e['ctx']['ip'];
            $offenders[$ip] = ($offenders[$ip] ?? 0) + 1;
        }
        arsort($offenders);
        $offenders = array_slice($offenders, 0, 20, true);
    }

    json_ok(['bans' => $bans, 'top_rate_limited' => $offenders]);
}

if ($method === 'POST') {
    $b   = body();
    $ip  = trim($b['ip'] ?? '');
    $ttl = (int)($b['ttl'] ?? 86400);
    $rsn = trim($b['reason'] ?? 'admin_manual');
    if (!filter_var($ip, FILTER_VALIDATE_IP)) json_err('Invalid IP', 400);
    if ($ttl < 60 || $ttl > 2592000) json_err('ttl must be 60–2592000 s', 400);
    add_to_blocklist($ip, $ttl, $rsn);
    json_ok(['banned' => $ip, 'until' => date('c', time() + $ttl)]);
}

if ($method === 'DELETE') {
    $ip = trim($_GET['ip'] ?? '');
    if (!filter_var($ip, FILTER_VALIDATE_IP)) json_err('Invalid IP', 400);
    remove_from_blocklist($ip);
    log_info('ip_unbanned', ['ip' => $ip, 'by' => $admin]);
    json_ok(['unbanned' => $ip]);
}

json_err('Method not allowed', 405);
