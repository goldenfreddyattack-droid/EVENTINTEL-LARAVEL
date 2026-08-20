<?php
$venueSlug = trim(strtolower($_GET['venue'] ?? ''));
$venues = [
  'alvin' => [
    'title' => 'Casa de Alvin',
    'address' => 'Apalit, Pampanga',
    'capacity' => '300 Guests',
    'price' => '25,000',
    'description' => 'An elegant venue for weddings and special occasions, featuring luxurious interiors, premium lighting, and flexible layouts.',
    'tag' => 'Indoor Elegant Venue',
    'offers' => [
      'Complete table and chair arrangement for up to 300 guests',
      'Decorative ambient lighting and chandelier setup',
      'Basic sound system with microphones and stage access',
      'Free parking area and private entrance access',
    ],
    'gallery' => [
      'https://images.unsplash.com/photo-1551926995-36a9be5a9e6f?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1540420773429-72d4c5db2e37?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1506784983877-45594efa4cbe?auto=format&fit=crop&w=900&q=80',
    ],
  ],
  'lios' => [
    'title' => 'LIOS Resort and Events Place',
    'address' => '#300 Danga, Colgante, Apalit, Pampanga',
    'capacity' => '250 Guests',
    'price' => '20,000',
    'description' => 'Private resort with swimming pool, fish pond, gazebo, billiard hall and barkada rooms for up to 36 pax. Whole-property rental for weddings, reunions and corporate events.',
    'tag' => 'Resort & Events Place',
    'offers' => [
      'Private pool access with lounge cabanas',
      'Garden ceremony and reception lawns',
      'Inclusive buffet catering options',
      'Dedicated coordination and welcome area',
    ],
    'gallery' => [
      'https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=900&q=80',
    ],
  ],
  'consuelo' => [
    'title' => 'Casa de Consuelo Private Resort and Events Place',
    'address' => 'Purok 1, Sto. Rosario Tabuyuc, Apalit, Pampanga',
    'capacity' => '220 Guests',
    'price' => '18,000',
    'description' => 'Private resort and events place with in-house catering services, ideal for weddings, birthdays and family celebrations.',
    'tag' => 'Private Resort & Events',
    'offers' => [
      'In-house catering and banquet setup',
      'Garden ceremony area with ambient lighting',
      'Guest accommodation and parking',
      'Live acoustic stage and sound system',
    ],
    'gallery' => [
      'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1496417263034-38ec4f0b665a?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1491336476513-0862ed0a3766?auto=format&fit=crop&w=900&q=80',
    ],
  ],
  'tehillah' => [
    'title' => 'La Tehillah Private Resort and Events Place',
    'address' => '92 Centro St., Brgy. Balucuc, Apalit, Pampanga',
    'capacity' => '200 Guests',
    'price' => '19,000',
    'description' => 'Resort and events venue offering all-in packages with accommodations, ideal for weddings and large celebrations.',
    'tag' => 'Resort & Accommodations',
    'offers' => [
      'Accommodation rooms for hosts and wedding party',
      'Private chapel and reception hall access',
      'Stylized set design and lighting packages',
      'Personal event manager and concierge support',
    ],
    'gallery' => [
      'https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1516779037-45a2bf5a4ff8?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&w=900&q=80',
    ],
  ],
];

$selectedVenue = $venues[$venueSlug] ?? null;
$hasValidVenue = $selectedVenue !== null;

function esc($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function buildMapUrl($address) {
  return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventIntel - Venue Details</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      background: #f7f6f3;
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
      filter: blur(150px);
      z-index: 0;
    }

    body::before {
      width: 420px;
      height: 420px;
      background: rgba(255, 206, 104, 0.10);
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

    .container {
      position: relative;
      z-index: 2;
      max-width: 1600px;
      margin: 0 auto;
      padding: 6px 48px 50px;
    }

    .navbar {
      width: 100%;
      padding: 6px 0 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-size: 26px;
      font-weight: 800;
      color: #222;
      letter-spacing: 1px;
    }

    .venue-card,
    .details-card,
    .venue-list-card {
      background: #ffffff;
      color: #111;
    }

    .details-card {
      border-color: rgba(0,0,0,.08);
    }

    .thumbnail {
      background: #ffffff;
    }

    .venue-list-card p,
    .subtitle,
    .venue-meta,
    .offer-item {
      color: #333;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 18px;
    }

    .nav-links button {
      padding: 8px 18px;
      border-radius: 12px;
      border: 1px solid rgba(0,0,0,0.08);
      background: rgba(255,255,255,0.92);
      color: #222;
      font-size: 14px;
      cursor: pointer;
      transition: 0.3s ease;
    }

    .nav-links button:hover,
    .nav-links .active {
      background: rgba(243,197,71,0.12);
      color: #111;
      box-shadow: 0 0 14px rgba(0,0,0,0.08);
    }

    .profile-btn {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      border: 1px solid rgba(0,0,0,0.1);
      background: #ffffff;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #111;
      cursor: pointer;
    }

    .hero {
      display: grid;
      grid-template-columns: 1.3fr 0.9fr;
      gap: 34px;
      margin-top: 10px;
    }

    .gallery {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .main-image {
      position: relative;
      width: 100%;
      min-height: 470px;
      overflow: hidden;
      border-radius: 28px;
      border: 1px solid rgba(0,0,0,0.08);
      background: #ffffff;
    }

    .main-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(1);
      transition: .4s ease;
    }

    .main-image:hover img {
      transform: scale(1.03);
      filter: brightness(1);
    }

    .main-image::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(255,255,255,.45), rgba(255,255,255,.12));
      pointer-events: none;
    }

    .thumbnail-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }

    .thumbnail {
      position: relative;
      height: 110px;
      border-radius: 20px;
      overflow: hidden;
      border: 1px solid rgba(0,0,0,0.08);
      cursor: pointer;
      transition: .3s ease;
      background: #ffffff;
    }

    .thumbnail img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(0.94);
      transition: .3s ease;
    }

    .thumbnail:hover {
      transform: translateY(-3px);
      border-color: rgba(255,215,0,.28);
    }

    .thumbnail:hover img,
    .thumbnail.active img {
      filter: brightness(.92);
      transform: scale(1.05);
    }

    .thumbnail.active {
      border: 1px solid rgba(243,197,71,.45);
      box-shadow: 0 0 18px rgba(243,197,71,.18);
    }

    .details-card {
      background: #ffffff;
      border: 1px solid rgba(0,0,0,0.08);
      border-radius: 30px;
      padding: 34px;
      box-shadow: 0 14px 30px rgba(0,0,0,0.06);
    }

    .tag {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 16px;
      border-radius: 999px;
      background: rgba(243,197,71,0.16);
      border: 1px solid rgba(243,197,71,0.22);
      color: #111;
      font-size: 13px;
      margin-bottom: 20px;
    }

    .details-card h1 {
      font-size: 48px;
      margin-bottom: 14px;
      line-height: 1.1;
      color: #111;
    }

    .subtitle {
      color: #333;
      line-height: 1.7;
      margin-bottom: 28px;
      font-size: 15px;
    }

    .stats {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
      margin-bottom: 28px;
    }

    .stat-box {
      padding: 18px;
      border-radius: 22px;
      background: rgba(243,243,243,0.85);
      border: 1px solid rgba(0,0,0,0.08);
    }

    .stat-box span {
      display: block;
      color: #555;
      font-size: 13px;
      margin-bottom: 8px;
    }

    .stat-box strong {
      color: #111;
      font-size: 24px;
      font-weight: 800;
    }

    .offers-title {
      font-size: 20px;
      margin-bottom: 18px;
    }

    .offers {
      display: grid;
      gap: 14px;
      margin-bottom: 32px;
    }

    .offer-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 14px 16px;
      border-radius: 18px;
      background: rgba(243,197,71,0.10);
      border: 1px solid rgba(243,197,71,0.14);
      color: #222;
    }

    .offer-item i {
      width: 38px;
      height: 38px;
      border-radius: 12px;
      display: flex;
      justify-content: center;
      align-items: center;
      background: rgba(243,197,71,0.18);
      color: #111;
      flex-shrink: 0;
    }

    .actions {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
    }

    .book-btn,
    .location-btn,
    .details-btn {
      flex: 1;
      min-width: 140px;
      height: 58px;
      border-radius: 18px;
      border: none;
      cursor: pointer;
      font-size: 15px;
      font-weight: 800;
      transition: .3s ease;
      text-decoration: none;
      display: inline-flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
    }

    .book-btn {
      background: linear-gradient(135deg, #ffeaa4, #f3c547, #c78f08);
      color: #111;
    }

    .book-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 24px rgba(243,197,71,.25);
    }

    .location-btn,
    .details-btn {
      background: rgba(0,0,0,0.05);
      color: #111;
      border: 1px solid rgba(0,0,0,0.08);
    }

    .location-btn:hover,
    .details-btn:hover {
      background: rgba(0,0,0,0.08);
      transform: translateY(-2px);
    }

    .venue-list {
      display: grid;
      grid-template-columns: repeat(3, minmax(260px, 1fr));
      gap: 24px;
      margin-top: 28px;
    }

    .venue-list-card {
      background: #ffffff;
      border: 1px solid rgba(0,0,0,0.08);
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 14px 30px rgba(0,0,0,0.06);
    }

    .venue-list-card img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      display: block;
    }

    .venue-list-card .card-body {
      padding: 22px;
    }

    .venue-list-card h2 {
      font-size: 22px;
      margin-bottom: 10px;
      color: #111;
    }

    .venue-list-card p {
      color: #333;
      font-size: 14px;
      margin-bottom: 18px;
      min-height: 56px;
    }

    .venue-list-card .card-meta {
      color: #555;
      font-size: 13px;
      margin-bottom: 18px;
    }

    .venue-list-card .card-actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .venue-list-card .book-btn,
    .venue-list-card .details-btn {
      width: calc(50% - 6px);
    }

    .venue-error {
      background: rgba(220,38,38,.12);
      border: 1px solid rgba(220,38,38,.25);
      color: #bb1e1e;
      padding: 16px 20px;
      border-radius: 20px;
      margin-top: 24px;
    }

    .addon-modal {
      position: fixed;
      inset: 0;
      display: none;
      align-items: center;
      justify-content: center;
      background: rgba(15, 15, 15, 0.35);
      z-index: 9999;
      padding: 24px;
    }

    .addon-modal.active {
      display: flex;
    }

    .addon-modal-content {
      width: min(680px, 100%);
      background: #ffffff;
      border-radius: 28px;
      padding: 28px;
      box-shadow: 0 30px 70px rgba(0, 0, 0, 0.18);
      color: #111;
    }

    .addon-modal-header h2 {
      font-size: 26px;
      margin-bottom: 10px;
      color: #111;
    }

    .addon-modal-header p {
      font-size: 15px;
      color: #444;
      margin-bottom: 22px;
    }

    .availability-list {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
      margin-top: 16px;
    }

    .availability-day {
      padding: 16px 18px;
      border-radius: 18px;
      border: 1px solid rgba(0,0,0,0.08);
      color: #111;
      background: rgba(248,248,248,0.95);
      font-weight: 700;
    }

    .availability-day.available {
      border-color: rgba(56, 177, 85, 0.22);
      background: rgba(227, 250, 231, 0.8);
      color: #135d2a;
    }

    .availability-day.busy {
      border-color: rgba(220, 38, 38, 0.18);
      background: rgba(255, 242, 242, 0.9);
      color: #9f1c1c;
    }

    .addon-list {
      display: grid;
      gap: 16px;
      margin-top: 16px;
    }

    .addon-item {
      border: 1px solid rgba(0,0,0,0.08);
      border-radius: 18px;
      background: rgba(255,255,255,0.96);
      padding: 16px 18px;
    }

    .addon-item label {
      display: flex;
      align-items: center;
      gap: 16px;
      width: 100%;
      cursor: pointer;
      color: #111;
    }

    .addon-item input[type="checkbox"] {
      width: 18px;
      height: 18px;
      accent-color: #c78f08;
    }

    .addon-item i {
      width: 44px;
      height: 44px;
      display: grid;
      place-items: center;
      border-radius: 14px;
      background: rgba(243,197,71,0.16);
      color: #111;
    }

    .addon-item-text {
      display: grid;
      gap: 4px;
    }

    .addon-item-name {
      font-weight: 700;
      color: #111;
    }

    .addon-item-desc {
      color: #555;
      font-size: 13px;
    }

    .addon-modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 26px;
      flex-wrap: wrap;
    }

    .cancel-btn,
    .confirm-btn {
      min-width: 140px;
      padding: 14px 18px;
      border-radius: 16px;
      border: 1px solid transparent;
      cursor: pointer;
      font-weight: 700;
      transition: 0.25s ease;
    }

    .cancel-btn {
      background: #f4f4f4;
      color: #222;
      border-color: rgba(0,0,0,0.08);
    }

    .cancel-btn:hover {
      background: #e8e8e8;
    }

    .confirm-btn {
      background: linear-gradient(135deg, #ffeaa4, #f3c547, #c78f08);
      color: #111;
      border-color: transparent;
    }

    .confirm-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 24px rgba(243,197,71,0.2);
    }

    @media (max-width: 1200px) {
      .hero {
        grid-template-columns: 1fr;
      }

      .venue-list {
        grid-template-columns: 1fr;
      }

      .venue-list-card .book-btn,
      .venue-list-card .details-btn {
        width: 100%;
      }
    }
  </style>

</head>
<body>
  <div class="container">
    <div class="navbar">
      <div class="logo">EventIntel</div>

      <div class="nav-links">
        <button onclick="window.location.href='homepage.php'">Home</button>
        <button class="active" onclick="window.location.href='createevent.php'">Create Event</button>
        <button onclick="window.location.href='yourevents.php'">Your Events</button>
        <div class="profile-btn">
          <i class="fa-regular fa-user"></i>
        </div>
      </div>
    </div>

    <?php if (!$hasValidVenue): ?>
      <div class="details-card" style="margin-top: 20px;">
        <div class="tag">
          <i class="fa-solid fa-map-location-dot"></i>
          Select a featured venue
        </div>
        <h1>Browse our featured venues</h1>
        <p class="subtitle">View detailed information for LIOS Resort, Casa de Consuelo, and La Tehillah. Click any card to see full venue details or book directly.</p>

        <div class="venue-list">
          <?php foreach ($venues as $key => $venue): ?>
            <div class="venue-list-card">
              <img src="<?= esc($venue['gallery'][0]) ?>" alt="<?= esc($venue['title']) ?>">
              <div class="card-body">
                <h2><?= esc($venue['title']) ?></h2>
                <div class="card-meta"><?= esc($venue['address']) ?> · <?= esc($venue['capacity']) ?></div>
                <p><?= esc($venue['description']) ?></p>
                <div class="card-actions">
                  <a class="details-btn" href="?venue=<?= esc($key) ?>">View Details</a>
                  <button class="book-btn" type="button" onclick="selectVenue('<?= esc($key) ?>')">Book</button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="hero">
        <div class="gallery">
          <div class="main-image">
            <img id="mainVenueImage" src="<?= esc($selectedVenue['gallery'][0]) ?>" alt="<?= esc($selectedVenue['title']) ?>">
          </div>

          <div class="thumbnail-row">
            <?php foreach ($selectedVenue['gallery'] as $index => $thumb): ?>
              <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" data-image="<?= esc($thumb) ?>">
                <img src="<?= esc($thumb) ?>" alt="Thumbnail <?= $index + 1 ?>">
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="details-card">
          <div class="tag">
            <i class="fa-solid fa-crown"></i>
            <?= esc($selectedVenue['tag']) ?>
          </div>

          <h1><?= esc($selectedVenue['title']) ?></h1>

          <p class="subtitle"><?= esc($selectedVenue['description']) ?></p>

          <div class="stats">
            <div class="stat-box">
              <span>Capacity</span>
              <strong><?= esc($selectedVenue['capacity']) ?></strong>
            </div>
            <div class="stat-box">
              <span>Starting Price</span>
              <strong>₱<?= esc($selectedVenue['price']) ?></strong>
            </div>
          </div>

          <div class="stats">
            <div class="stat-box">
              <span>Location</span>
              <strong><?= esc($selectedVenue['address']) ?></strong>
            </div>
            <div class="stat-box">
              <span>Venue</span>
              <strong><?= esc($selectedVenue['title']) ?></strong>
            </div>
          </div>

          <div class="offers-title">Included Offers</div>

          <div class="offers">
            <?php foreach ($selectedVenue['offers'] as $offer): ?>
              <div class="offer-item">
                <i class="fa-solid fa-check"></i>
                <?= esc($offer) ?>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="actions">
            <button class="book-btn" type="button" onclick="openAvailabilityModal('<?= esc($selectedVenue['title']) ?>')">
              <i class="fa-solid fa-calendar-check"></i>
              Book Venue
            </button>
            <a class="location-btn" href="<?= esc(buildMapUrl($selectedVenue['address'])) ?>" target="_blank" rel="noopener noreferrer">
              <i class="fa-solid fa-location-dot"></i>
              View Location
            </a>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div id="availabilityModal" class="addon-modal">
    <div class="addon-modal-content availability-modal-content">
      <div class="addon-modal-header">
        <h2>Venue Availability</h2>
        <p id="availabilityModalText">Check the next 7 days for your selected time slot.</p>
      </div>
      <div id="availabilityContent">
        <p style="color:#555;">No specific schedule has been selected yet. This is a preview of available dates.</p>
        <div class="availability-list">
          <div class="availability-day available"><strong>May 8</strong>Open</div>
          <div class="availability-day available"><strong>May 9</strong>Open</div>
          <div class="availability-day busy"><strong>May 10</strong>Booked</div>
          <div class="availability-day available"><strong>May 11</strong>Open</div>
          <div class="availability-day available"><strong>May 12</strong>Open</div>
          <div class="availability-day busy"><strong>May 13</strong>Booked</div>
          <div class="availability-day available"><strong>May 14</strong>Open</div>
        </div>
      </div>
      <div class="addon-modal-footer">
        <button class="cancel-btn" onclick="closeAvailabilityModal()">Cancel</button>
        <button class="confirm-btn" onclick="continueToAddons()">Continue to Add-ons</button>
      </div>
    </div>
  </div>

  <div id="addonModal" class="addon-modal">
    <div class="addon-modal-content">
      <div class="addon-modal-header">
        <h2>Venue Add-ons</h2>
        <p>Select additional services this venue can provide.</p>
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

  <script>
    const venueImages = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('mainVenueImage');
    const currentVenueSlug = '<?= esc($venueSlug) ?>';
    const currentVenueTitle = '<?= esc($selectedVenue['title'] ?? '') ?>';

    venueImages.forEach(function(thumbnail) {
      thumbnail.addEventListener('click', function() {
        document.querySelectorAll('.thumbnail').forEach(function(item) {
          item.classList.remove('active');
        });
        thumbnail.classList.add('active');
        const imageUrl = thumbnail.getAttribute('data-image');
        if (mainImage && imageUrl) {
          mainImage.src = imageUrl;
        }
      });
    });

    function selectVenue(slug, venueName, addons) {
      const name = venueName || slug;
      const normalizedVenue = String(name || '').trim();
      const selectedAddons = Array.isArray(addons)
        ? addons.map(function(item) { return String(item || '').trim(); }).filter(Boolean)
        : String(addons || '').split(',').map(function(item) { return String(item || '').trim(); }).filter(Boolean);

      if (normalizedVenue) {
        try {
          sessionStorage.setItem('event_selection_venue', normalizedVenue);
          sessionStorage.setItem('event_selection_venue_name', normalizedVenue);
          document.cookie = 'event_selection_venue=' + encodeURIComponent(normalizedVenue) + '; path=/; max-age=3600';
          document.cookie = 'event_selection_venue_name=' + encodeURIComponent(normalizedVenue) + '; path=/; max-age=3600';
        } catch (error) {
          console.warn('Unable to persist venue selection', error);
        }
      }

      const addonMap = {
        clothing: 'clothes',
        clothes: 'clothes',
        catering: 'catering',
        host: 'host',
        photographer: 'photographer',
        sounds_lights: 'sounds_lights'
      };

      const selectedAddonKeys = selectedAddons.map(function(addon) {
        return addonMap[addon] || addon;
      }).filter(Boolean);

      selectedAddonKeys.forEach(function(key) {
        try {
          sessionStorage.setItem('event_selection_' + key, normalizedVenue);
          document.cookie = 'event_selection_' + key + '=' + encodeURIComponent(normalizedVenue) + '; path=/; max-age=3600';
        } catch (error) {
          console.warn('Unable to persist addon selection for ' + key, error);
        }
      });

      try {
        sessionStorage.setItem('event_selection_addons', JSON.stringify(selectedAddonKeys));
        document.cookie = 'event_selection_addons=' + encodeURIComponent(JSON.stringify(selectedAddonKeys)) + '; path=/; max-age=3600';
      } catch (error) {
        console.warn('Unable to persist selected add-ons', error);
      }

      const params = new URLSearchParams(window.location.search);
      const from = params.get('from');
      const isModal = params.get('modal') === 'true';
      const returnUrl = params.get('return') || 'createevent.php';
      const selectedParams = ['venue'].concat(selectedAddonKeys).join(',');

      if (from === 'createevent' && isModal) {
        const message = {
          type: 'serviceSelected',
          service: 'venue',
          venue: normalizedVenue,
          venue_name: normalizedVenue,
        };
        if (selectedAddonKeys.length > 0) {
          message.addons = selectedAddonKeys;
        }

        if (window.parent && window.parent !== window) {
          window.parent.postMessage(message, '*');
          try {
            const parentModal = window.parent.document.getElementById('serviceModal');
            if (parentModal) {
              parentModal.style.display = 'none';
              parentModal.innerHTML = '';
            }
          } catch (error) {
            console.warn('Unable to remove parent iframe modal directly', error);
          }
          return;
        }

        if (window.opener && !window.opener.closed) {
          window.opener.postMessage(message, '*');
          return;
        }
      }

      if (from === 'createevent') {
        window.location.href = returnUrl + '?selected=' + encodeURIComponent(selectedParams);
      } else {
        alert('Venue selected: ' + normalizedVenue + (selectedAddonKeys.length ? ' (Add-ons: ' + selectedAddonKeys.join(', ') + ')' : ''));
      }
    }

    let selectedVenueName = currentVenueTitle;

    function openAvailabilityModal(venueName) {
      selectedVenueName = venueName || currentVenueTitle;
      document.getElementById('availabilityModalText').textContent = 'Availability for ' + selectedVenueName;
      document.getElementById('availabilityModal').classList.add('active');
    }

    function closeAvailabilityModal() {
      document.getElementById('availabilityModal').classList.remove('active');
    }

    function continueToAddons() {
      closeAvailabilityModal();
      document.getElementById('addonModal').classList.add('active');
    }

    document.getElementById('availabilityModal').addEventListener('click', function(event) {
      if (event.target === this) {
        closeAvailabilityModal();
      }
    });

    document.getElementById('addonModal').addEventListener('click', function(event) {
      if (event.target === this) {
        closeAddonModal();
      }
    });

    function closeAddonModal() {
      document.getElementById('addonModal').classList.remove('active');
    }

    function confirmAddons() {
      const addonCheckboxes = document.querySelectorAll('#addonModal input[name="addon"]:checked');
      const selectedAddons = Array.from(addonCheckboxes).map(function(cb) {
        return cb.value;
      });
      selectVenue(currentVenueSlug, selectedVenueName, selectedAddons);
      closeAddonModal();
    }
  </script>
</body>
</html>
