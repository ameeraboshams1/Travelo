<?php
header('Content-Type: application/json');
require __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'list') {
    $stmt = $pdo->query('SELECT id, first_name, last_name, display_name, email, avatar_url, is_super, is_active, created_at FROM admins ORDER BY created_at DESC');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if ($action === 'create') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $display_name = $_POST['display_name'] ?? null;
    $email = $_POST['email'] ?? '';
    $avatar_url = $_POST['avatar_url'] ?? null;
    $password_raw = $_POST['password_hash'] ?? '';
    $is_super = !empty($_POST['is_super']) ? 1 : 0;
    $is_active = !empty($_POST['is_active']) ? 1 : 0;

    if ($password_raw === '' || $email === '') {
        http_response_code(400);
        echo json_encode(['error' => 'email and password required']);
        exit;
    }

    $password_hash = str_starts_with($password_raw, '$2y$') ? $password_raw : password_hash($password_raw, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO admins (first_name, last_name, display_name, email, password_hash, avatar_url, is_super, is_active) VALUES (:fn, :ln, :dn, :email, :ph, :avatar, :is_super, :is_active)');
    $stmt->execute([
        'fn' => $first_name,
        'ln' => $last_name,
        'dn' => $display_name,
        'email' => $email,
        'ph' => $password_hash,
        'avatar' => $avatar_url,
        'is_super' => $is_super,
        'is_active' => $is_active
    ]);
    echo json_encode(['id' => $pdo->lastInsertId()]);
    exit;
}

if ($action === 'update') {
    $id = $_GET['id'] ?? ($_POST['id'] ?? null);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'id required']);
        exit;
    }

    $first_name = $_POST['first_name'] ?? null;
    $last_name = $_POST['last_name'] ?? null;
    $display_name = $_POST['display_name'] ?? null;
    $email = $_POST['email'] ?? null;
    $avatar_url = $_POST['avatar_url'] ?? null;
    $password_raw = $_POST['password_hash'] ?? null;
    $is_super = isset($_POST['is_super']) ? (!empty($_POST['is_super']) ? 1 : 0) : null;
    $is_active = isset($_POST['is_active']) ? (!empty($_POST['is_active']) ? 1 : 0) : null;

    $fields = [];
    $params = ['id' => $id];

    if ($first_name !== null) { $fields[] = 'first_name = :fn'; $params['fn'] = $first_name; }
    if ($last_name !== null) { $fields[] = 'last_name = :ln'; $params['ln'] = $last_name; }
    if ($display_name !== null) { $fields[] = 'display_name = :dn'; $params['dn'] = $display_name; }
    if ($email !== null) { $fields[] = 'email = :email'; $params['email'] = $email; }
    if ($avatar_url !== null) { $fields[] = 'avatar_url = :avatar'; $params['avatar'] = $avatar_url; }
    if ($is_super !== null) { $fields[] = 'is_super = :is_super'; $params['is_super'] = $is_super; }
    if ($is_active !== null) { $fields[] = 'is_active = :is_active'; $params['is_active'] = $is_active; }
    if ($password_raw !== null && $password_raw !== '') {
        $password_hash = str_starts_with($password_raw, '$2y$') ? $password_raw : password_hash($password_raw, PASSWORD_DEFAULT);
        $fields[] = 'password_hash = :ph';
        $params['ph'] = $password_hash;
    }

    if (!$fields) {
        echo json_encode(['ok' => true]);
        exit;
    }

    $sql = 'UPDATE admins SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'delete') {
    $id = $_GET['id'] ?? ($_POST['id'] ?? null);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'id required']);
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM admins WHERE id = :id');
    $stmt->execute(['id' => $id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'invalid action']);
