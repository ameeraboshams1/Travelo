<?php
header('Content-Type: application/json');
require __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'list') {
    // ✅ مطابق للأعمدة الحالية في جدول bookings
    $stmt = $pdo->query('
        SELECT
            id,
            user_id,
            booking_type,
            hotel_id,
            flight_id,
            package_id,
            booking_code,
            trip_start_date,
            trip_end_date,
            travellers_adults,
            travellers_children,
            travellers_infants,
            currency,
            amount_flight,
            amount_hotel,
            amount_package,
            amount_taxes,
            discount_amount,
            coupon_code,
            total_amount,
            booking_status,
            notes,
            created_at
        FROM bookings
        ORDER BY created_at DESC
    ');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if ($action === 'create') {
    $user_id             = $_POST['user_id']             ?? null;
    $booking_type        = $_POST['booking_type']        ?? 'package';
    $package_id          = $_POST['package_id']          ?? null;
    $booking_code        = $_POST['booking_code']        ?? '';
    $trip_start_date     = $_POST['trip_start_date']     ?? null;
    $trip_end_date       = $_POST['trip_end_date']       ?? null;
    $travellers_adults   = $_POST['travellers_adults']   ?? 1;
    $travellers_children = $_POST['travellers_children'] ?? 0;
    $travellers_infants  = $_POST['travellers_infants']  ?? 0;
    $currency            = $_POST['currency']            ?? 'USD';
    $amount_flight       = $_POST['amount_flight']       ?? 0;
    $amount_hotel        = $_POST['amount_hotel']        ?? 0;
    $amount_package      = $_POST['amount_package']      ?? 0;
    $amount_taxes        = $_POST['amount_taxes']        ?? 0;
    $discount_amount     = $_POST['discount_amount']     ?? 0;
    $coupon_code         = $_POST['coupon_code']         ?? null;
    $total_amount        = $_POST['total_amount']        ?? 0;
    $booking_status      = $_POST['booking_status']      ?? 'pending';
    $notes               = $_POST['notes']               ?? null;

    // ✅ ما في ولا payment_* هون
    $stmt = $pdo->prepare('
        INSERT INTO bookings (
            user_id,
            booking_type,
            hotel_id,
            flight_id,
            package_id,
            booking_code,
            trip_start_date,
            trip_end_date,
            travellers_adults,
            travellers_children,
            travellers_infants,
            currency,
            amount_flight,
            amount_hotel,
            amount_package,
            amount_taxes,
            discount_amount,
            coupon_code,
            total_amount,
            booking_status,
            notes
        ) VALUES (
            :uid,
            :type,
            NULL,
            NULL,
            :pkg,
            :code,
            :ts,
            :te,
            :ad,
            :ch,
            :inf,
            :cur,
            :af,
            :ah,
            :ap,
            :tax,
            :disc,
            :coupon,
            :total,
            :bstat,
            :notes
        )
    ');
    $stmt->execute([
        'uid'    => $user_id ?: null,
        'type'   => $booking_type,
        'pkg'    => $package_id ?: null,
        'code'   => $booking_code,
        'ts'     => $trip_start_date ?: null,
        'te'     => $trip_end_date ?: null,
        'ad'     => $travellers_adults,
        'ch'     => $travellers_children,
        'inf'    => $travellers_infants,
        'cur'    => $currency,
        'af'     => $amount_flight,
        'ah'     => $amount_hotel,
        'ap'     => $amount_package,
        'tax'    => $amount_taxes,
        'disc'   => $discount_amount,
        'coupon' => $coupon_code,
        'total'  => $total_amount,
        'bstat'  => $booking_status,
        'notes'  => $notes
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

    // ✅ شلنا payment_method / payment_status من اللستة
    $cols = [
        'user_id',
        'booking_type',
        'hotel_id',
        'flight_id',
        'package_id',
        'booking_code',
        'trip_start_date',
        'trip_end_date',
        'travellers_adults',
        'travellers_children',
        'travellers_infants',
        'currency',
        'amount_flight',
        'amount_hotel',
        'amount_package',
        'amount_taxes',
        'discount_amount',
        'coupon_code',
        'total_amount',
        'booking_status',
        'notes'
    ];

    foreach ($cols as $f) {
        if (array_key_exists($f, $_POST)) {
            $fields[]      = "$f = :$f";
            $params[$f]    = $_POST[$f];
        }
    }

    if (!$fields) {
        echo json_encode(['ok' => true]);
        exit;
    }

    $sql = 'UPDATE bookings SET ' . implode(', ', $fields) . ' WHERE id = :id';
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
    $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = :id');
    $stmt->execute(['id' => $id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'invalid action']);
