<?php
header('Content-Type: application/json');
require __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'list') {
    $stmt = $pdo->query('SELECT id, name, city, country, category, image_url, short_desc, long_desc, base_price, currency, is_top, is_active, created_at FROM destinations ORDER BY name ASC');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if ($action === 'create') {
    $name = $_POST['name'] ?? '';
    $city = $_POST['city'] ?? '';
    $country = $_POST['country'] ?? '';
    $category = $_POST['category'] ?? 'city';
    $image_url = $_POST['image_url'] ?? '';
    $short_desc = $_POST['short_desc'] ?? '';
    $long_desc = $_POST['long_desc'] ?? '';
    $base_price = $_POST['base_price'] ?? 0;
    $currency = $_POST['currency'] ?? 'USD';
    $is_top = !empty($_POST['is_top']) ? 1 : 0;
    $is_active = !empty($_POST['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare('INSERT INTO destinations (name, city, country, category, image_url, short_desc, long_desc, base_price, currency, is_top, is_active) VALUES (:name, :city, :country, :cat, :img, :sdesc, :ldesc, :price, :cur, :top, :active)');
    $stmt->execute([
        'name' => $name,
        'city' => $city,
        'country' => $country,
        'cat' => $category,
        'img' => $image_url,
        'sdesc' => $short_desc,
        'ldesc' => $long_desc,
        'price' => $base_price,
        'cur' => $currency,
        'top' => $is_top,
        'active' => $is_active
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

    $fields = [];
    $params = ['id' => $id];

    foreach (['name','city','country','category','image_url','short_desc','long_desc','base_price','currency'] as $f) {
        if (array_key_exists($f, $_POST)) {
            $fields[] = "$f = :$f";
            $params[$f] = $_POST[$f];
        }
    }
    if (array_key_exists('is_top', $_POST)) {
        $fields[] = 'is_top = :is_top';
        $params['is_top'] = !empty($_POST['is_top']) ? 1 : 0;
    }
    if (array_key_exists('is_active', $_POST)) {
        $fields[] = 'is_active = :is_active';
        $params['is_active'] = !empty($_POST['is_active']) ? 1 : 0;
    }

    if (!$fields) {
        echo json_encode(['ok' => true]);
        exit;
    }

    $sql = 'UPDATE destinations SET ' . implode(', ', $fields) . ' WHERE id = :id';
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
    $stmt = $pdo->prepare('DELETE FROM destinations WHERE id = :id');
    $stmt->execute(['id' => $id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'invalid action']);
