<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/messaging_helpers.php';
require_role('supplier');

$pdo = db();
$supplierId = $_SESSION['user_id'];
$selectedEventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$selectedUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$error = '';

// Handle POST send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'], $_POST['message'])) {
    $selectedEventId = intval($_POST['event_id']);
    $selectedUserId = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : 0;
    $messageText = trim($_POST['message']);

    if ($messageText === '') {
        $error = 'Please enter a message.';
    } else {
        try {
            firebase_save_message($selectedEventId, $supplierId, $selectedUserId ?: null, $messageText, $_SESSION['full_name'] ?? 'Supplier');
            header('Location: MESSAGES.php?event_id=' . $selectedEventId . '&user_id=' . $selectedUserId);
            exit;
        } catch (RuntimeException $e) {
            $error = 'Message could not be sent. Firebase rejected the message: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

// Build threads: events where this supplier's services are assigned
$serviceMap = [
    'venue_name' => 'Venue',
    'clothes' => 'Clothing',
    'catering' => 'Catering',
    'host' => 'Host',
    'photographer' => 'Photographer',
    'soundsnlights' => 'Sounds & Lights',
];

// Get this supplier's service names
$myServices = $pdo->prepare('SELECT name FROM supplier_services WHERE user_id = ?');
$myServices->execute([$supplierId]);
$myServiceNames = array_map('strtolower', array_column($myServices->fetchAll(), 'name'));

$threads = [];
if (!empty($myServiceNames)) {
    foreach ($serviceMap as $col => $label) {
        $placeholders = implode(',', array_fill(0, count($myServiceNames), '?'));
        $query = "SELECT e.event_id, e.title, e.event_date, e.user_id AS client_id, e.coordinator,
                         u.full_name AS client_name, e.$col AS service_name
                  FROM events e
                  JOIN users u ON e.user_id = u.user_id
                  WHERE LOWER(e.$col) IN ($placeholders)
                  ORDER BY e.created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($myServiceNames);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $r) {
            // Client thread
            $threads[] = [
                'event_id' => (int)$r['event_id'],
                'event_title' => $r['title'] ?: 'Untitled Event',
                'event_date' => $r['event_date'],
                'user_id' => (int)$r['client_id'],
                'name' => $r['client_name'] ?: 'Client',
                'role' => 'Client',
            ];

            // Coordinator thread (supplier <-> coordinator)
            if (!empty($r['coordinator'])) {
                $coordStmt = $pdo->prepare('SELECT user_id, full_name, business_name FROM users WHERE role=? AND full_name=? LIMIT 1');
                $coordStmt->execute(['coordinator', $r['coordinator']]);
                $coord = $coordStmt->fetch();
                if ($coord && (int)$coord['user_id'] !== $supplierId) {
                    $threads[] = [
                        'event_id' => (int)$r['event_id'],
                        'event_title' => $r['title'] ?: 'Untitled Event',
                        'event_date' => $r['event_date'],
                        'user_id' => (int)$coord['user_id'],
                        'name' => $coord['business_name'] ?: $coord['full_name'],
                        'role' => 'Coordinator',
                    ];
                }
            }
        }
    }
}

// If a specific user_id was requested but no event_id, find a matching thread event
if ($selectedUserId > 0 && $selectedEventId === 0) {
    foreach ($threads as $t) {
        if ($t['user_id'] === $selectedUserId) {
            $selectedEventId = $t['event_id'];
            break;
        }
    }
}

// Default: first thread
if ($selectedEventId === 0 && !empty($threads)) {
    $selectedEventId = $threads[0]['event_id'];
    $selectedUserId = $threads[0]['user_id'];
}

// If event selected but no user selected, pick first thread of that event
if ($selectedEventId > 0 && $selectedUserId === 0) {
    foreach ($threads as $t) {
        if ($t['event_id'] === $selectedEventId) {
            $selectedUserId = $t['user_id'];
            break;
        }
    }
}

$selectedThreadName = '';
$selectedThreadRole = '';
foreach ($threads as $t) {
    if ($t['event_id'] === $selectedEventId && $t['user_id'] === $selectedUserId) {
        $selectedThreadName = $t['name'];
        $selectedThreadRole = $t['role'];
        break;
    }
}

// Load messages
$messages = [];
$lastMessageId = 0;
if ($selectedEventId > 0 && $selectedUserId > 0) {
    $messages = firebase_get_messages_for_thread($selectedEventId, $supplierId, $selectedUserId);
    foreach ($messages as &$message) {
        $senderId = (int)($message['sender_id'] ?? 0);
        $message['full_name'] = $message['sender_name'] ?? ($senderId === $supplierId ? ($_SESSION['full_name'] ?? 'You') : $selectedThreadName);
        if (isset($message['read_by']) && is_array($message['read_by']) && isset($message['read_by'][(string)$supplierId])) {
            $message['is_read'] = true;
        }
    }
    unset($message);
    if (!empty($messages)) {
        $lastMessageId = (float)end($messages)['message_id'];
    }
    mark_messages_read($pdo, $selectedEventId, $supplierId, $selectedUserId);
}

// Unread counts per thread
$unreadCounts = [];
foreach ($threads as $t) {
    $unreadCounts[$t['event_id'] . '_' . $t['user_id']] = count_unread_for($pdo, $supplierId, $t['event_id'], $t['user_id']);
}
$globalUnread = total_unread_for_user($pdo, $supplierId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Messages</title>
    <link rel="stylesheet" href="../css/supplier.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <?php require_once __DIR__ . '/../includes/chat_ui.php'; ?>
    <style>
        .chat-container { display: grid; grid-template-columns: 320px 1fr; gap: 20px; }
        .chat-sidebar { background: var(--panel); border: 1px solid var(--border); border-radius: 24px; padding: 16px; max-height: 70vh; overflow-y: auto; }
        .chat-item { display: flex; gap: 10px; padding: 12px; border-radius: 16px; text-decoration: none; color: inherit; transition: background .2s ease; background: #fff; margin-bottom: 10px; border: 1px solid transparent; }
        .chat-item:hover, .chat-item.active { background: rgba(243,197,71,.1); border-color: rgba(243,197,71,.3); }
        .chat-info { flex: 1; position: relative; }
        .chat-info h4 { margin-bottom: 4px; font-size: 14px; }
        .chat-info p { font-size: 12px; color: var(--muted); }
        .role-badge { display: inline-block; font-size: 10px; padding: 3px 8px; border-radius: 999px; background: rgba(243,197,71,.15); color: #b07c00; font-weight: 700; margin-top: 4px; }
        .chat-box { background: var(--panel); border: 1px solid var(--border); border-radius: 24px; display: flex; flex-direction: column; box-shadow: var(--shadow); min-height: 560px; }
        .chat-header { padding: 18px; border-bottom: 1px solid var(--border); }
        .chat-messages { padding: 18px; flex: 1; overflow-y: auto; max-height: 55vh; }
        .chat-footer { padding: 16px; border-top: 1px solid var(--border); display: flex; gap: 10px; }
        .chat-footer textarea { flex: 1; min-height: 90px; padding: 14px; border: 1px solid var(--border); border-radius: 16px; resize: vertical; background: whitesmoke; color: var(--text); }
        .chat-footer button { padding: 14px 18px; border: none; border-radius: 16px; background: #f3c547; color: #111; cursor: pointer; font-weight: 700; }
        .alert { margin: 16px 18px; color: #b00; background: rgba(255,77,77,.12); padding: 12px 16px; border-radius: 16px; }
        .unread-dot { position: absolute; top: 8px; right: 8px; background: #ef4444; color: #fff; font-size: 11px; font-weight: 700; min-width: 18px; height: 18px; border-radius: 999px; display: flex; align-items: center; justify-content: center; padding: 0 5px; }
        .global-unread { background: #ef4444; color: #fff; border-radius: 999px; font-size: 11px; font-weight: 700; padding: 2px 8px; margin-left: 8px; }
        @media (max-width: 900px) { .chat-container { grid-template-columns: 1fr; } .chat-sidebar { max-height: none; } }
    </style>
</head>
<body>
    <div class="container">
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <main class="main-content">
            <div id="header"></div>

            <h2>Messages <?php if ($globalUnread > 0): ?><span class="global-unread" id="globalUnreadBadge"><?= $globalUnread ?></span><?php endif; ?></h2>
            <div class="chat-container">
                <div class="chat-sidebar">
                    <?php if (empty($threads)): ?>
                        <p style="color:var(--muted);">No conversations yet. When clients book your services, you can chat with them here.</p>
                    <?php else: ?>
                        <?php foreach ($threads as $t): ?>
                            <?php $active = ($t['event_id'] === $selectedEventId && $t['user_id'] === $selectedUserId); ?>
                            <?php $unc = $unreadCounts[$t['event_id'] . '_' . $t['user_id']] ?? 0; ?>
                            <a href="MESSAGES.php?event_id=<?= $t['event_id'] ?>&user_id=<?= $t['user_id'] ?>" class="chat-item<?= $active ? ' active' : '' ?>">
                                <div class="chat-info">
                                    <h4><?= htmlspecialchars($t['name']) ?></h4>
                                    <p><?= htmlspecialchars($t['event_title']) ?> • <?= htmlspecialchars($t['event_date'] ?: 'No date') ?></p>
                                    <span class="role-badge"><?= htmlspecialchars($t['role']) ?></span>
                                    <?php if (!$active && $unc > 0): ?><span class="unread-dot"><?= $unc ?></span><?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="chat-box">
                    <?php if (!$selectedThreadName): ?>
                        <div class="chat-header"><h3>Select a conversation</h3></div>
                    <?php else: ?>
                        <div class="chat-header">
                            <h3>Chat with <?= htmlspecialchars($selectedThreadName) ?> <span class="role-badge"><?= htmlspecialchars($selectedThreadRole) ?></span></h3>
                            <small><?= htmlspecialchars($selectedThreadRole === 'Coordinator' ? 'Event coordinator' : 'Event owner') ?></small>
                        </div>
                        <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                        <div class="chat-messages">
                            <?php if (empty($messages)): ?>
                                <div class="chat-msg received">No messages yet. Start the conversation.</div>
                            <?php else: ?>
                                <?php foreach ($messages as $message): ?>
                                    <?php $sent = $message['sender_id'] === $supplierId; ?>
                                    <div class="chat-msg <?= $sent ? 'sent' : 'received' ?>" data-mid="<?= $message['message_id'] ?>">
                                        <strong><?= htmlspecialchars($message['full_name'] ?: ($sent ? 'You' : $selectedThreadName)) ?></strong><br>
                                        <?= nl2br(htmlspecialchars($message['body'])) ?>
                                        <?php if ($sent): ?>
                                            <?php if ($message['is_read']): ?>
                                                <span class="read-tick" title="Seen"><i class="fas fa-check-double"></i></span>
                                            <?php else: ?>
                                                <span class="unread-tick" title="Delivered"><i class="fas fa-check"></i></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <span class="meta"><?= htmlspecialchars($message['created_at']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <form class="chat-footer" method="POST" action="MESSAGES.php?event_id=<?= $selectedEventId ?>&user_id=<?= $selectedUserId ?>">
                            <input type="hidden" name="event_id" value="<?= $selectedEventId ?>">
                            <input type="hidden" name="recipient_id" value="<?= $selectedUserId ?>">
                            <textarea name="message" placeholder="Type your message..."></textarea>
                            <button type="submit"><i class="fas fa-paper-plane"></i> Send</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script>
        window.CHAT_EVENT_ID = <?= (int)$selectedEventId ?>;
        window.CHAT_OTHER_USER_ID = <?= (int)$selectedUserId ?>;
        window.CHAT_CURRENT_USER_ID = <?= (int)$supplierId ?>;
        window.CHAT_POLL_URL = '../api/messaging.php';
        window.CHAT_LAST_ID = <?= (int)$lastMessageId ?>;
        if (window.ChatPoll) {
            window.ChatPoll.start();
        }
    </script>
    <script src="../js/header.js"></script>
</body>
</html>
