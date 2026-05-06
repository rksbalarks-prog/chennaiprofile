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

json_err('Unknown action.');
