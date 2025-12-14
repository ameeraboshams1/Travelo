<?php
require __DIR__ . '/admin_guard.php';

$stmt = $pdo->prepare("SELECT is_active FROM admins WHERE id=? LIMIT 1");
$stmt->execute([$adminId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) json_out(false, 'Admin not found', [], 404);

$new = ((int)$row['is_active'] === 1) ? 0 : 1;

$upd = $pdo->prepare("UPDATE admins SET is_active=? WHERE id=?");
$upd->execute([$new, $adminId]);

json_out(true, 'Status updated', ['is_active' => $new]);
