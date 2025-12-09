<?php
include 'db.php';

function send_error($msg) {
    echo json_encode(["status" => "error", "message" => $msg]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_error("Invalid request");
}

// Get form data
$first    = trim($_POST['first_name']);
$last     = trim($_POST['last_name']);
$email    = trim($_POST['email']);
$username = trim($_POST['username']);
$birth    = $_POST['birth_date'];
$pass     = $_POST['password']; 
$confirm  = $_POST['confirm_password'];

// PHP validation
if (!ctype_alpha($first)) send_error("First name must contain only letters.");
if (!ctype_alpha($last))  send_error("Last name must contain only letters.");

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_error("Invalid email address.");
}

if ($pass !== $confirm) {
    send_error("Passwords do not match.");
}

if (strlen($pass) < 6) {
    send_error("Password must be at least 6 characters.");
}

// Check if username exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->fetch()) {
    send_error("Username already taken.");
}

// Hash password
$hash = password_hash($pass, PASSWORD_DEFAULT);

// Insert new user
$stmt = $pdo->prepare("
    INSERT INTO users (first_name, last_name, username, email, birth_date, password_hash, is_active, created_at)
    VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
");

$success = $stmt->execute([$first, $last, $username, $email, $birth, $hash]);

if ($success) {
    echo "<script>
        alert('Account created successfully!');
        window.location.href = 'packages.html';
    </script>";
    exit;
} else {
    send_error('Database error while inserting user.');
}

?>
