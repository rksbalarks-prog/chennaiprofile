<?php
// matrimony/admin-config.php
// Admin-specific auth helpers. Requires config.php.

require_once __DIR__ . '/config.php';

// Start secure session
function adminSession(): void {
    secureSession();
}

// Require admin login - returns admin row or dies with 401
function adminRequired(): array {
    adminSession();
    if (empty($_SESSION['admin_id'])) {
        json_err('Admin not authenticated', 401);
    }
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admins WHERE id = :id AND status = 'active' LIMIT 1");
    $stmt->execute([':id' => $_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    if (!$admin) {
        $_SESSION = [];
        json_err('Admin session invalid', 401);
    }
    return $admin;
}

// Check if admin has a specific permission
function adminHasPerm(array $admin, string $permId): bool {
    if ($admin['role'] === 'super') return true;
    $db = getDB();
    $stmt = $db->prepare("SELECT permissions FROM role_permissions WHERE role = :r LIMIT 1");
    $stmt->execute([':r' => $admin['role']]);
    $row = $stmt->fetch();
    if (!$row) return false;
    $perms = json_decode($row['permissions'], true);
    if (!is_array($perms)) return false;
    return !empty($perms[$permId]);
}

// Require specific permission or die with 403
function requirePerm(array $admin, string $permId): void {
    if (!adminHasPerm($admin, $permId)) {
        json_err('Permission denied: ' . $permId, 403);
    }
}

// Push admin log entry
function pushAdminLog(string $action, string $detail, string $type, ?array $admin = null): void {
    $db = getDB();
    $adminName = $admin['name'] ?? ($_SESSION['admin_name'] ?? 'System');
    $role = $admin['role'] ?? ($_SESSION['admin_role'] ?? 'staff');
    $db->prepare(
        "INSERT INTO admin_log (admin_name, role, action, detail, type, timestamp)
         VALUES (:n, :r, :a, :d, :t, NOW())"
    )->execute([':n' => $adminName, ':r' => $role, ':a' => $action, ':d' => $detail, ':t' => $type]);
}

// Record update history — permanent, no delete/edit
function recordHistory(string $entityType, ?string $entityId, string $action, ?string $field = null, $oldVal = null, $newVal = null, ?array $admin = null): void {
    $db = getDB();
    $changedBy = $admin['name'] ?? ($_SESSION['admin_name'] ?? 'System');
    $role = $admin['role'] ?? ($_SESSION['admin_role'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $db->prepare(
        "INSERT INTO update_history (entity_type, entity_id, action, field_name, old_value, new_value, changed_by, role, ip_address)
         VALUES (:et, :eid, :a, :f, :ov, :nv, :cb, :r, :ip)"
    )->execute([
        ':et'  => $entityType,
        ':eid' => $entityId,
        ':a'   => $action,
        ':f'   => $field,
        ':ov'  => is_array($oldVal) ? json_encode($oldVal) : (string)($oldVal ?? ''),
        ':nv'  => is_array($newVal) ? json_encode($newVal) : (string)($newVal ?? ''),
        ':cb'  => $changedBy,
        ':r'   => $role,
        ':ip'  => $ip,
    ]);
}

// Record multiple field changes at once
function recordFieldChanges(string $entityType, string $entityId, string $action, array $oldData, array $newData, array $fields, ?array $admin = null): void {
    foreach ($fields as $field) {
        $oldVal = $oldData[$field] ?? '';
        $newVal = $newData[$field] ?? '';
        if ((string)$oldVal !== (string)$newVal) {
            recordHistory($entityType, $entityId, $action, $field, $oldVal, $newVal, $admin);
        }
    }
}

// Push notification
function pushNotification(string $title, string $desc, string $icon = '', string $bg = '#fdf1ee'): void {
    $db = getDB();
    $db->prepare(
        "INSERT INTO notifications (icon, bg, title, description, time_label, unread)
         VALUES (:i, :b, :t, :d, 'Just now', 1)"
    )->execute([':i' => $icon, ':b' => $bg, ':t' => $title, ':d' => $desc]);
}
