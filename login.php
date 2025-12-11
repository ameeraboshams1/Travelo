<?php
header("Content-Type: application/json");
include 'db.php';

// Get login input
$login = trim($_POST['login']);
$password = $_POST['password'];

// First, check admins table
$sqlAdmin = "SELECT * FROM admins WHERE email = ?";
$stmtAdmin = $pdo->prepare($sqlAdmin);
$stmtAdmin->execute([$login]);

if ($stmtAdmin->rowCount() == 1) {
    $admin = $stmtAdmin->fetch();
    if ($password === $admin['password_hash']) {
        echo json_encode([
            "status" => "success",
            "role" => "admin"
        ]);
        exit();
    }
}

// If not admin, check users table
$sqlUser = "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1";
$stmtUser = $pdo->prepare($sqlUser);
$stmtUser->execute([$login, $login]);

if ($stmtUser->rowCount() == 1) {
    $user = $stmtUser->fetch();
    if (password_verify($password, $user['password_hash'])) {
        echo json_encode([
            "status" => "success",
            "role" => "user"
        ]);
        exit();
    }
}

// If no match
echo json_encode([
    "status" => "error",
    "message" => "Invalid username/email or password."
]);
exit();
?>


