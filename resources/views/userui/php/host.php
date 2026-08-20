<?php require_once __DIR__ . '/../../config/db.php'; require_role('client'); 

$pdo = db();
$isModal = ($_GET['modal'] ?? '') === 'true';
$styleFilter = trim($_GET['style'] ?? '');

// Fetch host services from supplier_services table
$query = "
    SELECT s.*, u.full_name as supplier_name
    FROM supplier_services s
    JOIN users u ON s.user_id = u.user_id
    WHERE s.category = 'Host'";
$params = [];

// NOTE: style filtering is currently UI/design-only and does not affect backend results.
$query .= "\n    ORDER BY s.rating DESC, s.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$services = $stmt->fetchAll();

$serviceId = trim($_GET['service_id'] ?? '');
$selectedService = null;
$selectedIndex = 0;
foreach ($services as $i => $service) {
  if ((string)($service['service_id'] ?? $service['id'] ?? '') === $serviceId) {
    $selectedService = $service;
    $selectedIndex = $i;
    break;
  }
}

$queryParams = array_filter([
  'modal' => $_GET['modal'] ?? '',
  'from' => $_GET['from'] ?? '',
  'event_date' => $_GET['event_date'] ?? '',
  'event_time' => $_GET['event_time'] ?? '',
  'event_end_time' => $_GET['event_end_time'] ?? '',
  'guest_count' => $_GET['guest_count'] ?? '',
  'style' => $_GET['style'] ?? '',
]);
$preserveQuery = http_build_query($queryParams);

function getHostImage($index) {
  $images = [
    '../images/mamad.jpg',
    '../images/vince.jpg',
    'https://images.unsplash.com/photo-1514996937319-344454492b37?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=900&q=80',
  ];
  return $images[$index % count($images)];
}

function getHostGallery($index) {
  $images = [
    'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1500336624523-d727130c3328?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1515165562835-cd5a9f49c70f?auto=format&fit=crop&w=900&q=80',
  ];
  return $images[$index % count($images)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventIntel - Select Event Host</title>
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
      overflow-x: hidden;
      position: relative;
    }

    body::before,
    body::after {
      content: "";
      position: fixed;
      border-radius: 50%;
      filter: blur(140px);
      z-index: 0;
    }

    body::before {
      width: 420px;
      height: 420px;
      background: rgba(255, 196, 0, 0.10);
      top: -140px;
      left: -120px;
    }

    body::after {
      width: 540px;
      height: 540px;
      background: rgba(255, 215, 0, 0.06);
      bottom: -220px;
      right: -180px;
    }

    .background-strip {
      position: fixed;
      inset: 0;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      opacity: 0.10;
      z-index: 0;
    }

    .background-strip img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(0.9) blur(3px);
    }

    .background-strip::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(
        to bottom,
        rgba(255,255,255,.94),
        rgba(255,255,255,.75),
        rgba(255,255,255,.96)
      );
    }

    .container {
      position: relative;
      z-index: 2;
      max-width: 1600px;
      margin: 0 auto;
      padding: 6px 48px 40px;
    }

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
      border: 1px solid rgba(212, 160, 23, 0.35);
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
      border: 1px solid rgba(212, 160, 23, 0.25);
      background: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #d4a017;
      cursor: pointer;
    }

    .hero {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-bottom: 30px;
    }

    .hero h1 {
      font-size: 56px;
      font-weight: 900;
      margin-bottom: 12px;
      color: #111;
    }

    .hero p {
      max-width: 700px;
      color: #666;
      line-height: 1.7;
    }

    .search-box {
      position: relative;
      width: 340px;
    }

    .search-box input {
      width: 100%;
      padding: 16px 18px 16px 50px;
      border-radius: 18px;
      border: 1px solid rgba(212,160,23,0.15);
      background: rgba(255,255,255,.95);
      color: #222;
      outline: none;
      font-size: 14px;
    }

    .search-box i {
      position: absolute;
      top: 50%;
      left: 18px;
      transform: translateY(-50%);
      color: #d4a017;
    }

    .filter-bar {
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      margin-bottom: 28px;
      align-items: center;
    }

    .filter-bar label {
      font-size: 14px;
      font-weight: 700;
      color: #444;
    }

    .filter-bar select {
      min-width: 220px;
      padding: 14px 16px;
      border-radius: 16px;
      border: 1px solid rgba(212,160,23,0.18);
      background: rgba(255,255,255,0.95);
      color: #111;
      outline: none;
      font-size: 14px;
    }

    .host-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 26px;
    }

    .host-card {
      background: rgba(255,255,255,.92);
      border: 1px solid rgba(212,160,23,.12);
      border-radius: 30px;
      overflow: hidden;
      transition: .35s ease;
      box-shadow: 0 18px 40px rgba(0,0,0,.08);
      backdrop-filter: blur(16px);
    }

    .host-card:hover {
      transform: translateY(-8px);
      border-color: rgba(212,160,23,.3);
      box-shadow: 0 24px 50px rgba(243,197,71,.15);
    }

    .host-image {
      position: relative;
      height: 320px;
      overflow: hidden;
    }

    .host-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(.9);
      transition: .35s ease;
    }

    .host-card:hover .host-image img {
      transform: scale(1.05);
      filter: brightness(1);
    }

    .host-image::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(255,255,255,.92), rgba(255,255,255,.05));
    }

    .badge {
      position: absolute;
      top: 16px;
      right: 16px;
      z-index: 2;
      padding: 8px 14px;
      border-radius: 999px;
      background: rgba(243,197,71,.14);
      border: 1px solid rgba(243,197,71,.25);
      color: #d4a017;
      font-size: 12px;
      font-weight: 700;
    }

    .host-content {
      padding: 24px;
    }

    .host-content h3 {
      font-size: 24px;
      margin-bottom: 10px;
      color: #111;
    }

    .details {
      display: flex;
      gap: 18px;
      color: #666;
      font-size: 14px;
      margin-bottom: 16px;
    }

    .details span {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .host-content p {
      color: #777;
      line-height: 1.6;
      margin-bottom: 20px;
      min-height: 72px;
    }

    .footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .price {
      color: #d4a017;
      font-size: 22px;
      font-weight: 800;
    }

    .select-btn,
    .view-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 14px 24px;
      border-radius: 16px;
      border: none;
      background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208);
      color: #111;
      font-weight: 800;
      cursor: pointer;
      transition: .3s ease;
      text-decoration: none;
      line-height: 1;
    }

    .select-btn:hover,
    .view-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 24px rgba(243,197,71,.25);
      text-decoration: none;
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 12px 20px;
      border-radius: 16px;
      border: 1px solid rgba(243,197,71,0.25);
      background: rgba(255,255,255,0.95);
      color: #111;
      font-weight: 700;
      box-shadow: 0 14px 28px rgba(243,197,71,0.12);
      cursor: pointer;
      transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .back-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 28px rgba(0,0,0,0.12);
    }

    .detail-actions {
      margin-bottom: 22px;
      display: flex;
      align-items: center;
    }

    .service-detail {
      display: grid;
      grid-template-columns: 1.5fr 1fr;
      gap: 32px;
      align-items: flex-start;
      margin-bottom: 32px;
    }

    .detail-card {
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 28px 80px rgba(0, 0, 0, 0.12);
    }

    .detail-image img {
      width: 100%;
      display: block;
      object-fit: cover;
      min-height: 420px;
    }

    .detail-info {
      background: rgba(255, 255, 255, 0.92);
      border-radius: 28px;
      padding: 34px;
      box-shadow: 0 18px 50px rgba(0, 0, 0, 0.08);
    }

    .detail-info .badge {
      display: inline-block;
      background: #fae7a1;
      color: #7d5a07;
      padding: 10px 16px;
      border-radius: 999px;
      font-weight: 700;
      margin-bottom: 20px;
    }

    .detail-info h1 {
      font-size: 42px;
      margin-bottom: 16px;
      line-height: 1.05;
    }

    .detail-info p {
      font-size: 16px;
      line-height: 1.75;
      color: #545454;
      margin-bottom: 28px;
    }

    .detail-stats {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }

    .detail-stat {
      padding: 20px;
      border-radius: 18px;
      background: rgba(255,255,255,0.88);
      border: 1px solid rgba(229, 188, 61, 0.18);
    }

    .detail-stat span {
      display: block;
      font-size: 13px;
      color: #7a7a7a;
      margin-bottom: 8px;
    }

    .detail-stat strong {
      display: block;
      font-size: 18px;
      color: #111;
    }

    .thumbnail-row {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 16px;
      margin-top: 28px;
    }

    .thumbnail {
      border-radius: 20px;
      overflow: hidden;
      background: #fff;
      box-shadow: 0 16px 32px rgba(0,0,0,0.08);
    }

    .thumbnail img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
  </style>
</head>
<body>
  <div class="background-strip">
    <img src="https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=1200&q=80">
    <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1200&q=80">
    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=1200&q=80">
    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=1200&q=80">
  </div>

  <div class="container">
    <?php if (!$isModal): ?>
    <div class="navbar">
      <div class="logo">EventIntel</div>

      <div class="nav-links">
        <button onclick="window.location.href='homepage.php'">Home</button>
        <button class="active" onclick="window.location.href='createevent.php'">Create Event</button>
        <button onclick="window.location.href='yourevents.php'">Your Events</button>
        <button onclick="window.location.href='recommendation.php'">Recommendations</button>
        <button onclick="window.location.href='newsfeed.php'">Newsfeed</button>
      </div>
    </div>
    <?php endif; ?>

    <div class="hero">
      <div>
        <h1><?= $selectedService ? 'Host Details' : 'Select Event Host' ?></h1>
        <p><?= $selectedService ? 'Review this host profile before booking.' : 'Choose a professional host who can bring energy, confidence, and smooth coordination to your event.' ?></p>
      </div>

      <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search host or specialty...">
      </div>
    </div>

    <?php if ($selectedService): ?>
      <div class="detail-actions">
        <button type="button" class="back-btn" onclick="window.location.href = window.location.pathname + '<?= $preserveQuery ? '?' . $preserveQuery : '' ?>'">
          <i class="fa-solid fa-arrow-left"></i> Back to list
        </button>
      </div>
      <div class="service-detail">
        <div class="detail-card">
          <div class="detail-image">
              <img src="<?= esc(getHostImage($selectedIndex)) ?>" alt="<?= esc($selectedService['name']) ?>">
            </div>
        </div>
        <div class="detail-info">
          <div class="badge">Event Host</div>
          <h1><?= esc($selectedService['name']) ?></h1>
          <p><?= esc($selectedService['description'] ?? 'Professional hosting service for your event.') ?></p>
          <div class="detail-stats">
            <div class="detail-stat">
              <span>Rating</span>
              <strong><?= number_format($selectedService['rating'] ?? 4.8, 1) ?> ★</strong>
            </div>
            <div class="detail-stat">
              <span>Fee</span>
              <strong>₱<?= number_format($selectedService['price'] ?? 5000) ?></strong>
            </div>
          </div>
          <div class="detail-stats">
            <div class="detail-stat">
              <span>Supplier</span>
              <strong><?= esc($selectedService['supplier_name'] ?? 'Event Host Pro') ?></strong>
            </div>
            <div class="detail-stat">
              <span>Presentation Style</span>
              <strong><?= esc($selectedService['style'] ?? 'Interactive') ?></strong>
            </div>
          </div>
          <button class="select-btn" onclick="selectService('<?= esc(addslashes($selectedService['name'])) ?>','host')">Book Service</button>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!$selectedService): ?>
    <form method="GET" class="filter-bar">
      <input type="hidden" name="modal" value="<?= esc($_GET['modal'] ?? '') ?>">
      <input type="hidden" name="from" value="<?= esc($_GET['from'] ?? '') ?>">
      <input type="hidden" name="event_date" value="<?= esc($_GET['event_date'] ?? '') ?>">
      <input type="hidden" name="event_time" value="<?= esc($_GET['event_time'] ?? '') ?>">
      <input type="hidden" name="event_end_time" value="<?= esc($_GET['event_end_time'] ?? '') ?>">
      <input type="hidden" name="guest_count" value="<?= esc($_GET['guest_count'] ?? '') ?>">
      <label for="styleFilter">Hosting Style</label>
      <select id="styleFilter" name="style" onchange="this.form.submit()">
        <option value="">All styles</option>
        <option value="Formal" <?= $styleFilter === 'Formal' ? 'selected' : '' ?>>Formal</option>
        <option value="Casual" <?= $styleFilter === 'Casual' ? 'selected' : '' ?>>Casual</option>
        <option value="Emcee" <?= $styleFilter === 'Emcee' ? 'selected' : '' ?>>Emcee</option>
        <option value="Interactive" <?= $styleFilter === 'Interactive' ? 'selected' : '' ?>>Interactive</option>
      </select>
    </form>

    <div class="host-grid">
      <?php if (empty($services)): ?>
      <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #999;">
        <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 20px; display: block;"></i>
        <h3>No Host Services Available</h3>
        <p>Check back later for available event hosts</p>
      </div>
      <?php else: ?>
        <?php foreach ($services as $index => $service): ?>
      <div class="host-card">
        <div class="host-image">
          <span class="badge"><?= ($service['rating'] ?? 4.5) >= 4.5 ? 'Professional Host' : '' ?></span>
          <img src="<?= esc(getHostImage($index)) ?>" alt="<?= esc($service['name']) ?>">
        </div>
        <div class="host-content">
          <h3><?= esc($service['name']) ?></h3>
          <div class="details">
            <span><i class="fa-solid fa-star"></i> <?= number_format($service['rating'] ?? 5.0, 1) ?></span>
            <span><i class="fa-solid fa-microphone"></i> Professional</span>
          </div>
          <p><?= esc($service['description'] ?? 'Professional event host') ?></p>
          <div class="footer">
            <div class="price">₱<?= number_format($service['price'] ?? 5000) ?></div>
            <a class="view-btn" href="?service_id=<?= esc($service['service_id'] ?? $service['id'] ?? '') ?>&<?= $preserveQuery ?>">View Details</a>
          </div>
        </div>
      </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

<script>
function selectService(serviceName, serviceType) {
  const params = new URLSearchParams(window.location.search);
  const from = params.get('from');
  const isModal = params.get('modal') === 'true';
  if (from === 'createevent') {
    const message = { type: 'serviceSelected', service: serviceType };
    message[serviceType] = serviceName;
    if (isModal && window.parent && window.parent !== window) {
      window.parent.postMessage(message, '*');
    } else if (window.opener && !window.opener.closed) {
      window.opener.postMessage(message, '*');
      window.close();
    } else {
      const returnUrl = params.get('return') || 'createevent.php';
      window.location.href = returnUrl + '?selected=' + serviceType;
    }
  } else {
    alert(serviceName + ' selected!');
  }
}
document.querySelectorAll('.select-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    const card = this.closest('.host-card');
    const name = card.querySelector('h3')?.textContent || 'Host';
    selectService(name, 'host');
  });
});
</script>
</body>
</html>
