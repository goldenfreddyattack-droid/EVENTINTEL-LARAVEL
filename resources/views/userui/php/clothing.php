<?php require_once __DIR__ . '/../../config/db.php'; require_role('client'); 

$pdo = db();
$isModal = ($_GET['modal'] ?? '') === 'true';
$styleFilter = trim($_GET['style'] ?? '');

// Fetch clothing services from supplier_services table
$query = "
    SELECT s.*, u.full_name as supplier_name
    FROM supplier_services s
    JOIN users u ON s.user_id = u.user_id
    WHERE s.category = 'Clothing'";
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

function getClothingImage($index) {
  $images = [
    'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1520962915292-4a7d0f9ec5f0?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1495121605193-b116b5b9c5f0?auto=format&fit=crop&w=900&q=80',
  ];
  return $images[$index % count($images)];
}

function getClothingGallery($index) {
  $images = [
    'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1523293838609-a049d5c3e7e7?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1503341455253-b2e723bb3dbb?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?auto=format&fit=crop&w=900&q=80',
  ];
  return $images[$index % count($images)];
}
?>
<!DOCTYPE html>
<html lang="en">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventIntel - Select Clothing</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
  }

  body {
    background: #f8f8f8;
    color: #111;
    min-height: 100vh;
    overflow-x: hidden;
    position: relative;
  }

  /* GLOW EFFECTS */
  body::before,
  body::after {
    content: "";
    position: fixed;
    border-radius: 50%;
    filter: blur(140px);
    z-index: 0;
  }

  body::before {
    width: 430px;
    height: 430px;
    background: rgba(243,197,71,0.10);
    top: -140px;
    left: -120px;
  }

  body::after {
    width: 560px;
    height: 560px;
    background: rgba(243,197,71,0.07);
    bottom: -220px;
    right: -180px;
  }

  /* BACKGROUND STRIP */
  .background-strip {
    position: fixed;
    inset: 0;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    opacity: 0.22;
    z-index: 0;
    pointer-events: none;
  }

  .background-strip img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(1) blur(2px);
  }

  .background-strip::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
      to bottom,
      rgba(255,255,255,.90),
      rgba(255,255,255,.68),
      rgba(255,255,255,.94)
    );
  }

  /* MAIN CONTAINER */
  .container {
    position: relative;
    z-index: 2;
    max-width: 1600px;
    margin: 0 auto;
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
    border: 1px solid rgba(212,160,23,0.22);
    background: rgba(255,255,255,0.82);
    display: flex;
    justify-content: center;
    align-items: center;
    color: #d4a017;
    cursor: pointer;
  }

  /* HERO */
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

  /* SEARCH */
  .search-box {
    position: relative;
    width: 340px;
  }

  .search-box input {
    width: 100%;
    padding: 16px 18px 16px 50px;
    border-radius: 18px;
    border: 1px solid rgba(212,160,23,0.14);
    background: rgba(255,255,255,0.82);
    color: #111;
    outline: none;
    font-size: 14px;
  }

  .search-box i {
    position: absolute;
    left: 18px;
    top: 50%;
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

  /* GRID */
  .clothing-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 26px;
  }

  /* CARD */
  .clothing-card {
    background: rgba(255,255,255,.82);
    border: 1px solid rgba(212,160,23,.14);
    border-radius: 30px;
    overflow: hidden;
    transition: .35s ease;
    box-shadow: 0 18px 40px rgba(0,0,0,.08);
    backdrop-filter: blur(16px);
  }

  .clothing-card:hover {
    transform: translateY(-8px);
    border-color: rgba(212,160,23,.3);
    box-shadow: 0 24px 50px rgba(243,197,71,.12);
  }

  /* IMAGE */
  .clothing-image {
    position: relative;
    height: 280px;
    overflow: hidden;
  }

  .clothing-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(.92);
    transition: .35s ease;
  }

  .clothing-card:hover .clothing-image img {
    transform: scale(1.05);
    filter: brightness(1);
  }

  .clothing-card .details {
    padding: 20px 24px 28px;
  }

  .clothing-card .footer {
    gap: 12px;
    flex-wrap: wrap;
  }

  .clothing-image::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(255,255,255,.90), rgba(255,255,255,.08));
  }

  /* BADGE */
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

  /* CONTENT */
  .clothing-content {
    padding: 24px;
  }

  .clothing-content h3 {
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

  .clothing-content p {
    color: #777;
    line-height: 1.6;
    margin-bottom: 20px;
    min-height: 72px;
  }

  /* FOOTER */
  .footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
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
    background: linear-gradient(135deg,#fff1a8,#f3c547,#c99208);
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
    text-decoration: none !important;
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

  .view-btn:focus {
    outline: none;
    text-decoration: none !important;
  }

  .service-detail {
    display: grid;
    grid-template-columns: 1.3fr 1fr;
    gap: 28px;
    margin-bottom: 28px;
  }

  .service-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 22px;
  }

  .detail-card {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 30px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.08);
    overflow: hidden;
  }

  .detail-image {
    position: relative;
    min-height: 420px;
    overflow: hidden;
  }

  .detail-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: .35s ease;
  }

  .detail-info {
    padding: 34px;
  }

  .detail-info h1 {
    font-size: 44px;
    margin-bottom: 14px;
    color: #111;
  }

  .detail-info p {
    color: #444;
    line-height: 1.7;
    margin-bottom: 28px;
  }

  .detail-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 28px;
  }

  .detail-stat {
    background: #f6f6f6;
    padding: 18px;
    border-radius: 20px;
    border: 1px solid rgba(0,0,0,0.06);
  }

  .detail-stat strong {
    display: block;
    margin-top: 8px;
    font-size: 22px;
    color: #111;
  }

  .thumbnail-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-top: 18px;
  }

  .thumbnail {
    position: relative;
    overflow: hidden;
    border-radius: 18px;
    border: 1px solid rgba(0,0,0,0.08);
    min-height: 90px;
  }

  .thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  </style>
</head>
<body>
  <div class="background-strip">
    <img src="https://images.unsplash.com/photo-1555244162-803834f70033?auto=format&fit=crop&w=1200&q=80">
    <img src="https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=1200&q=80">
    <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200&q=80">
    <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=1200&q=80">
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
        <h1><?= $selectedService ? 'Clothing Details' : 'Select Clothing' ?></h1>
        <p>
          <?= $selectedService
            ? 'Review the selected clothing service and confirm before booking.'
            : 'Choose the ideal clothing service for your event. Explore different styles, sizes, and premium options.'
          ?>
        </p>
      </div>

      <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search clothing or accessory service...">
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
            <img src="<?= esc(getClothingGallery($selectedIndex)) ?>" alt="<?= esc($selectedService['name']) ?>">
          </div>
        </div>

        <div class="detail-info">
          <div class="badge">Clothing Service</div>
          <h1><?= esc($selectedService['name']) ?></h1>
          <p><?= esc($selectedService['description'] ?? 'Professional clothing service for your special event.') ?></p>
          <div class="detail-stats">
            <div class="detail-stat">
              <span>Service Rating</span>
              <strong><?= number_format($selectedService['rating'] ?? 4.8, 1) ?> ★</strong>
            </div>
            <div class="detail-stat">
              <span>Starting Price</span>
              <strong>₱<?= number_format($selectedService['price'] ?? 5000) ?></strong>
            </div>
          </div>
          <div class="detail-stats">
            <div class="detail-stat">
              <span>Supplier</span>
              <strong><?= esc($selectedService['supplier_name'] ?? 'Premium Clothing') ?></strong>
            </div>
            <div class="detail-stat">
              <span>Capacity</span>
              <strong><?= esc($selectedService['guest_capacity'] ?? '50+ guests') ?></strong>
            </div>
          </div>
          <button class="select-btn" onclick="selectService('<?= esc(addslashes($selectedService['name'])) ?>','clothes')">Book Service</button>

          <div class="thumbnail-row">
            <?php for ($i = 0; $i < 4; $i++): ?>
              <div class="thumbnail">
                <img src="<?= esc(getClothingGallery($selectedIndex + $i)) ?>" alt="Thumbnail <?= $i + 1 ?>">
              </div>
            <?php endfor; ?>
          </div>
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
      <label for="styleFilter">Style</label>
      <select id="styleFilter" name="style" onchange="this.form.submit()">
        <option value="">All styles</option>
        <option value="Formal" <?= $styleFilter === 'Formal' ? 'selected' : '' ?>>Formal</option>
        <option value="Casual" <?= $styleFilter === 'Casual' ? 'selected' : '' ?>>Casual</option>
        <option value="Traditional" <?= $styleFilter === 'Traditional' ? 'selected' : '' ?>>Traditional</option>
        <option value="Modern" <?= $styleFilter === 'Modern' ? 'selected' : '' ?>>Modern</option>
        <option value="Fusion" <?= $styleFilter === 'Fusion' ? 'selected' : '' ?>>Fusion</option>
      </select>
    </form>

    <div class="clothing-grid">
      <?php if (empty($services)): ?>
      <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #999;">
        <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 20px; display: block;"></i>
        <h3>No Clothing Services Available</h3>
        <p>Check back later for available clothing services</p>
      </div>
      <?php else: ?>
        <?php foreach ($services as $index => $service): ?>
      <div class="clothing-card">
        <div class="clothing-image">
          <span class="badge"><?= ($service['rating'] ?? 4.5) >= 4.5 ? 'Highly Rated' : '' ?></span>
          <img src="<?= esc(getClothingImage($index)) ?>" alt="<?= esc($service['name']) ?>">
        </div>
        <div class="clothing-content">
          <h3><?= esc($service['name']) ?></h3>
          <div class="details">
            <span><i class="fa-solid fa-star"></i> <?= number_format($service['rating'] ?? 5.0, 1) ?></span>
            <span><i class="fa-solid fa-users"></i> <?= $service['user_id'] ?? 0 ?></span>
          </div>
          <p><?= esc($service['description'] ?? 'Professional clothing service') ?></p>
          <div class="footer">
            <div>
              <small>Starting at</small>
              <strong>₱<?= number_format($service['price'] ?? 5000) ?></strong>
            </div>
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
    const card = this.closest('.clothing-card');
    const brand = card.querySelector('h3').textContent;
    selectService(brand, 'clothes');
  });
});
</script>
</body>
</html>
