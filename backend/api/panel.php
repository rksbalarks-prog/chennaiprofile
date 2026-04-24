<?php
// matrimony/api/panel.php

require_once __DIR__ . '/../config.php';

cors();
$mobile = authRequired();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_err('Method not allowed', 405);
}

$db = getDB();

// ── Default control structure ────────────────────────────────────────────────
$defaults = [
    'page_profile'        => true,
    'page_bills'          => true,
    'page_addorder'       => true,
    'page_activity'       => true,
    'page_loginhistory'   => true,
    'page_settings'       => true,
    'feat_create_profile' => true,
    'feat_edit_profile'   => true,
    'feat_delete_profile' => true,
    'feat_pay_now'        => true,
    'feat_view_contact'   => true,
    'feat_view_bill'      => true,
    'feat_req_mobile'     => true,
    'feat_print_profile'  => true,
    'feat_sign_out'       => true,
];

// ── Load global settings row ─────────────────────────────────────────────────
$globalStmt = $db->prepare(
    "SELECT settings FROM user_panel_ctrl
     WHERE type = 'global'
     ORDER BY id ASC
     LIMIT 1"
);
$globalStmt->execute();
$globalRow = $globalStmt->fetch();

$globalSettings = $defaults;
if ($globalRow) {
    $decoded = json_decode($globalRow['settings'], true);
    if (is_array($decoded)) {
        $globalSettings = array_merge($defaults, $decoded);
    }
}

// ── Check for an override row matching this user by mobile ──────────────────
$ctrl = $globalSettings;

$overrideStmt = $db->prepare(
    "SELECT settings FROM user_panel_ctrl
     WHERE type = 'override'
       AND mobile = :m
     ORDER BY id DESC
     LIMIT 1"
);
$overrideStmt->execute([':m' => $mobile]);
$overrideRow = $overrideStmt->fetch();

if ($overrideRow) {
    $overrideSettings = json_decode($overrideRow['settings'], true);
    if (is_array($overrideSettings)) {
        $ctrl = array_merge($globalSettings, $overrideSettings);
    }
}

// Ensure all values are boolean
foreach ($ctrl as $key => $val) {
    $ctrl[$key] = (bool) $val;
}

json_ok(['ctrl' => $ctrl]);
