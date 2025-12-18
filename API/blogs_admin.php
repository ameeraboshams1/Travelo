<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0');
ini_set('log_errors', '1');

function respond(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}
function ok(array $payload = []): void {
  respond(200, array_merge(['success' => true], $payload));
}
function fail(int $code, string $message, array $extra = []): void {
  respond($code, array_merge(['success' => false, 'message' => $message], $extra));
}
function readJsonBody(): array {
  $raw = file_get_contents('php://input');
  if (!$raw) return [];
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

/* ================== ADMIN GUARD ================== */
$isAdmin =
  !empty($_SESSION['admin_id']) ||
  (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') ||
  (!empty($_SESSION['is_admin']));

if (!$isAdmin) {
  fail(403, 'Forbidden');
}

/* ================== DB (PDO) ================== */
$host     = 'localhost';
$dbname   = 'travelo';
$username = 'root';
$password = '';

try {
  $pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $username,
    $password,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );
} catch (Throwable $e) {
  fail(500, 'DB connection failed');
}

/* ================== ROUTER ================== */
$action = $_GET['action'] ?? 'list';

try {

  /* ===== list ===== */
  if ($action === 'list') {
    $status = trim((string)($_GET['status'] ?? '')); // published | draft | ''(all)
    $q      = trim((string)($_GET['q'] ?? ''));

    $limit  = (int)($_GET['limit'] ?? 200);
    $offset = (int)($_GET['offset'] ?? 0);
    if ($limit < 1) $limit = 200;
    if ($limit > 500) $limit = 500;
    if ($offset < 0) $offset = 0;

    $where = [];
    $bind  = [];

    if ($status !== '') {
      // إذا عمود status مش موجود عندك، خليه يعاملها published بشكل افتراضي
      $where[] = "COALESCE(bp.status,'published') = :status";
      $bind[':status'] = $status;
    }

    if ($q !== '') {
      $where[] = "(bp.title LIKE :q OR bp.excerpt LIKE :q OR bp.category LIKE :q OR bp.slug LIKE :q)";
      $bind[':q'] = '%' . $q . '%';
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $sql = "
      SELECT
        bp.id,
        bp.title,
        bp.slug,
        bp.excerpt,
        bp.category,
        bp.cover_image,
        bp.views,
        bp.created_at,
        bp.updated_at,
        COALESCE(bp.status,'published') AS status,
        bp.author_id,
        u.username AS author_username,
        CONCAT_WS(' ', u.first_name, u.last_name) AS author_name
      FROM blog_posts bp
      LEFT JOIN users u ON u.id = bp.author_id
      $whereSql
      ORDER BY bp.created_at DESC
      LIMIT $limit OFFSET $offset
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($bind as $k => $v) $stmt->bindValue($k, $v);
    $stmt->execute();

    ok(['rows' => $stmt->fetchAll()]);
  }

  /* ===== get ===== */
  if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) fail(400, 'Missing id');

    $stmt = $pdo->prepare("
      SELECT
        bp.*,
        COALESCE(bp.status,'published') AS status,
        u.username AS author_username,
        u.email AS author_email,
        CONCAT_WS(' ', u.first_name, u.last_name) AS author_name
      FROM blog_posts bp
      LEFT JOIN users u ON u.id = bp.author_id
      WHERE bp.id = :id
      LIMIT 1
    ");
    $stmt->execute([':id' => $id]);

    $row = $stmt->fetch();
    if (!$row) fail(404, 'Post not found');

    ok(['row' => $row]);
  }

  /* ===== delete ===== */
  if ($action === 'delete') {
    $body = readJsonBody();
    $id = (int)($body['id'] ?? ($_GET['id'] ?? 0));
    if ($id <= 0) fail(400, 'Missing id');

    // تحقق موجود؟
    $chk = $pdo->prepare("SELECT id FROM blog_posts WHERE id = :id LIMIT 1");
    $chk->execute([':id' => $id]);
    if (!$chk->fetch()) fail(404, 'Post not found');

    $del = $pdo->prepare("DELETE FROM blog_posts WHERE id = :id");
    $del->execute([':id' => $id]);

    ok(['id' => $id]);
  }

  fail(400, 'Unknown action');

} catch (Throwable $e) {
  fail(500, 'Server error', ['debug' => $e->getMessage()]);
}
