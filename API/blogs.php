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

function slugify(string $text): string {
  $text = trim(mb_strtolower($text));
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);
  $text = preg_replace('~[^-\w]+~', '', $text);
  $text = trim($text, '-');
  $text = preg_replace('~-+~', '-', $text);
  return $text ?: 'post';
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

  /* ===== session ===== */
  if ($action === 'session') {
    $logged = !empty($_SESSION['user_id']);
    ok(['logged_in' => $logged, 'user_id' => $logged ? (int)$_SESSION['user_id'] : null]);
  }

  /* ===== meta (categories + authors) ===== */
  if ($action === 'meta') {
    $cats = $pdo->query("
      SELECT DISTINCT category
      FROM blog_posts
      WHERE category IS NOT NULL AND category <> ''
      ORDER BY category
    ")->fetchAll();

    $authors = $pdo->query("
      SELECT u.id,
             CONCAT_WS(' ', u.first_name, u.last_name) AS name,
             u.username
      FROM users u
      JOIN blog_posts bp ON bp.author_id = u.id
      GROUP BY u.id, u.first_name, u.last_name, u.username
      ORDER BY name
    ")->fetchAll();

    ok(['categories' => $cats, 'authors' => $authors]);
  }

  /* ===== list ===== */
  if ($action === 'list') {
    $category = trim((string)($_GET['category'] ?? ''));
    $author   = trim((string)($_GET['author'] ?? ''));
    $date     = trim((string)($_GET['date'] ?? ''));
    $q        = trim((string)($_GET['q'] ?? ''));
    $sort     = trim((string)($_GET['sort'] ?? 'newest'));

    $limit  = (int)($_GET['limit'] ?? 24);
    $offset = (int)($_GET['offset'] ?? 0);
    if ($limit < 1) $limit = 24;
    if ($limit > 100) $limit = 100;
    if ($offset < 0) $offset = 0;

    $whereParts = [];
    $params = [];

    if ($category !== '') {
      $whereParts[] = "bp.category = ?";
      $params[] = $category;
    }

    if ($author !== '') {
      $whereParts[] = "bp.author_id = ?";
      $params[] = (int)$author;
    }

    if ($date !== '') {
      if ($date === 'week')     $whereParts[] = "bp.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
      if ($date === 'month')    $whereParts[] = "bp.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
      if ($date === '3months')  $whereParts[] = "bp.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
      if ($date === 'year')     $whereParts[] = "bp.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
    }

    if ($q !== '') {
      $like = '%' . $q . '%';
      $whereParts[] = "(" .
        "bp.title LIKE ? OR bp.excerpt LIKE ? OR bp.content LIKE ? OR bp.category LIKE ? OR " .
        "u.username LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?" .
      ")";
      array_push($params, $like, $like, $like, $like, $like, $like, $like);
    }

    $where = $whereParts ? ("WHERE " . implode(" AND ", $whereParts)) : "";

    $orderBy = "bp.created_at DESC";
    if ($sort === 'oldest')  $orderBy = "bp.created_at ASC";
    if ($sort === 'popular') $orderBy = "bp.views DESC, bp.created_at DESC";
    if ($sort === 'title')   $orderBy = "bp.title ASC";

    $sql = "
      SELECT
        bp.*,
        u.username AS author_username,
        u.email AS author_email,
        CONCAT_WS(' ', u.first_name, u.last_name) AS author_name
      FROM blog_posts bp
      LEFT JOIN users u ON u.id = bp.author_id
      $where
      ORDER BY $orderBy
      LIMIT :lim OFFSET :off
    ";

    $stmt = $pdo->prepare($sql);

    // bind normal params ( ? )
    $i = 1;
    foreach ($params as $p) {
      $stmt->bindValue($i, $p);
      $i++;
    }

    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll();

    ok(['rows' => $rows]);
  }

  /* ===== get one ===== */
  if ($action === 'get') {
    $id   = trim((string)($_GET['id'] ?? ''));
    $slug = trim((string)($_GET['slug'] ?? ''));

    if ($id === '' && $slug === '') {
      fail(400, 'Missing id or slug');
    }

    if ($id !== '') {
      $stmt = $pdo->prepare("
        SELECT bp.*,
               u.username AS author_username,
               u.email AS author_email,
               CONCAT_WS(' ', u.first_name, u.last_name) AS author_name
        FROM blog_posts bp
        LEFT JOIN users u ON u.id = bp.author_id
        WHERE bp.id = ?
        LIMIT 1
      ");
      $stmt->execute([(int)$id]);
    } else {
      $stmt = $pdo->prepare("
        SELECT bp.*,
               u.username AS author_username,
               u.email AS author_email,
               CONCAT_WS(' ', u.first_name, u.last_name) AS author_name
        FROM blog_posts bp
        LEFT JOIN users u ON u.id = bp.author_id
        WHERE bp.slug = ?
        LIMIT 1
      ");
      $stmt->execute([$slug]);
    }

    $row = $stmt->fetch();
    if (!$row) fail(404, 'Post not found');

    ok(['row' => $row]);
  }

  /* ===== view (increment) ===== */
  if ($action === 'view') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) fail(400, 'Missing id');

    $stmt = $pdo->prepare("UPDATE blog_posts SET views = COALESCE(views,0) + 1 WHERE id = ?");
    $stmt->execute([$id]);

    ok(['id' => $id]);
  }

  /* ===== create ===== */
  if ($action === 'create') {
    if (empty($_SESSION['user_id'])) {
      fail(401, 'Login required');
    }

    $body = readJsonBody();

    $title   = trim((string)($body['title'] ?? ''));
    $content = trim((string)($body['content'] ?? ''));

    if ($title === '' || $content === '') {
      fail(422, 'Title & Content are required');
    }

    $category      = trim((string)($body['category'] ?? ''));
    $cover_image   = trim((string)($body['cover_image'] ?? ''));
    $excerpt       = trim((string)($body['excerpt'] ?? ''));
    $tags          = trim((string)($body['tags'] ?? ''));
    $read_time_min = isset($body['read_time_min']) && $body['read_time_min'] !== '' ? (int)$body['read_time_min'] : null;
    $location      = trim((string)($body['location'] ?? ''));
    $season        = trim((string)($body['season'] ?? ''));
    $budget        = trim((string)($body['budget'] ?? ''));

    $baseSlug = slugify($title);
    $slug = $baseSlug;

    // make slug unique
    $check = $pdo->prepare("SELECT COUNT(*) c FROM blog_posts WHERE slug = ?");
    $n = 2;
    while (true) {
      $check->execute([$slug]);
      $c = (int)($check->fetch()['c'] ?? 0);
      if ($c === 0) break;
      $slug = $baseSlug . '-' . $n;
      $n++;
      if ($n > 200) break;
    }

    $authorId = (int)$_SESSION['user_id'];

    // safer: don't force status (let DB default)
    $stmt = $pdo->prepare("
      INSERT INTO blog_posts
        (author_id, title, slug, excerpt, content, cover_image, category, tags, read_time_min, location, season, budget, created_at, updated_at)
      VALUES
        (:author_id, :title, :slug, :excerpt, :content, :cover_image, :category, :tags, :read_time_min, :location, :season, :budget, NOW(), NOW())
    ");

    $stmt->execute([
      ':author_id'     => $authorId,
      ':title'         => $title,
      ':slug'          => $slug,
      ':excerpt'       => $excerpt,
      ':content'       => $content,
      ':cover_image'   => $cover_image,
      ':category'      => $category,
      ':tags'          => $tags,
      ':read_time_min' => $read_time_min,
      ':location'      => $location,
      ':season'        => $season,
      ':budget'        => $budget,
    ]);

    $newId = (int)$pdo->lastInsertId();
    ok(['id' => $newId, 'slug' => $slug]);
  }

  /* ===== unknown ===== */
  fail(400, 'Unknown action');

} catch (Throwable $e) {
  // أهم شيء: نرجع JSON مش HTML
  fail(500, 'Server error', ['debug' => $e->getMessage()]);
}
