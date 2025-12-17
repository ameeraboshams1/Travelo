<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

date_default_timezone_set('Asia/Hebron');

header('Content-Type: application/json; charset=utf-8');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}


require __DIR__ . '/../db.php';           // لازم يعرّف $pdo
require __DIR__ . '/../vendor/autoload.php';

function json_ok(array $extra = []): void {
  echo json_encode(['success' => true] + $extra);
  exit;
}

function json_fail(string $message, int $code = 400, array $extra = []): void {
  http_response_code($code);
  echo json_encode(['success' => false, 'message' => $message] + $extra);
  exit;
}

function read_json(): array {
  $raw = file_get_contents('php://input');
  if (!$raw) return [];
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

function require_admin(): void {
  $isAdmin = isset($_SESSION['admin_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
  if (!$isAdmin) json_fail('Forbidden', 403);
}

function db(): PDO {
  global $pdo;
  if (!($pdo instanceof PDO)) json_fail('DB not ready', 500);
  return $pdo;
}

function clean_email(string $email): ?string {
  $email = trim($email);
  return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}
