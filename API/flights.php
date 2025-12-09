<?php
header('Content-Type: application/json');
require __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'list') {
    $stmt = $pdo->query('SELECT id, destination_id, airline_name, airline_code, flight_number, trip_type, departure_date, return_date, origin_city, origin_airport_code, destination_city, destination_airport_code, departure_time, arrival_time, duration_hours, stops_count, fare_subtitle, extras, base_price, currency, is_active, created_at FROM flights ORDER BY departure_date DESC, departure_time DESC');
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
    $airline_name = $_POST['airline_name'] ?? '';
    $airline_code = $_POST['airline_code'] ?? null;
    $flight_number = $_POST['flight_number'] ?? '';
    $trip_type = $_POST['trip_type'] ?? 'oneway';
    $origin_city = $_POST['origin_city'] ?? '';
    $origin_airport_code = $_POST['origin_airport_code'] ?? '';
    $destination_city = $_POST['destination_city'] ?? '';
    $destination_airport_code = $_POST['destination_airport_code'] ?? '';
    $departure_date = $_POST['depart_date'] ?? null;
    $return_date = $_POST['return_date'] ?? null;
    $departure_time = $_POST['departure_time'] ?? null;
    $arrival_time = $_POST['arrival_time'] ?? null;
    $duration_hours = $_POST['duration_hours'] ?? 0;
    $stops_count = $_POST['stops_count'] ?? 0;
    $fare_subtitle = $_POST['fare_subtitle'] ?? null;
    $extras = $_POST['extras'] ?? null;
    $base_price = $_POST['base_price'] ?? 0;
    $currency = $_POST['currency'] ?? 'USD';
    $is_active = !empty($_POST['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare('INSERT INTO flights (destination_id, airline_name, airline_code, flight_number, trip_type, departure_date, return_date, origin_city, origin_airport_code, destination_city, destination_airport_code, departure_time, arrival_time, duration_hours, stops_count, fare_subtitle, extras, base_price, currency, is_active) VALUES (:dest, :an, :ac, :fn, :tt, :dd, :rd, :oc, :oac, :dc, :dac, :dt, :at, :dur, :stops, :fs, :ex, :bp, :cur, :active)');
    $stmt->execute([
        'dest' => $destination_id ?: null,
        'an' => $airline_name,
        'ac' => $airline_code,
        'fn' => $flight_number,
        'tt' => $trip_type,
        'dd' => $departure_date ?: null,
        'rd' => $return_date ?: null,
        'oc' => $origin_city,
        'oac' => $origin_airport_code,
        'dc' => $destination_city,
        'dac' => $destination_airport_code,
        'dt' => $departure_time ?: null,
        'at' => $arrival_time ?: null,
        'dur' => $duration_hours,
        'stops' => $stops_count,
        'fs' => $fare_subtitle,
        'ex' => $extras,
        'bp' => $base_price,
        'cur' => $currency,
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

    $map = [
        'destination_id','airline_name','airline_code','flight_number','trip_type',
        'origin_city','origin_airport_code','destination_city','destination_airport_code',
        'fare_subtitle','extras','base_price','currency'
    ];
    foreach ($map as $f) {
        if (array_key_exists($f, $_POST)) {
            $fields[] = "$f = :$f";
            $params[$f] = $_POST[$f];
        }
    }

    if (array_key_exists('depart_date', $_POST)) {
        $fields[] = 'departure_date = :departure_date';
        $params['departure_date'] = $_POST['depart_date'] ?: null;
    }
    if (array_key_exists('return_date', $_POST)) {
        $fields[] = 'return_date = :return_date';
        $params['return_date'] = $_POST['return_date'] ?: null;
    }
    if (array_key_exists('departure_time', $_POST)) {
        $fields[] = 'departure_time = :departure_time';
        $params['departure_time'] = $_POST['departure_time'] ?: null;
    }
    if (array_key_exists('arrival_time', $_POST)) {
        $fields[] = 'arrival_time = :arrival_time';
        $params['arrival_time'] = $_POST['arrival_time'] ?: null;
    }
    if (array_key_exists('duration_hours', $_POST)) {
        $fields[] = 'duration_hours = :duration_hours';
        $params['duration_hours'] = $_POST['duration_hours'];
    }
    if (array_key_exists('stops_count', $_POST)) {
        $fields[] = 'stops_count = :stops_count';
        $params['stops_count'] = $_POST['stops_count'];
    }
    if (array_key_exists('is_active', $_POST)) {
        $fields[] = 'is_active = :is_active';
        $params['is_active'] = !empty($_POST['is_active']) ? 1 : 0;
    }

    if (!$fields) {
        echo json_encode(['ok' => true]);
        exit;
    }

    $sql = 'UPDATE flights SET ' . implode(', ', $fields) . ' WHERE id = :id';
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
    $stmt = $pdo->prepare('DELETE FROM flights WHERE id = :id');
    $stmt->execute(['id' => $id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'invalid action']);
