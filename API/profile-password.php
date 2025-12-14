<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['success'=>false,'message'=>'Not logged in']);
  exit;
}

/* DB */
$host='localhost'; $dbname='travelo'; $username='root'; $password='';
try{
  $pdo=new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4",$username,$password,[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
  ]);
}catch(Exception $e){
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'DB error']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$old = (string)($input['old'] ?? '');
$new = (string)($input['new'] ?? '');
$confirm = (string)($input['confirm'] ?? '');

if ($new !== $confirm) {
  http_response_code(422);
  echo json_encode(['success'=>false,'message'=>'Passwords do not match']);
  exit;
}
if (strlen($new) < 8) {
  http_response_code(422);
  echo json_encode(['success'=>false,'message'=>'Password must be at least 8 characters']);
  exit;
}

$uid = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id=?");
$stmt->execute([$uid]);
$row = $stmt->fetch();

if (!$row || empty($row['password_hash']) || !password_verify($old, $row['password_hash'])) {
  http_response_code(403);
  echo json_encode(['success'=>false,'message'=>'Current password is incorrect']);
  exit;
}

$hash = password_hash($new, PASSWORD_DEFAULT);
$up = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
$up->execute([$hash, $uid]);

echo json_encode(['success'=>true,'message'=>'Password updated']);
