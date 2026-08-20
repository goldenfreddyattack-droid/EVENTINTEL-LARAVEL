<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['error' => 'Invalid method']);
  exit;
}

$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
$service_type = $_POST['service_type'] ?? '';

$serviceMap = [
  'venue' => ['col' => 'venue_name', 'status' => 'venue_status'],
  'clothes' => ['col' => 'clothes', 'status' => 'clothes_status'],
  'catering' => ['col' => 'catering', 'status' => 'catering_status'],
  'host' => ['col' => 'host', 'status' => 'host_status'],
  'photographer' => ['col' => 'photographer', 'status' => 'photographer_status'],
  'soundsnlights' => ['col' => 'soundsnlights', 'status' => 'soundsnlights_status'],
  'coordinator' => ['col' => 'coordinator', 'status' => 'coordinator_status']
];

if (!isset($serviceMap[$service_type]) || $event_id <= 0) {
  echo json_encode(['error' => 'Invalid parameters']);
  exit;
}

$pdo = db();

// Verify event belongs to this user
$stmt = $pdo->prepare("SELECT {$serviceMap[$service_type]['col']}, {$serviceMap[$service_type]['status']} FROM events WHERE event_id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$event_id, $_SESSION['user_id']]);
$row = $stmt->fetch();
if (!$row) {
  echo json_encode(['error' => 'Event not found']);
  exit;
}

$currentStatus = strtolower(trim((string)$row[$serviceMap[$service_type]['status']] ?? 'pending'));

if ($currentStatus !== 'declined') {
  echo json_encode(['error' => 'Service not in declined state']);
  exit;
}

$col = $serviceMap[$service_type]['col'];
$statusCol = $serviceMap[$service_type]['status'];

// Clear the assignment and reset status to pending so client can reselect
$update = $pdo->prepare("UPDATE events SET $col = '', $statusCol = 'pending' WHERE event_id = ? AND user_id = ?");
$update->execute([$event_id, $_SESSION['user_id']]);

echo json_encode(['success' => true, 'message' => 'Service cleared. You can reselect a supplier now.']);
