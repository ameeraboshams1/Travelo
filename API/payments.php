<?php
header('Content-Type: application/json');
require __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'list') {
    $stmt = $pdo->query('SELECT id, booking_id, user_id, payment_method, amount_subtotal, amount_tax, amount_discount, amount_total, currency, promo_code, card_brand, card_last4, card_holder_name, exp_month, exp_year, card_saved, status, gateway_reference, created_at FROM payments ORDER BY created_at DESC');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if ($action === 'create') {
    $booking_id = $_POST['booking_id'] ?? null;
    $user_id = $_POST['user_id'] ?? null;
    $payment_method = $_POST['payment_method'] ?? 'visa';
    $amount_subtotal = $_POST['amount_subtotal'] ?? 0;
    $amount_tax = $_POST['amount_tax'] ?? 0;
    $amount_discount = $_POST['amount_discount'] ?? 0;
    $amount_total = $_POST['amount_total'] ?? 0;
    $currency = $_POST['currency'] ?? 'USD';
    $promo_code = $_POST['promo_code_id'] ?? ($_POST['promo_code'] ?? null);
    $card_brand = $_POST['card_brand'] ?? null;
    $card_last4 = $_POST['card_last4'] ?? null;
    $card_holder_name = $_POST['card_holder_name'] ?? null;
    $exp_month = $_POST['exp_month'] ?? null;
    $exp_year = $_POST['exp_year'] ?? null;
    $card_saved = !empty($_POST['card_saved']) ? 1 : 0;
    $status = $_POST['status'] ?? 'pending';
    $gateway_reference = $_POST['gateway_reference'] ?? null;

    $stmt = $pdo->prepare('INSERT INTO payments (booking_id, user_id, payment_method, amount_subtotal, amount_tax, amount_discount, amount_total, currency, promo_code, card_brand, card_last4, card_holder_name, exp_month, exp_year, card_saved, status, gateway_reference) VALUES (:bid, :uid, :pm, :sub, :tax, :disc, :total, :cur, :promo, :brand, :last4, :holder, :mm, :yy, :saved, :st, :gw)');
    $stmt->execute([
        'bid' => $booking_id ?: null,
        'uid' => $user_id ?: null,
        'pm' => $payment_method,
        'sub' => $amount_subtotal,
        'tax' => $amount_tax,
        'disc' => $amount_discount,
        'total' => $amount_total,
        'cur' => $currency,
        'promo' => $promo_code,
        'brand' => $card_brand,
        'last4' => $card_last4,
        'holder' => $card_holder_name,
        'mm' => $exp_month,
        'yy' => $exp_year,
        'saved' => $card_saved,
        'st' => $status,
        'gw' => $gateway_reference
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

    $cols = ['booking_id','user_id','payment_method','amount_subtotal','amount_tax','amount_discount','amount_total','currency','promo_code','card_brand','card_last4','card_holder_name','exp_month','exp_year','status','gateway_reference'];
    foreach ($cols as $f) {
        if (array_key_exists($f, $_POST)) {
            $fields[] = "$f = :$f";
            $params[$f] = $_POST[$f];
        }
    }
    if (array_key_exists('promo_code_id', $_POST)) {
        $fields[] = 'promo_code = :promo_code';
        $params['promo_code'] = $_POST['promo_code_id'];
    }
    if (array_key_exists('card_saved', $_POST)) {
        $fields[] = 'card_saved = :card_saved';
        $params['card_saved'] = !empty($_POST['card_saved']) ? 1 : 0;
    }

    if (!$fields) {
        echo json_encode(['ok' => true]);
        exit;
    }

    $sql = 'UPDATE payments SET ' . implode(', ', $fields) . ' WHERE id = :id';
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
    $stmt = $pdo->prepare('DELETE FROM payments WHERE id = :id');
    $stmt->execute(['id' => $id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'invalid action']);
