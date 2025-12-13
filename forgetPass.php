<?php
// Show errors for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Return JSON
header('Content-Type: application/json');

include 'db.php'; // your PDO connection

// Composer autoload only (no need for multiple require_once)
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get email from POST
$email = trim($_POST['emailfp'] ?? '');

if (empty($email)) {
    echo json_encode([
        "status" => "error",
        "message" => "Please enter your email/username"
    ]);
    exit;
}

// Generate 6-digit code
$code = random_int(100000, 999999);
$token_hash = password_hash($code, PASSWORD_DEFAULT);
$expiry = date("Y-m-d H:i:s", time() + 300); // 5 minutes

// Update user in DB
$sql = "UPDATE users 
        SET reset_token_hash = ?, reset_token_expires_at = ?
        WHERE email = ? OR username = ?
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$token_hash, $expiry, $email, $email]);

if ($stmt->rowCount() === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No account found with that email/username"
    ]);
    exit;
}

// Send email
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'traveloa9@gmail.com';      // your Gmail
    $mail->Password   = 'eypm apib kbci ndww';         // Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Gmail requires FROM to match the account
    $mail->setFrom('traveloa9@gmail.com', 'Travelo Support');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your Travelo Password Reset Code';
    $mail->Body    = "Your verification code is: <b>$code</b><br>It expires in 5 minutes.";

    $mail->send();
 
    echo json_encode([
        "status" => "success",
        "message" => "Verification code sent to your email"
    ]);
} catch (Exception $e) {
    // Return error in JSON for AJAX
    echo json_encode([
        "status" => "error",
        "message" => "Mailer Error: " . $mail->ErrorInfo
    ]);
}
exit;
?>