<?php
require_once __DIR__ . '/../config/db.php';
require_role('supplier');
$pdo = db();

// Load a mobile-like supplier newsfeed with your current service posts and booking activity.
$feedItems = $pdo->query(
    "SELECT s.*, u.full_name, u.business_name
     FROM supplier_services s
     JOIN users u ON s.user_id = u.user_id
     ORDER BY s.created_at DESC
     LIMIT 10"
)->fetchAll();

$bookingItems = [];
if (function_exists('table_exists') && table_exists('bookings')) {
    $bookingItemsStmt = $pdo->prepare(
        "SELECT b.*, e.title AS event_title, s.name AS service_name, e.event_date
         FROM bookings b
         JOIN events e ON b.event_id = e.event_id
         JOIN supplier_services s ON b.service_id = s.service_id
         WHERE s.user_id = ?
         ORDER BY b.created_at DESC
         LIMIT 5"
    );
    $bookingItemsStmt->execute([$_SESSION['user_id']]);
    $bookingItems = $bookingItemsStmt->fetchAll();
}

$serviceCount = $pdo->prepare("SELECT COUNT(*) FROM supplier_services WHERE user_id = ?");
$serviceCount->execute([$_SESSION['user_id']]);
$serviceCount = $serviceCount->fetchColumn();

$pendingCount = 0;
$approvedCount = 0;
if (function_exists('table_exists') && table_exists('bookings')) {
    $pendingCountStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM bookings b JOIN supplier_services s ON b.service_id = s.service_id WHERE s.user_id = ? AND b.status = 'pending'"
    );
    $pendingCountStmt->execute([$_SESSION['user_id']]);
    $pendingCount = $pendingCountStmt->fetchColumn();

    $approvedCountStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM bookings b JOIN supplier_services s ON b.service_id = s.service_id WHERE s.user_id = ? AND b.status = 'accepted'"
    );
    $approvedCountStmt->execute([$_SESSION['user_id']]);
    $approvedCount = $approvedCountStmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Supplier Feed</title>
    <link rel="stylesheet" href="../css/supplier.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        .feed-header{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:28px;flex-wrap:wrap}
        .feed-title{max-width:760px}
        .feed-title h1{font-size:clamp(36px,5vw,60px);line-height:1.03;margin-bottom:12px}
        .feed-title p{color:rgba(255,255,255,.76);max-width:680px;line-height:1.75;font-size:16px}
        .feed-actions{display:flex;flex-wrap:wrap;gap:12px}
        .feed-actions button{padding:12px 24px;border-radius:14px;border:1px solid rgba(255,255,255,.12);background:rgba(243,197,71,.15);color:#fff;font-weight:700;cursor:pointer;transition:.25s}
        .feed-actions button:hover{background:rgba(243,197,71,.24);transform:translateY(-1px)}
        .feed-columns{display:grid;grid-template-columns:2.2fr 1fr;gap:26px}
        .feed-stream{display:grid;gap:22px}
        .post-card{background:rgba(255,255,255,.04);border:1px solid rgba(243,197,71,.12);border-radius:28px;padding:26px;box-shadow:0 24px 60px rgba(0,0,0,.2)}
        .post-card .meta{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px}
        .post-card .tag{display:inline-flex;padding:8px 14px;border-radius:999px;background:rgba(243,197,71,.14);color:var(--gold);font-weight:800;font-size:13px}
        .post-card h3{font-size:24px;margin-bottom:14px}
        .post-card p{color:#ccc;line-height:1.75}
        .post-bottom{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:24px;color:#d3c274;font-size:14px}
        .feed-summary{display:grid;gap:18px}
        .summary-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:26px;padding:22px}
        .summary-card h4{font-size:15px;color:var(--gold);letter-spacing:.12em;text-transform:uppercase;margin-bottom:14px}
        .summary-card p{font-size:34px;color:#fff;margin:0}
        .summary-card small{display:block;margin-top:10px;color:rgba(255,255,255,.6)}
        .mini-feed{display:grid;gap:16px}
        .mini-item{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:22px;padding:18px;display:grid;gap:6px}
        .mini-item strong{display:block;color:#fff}
        .mini-item span{color:#ccc;font-size:14px}
        .mini-item time{color:#aaa;font-size:12px}
        @media(max-width:960px){.feed-columns{grid-template-columns:1fr}}    
    </style>
</head>
<body>
    <div class="container">
        <?php require_once __DIR__ . '/sidebar.php'; ?>
        <main class="main-content">
            <div>
            <div id="header"></div>

            <header class="main-header">
                <div class="feed-header">
                    <div class="feed-title">
                        <p class="tag">Supplier News</p>
                        <h1>Your supplier newsfeed is live</h1>
                        <p>See your latest service updates, recent booking activity, and fast access to manage the supplier dashboard.</p>
                    </div>
                    <div class="feed-actions">
                        <button type="button" onclick="location.href='<?= route('supplier.dashboard') ?>'">Open Dashboard</button>
                        <button type="button" onclick="location.href='<?= route('supplier.services') ?>'">Manage Services</button>
                        <button type="button" onclick="location.href='<?= route('supplier.bookings') ?>'">Booking Requests</button>
                    </div>
                </div>
            </header>

            <div class="feed-columns">
                <section class="feed-stream">
                    <?php if (empty($feedItems)): ?>
                        <div class="post-card">
                            <h3>No service updates yet</h3>
                            <p>You can add services in your supplier dashboard and they will appear here as newsfeed posts for your customers to see.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($feedItems as $item): ?>
                            <article class="post-card">
                                <div class="meta">
                                    <span class="tag"><?= esc($item['category'] ?? 'Update') ?></span>
                                    <span><?= esc($item['business_name'] ?? $_SESSION['full_name']) ?></span>
                                </div>
                                <h3><?= esc($item['name']) ?></h3>
                                <p><?= esc($item['description'] ?? 'No details available.') ?></p>
                                <div class="post-bottom">
                                    <span>Price: ₱<?= number_format($item['price'] ?? 0, 2) ?></span>
                                    <span>Posted <?= date('M j, Y', strtotime($item['created_at'] ?? 'now')) ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>

                <aside class="feed-summary">
                    <div class="summary-card">
                        <h4>Active services</h4>
                        <p><?= number_format($serviceCount) ?></p>
                        <small>Total services you currently publish</small>
                    </div>
                    <div class="summary-card">
                        <h4>Pending requests</h4>
                        <p><?= number_format($pendingCount) ?></p>
                        <small>New client booking requests</small>
                    </div>
                    <div class="summary-card">
                        <h4>Accepted bookings</h4>
                        <p><?= number_format($approvedCount) ?></p>
                        <small>Confirmed client requests</small>
                    </div>
                    <div class="mini-feed">
                        <h4 style="font-size:16px;color:var(--gold);text-transform:uppercase;letter-spacing:.14em;margin-bottom:12px">Recent activity</h4>
                        <?php if (empty($bookingItems)): ?>
                            <div class="mini-item">
                                <strong>No recent booking activity</strong>
                                <span>Once clients request your services, updates will appear here.</span>
                            </div>
                        <?php else: ?>
                            <?php foreach ($bookingItems as $booking): ?>
                                <div class="mini-item">
                                    <strong><?= esc($booking['event_title'] ?? 'Booking request') ?></strong>
                                    <span><?= esc($booking['service_name'] ?? 'Service') ?> • <?= esc($booking['status']) ?></span>
                                    <time><?= date('M j, Y', strtotime($booking['event_date'] ?? $booking['created_at'])) ?></time>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </main>
    </div>

<script src="../js/header.js"></script>
</body>
</html>


