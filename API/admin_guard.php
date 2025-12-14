<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

function json_out(bool $success, string $message = '', array $extra = [], int $code = 200){
  http_response_code($code);
  echo json_encode(array_merge([
    'success' => $success,
    'message' => $message
  ], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

$role = $_SESSION['role'] ?? '';
$adminId = (int)($_SESSION['admin_id'] ?? 0);
if ($adminId <= 0) $adminId = (int)($_SESSION['user_id'] ?? 0);

if ($adminId <= 0 || $role !== 'admin') {
  json_out(false, 'Unauthorized', [], 401);
}
