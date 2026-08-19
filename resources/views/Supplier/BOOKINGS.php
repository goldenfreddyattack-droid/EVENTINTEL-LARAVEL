<?php
require_once __DIR__ . '/../config/db.php';
require_role('supplier');

$pdo = db();

// Handle form submissions (decline notes / cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'], $_POST['service'])) {
    $action = in_array($_POST['action'], ['accepted', 'declined', 'pending', 'paid']) ? $_POST['action'] : 'pending';
    $eventId = intval($_POST['id']);
    $service = in_array($_POST['service'], ['venue', 'clothes', 'catering', 'host', 'photographer', 'soundsnlights']) ? $_POST['service'] : '';

    if ($service) {
        $map = [
            'venue' => ['column' => 'venue_name', 'status' => 'venue_status'],
            'clothes' => ['column' => 'clothes', 'status' => 'clothes_status'],
            'catering' => ['column' => 'catering', 'status' => 'catering_status'],
            'host' => ['column' => 'host', 'status' => 'host_status'],
            'photographer' => ['column' => 'photographer', 'status' => 'photographer_status'],
            'soundsnlights' => ['column' => 'soundsnlights', 'status' => 'soundsnlights_status']
        ];

        $colInfo = $map[$service];
        $statusColumn = $colInfo['status'];
        $nameColumn = $colInfo['column'];

        if ($action === 'accepted') {
            $newStatus = 'accepted';
        } elseif ($action === 'paid') {
            $newStatus = 'Paid';
        } else {
            $newStatus = $action;
        }

        if ($newStatus === 'declined') {
            $declineNote = isset($_POST['decline_note']) ? trim($_POST['decline_note']) : '';

            // Map to per-service note column (do not remove assigned name)
            $noteMap = [
                'venue' => 'venue_note',
                'clothes' => 'clothes_note',
                'catering' => 'catering_note',
                'host' => 'host_note',
                'photographer' => 'photographer_note',
                'soundsnlights' => 'soundsnlights_note'
            ];

            $noteColumn = $noteMap[$service] ?? 'decline_note';
            $stmt = $pdo->prepare("UPDATE events SET $statusColumn = ?, $noteColumn = ? WHERE event_id = ?");
            $stmt->execute([$newStatus, $declineNote, $eventId]);
        } else {
            // accepted or pending
            $pdo->prepare("UPDATE events SET $statusColumn = ? WHERE event_id = ?")->execute([$newStatus, $eventId]);
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// Fetch all services for this supplier from supplier_services table
$servicesQuery = "
    SELECT service_id, category, name 
    FROM supplier_services 
    WHERE user_id = ?
    ORDER BY category
";
$servicesStmt = $pdo->prepare($servicesQuery);
$servicesStmt->execute([$_SESSION['user_id']]);
$services = $servicesStmt->fetchAll();

// Map category to column and status column names
$categoryMap = [
    'Venue' => ['column' => 'venue_name', 'status' => 'venue_status', 'key' => 'venue'],
    'Clothing' => ['column' => 'clothes', 'status' => 'clothes_status', 'key' => 'clothes'],
    'Catering' => ['column' => 'catering', 'status' => 'catering_status', 'key' => 'catering'],
    'Host' => ['column' => 'host', 'status' => 'host_status', 'key' => 'host'],
    'Photographer' => ['column' => 'photographer', 'status' => 'photographer_status', 'key' => 'photographer'],
    'Sounds & Lights' => ['column' => 'soundsnlights', 'status' => 'soundsnlights_status', 'key' => 'soundsnlights']
];

// Build booking rows for each service
$bookingRows = [];
foreach ($services as $service) {
    $category = $service['category'];
    $serviceName = $service['name'];
    $serviceId = $service['service_id'];
    
    if (isset($categoryMap[$category])) {
        $colInfo = $categoryMap[$category];
        $column = $colInfo['column'];
        $statusColumn = $colInfo['status'];
        $serviceKey = $colInfo['key'];
        
        // Query events for this service
        $eventQuery = "
            SELECT 
                e.event_id, 
                e.title, 
                e.event_type, 
                e.event_date, 
                e.budget,
                e.$column,
                e.$statusColumn,
                e.payment_method,
                u.full_name as client_name
            FROM events e
            JOIN users u ON e.user_id = u.user_id
            WHERE e.$column = ?
            ORDER BY e.event_date DESC
        ";
        
        $eventStmt = $pdo->prepare($eventQuery);
        $eventStmt->execute([$serviceName]);
        $events = $eventStmt->fetchAll();
        
        foreach ($events as $event) {
            $bookingRows[] = [
                'service_id' => $serviceId,
                'event_id' => $event['event_id'],
                'title' => $event['title'],
                'event_type' => $event['event_type'],
                'event_date' => $event['event_date'],
                'budget' => $event['budget'],
                'client_name' => $event['client_name'],
                'service' => $category,
                'service_key' => $serviceKey,
                'status' => $event[$statusColumn],
                'payment_method' => $event['payment_method'] ?? 'cash',
                'business_name' => $serviceName
            ];
        }
    }
}

$statusFilter = $_GET['status'] ?? 'all';

if ($statusFilter !== 'all') {
    $bookingRows = array_filter($bookingRows, function ($row) use ($statusFilter) {
        if ($statusFilter === 'accepted') {
            return $row['status'] === 'accepted' || $row['status'] === 'Paid';
        }
        if ($statusFilter === 'Paid') {
            return $row['status'] === 'Paid';
        }
        return $row['status'] === $statusFilter;
    });
}

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 15;
$totalRows = count($bookingRows);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$paginatedRows = array_slice($bookingRows, $offset, $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings</title>
    <link rel="stylesheet" href="../css/supplier.css?v=2">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .filter-btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid #d0d0d0;
            background: #f3f3f3;
            color: #333;
            font-weight: 600;
            display: inline-block;
        }

        .filter-btn-active {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid #d5a200;
            background: linear-gradient(135deg, #ffe27d, #f3c547);
            color: #111;
            font-weight: 700;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <main class="main-content">
            <div id="header"></div>

            <section class="booking-request">
                <h2>All Bookings</h2>
                <div style="overflow-x:auto;">
                    <div style="margin-bottom:20px;display:flex;gap:10px;flex-wrap:wrap;">

    <a href="?status=all&page=1"
       class="<?= $statusFilter=='all' ? 'filter-btn-active' : 'filter-btn' ?>">
        All
    </a>

    <a href="?status=pending&page=1"
       class="<?= $statusFilter=='pending' ? 'filter-btn-active' : 'filter-btn' ?>">
        Pending
    </a>

    <a href="?status=accepted&page=1"
       class="<?= $statusFilter=='accepted' ? 'filter-btn-active' : 'filter-btn' ?>">
        Accepted
    </a>

    <a href="?status=declined&page=1"
       class="<?= $statusFilter=='declined' ? 'filter-btn-active' : 'filter-btn' ?>">
        Declined
    </a>

    <a href="?status=Payment%20Pending&page=1"
       class="<?= $statusFilter=='Payment Pending' ? 'filter-btn-active' : 'filter-btn' ?>">
        Payment Pending
    </a>

    <a href="?status=Paid&page=1"
       class="<?= $statusFilter=='Paid' ? 'filter-btn-active' : 'filter-btn' ?>">
        Paid
    </a>

</div>
                    <table class="booking-table">
                        <thead>
                            <tr>
                                <th>Supplier/Business</th>
                                <th>Type of Event</th>
                                <th>Service</th>
                                <th>Client Name</th>
                                <th>Date</th>
                                <th>Budget</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($paginatedRows)): ?>
                            <tr>
                                <td colspan="9" style="text-align:center;padding:40px;color:var(--muted);">No bookings yet</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($paginatedRows as $r): ?>
                            <tr>
                                <td><?= esc($r['business_name']) ?></td>
                                <td><?= esc($r['event_type'] ?? 'N/A') ?></td>
                                <td><?= esc($r['service']) ?></td>
                                <td><?= esc($r['client_name'] ?? 'N/A') ?></td>
                                <td><span class="date"><?= esc($r['event_date'] ?? 'TBD') ?></span></td>
                                <td>₱<?= number_format($r['budget'] ?? 0) ?></td>
                                <td>
                                    <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;<?= $r['payment_method']==='online' ? 'background:rgba(100,150,255,.15);color:#6496ff;' : 'background:rgba(76,175,80,.15);color:#4caf50;' ?>">
                                        <i class="fas <?= $r['payment_method']==='online' ? 'fa-credit-card' : 'fa-money-bill-wave' ?>"></i>
                                        <?= esc(ucfirst($r['payment_method'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="
                                        display:inline-block;
                                        padding:6px 14px;
                                        border-radius:999px;
                                        font-size:12px;
                                        font-weight:700;
                                        <?= $r['status']==='accepted' ? 'background:rgba(100,255,150,.15);color:#64ff96;' : ($r['status']==='declined' ? 'background:rgba(255,100,100,.15);color:#ff6464;' : ($r['status']==='Payment Pending' ? 'background:rgba(255,215,0,.15);color:#d4a017;' : ($r['status']==='Paid' ? 'background:rgba(76,175,80,.15);color:#388e3c;' : 'background:rgba(243,197,71,.15);color:var(--gold);'))) ?>
                                    ">
                                        <?= esc(ucfirst(str_replace('_', ' ', $r['status']))) ?>
                                    </span>
                                </td>
                                    </span>
                                </td>
                                <td>

                                    <?php if ($r['status'] === 'pending'): ?>

    <a href="#" onclick="acceptBooking(<?= $r['event_id'] ?>, '<?= $r['service_key'] ?>'); return false;"
       class="accept-btn"
       style="text-decoration:none;display:inline-block;margin-right:6px;">
        Accept
    </a>

    <a href="#" onclick="openDeclineModal(<?= $r['event_id'] ?>, '<?= $r['service_key'] ?>'); return false;"
       class="decline-btn"
       style="text-decoration:none;display:inline-block;">
        Decline
    </a>

<?php elseif ($r['status'] === 'accepted'): ?>

<a href="#" onclick="acceptSupplierPayment(<?= $r['event_id'] ?>, '<?= $r['service_key'] ?>'); return false;"
   class="accept-btn"
   style="text-decoration:none;display:inline-block;">
    Receive Payment
</a>

<?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <?php if ($totalPages > 1): ?>
                    <div style="display:flex;justify-content:center;gap:8px;margin-top:20px;flex-wrap:wrap;">
                        <?php if ($page > 1): ?>
                        <a href="?status=<?= urlencode($statusFilter) ?>&page=<?= $page - 1 ?>" class="filter-btn">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?status=<?= urlencode($statusFilter) ?>&page=<?= $i ?>" class="<?= $i === $page ? 'filter-btn-active' : 'filter-btn' ?>"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <a href="?status=<?= urlencode($statusFilter) ?>&page=<?= $page + 1 ?>" class="filter-btn">Next</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
        <script src="../js/header.js"></script>

        <!-- Decline modal -->
        <div id="declineModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);align-items:center;justify-content:center;z-index:9999;">
                <div style="background:#fff;max-width:600px;width:90%;margin:auto;padding:20px;border-radius:8px;box-shadow:0 6px 24px rgba(0,0,0,.2);">
                        <h3 style="margin-top:0;margin-bottom:8px;">Reason for declining</h3>
                        <p style="margin-top:0;margin-bottom:8px;color:var(--muted);font-size:14px;">Optionally provide a short note to send to the client.</p>
                        <textarea id="declineNote" rows="6" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:14px;"></textarea>
                        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;">
                                <button id="declineCancelBtn" style="padding:8px 14px;border-radius:8px;background:#f3f3f3;border:1px solid #ccc;">Cancel</button>
                                <button id="declineSendBtn" style="padding:8px 14px;border-radius:8px;background:#d9534f;color:#fff;border:0;">Send</button>
                        </div>
                </div>
        </div>

        <script>
        const currentStatus = '<?= addslashes($statusFilter) ?>';
        let _declineEventId = null;
        let _declineServiceKey = null;

        function openDeclineModal(eventId, serviceKey) {
                console.log('openDeclineModal called');
                _declineEventId = eventId;
                _declineServiceKey = serviceKey;
                document.getElementById('declineNote').value = '';
                document.getElementById('declineModal').style.display = 'flex';
        }

        function closeDeclineModal() {
                console.log('closeDeclineModal called');
                document.getElementById('declineModal').style.display = 'none';
                _declineEventId = null;
                _declineServiceKey = null;
        }

        const declineSendBtn = document.getElementById('declineSendBtn');
        const declineCancelBtn = document.getElementById('declineCancelBtn');
        
        if (!declineSendBtn) console.error('declineSendBtn not found');
        if (!declineCancelBtn) console.error('declineCancelBtn not found');
        
        if (declineSendBtn) {
                declineSendBtn.addEventListener('click', function () {
                console.log('Decline send clicked');
                if (!_declineEventId || !_declineServiceKey) return closeDeclineModal();
                const note = document.getElementById('declineNote').value || '';
                const body = `action=declined&id=${encodeURIComponent(_declineEventId)}&service=${encodeURIComponent(_declineServiceKey)}&decline_note=${encodeURIComponent(note)}&status=${encodeURIComponent(currentStatus)}`;
                console.log('Sending decline:', body);

                fetch(window.location.pathname, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body
                }).then(r => {
                        console.log('Decline response:', r.status);
                        if (r.redirected) {
                                window.location = r.url;
                        } else {
                                location.reload();
                        }
                }).catch(err => {
                        console.error(err);
                        alert('Failed to send decline note: ' + err.message);
                });
        });
}

if (declineCancelBtn) {
        declineCancelBtn.addEventListener('click', function () {
                console.log('Decline cancel clicked');
                // On cancel, revert status to pending on server just in case
                if (!_declineEventId || !_declineServiceKey) return closeDeclineModal();
                const body = `action=pending&id=${encodeURIComponent(_declineEventId)}&service=${encodeURIComponent(_declineServiceKey)}&status=${encodeURIComponent(currentStatus)}`;
                console.log('Sending cancel:', body);
                fetch(window.location.pathname, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body
                }).then(r => {
                        console.log('Cancel response:', r.status);
                        closeDeclineModal();
                        if (r.redirected) {
                                window.location = r.url;
                        } else {
                                location.reload();
                        }
                }).catch(err => {
                        console.error(err);
                        closeDeclineModal();
                });
        });
}

function acceptBooking(eventId, serviceType) {
            console.log('acceptBooking called:', eventId, serviceType);
            const body = `action=accepted&id=${encodeURIComponent(eventId)}&service=${encodeURIComponent(serviceType)}&status=${encodeURIComponent(currentStatus)}`;
            console.log('Sending:', body);

            fetch(window.location.pathname, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            }).then(r => {
                console.log('Response status:', r.status);
                location.reload();
            }).catch(error => {
                console.error('Error:', error);
                alert('Failed to accept booking: ' + error.message);
            });
        }

        function acceptSupplierPayment(eventId, serviceType) {
            console.log('acceptSupplierPayment called:', eventId, serviceType);
            const body = `action=paid&id=${encodeURIComponent(eventId)}&service=${encodeURIComponent(serviceType)}&status=${encodeURIComponent(currentStatus)}`;
            console.log('Sending:', body);

            fetch(window.location.pathname, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            }).then(r => {
                console.log('Response status:', r.status);
                location.reload();
            }).catch(error => {
                console.error('Error:', error);
                alert('Failed to record payment: ' + error.message);
            });
        }
        </script>
</body>
</html>
