<?php
// matrimony/api/bills.php

require_once __DIR__ . '/../config.php';

cors();
$mobile = authRequired();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_err('Method not allowed', 405);
}

$db   = getDB();
$stmt = $db->prepare(
    "SELECT
       b.id,
       b.cp_id,
       b.mobile,
       b.name,
       b.plan_name,
       b.plan_type,
       b.amount,
       b.payment,
       b.billed_by,
       b.billed_date,
       b.expiry
     FROM bills b
     WHERE b.mobile = :m
     ORDER BY b.billed_date DESC, b.id DESC"
);
$stmt->execute([':m' => $mobile]);
$bills = $stmt->fetchAll();

// Cast amount to float for JSON
foreach ($bills as &$bill) {
    $bill['amount'] = (float) $bill['amount'];
}
unset($bill);

json_ok(['bills' => $bills]);
