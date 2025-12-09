<?php
header('Content-Type: application/json');
require __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'list') {
    $stmt = $pdo->query('SELECT id, title, destination_id, hotel_id, flight_id, location, from_city, image_url, badge_type, price_usd, duration_days, rating, reviews_count, category, is_featured, is_active, created_at FROM packages ORDER BY created_at DESC');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if ($action === 'create') {
    $destination_id = $_POST['destination_id'] ?? null;
    $title = $_POST['title'] ?? '';
    $location = $_POST['location'] ?? '';
    $from_city = $_POST['from_city'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    $badge_type = $_POST['badge_type'] ?? '';
    $price_usd = $_POST['price_usd'] ?? 0;
    $duration_days = $_POST['duration_days'] ?? 0;
    $rating = $_POST['rating'] ?? 0;
    $reviews_count = $_POST['reviews_count'] ?? 0;
    $category = $_POST['category'] ?? 'city';
    $is_featured = !empty($_POST['is_featured']) ? 1 : 0;
    $is_active = !empty($_POST['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare('INSERT INTO packages (title, destination_id, location, from_city, image_url, badge_type, price_usd, duration_days, rating, reviews_count, category, is_featured, is_active) VALUES (:title, :dest, :loc, :fromc, :img, :badge, :price, :days, :rating, :reviews, :cat, :feat, :active)');
    $stmt->execute([
        'title' => $title,
        'dest' => $destination_id ?: null,
        'loc' => $location,
        'fromc' => $from_city,
        'img' => $image_url,
        'badge' => $badge_type,
        'price' => $price_usd,
        'days' => $duration_days,
        'rating' => $rating,
        'reviews' => $reviews_count,
        'cat' => $category,
        'feat' => $is_featured,
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

    foreach (['title','destination_id','location','from_city','image_url','badge_type','price_usd','duration_days','rating','reviews_count','category'] as $f) {
        if (array_key_exists($f, $_POST)) {
            $fields[] = "$f = :$f";
            $params[$f] = $_POST[$f];
        }
    }
    if (array_key_exists('is_featured', $_POST)) {
        $fields[] = 'is_featured = :is_featured';
        $params['is_featured'] = !empty($_POST['is_featured']) ? 1 : 0;
    }
    if (array_key_exists('is_active', $_POST)) {
        $fields[] = 'is_active = :is_active';
        $params['is_active'] = !empty($_POST['is_active']) ? 1 : 0;
    }

    if (!$fields) {
        echo json_encode(['ok' => true]);
        exit;
    }

    $sql = 'UPDATE packages SET ' . implode(', ', $fields) . ' WHERE id = :id';
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
    $stmt = $pdo->prepare('DELETE FROM packages WHERE id = :id');
    $stmt->execute(['id' => $id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'invalid action']);
