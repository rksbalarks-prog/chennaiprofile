<?php
// matrimony/api/activity.php

require_once __DIR__ . '/../config.php';

cors();
$mobile = authRequired();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_err('Method not allowed', 405);
}

$db = getDB();

// ── Resolve session user's cp_id ─────────────────────────────────────────────
$profStmt = $db->prepare("SELECT cp_id FROM profiles WHERE mobile = :m LIMIT 1");
$profStmt->execute([':m' => $mobile]);
$prof = $profStmt->fetch();

if (!$prof) {
    // No profile yet – return empty activity
    json_ok([
        'profileViews' => [],
        'contactViews' => [],
        'viewedBy'     => [],
    ]);
}

$cpId = $prof['cp_id'];

// ── Profile views made BY session user ──────────────────────────────────────
$pvStmt = $db->prepare(
    "SELECT
       ua.id,
       ua.mobile,
       ua.cp_id,
       ua.name,
       ua.plan,
       ua.target_cp_id,
       ua.datetime,
       ua.time_spent,
       ua.scroll_depth,
       p.name        AS target_name,
       p.plan        AS target_plan,
       p.status      AS target_status
     FROM usage_activity ua
     LEFT JOIN profiles p ON p.cp_id = ua.target_cp_id
     WHERE (ua.cp_id = :c OR ua.mobile = :m) AND ua.activity_type = 'profile_view'
     ORDER BY ua.datetime DESC
     LIMIT 200"
);
$pvStmt->execute([':c' => $cpId, ':m' => $mobile]);
$profileViews = $pvStmt->fetchAll();

// ── Contact views made BY session user ──────────────────────────────────────
$cvStmt = $db->prepare(
    "SELECT
       ua.id,
       ua.mobile,
       ua.cp_id,
       ua.name,
       ua.plan,
       ua.target_cp_id,
       ua.datetime,
       ua.time_spent,
       ua.scroll_depth,
       p.name        AS target_name,
       p.plan        AS target_plan,
       p.status      AS target_status
     FROM usage_activity ua
     LEFT JOIN profiles p ON p.cp_id = ua.target_cp_id
     WHERE (ua.cp_id = :c OR ua.mobile = :m) AND ua.activity_type = 'contact_view'
     ORDER BY ua.datetime DESC
     LIMIT 200"
);
$cvStmt->execute([':c' => $cpId, ':m' => $mobile]);
$contactViews = $cvStmt->fetchAll();

// ── Who viewed THIS user's profile (viewedBy) ────────────────────────────────
// Query: rows where target_cp_id = current user's cpId and type = profile_view
$vbStmt = $db->prepare(
    "SELECT
       ua.id,
       ua.mobile          AS viewer_mobile,
       ua.cp_id           AS viewer_cp_id,
       ua.name            AS viewer_name,
       ua.plan            AS viewer_plan,
       ua.datetime,
       ua.time_spent,
       ua.scroll_depth,
       p.name             AS viewer_profile_name,
       p.plan             AS viewer_profile_plan,
       p.status           AS viewer_status,
       p.gender           AS viewer_gender,
       p.age              AS viewer_age
     FROM usage_activity ua
     LEFT JOIN profiles p ON p.cp_id = ua.cp_id
     WHERE ua.target_cp_id = :c AND ua.activity_type = 'profile_view'
     ORDER BY ua.datetime DESC
     LIMIT 200"
);
$vbStmt->execute([':c' => $cpId]);
$viewedBy = $vbStmt->fetchAll();

// Cast numerics
$castActivity = function (array &$rows): void {
    foreach ($rows as &$row) {
        if (isset($row['time_spent']))   $row['time_spent']   = $row['time_spent']   !== null ? (int)$row['time_spent']   : null;
        if (isset($row['scroll_depth'])) $row['scroll_depth'] = $row['scroll_depth'] !== null ? (int)$row['scroll_depth'] : null;
        if (isset($row['age']))          $row['age']          = $row['age']          !== null ? (int)$row['age']          : null;
    }
    unset($row);
};

$castActivity($profileViews);
$castActivity($contactViews);
$castActivity($viewedBy);

json_ok([
    'cpId'         => $cpId,
    'profileViews' => $profileViews,
    'contactViews' => $contactViews,
    'viewedBy'     => $viewedBy,
]);
