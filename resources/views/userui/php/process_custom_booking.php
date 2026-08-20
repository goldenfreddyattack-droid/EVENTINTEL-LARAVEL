<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$coordinatorId = (int)($_POST['coordinator_id'] ?? 0);
$eventType = trim($_POST['event_type'] ?? '');
$eventDate = trim($_POST['event_date'] ?? '');
$venuePreference = trim($_POST['venue_preference'] ?? '');
$guestCount = (int)($_POST['guest_count'] ?? 0);
$theme = trim($_POST['theme'] ?? '');
$budget = (float)($_POST['budget'] ?? 0);
$requiredServices = trim($_POST['required_services'] ?? '');
$specialRequests = trim($_POST['special_requests'] ?? '');
$notes = trim($_POST['additional_notes'] ?? '');

if (!$coordinatorId || !$eventType || !$eventDate) {
    echo json_encode(['success' => false, 'message' => 'Event type and date are required.']);
    exit;
}

$pdo = db();

// Fetch coordinator info
$coordStmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE user_id=? AND role='coordinator'");
$coordStmt->execute([$coordinatorId]);
$coordinator = $coordStmt->fetch();
if (!$coordinator) {
    echo json_encode(['success' => false, 'message' => 'Coordinator not found.']);
    exit;
}

try {
    // Create a new event assigned to this coordinator with status planning
    $title = $eventType . ' Event (Custom)';
    $eventStmt = $pdo->prepare(
        "INSERT INTO events (user_id, title, event_type, theme, budget, event_date, guest_count, coordinator, coordinator_status, status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'planning', NOW())"
    );
    $eventStmt->execute([
        $_SESSION['user_id'],
        $title,
        $eventType,
        $theme ?: null,
        $budget > 0 ? $budget : null,
        $eventDate ?: null,
        $guestCount > 0 ? $guestCount : null,
        $coordinator['full_name'],
    ]);
    $eventId = (int)$pdo->lastInsertId();

    // Store the custom request details
    $reqStmt = $pdo->prepare(
        "INSERT INTO custom_event_requests (event_id, client_id, coordinator_id, event_type, event_date, venue_preference, guest_count, theme, budget, required_services, special_requests, additional_notes, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
    );
    $reqStmt->execute([
        $eventId,
        $_SESSION['user_id'],
        $coordinatorId,
        $eventType,
        $eventDate ?: null,
        $venuePreference ?: null,
        $guestCount > 0 ? $guestCount : null,
        $theme ?: null,
        $budget > 0 ? $budget : null,
        $requiredServices ?: null,
        $specialRequests ?: null,
        $notes ?: null,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Custom event request sent to ' . $coordinator['full_name'] . '!',
        'event_id' => $eventId,
    ]);
    exit;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error creating custom booking: ' . $e->getMessage()]);
    exit;
}
