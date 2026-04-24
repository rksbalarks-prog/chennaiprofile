<?php
// Admin settings: plans, admins, restrictions, payment opts, panel ctrl, mobile reqs, stories, notifications
require_once __DIR__ . '/../../admin-config.php';
cors();
$admin = adminRequired();
$db = getDB();

$b = ($_SERVER['REQUEST_METHOD'] === 'POST') ? body() : [];
$section = str_clean(($_SERVER['REQUEST_METHOD'] === 'GET' ? ($_GET['section'] ?? '') : ($b['section'] ?? '')), 30);

// ════════════════════ GET ════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($section) {
        case 'all': {
            // Return everything in one request for loadAll()
            $plans = $db->query("SELECT * FROM subscription_plans ORDER BY amount ASC")->fetchAll();
            foreach ($plans as &$p) { $p['amount']=(float)$p['amount']; $p['validity']=(int)$p['validity']; } unset($p);
            $adminsRows = $db->query("SELECT id, name, username, mobile, role, status, plain_password, created_at FROM admins ORDER BY id ASC")->fetchAll();
            $globalR = $db->prepare("SELECT * FROM restrictions WHERE type='global' LIMIT 1"); $globalR->execute();
            $indR = $db->query("SELECT * FROM restrictions WHERE type='individual' ORDER BY id DESC")->fetchAll();
            $payOpts = $db->query("SELECT * FROM payment_options ORDER BY id ASC")->fetchAll();
            foreach ($payOpts as &$o) { if (!empty($o['details'])&&is_string($o['details'])) $o['details']=json_decode($o['details'],true); } unset($o);
            $gCtrl = $db->prepare("SELECT settings FROM user_panel_ctrl WHERE type='global' ORDER BY id ASC LIMIT 1"); $gCtrl->execute(); $gCtrlRow=$gCtrl->fetch();
            $overrides = $db->query("SELECT * FROM user_panel_ctrl WHERE type='override' ORDER BY id DESC")->fetchAll();
            foreach ($overrides as &$ov) { if(!empty($ov['settings'])&&is_string($ov['settings'])) $ov['settings']=json_decode($ov['settings'],true); } unset($ov);
            $mReqs = $db->query("SELECT * FROM mobile_requests ORDER BY requested_at DESC")->fetchAll();
            foreach ($mReqs as &$mr) { if(!empty($mr['profile_snapshot'])&&is_string($mr['profile_snapshot'])) $mr['profile_snapshot']=json_decode($mr['profile_snapshot'],true); } unset($mr);
            $stories = $db->query("SELECT * FROM success_stories ORDER BY id DESC")->fetchAll();
            $notifs = $db->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 100")->fetchAll();
            $otpRows = $db->query(
                "SELECT l.*, s.otp AS live_otp, s.expires_at AS live_otp_expires, s.verified AS live_otp_verified
                   FROM otp_logs l
                   LEFT JOIN otp_sessions s ON s.mobile = l.mobile
                  ORDER BY l.otp_requested_at DESC"
            )->fetchAll();
            foreach ($otpRows as &$or) {
                $or['login_count']=(int)$or['login_count'];
                $or['banned']=(bool)(int)$or['banned'];
                $expired = !empty($or['live_otp_expires']) && strtotime($or['live_otp_expires']) < time();
                if (!empty($or['live_otp_verified']) || $expired) $or['live_otp'] = null;
            } unset($or);
            $delRows = $db->query("SELECT * FROM deleted_profiles ORDER BY deleted_at DESC")->fetchAll();
            $expRows = $db->query("SELECT * FROM expired_profiles ORDER BY expired_on DESC")->fetchAll();
            $usageRows = $db->query("SELECT * FROM usage_activity ORDER BY datetime DESC LIMIT 2000")->fetchAll();
            $logRows = $db->query("SELECT * FROM admin_log ORDER BY timestamp DESC LIMIT 500")->fetchAll();
            $rpRows = $db->query("SELECT * FROM role_permissions")->fetchAll();
            $rPerms = []; foreach ($rpRows as $rr) $rPerms[$rr['role']] = json_decode($rr['permissions'],true)?:[];
            $thRow = $db->query("SELECT * FROM alert_thresholds LIMIT 1")->fetch();
            $phRows = $db->query("SELECT * FROM plan_history ORDER BY recorded_at DESC LIMIT 200")->fetchAll();
            $upHistRows = $db->query("SELECT * FROM up_ctrl_history ORDER BY recorded_at DESC LIMIT 200")->fetchAll();
            $bhRows = $db->query("SELECT * FROM bill_history ORDER BY recorded_at DESC LIMIT 500")->fetchAll();
            foreach ($bhRows as &$bh) { $bh['amount']=(float)$bh['amount']; } unset($bh);
            $castes = $db->query("SELECT DISTINCT caste AS id, caste FROM profiles WHERE caste IS NOT NULL AND caste != '' ORDER BY caste ASC")->fetchAll();
            $subcastes = $db->query("SELECT DISTINCT sub_caste AS subcaste, caste AS caste_name FROM profiles WHERE sub_caste IS NOT NULL AND sub_caste != '' ORDER BY sub_caste ASC")->fetchAll();
            json_ok([
                'plans' => $plans, 'admins' => $adminsRows,
                'restrictions' => ['global' => $globalR->fetch() ?: null, 'individual' => $indR],
                'paymentOpts' => $payOpts,
                'panelCtrl' => ['global' => $gCtrlRow ? json_decode($gCtrlRow['settings'],true) : null, 'overrides' => $overrides],
                'mobileReqs' => $mReqs, 'stories' => $stories, 'notifications' => $notifs,
                'otpLogs' => $otpRows, 'deleted' => $delRows, 'expired' => $expRows,
                'usage' => $usageRows, 'adminLog' => $logRows, 'rolePerms' => $rPerms,
                'alertThresholds' => $thRow ?: ['contact_day'=>10,'otp_day'=>3,'profile_day'=>10],
                'planHistory' => $phRows, 'upCtrlHistory' => $upHistRows, 'billHistory' => $bhRows,
                'castes' => $castes, 'subcastes' => $subcastes,
                'profileReports' => $db->query("SELECT r.*, p.name as profile_name, p.mobile as profile_mobile FROM profile_reports r LEFT JOIN profiles p ON r.cp_id = p.cp_id ORDER BY r.reported_at DESC LIMIT 500")->fetchAll(),
            ]);
        }
        case 'plans': {
            $plans = $db->query("SELECT * FROM subscription_plans ORDER BY amount ASC")->fetchAll();
            foreach ($plans as &$p) { $p['amount']=(float)$p['amount']; $p['validity']=(int)$p['validity']; }
            $history = $db->query("SELECT * FROM plan_history ORDER BY recorded_at DESC LIMIT 200")->fetchAll();
            json_ok(['plans' => $plans, 'planHistory' => $history]);
        }
        case 'admins': {
            $admins = $db->query("SELECT id, name, username, mobile, role, status, plain_password, created_at FROM admins ORDER BY id ASC")->fetchAll();
            json_ok(['admins' => $admins]);
        }
        case 'restrictions': {
            $global = $db->prepare("SELECT * FROM restrictions WHERE type='global' LIMIT 1");
            $global->execute(); $g = $global->fetch();
            $ind = $db->query("SELECT * FROM restrictions WHERE type='individual' ORDER BY id DESC")->fetchAll();
            json_ok(['global' => $g ?: null, 'individual' => $ind]);
        }
        case 'paymentOpts': {
            $opts = $db->query("SELECT * FROM payment_options ORDER BY id ASC")->fetchAll();
            foreach ($opts as &$o) {
                if (!empty($o['details']) && is_string($o['details'])) $o['details'] = json_decode($o['details'], true);
            }
            json_ok(['paymentOptions' => $opts]);
        }
        case 'panelCtrl': {
            $global = $db->prepare("SELECT settings FROM user_panel_ctrl WHERE type='global' ORDER BY id ASC LIMIT 1");
            $global->execute(); $gRow = $global->fetch();
            $overrides = $db->query("SELECT * FROM user_panel_ctrl WHERE type='override' ORDER BY id DESC")->fetchAll();
            foreach ($overrides as &$ov) {
                if (!empty($ov['settings']) && is_string($ov['settings'])) $ov['settings'] = json_decode($ov['settings'], true);
            }
            $history = $db->query("SELECT * FROM up_ctrl_history ORDER BY recorded_at DESC LIMIT 200")->fetchAll();
            json_ok([
                'global' => $gRow ? json_decode($gRow['settings'], true) : null,
                'overrides' => $overrides,
                'history' => $history
            ]);
        }
        case 'mobileReqs': {
            $reqs = $db->query("SELECT * FROM mobile_requests ORDER BY requested_at DESC")->fetchAll();
            foreach ($reqs as &$r) {
                if (!empty($r['profile_snapshot']) && is_string($r['profile_snapshot'])) $r['profile_snapshot'] = json_decode($r['profile_snapshot'], true);
            }
            json_ok(['mobileReqs' => $reqs]);
        }
        case 'stories': {
            json_ok(['stories' => $db->query("SELECT * FROM success_stories ORDER BY id DESC")->fetchAll()]);
        }
        case 'notifications': {
            json_ok(['notifications' => $db->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 100")->fetchAll()]);
        }
        case 'otpLogs': {
            $rows = $db->query(
                "SELECT l.*, s.otp AS live_otp, s.expires_at AS live_otp_expires, s.verified AS live_otp_verified
                   FROM otp_logs l
                   LEFT JOIN otp_sessions s ON s.mobile = l.mobile
                  ORDER BY l.otp_requested_at DESC"
            )->fetchAll();
            foreach ($rows as &$or) {
                $expired = !empty($or['live_otp_expires']) && strtotime($or['live_otp_expires']) < time();
                if (!empty($or['live_otp_verified']) || $expired) $or['live_otp'] = null;
            } unset($or);
            foreach ($rows as &$r) { $r['login_count']=(int)$r['login_count']; $r['banned']=(bool)(int)$r['banned']; }
            json_ok(['otpLogs' => $rows]);
        }
        case 'deleted': {
            json_ok(['deleted' => $db->query("SELECT * FROM deleted_profiles ORDER BY deleted_at DESC")->fetchAll()]);
        }
        case 'expired': {
            json_ok(['expired' => $db->query("SELECT * FROM expired_profiles ORDER BY expired_on DESC")->fetchAll()]);
        }
        case 'usage': {
            $rows = $db->query("SELECT * FROM usage_activity ORDER BY datetime DESC LIMIT 2000")->fetchAll();
            json_ok(['usage' => $rows]);
        }
        case 'adminLog': {
            $rows = $db->query("SELECT * FROM admin_log ORDER BY timestamp DESC LIMIT 500")->fetchAll();
            json_ok(['adminLog' => $rows]);
        }
        case 'rolePerms': {
            $rows = $db->query("SELECT * FROM role_permissions")->fetchAll();
            $perms = [];
            foreach ($rows as $r) $perms[$r['role']] = json_decode($r['permissions'], true) ?: [];
            json_ok(['rolePerms' => $perms]);
        }
        case 'messages': {
            $rows = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 500")->fetchAll();
            json_ok(['messages' => $rows]);
        }
        case 'accounts': {
            $entries = $db->query("SELECT * FROM accounts ORDER BY date DESC, id DESC LIMIT 1000")->fetchAll();
            json_ok(['entries' => $entries]);
        }
        case 'userOrders': {
            $rows = $db->query("SELECT * FROM user_orders ORDER BY created_at DESC LIMIT 500")->fetchAll();
            $archive = $db->query("SELECT * FROM order_archive ORDER BY created_at DESC LIMIT 500")->fetchAll();
            json_ok(['orders' => $rows, 'archive' => $archive]);
        }
        case 'contactViewLog': {
            $logs = $db->query("SELECT ua.mobile, ua.cp_id, ua.name, ua.plan, ua.target_cp_id, ua.datetime,
                p.name as target_name, p.mobile as target_mobile
                FROM usage_activity ua
                LEFT JOIN profiles p ON p.cp_id = ua.target_cp_id
                WHERE ua.activity_type = 'contact_view'
                ORDER BY ua.datetime DESC LIMIT 1000")->fetchAll();
            json_ok(['logs' => $logs]);
        }
        case 'profileViewLog': {
            $logs = $db->query("SELECT ua.mobile, ua.cp_id, ua.name, ua.plan, ua.target_cp_id, ua.datetime,
                p.name as target_name
                FROM usage_activity ua
                LEFT JOIN profiles p ON p.cp_id = ua.target_cp_id
                WHERE ua.activity_type = 'profile_view'
                ORDER BY ua.datetime DESC LIMIT 1000")->fetchAll();
            json_ok(['logs' => $logs]);
        }
        case 'directLogin': {
            $rows = $db->query("SELECT * FROM direct_login ORDER BY created_at DESC")->fetchAll();
            $logs = $db->query("SELECT * FROM direct_login_log ORDER BY created_at DESC LIMIT 500")->fetchAll();
            json_ok(['directLogins' => $rows, 'logs' => $logs]);
        }
        case 'alertThresholds': {
            $row = $db->query("SELECT * FROM alert_thresholds LIMIT 1")->fetch();
            json_ok(['thresholds' => $row ?: ['contact_day'=>10,'otp_day'=>3,'profile_day'=>10]]);
        }
        case 'updateHistory': {
            $limit = min((int)($_GET['limit'] ?? 200), 500);
            $entityType = str_clean($_GET['entity_type'] ?? '', 50);
            $where = '1=1';
            $params = [];
            if ($entityType) { $where .= ' AND entity_type = :et'; $params[':et'] = $entityType; }
            $stmt = $db->prepare("SELECT * FROM update_history WHERE $where ORDER BY created_at DESC LIMIT $limit");
            $stmt->execute($params);
            json_ok(['history' => $stmt->fetchAll()]);
        }
        default: json_err('Unknown section.');
    }
}

// ════════════════════ POST ════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = str_clean($b['action'] ?? '', 30);

    switch ($section) {

        // ── Subscription Plans ──
        case 'plans': {
            if ($action === 'save') {
                $name     = str_clean($b['name'] ?? '', 100);
                $type     = str_clean($b['type'] ?? '', 20);
                $amount   = (float)($b['amount'] ?? 0);
                $validity = (int)($b['validity'] ?? 365);
                $desc     = str_clean($b['description'] ?? '', 1000);
                $status   = str_clean($b['status'] ?? 'active', 20);
                $planId   = str_clean($b['plan_id'] ?? '', 20);
                if (!$name || !$type) json_err('Name and type required.');
                $userVisible = isset($b['user_visible']) ? (int)$b['user_visible'] : 1;
                if ($planId) {
                    $db->prepare("UPDATE subscription_plans SET name=:n,type=:t,amount=:a,validity=:v,description=:d,status=:s,user_visible=:uv WHERE plan_id=:p")
                       ->execute([':n'=>$name,':t'=>$type,':a'=>$amount,':v'=>$validity,':d'=>$desc,':s'=>$status,':uv'=>$userVisible,':p'=>$planId]);
                    $histAction = 'Updated';
                } else {
                    $planId = 'PLAN'.date('YmdHis').str_pad((string)random_int(0,99),2,'0',STR_PAD_LEFT);
                    $db->prepare("INSERT INTO subscription_plans (plan_id,name,type,description,amount,validity,status,created_by) VALUES (:p,:n,:t,:d,:a,:v,:s,:cb)")
                       ->execute([':p'=>$planId,':n'=>$name,':t'=>$type,':d'=>$desc,':a'=>$amount,':v'=>$validity,':s'=>$status,':cb'=>$admin['name']]);
                    $histAction = 'Created';
                }
                $db->prepare("INSERT INTO plan_history (plan_name,type,amount,validity,description,status,action,recorded_by) VALUES (:n,:t,:a,:v,:d,:s,:act,:rb)")
                   ->execute([':n'=>$name,':t'=>$type,':a'=>$amount,':v'=>$validity,':d'=>$desc,':s'=>$status,':act'=>$histAction,':rb'=>$admin['name']]);
                recordHistory('plan', $planId, strtolower($histAction), null, null, $name.' / Rs'.$amount.' / '.$validity.'d', $admin);
                pushAdminLog($histAction.' Plan', $name.' - Rs'.$amount, 'plan', $admin);
                json_ok(['msg' => 'Plan saved.', 'plan_id' => $planId]);
            }
            if ($action === 'delete') {
                $planId = str_clean($b['plan_id'] ?? '', 20);
                $db->prepare("DELETE FROM subscription_plans WHERE plan_id=:p")->execute([':p'=>$planId]);
                json_ok(['msg' => 'Plan deleted.']);
            }
            json_err('Unknown plan action.');
        }

        // ── Admin Accounts ──
        case 'admins': {
            if ($action === 'create') {
                $name = str_clean($b['name'] ?? '', 150);
                $username = str_clean($b['username'] ?? '', 100);
                $mobile = str_clean($b['mobile'] ?? '', 15);
                $role = str_clean($b['role'] ?? 'staff', 20);
                $pwd = $b['password'] ?? '';
                if (!$name||!$username||!$pwd) json_err('Name, username and password required.');
                $dup = $db->prepare("SELECT id FROM admins WHERE username=:u LIMIT 1");
                $dup->execute([':u'=>$username]);
                if ($dup->fetch()) json_err('Username already exists.');
                $hash = password_hash($pwd, PASSWORD_DEFAULT);
                $db->prepare("INSERT INTO admins (username,password,plain_password,name,mobile,role,status,created_at) VALUES (:u,:p,:pp,:n,:m,:r,'active',NOW())")
                   ->execute([':u'=>$username,':p'=>$hash,':pp'=>$pwd,':n'=>$name,':m'=>$mobile,':r'=>$role]);
                recordHistory('admin', $username, 'created', null, null, $name.' / '.$role, $admin);
                pushAdminLog('Added Admin', $name.' (@'.$username.')', 'admin', $admin);
                json_ok(['msg' => 'Admin created.']);
            }
            if ($action === 'update') {
                $id = (int)($b['id'] ?? 0);
                if (!$id) json_err('id required.');
                $sets = []; $params = [':id'=>$id];
                foreach (['name','username','mobile','role','status'] as $f) {
                    if (isset($b[$f])) { $sets[]="`{$f}`=:{$f}"; $params[':'.$f]=str_clean($b[$f],150); }
                }
                if (!empty($b['password'])) {
                    $sets[] = "`password`=:pwd";
                    $params[':pwd'] = password_hash($b['password'], PASSWORD_DEFAULT);
                    $sets[] = "`plain_password`=:ppwd";
                    $params[':ppwd'] = $b['password'];
                }
                if (empty($sets)) json_err('Nothing to update.');
                $db->prepare("UPDATE admins SET ".implode(',',$sets)." WHERE id=:id")->execute($params);
                recordHistory('admin', 'ID:'.$id, 'updated', null, null, implode(', ', array_keys(array_filter($b, fn($v,$k) => in_array($k,['name','username','mobile','role','status','password']), ARRAY_FILTER_USE_BOTH))), $admin);
                pushAdminLog('Edited Admin', ($b['name']??'').' ID '.$id, 'admin', $admin);
                json_ok(['msg' => 'Admin updated.']);
            }
            if ($action === 'delete') {
                $id = (int)($b['id'] ?? 0);
                $target = $db->prepare("SELECT role,name FROM admins WHERE id=:id LIMIT 1");
                $target->execute([':id'=>$id]);
                $t = $target->fetch();
                if ($t && $t['role'] === 'super') json_err('Cannot delete Super Admin.');
                $db->prepare("DELETE FROM admins WHERE id=:id")->execute([':id'=>$id]);
                pushAdminLog('Removed Admin', $t['name']??'', 'admin', $admin);
                json_ok(['msg' => 'Admin removed.']);
            }
            if ($action === 'resetPassword') {
                $username = str_clean($b['username'] ?? '', 100);
                $pwd = $b['password'] ?? '';
                if (!$username || !$pwd) json_err('Username and password required.');
                if (strlen($pwd) < 4) json_err('Password must be at least 4 characters.');
                $hash = password_hash($pwd, PASSWORD_DEFAULT);
                $db->prepare("UPDATE admins SET password=:p, plain_password=:pp WHERE username=:u")
                   ->execute([':p'=>$hash, ':pp'=>$pwd, ':u'=>$username]);
                pushAdminLog('Reset Password', $username, 'admin', $admin);
                json_ok(['msg' => 'Password reset.']);
            }
            json_err('Unknown admin action.');
        }

        // ── Restrictions ──
        case 'restrictions': {
            if ($action === 'saveGlobal') {
                $day   = ($b['day']   !== '' && $b['day']   !== null) ? (int)$b['day'] : null;
                $month = ($b['month'] !== '' && $b['month'] !== null) ? (int)$b['month'] : null;
                $total = ($b['total'] !== '' && $b['total'] !== null) ? (int)$b['total'] : null;
                $db->prepare("INSERT INTO restrictions (type,mobile,per_day,per_month,total) VALUES ('global',NULL,:d,:m,:t)
                    ON DUPLICATE KEY UPDATE per_day=VALUES(per_day),per_month=VALUES(per_month),total=VALUES(total)")
                   ->execute([':d'=>$day,':m'=>$month,':t'=>$total]);
                pushAdminLog('Set Global Restriction', 'Day:'.$day.' Month:'.$month.' Total:'.$total, 'setting', $admin);
                json_ok(['msg' => 'Saved.']);
            }
            if ($action === 'saveIndividual') {
                $mobile = str_clean($b['mobile'] ?? '', 15);
                $name   = str_clean($b['name'] ?? '', 150);
                $day    = ($b['day']   !== '' && $b['day']   !== null) ? (int)$b['day'] : null;
                $month  = ($b['month'] !== '' && $b['month'] !== null) ? (int)$b['month'] : null;
                $total  = ($b['total'] !== '' && $b['total'] !== null) ? (int)$b['total'] : null;
                if (!preg_match('/^\d{10}$/', $mobile)) json_err('Invalid mobile.');
                $db->prepare("INSERT INTO restrictions (type,mobile,name,per_day,per_month,total) VALUES ('individual',:mob,:n,:d,:m,:t)
                    ON DUPLICATE KEY UPDATE name=VALUES(name),per_day=VALUES(per_day),per_month=VALUES(per_month),total=VALUES(total)")
                   ->execute([':mob'=>$mobile,':n'=>$name,':d'=>$day,':m'=>$month,':t'=>$total]);
                pushAdminLog('Set Individual Restriction', $mobile, 'setting', $admin);
                json_ok(['msg' => 'Saved.']);
            }
            if ($action === 'deleteIndividual') {
                $id = (int)($b['id'] ?? 0);
                $db->prepare("DELETE FROM restrictions WHERE id=:id AND type='individual'")->execute([':id'=>$id]);
                json_ok(['msg' => 'Removed.']);
            }
            json_err('Unknown restriction action.');
        }

        // ── Payment Options ──
        case 'paymentOpts': {
            if ($action === 'save') {
                $optId   = str_clean($b['opt_id'] ?? '', 20);
                $method  = str_clean($b['method'] ?? '', 20);
                $label   = str_clean($b['label'] ?? '', 100);
                $details = isset($b['details']) ? json_encode($b['details'], JSON_UNESCAPED_UNICODE) : '{}';
                $notes   = str_clean($b['notes'] ?? '', 1000);
                $status  = str_clean($b['status'] ?? 'active', 20);
                if (!$method || !$label) json_err('Method and label required.');
                if ($optId) {
                    $db->prepare("UPDATE payment_options SET label=:l,method=:m,details=:d,notes=:n,status=:s WHERE opt_id=:o")
                       ->execute([':l'=>$label,':m'=>$method,':d'=>$details,':n'=>$notes,':s'=>$status,':o'=>$optId]);
                } else {
                    $optId = 'PAY'.date('YmdHis');
                    $db->prepare("INSERT INTO payment_options (opt_id,label,method,details,notes,status) VALUES (:o,:l,:m,:d,:n,:s)")
                       ->execute([':o'=>$optId,':l'=>$label,':m'=>$method,':d'=>$details,':n'=>$notes,':s'=>$status]);
                }
                recordHistory('payment_option', $optId, 'saved', null, null, $label.' / '.$method, $admin);
                pushAdminLog('Saved Payment Option', $label, 'setting', $admin);
                json_ok(['msg' => 'Saved.', 'opt_id' => $optId]);
            }
            if ($action === 'delete') {
                $optId = str_clean($b['opt_id'] ?? '', 20);
                $db->prepare("DELETE FROM payment_options WHERE opt_id=:o")->execute([':o'=>$optId]);
                json_ok(['msg' => 'Deleted.']);
            }
            if ($action === 'toggle') {
                $optId = str_clean($b['opt_id'] ?? '', 20);
                $db->prepare("UPDATE payment_options SET status = IF(status='active','inactive','active') WHERE opt_id=:o")
                   ->execute([':o'=>$optId]);
                json_ok(['msg' => 'Toggled.']);
            }
            json_err('Unknown payment action.');
        }

        // ── Panel Control ──
        case 'panelCtrl': {
            if ($action === 'saveGlobal') {
                $settings = json_encode($b['settings'] ?? [], JSON_UNESCAPED_UNICODE);
                $existing = $db->query("SELECT id FROM user_panel_ctrl WHERE type='global' LIMIT 1")->fetch();
                if ($existing) {
                    $db->prepare("UPDATE user_panel_ctrl SET settings=:s WHERE id=:id")->execute([':s'=>$settings,':id'=>$existing['id']]);
                } else {
                    $db->prepare("INSERT INTO user_panel_ctrl (type,settings) VALUES ('global',:s)")->execute([':s'=>$settings]);
                }
                $db->prepare("INSERT INTO up_ctrl_history (action,scope,detail,changed_by) VALUES ('Global Save','All Users',:d,:cb)")
                   ->execute([':d'=>$settings,':cb'=>$admin['name']]);
                json_ok(['msg' => 'Saved.']);
            }
            if ($action === 'saveOverride') {
                $cpId = str_clean($b['cp_id'] ?? '', 20);
                $mobile = str_clean($b['mobile'] ?? '', 15);
                $name = str_clean($b['name'] ?? '', 150);
                $settings = json_encode($b['settings'] ?? [], JSON_UNESCAPED_UNICODE);
                $existing = $db->prepare("SELECT id FROM user_panel_ctrl WHERE type='override' AND mobile=:m LIMIT 1");
                $existing->execute([':m'=>$mobile]);
                $ex = $existing->fetch();
                if ($ex) {
                    $db->prepare("UPDATE user_panel_ctrl SET settings=:s, cp_id=:c, mobile=:m WHERE id=:id")
                       ->execute([':s'=>$settings,':c'=>$cpId,':m'=>$mobile,':id'=>$ex['id']]);
                    $histAction = 'Override Updated';
                } else {
                    $db->prepare("INSERT INTO user_panel_ctrl (type,cp_id,mobile,settings) VALUES ('override',:c,:m,:s)")
                       ->execute([':c'=>$cpId,':m'=>$mobile,':s'=>$settings]);
                    $histAction = 'Override Added';
                }
                $db->prepare("INSERT INTO up_ctrl_history (action,scope,detail,changed_by) VALUES (:a,:scope,:d,:cb)")
                   ->execute([':a'=>$histAction,':scope'=>$name.' ('.$cpId.')',':d'=>$settings,':cb'=>$admin['name']]);
                json_ok(['msg' => 'Override saved.']);
            }
            if ($action === 'deleteOverride') {
                $id = (int)($b['id'] ?? 0);
                $mobile = str_clean($b['mobile'] ?? '', 15);
                if ($id) {
                    $db->prepare("DELETE FROM user_panel_ctrl WHERE id=:id AND type='override'")->execute([':id'=>$id]);
                } elseif ($mobile) {
                    $db->prepare("DELETE FROM user_panel_ctrl WHERE mobile=:m AND type='override'")->execute([':m'=>$mobile]);
                } else {
                    json_err('id or mobile required.');
                }
                pushAdminLog('Removed User Override', $mobile ?: "ID:$id", 'setting', $admin);
                json_ok(['msg' => 'Override removed.']);
            }
            json_err('Unknown panel ctrl action.');
        }

        // ── OTP Ban/Unban ──
        case 'otpLogs': {
            if ($action === 'toggleBan') {
                $mobile = str_clean($b['mobile'] ?? '', 15);
                $db->prepare("UPDATE otp_logs SET banned = NOT banned WHERE mobile=:m")->execute([':m'=>$mobile]);
                $row = $db->prepare("SELECT banned FROM otp_logs WHERE mobile=:m LIMIT 1");
                $row->execute([':m'=>$mobile]); $r = $row->fetch();
                $isBanned = $r && (int)$r['banned'];
                pushAdminLog($isBanned?'Banned User':'Unbanned User', $mobile, 'ban', $admin);
                json_ok(['msg' => $isBanned ? 'Banned.' : 'Unbanned.', 'banned' => $isBanned]);
            }
            json_err('Unknown otp action.');
        }

        // ── Stories ──
        case 'stories': {
            if ($action === 'create') {
                $groom = str_clean($b['groom'] ?? '', 200);
                $bride = str_clean($b['bride'] ?? '', 200);
                $date  = str_clean($b['date'] ?? '', 10);
                $quote = str_clean($b['quote'] ?? '', 2000);
                if (!$groom || !$bride) json_err('Groom and bride required.');
                $db->prepare("INSERT INTO success_stories (groom,bride,date,quote) VALUES (:g,:b,:d,:q)")
                   ->execute([':g'=>$groom,':b'=>$bride,':d'=>$date?:null,':q'=>$quote]);
                pushAdminLog('Added Story', $groom.' + '.$bride, 'story', $admin);
                json_ok(['msg' => 'Story added.']);
            }
            if ($action === 'delete') {
                $id = (int)($b['id'] ?? 0);
                $db->prepare("DELETE FROM success_stories WHERE id=:id")->execute([':id'=>$id]);
                json_ok(['msg' => 'Story removed.']);
            }
            json_err('Unknown story action.');
        }

        // ── Notifications ──
        case 'notifications': {
            if ($action === 'markRead') {
                $id = (int)($b['id'] ?? 0);
                $db->prepare("UPDATE notifications SET unread=0 WHERE id=:id")->execute([':id'=>$id]);
                json_ok(['msg' => 'OK']);
            }
            if ($action === 'markAllRead') {
                $db->query("UPDATE notifications SET unread=0");
                json_ok(['msg' => 'All read.']);
            }
            json_err('Unknown notification action.');
        }

        // ── Deleted restore ──
        case 'deleted': {
            if ($action === 'restore') {
                $id = (int)($b['id'] ?? 0);
                $row = $db->prepare("SELECT * FROM deleted_profiles WHERE id=:id LIMIT 1");
                $row->execute([':id'=>$id]); $d = $row->fetch();
                if (!$d) json_err('Not found.');
                $pj = json_decode($d['profile_json'], true);
                if ($pj) {
                    unset($pj['id'], $pj['updated_at']);
                    $cols = array_keys($pj);
                    $ph = array_map(fn($c)=>':'.$c, $cols);
                    $db->prepare("INSERT INTO profiles (`".implode('`,`',$cols)."`) VALUES (".implode(',',$ph).")")
                       ->execute(array_combine($ph, array_values($pj)));
                }
                $db->prepare("DELETE FROM deleted_profiles WHERE id=:id")->execute([':id'=>$id]);
                pushAdminLog('Restored Profile', $d['name'].' - '.$d['cp_id'], 'profile', $admin);
                json_ok(['msg' => 'Restored.']);
            }
            json_err('Unknown deleted action.');
        }

        // ── Expired restore ──
        case 'expired': {
            if ($action === 'restore') {
                $id = (int)($b['id'] ?? 0);
                $row = $db->prepare("SELECT * FROM expired_profiles WHERE id=:id LIMIT 1");
                $row->execute([':id'=>$id]); $e = $row->fetch();
                if (!$e) json_err('Not found.');
                $db->prepare("UPDATE profiles SET status='Preapproved', approved=NULL, expiry=NULL WHERE cp_id=:c")
                   ->execute([':c'=>$e['cp_id']]);
                $db->prepare("DELETE FROM expired_profiles WHERE id=:id")->execute([':id'=>$id]);
                pushAdminLog('Restored Expired', $e['name'].' - '.$e['cp_id'], 'expired', $admin);
                json_ok(['msg' => 'Restored.']);
            }
            json_err('Unknown expired action.');
        }

        // ── Mobile Requests ──
        case 'mobileReqs': {
            if ($action === 'approve') {
                $id = (int)($b['id'] ?? 0);
                $req = $db->prepare("SELECT * FROM mobile_requests WHERE id=:id AND status='pending' LIMIT 1");
                $req->execute([':id'=>$id]); $r = $req->fetch();
                if (!$r) json_err('Request not found or already actioned.');

                $old = $r['old_mobile'];
                $new = $r['new_mobile'];

                // Block if the new mobile is already used by a different profile
                $dup = $db->prepare("SELECT cp_id FROM profiles WHERE mobile=:m AND mobile<>:old LIMIT 1");
                $dup->execute([':m'=>$new, ':old'=>$old]);
                if ($dup->fetch()) json_err('New number ' . $new . ' is already used by another profile. Cannot approve.');

                try {
                    $db->beginTransaction();
                    $db->prepare("UPDATE profiles SET mobile=:new WHERE mobile=:old")->execute([':new'=>$new, ':old'=>$old]);
                    $db->prepare("UPDATE bills SET mobile=:new WHERE mobile=:old")->execute([':new'=>$new, ':old'=>$old]);

                    // otp_logs.mobile has a UNIQUE index. The user almost always has
                    // an otp_logs row for the new number (from verifying it when
                    // submitting the request). Drop the stale log rows on both sides
                    // before re-pointing — a fresh log row will be written on next OTP.
                    $db->prepare("DELETE FROM otp_logs WHERE mobile IN (:old, :new)")
                       ->execute([':old'=>$old, ':new'=>$new]);

                    $db->prepare("UPDATE mobile_requests SET status='approved', admin_note=:n WHERE id=:id")
                       ->execute([':n'=>'Approved by '.$admin['name'], ':id'=>$id]);
                    $db->commit();
                } catch (Throwable $e) {
                    if ($db->inTransaction()) $db->rollBack();
                    error_log('approve mobileReq '.$id.' failed: '.$e->getMessage());
                    json_err('Approval failed: ' . $e->getMessage(), 500);
                }
                pushAdminLog('Approved Mobile Change', $old.' -> '.$new, 'admin', $admin);
                json_ok(['msg' => 'Approved.']);
            }
            if ($action === 'reject') {
                $id = (int)($b['id'] ?? 0);
                $note = str_clean($b['note'] ?? 'Rejected', 500);
                $db->prepare("UPDATE mobile_requests SET status='rejected', admin_note=:n WHERE id=:id")
                   ->execute([':n'=>$note,':id'=>$id]);
                json_ok(['msg' => 'Rejected.']);
            }
            json_err('Unknown mobile req action.');
        }

        // ── Role Permissions ──
        case 'rolePerms': {
            if ($action === 'save') {
                $role = str_clean($b['role'] ?? '', 20);
                $perms = json_encode($b['permissions'] ?? [], JSON_UNESCAPED_UNICODE);
                $db->prepare("INSERT INTO role_permissions (role, permissions) VALUES (:r,:p) ON DUPLICATE KEY UPDATE permissions=VALUES(permissions)")
                   ->execute([':r'=>$role, ':p'=>$perms]);
                recordHistory('role_permission', $role, 'updated', null, null, $perms, $admin);
                pushAdminLog('Updated Role Permissions', $role, 'setting', $admin);
                json_ok(['msg' => 'Saved.']);
            }
            json_err('Unknown role action.');
        }

        // ── Alert Thresholds ──
        case 'alertThresholds': {
            if ($action === 'save') {
                $cd = (int)($b['contact_day'] ?? 10);
                $od = (int)($b['otp_day'] ?? 3);
                $pd = (int)($b['profile_day'] ?? 10);
                $db->query("DELETE FROM alert_thresholds");
                $db->prepare("INSERT INTO alert_thresholds (contact_day,otp_day,profile_day) VALUES (:c,:o,:p)")
                   ->execute([':c'=>$cd,':o'=>$od,':p'=>$pd]);
                json_ok(['msg' => 'Thresholds saved.']);
            }
            json_err('Unknown threshold action.');
        }

        // ── Admin Log ──
        case 'adminLog': {
            if ($action === 'clear') {
                $db->query("DELETE FROM admin_log");
                json_ok(['msg' => 'Log cleared.']);
            }
            json_err('Unknown log action.');
        }

        case 'messages': {
            $act = str_clean($b['action'] ?? '', 30);
            if ($act === 'reply') {
                $id = (int)($b['id'] ?? 0);
                $reply = str_clean($b['reply'] ?? '', 2000);
                if (!$id || !$reply) json_err('Message ID and reply required');
                $db->prepare("UPDATE contact_messages SET admin_reply = :r, replied_by = :by, replied_at = NOW(), status = 'replied' WHERE id = :id")
                    ->execute([':r' => $reply, ':by' => $admin['name'], ':id' => $id]);
                json_ok(['message' => 'Reply saved']);
            }
            if ($act === 'status') {
                $id = (int)($b['id'] ?? 0);
                $status = str_clean($b['status'] ?? '', 10);
                if (!$id || !in_array($status, ['new','read','replied','closed'])) json_err('Invalid status');
                $db->prepare("UPDATE contact_messages SET status = :s WHERE id = :id")
                    ->execute([':s' => $status, ':id' => $id]);
                json_ok(['message' => 'Status updated']);
            }
            if ($act === 'delete') {
                $id = (int)($b['id'] ?? 0);
                if (!$id) json_err('Message ID required');
                $db->prepare("DELETE FROM contact_messages WHERE id = :id")->execute([':id' => $id]);
                json_ok(['message' => 'Message deleted']);
            }
            json_err('Unknown action');
        }

        case 'accounts': {
            $act = str_clean($b['action'] ?? '', 30);
            if ($act === 'add') {
                $date = str_clean($b['date'] ?? '', 10);
                $type = str_clean($b['type'] ?? '', 10);
                $category = str_clean($b['category'] ?? '', 100);
                $amount = (float)($b['amount'] ?? 0);
                $desc = str_clean($b['description'] ?? '', 500);
                $mode = str_clean($b['payment_mode'] ?? '', 50);
                $ref = str_clean($b['reference'] ?? '', 100);
                $related = str_clean($b['related'] ?? '', 20);
                $adminName = str_clean($b['admin_name'] ?? 'Admin', 100);

                if (!$date || !$type || !$category || $amount <= 0) json_err('Date, type, category and amount required');

                $relCpId = null; $relMobile = null;
                if ($related) {
                    if (preg_match('/^\d{10}$/', $related)) $relMobile = $related;
                    else $relCpId = $related;
                }

                $db->prepare("INSERT INTO accounts (date, type, category, description, amount, payment_mode, reference, related_cp_id, related_mobile, created_by)
                    VALUES (:d, :t, :c, :desc, :a, :m, :r, :cp, :mob, :by)")
                    ->execute([':d' => $date, ':t' => $type, ':c' => $category, ':desc' => $desc,
                               ':a' => $amount, ':m' => $mode, ':r' => $ref,
                               ':cp' => $relCpId, ':mob' => $relMobile, ':by' => $adminName]);
                json_ok(['message' => 'Entry added']);
            }
            if ($act === 'update') {
                $id = (int)($b['id'] ?? 0);
                if (!$id) json_err('Entry ID required');
                $date = str_clean($b['date'] ?? '', 10);
                $type = str_clean($b['type'] ?? '', 10);
                $category = str_clean($b['category'] ?? '', 100);
                $amount = (float)($b['amount'] ?? 0);
                $desc = str_clean($b['description'] ?? '', 500);
                $mode = str_clean($b['payment_mode'] ?? '', 50);
                $ref = str_clean($b['reference'] ?? '', 100);
                $db->prepare("UPDATE accounts SET date=:d, type=:t, category=:c, description=:desc, amount=:a, payment_mode=:m, reference=:r WHERE id=:id")
                    ->execute([':d'=>$date, ':t'=>$type, ':c'=>$category, ':desc'=>$desc, ':a'=>$amount, ':m'=>$mode, ':r'=>$ref, ':id'=>$id]);
                json_ok(['message' => 'Entry updated']);
            }
            if ($act === 'delete') {
                $id = (int)($b['id'] ?? 0);
                if (!$id) json_err('Entry ID required');
                $db->prepare("DELETE FROM accounts WHERE id=:id")->execute([':id'=>$id]);
                json_ok(['message' => 'Entry deleted']);
            }
            if ($act === 'sync_bills') {
                // Import all paid bills that are not yet in accounts
                $bills = $db->query("SELECT * FROM bills WHERE amount > 0 ORDER BY billed_date ASC")->fetchAll();
                $added = 0;
                foreach ($bills as $bill) {
                    $cpId = $bill['cp_id'] ?? '';
                    // Check if already synced (by reference = cp_id and matching amount)
                    $exists = $db->prepare("SELECT id FROM accounts WHERE reference = :r AND amount = :a AND type = 'income' LIMIT 1");
                    $exists->execute([':r' => $cpId, ':a' => $bill['amount']]);
                    if ($exists->fetch()) continue;
                    $db->prepare("INSERT INTO accounts (date, type, category, description, amount, payment_mode, reference, related_cp_id, created_by)
                        VALUES (:d, 'income', 'Registration Fee', :desc, :a, :m, :r, :cp, :by)")
                        ->execute([
                            ':d' => $bill['billed_date'] ?: date('Y-m-d'),
                            ':desc' => ($bill['name'] ?? '') . ' - ' . ($bill['plan_name'] ?? '') . ' (' . $cpId . ')',
                            ':a' => $bill['amount'],
                            ':m' => $bill['payment'] ?? 'Cash',
                            ':r' => $cpId,
                            ':cp' => $cpId,
                            ':by' => $bill['billed_by'] ?? 'Admin'
                        ]);
                    $added++;
                }
                json_ok(['message' => $added . ' bill(s) synced to accounts', 'added' => $added]);
            }
            json_err('Unknown action');
        }

        case 'userOrders': {
            $act = str_clean($b['action'] ?? '', 30);
            if ($act === 'process') {
                $id = (int)($b['id'] ?? 0);
                $status = str_clean($b['status'] ?? 'approved', 20);
                $adminNote = str_clean($b['admin_note'] ?? '', 500);
                $adminName = str_clean($b['admin_name'] ?? 'Admin', 100);
                // Get order details before updating
                $orderRow = $db->prepare("SELECT * FROM user_orders WHERE id = :id"); $orderRow->execute([':id' => $id]); $o = $orderRow->fetch();

                $db->prepare("UPDATE user_orders SET status = :s, admin_note = :n, processed_by = :by, processed_at = NOW() WHERE id = :id")
                   ->execute([':s' => $status, ':n' => $adminNote, ':by' => $adminName, ':id' => $id]);

                // If approved, update the profile's plan + auto-create bill
                if ($status === 'approved' && $o) {
                    // Get plan validity
                    $planStmt = $db->prepare("SELECT validity, type FROM subscription_plans WHERE LOWER(name) = LOWER(:n) LIMIT 1");
                    $planStmt->execute([':n' => $o['plan']]);
                    $planRow = $planStmt->fetch();
                    $validity = $planRow ? (int)$planRow['validity'] : 365;
                    $planType = $planRow ? $planRow['type'] : $o['plan'];

                    $billedDate = date('Y-m-d');
                    $expiryDate = date('Y-m-d', strtotime("+{$validity} days"));

                    // Update profile plan + dates + clear pending payment
                    $db->prepare("UPDATE profiles SET plan = :pl, approved = :ad, expiry = :ex, payment_status = NULL, pending_plan = NULL, pending_amount = NULL, pending_pay_opt_id = NULL WHERE mobile = :m")
                       ->execute([':pl' => $planType, ':ad' => $billedDate, ':ex' => $expiryDate, ':m' => $o['mobile']]);

                    // Auto-create bill
                    $db->prepare("INSERT INTO bills (cp_id, mobile, name, plan_name, plan_type, amount, payment, billed_by, billed_date, expiry)
                        VALUES (:cp, :m, :n, :pn, :pt, :a, :pay, :by, :bd, :ex)")
                        ->execute([
                            ':cp' => $o['cp_id'], ':m' => $o['mobile'], ':n' => $o['name'],
                            ':pn' => ucfirst($o['plan']), ':pt' => $planType,
                            ':a' => (float)preg_replace('/[^\d.]/', '', $o['amount']),
                            ':pay' => 'Online - ' . ($o['method'] ?: 'User Payment'),
                            ':by' => $adminName, ':bd' => $billedDate, ':ex' => $expiryDate
                        ]);

                    // Also record in bill_history
                    $db->prepare("INSERT INTO bill_history (cp_id, mobile, name, plan_name, amount, payment, action, recorded_by, recorded_at)
                        VALUES (:cp, :m, :n, :pn, :a, :pay, 'Created', :by, NOW())")
                        ->execute([
                            ':cp' => $o['cp_id'], ':m' => $o['mobile'], ':n' => $o['name'],
                            ':pn' => ucfirst($o['plan']),
                            ':a' => (float)preg_replace('/[^\d.]/', '', $o['amount']),
                            ':pay' => 'Online - ' . ($o['method'] ?: 'User Payment'),
                            ':by' => $adminName
                        ]);
                }

                // Archive log (permanent, no delete/edit)
                if ($o) {
                    $db->prepare("INSERT INTO order_archive (order_id, mobile, cp_id, name, plan, amount, method, txn_ref, notes, action, action_by, admin_note, created_at)
                        VALUES (:oid, :m, :cp, :n, :pl, :a, :mt, :tx, :nt, :act, :by, :an, NOW())")
                        ->execute([':oid' => $id, ':m' => $o['mobile'], ':cp' => $o['cp_id'], ':n' => $o['name'],
                                   ':pl' => $o['plan'], ':a' => $o['amount'], ':mt' => $o['method'],
                                   ':tx' => $o['txn_ref'], ':nt' => $o['notes'],
                                   ':act' => ucfirst($status), ':by' => $adminName, ':an' => $adminNote]);
                }
                json_ok(['message' => 'Order processed']);
            }
            json_err('Unknown action');
        }

        case 'directLogin': {
            $act = str_clean($b['action'] ?? '', 30);
            if ($act === 'add') {
                $mobile = preg_replace('/\D/', '', $b['mobile'] ?? '');
                $adminName = str_clean($b['admin_name'] ?? 'Admin', 100);
                if (strlen($mobile) !== 10) json_err('Invalid mobile');
                $chk = $db->prepare("SELECT id FROM direct_login WHERE mobile = :m"); $chk->execute([':m' => $mobile]);
                if ($chk->fetch()) json_err('Mobile already has direct login');
                $prof = $db->prepare("SELECT cp_id, name FROM profiles WHERE mobile = :m LIMIT 1"); $prof->execute([':m' => $mobile]);
                $p = $prof->fetch();
                $db->prepare("INSERT INTO direct_login (mobile, name, cp_id, status, created_by, created_at) VALUES (:m, :n, :cp, 'active', :by, NOW())")
                   ->execute([':m' => $mobile, ':n' => $p['name'] ?? null, ':cp' => $p['cp_id'] ?? null, ':by' => $adminName]);
                $db->prepare("INSERT INTO direct_login_log (mobile, name, cp_id, action, action_by, created_at) VALUES (:m, :n, :cp, 'Added', :by, NOW())")
                   ->execute([':m' => $mobile, ':n' => $p['name'] ?? null, ':cp' => $p['cp_id'] ?? null, ':by' => $adminName]);
                json_ok(['message' => 'Direct login added']);
            }
            if ($act === 'toggle') {
                $id = (int)($b['id'] ?? 0);
                $status = str_clean($b['status'] ?? 'active', 10);
                $adminName = str_clean($b['admin_name'] ?? 'Admin', 100);
                $dl = $db->prepare("SELECT mobile, name, cp_id FROM direct_login WHERE id = :id"); $dl->execute([':id' => $id]); $dlRow = $dl->fetch();
                if ($status === 'inactive') {
                    $db->prepare("UPDATE direct_login SET status = 'inactive', deactivated_by = :by, deactivated_at = NOW() WHERE id = :id")
                       ->execute([':by' => $adminName, ':id' => $id]);
                } else {
                    $db->prepare("UPDATE direct_login SET status = 'active', deactivated_by = NULL, deactivated_at = NULL WHERE id = :id")
                       ->execute([':id' => $id]);
                }
                if ($dlRow) {
                    $db->prepare("INSERT INTO direct_login_log (mobile, name, cp_id, action, action_by, created_at) VALUES (:m, :n, :cp, :a, :by, NOW())")
                       ->execute([':m' => $dlRow['mobile'], ':n' => $dlRow['name'], ':cp' => $dlRow['cp_id'],
                                  ':a' => $status === 'inactive' ? 'Deactivated' : 'Activated', ':by' => $adminName]);
                }
                json_ok(['message' => 'Status updated']);
            }
            json_err('Unknown action');
        }

        case 'alertThresholds': {
            $act = str_clean($b['action'] ?? '', 30);
            if ($act === 'save') {
                $cd = (int)($b['contact_day'] ?? 10);
                $od = (int)($b['otp_day'] ?? 3);
                $pd = (int)($b['profile_day'] ?? 10);
                $existing = $db->query("SELECT id FROM alert_thresholds LIMIT 1")->fetch();
                if ($existing) {
                    $db->prepare("UPDATE alert_thresholds SET contact_day = :c, otp_day = :o, profile_day = :p WHERE id = :id")
                       ->execute([':c' => $cd, ':o' => $od, ':p' => $pd, ':id' => $existing['id']]);
                } else {
                    $db->prepare("INSERT INTO alert_thresholds (contact_day, otp_day, profile_day) VALUES (:c, :o, :p)")
                       ->execute([':c' => $cd, ':o' => $od, ':p' => $pd]);
                }
                json_ok(['message' => 'Thresholds saved']);
            }
            json_err('Unknown action');
        }

        case 'profileReports': {
            $act = str_clean($b['action'] ?? '', 30);
            if ($act === 'resolve') {
                $id = (int)($b['id'] ?? 0);
                $status = str_clean($b['status'] ?? 'resolved', 20);
                $note = str_clean($b['admin_note'] ?? '', 500);
                $adminName = $admin['name'] ?? 'Admin';
                $db->prepare("UPDATE profile_reports SET status = :s, admin_note = :n, resolved_at = NOW(), resolved_by = :by WHERE id = :id")
                   ->execute([':s' => $status, ':n' => $note, ':by' => $adminName, ':id' => $id]);
                json_ok(['message' => 'Report updated']);
            }
            json_err('Unknown action');
        }

        default: json_err('Unknown section: ' . $section);
    }
}

json_err('Method not allowed', 405);
