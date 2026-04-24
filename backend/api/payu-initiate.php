<?php
// matrimony/backend/api/payu-initiate.php
// User picks a plan → this script creates a pending order and auto-submits an
// HTML form to PayU's hosted payment page.

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../payu-config.php';

secureSession();
if (empty($_SESSION['mobile'])) {
    http_response_code(401);
    header('Content-Type: text/html; charset=utf-8');
    echo '<h3>Not authenticated</h3><p>Please log in first.</p>';
    exit;
}
$mobile = (string) $_SESSION['mobile'];
$db     = getDB();

$planId = trim($_POST['plan_id'] ?? $_GET['plan_id'] ?? '');
if ($planId === '') {
    http_response_code(400);
    echo 'plan_id required'; exit;
}

$stmt = $db->prepare("SELECT plan_id, name, amount, validity FROM subscription_plans WHERE plan_id = :p AND status = 'active' LIMIT 1");
$stmt->execute([':p' => $planId]);
$plan = $stmt->fetch();
if (!$plan) { http_response_code(404); echo 'Invalid plan'; exit; }

$amount = number_format((float) $plan['amount'], 2, '.', '');
if ((float) $amount <= 0) {
    header('Location: ../user-panel.php?pay=free'); exit;
}

$prof = $db->prepare("SELECT cp_id, name, email FROM profiles WHERE mobile = :m LIMIT 1");
$prof->execute([':m' => $mobile]);
$profile = $prof->fetch();
if (!$profile) { http_response_code(404); echo 'Profile not found'; exit; }

$firstname = preg_replace('/[^A-Za-z0-9 ]/', '', $profile['name'] ?: 'User');
$email     = $profile['email'] ?: ($mobile . '@matrimony.local');
$phone     = $mobile;

$db->prepare("INSERT INTO user_orders (mobile, cp_id, name, plan, amount, method, txn_ref, notes, status, created_at)
              VALUES (:m, :cp, :n, :pl, :a, 'payumoney', '', :nt, 'pending', NOW())")
   ->execute([
       ':m'  => $mobile,
       ':cp' => $profile['cp_id'] ?? '',
       ':n'  => $profile['name'] ?? '',
       ':pl' => $plan['name'],
       ':a'  => $amount,
       ':nt' => 'PayU initiated for ' . $plan['name'],
   ]);
$orderId = (int) $db->lastInsertId();

$txnid = 'MTRM' . $orderId . '_' . bin2hex(random_bytes(4));

// Persist txnid back onto the order so the return handler can correlate.
$db->prepare("UPDATE user_orders SET txn_ref = :t WHERE id = :id")
   ->execute([':t' => $txnid, ':id' => $orderId]);

$base = payuBaseUrl();
$params = [
    'key'         => PAYU_KEY,
    'txnid'       => $txnid,
    'amount'      => $amount,
    'productinfo' => $plan['name'],
    'firstname'   => $firstname ?: 'User',
    'email'       => $email,
    'phone'       => $phone,
    'surl'        => $base . '/api/payu-return.php',
    'furl'        => $base . '/api/payu-return.php',
    'udf1'        => (string) $orderId,
    'udf2'        => $mobile,
    'udf3'        => $plan['plan_id'],
    'udf4'        => (string) $plan['validity'],
    'udf5'        => $profile['cp_id'] ?? '',
    'service_provider' => 'payu_paisa',
];
$params['hash'] = payuRequestHash($params);

header('Content-Type: text/html; charset=utf-8');
?><!doctype html>
<html><head><meta charset="utf-8"><title>Redirecting to PayU…</title>
<style>body{font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;background:#f8f9fa;color:#333;margin:0}.box{text-align:center}.sp{width:42px;height:42px;border:4px solid #e5e7eb;border-top-color:#c2553d;border-radius:50%;margin:0 auto 18px;animation:s 1s linear infinite}@keyframes s{to{transform:rotate(360deg)}}</style></head>
<body><div class="box"><div class="sp"></div><h3>Redirecting to PayU…</h3><p>Please do not close this window.</p>
<form id="payu" action="<?php echo htmlspecialchars(PAYU_ENDPOINT); ?>" method="post">
<?php foreach ($params as $k => $v): ?>
  <input type="hidden" name="<?php echo htmlspecialchars($k); ?>" value="<?php echo htmlspecialchars((string)$v); ?>">
<?php endforeach; ?>
<noscript><button type="submit">Continue to PayU</button></noscript>
</form>
<script>document.getElementById('payu').submit();</script>
</div></body></html>
