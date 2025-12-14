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

$first = trim($input['first_name'] ?? '');
$last  = trim($input['last_name'] ?? '');
$usern = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$birth = trim($input['birth_date'] ?? '');

if ($usern === '' || $email === '') {
  http_response_code(422);
  echo json_encode(['success'=>false,'message'=>'Username and email are required']);
  exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo json_encode(['success'=>false,'message'=>'Invalid email']);
  exit;
}
if ($birth !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth)) {
  http_response_code(422);
  echo json_encode(['success'=>false,'message'=>'Invalid birth date']);
  exit;
}

$uid = (int)$_SESSION['user_id'];

/* unique username/email */
$chk = $pdo->prepare("SELECT id FROM users WHERE (username=? OR email=?) AND id<>? LIMIT 1");
$chk->execute([$usern, $email, $uid]);
if ($chk->fetch()) {
  http_response_code(409);
  echo json_encode(['success'=>false,'message'=>'Username or email already exists']);
  exit;
}

/* update */
$up = $pdo->prepare("UPDATE users
                     SET first_name=?, last_name=?, username=?, email=?, birth_date=?
                     WHERE id=?");
$up->execute([
  $first !== '' ? $first : null,
  $last  !== '' ? $last  : null,
  $usern,
  $email,
  $birth !== '' ? $birth : null,
  $uid
]);

/* refresh user */
$stmt = $pdo->prepare("SELECT first_name,last_name,username,email,birth_date,created_at FROM users WHERE id=?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

$_SESSION['user_name']  = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['username'] ?? 'Traveler');
$_SESSION['user_email'] = $user['email'] ?? '';

echo json_encode(['success'=>true,'user'=>$user]);
