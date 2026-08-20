<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/messaging_helpers.php';
require_role('client');

$pdo = db();
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
        $stmt = $pdo->prepare('SELECT event_id FROM events WHERE event_id = ? AND user_id = ?');
        $stmt->execute([$selectedEventId, $_SESSION['user_id']]);
        $event = $stmt->fetch();

        if ($event) {
            try {
                firebase_save_message($selectedEventId, $_SESSION['user_id'], $selectedUserId ?: null, $messageText, $_SESSION['full_name'] ?? 'Client');
                header('Location: message.php?event_id=' . $selectedEventId . '&user_id=' . $selectedUserId);
                exit;
            } catch (RuntimeException $e) {
                $error = 'Message could not be sent. Firebase rejected the message: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            }
        }

        $error = $error ?: 'You cannot send a message for this event.';
    }
}

// Build a thread list: for each event of this client, list the coordinator and each assigned supplier.
$threads = [];

$eventsStmt = $pdo->prepare(
    'SELECT e.event_id, e.title, e.event_date, e.coordinator, e.venue_name, e.clothes, e.catering, e.host, e.photographer, e.soundsnlights
     FROM events e
     WHERE e.user_id = ?
     ORDER BY e.created_at DESC'
);
$eventsStmt->execute([$_SESSION['user_id']]);
$clientEvents = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($clientEvents as $evt) {
    // Coordinator thread
    if (!empty($evt['coordinator'])) {
        $coordStmt = $pdo->prepare('SELECT user_id, full_name, business_name FROM users WHERE role = ? AND full_name = ? LIMIT 1');
        $coordStmt->execute(['coordinator', $evt['coordinator']]);
        $coord = $coordStmt->fetch();
        if ($coord) {
            $threads[] = [
                'event_id' => (int)$evt['event_id'],
                'event_title' => $evt['title'] ?: 'Untitled Event',
                'event_date' => $evt['event_date'],
                'user_id' => (int)$coord['user_id'],
                'name' => $coord['business_name'] ?: $coord['full_name'],
                'role' => 'Coordinator',
            ];
        }
    }

    // Supplier threads
    $serviceMap = [
        'venue_name' => 'Venue',
        'clothes' => 'Clothing',
        'catering' => 'Catering',
        'host' => 'Host',
        'photographer' => 'Photographer',
        'soundsnlights' => 'Sounds & Lights',
    ];
    foreach ($serviceMap as $col => $label) {
        $serviceName = trim((string)($evt[$col] ?? ''));
        if ($serviceName === '') continue;
        $supStmt = $pdo->prepare(
            'SELECT s.user_id, u.full_name, u.business_name FROM supplier_services s
             JOIN users u ON s.user_id = u.user_id
             WHERE LOWER(s.name) = LOWER(?) ORDER BY s.service_id DESC LIMIT 1'
        );
        $supStmt->execute([$serviceName]);
        $sup = $supStmt->fetch();
        if ($sup) {
            $threads[] = [
                'event_id' => (int)$evt['event_id'],
                'event_title' => $evt['title'] ?: 'Untitled Event',
                'event_date' => $evt['event_date'],
                'user_id' => (int)$sup['user_id'],
                'name' => $sup['business_name'] ?: $sup['full_name'],
                'role' => $label,
            ];
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

// If event selected but no user selected, pick coordinator or first thread of that event
if ($selectedEventId > 0 && $selectedUserId === 0) {
    foreach ($threads as $t) {
        if ($t['event_id'] === $selectedEventId) {
            $selectedUserId = $t['user_id'];
            break;
        }
    }
}

// Determine selected thread name/role
$selectedThreadName = '';
$selectedThreadRole = '';
foreach ($threads as $t) {
    if ($t['event_id'] === $selectedEventId && $t['user_id'] === $selectedUserId) {
        $selectedThreadName = $t['name'];
        $selectedThreadRole = $t['role'];
        break;
    }
}

// Load messages for the selected thread (1:1 between this client and recipient)
$messages = [];
if ($selectedEventId > 0) {
    $evtCheck = $pdo->prepare('SELECT event_id FROM events WHERE event_id = ? AND user_id = ?');
    $evtCheck->execute([$selectedEventId, $_SESSION['user_id']]);
    if ($evtCheck->fetch()) {
        if ($selectedUserId > 0) {
            $messages = firebase_get_messages_for_thread($selectedEventId, $_SESSION['user_id'], $selectedUserId);
            foreach ($messages as &$message) {
                $senderId = (int)($message['sender_id'] ?? 0);
                $message['full_name'] = $message['sender_name'] ?? ($senderId === $_SESSION['user_id'] ? ($_SESSION['full_name'] ?? 'You') : $selectedThreadName);
                if (isset($message['read_by']) && is_array($message['read_by']) && isset($message['read_by'][(string)$_SESSION['user_id']])) {
                    $message['is_read'] = true;
                }
            }
            unset($message);
            mark_messages_read($pdo, $selectedEventId, $_SESSION['user_id'], $selectedUserId);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EventIntel - Messages</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<?php require_once __DIR__ . '/../../includes/chat_ui.php'; ?>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif}
body{background:#ffffff;color:#222;min-height:100vh}
.container{max-width:1600px;margin:auto;padding:6px 48px 40px;display:flex;flex-direction:column;gap:24px}
.navbar{width:100%;padding:12px 0 24px;display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap}
.logo{font-size:26px;font-weight:800;color:#f3c547;letter-spacing:1px}
.nav-links{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.nav-links button{padding:8px 18px;border-radius:12px;border:1px solid rgba(255,215,0,.35);background:rgba(255,255,255,.55);color:#222;font-size:14px;cursor:pointer;transition:.3s ease}
.nav-links button:hover,.nav-links .active{background:linear-gradient(to right,#ffe17a,#d4a017);color:black;box-shadow:0 0 14px rgba(255,215,0,.12)}
.profile-btn{width:44px;height:44px;border-radius:50%;border:1px solid rgba(255,215,0,.30);background:#fff;display:flex;align-items:center;justify-content:center;color:#f3c547}
.chat-container{display:grid;grid-template-columns:320px 1fr;gap:20px}
.sidebar{background:#fff;border:1px solid rgba(255,215,0,.12);border-radius:24px;padding:16px;overflow-y:auto;box-shadow:0 6px 16px rgba(0,0,0,.08);max-height:75vh}
.search{margin-bottom:15px}
.search input{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,215,0,.15);background:#fafafa;color:#222}
.chat-item{display:flex;gap:10px;padding:12px;border-radius:16px;text-decoration:none;color:inherit;transition:.2s;background:#fff;margin-bottom:10px;border:1px solid transparent}
.chat-item:hover,.chat-item.active{background:rgba(255,215,0,.1);border-color:rgba(255,215,0,.3)}
.chat-info{flex:1}
.chat-info h4{font-size:14px;margin-bottom:4px}
.chat-info p{font-size:12px;color:#666}
.role-badge{display:inline-block;font-size:10px;padding:3px 8px;border-radius:999px;background:rgba(243,197,71,.15);color:#b07c00;font-weight:700;margin-top:4px}
.chat-box{background:#fff;border:1px solid rgba(255,215,0,.12);border-radius:24px;display:flex;flex-direction:column;box-shadow:0 6px 16px rgba(0,0,0,.08);min-height:560px}
.chat-header{padding:18px;border-bottom:1px solid rgba(255,215,0,.12)}
.messages{padding:18px;flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:14px;max-height:60vh}
.message{max-width:70%;padding:14px 18px;border-radius:20px;font-size:14px;line-height:1.5}
.message.sent{align-self:flex-end;background:linear-gradient(135deg,#fff2ab,#f3c547,#c99208);color:#111}
.message.received{align-self:flex-start;background:#f9f6ec;color:#222;border:1px solid rgba(255,215,0,.25)}
.chat-footer{padding:16px;border-top:1px solid rgba(255,215,0,.12);display:flex;gap:10px}
.chat-footer textarea{flex:1;min-height:90px;padding:14px;border:1px solid rgba(255,215,0,.15);border-radius:16px;resize:vertical}
.chat-footer button{padding:14px 18px;border:none;border-radius:16px;background:#f3c547;color:#111;cursor:pointer;font-weight:700}
.alert{margin:16px 18px;color:#b00;background:rgba(255,77,77,.12);padding:12px 16px;border-radius:16px}
@media (max-width:900px){.chat-container{grid-template-columns:1fr}.sidebar{max-height:none}}
</style>
</head>
<body>
<div class="container">
<div class="navbar">
<div class="logo">EventIntel</div>
<div class="nav-links">
<button onclick="window.location.href='homepage.php'">Home</button>
<button onclick="window.location.href='createevent.php'">Create Event</button>
<button onclick="window.location.href='yourevents.php'">Your Events</button>
<button onclick="window.location.href='recommendation.php'">Recommendations</button>
<button onclick="window.location.href='newsfeed.php'">Newsfeed</button>
<button class="profile-btn" type="button" aria-label="Profile" title="Profile" onclick="window.location.href='profile.php'">
  <i class="fas fa-user"></i>
</button>
</div>
</div>
<div class="chat-container">
<div class="sidebar">
<div class="search"><input placeholder="Search chats..." readonly></div>
<?php if (empty($threads)): ?>
    <p style="color:#666;">No conversations yet. Once you book suppliers or a coordinator, you can chat with them here.</p>
<?php else: ?>
    <?php foreach ($threads as $t): ?>
        <?php $active = ($t['event_id'] === $selectedEventId && $t['user_id'] === $selectedUserId); ?>
        <a href="message.php?event_id=<?= $t['event_id'] ?>&user_id=<?= $t['user_id'] ?>" class="chat-item<?= $active ? ' active' : '' ?>">
            <div class="chat-info">
                <h4><?= htmlspecialchars($t['name']) ?></h4>
                <p><?= htmlspecialchars($t['event_title']) ?> • <?= htmlspecialchars($t['event_date'] ?: 'No date') ?></p>
                <span class="role-badge"><?= htmlspecialchars($t['role']) ?></span>
            </div>
        </a>
    <?php endforeach; ?>
<?php endif; ?>
</div>
<div class="chat-box">
    <?php if (!$selectedThreadName): ?>
        <div class="chat-header"><h3>Select a conversation to view messages</h3></div>
    <?php else: ?>
        <div class="chat-header">
            <h3>Chat with <?= htmlspecialchars($selectedThreadName) ?> <span class="role-badge"><?= htmlspecialchars($selectedThreadRole) ?></span></h3>
            <small><?= htmlspecialchars($selectedThreadRole === 'Coordinator' ? 'Event coordination' : 'Service provider') ?></small>
        </div>
        <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="messages chat-messages">
            <?php if (empty($messages)): ?>
                <div class="message received">No messages yet. Start the conversation.</div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <?php $sent = $message['sender_id'] === $_SESSION['user_id']; ?>
                    <div class="message <?= $sent ? 'sent' : 'received' ?>" data-mid="<?= (int)$message['message_id'] ?>">
                        <strong><?= htmlspecialchars($message['full_name'] ?: ($sent ? 'You' : $selectedThreadName)) ?></strong><br>
                        <?= nl2br(htmlspecialchars($message['body'])) ?><br>
                        <small style="display:block;margin-top:8px;color:#777;"><?= htmlspecialchars($message['created_at']) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
<form class="chat-footer" method="POST" action="message.php?event_id=<?= $selectedEventId ?>&user_id=<?= $selectedUserId ?>">
            <input type="hidden" name="event_id" value="<?= $selectedEventId ?>">
            <input type="hidden" name="recipient_id" value="<?= $selectedUserId ?>">
            <textarea name="message" placeholder="Type your message..."></textarea>
            <button type="submit">Send</button>
        </form>
    <?php endif; ?>
</div>
</div>
</div>
<script>
    window.CHAT_EVENT_ID = <?= (int)$selectedEventId ?>;
    window.CHAT_OTHER_USER_ID = <?= (int)$selectedUserId ?>;
    window.CHAT_CURRENT_USER_ID = <?= (int)$_SESSION['user_id'] ?>;
    window.CHAT_POLL_URL = '../../api/messaging.php';
    window.CHAT_LAST_ID = <?= !empty($messages) ? (int)end($messages)['message_id'] : 0 ?>;
    if (window.ChatPoll) {
        window.ChatPoll.start();
    }
</script>
</body>
</html>

