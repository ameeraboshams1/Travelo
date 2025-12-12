<?php
session_start();
header("Content-Type: application/json; charset=utf-8");
include 'db.php'; // نتوقع هنا إنو $pdo جاهز ومتصل بقاعدة البيانات

// =======================
// دالة مساعدة لفحص الباسورد
// =======================
function checkPassword(string $plain, string $stored): bool
{
    // لو شكله hash (طويل) جرّبي password_verify
    if (!empty($stored) && strlen($stored) > 20) {
        if (password_verify($plain, $stored)) {
            return true;
        }
    }

    // غير هيك نفترضه نص عادي (مشروع جامعة)
    return $plain === $stored;
}

// =======================
// قراءة بيانات الفورم
// =======================
$login    = isset($_POST['login']) ? trim($_POST['login']) : '';
$password = $_POST['password'] ?? '';

if ($login === '' || $password === '') {
    echo json_encode([
        "status"  => "error",
        "message" => "Please enter your email/username and password."
    ]);
    exit();
}

// =======================
// 1) نتحقق أولاً من admins
// =======================
$sqlAdmin = "SELECT * FROM admins WHERE email = ? LIMIT 1";
$stmtAdmin = $pdo->prepare($sqlAdmin);
$stmtAdmin->execute([$login]);
$admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    $stored = $admin['password_hash'];

    if (checkPassword($password, $stored)) {
        // نخزن السيشن للأدمن
        $_SESSION['user_id']   = $admin['id'];
        $_SESSION['role']      = 'admin';
        $_SESSION['user_name'] = $admin['display_name']
            ? $admin['display_name']
            : ($admin['first_name'] . ' ' . $admin['last_name']);

        echo json_encode([
            "status" => "success",
            "role"   => "admin"
        ]);
        exit();
    }

    // الإيميل تبع أدمِن لكن الباسورد غلط
    echo json_encode([
        "status"  => "error",
        "message" => "Invalid password for admin account."
    ]);
    exit();
}

// =======================
// 2) لو مش أدمِن، نتحقق من users
// =======================
$sqlUser = "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1";
$stmtUser = $pdo->prepare($sqlUser);
$stmtUser->execute([$login, $login]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $stored = $user['password_hash'];

    if (checkPassword($password, $stored)) {
        // نخزن السيشن لليوزر
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['role']      = 'user';
        $_SESSION['user_name'] = $user['first_name'];

        echo json_encode([
            "status" => "success",
            "role"   => "user"
        ]);
        exit();
    } else {
        echo json_encode([
            "status"  => "error",
            "message" => "Invalid password."
        ]);
        exit();
    }
}

// =======================
// 3) لا أدمِن ولا يوزر
// =======================
echo json_encode([
    "status"  => "error",
    "message" => "Invalid username/email or password."
]);
exit();
