<?php
/**
 * Messaging API for real-time polling, read receipts, and unread counts.
 * Uses Firebase Realtime Database instead of the MySQL messages table.
 */
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/messaging_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'poll':
        $eventId = (int)($_GET['event_id'] ?? 0);
        $otherUserId = (int)($_GET['other_user_id'] ?? 0);
        $lastId = (float)($_GET['last_id'] ?? 0);

        if (!$eventId || !$otherUserId) {
            echo json_encode(['error' => 'Missing params']);
            exit;
        }

        $messages = firebase_get_messages_for_thread($eventId, $userId, $otherUserId);
        $newMessages = [];
        foreach ($messages as $message) {
            $messageId = (float)($message['message_id'] ?? 0);
            if ($messageId <= $lastId) {
                continue;
            }

            $senderId = (int)($message['sender_id'] ?? 0);
            $recipientId = isset($message['recipient_id']) && $message['recipient_id'] !== '' && $message['recipient_id'] !== null ? (int)$message['recipient_id'] : null;
            $senderName = $message['sender_name'] ?? '';
            $isIncoming = $senderId === $otherUserId && ($recipientId === null || $recipientId === $userId);
            $isOutgoing = $senderId === $userId && ($recipientId === null || $recipientId === $otherUserId);
            if ($isIncoming || $isOutgoing) {
                $message['sender_name'] = $senderName ?: ($senderId === $userId ? 'You' : 'Other');
                $newMessages[] = $message;
            }
        }

        echo json_encode(['messages' => $newMessages]);
        exit;

    case 'mark_read':
        $eventId = (int)($_GET['event_id'] ?? 0);
        $otherUserId = (int)($_GET['other_user_id'] ?? 0);
        if ($eventId && $otherUserId) {
            firebase_mark_thread_read($eventId, $userId, $otherUserId);
        }
        echo json_encode(['success' => true]);
        exit;

    case 'unread':
        echo json_encode(['unread' => total_unread_for_user(null, $userId)]);
        exit;

    case 'thread_unread':
        $eventId = (int)($_GET['event_id'] ?? 0);
        $otherUserId = (int)($_GET['other_user_id'] ?? 0);
        echo json_encode(['unread' => count_unread_for(null, $userId, $eventId, $otherUserId)]);
        exit;

    default:
        echo json_encode(['error' => 'Unknown action']);
        exit;
}
