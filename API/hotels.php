<?php
header('Content-Type: application/json');
require __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

/**
 * Detect columns of hotel_images table dynamically to avoid name-mismatch issues.
 * Returns:
 *  - hasImagesTable (bool)
 *  - hotelIdCol (string|null)
 *  - urlCol (string|null)
 *  - coverCol (string|null)
 *  - orderCol (string|null)
 *  - createdCol (string|null)
 */
function detectHotelImagesMeta(PDO $pdo): array {
    try {
        // does table exist?
        $t = $pdo->prepare("
            SELECT COUNT(*) 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hotel_images'
        ");
        $t->execute();
        $exists = (int)$t->fetchColumn() > 0;

        if (!$exists) {
            return [
                'hasImagesTable' => false,
                'hotelIdCol' => null,
                'urlCol' => null,
                'coverCol' => null,
                'orderCol' => null,
                'createdCol' => null
            ];
        }

        $c = $pdo->prepare("
            SELECT COLUMN_NAME
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hotel_images'
        ");
        $c->execute();
        $cols = array_map('strtolower', $c->fetchAll(PDO::FETCH_COLUMN));

        $pickFirst = function(array $cands) use ($cols) {
            foreach ($cands as $x) {
                if (in_array(strtolower($x), $cols, true)) return $x;
            }
            return null;
        };

        // common naming variants
        $hotelIdCol = $pickFirst(['hotel_id', 'hotels_id', 'hotelid']);
        $urlCol     = $pickFirst(['image_url', 'url', 'image_path', 'path', 'src', 'photo_url']);
        $coverCol   = $pickFirst(['is_cover', 'is_main', 'is_primary', 'is_featured', 'cover']);
        $orderCol   = $pickFirst(['sort_order', 'position', 'sort', 'ord']);
        $createdCol = $pickFirst(['created_at', 'created', 'uploaded_at']);

        return [
            'hasImagesTable' => true,
            'hotelIdCol' => $hotelIdCol,
            'urlCol' => $urlCol,
            'coverCol' => $coverCol,
            'orderCol' => $orderCol,
            'createdCol' => $createdCol
        ];
    } catch (Throwable $e) {
        return [
            'hasImagesTable' => false,
            'hotelIdCol' => null,
            'urlCol' => null,
            'coverCol' => null,
            'orderCol' => null,
            'createdCol' => null
        ];
    }
}

if ($action === 'list') {
    // Build a safe "cover image" selection from hotel_images (if table/columns exist)
    $meta = detectHotelImagesMeta($pdo);

    $imageSelectSql = "NULL AS image_url";
    if ($meta['hasImagesTable'] && $meta['hotelIdCol'] && $meta['urlCol']) {
        $hotelIdCol = $meta['hotelIdCol'];
        $urlCol     = $meta['urlCol'];
        $coverCol   = $meta['coverCol'];
        $orderCol   = $meta['orderCol'];
        $createdCol = $meta['createdCol'];

        $orderParts = [];

        // Prefer cover if exists
        if ($coverCol) {
            $orderParts[] = "CASE WHEN hi.`$coverCol` IS NULL THEN 0 ELSE hi.`$coverCol` END DESC";
        }
        // Then sort_order / position if exists
        if ($orderCol) {
            $orderParts[] = "hi.`$orderCol` ASC";
        }
        // Then created_at if exists
        if ($createdCol) {
            $orderParts[] = "hi.`$createdCol` ASC";
        }
        // Finally by id (always exists usually)
        $orderParts[] = "hi.id ASC";

        $orderBy = implode(", ", $orderParts);

        // Correlated subquery to fetch 1 best image per hotel
        $imageSelectSql = "(
            SELECT hi.`$urlCol`
            FROM hotel_images hi
            WHERE hi.`$hotelIdCol` = h.id
            ORDER BY $orderBy
            LIMIT 1
        ) AS image_url";
    }

    $sql = "
        SELECT
            h.id,
            h.destination_id,
            h.name,
            h.location_text,
            h.rating,
            h.reviews_count,
            h.price_per_night,
            h.currency,
            h.discount_percent,
            h.description,
            h.has_parking,
            h.has_attached_bathroom,
            h.has_cctv,
            h.has_wifi,
            h.has_sea_view,
            h.has_city_view,
            h.has_free_breakfast,
            h.pay_at_hotel,
            h.couple_friendly,
            h.pet_friendly,
            h.airport_shuttle,
            h.is_active,
            h.created_at,
            $imageSelectSql
        FROM hotels h
        ORDER BY h.created_at DESC
    ";

    $stmt = $pdo->query($sql);
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

    $stmt = $pdo->prepare('
        INSERT INTO hotels
          (destination_id, name, location_text, rating, reviews_count, price_per_night, currency, discount_percent, description,
           has_parking, has_attached_bathroom, has_cctv, has_wifi, has_sea_view, has_city_view, has_free_breakfast, pay_at_hotel,
           couple_friendly, pet_friendly, airport_shuttle, is_active)
        VALUES
          (:dest, :name, :loc, :rating, :reviews, :price, :cur, :disc, :desc,
           :park, :bath, :cctv, :wifi, :sea, :city, :breakfast, :pay,
           :couple, :pet, :shuttle, :active)
    ');

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
