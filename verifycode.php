<?php
header('Content-Type: application/json');
include 'db.php';

$email = trim($_POST['emailfp'] ?? '');
$code = trim($_POST['code'] ?? '');

if (!$email || !$code) {
    echo json_encode([
        "status" => "error",
        "message" => "Email and code are required"
    ]);
    exit;
}

// fetch user by email/username
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

// success, allow next step
echo json_encode([
    "status" => "success",
    "message" => "Code verified successfully"
]);
exit;
?>
