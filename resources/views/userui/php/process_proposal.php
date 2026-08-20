<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['event_id'], $_POST['action'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$event_id = intval($_POST['event_id']);
$action = trim($_POST['action']);
$pdo = db();

$stmt = $pdo->prepare('SELECT event_id, coordinator_status FROM events WHERE event_id = ? AND user_id = ?');
$stmt->execute([$event_id, $_SESSION['user_id']]);
$event = $stmt->fetch();

if (!$event) {
    echo json_encode(['error' => 'Event not found']);
    exit;
}

$validProposalActions = ['accept', 'decline', 'revision'];
if (!in_array($action, $validProposalActions, true)) {
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

if ($action === 'accept') {
    $updateStmt = $pdo->prepare(
        "UPDATE events SET
            coordinator_status = 'proposal_accepted',
            payment_status = 'pending',
            venue_status = CASE WHEN venue_status = 'accepted' THEN 'Payment Pending' ELSE venue_status END,
            clothes_status = CASE WHEN clothes_status = 'accepted' THEN 'Payment Pending' ELSE clothes_status END,
            catering_status = CASE WHEN catering_status = 'accepted' THEN 'Payment Pending' ELSE catering_status END,
            host_status = CASE WHEN host_status = 'accepted' THEN 'Payment Pending' ELSE host_status END,
            photographer_status = CASE WHEN photographer_status = 'accepted' THEN 'Payment Pending' ELSE photographer_status END,
            soundsnlights_status = CASE WHEN soundsnlights_status = 'accepted' THEN 'Payment Pending' ELSE soundsnlights_status END
         WHERE event_id = ? AND user_id = ?"
    );
    $updateStmt->execute([$event_id, $_SESSION['user_id']]);

    echo json_encode(['success' => true, 'message' => 'Proposal accepted. Event is now pending payment.']);
    exit;
}

if ($action === 'revision') {
    $feedback = trim($_POST['feedback'] ?? '');
    if ($feedback === '') {
        echo json_encode(['error' => 'Please provide your revision feedback.']);
        exit;
    }

    $updateStmt = $pdo->prepare('UPDATE events SET coordinator_status = ? WHERE event_id = ? AND user_id = ?');
    $updateStmt->execute(['proposal_revision', $event_id, $_SESSION['user_id']]);

    // Record the feedback as a message to the coordinator so they can see and address it
    try {
        // Find the coordinator user id for this event
        $evtStmt = $pdo->prepare('SELECT coordinator FROM events WHERE event_id = ?');
        $evtStmt->execute([$event_id]);
        $evt = $evtStmt->fetch();
        if ($evt && !empty($evt['coordinator'])) {
            $coordStmt = $pdo->prepare("SELECT user_id FROM users WHERE role = 'coordinator' AND full_name = ? LIMIT 1");
            $coordStmt->execute([$evt['coordinator']]);
            $coord = $coordStmt->fetch();
            if ($coord) {
                $msgStmt = $pdo->prepare('INSERT INTO messages (event_id, sender_id, recipient_id, body) VALUES (?, ?, ?, ?)');
                $msgStmt->execute([$event_id, $_SESSION['user_id'], $coord['user_id'], 'Proposal revision request: ' . $feedback]);
            }
        }
    } catch (Exception $e) {
        error_log('Failed to notify coordinator of revision: ' . $e->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'Revision requested. Your feedback has been sent to the coordinator.']);
    exit;
}

if ($action === 'decline') {
    $updateStmt = $pdo->prepare('UPDATE events SET coordinator_status = ? WHERE event_id = ? AND user_id = ?');
    $updateStmt->execute(['proposal_declined', $event_id, $_SESSION['user_id']]);

    echo json_encode(['success' => true, 'message' => 'Proposal declined. The coordinator can send a revised proposal.']);
    exit;
}
