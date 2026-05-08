<?php
// Admin points management: stats, list users, credit/debit
require_once __DIR__ . '/../../admin-config.php';
require_once __DIR__ . '/../../api/points.php'; // helpers: pts_credit, pts_deduct

cors();
adminSession();
$admin = adminRequired();
$db    = getDB();

$method = $_SERVER['REQUEST_METHOD'];
$act    = $_GET['action'] ?? (body()['action'] ?? '');

if ($act === 'stats') {
    $total_users   = $db->query("SELECT COUNT(*) FROM user_points WHERE total_bought > 0")->fetchColumn();
    $total_bought  = $db->query("SELECT SUM(total_bought) FROM user_points")->fetchColumn() ?? 0;
    $total_used    = $db->query("SELECT SUM(total_used) FROM user_points")->fetchColumn() ?? 0;
    $total_balance = $db->query("SELECT SUM(balance) FROM user_points")->fetchColumn() ?? 0;
    $recent = $db->query("SELECT t.mobile, p.name, t.type, t.points, t.balance_after, t.description, t.ref_id, t.created_at
        FROM point_transactions t LEFT JOIN profiles p ON p.mobile = t.mobile
        ORDER BY t.id DESC LIMIT 20")->fetchAll();
    json_ok(compact('total_users','total_bought','total_used','total_balance','recent'));
}

if ($act === 'users') {
    $search = str_clean($_GET['q'] ?? '', 50);
    $where  = $search ? "WHERE u.mobile LIKE :q OR p.name LIKE :q" : "";
    $params = $search ? [':q' => '%' . $search . '%'] : [];
    $stmt   = $db->prepare("SELECT u.mobile, p.name, u.balance, u.total_bought, u.total_used, u.updated_at
        FROM user_points u LEFT JOIN profiles p ON p.mobile = u.mobile
        $where ORDER BY u.total_bought DESC LIMIT 100");
    $stmt->execute($params);
    json_ok(['users' => $stmt->fetchAll()]);
}

if ($act === 'user_history') {
    $mobile = str_clean($_GET['mobile'] ?? '', 15);
    if (!$mobile) json_err('Mobile required.');
    $r = $db->prepare("SELECT type, points, balance_after, description, ref_id, created_at FROM point_transactions WHERE mobile = :m ORDER BY id DESC LIMIT 100");
    $r->execute([':m' => $mobile]);
    $bal = pts_get_balance($db, $mobile);
    json_ok(['history' => $r->fetchAll(), 'balance' => $bal]);
}

if ($act === 'credit' && $method === 'POST') {
    $b      = body();
    $mobile = str_clean($b['mobile'] ?? '', 15);
    $points = (int)($b['points'] ?? 0);
    $note   = str_clean($b['note'] ?? 'Admin credit', 200);
    if (!$mobile || $points <= 0) json_err('Mobile and positive points required.');

    $newBal = pts_credit($db, $mobile, $points, 'admin_credit', $note, 'admin:' . $admin['id']);
    pushAdminLog('Points Credited', "+{$points} pts to {$mobile} — {$note}", 'payment', $admin);
    json_ok(['balance' => $newBal, 'msg' => "{$points} pts credited to {$mobile}."]);
}

if ($act === 'debit' && $method === 'POST') {
    $b      = body();
    $mobile = str_clean($b['mobile'] ?? '', 15);
    $points = (int)($b['points'] ?? 0);
    $note   = str_clean($b['note'] ?? 'Admin debit', 200);
    if (!$mobile || $points <= 0) json_err('Mobile and positive points required.');

    $result = pts_deduct($db, $mobile, $points, $note, 'admin:' . $admin['id']);
    if ($result === false) json_err('Insufficient balance to debit.');
    pushAdminLog('Points Debited', "-{$points} pts from {$mobile} — {$note}", 'payment', $admin);
    json_ok(['balance' => $result, 'msg' => "{$points} pts debited from {$mobile}."]);
}

if ($act === 'confirm_order' && $method === 'POST') {
    $b     = body();
    $txnId = str_clean($b['txn_id'] ?? '', 100);
    if (!$txnId) json_err('txn_id required.');
    $ord = $db->prepare("SELECT * FROM point_orders WHERE txn_id = :t AND status = 'pending' LIMIT 1");
    $ord->execute([':t' => $txnId]);
    $order = $ord->fetch();
    if (!$order) json_err('Order not found or already processed.');

    $db->prepare("UPDATE point_orders SET status='success', updated_at=NOW() WHERE txn_id=:t")
       ->execute([':t' => $txnId]);
    $newBal = pts_credit($db, $order['mobile'], (int)$order['points'], 'purchase', 'Purchased ' . $order['points'] . ' pts (admin confirmed)', $txnId);
    pushAdminLog('Points Order Confirmed', "{$order['points']} pts to {$order['mobile']} — txn:{$txnId}", 'payment', $admin);
    json_ok(['balance' => $newBal, 'msg' => 'Order confirmed. ' . $order['points'] . ' pts credited.']);
}

// ── Point Packages CRUD ───────────────────────────────────────────────────────

function ensurePackagesTable(PDO $db): void {
    $db->exec("CREATE TABLE IF NOT EXISTS point_packages (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        pkg_id     VARCHAR(20) NOT NULL UNIQUE,
        points     INT NOT NULL,
        price      DECIMAL(10,2) NOT NULL,
        label      VARCHAR(100) NOT NULL,
        badge      VARCHAR(50) NOT NULL DEFAULT '',
        sort_order INT NOT NULL DEFAULT 0,
        active     TINYINT NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $db->exec("INSERT IGNORE INTO point_packages (pkg_id, points, price, label, badge, sort_order, active) VALUES
        ('p100',  100,  100.00, '100 Points',  '',           1, 1),
        ('p500',  500,  500.00, '500 Points',  'Popular',    2, 1),
        ('p1000', 1000, 1000.00,'1000 Points', 'Best Value', 3, 1)");
}

if ($act === 'packages') {
    ensurePackagesTable($db);
    $rows = $db->query("SELECT * FROM point_packages ORDER BY sort_order ASC, id ASC")->fetchAll();
    json_ok(['packages' => $rows]);
}

if ($act === 'save_package' && $method === 'POST') {
    ensurePackagesTable($db);
    $b      = body();
    $pkgId  = preg_replace('/[^a-z0-9_]/i', '', $b['pkg_id'] ?? '');
    $points = (int)($b['points'] ?? 0);
    $price  = round((float)($b['price'] ?? 0), 2);
    $label  = str_clean($b['label'] ?? '', 100);
    $badge  = str_clean($b['badge'] ?? '', 50);
    $sort   = (int)($b['sort_order'] ?? 0);
    $active = (int)!empty($b['active']);
    if (!$pkgId || $points <= 0 || $price <= 0 || !$label) json_err('pkg_id, points, price and label are required.');

    $db->prepare("INSERT INTO point_packages (pkg_id, points, price, label, badge, sort_order, active)
        VALUES (:id, :pts, :pr, :lbl, :bdg, :srt, :act)
        ON DUPLICATE KEY UPDATE points=:pts2, price=:pr2, label=:lbl2, badge=:bdg2, sort_order=:srt2, active=:act2")
       ->execute([':id'=>$pkgId, ':pts'=>$points, ':pr'=>$price, ':lbl'=>$label, ':bdg'=>$badge, ':srt'=>$sort, ':act'=>$active,
                  ':pts2'=>$points, ':pr2'=>$price, ':lbl2'=>$label, ':bdg2'=>$badge, ':srt2'=>$sort, ':act2'=>$active]);
    pushAdminLog('Point Package Saved', "pkg:{$pkgId} {$points}pts ₹{$price}", 'setting', $admin);
    json_ok(['msg' => "Package '{$pkgId}' saved."]);
}

if ($act === 'delete_package' && $method === 'POST') {
    ensurePackagesTable($db);
    $pkgId = preg_replace('/[^a-z0-9_]/i', '', body()['pkg_id'] ?? '');
    if (!$pkgId) json_err('pkg_id required.');
    $db->prepare("DELETE FROM point_packages WHERE pkg_id = :id")->execute([':id' => $pkgId]);
    pushAdminLog('Point Package Deleted', "pkg:{$pkgId}", 'setting', $admin);
    json_ok(['msg' => "Package deleted."]);
}

json_err('Unknown action.');
