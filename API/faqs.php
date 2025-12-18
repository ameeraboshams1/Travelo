<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../db.php'; // API/ -> root/db.php

// ✅ عدّلي شرط الأدمن حسب سيشناتك
$isAdmin = isset($_SESSION['admin_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

$ALLOWED_CATEGORIES = [
  'All',
  'Booking',
  'Payments',
  'Flights',
  'Hotels',
  'Changes & Refunds',
  'Travel Policies',
  'Support & Account'
];

function json_ok($data = []) { echo json_encode(['success' => true] + $data); exit; }
function json_err($msg, $code = 400) { http_response_code($code); echo json_encode(['success'=>false,'message'=>$msg]); exit; }

function read_payload() {
  $raw = file_get_contents('php://input');
  $ct  = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
  if ($raw && stripos($ct, 'application/json') !== false) {
    $j = json_decode($raw, true);
    return is_array($j) ? $j : [];
  }
  return $_POST ?: [];
}
function s($v, $fb=''){ return trim($v===null ? $fb : (string)$v); }
function i01($v){ return (int)!!(is_string($v) ? ($v==='1' || strtolower($v)==='true') : $v); }
function clamp_int($v, $min, $max, $fb){ $n = (int)$v; if ($n < $min) return $fb; if ($n > $max) return $max; return $n; }

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');
$payload = read_payload();

try {
  // =========================
  // LIST (public + admin)
  // =========================
  if ($action === 'list') {
    $category   = s($_GET['category'] ?? 'All');
    $q          = s($_GET['q'] ?? '');
    $onlyActive = isset($_GET['onlyActive']) ? i01($_GET['onlyActive']) : null; // admin can override
    $onlyPopular= isset($_GET['onlyPopular']) ? i01($_GET['onlyPopular']) : 0;

    $limit  = clamp_int($_GET['limit'] ?? 200, 1, 500, 200);
    $offset = max(0, (int)($_GET['offset'] ?? 0));

    $where = [];
    $params = [];

    // ✅ public: اجباري فعال فقط
    if (!$GLOBALS['isAdmin']) {
      $where[] = "is_active = 1";
    } else {
      if ($onlyActive !== null) {
        $where[] = "is_active = ?";
        $params[] = $onlyActive;
      }
    }

    // category filter (All = no filter)
    if ($category !== '' && $category !== 'All') {
      $where[] = "category = ?";
      $params[] = $category;
    }

    // search
    if ($q !== '') {
      $where[] = "(question LIKE ? OR answer LIKE ? OR tags LIKE ?)";
      $like = "%$q%";
      array_push($params, $like, $like, $like);
    }

    if ($onlyPopular) {
      $where[] = "is_popular = 1";
    }

    $sql = "SELECT id, category, question, answer, tags, is_popular, sort_order, is_active, created_at, updated_at
            FROM faqs
            " . (count($where) ? "WHERE " . implode(" AND ", $where) : "") . "
            ORDER BY sort_order ASC, id DESC
            LIMIT $limit OFFSET $offset";

    $st = $pdo->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    json_ok(['rows' => $rows, 'isAdmin' => $GLOBALS['isAdmin']]);
  }

  // =========================
  // GET ONE (admin only)
  // =========================
  if ($action === 'get') {
    if (!$isAdmin) json_err('Forbidden', 403);

    $id = (int)($_GET['id'] ?? 0);
    if (!$id) json_err('Missing id');

    $st = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $st->execute([$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) json_err('Not found', 404);

    json_ok(['row' => $row]);
  }

  // =========================
  // CREATE (admin only)
  // =========================
  if ($action === 'create') {
    if (!$isAdmin) json_err('Forbidden', 403);

    $category = s($payload['category'] ?? 'All');
    $question = s($payload['question'] ?? '');
    $answer   = s($payload['answer'] ?? '');
    $tags     = s($payload['tags'] ?? '');
    $is_popular = i01($payload['is_popular'] ?? 0);
    $sort_order = (int)($payload['sort_order'] ?? 0);
    $is_active  = i01($payload['is_active'] ?? 1);

    if ($question === '' || $answer === '') json_err('Question and answer are required');
    if (!in_array($category, $ALLOWED_CATEGORIES, true)) json_err('Invalid category');

    $st = $pdo->prepare("INSERT INTO faqs (category, question, answer, tags, is_popular, sort_order, is_active)
                         VALUES (?,?,?,?,?,?,?)");
    $st->execute([$category, $question, $answer, ($tags!==''?$tags:null), $is_popular, $sort_order, $is_active]);

    json_ok(['id' => (int)$pdo->lastInsertId()]);
  }

  // =========================
  // UPDATE (admin only)
  // =========================
  if ($action === 'update') {
    if (!$isAdmin) json_err('Forbidden', 403);

    $id = (int)($payload['id'] ?? 0);
    if (!$id) json_err('Missing id');

    $category = s($payload['category'] ?? '');
    $question = s($payload['question'] ?? '');
    $answer   = s($payload['answer'] ?? '');
    $tags     = s($payload['tags'] ?? '');
    $is_popular = isset($payload['is_popular']) ? i01($payload['is_popular']) : null;
    $sort_order = isset($payload['sort_order']) ? (int)$payload['sort_order'] : null;
    $is_active  = isset($payload['is_active']) ? i01($payload['is_active']) : null;

    $set = [];
    $params = [];

    if ($category !== '') {
      if (!in_array($category, $ALLOWED_CATEGORIES, true)) json_err('Invalid category');
      $set[] = "category = ?";
      $params[] = $category;
    }
    if ($question !== '') { $set[] = "question = ?"; $params[] = $question; }
    if ($answer !== '')   { $set[] = "answer = ?";   $params[] = $answer; }
    if (isset($payload['tags'])) { $set[] = "tags = ?"; $params[] = ($tags!==''?$tags:null); }

    if ($is_popular !== null) { $set[] = "is_popular = ?"; $params[] = $is_popular; }
    if ($sort_order !== null) { $set[] = "sort_order = ?"; $params[] = $sort_order; }
    if ($is_active  !== null) { $set[] = "is_active = ?";  $params[] = $is_active; }

    if (!count($set)) json_err('Nothing to update');

    $params[] = $id;
    $sql = "UPDATE faqs SET " . implode(", ", $set) . " WHERE id = ?";
    $st = $pdo->prepare($sql);
    $st->execute($params);

    json_ok();
  }

  // =========================
  // DELETE (admin only)
  // =========================
  if ($action === 'delete') {
    if (!$isAdmin) json_err('Forbidden', 403);

    $id = (int)($payload['id'] ?? 0);
    if (!$id) json_err('Missing id');

    $st = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
    $st->execute([$id]);

    json_ok();
  }

  json_err('Unknown action', 400);

} catch (Throwable $e) {
  json_err($e->getMessage(), 500);
}
