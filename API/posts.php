<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../db.php';

function is_admin(): bool {
  return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')
      && isset($_SESSION['admin_id']);
}


function out($ok, $arr = [], $code = 200){
  http_response_code($code);
  echo json_encode(array_merge(['success'=>$ok], $arr), JSON_UNESCAPED_UNICODE);
  exit;
}
function intv($v, $d=0){ return (isset($v) && is_numeric($v)) ? (int)$v : $d; }
function strv($v, $d=''){ return isset($v) ? trim((string)$v) : $d; }

if (!is_admin()) out(false, ['message'=>'Forbidden'], 403);

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

$read_body = function(): array {
  $raw = file_get_contents('php://input');
  $j = json_decode($raw, true);
  if (is_array($j)) return $j;
  return is_array($_POST) ? $_POST : [];
};

try {

  // GET: list
  if ($action === 'list') {
    $status = strv($_GET['status'] ?? 'pending'); // pending|approved|rejected|all|''
    $q      = strv($_GET['q'] ?? '');
    $limit  = max(1, min(200, intv($_GET['limit'] ?? 60, 60)));
    $offset = max(0, intv($_GET['offset'] ?? 0, 0));

    $where = [];
    $params = [];

    if ($status !== '' && $status !== 'all') {
      $where[] = "status = ?";
      $params[] = $status;
    }

    if ($q !== '') {
      $where[] = "(name LIKE ? OR title LIKE ? OR message LIKE ?)";
      $like = "%$q%";
      $params[] = $like; $params[] = $like; $params[] = $like;
    }

    $wsql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

    $sql = "SELECT id,user_id,name,title,message,rating,avatar_url,status,reviewed_at,created_at
            FROM testimonials
            $wsql
            ORDER BY created_at DESC
            LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    out(true, ['rows'=>$stmt->fetchAll()]);
  }

  // GET: details
  if ($action === 'get') {
    $id = intv($_GET['id'] ?? 0, 0);
    if ($id <= 0) out(false, ['message'=>'Invalid id'], 400);

    $stmt = $pdo->prepare("SELECT id,user_id,name,title,message,rating,avatar_url,status,reviewed_at,created_at
                           FROM testimonials WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) out(false, ['message'=>'Not found'], 404);

    out(true, ['row'=>$row]);
  }

  // باقي العمليات POST
  if ($method !== 'POST') out(false, ['message'=>'Method not allowed'], 405);

  $body = $read_body();
  $id = intv($body['id'] ?? 0, 0);
  if ($id <= 0) out(false, ['message'=>'Invalid id'], 400);

  if ($action === 'approve') {
    $stmt = $pdo->prepare("UPDATE testimonials SET status='approved', reviewed_at=NOW() WHERE id=?");
    $stmt->execute([$id]);
    out(true, ['message'=>'Approved']);
  }

  // Reject = DELETE (حسب طلبك)
  if ($action === 'reject') {
    $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id=?");
    $stmt->execute([$id]);
    out(true, ['message'=>'Deleted']);
  }

  out(false, ['message'=>'Unknown action'], 400);

} catch (Throwable $e) {
  out(false, ['message'=>'Server error'], 500);
}
