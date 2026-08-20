<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');

header('Content-Type: application/json');

if (!isset($_POST['event_id']) || !isset($_POST['service_type']) || !isset($_POST['payment_method'])) {
  echo json_encode(['error' => 'Missing event_id, service_type or payment_method']);
  exit;
}

$event_id = (int) $_POST['event_id'];
$service_type = trim($_POST['service_type']);
$payment_method = in_array(trim($_POST['payment_method']), ['cash', 'online', 'gcash'], true) ? trim($_POST['payment_method']) : 'cash';
if ($payment_method === 'gcash') {
  $payment_method = 'online';
}

$pdo = db();

// Verify event belongs to this client
$stmt = $pdo->prepare('SELECT event_id FROM events WHERE event_id = ? AND user_id = ?');
$stmt->execute([$event_id, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
  echo json_encode(['error' => 'Event not found']);
  exit;
}

// Map service key -> status column + name column
$serviceMap = [
  'venue'        => ['status' => 'venue_status',        'name' => 'venue_name'],
  'clothes'      => ['status' => 'clothes_status',      'name' => 'clothes'],
  'catering'     => ['status' => 'catering_status',     'name' => 'catering'],
  'host'         => ['status' => 'host_status',         'name' => 'host'],
  'photographer' => ['status' => 'photographer_status', 'name' => 'photographer'],
  'soundsnlights'=> ['status' => 'soundsnlights_status','name' => 'soundsnlights'],
  'coordinator'  => ['status' => 'coordinator_status',  'name' => 'coordinator'],
];

if (!isset($serviceMap[$service_type])) {
  echo json_encode(['error' => 'Invalid service type']);
  exit;
}

$statusColumn = $serviceMap[$service_type]['status'];
$nameColumn   = $serviceMap[$service_type]['name'];

// Fetch the service name + the user that owns that supplier service (paid_to)
$eventStmt = $pdo->prepare("SELECT $nameColumn AS svc_name FROM events WHERE event_id = ?");
$eventStmt->execute([$event_id]);
$eventRow = $eventStmt->fetch();
$serviceName = trim((string)($eventRow['svc_name'] ?? ''));
if ($serviceName === '') {
  echo json_encode(['error' => 'Service not assigned to this event']);
  exit;
}

$paidTo = null;
if ($service_type === 'coordinator') {
  // Coordinator may be assigned by name; try to find the coordinator user by full_name
  $c = $pdo->prepare("SELECT user_id FROM users WHERE role='coordinator' AND full_name = ? LIMIT 1");
  $c->execute([$serviceName]);
  $paidTo = $c->fetchColumn() ? (int)$c->fetchColumn() : null;
} else {
  $lookup = $pdo->prepare("SELECT user_id FROM supplier_services WHERE LOWER(name) = LOWER(?) ORDER BY service_id DESC LIMIT 1");
  $lookup->execute([$serviceName]);
  $paidTo = $lookup->fetchColumn() ? (int)$lookup->fetchColumn() : null;
}

// Determine amount from the supplier service price (or 0 if not found)
$amount = 0.0;
if ($service_type !== 'coordinator') {
  $priceStmt = $pdo->prepare("SELECT price FROM supplier_services WHERE LOWER(name) = LOWER(?) ORDER BY service_id DESC LIMIT 1");
  $priceStmt->execute([$serviceName]);
  $amount = (float)($priceStmt->fetchColumn() ?: 0);
} else {
  // Coordinator proposals: use the coordinator proposal amount if stored as "Amount: 12345" text
  $prop = $pdo->prepare("SELECT coordinator_proposal FROM events WHERE event_id = ?");
  $prop->execute([$event_id]);
  $proposal = (string)($prop->fetchColumn() ?: '');
  if (preg_match('/[₱P]?\s?([0-9][0-9,]*)/i', $proposal, $m)) {
    $amount = (float)str_replace(',', '', $m[1]);
  }
}

// Update the service status to Payment Pending (same behavior as update_service_payment.php)
$updateStmt = $pdo->prepare("UPDATE events SET $statusColumn = 'Payment Pending' WHERE event_id = ? AND user_id = ?");
$updateStmt->execute([$event_id, $_SESSION['user_id']]);

// Record the payment in the payments log table
$payStmt = $pdo->prepare(
  "INSERT INTO payments (event_id, service_type, service_name, amount, payment_method, paid_by, paid_to, status, note)
   VALUES (?, ?, ?, ?, ?, ?, ?, 'Payment Pending', ?)"
);
$payStmt->execute([
  $event_id,
  $service_type,
  $serviceName,
  $amount,
  $payment_method,
  $_SESSION['user_id'],
  $paidTo,
  'Client initiated payment via ' . ucfirst($payment_method),
]);

echo json_encode([
  'success' => true,
  'message' => 'Payment recorded. Supplier has been notified.',
  'payment_method' => $payment_method,
  'amount' => $amount,
  'service_name' => $serviceName,
]);
?>


