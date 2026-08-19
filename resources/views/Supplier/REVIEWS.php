<?php
require_once __DIR__ . '/../config/db.php';
require_role('supplier');

$pdo = db();
$reviewRows = [];

if (function_exists('table_exists') && table_exists('reviews')) {
    $reviews = $pdo->prepare("
        SELECT r.*, e.title as event_title, s.name as service_name, u.full_name as reviewer_name 
        FROM reviews r 
        JOIN events e ON r.event_id = e.event_id 
        JOIN supplier_services s ON r.service_id = s.service_id 
        JOIN users u ON r.user_id = u.user_id
        WHERE s.user_id = ? 
        ORDER BY r.created_at DESC
    ");
    $reviews->execute([$_SESSION['user_id']]);
    $reviewRows = $reviews->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REVIEWS</title>
    <link rel="stylesheet" href="../css/supplier.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container">
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <main class="main-content">
            <div id="header"></div>

            <section class="reviews-page">
                <h2>Customer Reviews</h2>

                <?php if (empty($reviewRows)): ?>
                <div class="review-card" style="text-align:center;padding:60px 40px;">
                    <i class="fas fa-star" style="font-size:48px;color:var(--gold);margin-bottom:16px;"></i>
                    <h3>No reviews yet</h3>
                    <p>Reviews will appear here when customers leave feedback</p>
                </div>
                <?php else: ?>
                <?php foreach ($reviewRows as $rev): ?>
                <div class="review-card">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                        <div>
                            <h3><?= esc($rev['reviewer_name'] ?? 'Anonymous') ?></h3>
                            <p style="font-size:13px;color:var(--muted);margin:4px 0;"><?= esc($rev['event_title'] ?? 'Event') ?> • <?= esc($rev['service_name'] ?? 'Service') ?></p>
                        </div>
                        <span class="stars"><?= str_repeat('⭐', $rev['rating'] ?? 5) ?></span>
                    </div>
                    <p><?= esc($rev['comment'] ?? 'No comment provided') ?></p>
                    <p style="font-size:12px;color:var(--muted);margin-top:12px;">
                        <i class="fas fa-clock"></i> <?= esc($rev['created_at'] ?? '') ?>
                    </p>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="../js/header.js"></script>
</body>
</html>
