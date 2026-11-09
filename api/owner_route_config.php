<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse([
        'success' => false,
        'message' => 'Method not allowed.'
    ], 405);
}

requireLogin();

$pdo = getDbConnection();
$userId = (int) ($_SESSION['user']['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM owner_route_configs WHERE user_id = :user_id LIMIT 1');
    $stmt->execute([':user_id' => $userId]);
    $row = $stmt->fetch();

    if (!$row) {
        jsonResponse([
            'success' => true,
            'data' => null
        ]);
    }

    jsonResponse([
        'success' => true,
        'data' => [
            'user_id' => (int) $row['user_id'],
            'vehicle_type' => $row['vehicle_type'],
            'vehicle_speed' => (float) $row['vehicle_speed'],
            'pickup_label' => $row['pickup_label'],
            'pickup_lat' => $row['pickup_lat'],
            'pickup_lng' => $row['pickup_lng'],
            'destination_label' => $row['destination_label'],
            'destination_lat' => $row['destination_lat'],
            'destination_lng' => $row['destination_lng'],
        ]
    ]);
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    jsonResponse([
        'success' => false,
        'message' => 'Payload tidak valid.'
    ], 400);
}

$vehicleType = in_array(($payload['ownerVehicleType'] ?? 'motor'), ['motor', 'mobil', 'pesawat', 'helikopter'], true)
    ? $payload['ownerVehicleType']
    : 'motor';

$vehicleSpeed = (float) ($payload['ownerVehicleSpeed'] ?? 45);
if ($vehicleSpeed <= 0) {
    $vehicleSpeed = 45;
}

$pickup = $payload['pickup'] ?? null;
$destination = $payload['destination'] ?? null;

$pickupLabel = is_array($pickup) ? trim((string) ($pickup['label'] ?? '')) : '';
$pickupLat = is_array($pickup) && isset($pickup['coords'][0]) ? (float) $pickup['coords'][0] : null;
$pickupLng = is_array($pickup) && isset($pickup['coords'][1]) ? (float) $pickup['coords'][1] : null;

$destinationLabel = is_array($destination) ? trim((string) ($destination['label'] ?? '')) : '';
$destinationLat = is_array($destination) && isset($destination['coords'][0]) ? (float) $destination['coords'][0] : null;
$destinationLng = is_array($destination) && isset($destination['coords'][1]) ? (float) $destination['coords'][1] : null;

$stmt = $pdo->prepare(
    'INSERT INTO owner_route_configs (
        user_id, vehicle_type, vehicle_speed, pickup_label, pickup_lat, pickup_lng, destination_label, destination_lat, destination_lng
    ) VALUES (
        :user_id, :vehicle_type, :vehicle_speed, :pickup_label, :pickup_lat, :pickup_lng, :destination_label, :destination_lat, :destination_lng
    ) ON DUPLICATE KEY UPDATE
        vehicle_type = VALUES(vehicle_type),
        vehicle_speed = VALUES(vehicle_speed),
        pickup_label = VALUES(pickup_label),
        pickup_lat = VALUES(pickup_lat),
        pickup_lng = VALUES(pickup_lng),
        destination_label = VALUES(destination_label),
        destination_lat = VALUES(destination_lat),
        destination_lng = VALUES(destination_lng),
        updated_at = CURRENT_TIMESTAMP'
);

$stmt->execute([
    ':user_id' => $userId,
    ':vehicle_type' => $vehicleType,
    ':vehicle_speed' => $vehicleSpeed,
    ':pickup_label' => $pickupLabel ?: null,
    ':pickup_lat' => $pickupLat,
    ':pickup_lng' => $pickupLng,
    ':destination_label' => $destinationLabel ?: null,
    ':destination_lat' => $destinationLat,
    ':destination_lng' => $destinationLng,
]);

jsonResponse([
    'success' => true,
    'message' => 'Konfigurasi rute owner berhasil disimpan.'
]);
