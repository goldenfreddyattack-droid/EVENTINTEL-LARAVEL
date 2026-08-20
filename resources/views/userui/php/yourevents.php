<?php require_once __DIR__ . '/../../config/db.php'; require_role('client');

$pdo = db();
$perPage = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$statusFilter = $_GET['status'] ?? 'all';
$allowedStatuses = ['all', 'pending', 'planning', 'ongoing', 'completed', 'cancelled'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
  $statusFilter = 'all';
}

$where = ['user_id = :user_id'];
$params = [':user_id' => $_SESSION['user_id']];
if ($statusFilter !== 'all') {
  $where[] = 'status = :status';
  $params[':status'] = $statusFilter;
}

$countSql = 'SELECT COUNT(*) AS total FROM events WHERE ' . implode(' AND ', $where);
$countStmt = $pdo->prepare($countSql);
foreach ($params as $name => $value) {
  $countStmt->bindValue($name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$countStmt->execute();
$totalEvents = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalEvents / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = 'SELECT * FROM events WHERE ' . implode(' AND ', $where) . ' ORDER BY event_date DESC, event_time DESC LIMIT :limit OFFSET :offset';
$stmt = $pdo->prepare($sql);
foreach ($params as $name => $value) {
  $stmt->bindValue($name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$events = $stmt->fetchAll();

$statsStmt = $pdo->prepare('SELECT status, COUNT(*) AS total FROM events WHERE user_id = ? GROUP BY status');
$statsStmt->execute([$_SESSION['user_id']]);
$statusCounts = [
  'all' => 0,
  'pending' => 0,
  'planning' => 0,
  'ongoing' => 0,
  'completed' => 0,
  'cancelled' => 0,
];
foreach ($statsStmt->fetchAll() as $row) {
  $statusCounts[$row['status']] = (int)$row['total'];
}
$statusCounts['all'] = array_sum([$statusCounts['pending'], $statusCounts['planning'], $statusCounts['ongoing'], $statusCounts['completed'], $statusCounts['cancelled']]);
?><!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventIntel - Your Events</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
  }

  body {
    background: #ffffff;
    color: #222;
    min-height: 100vh;
  }

  .container {
    max-width: 1600px;
    margin: auto;
    padding: 6px 48px 40px;
  }

  /* NAVBAR */
  .navbar {
    width: 100%;
    padding: 12px 0 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
  }

  .logo {
    font-size: 26px;
    font-weight: 800;
    color: #f3c547;
    letter-spacing: 1px;
  }

  .nav-links {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .nav-links button {
    padding: 8px 18px;
    border-radius: 12px;
    border: 1px solid rgba(212,160,23,0.35);
    background: rgba(255,255,255,0.55);
    color: #222;
    font-size: 14px;
    cursor: pointer;
    transition: 0.3s ease;
  }

  .nav-links button:hover,
  .nav-links .active {
    background: linear-gradient(to right, #ffe17a, #d4a017);
    color: black;
    box-shadow: 0 0 14px rgba(255, 215, 0, 0.12);
  }

  .profile-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 1px solid rgba(255, 215, 0, 0.30);
    background: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #f3c547;
    cursor: pointer;
  }

  /* HEADER */
  .header {
    margin-bottom: 30px;
  }

  .header h1 {
    font-size: 48px;
    margin-bottom: 10px;
    color:#111;
  }

  .header p {
    color: #555;
  }

  /* GRID */
  .events-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
  }

  .event-card {
    background: #fff;
    border: 1px solid rgba(255,215,0,.12);
    border-radius: 26px;
    overflow: hidden;
    transition: 0.3s ease;
    box-shadow: 0 8px 20px rgba(0,0,0,.08);
  }

  .event-card:hover {
    transform: translateY(-6px);
    border-color: rgba(255,215,0,.3);
    box-shadow: 0 18px 40px rgba(243,197,71,.12);
  }

  .event-img {
    height: 200px;
    overflow: hidden;
  }

  .event-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(.95);
    transition: .3s;
  }

  .event-card:hover img {
    transform: scale(1.05);
    filter: brightness(1);
  }

  .event-content {
    padding: 20px;
  }

  .event-content h3 {
    margin-bottom: 10px;
    font-size: 20px;
    color:#111;
  }

  .event-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 14px;
    color: #666;
    margin-bottom: 15px;
  }

  .event-info i {
    color: #f3c547;
    margin-right: 6px;
  }

  .status {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    margin-bottom: 14px;
  }

  .status.upcoming {
    background: rgba(255,215,0,.12);
    color: #f3c547;
  }

  .status.completed {
    background: rgba(100,255,150,.12);
    color: #2a9d6f;
  }

  .card-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .btn {
    flex: 1 1 120px;
    min-width: 120px;
    padding: 12px 14px;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    font-size: 13px;
    font-weight: 700;
  }

  .btn-primary {
    background: linear-gradient(135deg, #fff2ab, #f3c547, #c99208);
    color: #111;
  }

  .btn-outline {
    background: transparent;
    border: 1px solid rgba(255,215,0,.3);
    color: #f3c547;
  }

  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(243,197,71,.2);
  }

  /* MODAL STYLES */
  .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
  }

  .modal.show {
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .modal-content {
    background-color: #fff;
    padding: 30px;
    border-radius: 20px;
    width: 90%;
    max-width: 700px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    max-height: 80vh;
    overflow-y: auto;
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .modal-header h2 {
    font-size: 24px;
    color: #111;
  }

  .close-btn {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #999;
  }

  .close-btn:hover {
    color: #111;
  }

  .service-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
  }

  .service-table thead {
    background-color: #fff2ab;
    color: #111;
  }

  .service-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #f3c547;
  }

  .service-table td {
    padding: 12px;
    border-bottom: 1px solid rgba(255, 215, 0, 0.2);
  }

  .service-table tbody tr:hover {
    background-color: rgba(255, 215, 0, 0.05);
  }

  .status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
  }

  .status-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .pay-btn {
    border: none;
    border-radius: 999px;
    padding: 6px 10px;
    background: linear-gradient(135deg, #ffe27d, #f3c547);
    color: #111;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
  }

  .status-badge.pending {
    background-color: rgba(255, 215, 0, 0.2);
    color: #d4a017;
  }

  .status-badge.accepted {
    background-color: rgba(100, 255, 150, 0.2);
    color: #2a9d6f;
  }

  .status-badge.declined {
    background-color: rgba(255, 100, 100, 0.2);
    color: #d32f2f;
  }

  .status-badge.paid {
    background-color: rgba(76, 175, 80, 0.2);
    color: #388e3c;
  }

  .payment-info {
    background-color: rgba(243, 197, 71, 0.1);
    padding: 15px;
    border-radius: 12px;
    border-left: 4px solid #f3c547;
    margin-top: 20px;
  }

  .payment-info h3 {
    font-size: 14px;
    color: #111;
    margin-bottom: 8px;
  }

  .payment-info p {
    font-size: 16px;
    font-weight: 600;
    color: #f3c547;
  }

  .pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin: 32px 0 12px;
    flex-wrap: wrap;
  }

  .pagination a {
    padding: 10px 20px;
    min-width: 64px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    border-radius: 999px;
    border: 1px solid rgba(243,197,71,.25);
    color: #b07c00;
    text-decoration: none;
    font-weight: 600;
    transition: 0.2s ease;
  }

  .pagination a.active {
    background: linear-gradient(135deg, #fff2ab, #f3c547, #c99208);
    color: #111;
    border-color: rgba(255,215,0,.55);
  }

  .btn {
    flex: 1 1 110px;
    min-height: 46px;
  }

  .filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 28px;
  }

  .filter-chip {
    padding: 10px 18px;
    border-radius: 999px;
    border: 1px solid rgba(255,215,0,.22);
    background: rgba(255,255,255,.85);
    color: #222;
    cursor: pointer;
    text-decoration: none;
    font-weight: 700;
    transition: 0.2s ease;
    min-width: 110px;
    text-align: center;
  }

  .filter-chip.active,
  .filter-chip:hover {
    background: linear-gradient(135deg, #fff2ab, #f3c547, #c99208);
    color: #111;
    border-color: rgba(255,215,0,.55);
  }

  .event-card {
    display: block;
  }

  .event-card[data-status="pending"] .status {
    background: rgba(255, 233, 171, .2);
    color: #c99208;
  }

  .event-card[data-status="planning"] .status {
    background: rgba(255, 215, 0, .16);
    color: #b07c00;
  }

  .event-card[data-status="ongoing"] .status {
    background: rgba(76, 175, 80, .12);
    color: #388e3c;
  }

  .event-card[data-status="completed"] .status {
    background: rgba(100, 255, 150, .14);
    color: #2a9d6f;
  }

  .event-card[data-status="cancelled"] .status {
    background: rgba(255, 100, 100, .14);
    color: #d32f2f;
  }

  .pagination a:hover {
    background: linear-gradient(135deg, #fff2ab, #f3c547, #c99208);
    color: #111;
  }
  </style>


</head>
<body>

  <div class="container">

<div class="navbar">
  <div class="logo">EventIntel</div>

  <div class="nav-links">
    <button onclick="window.location.href='homepage.php'">Home</button>
    <button onclick="window.location.href='createevent.php'">Create Event</button>
    <button class="active" onclick="window.location.href='yourevents.php'">Your Events</button>
    <button onclick="window.location.href='recommendation.php'">Recommendations</button>
    <button onclick="window.location.href='packages.php'">Packages</button>
    <button onclick="window.location.href='newsfeed.php'">Newsfeed</button>
    <button class="profile-btn" type="button" aria-label="Profile" title="Profile" onclick="window.location.href='profile.php'">
      <i class="fas fa-user"></i>
    </button>
  </div>
</div>

<div class="header">
  <h1>Your Events</h1>
  <p>Manage and track all your upcoming and completed events.</p>
</div>

<div class="filter-bar">
  <a class="filter-chip <?= $statusFilter === 'all' ? 'active' : '' ?>" href="yourevents.php?status=all&page=1">All (<?= (int)$statusCounts['all'] ?>)</a>
  <a class="filter-chip <?= $statusFilter === 'pending' ? 'active' : '' ?>" href="yourevents.php?status=pending&page=1">Pending (<?= (int)$statusCounts['pending'] ?>)</a>
  <a class="filter-chip <?= $statusFilter === 'planning' ? 'active' : '' ?>" href="yourevents.php?status=planning&page=1">Planning (<?= (int)$statusCounts['planning'] ?>)</a>
  <a class="filter-chip <?= $statusFilter === 'ongoing' ? 'active' : '' ?>" href="yourevents.php?status=ongoing&page=1">Ongoing (<?= (int)$statusCounts['ongoing'] ?>)</a>
  <a class="filter-chip <?= $statusFilter === 'completed' ? 'active' : '' ?>" href="yourevents.php?status=completed&page=1">Completed (<?= (int)$statusCounts['completed'] ?>)</a>
  <a class="filter-chip <?= $statusFilter === 'cancelled' ? 'active' : '' ?>" href="yourevents.php?status=cancelled&page=1">Cancelled (<?= (int)$statusCounts['cancelled'] ?>)</a>
</div>

<div class="events-grid" id="eventsGrid">
<?php if(!$events): ?>
  <p style="color:#aaa;">No events yet. Create your first event.</p>
<?php endif; ?>
<?php foreach($events as $ev):
  $statusKey = strtolower(trim((string)$ev['status'] ?? 'planning')) ?: 'planning';
?>
  <div class="event-card" data-status="<?= esc($statusKey) ?>">
    <div class="event-img"><img src="https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=800&q=80"></div>
    <div class="event-content">
      <span class="status <?= esc($statusKey) ?>"><?= esc($ev['status'] ?: 'Planning') ?></span>
      <h3><?=esc($ev['title'])?></h3>
      <div class="event-info">
        <div><i class="fa-solid fa-calendar"></i> <?=esc($ev['event_date'])?> <?=esc($ev['event_time'])?></div>
        <div><i class="fa-solid fa-users"></i> <?=esc($ev['guest_count'])?> guests</div>
      </div>
      <div class="card-actions">
        <button class="btn btn-primary" onclick="window.location.href='guests.php?id=<?=$ev['event_id']?>'">Guests / QR</button>
        <button class="btn btn-outline" onclick="window.location.href='invitation_builder.php?id=<?=$ev['event_id']?>'">Edit Invitation</button>
        <button class="btn btn-outline" onclick="window.location.href='map.php?id=<?=$ev['event_id']?>'">GPS</button>
        <button class="btn btn-outline" onclick="showServiceStatus(<?=$ev['event_id']?>)">Status</button>
        <a class="btn btn-outline" href="message.php?event_id=<?= (int)$ev['event_id'] ?>" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;">Messages</a>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<p id="noEventsMessage" style="color:#aaa; display:none; margin-top: 16px;">No events match this filter on this page.</p>

<?php if ($totalPages > 1 && !empty($events)): ?>
<div class="pagination">
  <?php if ($page > 1): ?>
    <a class="page-link" href="yourevents.php?status=<?= urlencode($statusFilter) ?>&page=<?= $page - 1 ?>">← Previous</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a class="page-link <?= $i === $page ? 'active' : '' ?>" href="yourevents.php?status=<?= urlencode($statusFilter) ?>&page=<?= $i ?>"><?= $i ?></a>
  <?php endfor; ?>

  <?php if ($page < $totalPages): ?>
    <a class="page-link" href="yourevents.php?status=<?= urlencode($statusFilter) ?>&page=<?= $page + 1 ?>">Next →</a>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Service Status Modal -->
<div id="serviceStatusModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Service Status</h2>
      <button class="close-btn" onclick="closeServiceStatus()">&times;</button>
    </div>
    <div id="serviceStatusContent"></div>
  </div>
</div>

<!-- Payment Method Modal -->
<div id="paymentModal" class="modal">
  <div class="modal-content" style="max-width:560px;">
    <div class="modal-header">
      <h2><i class="fas fa-coins" style="color:#f3c547;"></i> Pay for Service</h2>
      <button class="close-btn" onclick="closePaymentModal()">&times;</button>
    </div>
    <div id="paymentModalBody">
      <div style="padding:16px;border-radius:14px;background:rgba(243,197,71,.08);border:1px solid rgba(243,197,71,.25);margin-bottom:18px;">
        <div style="font-size:14px;color:#666;">Service</div>
        <div id="payServiceName" style="font-size:18px;font-weight:700;color:#111;margin-bottom:6px;"></div>
        <div style="font-size:14px;color:#666;">Amount</div>
        <div id="payAmount" style="font-size:28px;font-weight:800;color:#f3c547;"></div>
      </div>

      <p style="font-size:14px;color:#555;margin-bottom:14px;">Choose how you would like to pay this supplier:</p>

      <label class="payment-option" style="display:flex;gap:12px;align-items:center;padding:16px;border:2px solid rgba(243,197,71,.2);border-radius:14px;margin-bottom:10px;cursor:pointer;background:#fafafa;">
        <input type="radio" name="payment_method_choice" value="cash" style="width:18px;height:18px;accent-color:#f3c547;">
        <i class="fas fa-money-bill-wave" style="font-size:22px;color:#f3c547;"></i>
        <div>
          <div style="font-weight:700;color:#111;">Cash Payment</div>
          <div style="font-size:12px;color:#888;">Pay directly on the day of the event</div>
        </div>
      </label>

      <label class="payment-option" style="display:flex;gap:12px;align-items:center;padding:16px;border:2px solid rgba(243,197,71,.2);border-radius:14px;margin-bottom:10px;cursor:pointer;background:#fafafa;">
        <input type="radio" name="payment_method_choice" value="online" style="width:18px;height:18px;accent-color:#f3c547;">
        <i class="fas fa-credit-card" style="font-size:22px;color:#f3c547;"></i>
        <div>
          <div style="font-weight:700;color:#111;">Online Payment</div>
          <div style="font-size:12px;color:#888;">GCash / Bank transfer</div>
        </div>
      </label>

      <div id="gcashSection" style="display:none;margin-top:14px;padding:16px;border-radius:14px;background:rgba(243,197,71,.05);border:1px dashed rgba(243,197,71,.4);text-align:center;">
        <img src="../../Payment/GCASHQR.jpg" alt="GCash QR" style="max-width:180px;border-radius:12px;margin-bottom:10px;">
        <div style="font-size:13px;color:#666;">Scan to pay via GCash. After paying, the supplier will be notified and can confirm your payment.</div>
      </div>

      <div class="modal-actions" style="display:flex;justify-content:flex-end;gap:12px;margin-top:20px;">
        <button class="pay-btn" style="background:#eee;color:#333;padding:10px 20px;" onclick="closePaymentModal()">Cancel</button>
        <button class="pay-btn" style="background:linear-gradient(135deg,#ffe27d,#f3c547);color:#111;padding:10px 20px;" onclick="confirmPayment()">Confirm Payment</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Toggle GCash QR display based on selected payment method
  document.addEventListener('change', function(e) {
    if (e.target && e.target.name === 'payment_method_choice') {
      const gcashSection = document.getElementById('gcashSection');
      if (gcashSection) {
        gcashSection.style.display = e.target.value === 'online' ? 'block' : 'none';
      }
    }
  });
</script>

<script>
function showServiceStatus(eventId) {
  const modal = document.getElementById('serviceStatusModal');
  const content = document.getElementById('serviceStatusContent');

  // Fetch service data for this event
  fetch(`../html/get_service_status.php?event_id=${eventId}`)
    .then(response => response.json())
    .then(data => {
      let html = '';

      if (data.services && data.services.length > 0) {
        html += '<table class="service-table">';
        html += '<thead><tr>';
        html += '<th>Service Name</th>';
        html += '<th>Service Type</th>';
        html += '<th>Status</th>';
        html += '</tr></thead>';
        html += '<tbody>';

        data.services.forEach(service => {
          const statusLower = (service.raw_status || service.status).toLowerCase().replace(/\s+/g, '_');
          const isAccepted = statusLower === 'accepted';
          const isPaymentPending = statusLower === 'payment_pending';
          const isPaid = statusLower === 'paid';
          const isProposalSent = statusLower === 'proposal_sent';
          const isProposalAccepted = statusLower === 'proposal_accepted';
          const isProposalDeclined = statusLower === 'proposal_declined';

          let statusHtml = '';
          let messageBtn = '';
          const serviceKey = service.service_key || service.type.toLowerCase().replace(/ /g, '_');

          // Message button for any assigned supplier/coordinator
          if (service.supplier_user_id && service.supplier_user_id > 0) {
            messageBtn = `<a class="pay-btn" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;" href="message.php?event_id=${eventId}&user_id=${service.supplier_user_id}">Message</a>`;
          } else if (serviceKey === 'coordinator') {
            messageBtn = `<a class="pay-btn" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;" href="message.php?event_id=${eventId}">Message</a>`;
          }

          if (isAccepted && service.service_key !== 'coordinator') {
            statusHtml = `<div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
              <span class="status-badge accepted">Accepted</span>
              <button class="pay-btn" onclick="openPaymentModal(${eventId}, '${serviceKey}', ${service.price || 0}, '${escapeHtml(service.name)}')">Pay</button>
              ${messageBtn}
            </div>`;
          } else if (isAccepted && service.service_key === 'coordinator') {
            statusHtml = `<div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
              <span class="status-badge accepted">Accepted</span>
              <button class="pay-btn" onclick="openPaymentModal(${eventId}, '${serviceKey}', ${service.price || 0}, '${escapeHtml(service.name)}')">Pay</button>
              ${messageBtn}
            </div>`;
          } else if (isPaymentPending) {
            if (serviceKey === 'coordinator') {
              statusHtml = `<div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <span class="status-badge pending">Payment Pending</span>
                ${messageBtn}
              </div>`;
            } else {
              statusHtml = `<div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <span class="status-badge pending">Payment Pending</span>
                <button class="pay-btn" onclick="openPaymentModal(${eventId}, '${serviceKey}', ${service.price || 0}, '${escapeHtml(service.name)}')">Pay</button>
                ${messageBtn}
              </div>`;
            }
          } else if (isPaid) {
            statusHtml = `<div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
              <span class="status-badge paid">Paid</span>
              ${messageBtn}
            </div>`;
          } else if (statusLower === 'declined') {
            statusHtml = `<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
              <span class="status-badge declined">Declined</span>
              <button class="pay-btn" onclick="reselectService(${eventId}, '${service.service_key}')">Reselect</button>
              ${service.note ? `<button class="pay-btn" onclick="viewServiceNote(${eventId}, '${service.service_key}')">View Note</button>` : ''}
              ${messageBtn}
            </div>`;
} else if (isProposalSent) {
            statusHtml = `<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
              <span class="status-badge pending">Proposal Sent</span>
              <a class="pay-btn" style="text-decoration:none;background:linear-gradient(135deg,#ffe27d,#f3c547);color:#111;" href="proposal_review.php?event_id=${eventId}">Review Proposal</a>
              ${messageBtn}
            </div>`;
          } else if (isProposalAccepted) {
            // If coordinator proposal accepted, show Pay button for coordinator service
            if (serviceKey === 'coordinator') {
              statusHtml = `<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <span class="status-badge accepted">Proposal Accepted</span>
                <button class="pay-btn" onclick="openPaymentModal(${eventId}, 'coordinator', ${service.price || 0}, '${escapeHtml(service.name)}')">Pay</button>
                ${messageBtn}
              </div>`;
            } else {
              statusHtml = `<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <span class="status-badge accepted">Proposal Accepted</span>
                ${messageBtn}
              </div>`;
            }
          } else if (isProposalDeclined) {
            statusHtml = `<span class="status-badge declined">Proposal Declined</span>`;
          } else {
            statusHtml = `<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
              <span class="status-badge ${statusLower}">${escapeHtml(service.status)}</span>
              ${messageBtn}
            </div>`;
          }

          const priceDisplay = (service.price && service.price > 0) ? `<div style="color:#d4a017;font-weight:700;">₱${Number(service.price).toLocaleString()}</div>` : '';
          html += `<tr>
            <td>${escapeHtml(service.name)}${priceDisplay}</td>
            <td>${escapeHtml(service.type)}</td>
            <td>
              <div class="status-cell">
                ${statusHtml}
              </div>
            </td>
          </tr>`;
        });

        html += '</tbody>';
        html += '</table>';

        // Total to be paid summary
        let total = 0;
        data.services.forEach(s => {
          if (s.price && s.price > 0 && ['accepted', 'payment_pending', 'paid'].includes(String(s.raw_status).toLowerCase().replace(/\s+/g, '_'))) {
            total += Number(s.price);
          }
        });
        if (total > 0) {
          html += `<div class="payment-info">
            <h3><i class="fas fa-coins"></i> Total to be Paid</h3>
            <p style="font-size: 22px;">₱${total.toLocaleString()}</p>
            <small style="color:#888;">Payments are processed per service. Pay each accepted supplier directly from this list.</small>
          </div>`;
        }
        if (data.coordinator_proposal) {
          html += `<div class="payment-info"><h3>Coordinator Proposal</h3><p>${escapeHtml(data.coordinator_proposal)}</p></div>`;
        }
      } else {
        html += '<p style="color: #aaa;">No services assigned yet.</p>';
      }


      content.innerHTML = html;
      modal.classList.add('show');
    })
    .catch(error => {
      console.error('Error:', error);
      content.innerHTML = '<p style="color: red;">Error loading service status.</p>';
      modal.classList.add('show');
    });
}

function closeServiceStatus() {
  document.getElementById('serviceStatusModal').classList.remove('show');
}

function payService(eventId, serviceType) {
  openPaymentModal(eventId, serviceType, 0, '');
}

function openPaymentModal(eventId, serviceType, amount, serviceName) {
  // Store payment context in global
  window._paymentCtx = { eventId, serviceType, amount: Number(amount) || 0, serviceName: serviceName || '' };

  const modal = document.getElementById('paymentModal');
  const amountEl = document.getElementById('payAmount');
  const svcNameEl = document.getElementById('payServiceName');

  if (svcNameEl) svcNameEl.textContent = window._paymentCtx.serviceName;
  if (amountEl) amountEl.textContent = '₱' + window._paymentCtx.amount.toLocaleString();

  if (modal) modal.classList.add('show');
}

function closePaymentModal() {
  document.getElementById('paymentModal').classList.remove('show');
  window._paymentCtx = null;
}

function confirmPayment() {
  if (!window._paymentCtx) return;
  const method = document.querySelector('input[name="payment_method_choice"]:checked');
  if (!method) {
    alert('Please choose a payment method.');
    return;
  }

  const { eventId, serviceType, amount, serviceName } = window._paymentCtx;

  fetch(`../html/process_service_payment.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `event_id=${eventId}&service_type=${serviceType}&payment_method=${encodeURIComponent(method.value)}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      closePaymentModal();
      showServiceStatus(eventId);
    } else {
      alert('Error recording payment: ' + (data.error || 'Unknown error'));
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Failed to process payment');
  });
}

function acceptProposal(eventId) {
  fetch(`../html/process_proposal.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `event_id=${eventId}&action=accept`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showServiceStatus(eventId);
    } else {
      alert('Error: ' + (data.error || data.message || 'Unable to accept proposal'));
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Failed to accept proposal');
  });
}

function declineProposal(eventId) {
  fetch(`../html/process_proposal.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `event_id=${eventId}&action=decline`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showServiceStatus(eventId);
    } else {
      alert('Error: ' + (data.error || data.message || 'Unable to decline proposal'));
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Failed to decline proposal');
  });
}

function acceptServicePayment(eventId, serviceType) {
  fetch(`../html/accept_service_payment.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `event_id=${eventId}&service_type=${serviceType}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showServiceStatus(eventId);
    } else {
      alert('Error accepting payment: ' + (data.error || 'Unknown error'));
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Failed to accept payment');
  });
}

function reselectService(eventId, serviceType) {
  if (!confirm('Clear this declined supplier and reselect another?')) return;
  fetch(`../html/reselect_service.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `event_id=${eventId}&service_type=${serviceType}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showServiceStatus(eventId);
      // optionally reload page so create flow can pick new supplier
      // location.reload();
    } else {
      alert('Error: ' + (data.error || 'Unknown error'));
    }
  })
  .catch(err => {
    console.error(err);
    alert('Failed to reselect service');
  });
}

function escapeHtml(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

// Close modal when clicking outside of it
window.onclick = function(event) {
  const modal = document.getElementById('serviceStatusModal');
  if (event.target == modal) {
    modal.classList.remove('show');
  }
  const paymentModal = document.getElementById('paymentModal');
  if (event.target == paymentModal) {
    paymentModal.classList.remove('show');
  }
}
</script>

<!-- Decline note modal -->
<div id="viewNoteModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Note</h2>
      <button class="close-btn" onclick="closeViewNote()">&times;</button>
    </div>
    <div id="viewNoteContent" style="padding:16px;color:#222;white-space:pre-wrap;"></div>
  </div>
</div>

<script>
function viewServiceNote(eventId, serviceKey, note) {
  const modal = document.getElementById('viewNoteModal');
  const content = document.getElementById('viewNoteContent');

  // Fetch fresh service status data to get the stored note from DB
  fetch(`../html/get_service_status.php?event_id=${eventId}`)
    .then(r => r.json())
    .then(data => {
      if (!data.services) {
        content.textContent = 'No note available.';
        modal.classList.add('show');
        return;
      }
      const svc = data.services.find(s => (s.service_key || s.type.toLowerCase().replace(/ /g,'_')) === serviceKey);
      const noteText = svc ? (svc.note || '') : '';
      content.textContent = noteText || 'No note provided.';
      modal.classList.add('show');
    })
    .catch(err => {
      console.error(err);
      content.textContent = 'Error loading note.';
      modal.classList.add('show');
    });
}

function closeViewNote() {
  document.getElementById('viewNoteModal').classList.remove('show');
}
</script>
