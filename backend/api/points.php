<?php
// Points system API — Chennai Profile only.
// Actions: balance, packages, history, buy_init, buy_return (GET callback from PayU)
require_once __DIR__ . '/../config.php';

cors();

define('POINTS_PER_CONTACT', 10);
define('POINTS_SITE_ID', 'chennaip');

$PACKAGES = [
    'p100'  => ['id' => 'p100',  'points' => 100,  'price' => 100,  'label' => '100 Points',  'badge' => ''],
    'p500'  => ['id' => 'p500',  'points' => 500,  'price' => 500,  'label' => '500 Points',  'badge' => 'Popular'],
    'p1000' => ['id' => 'p1000', 'points' => 1000, 'price' => 1000, 'label' => '1000 Points', 'badge' => 'Best Value'],
];

// ── Helpers ───────────────────────────────────────────────────────────────────

function pts_get_balance(PDO $db, string $mobile): int {
    $r = $db->prepare("SELECT balance FROM user_points WHERE mobile = :m");
    $r->execute([':m' => $mobile]);
    return (int)($r->fetchColumn() ?? 0);
}

function pts_ensure_row(PDO $db, string $mobile): void {
    $db->prepare("INSERT IGNORE INTO user_points (mobile, balance) VALUES (:m, 0)")
       ->execute([':m' => $mobile]);
}

function pts_credit(PDO $db, string $mobile, int $points, string $type, string $desc, string $refId = ''): int {
    pts_ensure_row($db, $mobile);
    $col = ($type === 'purchase') ? 'total_bought' : 'total_bought';
    $db->prepare("UPDATE user_points SET balance = balance + :p, total_bought = total_bought + :pp, updated_at = NOW() WHERE mobile = :m")
       ->execute([':p' => $points, ':pp' => ($type === 'purchase' ? $points : 0), ':m' => $mobile]);
    $bal = pts_get_balance($db, $mobile);
    $db->prepare("INSERT INTO point_transactions (mobile, type, points, balance_after, description, ref_id) VALUES (:m, :t, :p, :b, :d, :r)")
       ->execute([':m' => $mobile, ':t' => $type, ':p' => $points, ':b' => $bal, ':d' => $desc, ':r' => $refId]);
    return $bal;
}

// Returns new balance on success, false on insufficient funds
function pts_deduct(PDO $db, string $mobile, int $points, string $desc, string $refId = '') {
    pts_ensure_row($db, $mobile);
    $upd = $db->prepare("UPDATE user_points SET balance = balance - :p, total_used = total_used + :p, updated_at = NOW() WHERE mobile = :m AND balance >= :p");
    $upd->execute([':p' => $points, ':m' => $mobile]);
    if ($upd->rowCount() === 0) return false;
    $bal = pts_get_balance($db, $mobile);
    $db->prepare("INSERT INTO point_transactions (mobile, type, points, balance_after, description, ref_id) VALUES (:m, 'deduct', :p, :b, :d, :r)")
       ->execute([':m' => $mobile, ':p' => -$points, ':b' => $bal, ':d' => $desc, ':r' => $refId]);
    return $bal;
}

// ── Router ────────────────────────────────────────────────────────────────────
// Guard: only run the HTTP router when points.php is the entry-point script.
// When required from public.php as a helper library, bail out here so the
// router code doesn't execute (it would fall through to json_err + exit).
if (basename(realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) !== 'points.php') return;

$method = $_SERVER['REQUEST_METHOD'];
$act    = $_GET['action'] ?? '';

// PayU async callback comes as GET buy_return — handle before auth check
if ($act === 'buy_return' && $method === 'POST') {
    _handle_buy_return();
    exit;
}

secureSession();
$db = getDB();

if ($act === 'packages') {
    global $PACKAGES;
    json_ok(['packages' => array_values($PACKAGES), 'per_contact' => POINTS_PER_CONTACT]);
}

if ($act === 'balance') {
    $mobile = $_SESSION['mobile'] ?? '';
    if (!$mobile) json_err('Not logged in.', 401);
    $r = $db->prepare("SELECT balance, total_bought, total_used FROM user_points WHERE mobile = :m");
    $r->execute([':m' => $mobile]);
    $row = $r->fetch() ?: ['balance' => 0, 'total_bought' => 0, 'total_used' => 0];
    json_ok(['balance' => (int)$row['balance'], 'total_bought' => (int)$row['total_bought'], 'total_used' => (int)$row['total_used'], 'per_contact' => POINTS_PER_CONTACT]);
}

if ($act === 'history') {
    $mobile = $_SESSION['mobile'] ?? '';
    if (!$mobile) json_err('Not logged in.', 401);
    $r = $db->prepare("SELECT type, points, balance_after, description, ref_id, created_at FROM point_transactions WHERE mobile = :m ORDER BY id DESC LIMIT 50");
    $r->execute([':m' => $mobile]);
    json_ok(['history' => $r->fetchAll()]);
}

if ($act === 'buy_init' && $method === 'POST') {
    $mobile = $_SESSION['mobile'] ?? '';
    if (!$mobile) json_err('Not logged in.', 401);
    global $PACKAGES;
    $b     = body();
    $pkgId = $b['pkg_id'] ?? '';
    if (!isset($PACKAGES[$pkgId])) json_err('Invalid package.');

    $pkg    = $PACKAGES[$pkgId];
    $amount = number_format($pkg['price'], 2, '.', '');
    $txnId  = 'PTS' . time() . '_' . bin2hex(random_bytes(4));

    // Fetch profile info for PayU
    $prof = $db->prepare("SELECT cp_id, name, email FROM profiles WHERE mobile = :m LIMIT 1");
    $prof->execute([':m' => $mobile]);
    $profile = $prof->fetch();

    // Save pending order
    $db->prepare("INSERT INTO point_orders (mobile, txn_id, pkg_id, points, amount, status) VALUES (:m, :t, :pk, :pts, :a, 'pending')")
       ->execute([':m' => $mobile, ':t' => $txnId, ':pk' => $pkgId, ':pts' => $pkg['points'], ':a' => $pkg['price']]);

    // If PayU config available, build redirect params
    $payuAvail = is_file(__DIR__ . '/../payu-config.php');
    if ($payuAvail) {
        require_once __DIR__ . '/../payu-config.php';
        $base = payuBaseUrl();
        $firstname = preg_replace('/[^A-Za-z0-9 ]/', '', $profile['name'] ?? 'User') ?: 'User';
        $email     = $profile['email'] ?? ($mobile . '@chennaiprofile.in');
        $params = [
            'key'          => PAYU_KEY,
            'txnid'        => $txnId,
            'amount'       => $amount,
            'productinfo'  => $pkg['label'] . ' - Chennai Profile',
            'firstname'    => $firstname,
            'email'        => $email,
            'phone'        => $mobile,
            'surl'         => $base . '/api/points.php?action=buy_return',
            'furl'         => $base . '/api/points.php?action=buy_return',
            'udf1'         => $txnId,
            'udf2'         => $mobile,
            'udf3'         => $pkgId,
            'service_provider' => 'payu_paisa',
        ];
        $params['hash'] = payuRequestHash($params);
        json_ok(['payu' => true, 'endpoint' => PAYU_ENDPOINT, 'params' => $params]);
    }

    // Fallback: admin-manual flow
    json_ok(['payu' => false, 'txn_id' => $txnId, 'amount' => $pkg['price'], 'points' => $pkg['points'],
             'msg' => 'Pay ₹' . $pkg['price'] . ' via UPI/bank and share this reference: ' . $txnId]);
}

json_err('Unknown action.');

// ── PayU return handler ───────────────────────────────────────────────────────
function _handle_buy_return(): void {
    $payuAvail = is_file(__DIR__ . '/../payu-config.php');
    if (!$payuAvail) { header('Location: /backend/user-panel.php?pay=pts_fail'); exit; }
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../payu-config.php';

    $status  = $_POST['status']  ?? '';
    $txnId   = $_POST['udf1']    ?? '';
    $mobile  = $_POST['udf2']    ?? '';
    $pkgId   = $_POST['udf3']    ?? '';
    $payuTxn = $_POST['mihpayid'] ?? '';
    $hash    = $_POST['hash']    ?? '';

    if (!$txnId || !$mobile || !$pkgId) { header('Location: /backend/user-panel.php?pay=pts_fail'); exit; }

    // Verify hash
    $expected = payuResponseHash($_POST);
    if (!hash_equals($expected, $hash)) { header('Location: /backend/user-panel.php?pay=pts_fail'); exit; }

    $db = getDB();

    // Mark order
    $ord = $db->prepare("SELECT id, points, status FROM point_orders WHERE txn_id = :t AND mobile = :m LIMIT 1");
    $ord->execute([':t' => $txnId, ':m' => $mobile]);
    $order = $ord->fetch();
    if (!$order || $order['status'] !== 'pending') { header('Location: /backend/user-panel.php?pay=pts_done'); exit; }

    if ($status === 'success') {
        $db->prepare("UPDATE point_orders SET status='success', payu_txn_id=:p, updated_at=NOW() WHERE txn_id=:t")
           ->execute([':p' => $payuTxn, ':t' => $txnId]);
        pts_credit($db, $mobile, (int)$order['points'], 'purchase', 'Purchased ' . $order['points'] . ' pts', $txnId);
        header('Location: /backend/user-panel.php?pay=pts_ok&pts=' . $order['points']); exit;
    }

    $db->prepare("UPDATE point_orders SET status='failed', updated_at=NOW() WHERE txn_id=:t")
       ->execute([':t' => $txnId]);
    header('Location: /backend/user-panel.php?pay=pts_fail'); exit;
}
