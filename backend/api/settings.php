<?php
// matrimony/api/settings.php

require_once __DIR__ . '/../config.php';

cors();
$mobile = authRequired();
$db     = getDB();

// ── GET – return mobile change requests for session user ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $stmt = $db->prepare(
        "SELECT
           id,
           req_id,
           old_mobile,
           new_mobile,
           reason,
           requested_at,
           status,
           admin_note,
           profile_snapshot
         FROM mobile_requests
         WHERE old_mobile = :m
         ORDER BY requested_at DESC
         LIMIT 50"
    );
    $stmt->execute([':m' => $mobile]);
    $requests = $stmt->fetchAll();

    foreach ($requests as &$req) {
        if (!empty($req['profile_snapshot']) && is_string($req['profile_snapshot'])) {
            $decoded = json_decode($req['profile_snapshot'], true);
            $req['profile_snapshot'] = is_array($decoded) ? $decoded : null;
        }
    }
    unset($req);

    json_ok(['requests' => $requests]);
}

// ── POST – submit mobile change request ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $b      = body();
    $action = str_clean($b['action'] ?? '', 20);

    if ($action !== 'submit') {
        json_err('Unknown action.');
    }

    $newMobile = str_clean($b['newMobile'] ?? '', 15);
    $reason    = str_clean($b['reason']    ?? '', 500);

    if (!preg_match('/^\d{10}$/', $newMobile)) {
        json_err('Invalid new mobile number. Must be 10 digits.');
    }
    if ($newMobile === $mobile) {
        json_err('New mobile must be different from current mobile.');
    }
    if ($reason === '') {
        json_err('Reason is required.');
    }

    // Check new mobile is not already registered
    $dupCheck = $db->prepare("SELECT id FROM profiles WHERE mobile = :m LIMIT 1");
    $dupCheck->execute([':m' => $newMobile]);
    if ($dupCheck->fetch()) {
        json_err('The new mobile number is already registered to another profile.');
    }

    // Check for existing pending request
    $pendingCheck = $db->prepare(
        "SELECT id FROM mobile_requests
         WHERE old_mobile = :m AND status = 'pending'
         LIMIT 1"
    );
    $pendingCheck->execute([':m' => $mobile]);
    if ($pendingCheck->fetch()) {
        json_err('You already have a pending mobile change request.');
    }

    // Take snapshot of current profile
    $profStmt = $db->prepare("SELECT * FROM profiles WHERE mobile = :m LIMIT 1");
    $profStmt->execute([':m' => $mobile]);
    $snapshot = $profStmt->fetch() ?: null;

    // Generate unique req_id: REQ + timestamp + random
    $reqId = 'REQ' . date('YmdHis') . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);

    $ins = $db->prepare(
        "INSERT INTO mobile_requests
           (req_id, old_mobile, new_mobile, reason, requested_at, status, profile_snapshot)
         VALUES
           (:rid, :old, :new, :rsn, NOW(), 'pending', :snap)"
    );
    $ins->execute([
        ':rid'  => $reqId,
        ':old'  => $mobile,
        ':new'  => $newMobile,
        ':rsn'  => $reason,
        ':snap' => $snapshot ? json_encode($snapshot, JSON_UNESCAPED_UNICODE) : null,
    ]);

    json_ok([
        'req_id' => $reqId,
        'msg'    => 'Mobile change request submitted successfully. Please wait for admin approval.',
    ]);
}

json_err('Method not allowed', 405);
