<?php
require __DIR__ . '/admin_guard.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) json_out(false, 'Invalid JSON', [], 400);

$old = (string)($data['old_password'] ?? '');
$new = (string)($data['new_password'] ?? '');

if (strlen($new) < 8) json_out(false, 'New password must be at least 8 characters', [], 422);

$stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id=? LIMIT 1");
$stmt->execute([$adminId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) json_out(false, 'Admin not found', [], 404);

$hash = (string)$row['password_hash'];
if (!password_verify($old, $hash)) json_out(false, 'Current password is incorrect', [], 403);

$newHash = password_hash($new, PASSWORD_DEFAULT);
$upd = $pdo->prepare("UPDATE admins SET password_hash=? WHERE id=?");
$upd->execute([$newHash, $adminId]);

json_out(true, 'Password updated');
