<?php
header('Content-Type: application/json');
include 'db.php';

$email = trim($_POST['emailfp'] ?? '');
$code = trim($_POST['code'] ?? '');
$newPassword = trim($_POST['newpass'] ?? '');

if (!$email || !$code || !$newPassword) {
    echo json_encode([
        "status" => "error",
        "message" => "Email, code, and new password are required"
    ]);
    exit;
}

// fetch user
$sql = "SELECT id, reset_token_hash, reset_token_expires_at FROM users 
        WHERE email = ? OR username = ? LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email, $email]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode([
        "status" => "error",
        "message" => "No account found with that email/username"
    ]);
    exit;
}

// check if token expired
if (new DateTime() > new DateTime($user['reset_token_expires_at'])) {
    echo json_encode([
        "status" => "error",
        "message" => "The verification code has expired. Please request a new one."
    ]);
    exit;
}

// verify code
if (!password_verify($code, $user['reset_token_hash'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Incorrect verification code"
    ]);
    exit;
}

// hash new password
$newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

// update user password and clear reset token
$sql = "UPDATE users SET password_hash = ?, reset_token_hash = NULL, reset_token_expires_at = NULL 
        WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$newPasswordHash, $user['id']]);

echo json_encode([
    "status" => "success",
    "message" => "Password updated successfully"
]);
exit;
?>
