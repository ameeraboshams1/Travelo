<?php
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';

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

// 1) check exists
$stmt = $conn->prepare('SELECT id FROM newsletter_subscribers WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  echo json_encode(['status' => 'exists']);
  exit;
}
$stmt->close();

// 2) insert
$stmt = $conn->prepare('INSERT INTO newsletter_subscribers (email, source, status) VALUES (?, ?, ?)');
$source = 'landing';
$status = 'active';
$stmt->bind_param('sss', $email, $source, $status);

if (!$stmt->execute()) {
  // لو عندك UNIQUE على email و صار سباق
  if ($conn->errno === 1062) {
    echo json_encode(['status' => 'exists']);
  } else {
    echo json_encode(['status' => 'db_error']);
  }
  exit;
}
$stmt->close();

// 3) send welcome email
$mail = new PHPMailer(true);

try {
  $mail->isSMTP();
  $mail->Host       = 'smtp.gmail.com';
  $mail->SMTPAuth   = true;

  $mail->Username   = 'traveloa9@gmail.com';
  $mail->Password   = 'ojxwfckrsuqnfaub'; // <-- App Password

  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port       = 587;

  $mail->CharSet = 'UTF-8';

  $mail->setFrom('traveloa9@gmail.com', 'Travelo');
  $mail->addAddress($email);

  $mail->isHTML(true);
  $mail->Subject = 'Welcome to Travelo Newsletter';
  $mail->Body    = "
    <h2>Hi, traveler! ✈️</h2>
    <p>Thank you for subscribing to <strong>Travelo</strong> newsletter.</p>
    <p>You will start receiving our best offers and travel inspirations soon.</p>
    <p style='margin-top:16px;'>Love,<br>Travelo Team</p>
  ";
  $mail->AltBody = 'Thank you for subscribing to Travelo newsletter.';

  $mail->send();

  echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
  // الإشتراك تم، بس الإيميل فشل
  echo json_encode(['status' => 'mail_error', 'error' => $mail->ErrorInfo]);
}

$conn->close();
exit;
