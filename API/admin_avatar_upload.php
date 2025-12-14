<?php
require __DIR__ . '/admin_guard.php';

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
  json_out(false, 'No file uploaded', [], 400);
}

$f = $_FILES['avatar'];
if ($f['size'] > 2 * 1024 * 1024) json_out(false, 'Max size is 2MB', [], 422);

$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','webp'];
if (!in_array($ext, $allowed, true)) json_out(false, 'Invalid file type', [], 422);

$uploadDir = __DIR__ . '/../assets/uploads/admins';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

$filename = 'admin_' . $adminId . '_' . time() . '.' . $ext;
$destAbs  = $uploadDir . '/' . $filename;

if (!move_uploaded_file($f['tmp_name'], $destAbs)) {
  json_out(false, 'Upload failed', [], 500);
}

$destRel = './assets/uploads/admins/' . $filename;

$upd = $pdo->prepare("UPDATE admins SET avatar_url=? WHERE id=?");
$upd->execute([$destRel, $adminId]);

json_out(true, 'Avatar updated', ['avatar_url' => $destRel]);
