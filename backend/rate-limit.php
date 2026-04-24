<?php
// Rate limiting, IP blocklist, and bot-signature checks.
// Backed by APCu when available (µs-fast in-memory), file fallback otherwise.
// Integrates with the logger from config.php — include config.php FIRST.

define('BLOCKLIST_DIR', __DIR__ . '/logs/blocked');
if (!is_dir(BLOCKLIST_DIR)) @mkdir(BLOCKLIST_DIR, 0755, true);

// ── Real client IP ──────────────────────────────────────────────────────────
// If behind Cloudflare, trust CF-Connecting-IP. Never blindly trust
// X-Forwarded-For — it's trivially spoofable when not behind a proxy.
function client_ip(): string {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ── Blocklist ────────────────────────────────────────────────────────────────
function blocklist_path(string $ip): string {
    return BLOCKLIST_DIR . '/' . md5($ip) . '.json';
}

function add_to_blocklist(string $ip, int $ttlSeconds, string $reason = ''): void {
    $entry = ['ip' => $ip, 'until' => time() + $ttlSeconds, 'reason' => $reason, 'added' => time()];
    @file_put_contents(blocklist_path($ip), json_encode($entry), LOCK_EX);
    cache_set("block:$ip", $entry['until'], $ttlSeconds);
    log_warn('ip_blocked', ['ip' => $ip, 'ttl' => $ttlSeconds, 'reason' => $reason]);
}

function remove_from_blocklist(string $ip): void {
    @unlink(blocklist_path($ip));
    cache_delete("block:$ip");
}

function is_blocked(string $ip): ?int {
    // Fast path: APCu/file cache
    $until = cache_get("block:$ip");
    if ($until && $until > time()) return $until;

    // Slow path: disk file
    $p = blocklist_path($ip);
    if (!is_file($p)) return null;
    $entry = json_decode((string)@file_get_contents($p), true);
    if (!$entry || ($entry['until'] ?? 0) < time()) { @unlink($p); return null; }

    cache_set("block:$ip", $entry['until'], $entry['until'] - time());
    return $entry['until'];
}

// Called at the very top of every request in config.php — fast 403 if banned.
function enforce_blocklist(): void {
    $ip = client_ip();
    $until = is_blocked($ip);
    if ($until === null) return;
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    header('Retry-After: ' . max(1, $until - time()));
    echo json_encode(['ok' => false, 'error' => 'blocked']);
    exit;
}

// ── Rate limiter (fixed-window, APCu/file-backed) ───────────────────────────
// Returns TRUE if the request is allowed. Sends a 429 and exits otherwise.
// On violation, auto-escalates to a blocklist entry if the IP has been
// rate-limited >10 times in 5 minutes.
function rate_limit(string $bucket, int $max, int $windowSec, ?string $subject = null): void {
    $ip  = client_ip();
    $sub = $subject !== null ? $subject : $ip;
    $window = (int) floor(time() / $windowSec);
    $key = "rl:{$bucket}:{$sub}:{$window}";

    $count = 1;
    if (cache_has_apcu()) {
        // Atomic increment; sets to 1 on miss.
        $ok = false;
        apcu_inc($key, 1, $ok, $windowSec);
        if (!$ok) {
            apcu_store($key, 1, $windowSec);
            $count = 1;
        } else {
            $count = (int) apcu_fetch($key);
        }
    } else {
        $cur = cache_get($key);
        $count = (int)$cur + 1;
        cache_set($key, $count, $windowSec);
    }

    if ($count > $max) {
        // Track violations per IP across a rolling 5-minute window.
        $vKey = 'rl_violations:' . $ip . ':' . (int)floor(time() / 300);
        $violations = (int) (cache_get($vKey) ?? 0) + 1;
        cache_set($vKey, $violations, 300);

        log_warn('rate_limited', [
            'ip' => $ip, 'bucket' => $bucket, 'count' => $count,
            'limit' => $max, 'window_s' => $windowSec, 'violations_5m' => $violations,
        ]);

        // Auto-ban escalation: persistent offender → blocklist
        if ($violations > 10)      add_to_blocklist($ip, 3600,  'rate_limit: 10+ violations/5m');
        elseif ($violations > 30)  add_to_blocklist($ip, 86400, 'rate_limit: 30+ violations/10m');

        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        header('Retry-After: ' . $windowSec);
        echo json_encode([
            'ok' => false, 'error' => 'rate_limited',
            'retry_after' => $windowSec,
        ]);
        exit;
    }
}

// ── Bot-signature checks ─────────────────────────────────────────────────────
// Returns TRUE if the request looks bot-like. Non-exhaustive — pairs with
// Cloudflare Bot Fight Mode. These are cheap heuristics that catch basic
// scripts (curl, python requests without overrides, headless crawlers).
function looks_like_bot(): bool {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ($ua === '') return true;

    // Known bad substrings (lowercased match)
    $uaLower = strtolower($ua);
    $badUA = ['bot', 'crawler', 'spider', 'scrape', 'http-client', 'python-requests',
              'curl/', 'wget/', 'go-http-client', 'okhttp/', 'java/', 'libwww'];
    foreach ($badUA as $needle) {
        if (strpos($uaLower, $needle) !== false) return true;
    }

    // Real browsers always send Accept-Language
    if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) return true;

    return false;
}

// Soft protection: reject bot-like POSTs to sensitive mutation actions.
function block_bots_on_sensitive(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    if (!looks_like_bot()) return;
    log_warn('bot_blocked', [
        'ip' => client_ip(),
        'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200),
        'action' => $_GET['action'] ?? $_POST['action'] ?? null,
    ]);
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'forbidden']);
    exit;
}

// Honeypot: reject if a hidden form field ("website") was filled.
// Real humans don't see it; bots that auto-fill every field trip it.
function check_honeypot(array $input): void {
    if (!empty($input['website']) || !empty($input['url']) || !empty($input['fax'])) {
        log_warn('honeypot_triggered', ['ip' => client_ip(), 'action' => $input['action'] ?? null]);
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'bad_request']);
        exit;
    }
}
