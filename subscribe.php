<?php
session_start();

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
    die('DB connection failed: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['newsletter_status'] = 'invalid';
        header('Location: index.php#newsletter');
        exit;
    }

    // حفظ الإيميل في جدول المشتركين
    $stmt = $conn->prepare('INSERT INTO newsletter_subscribers (email, source, status) VALUES (?, ?, ?)');
    if ($stmt) {
        $source = 'landing';
        $status = 'active';
        $stmt->bind_param('sss', $email, $source, $status);
        $stmt->execute();
        $stmt->close();
    }

    // إرسال إيميل ترحيبي
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'traveloa9@gmail.com';
        $mail->Password   = 'ojxwfckrsuqnfaub';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('traveloa9@gmail.com', 'Travelo');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Travelo Newsletter';
        $mail->Body    = '
            <h2>Hi, traveler! ✈️</h2>
            <p>Thank you for subscribing to <strong>Travelo</strong> newsletter.</p>
            <p>You will start receiving our best offers and travel inspirations soon.</p>
            <p style="margin-top:16px;">Love,<br>Travelo Team</p>
        ';
        $mail->AltBody = 'Thank you for subscribing to Travelo newsletter.';

        $mail->send();
        $_SESSION['newsletter_status'] = 'ok';
    } catch (Exception $e) {
        $_SESSION['newsletter_status'] = 'mail_error';
        // لو حابة تشوفي الخطأ فعليًا، تقدري تفتحي هالسطر:
        // error_log('Mailer Error: ' . $mail->ErrorInfo);
    }

    $conn->close();
    header('Location: index.php#newsletter');
    exit;
} else {
    header('Location: index.php');
    exit;
}
