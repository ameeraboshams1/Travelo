<?php
header('Content-Type: application/json');

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'travelo';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
  echo json_encode(['status' => 'db_error']);
  exit;
}

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['status' => 'invalid']);
  exit;
}

$stmt = $conn->prepare('SELECT id FROM newsletter_subscribers WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  echo json_encode(['status' => 'exists']);
  exit;
}
$stmt->close();

$stmt = $conn->prepare(
  'INSERT INTO newsletter_subscribers (email, source, status) VALUES (?, ?, ?)'
);
$source = 'landing';
$status = 'active';
$stmt->bind_param('sss', $email, $source, $status);
$stmt->execute();
$stmt->close();

echo json_encode(['status' => 'ok']);
$conn->close();
exit;           
?>