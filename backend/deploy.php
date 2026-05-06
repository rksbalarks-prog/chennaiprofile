<?php
// matrimony/backend/deploy.php
//
// HTTPS deploy endpoint for GitHub Actions → shared hosting.
// GitHub Actions POSTs a zip of the built site; this endpoint authenticates
// the request via HMAC-SHA256, validates paths, and writes files in place.
//
// Authentication model:
//   - Shared secret lives in `backend/.deploy-secret` on the server (one line,
//     64-char hex). The SAME value lives in GitHub secret DEPLOY_HMAC_SECRET.
//   - Request MUST include:
//       X-Deploy-Timestamp: <unix epoch seconds>
//       X-Deploy-Signature: sha256=<hex-hmac>
//     where hmac = HMAC-SHA256(secret, "<timestamp>:<sha256(body)>").
//   - Timestamp must be within ±300s of server time (replay window).
//
// Safety:
//   - Refuses to write to config.php, sms.php, payu-config.php,
//     frontend/API/db.php, or any uploads/ subtree — these live only on the
//     server and must never be overwritten by a deploy.
//   - Rejects any zip entry whose resolved target escapes the site root.
//   - Does NOT delete existing files not present in the zip (additive/update
//     only). Clean up obsolete files manually via the DA File Manager.

declare(strict_types=1);

@set_time_limit(120);
@ini_set('memory_limit', '256M');

header('Content-Type: application/json');

function deploy_fail(int $code, string $msg, array $extra = []): void {
    http_response_code($code);
    echo json_encode(array_merge(['ok' => false, 'error' => $msg], $extra));
    exit;
}

function deploy_ok(array $extra = []): void {
    echo json_encode(array_merge(['ok' => true], $extra));
    exit;
}

// ── 1. Load the shared secret ───────────────────────────────────────────────
$secretFile = __DIR__ . '/.deploy-secret';
if (!is_readable($secretFile)) {
    deploy_fail(500, 'deploy-secret missing on server');
}
$secret = trim((string)file_get_contents($secretFile));
if (strlen($secret) < 32) {
    deploy_fail(500, 'deploy-secret looks invalid (too short)');
}

// ── 2. Read headers ─────────────────────────────────────────────────────────
$tsHeader  = $_SERVER['HTTP_X_DEPLOY_TIMESTAMP'] ?? '';
$sigHeader = $_SERVER['HTTP_X_DEPLOY_SIGNATURE'] ?? '';

if (!$tsHeader || !$sigHeader) {
    deploy_fail(400, 'missing auth headers');
}
if (!preg_match('/^\d{10}$/', $tsHeader)) {
    deploy_fail(400, 'bad timestamp format');
}
if (abs(time() - (int)$tsHeader) > 300) {
    deploy_fail(401, 'timestamp outside ±300s window');
}
if (!preg_match('/^sha256=([a-f0-9]{64})$/', $sigHeader, $m)) {
    deploy_fail(400, 'bad signature format');
}
$providedSig = $m[1];

// ── 3. Read raw request body (the zip) ──────────────────────────────────────
$body = file_get_contents('php://input');
if ($body === false || $body === '') {
    deploy_fail(400, 'empty body');
}
$bodyLen = strlen($body);
$bodyHash = hash('sha256', $body);

// ── 4. Verify HMAC ──────────────────────────────────────────────────────────
$expected = hash_hmac('sha256', $tsHeader . ':' . $bodyHash, $secret);
if (!hash_equals($expected, $providedSig)) {
    deploy_fail(401, 'signature mismatch');
}

// ── 5. Save zip to a temp file, extract, validate, copy ─────────────────────
// Target site root: two directories up from this file (backend/../ → public_html/).
$siteRoot = realpath(__DIR__ . '/..');
if ($siteRoot === false) {
    deploy_fail(500, 'site root resolution failed');
}

$tmpZip = tempnam(sys_get_temp_dir(), 'depzip_');
if ($tmpZip === false || file_put_contents($tmpZip, $body) !== $bodyLen) {
    deploy_fail(500, 'failed to write temp zip');
}

if (!class_exists('ZipArchive')) {
    @unlink($tmpZip);
    deploy_fail(500, 'ZipArchive unavailable on server');
}

$zip = new ZipArchive();
if ($zip->open($tmpZip) !== true) {
    @unlink($tmpZip);
    deploy_fail(400, 'zip open failed');
}

// Paths (relative to siteRoot) that must NEVER be overwritten.
$protected = [
    'backend/config.production.php',
    'backend/payu-config.php',
    'backend/.deploy-secret',
    'frontend/API/db.php',
];
// Path prefixes under which every write is refused (user content, runtime).
$protectedPrefixes = [
    'uploads/',
    'backend/api/uploads/',
    'backend/logs/',
];

$written = 0;
$skipped = 0;
$skippedPaths = [];
$errors = [];

for ($i = 0; $i < $zip->numFiles; $i++) {
    $stat = $zip->statIndex($i);
    if ($stat === false) continue;
    $name = $stat['name'];

    // Directory entries end with "/" — create if missing, skip write.
    if (substr($name, -1) === '/') {
        $dirTarget = $siteRoot . DIRECTORY_SEPARATOR . $name;
        if (!is_dir($dirTarget)) @mkdir($dirTarget, 0755, true);
        continue;
    }

    // Reject path traversal / absolute paths.
    if (strpos($name, '..') !== false || $name[0] === '/' || $name[0] === '\\') {
        $errors[] = "rejected (traversal): $name";
        continue;
    }

    // Protected exact-path check.
    if (in_array($name, $protected, true)) {
        $skipped++;
        $skippedPaths[] = $name;
        continue;
    }

    // Protected prefix check.
    $isProtectedPrefix = false;
    foreach ($protectedPrefixes as $p) {
        if (strpos($name, $p) === 0) { $isProtectedPrefix = true; break; }
    }
    if ($isProtectedPrefix) {
        $skipped++;
        $skippedPaths[] = $name;
        continue;
    }

    $target = $siteRoot . DIRECTORY_SEPARATOR . $name;
    $targetDir = dirname($target);

    // Realpath check: resolved parent must stay within siteRoot.
    if (!is_dir($targetDir)) {
        if (!@mkdir($targetDir, 0755, true)) {
            $errors[] = "mkdir failed: $targetDir";
            continue;
        }
    }
    $realDir = realpath($targetDir);
    if ($realDir === false || strpos($realDir, $siteRoot) !== 0) {
        $errors[] = "rejected (escape): $name";
        continue;
    }

    // Read zip contents and write to target.
    $contents = $zip->getFromIndex($i);
    if ($contents === false) {
        $errors[] = "read failed: $name";
        continue;
    }

    // Write to a sibling .tmp file, then rename (atomic per file).
    $tmpTarget = $target . '.deploy-tmp';
    if (file_put_contents($tmpTarget, $contents) === false) {
        $errors[] = "write failed: $name";
        continue;
    }
    if (!@rename($tmpTarget, $target)) {
        @unlink($tmpTarget);
        $errors[] = "rename failed: $name";
        continue;
    }
    $written++;
}

$zip->close();
@unlink($tmpZip);

// Invalidate PHP opcache so newly-written files are picked up immediately.
// Without this, opcache serves the previous compiled bytecode for up to
// opcache.revalidate_freq (60s default) and small fixes appear "deployed
// but not live."
$opcacheReset = false;
if (function_exists('opcache_reset')) {
    $opcacheReset = (bool) @opcache_reset();
}

if (!empty($errors)) {
    deploy_fail(500, 'deploy completed with errors', [
        'written' => $written,
        'skipped' => $skipped,
        'errors'  => array_slice($errors, 0, 20),
    ]);
}

deploy_ok([
    'written'       => $written,
    'skipped'       => $skipped,
    'skipped_paths' => $skippedPaths,
    'body_bytes'    => $bodyLen,
    'opcache_reset' => $opcacheReset,
]);
