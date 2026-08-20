<?php
require_once __DIR__ . '/../../config/db.php';
require_role('supplier');

header('Content-Type: application/json');

if (!isset($_POST['event_id']) || !isset($_POST['service_type'])) {
  echo json_encode(['error' => 'Missing event_id or service_type']);
  exit;
}

$event_id = (int) $_POST['event_id'];
$service_type = $_POST['service_type'];
$pdo = db();

// Map service type to status column and service name column
$serviceMap = [
  'venue' => ['status' => 'venue_status', 'column' => 'venue_name'],
  'clothes' => ['status' => 'clothes_status', 'column' => 'clothes'],
  'catering' => ['status' => 'catering_status', 'column' => 'catering'],
  'host' => ['status' => 'host_status', 'column' => 'host'],
  'photographer' => ['status' => 'photographer_status', 'column' => 'photographer'],
  'soundsnlights' => ['status' => 'soundsnlights_status', 'column' => 'soundsnlights'],
  'coordinator' => ['status' => 'coordinator_status', 'column' => 'coordinator']
];

if (!isset($serviceMap[$service_type])) {
  echo json_encode(['error' => 'Invalid service type']);
  exit;
}

// Verify supplier has this service
$serviceColumn = $serviceMap[$service_type]['column'];
$statusColumn = $serviceMap[$service_type]['status'];

$stmt = $pdo->prepare("SELECT $serviceColumn FROM events WHERE event_id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event || empty($event[$serviceColumn])) {
  echo json_encode(['error' => 'Event or service not found']);
  exit;
}

// Update the service status to Paid
$updateStmt = $pdo->prepare("UPDATE events SET $statusColumn = 'Paid' WHERE event_id = ?");
$updateStmt->execute([$event_id]);

echo json_encode(['success' => true, 'message' => 'Payment accepted and marked as Paid']);
?>
