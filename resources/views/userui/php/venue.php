<?php require_once __DIR__ . '/../../config/db.php'; require_role('client');

function hasColumn($pdo, $table, $column) {
  $check = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE :column");
  $check->execute([':column' => $column]);
  return $check->rowCount() > 0;
}

function normalizeTimeValue($value) {
  if ($value === null || $value === '') {
    return null;
  }
  $value = trim((string) $value);
  if (strlen($value) === 5) {
    $value .= ':00';
  }
  $dt = DateTime::createFromFormat('H:i:s', $value);
  if ($dt) {
    return $dt->format('H:i:s');
  }
  $dt = DateTime::createFromFormat('H:i', $value);
  if ($dt) {
    return $dt->format('H:i:s');
  }
  return $value;
}

function timeToSeconds($value) {
  $normalized = normalizeTimeValue($value);
  if ($normalized === null) {
    return null;
  }
  $parts = explode(':', $normalized);
  if (count($parts) < 2) {
    return null;
  }
  return ((int) $parts[0] * 3600) + ((int) $parts[1] * 60) + ((int) ($parts[2] ?? 0));
}

function getVenueCapacity($service) {
  $capacity = isset($service['capacity']) ? intval($service['capacity']) : 0;
  if ($capacity > 0) {
    return $capacity;
  }

  $fallbackMap = [
    'Casa de Alvin' => 300,
    'LIOS Resort and Events Place' => 250,
    'Casa de Consuelo Private Resort and Events Place' => 220,
    'La Tehillah Private Resort and Events Place' => 200,
    'Balai Manlapaz Resto' => 150,
  ];

  $name = trim((string) ($service['name'] ?? ''));
  foreach ($fallbackMap as $knownVenue => $fallbackCapacity) {
    if ($name !== '' && stripos($name, $knownVenue) !== false) {
      return $fallbackCapacity;
    }
  }

  return null;
}

function getVenueSlug($name) {
  $slugMap = [
    'casa de alvin' => 'alvin',
    'lios resort and events place' => 'lios',
    'casa de consuelo private resort and events place' => 'consuelo',
    'la tehillah private resort and events place' => 'tehillah',
    'balai manlapaz resto' => 'balai',
  ];

  $name = trim(strtolower((string) $name));
  return $slugMap[$name] ?? preg_replace('/[^a-z0-9]+/', '-', $name);
}

function getVenueAvailability($pdo, $venueName, $selectedDate, $startTime, $endTime) {
  $availability = [];
  if ($selectedDate === '') {
    return $availability;
  }

  $start = new DateTime($selectedDate);
  for ($offset = 0; $offset < 7; $offset++) {
    $day = (clone $start)->modify('+' . $offset . ' day');
    $dayDate = $day->format('Y-m-d');

    $isAvailable = true;
    $hasEndTimeColumn = hasColumn($pdo, 'events', 'event_end_time');
    $selectColumns = $hasEndTimeColumn ? 'event_time, event_end_time' : 'event_time';
    $stmt = $pdo->prepare("SELECT $selectColumns FROM events WHERE venue_name = ? AND event_date = ? AND status <> 'cancelled'");
    $stmt->execute([$venueName, $dayDate]);
    $events = $stmt->fetchAll();

    if (!empty($startTime)) {
      $requestedStart = timeToSeconds($startTime);
      $requestedEnd = !empty($endTime) ? timeToSeconds($endTime) : $requestedStart;

      foreach ($events as $event) {
        $existingStart = timeToSeconds($event['event_time']);
        $existingEnd = $hasEndTimeColumn && !empty($event['event_end_time']) ? timeToSeconds($event['event_end_time']) : $existingStart;

        if ($requestedStart !== null && $existingStart !== null && $requestedEnd !== null && $existingEnd !== null) {
          if ($requestedStart < $existingEnd && $requestedEnd > $existingStart) {
            $isAvailable = false;
            break;
          }
        }
      }
    }

    $availability[] = [
      'label' => $day->format('M j'),
      'date' => $dayDate,
      'available' => $isAvailable,
      'selected' => $dayDate === $selectedDate,
    ];
  }

  return $availability;
}

$pdo = db();
$isModal = ($_GET['modal'] ?? '') === 'true';
$guestCount = isset($_GET['guest_count']) ? max(1, intval($_GET['guest_count'])) : null;
$eventDate = trim($_GET['event_date'] ?? '');
$eventTime = trim($_GET['event_time'] ?? '');
$eventEndTime = trim($_GET['event_end_time'] ?? '');
$styleFilter = trim($_GET['style'] ?? '');

// Fetch venue services from supplier_services table
$query = "
    SELECT s.*, u.full_name as supplier_name
    FROM supplier_services s
    JOIN users u ON s.user_id = u.user_id
    WHERE s.category = 'Venue'";

// Apply style filter when selected
$queryParams = [];
if ($styleFilter !== '') {
  $query .= " AND s.style = ?";
  $queryParams[] = $styleFilter;
}

$query .= " ORDER BY s.rating DESC, s.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($queryParams);
$servicesRaw = $stmt->fetchAll();

$services = [];
foreach ($servicesRaw as $service) {
  $capacity = getVenueCapacity($service);
  $service['capacity'] = $capacity;
  $service['availability'] = getVenueAvailability($pdo, $service['name'], $eventDate, $eventTime, $eventEndTime);

  if ($guestCount === null || $capacity === null || $capacity >= $guestCount) {
    $services[] = $service;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventIntel - Select Venue</title>
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
    background: rgba(255,196,0,0.10);
    top: -120px;
    left: -100px;
  }

  body::after {
    width: 520px;
    height: 520px;
    background: rgba(255,215,0,0.08);
    bottom: -180px;
    right: -140px;
  }

  .background-strip {
    position: fixed;
    inset: 0;
    opacity: 0.08;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
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
    background: linear-gradient(to bottom, rgba(255,255,255,.95), rgba(255,255,255,.75), rgba(255,255,255,.98));
  }

  .container {
    position: relative;
    z-index: 1;
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

  .hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 28px;
  }

  .hero h1 {
    font-size: 54px;
    font-weight: 900;
    margin-bottom: 10px;
    color: #111;
  }

  .hero p {
    max-width: 600px;
    color: #555;
    line-height: 1.6;
  }

  .location-filter {
    width: 340px;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .location-filter label {
    color: #f3c547;
    font-size: 13px;
    letter-spacing: 2px;
    text-transform: uppercase;
  }

  .location-filter select {
    width: 100%;
    padding: 16px 18px;
    border-radius: 18px;
    border: 1px solid rgba(255,215,0,.15);
    background: #fff;
    color: #222;
    outline: none;
    font-size: 15px;
    box-shadow: 0 12px 28px rgba(0,0,0,.08);
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

  .venue-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    padding-bottom: 40px;
  }

  .venue-card {
    background: #fff;
    border: 1px solid rgba(255,215,0,.12);
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,.08);
    transition: .35s ease;
    position: relative;
  }

  .venue-card:hover {
    transform: translateY(-8px);
    border-color: rgba(255,215,0,.3);
    box-shadow: 0 18px 40px rgba(243,197,71,.12);
  }

  .venue-image {
    position: relative;
    height: 220px;
    overflow: hidden;
  }

  .venue-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(.95);
    transition: .4s ease;
  }

  .venue-card:hover .venue-image img {
    transform: scale(1.06);
    filter: brightness(1);
  }

  .venue-image::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(255,255,255,.92), rgba(255,255,255,.05));
  }

  .tag {
    position: absolute;
    top: 16px;
    right: 16px;
    z-index: 2;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(243,197,71,.14);
    border: 1px solid rgba(243,197,71,.25);
    color: #f3c547;
    font-size: 12px;
    font-weight: 700;
  }

  .venue-content {
    padding: 22px;
  }

  .venue-content h3 {
    font-size: 22px;
    margin-bottom: 10px;
    color: #111;
  }

  .venue-meta {
    display: flex;
    gap: 18px;
    margin-bottom: 16px;
    color: #666;
    font-size: 14px;
  }

  .venue-meta span {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .venue-content p {
    color: #555;
    line-height: 1.6;
    margin-bottom: 22px;
    min-height: 72px;
  }

  .venue-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .price {
    display: flex;
    flex-direction: column;
  }

  .price small {
    color: #888;
    margin-bottom: 4px;
  }

  .price strong {
    color: #f3c547;
    font-size: 20px;
  }

  .select-btn {
    padding: 14px 24px;
    border-radius: 16px;
    border: none;
    background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208);
    color: #111;
    font-weight: 800;
    cursor: pointer;
    transition: .3s ease;
  }

  .select-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(243,197,71,.25);
  }

  ::-webkit-scrollbar {
    width: 10px;
  }

  ::-webkit-scrollbar-thumb {
    background: rgba(243,197,71,.45);
    border-radius: 999px;
  }

  @media (max-width: 1200px) {
    .venue-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .hero {
      flex-direction: column;
      align-items: flex-start;
      gap: 20px;
    }
  }

  @media (max-width: 800px) {
    .venue-grid {
      grid-template-columns: 1fr;
    }

    .container {
      padding: 12px 20px 30px;
    }
  }

  /* Addon Modal Styles */
  .addon-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    padding: 20px;
  }

  .addon-modal.active {
    display: flex;
  }

  .addon-modal-content {
    background: white;
    border-radius: 20px;
    padding: 40px;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    max-height: 80vh;
    overflow-y: auto;
  }

  .addon-modal-header {
    margin-bottom: 30px;
  }

  .addon-modal-header h2 {
    font-size: 26px;
    color: #111;
    margin-bottom: 8px;
  }

  .addon-modal-header p {
    color: #666;
    font-size: 14px;
  }

  .addon-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 30px;
  }

  .addon-item {
    display: flex;
    align-items: center;
    padding: 16px;
    border: 1px solid rgba(212,160,23,0.2);
    border-radius: 12px;
    cursor: pointer;
    transition: 0.3s ease;
  }

  .addon-item:hover {
    background: rgba(243,197,71,0.05);
    border-color: rgba(212,160,23,0.4);
  }

  .addon-item input[type="checkbox"] {
    width: 20px;
    height: 20px;
    margin-right: 12px;
    cursor: pointer;
  }

  .addon-item label {
    cursor: pointer;
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .addon-item i {
    font-size: 18px;
    color: #d4a017;
    width: 24px;
  }

  .addon-item-text {
    display: flex;
    flex-direction: column;
  }

  .addon-item-name {
    font-weight: 600;
    color: #111;
    font-size: 15px;
  }

  .addon-item-desc {
    color: #999;
    font-size: 12px;
  }

  .addon-modal-footer {
    display: flex;
    gap: 12px;
  }

  .addon-modal-footer button {
    flex: 1;
    padding: 12px 20px;
    border-radius: 12px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s ease;
    font-size: 14px;
  }

  .addon-modal-footer .cancel-btn {
    background: rgba(212,160,23,0.1);
    color: #d4a017;
  }

  .addon-modal-footer .cancel-btn:hover {
    background: rgba(212,160,23,0.2);
  }

  .addon-modal-footer .confirm-btn {
    background: linear-gradient(to right, #ffd54a, #b8860b);
    color: black;
  }

  .addon-modal-footer .confirm-btn:hover {
    background: linear-gradient(to right, #ffe17a, #c99700);
  }

  .availability-modal-content {
    max-width: 620px;
  }

  .availability-list {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 10px;
    margin: 18px 0 8px;
  }

  .availability-day {
    border: 1px solid rgba(212,160,23,0.2);
    border-radius: 14px;
    padding: 10px 8px;
    text-align: center;
    background: #fff8e1;
    color: #7a5b00;
    font-size: 13px;
  }

  .availability-day.available {
    background: #e8f8ee;
    color: #166534;
    border-color: rgba(34,197,94,.2);
  }

  .availability-day.busy {
    background: #fde8e8;
    color: #b91c1c;
    border-color: rgba(248,113,113,.2);
  }

  .availability-day strong {
    display: block;
    margin-bottom: 4px;
    font-size: 12px;
  }
  </style>

</head>
<body>
  <div class="background-strip">
    <img src="https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80">
    <img src="https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1200&q=80">
    <img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80">
    <img src="https://images.unsplash.com/photo-1505236858219-8359eb29e329?auto=format&fit=crop&w=1200&q=80">
  </div>

  <!-- Availability Modal -->
  <div id="availabilityModal" class="addon-modal">
    <div class="addon-modal-content availability-modal-content">
      <div class="addon-modal-header">
        <h2>Venue Availability</h2>
        <p id="availabilityModalText">Check the next 7 days for your selected time slot.</p>
      </div>

      <div id="availabilityContent"></div>

      <div class="addon-modal-footer">
        <button class="cancel-btn" onclick="closeAvailabilityModal()">Cancel</button>
        <button class="confirm-btn" onclick="continueToAddons()">Continue to Add-ons</button>
      </div>
    </div>
  </div>

  <!-- Addon Modal -->
  <div id="addonModal" class="addon-modal">
    <div class="addon-modal-content">
      <div class="addon-modal-header">
        <h2>Venue Add-ons</h2>
        <p>Select additional services this venue can provide</p>
      </div>

      <div class="addon-list">
        <div class="addon-item">
          <label>
            <input type="checkbox" name="addon" value="catering">
            <i class="fas fa-utensils"></i>
            <div class="addon-item-text">
              <span class="addon-item-name">Catering</span>
              <span class="addon-item-desc">Food & beverage services</span>
            </div>
          </label>
        </div>

        <div class="addon-item">
          <label>
            <input type="checkbox" name="addon" value="clothing">
            <i class="fas fa-shirt"></i>
            <div class="addon-item-text">
              <span class="addon-item-name">Clothing & Styling</span>
              <span class="addon-item-desc">Event styling services</span>
            </div>
          </label>
        </div>

        <div class="addon-item">
          <label>
            <input type="checkbox" name="addon" value="sounds_lights">
            <i class="fas fa-lightbulb"></i>
            <div class="addon-item-text">
              <span class="addon-item-name">Sounds & Lights</span>
              <span class="addon-item-desc">Audio & lighting equipment</span>
            </div>
          </label>
        </div>

        <div class="addon-item">
          <label>
            <input type="checkbox" name="addon" value="host">
            <i class="fas fa-microphone"></i>
            <div class="addon-item-text">
              <span class="addon-item-name">Host / MC</span>
              <span class="addon-item-desc">Professional event host</span>
            </div>
          </label>
        </div>

        <div class="addon-item">
          <label>
            <input type="checkbox" name="addon" value="photographer">
            <i class="fas fa-camera"></i>
            <div class="addon-item-text">
              <span class="addon-item-name">Photographer</span>
              <span class="addon-item-desc">Professional photography</span>
            </div>
          </label>
        </div>
      </div>

      <div class="addon-modal-footer">
        <button class="cancel-btn" onclick="closeAddonModal()">Cancel</button>
        <button class="confirm-btn" onclick="confirmAddons()">Confirm & Select Venue</button>
      </div>
    </div>
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
        <h1>Select Your Venue</h1>
        <p>Choose the perfect place for your event. Filter by location and browse premium venues that match your celebration.</p>
      </div>

      <div class="location-filter">
        <label>Select Place</label>
        <select>
          <option>Apalit, Pampanga</option>
          <option>San Fernando, Pampanga</option>
          <option>Angeles, Pampanga</option>
          <option>Mabalacat, Pampanga</option>
          <option>Mexico, Pampanga</option>
          <option>Guagua, Pampanga</option>
          <option>Bacolor, Pampanga</option>
          <option>Lubao, Pampanga</option>
          <option>Malolos, Bulacan</option>
          <option>Quezon City</option>
        </select>
      </div>
    </div>

    <form method="GET" class="filter-bar">
      <input type="hidden" name="modal" value="<?= esc($_GET['modal'] ?? '') ?>">
      <input type="hidden" name="from" value="<?= esc($_GET['from'] ?? '') ?>">
      <input type="hidden" name="guest_count" value="<?= esc($_GET['guest_count'] ?? '') ?>">
      <input type="hidden" name="event_date" value="<?= esc($_GET['event_date'] ?? '') ?>">
      <input type="hidden" name="event_time" value="<?= esc($_GET['event_time'] ?? '') ?>">
      <input type="hidden" name="event_end_time" value="<?= esc($_GET['event_end_time'] ?? '') ?>">
      <label for="styleFilter">Venue Style</label>
      <select id="styleFilter" name="style" onchange="this.form.submit()">
        <option value="">All styles</option>
        <option value="Garden" <?= $styleFilter === 'Garden' ? 'selected' : '' ?>>Garden</option>
        <option value="Ballroom" <?= $styleFilter === 'Ballroom' ? 'selected' : '' ?>>Ballroom</option>
        <option value="Resort" <?= $styleFilter === 'Resort' ? 'selected' : '' ?>>Resort</option>
        <option value="Resto" <?= $styleFilter === 'Resto' ? 'selected' : '' ?>>Resto</option>
        <option value="Private" <?= $styleFilter === 'Private' ? 'selected' : '' ?>>Private</option>
      </select>
    </form>

    <div class="venue-grid">
      <?php if (empty($services)): ?>
      <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #999;">
        <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 20px; display: block;"></i>
        <h3>No Venue Services Available</h3>
        <p>Check back later for available venues</p>
      </div>
      <?php else: ?>
        <?php foreach ($services as $service):
          $serviceSlug = getVenueSlug($service['name'] ?? ''); ?>
      <div class="venue-card">
        <div class="venue-image">
          <span class="tag"><?= ($service['rating'] ?? 4.5) >= 4.5 ? 'Popular' : '' ?></span>
          <img src="../images/logo.png" alt="<?= esc($service['name']) ?>">
        </div>
        <div class="venue-content">
          <h3><?= esc($service['name']) ?></h3>
          <div class="venue-meta">
            <span><i class="fa-solid fa-location-dot"></i> <?= esc($service['address'] ?? 'Location') ?></span>
            <span><i class="fa-solid fa-users"></i> 300+ Guests</span>
          </div>
          <p><?= esc($service['description'] ?? 'Professional venue') ?></p>
          <div class="venue-footer">
            <div class="price">
              <small>Starting at</small>
              <strong>₱<?= number_format($service['price'] ?? 25000) ?></strong>
            </div>
            <button type="button" class="select-btn" data-venue-slug="<?= esc($serviceSlug) ?>" data-venue-name="<?= esc($service['name']) ?>">Select</button>
          </div>
        </div>
      </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

<script>
let selectedVenueInfo = null;

function openAvailabilityModal(venueName, availabilityMarkup) {
  selectedVenueInfo = {
    name: venueName,
    addons: []
  };
  document.getElementById('availabilityModalText').textContent = 'Availability for ' + venueName;
  document.getElementById('availabilityContent').innerHTML = availabilityMarkup || '<p>No availability information is available for this venue yet.</p>';
  document.getElementById('availabilityModal').classList.add('active');
}

function closeAvailabilityModal() {
  document.getElementById('availabilityModal').classList.remove('active');
}

function continueToAddons() {
  if (selectedVenueInfo) {
    closeAvailabilityModal();
    openAddonModal(selectedVenueInfo.name);
  }
}

function openAddonModal(venueName) {
  if (!selectedVenueInfo) {
    selectedVenueInfo = {
      name: venueName,
      addons: []
    };
  }
  document.getElementById('addonModal').classList.add('active');
}

function closeAddonModal() {
  document.getElementById('addonModal').classList.remove('active');
}

function confirmAddons() {
  const addonCheckboxes = document.querySelectorAll('#addonModal input[name="addon"]:checked');
  const selectedAddons = Array.from(addonCheckboxes).map(cb => cb.value);

  if (selectedVenueInfo) {
    selectedVenueInfo.addons = selectedAddons;
    selectVenue(selectedVenueInfo.name, selectedAddons);
    selectedVenueInfo = null;
  }

  closeAddonModal();
}

function selectVenue(venueName, addons = []) {
  const normalizedVenue = String(venueName || '').trim();
  if (normalizedVenue) {
    try {
      sessionStorage.setItem('event_selection_venue', normalizedVenue);
      sessionStorage.setItem('event_selection_venue_name', normalizedVenue);
      document.cookie = 'event_selection_venue=' + encodeURIComponent(normalizedVenue) + '; path=/; max-age=3600';
      document.cookie = 'event_selection_venue_name=' + encodeURIComponent(normalizedVenue) + '; path=/; max-age=3600';
    } catch (err) {
      console.warn('Could not persist venue selection', err);
    }
  }

  const params = new URLSearchParams(window.location.search);
  const from = params.get('from');
  const isModal = params.get('modal') === 'true';
  const addonList = Array.isArray(addons) ? addons.map(String).map(s => s.trim()).filter(Boolean) : String(addons || '').split(',').map(s => s.trim()).filter(Boolean);
  const addonString = addonList.join(',');

  const message = {
    type: 'serviceSelected',
    service: 'venue',
    venue: normalizedVenue,
    venue_name: normalizedVenue
  };

  if (addonList.length > 0) {
    message.addons = addonList;
  }

  if (from === 'createevent') {
    if (isModal && window.parent && window.parent !== window) {
      window.parent.postMessage(message, '*');
    } else if (window.opener && !window.opener.closed) {
      window.opener.postMessage(message, '*');
      window.close();
    } else {
      const returnUrl = params.get('return') || 'createevent.php';
      window.location.href = returnUrl + '?selected=venue';
    }
  } else {
    alert('Venue "' + normalizedVenue + '" selected with add-ons: ' + (addonString || 'None'));
  }
}

document.querySelectorAll('.select-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    const slug = this.getAttribute('data-venue-slug') || this.getAttribute('data-venue-name') || '';
    const params = new URLSearchParams(window.location.search);
    params.set('venue', slug);
    params.set('from', 'createevent');
    params.set('modal', 'true');
    params.set('return', 'createevent.php');
    window.location.href = 'venuedetails.php?' + params.toString();
  });
});

document.getElementById('availabilityModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeAvailabilityModal();
  }
});

document.getElementById('addonModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeAddonModal();
  }
});
</script>
</body>
</html>
