<?php
// Runtime helpers — included by config.php (generated) and local dev config.
// Contains no credentials; safe to commit.

declare(strict_types=1);

// ── Runtime limits ───────────────────────────────────────────────────────────
@ini_set('memory_limit', '256M');
@set_time_limit(30);

// ── Cache (APCu if available, file fallback otherwise) ──────────────────────
define('CACHE_DIR', __DIR__ . '/logs/cache');
if (!is_dir(CACHE_DIR)) @mkdir(CACHE_DIR, 0755, true);

function cache_has_apcu(): bool {
    static $ok = null;
    if ($ok === null) $ok = function_exists('apcu_fetch') && ini_get('apc.enabled');
    return $ok;
}

function cache_get(string $key) {
    if (cache_has_apcu()) {
        $ok = false;
        $v = apcu_fetch($key, $ok);
        return $ok ? $v : null;
    }
    $path = CACHE_DIR . '/' . md5($key) . '.cache';
    if (!is_file($path)) return null;
    $raw = @file_get_contents($path);
    if (!$raw) return null;
    $entry = @unserialize($raw);
    if (!is_array($entry) || ($entry['exp'] ?? 0) < time()) { @unlink($path); return null; }
    return $entry['val'];
}

function cache_set(string $key, $val, int $ttl = 60): void {
    if (cache_has_apcu()) { @apcu_store($key, $val, $ttl); return; }
    $path = CACHE_DIR . '/' . md5($key) . '.cache';
    @file_put_contents($path, serialize(['exp' => time() + $ttl, 'val' => $val]), LOCK_EX);
}

function cache_delete(string $key): void {
    if (cache_has_apcu()) @apcu_delete($key);
    $path = CACHE_DIR . '/' . md5($key) . '.cache';
    if (is_file($path)) @unlink($path);
}

function cache_remember(string $key, int $ttl, callable $producer) {
    $hit = cache_get($key);
    if ($hit !== null) return $hit;
    $val = $producer();
    cache_set($key, $val, $ttl);
    return $val;
}

// ── Error logging ────────────────────────────────────────────────────────────
define('LOG_DIR', __DIR__ . '/logs');
define('LOG_RETENTION_DAYS', 30);

if (!is_dir(LOG_DIR)) @mkdir(LOG_DIR, 0755, true);
$htaFile = LOG_DIR . '/.htaccess';
if (!is_file($htaFile)) @file_put_contents($htaFile, "Require all denied\nDeny from all\n");

function log_write(string $level, string $msg, array $ctx = []): void {
    $entry = [
        'ts'     => date('c'),
        'level'  => $level,
        'msg'    => $msg,
        'action' => $_GET['action'] ?? $_POST['action'] ?? null,
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
        'uri'    => $_SERVER['REQUEST_URI'] ?? null,
        'ip'     => $_SERVER['REMOTE_ADDR'] ?? null,
        'mem_mb' => round(memory_get_peak_usage(true) / 1048576, 1),
    ];
    if (!empty($ctx)) $entry['ctx'] = $ctx;
    $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    $file = LOG_DIR . '/errors-' . date('Y-m-d') . '.log';
    @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
}

function log_error(string $msg, array $ctx = []): void { log_write('error', $msg, $ctx); }
function log_warn(string $msg, array $ctx = []):  void { log_write('warn',  $msg, $ctx); }
function log_info(string $msg, array $ctx = []):  void { log_write('info',  $msg, $ctx); }

$markerFile = LOG_DIR . '/.last_prune';
$today = date('Y-m-d');
if (@file_get_contents($markerFile) !== $today) {
    @file_put_contents($markerFile, $today);
    $cutoff = time() - (LOG_RETENTION_DAYS * 86400);
    foreach (glob(LOG_DIR . '/errors-*.log') ?: [] as $f) {
        if (@filemtime($f) < $cutoff) @unlink($f);
    }
}

set_error_handler(function($severity, $msg, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($msg, 0, $severity, $file, $line);
});

set_exception_handler(function(Throwable $e) {
    log_error($e->getMessage(), [
        'exception' => get_class($e),
        'file'      => $e->getFile(),
        'line'      => $e->getLine(),
        'trace'     => array_slice(array_map(function($f) {
            return ($f['file'] ?? '?') . ':' . ($f['line'] ?? '?') . ' ' . ($f['function'] ?? '');
        }, $e->getTrace()), 0, 5),
    ]);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(['ok' => false, 'error' => 'server_error']);
});

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        log_error('FATAL: ' . $err['message'], ['file' => $err['file'], 'line' => $err['line'], 'type' => $err['type']]);
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'server_error']);
        }
    }
});

@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
error_reporting(E_ALL);

// ── Blocklist + rate-limit ───────────────────────────────────────────────────
require_once __DIR__ . '/rate-limit.php';
enforce_blocklist();

define('SLOW_REQUEST_THRESHOLD_S', 3.0);
$GLOBALS['__req_started_at'] = microtime(true);
register_shutdown_function(function() {
    $elapsed = microtime(true) - ($GLOBALS['__req_started_at'] ?? microtime(true));
    if ($elapsed > SLOW_REQUEST_THRESHOLD_S) {
        log_warn('slow_request', ['elapsed_s' => round($elapsed, 2), 'status' => http_response_code()]);
    }
});

// ── Database connection (PDO, singleton) ─────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            log_error('DB connection failed: ' . $e->getMessage(), ['code' => $e->getCode(), 'host' => DB_HOST, 'db' => DB_NAME]);
            http_response_code(503);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'service_unavailable']);
            exit;
        }
    }
    return $pdo;
}

// ── CORS + JSON headers ───────────────────────────────────────────────────────
function cors(): void {
    $allowed = $_SERVER['HTTP_ORIGIN'] ?? '*';
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . $allowed);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('X-Content-Type-Options: nosniff');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
}

function json_ok(array $data = []): void {
    echo json_encode(['ok' => true] + $data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_err(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function authRequired(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['mobile'])) json_err('Not authenticated', 401);
    return (string) $_SESSION['mobile'];
}

function body(): array {
    static $parsed = null;
    if ($parsed === null) {
        $raw    = file_get_contents('php://input');
        $parsed = json_decode($raw, true) ?? [];
    }
    return $parsed;
}

function str_clean(?string $val, int $maxLen = 255): string {
    return mb_substr(trim((string) $val), 0, $maxLen);
}

function nextCpId(PDO $db): string {
    $stmt = $db->query("SELECT cp_id FROM profiles WHERE cp_id LIKE 'CM%' ORDER BY cp_id DESC LIMIT 1");
    $last = $stmt->fetchColumn();
    if ($last && preg_match('/^CM(\d+)$/', $last, $m)) {
        return 'CM' . ((int)$m[1] + 1);
    }
    return 'CM2011001';
}

function resolve_photo_url(?string $val): ?string {
    if (!$val || str_starts_with($val, 'default_') || str_starts_with($val, 'http')) return $val;
    if (!defined('S3_ENABLED') || !S3_ENABLED) return $val;
    $bare = ltrim(preg_replace('#^uploads[/\\\\]#i', '', trim($val)), '/\\');
    return 'https://' . S3_BUCKET . '.s3.' . S3_REGION . '.amazonaws.com/photos/' . $bare;
}

function resolve_profile_photos(array &$row): void {
    foreach (['photo1', 'photo2', 'photo3', 'rasi_photo', 'amsam_photo'] as $col) {
        if (isset($row[$col])) $row[$col] = resolve_photo_url($row[$col]);
    }
}

function secureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $lifetime = 12 * 60 * 60;
        @ini_set('session.gc_maxlifetime', (string)$lifetime);
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => $cookieParams['domain'],
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}
