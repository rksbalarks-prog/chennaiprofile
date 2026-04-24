<?php
// matrimony/api/payment.php

require_once __DIR__ . '/../config.php';

cors();
$mobile = authRequired();
$db     = getDB();

// ── GET ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = str_clean($_GET['type'] ?? '', 20);

    if ($type === 'plans') {
        $stmt = $db->prepare(
            "SELECT
               plan_id,
               name,
               type,
               description,
               amount,
               validity,
               status,
               user_visible,
               created_by,
               created_at
             FROM subscription_plans
             WHERE status = 'active'
             ORDER BY amount ASC"
        );
        $stmt->execute();
        $plans = $stmt->fetchAll();

        foreach ($plans as &$plan) {
            $plan['amount']   = (float) $plan['amount'];
            $plan['validity'] = (int)   $plan['validity'];
        }
        unset($plan);

        json_ok(['plans' => $plans]);
    }

    if ($type === 'options') {
        $stmt = $db->prepare(
            "SELECT
               opt_id,
               label,
               method,
               details,
               notes,
               status
             FROM payment_options
             WHERE status = 'active'
             ORDER BY id ASC"
        );
        $stmt->execute();
        $options = $stmt->fetchAll();

        foreach ($options as &$opt) {
            if (!empty($opt['details']) && is_string($opt['details'])) {
                $decoded = json_decode($opt['details'], true);
                $opt['details'] = is_array($decoded) ? $decoded : null;
            }
        }
        unset($opt);

        json_ok(['options' => $options]);
    }

    json_err("Invalid type. Use ?type=plans or ?type=options");
}

// ── POST ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $b      = body();
    $action = str_clean($b['action'] ?? '', 20);

    if ($action !== 'notify') {
        json_err('Unknown action.');
    }

    // Validate
    $planName   = str_clean($b['planName']   ?? '', 100);
    $planAmount = isset($b['planAmount']) ? (float) $b['planAmount'] : null;
    $payOptId   = str_clean($b['payOptId']   ?? '', 20);

    if ($planName === '') {
        json_err('planName is required.');
    }
    if ($planAmount === null || $planAmount < 0) {
        json_err('planAmount must be a non-negative number.');
    }
    if ($payOptId === '') {
        json_err('payOptId is required.');
    }

    // Verify payment option exists and is active
    $optCheck = $db->prepare(
        "SELECT opt_id FROM payment_options WHERE opt_id = :o AND status = 'active' LIMIT 1"
    );
    $optCheck->execute([':o' => $payOptId]);
    if (!$optCheck->fetch()) {
        json_err('Invalid or inactive payment option.');
    }

    // Ensure profile exists
    $profCheck = $db->prepare("SELECT id FROM profiles WHERE mobile = :m LIMIT 1");
    $profCheck->execute([':m' => $mobile]);
    if (!$profCheck->fetch()) {
        json_err('Profile not found. Please create a profile first.', 404);
    }

    // Update profile with pending payment info
    $upd = $db->prepare(
        "UPDATE profiles
         SET
           pending_plan        = :plan,
           pending_amount      = :amt,
           pending_pay_opt_id  = :opt,
           payment_status      = 'payment_notified'
         WHERE mobile = :m"
    );
    $upd->execute([
        ':plan' => $planName,
        ':amt'  => $planAmount,
        ':opt'  => $payOptId,
        ':m'    => $mobile,
    ]);

    json_ok(['msg' => 'Payment notification submitted. Admin will verify and activate your plan shortly.']);
}

json_err('Method not allowed', 405);
