<?php
// Admin bills: list, create, update, history
require_once __DIR__ . '/../../admin-config.php';
cors();
$admin = adminRequired();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = str_clean($_GET['type'] ?? 'active', 20);
    if ($type === 'history') {
        $rows = $db->query("SELECT * FROM bill_history ORDER BY recorded_at DESC LIMIT 500")->fetchAll();
        foreach ($rows as &$r) $r['amount'] = (float)$r['amount'];
        json_ok(['billHistory' => $rows]);
    }
    $rows = $db->query("SELECT * FROM bills ORDER BY billed_date DESC, id DESC")->fetchAll();
    foreach ($rows as &$r) $r['amount'] = (float)$r['amount'];
    json_ok(['bills' => $rows]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $b = body();
    if (isset($b['cpId']) && !isset($b['cp_id'])) $b['cp_id'] = $b['cpId'];
    if (isset($b['planName']) && !isset($b['plan_name'])) $b['plan_name'] = $b['planName'];
    if (isset($b['planType']) && !isset($b['plan_type'])) $b['plan_type'] = $b['planType'];
    if (isset($b['billedDate']) && !isset($b['billed_date'])) $b['billed_date'] = $b['billedDate'];
    if (isset($b['billedBy']) && !isset($b['billed_by'])) $b['billed_by'] = $b['billedBy'];
    if (isset($b['billId']) && !isset($b['bill_id'])) $b['bill_id'] = $b['billId'];
    $action = str_clean($b['action'] ?? '', 20);

    if ($action === 'create' || $action === 'update') {
        $cpId      = str_clean($b['cp_id'] ?? '', 20);
        $planName  = str_clean($b['plan_name'] ?? '', 100);
        $planType  = str_clean($b['plan_type'] ?? '', 50);
        $amount    = isset($b['amount']) ? (float)$b['amount'] : 0;
        $payment   = str_clean($b['payment'] ?? '', 100);
        $billedDate= str_clean($b['billed_date'] ?? '', 10) ?: date('Y-m-d');
        $expiry    = str_clean($b['expiry'] ?? '', 10) ?: null;
        $mobile    = str_clean($b['mobile'] ?? '', 15);
        $name      = str_clean($b['name'] ?? '', 150);

        if (!$cpId || !$planName) json_err('cp_id and plan_name required.');

        // Archive to bill_history
        if ($action === 'update') {
            $billId = isset($b['bill_id']) ? (int)$b['bill_id'] : 0;
            if ($billId > 0) {
                $old = $db->prepare("SELECT * FROM bills WHERE id = :id LIMIT 1");
                $old->execute([':id' => $billId]);
                $oldRow = $old->fetch();
                if ($oldRow) {
                    $db->prepare("INSERT INTO bill_history (cp_id,name,mobile,plan_name,plan_type,amount,payment,billed_by,billed_date,expiry,action,recorded_at)
                        VALUES (:c,:n,:m,:pn,:pt,:a,:py,:bb,:bd,:ex,'Updated',NOW())")
                       ->execute([':c'=>$oldRow['cp_id'],':n'=>$oldRow['name'],':m'=>$oldRow['mobile'],':pn'=>$oldRow['plan_name'],
                                  ':pt'=>$oldRow['plan_type'],':a'=>$oldRow['amount'],':py'=>$oldRow['payment'],':bb'=>$oldRow['billed_by'],
                                  ':bd'=>$oldRow['billed_date'],':ex'=>$oldRow['expiry']]);
                }
                $db->prepare("UPDATE bills SET plan_name=:pn,plan_type=:pt,amount=:a,payment=:py,billed_by=:bb,billed_date=:bd,expiry=:ex WHERE id=:id")
                   ->execute([':pn'=>$planName,':pt'=>$planType,':a'=>$amount,':py'=>$payment,':bb'=>$admin['name'],':bd'=>$billedDate,':ex'=>$expiry,':id'=>$billId]);
            }
        } else {
            // Remove existing bill for same CP
            $existing = $db->prepare("SELECT * FROM bills WHERE cp_id = :c LIMIT 1");
            $existing->execute([':c' => $cpId]);
            $ex = $existing->fetch();
            if ($ex) {
                $db->prepare("INSERT INTO bill_history (cp_id,name,mobile,plan_name,plan_type,amount,payment,billed_by,billed_date,expiry,action,recorded_at)
                    VALUES (:c,:n,:m,:pn,:pt,:a,:py,:bb,:bd,:ex,'Updated',NOW())")
                   ->execute([':c'=>$ex['cp_id'],':n'=>$ex['name'],':m'=>$ex['mobile'],':pn'=>$ex['plan_name'],
                              ':pt'=>$ex['plan_type'],':a'=>$ex['amount'],':py'=>$ex['payment'],':bb'=>$ex['billed_by'],
                              ':bd'=>$ex['billed_date'],':ex'=>$ex['expiry']]);
                $db->prepare("DELETE FROM bills WHERE cp_id = :c")->execute([':c' => $cpId]);
            }
            // Create new bill
            $db->prepare("INSERT INTO bills (cp_id,mobile,name,plan_name,plan_type,amount,payment,billed_by,billed_date,expiry)
                VALUES (:c,:m,:n,:pn,:pt,:a,:py,:bb,:bd,:ex)")
               ->execute([':c'=>$cpId,':m'=>$mobile,':n'=>$name,':pn'=>$planName,':pt'=>$planType,':a'=>$amount,
                          ':py'=>$payment,':bb'=>$admin['name'],':bd'=>$billedDate,':ex'=>$expiry]);
            // Archive creation event
            $db->prepare("INSERT INTO bill_history (cp_id,name,mobile,plan_name,plan_type,amount,payment,billed_by,billed_date,expiry,action,recorded_at)
                VALUES (:c,:n,:m,:pn,:pt,:a,:py,:bb,:bd,:ex,'Created',NOW())")
               ->execute([':c'=>$cpId,':n'=>$name,':m'=>$mobile,':pn'=>$planName,':pt'=>$planType,':a'=>$amount,
                          ':py'=>$payment,':bb'=>$admin['name'],':bd'=>$billedDate,':ex'=>$expiry]);
            // Update profile plan + expiry
            $db->prepare("UPDATE profiles SET plan=:pt, expiry=:ex WHERE cp_id=:c")
               ->execute([':pt'=>strtolower($planType)?:'free',':ex'=>$expiry,':c'=>$cpId]);
        }
        pushAdminLog($action === 'update' ? 'Updated Bill' : 'Created Bill', $name.' - '.$planName.' - Rs'.$amount, 'bill', $admin);
        json_ok(['msg' => 'Bill saved.']);
    }

    json_err('Unknown action.');
}
json_err('Method not allowed', 405);
