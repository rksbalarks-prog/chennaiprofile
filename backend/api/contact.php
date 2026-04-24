<?php
// Public + admin contact messages API
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../admin-config.php';
cors();

$db = getDB();

// ── POST: Submit a message (public) or admin actions ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $b = json_decode(file_get_contents('php://input'), true) ?? [];
    $act = trim($b['action'] ?? '');

    // Public: submit contact message
    if ($act === 'submit') {
        $name    = str_clean($b['name'] ?? '', 150);
        $phone   = str_clean($b['phone'] ?? '', 15);
        $email   = str_clean($b['email'] ?? '', 150);
        $message = str_clean($b['message'] ?? '', 2000);
        $cpId    = str_clean($b['cp_id'] ?? '', 20);
        $mobile  = str_clean($b['mobile'] ?? '', 15);
        $loggedIn = !empty($b['is_logged_in']) ? 1 : 0;

        if (!$name)    json_err('Name is required');
        if (!$phone || !preg_match('/^\d{10}$/', $phone)) json_err('Valid 10-digit phone required');
        if (!$message) json_err('Message is required');

        $db->prepare("INSERT INTO contact_messages (name, phone, email, message, cp_id, mobile, is_logged_in)
            VALUES (:n, :p, :e, :m, :cp, :mob, :li)")
            ->execute([':n' => $name, ':p' => $phone, ':e' => $email ?: null,
                       ':m' => $message, ':cp' => $cpId ?: null,
                       ':mob' => $mobile ?: null, ':li' => $loggedIn]);
        json_ok(['message' => 'Message sent successfully']);
    }

    // Admin: reply
    if ($act === 'reply') {
        $admin = adminRequired();
        $id = (int)($b['id'] ?? 0);
        $reply = str_clean($b['reply'] ?? '', 2000);
        if (!$id || !$reply) json_err('Message ID and reply required');
        $db->prepare("UPDATE contact_messages SET admin_reply = :r, replied_by = :by, replied_at = NOW(), status = 'replied' WHERE id = :id")
            ->execute([':r' => $reply, ':by' => $admin['name'], ':id' => $id]);
        json_ok(['message' => 'Reply saved']);
    }

    // Admin: update status
    if ($act === 'status') {
        $admin = adminRequired();
        $id = (int)($b['id'] ?? 0);
        $status = str_clean($b['status'] ?? '', 10);
        if (!$id || !in_array($status, ['new','read','replied','closed'])) json_err('Invalid status');
        $db->prepare("UPDATE contact_messages SET status = :s WHERE id = :id")
            ->execute([':s' => $status, ':id' => $id]);
        json_ok(['message' => 'Status updated']);
    }

    // Admin: delete
    if ($act === 'delete') {
        $admin = adminRequired();
        $id = (int)($b['id'] ?? 0);
        if (!$id) json_err('Message ID required');
        $db->prepare("DELETE FROM contact_messages WHERE id = :id")->execute([':id' => $id]);
        json_ok(['message' => 'Message deleted']);
    }

    json_err('Unknown action');
}

// ── GET: List messages (admin only) ──
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $admin = adminRequired();
    $rows = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 500")->fetchAll();
    json_ok(['messages' => $rows]);
}

json_err('Method not allowed', 405);
