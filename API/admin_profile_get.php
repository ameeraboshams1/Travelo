<?php
require __DIR__ . '/admin_guard.php';

$stmt = $pdo->prepare("SELECT id, first_name, last_name, display_name, email, avatar_url, is_super, is_active, created_at
                       FROM admins WHERE id=? LIMIT 1");
$stmt->execute([$adminId]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) json_out(false, 'Admin not found', [], 404);

json_out(true, 'OK', ['admin' => $admin]);
