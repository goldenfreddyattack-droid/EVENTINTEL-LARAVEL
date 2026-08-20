<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');

header('Content-Type: application/json');

if (!isset($_POST['event_id']) || !isset($_POST['service_type'])) {
  echo json_encode(['error' => 'Missing event_id or service_type']);
  exit;
}

$event_id = (int) $_POST['event_id'];
$service_type = $_POST['service_type'];
$pdo = db();

// Verify event belongs to user
$stmt = $pdo->prepare("SELECT event_id FROM events WHERE event_id = ? AND user_id = ?");
$stmt->execute([$event_id, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
  echo json_encode(['error' => 'Event not found']);
  exit;
}

// Map service type to status column
$serviceMap = [
  'venue' => 'venue_status',
  'clothes' => 'clothes_status',
  'catering' => 'catering_status',
  'host' => 'host_status',
  'photographer' => 'photographer_status',
  'soundsnlights' => 'soundsnlights_status',
  'coordinator' => 'coordinator_status'
];

if (!isset($serviceMap[$service_type])) {
  echo json_encode(['error' => 'Invalid service type']);
  exit;
}

$statusColumn = $serviceMap[$service_type];

// Update the service status to Paid
$updateStmt = $pdo->prepare("UPDATE events SET $statusColumn = 'Paid' WHERE event_id = ? AND user_id = ?");
$updateStmt->execute([$event_id, $_SESSION['user_id']]);

echo json_encode(['success' => true, 'message' => 'Service marked as Paid']);
?>
