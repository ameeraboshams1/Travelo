<?php
session_start();
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . "/db.php"; // لازم يكون فيه $pdo

function jsonOut($arr, int $code = 200){
  http_response_code($code);
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

/* =======================
   Password checker (hash أو نص عادي)
   ======================= */
function checkPassword(string $plain, ?string $stored): bool
{
  $stored = (string)$stored;

  // Hash غالباً (طويل)
  if ($stored !== '' && strlen($stored) > 20) {
    return password_verify($plain, $stored);
  }

  // Plain (مشروع جامعة)
  return hash_equals($stored, $plain);
}

$login    = isset($_POST['login']) ? trim($_POST['login']) : '';
$password = $_POST['password'] ?? '';

if ($login === '' || $password === '') {
  jsonOut([
    "status"  => "error",
    "message" => "Please enter your email/username and password."
  ], 400);
}

/* =======================
   1) Check ADMIN first
   ======================= */
try {
  $sqlAdmin = "SELECT id, first_name, last_name, display_name, email, password_hash, is_active, is_super
              FROM admins
              WHERE email = ?
              LIMIT 1";
  $stmt = $pdo->prepare($sqlAdmin);
  $stmt->execute([$login]);
  $admin = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($admin) {
    // لو عندك is_active للأدمن
    if (isset($admin['is_active']) && (int)$admin['is_active'] === 0) {
      jsonOut([
        "status"  => "error",
        "message" => "This admin account is inactive."
      ], 403);
    }

    if (!checkPassword($password, $admin['password_hash'] ?? '')) {
      jsonOut([
        "status"  => "error",
        "message" => "Invalid password for admin account."
      ], 401);
    }

    // ✅ sessions للأدمن
    $shownName = trim((string)($admin['display_name'] ?? ''));
    if ($shownName === '') {
      $shownName = trim((string)($admin['first_name'] ?? '') . ' ' . (string)($admin['last_name'] ?? ''));
      if ($shownName === '') $shownName = 'Admin';
    }

    $_SESSION['role']      = 'admin';
    $_SESSION['admin_id']  = (int)$admin['id'];
    $_SESSION['user_id']   = (int)$admin['id']; // إذا كودك يعتمد على user_id
    $_SESSION['user_name'] = $shownName;
    $_SESSION['user_email']= (string)($admin['email'] ?? '');

    jsonOut([
      "status"   => "success",
      "role"     => "admin",
      "redirect" => "./dashboard.php"
    ]);
  }

  /* =======================
     2) Check USER
     ======================= */
  $sqlUser = "SELECT id, first_name, username, email, password_hash, is_active
             FROM users
             WHERE (username = ? OR email = ?)
             AND is_active = 1
             LIMIT 1";
  $stmtU = $pdo->prepare($sqlUser);
  $stmtU->execute([$login, $login]);
  $user = $stmtU->fetch(PDO::FETCH_ASSOC);

  if ($user) {
    if (!checkPassword($password, $user['password_hash'] ?? '')) {
      jsonOut([
        "status"  => "error",
        "message" => "Invalid password."
      ], 401);
    }

    $_SESSION['role']      = 'user';
    $_SESSION['user_id']   = (int)$user['id'];
    $_SESSION['user_name'] = (string)($user['first_name'] ?? 'Traveler');
    $_SESSION['user_email']= (string)($user['email'] ?? '');

    jsonOut([
      "status"   => "success",
      "role"     => "user",
      "redirect" => "./index.php"  // أو ./profile.php إذا بدك
    ]);
  }

  /* =======================
     3) Not found
     ======================= */
  jsonOut([
    "status"  => "error",
    "message" => "Invalid username/email or password."
  ], 401);

} catch (Throwable $e) {
  jsonOut([
    "status"  => "error",
    "message" => "Server error."
  ], 500);
}
