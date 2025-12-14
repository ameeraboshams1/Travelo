<?php
require __DIR__ . '/admin_guard.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) json_out(false, 'Invalid JSON', [], 400);

$first = trim((string)($data['first_name'] ?? ''));
$last  = trim((string)($data['last_name'] ?? ''));
$disp  = trim((string)($data['display_name'] ?? ''));
$email = trim((string)($data['email'] ?? ''));

if ($first === '' || $last === '' || $email === '') {
  json_out(false, 'First name, last name and email are required', [], 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  json_out(false, 'Invalid email', [], 422);
}

$check = $pdo->prepare("SELECT id FROM admins WHERE email=? AND id<>? LIMIT 1");
$check->execute([$email, $adminId]);
if ($check->fetch()) json_out(false, 'Email already used by another admin', [], 409);

$upd = $pdo->prepare("UPDATE admins
                      SET first_name=?, last_name=?, display_name=?, email=?
                      WHERE id=?");
$upd->execute([$first, $last, ($disp === '' ? null : $disp), $email, $adminId]);

$_SESSION['admin_name'] = ($disp !== '' ? $disp : ($first.' '.$last));

json_out(true, 'Profile updated', [
  'admin' => [
    'first_name' => $first,
    'last_name' => $last,
    'display_name' => ($disp === '' ? null : $disp),
    'email' => $email
  ]
]);
