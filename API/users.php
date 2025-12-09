<?php
header('Content-Type: application/json');
require __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'list') {
    $stmt = $pdo->query('SELECT id, first_name, last_name, username, email, birth_date, is_active, created_at FROM users ORDER BY created_at DESC');
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
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $birth_date = $_POST['birth_date'] ?? null;
    $password_raw = $_POST['password_hash'] ?? '';
    $is_active = !empty($_POST['is_active']) ? 1 : 0;

    if ($email === '' || $username === '' || $password_raw === '') {
        http_response_code(400);
        echo json_encode(['error' => 'username, email, password required']);
        exit;
    }

    $password_hash = str_starts_with($password_raw, '$2y$') ? $password_raw : password_hash($password_raw, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, username, email, birth_date, password_hash, is_active) VALUES (:fn, :ln, :un, :email, :bd, :ph, :is_active)');
    $stmt->execute([
        'fn' => $first_name,
        'ln' => $last_name,
        'un' => $username,
        'email' => $email,
        'bd' => $birth_date ?: null,
        'ph' => $password_hash,
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
    $username = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;
    $birth_date = $_POST['birth_date'] ?? null;
    $password_raw = $_POST['password_hash'] ?? null;
    $is_active = isset($_POST['is_active']) ? (!empty($_POST['is_active']) ? 1 : 0) : null;

    $fields = [];
    $params = ['id' => $id];

    if ($first_name !== null) { $fields[] = 'first_name = :fn'; $params['fn'] = $first_name; }
    if ($last_name !== null) { $fields[] = 'last_name = :ln'; $params['ln'] = $last_name; }
    if ($username !== null) { $fields[] = 'username = :un'; $params['un'] = $username; }
    if ($email !== null) { $fields[] = 'email = :email'; $params['email'] = $email; }
    if ($birth_date !== null) { $fields[] = 'birth_date = :bd'; $params['bd'] = $birth_date ?: null; }
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

    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
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
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute(['id' => $id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'invalid action']);
