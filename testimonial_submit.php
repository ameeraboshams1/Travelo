<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';

function out($ok, $arr = [], $code = 200){
  http_response_code($code);
  echo json_encode(array_merge(['success'=>$ok], $arr), JSON_UNESCAPED_UNICODE);
  exit;
}

if (!isset($_SESSION['user_id'])) {
  out(false, ['message'=>'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  out(false, ['message'=>'Method not allowed'], 405);
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) $body = $_POST;

$name    = trim((string)($body['name'] ?? ''));
$title   = trim((string)($body['title'] ?? ''));
$message = trim((string)($body['message'] ?? ''));
$rating  = (int)($body['rating'] ?? 5);

if ($rating < 1) $rating = 1;
if ($rating > 5) $rating = 5;

// نفس فرونتك: max 300
if (mb_strlen($message) > 300) {
  $message = mb_substr($message, 0, 300);
}

if ($name === '' || $message === '') {
  out(false, ['message'=>'Name and message are required'], 400);
}

$userId = (int)$_SESSION['user_id'];

try {
  // (اختياري أمان) تأكد اليوزر active
  $st = $pdo->prepare("SELECT is_active FROM users WHERE id=?");
  $st->execute([$userId]);
  $u = $st->fetch();
  if (!$u || (int)$u['is_active'] !== 1) {
    out(false, ['message'=>'User not active'], 403);
  }

  $stmt = $pdo->prepare("
    INSERT INTO testimonials (user_id, name, title, message, rating, avatar_url, status, reviewed_at, created_at)
    VALUES (?, ?, ?, ?, ?, NULL, 'pending', NULL, CURRENT_TIMESTAMP)
  ");
  $stmt->execute([$userId, $name, ($title !== '' ? $title : null), $message, $rating]);

  out(true, ['message'=>'Submitted', 'id'=>(int)$pdo->lastInsertId()]);
} catch (Throwable $e) {
  out(false, ['message'=>'Server error'], 500);
}
