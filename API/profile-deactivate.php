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

$uid = (int)$_SESSION['user_id'];

$pdo->prepare("UPDATE users SET is_active=0 WHERE id=?")->execute([$uid]);

session_destroy();
echo json_encode(['success'=>true,'redirect'=>'login.html']);
