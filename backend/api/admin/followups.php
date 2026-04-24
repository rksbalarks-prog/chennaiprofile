<?php
// Admin follow-ups: list, create, update, undo (reopen closed)
require_once __DIR__ . '/../../admin-config.php';
cors();
$admin = adminRequired();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = $db->query("SELECT * FROM follow_ups ORDER BY date DESC, id DESC")->fetchAll();
    json_ok(['followUps' => $rows]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $b = body();
    if (isset($b['cpId']) && !isset($b['cp_id'])) $b['cp_id'] = $b['cpId'];
    if (isset($b['memberName']) && !isset($b['member_name'])) $b['member_name'] = $b['memberName'];
    $action = str_clean($b['action'] ?? '', 20);

    switch ($action) {
        case 'create': {
            $cpId   = str_clean($b['cp_id'] ?? '', 20);
            $member = str_clean($b['member_name'] ?? '', 150);
            $mobile = str_clean($b['mobile'] ?? '', 15);
            $type   = str_clean($b['type'] ?? '', 20);
            $date   = str_clean($b['date'] ?? '', 10);
            $reason = str_clean($b['reason'] ?? '', 1000);
            if (!$cpId || !$type || !$date) json_err('cp_id, type and date required.');
            $db->prepare("INSERT INTO follow_ups (cp_id,member_name,mobile,type,admin,date,reason) VALUES (:c,:n,:m,:t,:a,:d,:r)")
               ->execute([':c'=>$cpId,':n'=>$member,':m'=>$mobile,':t'=>$type,':a'=>$admin['name'],':d'=>$date,':r'=>$reason]);
            pushAdminLog('Scheduled Follow-up', $cpId.' - '.$type.' on '.$date, 'followup', $admin);
            json_ok(['msg' => 'Follow-up created.']);
        }

        case 'update': {
            $id     = (int)($b['id'] ?? 0);
            $type   = str_clean($b['type'] ?? '', 20);
            $date   = str_clean($b['date'] ?? '', 10);
            $reason = str_clean($b['reason'] ?? '', 1000);
            if (!$id) json_err('id required.');
            $sets = []; $params = [':id' => $id];
            if ($type) { $sets[] = "type=:t"; $params[':t'] = $type; }
            if ($date) { $sets[] = "date=:d"; $params[':d'] = $date; }
            if ($reason !== '') { $sets[] = "reason=:r"; $params[':r'] = $reason; }
            if (empty($sets)) json_err('Nothing to update.');
            $db->prepare("UPDATE follow_ups SET ".implode(',',$sets)." WHERE id=:id")->execute($params);
            pushAdminLog('Updated Follow-up', 'ID '.$id, 'followup', $admin);
            json_ok(['msg' => 'Follow-up updated.']);
        }

        case 'delete': {
            $id = (int)($b['id'] ?? 0);
            if (!$id) json_err('id required.');
            $db->prepare("DELETE FROM follow_ups WHERE id=:id")->execute([':id'=>$id]);
            json_ok(['msg' => 'Follow-up deleted.']);
        }

        default: json_err('Unknown action.');
    }
}
json_err('Method not allowed', 405);
