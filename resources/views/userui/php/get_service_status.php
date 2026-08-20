<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');

header('Content-Type: application/json');

if (!isset($_GET['event_id'])) {
    echo json_encode(['error' => 'No event ID provided']);
    exit;
}

$event_id = intval($_GET['event_id']);
$pdo = db();

$stmt = $pdo->prepare('SELECT event_id, payment_method, payment_status FROM events WHERE event_id = ? AND user_id = ?');
$stmt->execute([$event_id, $_SESSION['user_id']]);
$event = $stmt->fetch();

if (!$event) {
    echo json_encode(['error' => 'Event not found']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM events WHERE event_id = ?');
$stmt->execute([$event_id]);
$eventData = $stmt->fetch();

$services = [];

$serviceMap = [
    'venue_name' => ['name' => 'venue_name', 'status' => 'venue_status', 'type' => 'Venue'],
    'clothes' => ['name' => 'clothes', 'status' => 'clothes_status', 'type' => 'Clothing'],
    'catering' => ['name' => 'catering', 'status' => 'catering_status', 'type' => 'Catering'],
    'host' => ['name' => 'host', 'status' => 'host_status', 'type' => 'Host'],
    'photographer' => ['name' => 'photographer', 'status' => 'photographer_status', 'type' => 'Photographer'],
    'soundsnlights' => ['name' => 'soundsnlights', 'status' => 'soundsnlights_status', 'type' => 'Sounds & Lights'],
    'coordinator' => ['name' => 'coordinator', 'status' => 'coordinator_status', 'type' => 'Coordinator'],
];

foreach ($serviceMap as $key => $config) {
    $serviceName = $eventData[$config['name']] ?? null;
    $serviceStatus = $eventData[$config['status']] ?? 'pending';

    if (!empty($serviceName)) {
        // determine note column name
        $noteMap = [
            'venue_name' => 'venue_note',
            'clothes' => 'clothes_note',
            'catering' => 'catering_note',
            'host' => 'host_note',
            'photographer' => 'photographer_note',
            'soundsnlights' => 'soundsnlights_note',
            'coordinator' => 'coordinator_proposal'
        ];

        $noteColumn = $noteMap[$key] ?? null;
        $note = $noteColumn ? ($eventData[$noteColumn] ?? '') : '';

        // Look up supplier service by name to get price + supplier user_id
        $price = 0;
        $supplierUserId = null;
        $serviceCategory = $config['type'];
        $lookup = $pdo->prepare(
            "SELECT s.user_id, s.price FROM supplier_services s
             WHERE s.category = ? AND LOWER(s.name) = LOWER(?)
             ORDER BY s.service_id DESC LIMIT 1"
        );
        $lookup->execute([$serviceCategory, $serviceName]);
        $svcRow = $lookup->fetch();
        if ($svcRow) {
            $supplierUserId = $svcRow['user_id'] ? intval($svcRow['user_id']) : null;
            $price = (float)($svcRow['price'] ?? 0);
        } else {
            // Fallback: try matching by name only (any category)
            $lookup2 = $pdo->prepare(
                "SELECT s.user_id, s.price, s.category FROM supplier_services s
                 WHERE LOWER(s.name) = LOWER(?)
                 ORDER BY s.service_id DESC LIMIT 1"
            );
            $lookup2->execute([$serviceName]);
            $svcRow2 = $lookup2->fetch();
            if ($svcRow2) {
                $supplierUserId = $svcRow2['user_id'] ? intval($svcRow2['user_id']) : null;
                $price = (float)($svcRow2['price'] ?? 0);
            }
        }

        $services[] = [
            'name' => $serviceName,
            'type' => $config['type'],
            'status' => ucfirst(str_replace('_', ' ', $serviceStatus)),
            'raw_status' => strtolower($serviceStatus),
            'service_key' => $key,
            'note' => $note,
            'price' => $price,
            'supplier_user_id' => $supplierUserId,
        ];
    }
}

echo json_encode([
    'services' => $services,
    'payment_method' => $event['payment_method'] ?? 'Not specified',
    'payment_status' => $event['payment_status'] ?? 'pending',
    'coordinator_proposal' => $eventData['coordinator_proposal'] ?? null,
]);
?>
