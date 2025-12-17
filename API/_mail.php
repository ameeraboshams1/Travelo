<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function mail_config(): array {
  return [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'traveloa9@gmail.com',
    'password' => 'nvnr dkfz clsh xbqa', // <-- App Password
    'from_email' => 'traveloa9@gmail.com',
    'from_name' => 'Travelo Notifications',
  ];
}

function send_email(string $to, string $subject, string $html, string $text = ''): array {
  $cfg = mail_config();

  $mail = new PHPMailer(true);
  try {
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = $cfg['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $cfg['username'];
    $mail->Password = $cfg['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int)$cfg['port'];

    $mail->setFrom($cfg['from_email'], $cfg['from_name']);
    $mail->addAddress($to);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $html;
    $mail->AltBody = $text ?: strip_tags($html);

    $mail->send();
    return ['ok' => true, 'error' => null];
  } catch (Exception $e) {
    return ['ok' => false, 'error' => $mail->ErrorInfo ?: $e->getMessage()];
  }
}
