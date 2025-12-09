<?php
header('Content-Type: application/json');
require __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'list') {
    $stmt = $pdo->query('SELECT id, destination_id, name, location_text, rating, reviews_count, price_per_night, currency, discount_percent, description, has_parking, has_attached_bathroom, has_cctv, has_wifi, has_sea_view, has_city_view, has_free_breakfast, pay_at_hotel, couple_friendly, pet_friendly, airport_shuttle, is_active, created_at FROM hotels ORDER BY created_at DESC');
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
    $name = $_POST['name'] ?? '';
    $location_text = $_POST['location_text'] ?? '';
    $rating = $_POST['rating'] ?? 0;
    $reviews_count = $_POST['reviews_count'] ?? 0;
    $price_per_night = $_POST['price_per_night'] ?? 0;
    $currency = $_POST['currency'] ?? 'USD';
    $discount_percent = $_POST['discount_percent'] ?? 0;
    $description = $_POST['description'] ?? '';
    $has_parking = !empty($_POST['has_parking']) ? 1 : 0;
    $has_attached_bathroom = !empty($_POST['has_attached_bathroom']) ? 1 : 0;
    $has_cctv = !empty($_POST['has_cctv']) ? 1 : 0;
    $has_wifi = !empty($_POST['has_wifi']) ? 1 : 0;
    $has_sea_view = !empty($_POST['has_sea_view']) ? 1 : 0;
    $has_city_view = !empty($_POST['has_city_view']) ? 1 : 0;
    $has_free_breakfast = !empty($_POST['has_free_breakfast']) ? 1 : 0;
    $pay_at_hotel = !empty($_POST['pay_at_hotel']) ? 1 : 0;
    $couple_friendly = !empty($_POST['couple_friendly']) ? 1 : 0;
    $pet_friendly = !empty($_POST['pet_friendly']) ? 1 : 0;
    $airport_shuttle = !empty($_POST['airport_shuttle']) ? 1 : 0;
    $is_active = !empty($_POST['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare('INSERT INTO hotels (destination_id, name, location_text, rating, reviews_count, price_per_night, currency, discount_percent, description, has_parking, has_attached_bathroom, has_cctv, has_wifi, has_sea_view, has_city_view, has_free_breakfast, pay_at_hotel, couple_friendly, pet_friendly, airport_shuttle, is_active) VALUES (:dest, :name, :loc, :rating, :reviews, :price, :cur, :disc, :desc, :park, :bath, :cctv, :wifi, :sea, :city, :breakfast, :pay, :couple, :pet, :shuttle, :active)');
    $stmt->execute([
        'dest' => $destination_id ?: null,
        'name' => $name,
        'loc' => $location_text,
        'rating' => $rating,
        'reviews' => $reviews_count,
        'price' => $price_per_night,
        'cur' => $currency,
        'disc' => $discount_percent,
        'desc' => $description,
        'park' => $has_parking,
        'bath' => $has_attached_bathroom,
        'cctv' => $has_cctv,
        'wifi' => $has_wifi,
        'sea' => $has_sea_view,
        'city' => $has_city_view,
        'breakfast' => $has_free_breakfast,
        'pay' => $pay_at_hotel,
        'couple' => $couple_friendly,
        'pet' => $pet_friendly,
        'shuttle' => $airport_shuttle,
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

    foreach (['destination_id','name','location_text','rating','reviews_count','price_per_night','currency','discount_percent','description'] as $f) {
        if (array_key_exists($f, $_POST)) {
            $fields[] = "$f = :$f";
            $params[$f] = $_POST[$f];
        }
    }
    foreach (['has_parking','has_attached_bathroom','has_cctv','has_wifi','has_sea_view','has_city_view','has_free_breakfast','pay_at_hotel','couple_friendly','pet_friendly','airport_shuttle','is_active'] as $f) {
        if (array_key_exists($f, $_POST)) {
            $fields[] = "$f = :$f";
            $params[$f] = !empty($_POST[$f]) ? 1 : 0;
        }
    }

    if (!$fields) {
        echo json_encode(['ok' => true]);
        exit;
    }

    $sql = 'UPDATE hotels SET ' . implode(', ', $fields) . ' WHERE id = :id';
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
    $stmt = $pdo->prepare('DELETE FROM hotels WHERE id = :id');
    $stmt->execute(['id' => $id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'invalid action']);
