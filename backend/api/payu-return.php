<?php
// matrimony/backend/api/payu-return.php
// PayU posts back here after the user completes (or cancels) payment.
// Both surl and furl point to this file; we branch on the `status` field.

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../payu-config.php';

$r = $_POST ?: [];
if (!$r) {
    header('Location: ../user-panel.php?pay=failure&reason=empty');
    exit;
}

$txnid    = trim($r['txnid'] ?? '');
$status   = strtolower(trim($r['status'] ?? ''));
$amount   = trim($r['amount'] ?? '');
$payuId   = trim($r['mihpayid'] ?? '');
$mode     = trim($r['mode'] ?? '');
$orderId  = (int) ($r['udf1'] ?? 0);
$mobile   = trim($r['udf2'] ?? '');
$planId   = trim($r['udf3'] ?? '');
$validity = (int) ($r['udf4'] ?? 0);
$cpId     = trim($r['udf5'] ?? '');

$db = getDB();

if (!payuVerifyResponseHash($r)) {
    if ($orderId) {
        $db->prepare("UPDATE user_orders SET status='rejected', admin_note=:n, processed_at=NOW(), processed_by='PayU' WHERE id=:id")
           ->execute([':n' => 'Hash verification failed', ':id' => $orderId]);
    }
    header('Location: ../user-panel.php?pay=failure&reason=hash');
    exit;
}

// Cross-check the order exists and amount matches what we initiated.
$stmt = $db->prepare("SELECT id, mobile, plan, amount FROM user_orders WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch();
if (!$order || $order['mobile'] !== $mobile || (float) $order['amount'] !== (float) $amount) {
    if ($orderId) {
        $db->prepare("UPDATE user_orders SET status='rejected', admin_note='Order/amount mismatch', processed_at=NOW(), processed_by='PayU' WHERE id=:id")
           ->execute([':id' => $orderId]);
    }
    header('Location: ../user-panel.php?pay=failure&reason=mismatch');
    exit;
}

if ($status === 'success') {
    $db->beginTransaction();
    try {
        $note = "PayU OK · mihpayid=$payuId · mode=$mode · txnid=$txnid";
        $db->prepare("UPDATE user_orders SET status='approved', method='payumoney', txn_ref=:t, admin_note=:n, processed_by='PayU', processed_at=NOW() WHERE id=:id")
           ->execute([':t' => $txnid, ':n' => $note, ':id' => $orderId]);

        $expiry = $validity > 0
            ? date('Y-m-d', strtotime("+{$validity} days"))
            : null;

        $db->prepare("UPDATE profiles
                      SET plan = :pl,
                          expiry = :exp,
                          payment_status = 'active',
                          pending_plan = NULL,
                          pending_amount = NULL,
                          pending_pay_opt_id = NULL
                      WHERE mobile = :m")
           ->execute([':pl' => $order['plan'], ':exp' => $expiry, ':m' => $mobile]);

        $db->prepare("INSERT INTO bills (cp_id, mobile, name, plan_name, plan_type, amount, payment, billed_by, billed_date, expiry)
                      SELECT :cp, mobile, name, :pn, :pt, :amt, 'PayU', 'PayU (auto)', CURDATE(), :exp
                      FROM profiles WHERE mobile = :m LIMIT 1")
           ->execute([
               ':cp'  => $cpId,
               ':pn'  => $order['plan'],
               ':pt'  => strtolower($order['plan']),
               ':amt' => $amount,
               ':exp' => $expiry,
               ':m'   => $mobile,
           ]);

        $db->prepare("INSERT INTO order_archive (order_id, mobile, cp_id, name, plan, amount, method, txn_ref, notes, action, action_by, admin_note, created_at)
                      SELECT id, mobile, cp_id, name, plan, amount, 'payumoney', :t, notes, 'Approved', 'PayU', :n, NOW()
                      FROM user_orders WHERE id = :id")
           ->execute([':t' => $txnid, ':n' => $note, ':id' => $orderId]);

        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        header('Location: ../user-panel.php?pay=failure&reason=server');
        exit;
    }

    header('Location: ../user-panel.php?pay=success&order_id=' . $orderId);
    exit;
}

// Anything other than success → failure / cancelled / pending.
$note = "PayU $status · mihpayid=$payuId · txnid=$txnid";
$db->prepare("UPDATE user_orders SET status='rejected', admin_note=:n, processed_by='PayU', processed_at=NOW() WHERE id=:id")
   ->execute([':n' => $note, ':id' => $orderId]);

$db->prepare("INSERT INTO order_archive (order_id, mobile, cp_id, name, plan, amount, method, txn_ref, notes, action, action_by, admin_note, created_at)
              SELECT id, mobile, cp_id, name, plan, amount, 'payumoney', :t, notes, 'Rejected', 'PayU', :n, NOW()
              FROM user_orders WHERE id = :id")
   ->execute([':t' => $txnid, ':n' => $note, ':id' => $orderId]);

header('Location: ../user-panel.php?pay=failure&reason=' . urlencode($status));
exit;
