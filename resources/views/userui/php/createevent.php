<?php require_once __DIR__ . '/../../config/db.php'; require_role('client');

$backendError = trim($_GET['error'] ?? '');

// When arriving from packages.php, auto-fetch the best supplier for each
// service category included in the chosen package so the user doesn't have
// to manually search for every provider.
$packageAutoFill = [];
if (($fromPackage = trim($_GET['from'] ?? '')) === 'package') {
  $pkgServicesParam = trim($_GET['services'] ?? '');
  if ($pkgServicesParam !== '') {
    $pdo = db();
    $catMap = [
      'venue' => 'Venue',
      'catering' => 'Catering',
      'host' => 'Host',
      'photographer' => 'Photographer',
      'sounds_lights' => 'Sounds & Lights',
      'clothes' => 'Clothing',
      'church' => 'Church',
      'rental_car' => 'Rental Car',
    ];
    $svcKeys = array_filter(array_map('trim', explode(',', $pkgServicesParam)));
    foreach ($svcKeys as $key) {
      if (!isset($catMap[$key])) continue;
      $cat = $catMap[$key];
      // Prefer highest rated, then cheapest as a tie-breaker
      $stmt = $pdo->prepare(
        "SELECT name, rating, price FROM supplier_services
         WHERE category = ? AND price IS NOT NULL AND price > 0
         ORDER BY rating DESC, price ASC LIMIT 1"
      );
      $stmt->execute([$cat]);
      $best = $stmt->fetch();
      if ($best) {
        $packageAutoFill[$key] = $best['name'];
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventIntel - Create Event</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Segoe UI", sans-serif;
    }

    :root {
      --bg: #f8f8f8;
      --panel: rgba(255,255,255,0.78);
      --panel-2: rgba(255,255,255,0.88);
      --gold: #d4a017;
      --gold-soft: rgba(212,160,23,0.12);
      --border: rgba(212,160,23,0.14);
      --text: #111111;
      --muted: #666666;
    }

    body {
      background:
        radial-gradient(circle at top left, rgba(243,197,71,0.12), transparent 28%),
        radial-gradient(circle at bottom right, rgba(243,197,71,0.08), transparent 32%),
        var(--bg);
      color: var(--text);
      min-height: 100vh;
      overflow-x: hidden;
      overflow-y: auto;
      position: relative;
    }

    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background:
        linear-gradient(rgba(0,0,0,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,0,0,0.03) 1px, transparent 1px);
      background-size: 60px 60px;
      mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
      pointer-events: none;
      z-index: 0;
    }

    .event-bg {
      position: fixed;
      inset: 0;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      opacity: 0.28;
      z-index: 0;
      overflow: hidden;
    }

    .event-bg img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: blur(2px) brightness(1) saturate(0.9);
      transform: scale(1.08);
    }

    .event-bg::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(
        to bottom,
        rgba(255,255,255,0.82),
        rgba(255,255,255,0.55) 35%,
        rgba(255,255,255,0.88)
      );
    }

    .container {
      position: relative;
      z-index: 2;
      width: min(1600px, 100%);
      margin: 0 auto;
      min-height: 100vh;
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
      position: relative;
      z-index: 3;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 8px;
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
      border: 1px solid rgba(212,160,23,0.25);
      background: rgba(255,255,255,0.75);
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      transition: 0.3s ease;
      color: var(--gold);
      font-size: 18px;
      backdrop-filter: blur(10px);
    }

    .profile-btn:hover {
      background: rgba(255,255,255,0.95);
      box-shadow: 0 0 14px rgba(255, 215, 0, 0.18);
    }

    .inline-error-banner {
      margin: 18px 0 0;
      padding: 14px 16px;
      border-radius: 14px;
      background: rgba(220,38,38,0.1);
      border: 1px solid rgba(220,38,38,0.2);
      color: #991b1b;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 14px;
      position: relative;
      z-index: 3;
    }

    .hero-bar {
      display: flex;
      justify-content: space-between;
      align-items: end;
      margin-bottom: 26px;
    }

    .hero-text small {
      color: var(--gold);
      letter-spacing: 4px;
      text-transform: uppercase;
      font-size: 12px;
    }

    .hero-text h1 {
      margin-top: 8px;
      font-size: 56px;
      line-height: 1;
      font-weight: 900;
      color: #111;
    }

    .hero-text p {
      margin-top: 14px;
      max-width: 620px;
      color: var(--muted);
      line-height: 1.6;
      font-size: 15px;
    }

    .progress {
      display: flex;
      gap: 14px;
    }

    .step {
      width: 54px;
      height: 54px;
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255,255,255,0.65);
      border: 1px solid var(--border);
      color: var(--muted);
      backdrop-filter: blur(14px);
    }

    .step.active {
      background: linear-gradient(135deg, #ffe27d, #c78f08);
      color: #111;
      box-shadow: 0 0 18px rgba(243,197,71,0.35);
    }

    .content {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      gap: 28px;
      padding-bottom: 50px;
      flex-wrap: wrap;
    }

    .left-column,
    .right-column {
      display: flex;
      flex-direction: column;
      gap: 24px;
      flex: 1;
      min-width: min(100%, 360px);
      max-width: 620px;
    }

    .card {
      position: relative;
      background: var(--panel);
      border: 1px solid rgba(212,160,23,0.12);
      border-radius: 30px;
      padding: 28px;
      backdrop-filter: blur(22px);
      box-shadow: 0 18px 40px rgba(0,0,0,0.08);
      overflow: hidden;
    }

    .card::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(243,197,71,0.45), transparent);
    }

    .card-title {
      display: flex;
      align-items: center;
      gap: 12px;
      color: var(--gold);
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 22px;
      letter-spacing: 1px;
    }

    .event-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
    }

    .event-option {
      position: relative;
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 18px;
      border-radius: 20px;
      background: rgba(255,255,255,0.68);
      border: 1px solid rgba(0,0,0,0.06);
      transition: .3s ease;
      cursor: pointer;
    }

    .event-option:hover {
      transform: translateY(-3px);
      border-color: rgba(243,197,71,0.35);
      background: rgba(243,197,71,0.08);
      box-shadow: 0 10px 24px rgba(243,197,71,0.08);
    }

    .event-option input {
      accent-color: var(--gold);
      width: 18px;
      height: 18px;
    }

    .event-option span {
      font-size: 15px;
      font-weight: 600;
      color: #111;
    }

    .theme-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .theme-chip {
      padding: 10px 16px;
      border-radius: 999px;
      border: 1px solid rgba(212,160,23,0.22);
      background: rgba(255,255,255,0.72);
      color: #333;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: .25s ease;
    }

    .theme-chip:hover {
      border-color: rgba(212,160,23,0.5);
      background: rgba(243,197,71,0.1);
      transform: translateY(-2px);
    }

    .theme-chip.selected {
      background: linear-gradient(135deg, #ffe27d, #c78f08);
      color: #111;
      border-color: transparent;
      box-shadow: 0 6px 16px rgba(243,197,71,.25);
    }

    .package-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
    }

    .package-card {
      border: 1px solid rgba(212,160,23,0.18);
      border-radius: 18px;
      padding: 16px;
      background: rgba(255,255,255,0.8);
      cursor: pointer;
      transition: .25s ease;
      text-align: center;
    }

    .package-card:hover {
      transform: translateY(-3px);
      border-color: rgba(212,160,23,0.45);
      box-shadow: 0 10px 22px rgba(243,197,71,.14);
    }

    .package-card.selected {
      border-color: #d4a017;
      background: linear-gradient(135deg, #fff7dc, #fffdf4);
      box-shadow: 0 10px 24px rgba(212,160,23,.2);
    }

    .package-card h4 {
      font-size: 14px;
      font-weight: 800;
      color: #111;
      margin-bottom: 4px;
    }

    .package-card .pkg-price {
      color: #d4a017;
      font-weight: 800;
      font-size: 17px;
      margin-bottom: 8px;
    }

    .package-card .pkg-includes {
      font-size: 11px;
      color: #777;
      line-height: 1.5;
    }

    .other-input,
    .field input {
      width: 100%;
      padding: 16px 18px;
      border-radius: 18px;
      background: rgba(255,255,255,0.68);
      border: 1px solid rgba(0,0,0,0.06);
      color: #111;
      outline: none;
      transition: .3s ease;
    }

    .other-input {
      margin-top: 18px;
    }
    .other-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background: rgba(230,230,230,0.7);
}

    .other-input:focus,
    .field input:focus {
      border-color: rgba(243,197,71,0.45);
      box-shadow: 0 0 0 4px rgba(243,197,71,0.08);
      background: rgba(255,255,255,0.95);
    }

    .schedule-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
    }

    .field label {
      display: block;
      margin-bottom: 10px;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: var(--muted);
    }

    .field.full {
      grid-column: span 2;
    }

    .services {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .service-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 18px 20px;
      border-radius: 22px;
      background: var(--panel-2);
      border: 1px solid rgba(0,0,0,0.06);
      transition: .3s ease;
    }

    .service-row:hover {
      transform: translateX(6px);
      border-color: rgba(243,197,71,0.25);
      box-shadow: 0 12px 24px rgba(0,0,0,0.08);
    }

    .service-name {
      display: flex;
      align-items: center;
      gap: 14px;
      font-weight: 600;
      font-size: 16px;
      color: #111;
    }

    .service-name i {
      width: 42px;
      height: 42px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(243,197,71,0.1);
      color: var(--gold);
    }

    .service-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .view-btn {
      border: none;
      padding: 12px 20px;
      border-radius: 999px;
      background: rgba(255,255,255,0.7);
      color: var(--gold);
      font-weight: 700;
      cursor: pointer;
      transition: .3s ease;
    }

    .view-btn:hover {
      background: linear-gradient(135deg, #ffe27d, #c88f09);
      color: #111;
    }

    .service-check {
      width: 42px;
      height: 42px;
      border-radius: 14px;
      accent-color: var(--gold);
      cursor: pointer;
    }

    .status {
      width: 42px;
      height: 42px;
      border-radius: 14px;
      background: rgba(40,140,70,0.12);
      color: #2fa45e;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1px solid rgba(40,140,70,0.12);
    }

    .hidden-step {
      display: none;
      opacity: 0;
      pointer-events: none;
      transform: translateY(12px);
      transition: all 0.3s ease;
    }

    .step-indicator {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 12px;
      margin-bottom: 24px;
      max-width: 260px;
    }

    .step-pill {
      padding: 14px 20px;
      border-radius: 999px;
      background: rgba(255,255,255,0.95);
      color: #111;
      font-weight: 700;
      letter-spacing: .4px;
      transition: .3s ease;
      min-width: 100%;
      box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }

    .step-pill.active {
      background: linear-gradient(135deg, #ffe27d, #c78f08);
      color: #111;
      box-shadow: 0 0 16px rgba(243,197,71,.18);
    }

    .footer-actions {
      margin-top: auto;
      display: flex;
      justify-content: flex-end;
      gap: 14px;
      padding-top: 12px;
    }

    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.58);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      padding: 20px;
    }

    .modal-overlay.is-open {
      display: flex;
    }

    .modal-dialog {
      width: min(780px, 100%);
      max-height: 90vh;
      overflow: auto;
      border-radius: 30px;
      background: rgba(255,255,255,0.95);
      backdrop-filter: blur(24px);
      box-shadow: 0 24px 60px rgba(0,0,0,0.25);
      padding: 28px;
      position: relative;
    }

    /* Hide visible scrollbar on modal but remain scrollable */
    .modal-dialog {
      -ms-overflow-style: none; /* IE and Edge */
      scrollbar-width: none; /* Firefox */
    }
    .modal-dialog::-webkit-scrollbar {
      display: none; /* Chrome, Safari, Opera */
      width: 0;
      height: 0;
    }

    .modal-close {
      position: absolute;
      top: 16px;
      right: 16px;
      width: 40px;
      height: 40px;
      border: none;
      border-radius: 50%;
      background: #f3c547;
      color: #111;
      font-size: 18px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .modal-close:hover {
      background: #e8b70f;
    }

    .modal-body {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    /* Services modal: make smaller and avoid internal scrolling */
    #servicesModal .modal-dialog {
      width: min(640px, 100%);
      max-height: none;
      overflow: visible;
      padding: 18px;
    }
    #servicesModal .modal-body .card {
      padding: 14px;
      border-radius: 20px;
    }
    #servicesModal .services {
      gap: 10px;
      max-height: none;
      overflow: visible;
    }
    #servicesModal .service-row {
      padding: 12px 14px;
      border-radius: 14px;
    }

    .modal-body .card {
      padding: 24px;
      box-shadow: none;
      background: rgba(255,255,255,0.82);
      border: 1px solid rgba(212,160,23,0.16);
      border-radius: 24px;
    }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 18px;
    }

    .modal-actions button {
      padding: 12px 20px;
      border-radius: 999px;
      border: none;
      cursor: pointer;
      font-weight: 700;
    }

    .modal-actions .secondary-btn {
      background: rgba(0,0,0,0.06);
      color: #222;
    }

    .modal-actions .primary-btn {
      background: linear-gradient(135deg, #ffe27d, #c78f08);
      color: #111;
    }

    .footer-actions button {
      padding: 16px 32px;
      border-radius: 18px;
      font-size: 15px;
      font-weight: 700;
      border: none;
      cursor: pointer;
      transition: .3s ease;
    }

    .cancel-btn {
      background: rgba(0,0,0,0.05);
      color: #222;
      border: 1px solid rgba(0,0,0,0.08);
    }

    .create-btn {
      background: linear-gradient(135deg, #fff1a8, #f3c547 45%, #c98f08);
      color: #111;
      box-shadow: 0 18px 35px rgba(243,197,71,0.25);
    }

    .cancel-btn:hover,
    .create-btn:hover {
      transform: translateY(-3px);
    }

    ::-webkit-scrollbar {
      width: 7px;
    }

    ::-webkit-scrollbar-thumb {
      background: rgba(243,197,71,0.45);
      border-radius: 999px;
    }

    @media (max-width: 1200px) {
      .content {
        grid-template-columns: 1fr;
      }

      .hero-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="event-bg">
    <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=900&q=80">
    <img src="https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=900&q=80">
    <img src="https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=900&q=80">
    <img src="https://images.unsplash.com/photo-1505236858219-8359eb29e329?auto=format&fit=crop&w=900&q=80">
  </div>

  <div class="container">
    <?php if ($backendError !== ''): ?>
    <div class="inline-error-banner" role="alert">
      <i class="fas fa-exclamation-circle"></i>
      <span><?= esc($backendError) ?></span>
    </div>
    <?php endif; ?>
    <div class="navbar">
      <div class="logo">EventIntel</div>

      <div class="nav-links">
        <button type="button" onclick="window.location.href='homepage.php'">Home</button>
        <button type="button" class="active" onclick="window.location.href='createevent.php'">Create Event</button>
        <button type="button" onclick="window.location.href='yourevents.php'">Your Events</button>
        <button type="button" onclick="window.location.href='recommendation.php'">Recommendations</button>
        <button type="button" onclick="window.location.href='packages.php'">Packages</button>
        <button type="button" onclick="window.location.href='newsfeed.php'">Newsfeed</button>
        <button class="profile-btn" type="button" aria-label="Profile" title="Profile" onclick="window.location.href='profile.php'">
          <i class="fas fa-user"></i>
        </button>
      </div>
    </div>

    <h1>Create an Event</h1>
    <br>
    <div class="step-indicator">
      <span class="step-pill active" data-step="1">1. Choose Event</span>
      <span class="step-pill" data-step="2">2. Schedule</span>
      <span class="step-pill" data-step="3">3. Services</span>
    </div>
    <form action="save_event.php" method="POST" onsubmit="syncSelectionsToForm()">
    <input type="hidden" name="venue" id="selectedVenue">
    <input type="hidden" name="venue_name" id="selectedVenueName">
    <input type="hidden" name="clothes" id="selectedClothes">
    <input type="hidden" name="catering" id="selectedCatering">
    <input type="hidden" name="host" id="selectedHost">
    <input type="hidden" name="photographer" id="selectedPhotographer">
    <input type="hidden" name="sounds_lights" id="selectedSoundsLights">
    <input type="hidden" name="wedding_type" id="wedding_type_input">
    <input type="hidden" name="reception_type" id="reception_type_input">
    <input type="hidden" name="church" id="selectedChurch">
    <input type="hidden" name="rental_car" id="selectedRentalCar">
    <input type="hidden" name="theme" id="theme_input">
    <input type="hidden" name="budget" id="budget_input">

    <div class="modal-overlay" id="eventModal" role="dialog" aria-modal="true" aria-label="Create your event">
      <div class="modal-dialog">
        <div class="modal-body">
          <div class="card">
            <div class="card-title">Event Details</div>

            <div class="event-grid">
              <label class="event-option"><input type="radio" name="event_type" value="Birthday" required><span>Birthday</span></label>
              <label class="event-option"><input type="radio" name="event_type" value="Debut" required><span>Debut</span></label>
              <label class="event-option"><input type="radio" name="event_type" value="Wedding" required><span>Wedding</span></label>
              <label class="event-option"><input type="radio" name="event_type" value="Anniversary" required><span>Anniversary</span></label>
              <label class="event-option"><input type="radio" name="event_type" value="Christening" required><span>Christening</span></label>
              <label class="event-option"><input type="radio" name="event_type" value="Gender Reveal" required><span>Gender Reveal</span></label>
              <label class="event-option"><input type="radio" name="event_type" value="Reunion" required><span>Reunion</span></label>
              <label class="event-option"><input type="radio" name="event_type" value="Others" required><span>Others</span></label>
            </div>

            <input class="other-input" type="text" name="other_event_type" id="otherEventType" placeholder="Other event type..." disabled>

            <div class="card-title" style="margin-top:24px;">Theme <span style="font-size:12px;color:#999;font-weight:400;text-transform:none;letter-spacing:0;">(pick a theme for this event)</span></div>
            <div id="themePanel" style="display:none;">
              <div id="themeChips" class="theme-grid"></div>
              <input class="other-input" type="text" id="customThemeInput" placeholder="Type your custom theme..." style="display:none;margin-top:12px;">
              <div id="themeError" style="display:none;margin-top:10px;color:#991b1b;font-size:13px;background:rgba(220,38,38,.08);padding:10px 14px;border-radius:12px;border:1px solid rgba(220,38,38,.2);">Please select a theme for your event.</div>
            </div>

            <div class="card-title" style="margin-top:24px;">Suggested Packages</div>
            <div id="packagePanel" style="display:none;">
              <p style="color:#666;font-size:13px;margin-bottom:14px;line-height:1.6;">Based on your event type, here are popular package options. Choose one to pre-fill your budget, or browse all packages.</p>
              <div id="packageChips" class="package-grid"></div>
              <button type="button" class="view-btn" style="margin-top:12px;" onclick="window.location.href='packages.php'"><i class="fa-solid fa-box"></i> View All Packages</button>
            </div>

            <div class="field" style="margin-top:24px;">
              <label>Estimated Budget (₱)</label>
              <input type="number" name="event_budget" id="eventBudgetInput" placeholder="e.g. 50000" min="0">
            </div>

            <div class="card-title" style="margin-top:24px;">Schedule & Attendees</div>
            <div class="schedule-grid">
              <div class="field">
                <label>Date</label>
                <input type="date" name="event_date" required>
              </div>

              <div class="field">
                <label>Start Time</label>
                <input type="time" name="event_time" required>
              </div>

              <div class="field">
                <label>End Time</label>
                <input type="time" name="event_end_time" required>
              </div>

              <div class="field">
                <label>Number of Attendees</label>
                <input type="number" name="guest_count" placeholder="120" min="1" required>
              </div>
            </div>

            <div class="modal-actions">
              <button type="button" class="secondary-btn" onclick="window.location.href='homepage.php'">Cancel</button>
              <button type="button" class="primary-btn" onclick="goToServices()">Next</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Wedding details modal (shown when Wedding selected before services) -->
    <div class="modal-overlay" id="weddingModal" role="dialog" aria-modal="true" aria-label="Wedding details">
      <div class="modal-dialog">
        <div class="modal-body">
          <div class="card">
            <div class="card-title">Wedding Details</div>
            <div class="field full">
              <label>Type of Wedding</label>
              <select id="weddingType" style="width:100%;padding:14px;border-radius:12px;border:1px solid rgba(0,0,0,0.06);">
                <option value="">-- Select wedding type --</option>
                <option value="Traditional">Traditional</option>
                <option value="Destination">Destination</option>
                <option value="Civil">Civil</option>
              </select>
            </div>

            <div class="field full">
              <label>Reception Type</label>
              <select id="receptionType" style="width:100%;padding:14px;border-radius:12px;border:1px solid rgba(0,0,0,0.06);">
                <option value="">-- Select reception type --</option>
                <option value="Intimate">Intimate</option>
                <option value="Grand">Grand</option>
                <option value="Buffet">Buffet</option>
                <option value="Cocktail">Cocktail</option>
                <option value="Seated Dinner">Seated Dinner</option>
              </select>
            </div>

            <div class="modal-actions">
              <button type="button" class="secondary-btn" onclick="closeModal('weddingModal'); openModal('eventModal')">Back</button>
              <button type="button" class="primary-btn" id="weddingNextBtn">Next</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal-overlay" id="servicesModal" role="dialog" aria-modal="true" aria-label="Choose services">
      <div class="modal-dialog">
        <div class="modal-body">
          <div class="card" id="servicePanel">
            <div class="card-title">Choose Services</div>
            <p id="servicePanelHint" style="margin:0 0 14px;padding:12px 16px;border-radius:14px;background:rgba(243,197,71,.12);border:1px solid rgba(243,197,71,.25);color:#7a5b00;font-size:13px;">
              Fill in your event date, start time, end time, and number of attendees first so we can check availability and capacity for each service.
            </p>
            <div class="services">
              <div class="service-row" data-service="venue">
                <div class="service-name"><i class="fa-solid fa-location-dot"></i>Venue</div>
                <div class="service-actions">
                  <button type="button" class="view-btn" onclick="openService('venue')">View</button>
                  <input class="service-check" type="checkbox" name="services[]" value="venue" id="check-venue" readonly tabindex="-1" onclick="return false;">
                </div>
              </div>

              <div class="service-row" data-service="clothes">
                <div class="service-name"><i class="fa-solid fa-shirt"></i>Clothes</div>
                <div class="service-actions">
                  <button type="button" class="view-btn" onclick="openService('clothing')">View</button>
                  <input class="service-check" type="checkbox" name="services[]" value="clothes" id="check-clothes" readonly tabindex="-1" onclick="return false;">
                </div>
              </div>

              <div class="service-row" data-service="catering">
                <div class="service-name"><i class="fa-solid fa-utensils"></i>Food & Catering</div>
                <div class="service-actions">
                  <button type="button" class="view-btn" onclick="openService('catering')">View</button>
                  <input class="service-check" type="checkbox" name="services[]" value="catering" id="check-catering" readonly tabindex="-1" onclick="return false;">
                </div>
              </div>

              <div class="service-row" data-service="host">
                <div class="service-name"><i class="fa-solid fa-microphone"></i>Host</div>
                <div class="service-actions">
                  <button type="button" class="view-btn" onclick="openService('host')">View</button>
                  <input class="service-check" type="checkbox" name="services[]" value="host" id="check-host" readonly tabindex="-1" onclick="return false;">
                </div>
              </div>

              <!-- Church is shown only for Traditional weddings -->
              <div class="service-row" data-service="church" id="row-church" style="display:none;">
                <div class="service-name"><i class="fa-solid fa-church"></i>Church</div>
                <div class="service-actions">
                  <button type="button" class="view-btn" onclick="openService('church')">View</button>
                  <input class="service-check" type="checkbox" name="services[]" value="church" id="check-church" readonly tabindex="-1" onclick="return false;">
                </div>
              </div>

              <div class="service-row" data-service="sounds_lights">
                <div class="service-name"><i class="fa-solid fa-lightbulb"></i>Sounds & Lights</div>
                <div class="service-actions">
                  <button type="button" class="view-btn" onclick="openService('s&l')">View</button>
                  <input class="service-check" type="checkbox" name="services[]" value="sounds_lights" id="check-sounds_lights" readonly tabindex="-1" onclick="return false;">
                </div>
              </div>

              <div class="service-row" data-service="photographer">
                <div class="service-name"><i class="fa-solid fa-camera"></i>Photographer</div>
                <div class="service-actions">
                  <button type="button" class="view-btn" onclick="openService('photographers')">View</button>
                  <input class="service-check" type="checkbox" name="services[]" value="photographer" id="check-photographer" readonly tabindex="-1" onclick="return false;">
                </div>
              </div>
            </div>

            <div class="footer-actions">
              <button type="button" class="cancel-btn" onclick="goBackToEventModal()">Back</button>
              <button type="submit" class="create-btn">Create Event</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    </form>
  </div>

<script>

  function toggleOtherInput() {
  const selected = document.querySelector('input[name="event_type"]:checked');
  const otherInput = document.getElementById('otherEventType');

  if (selected && selected.value === 'Others') {
    otherInput.disabled = false;
    otherInput.required = true;
    otherInput.focus();
  } else {
    otherInput.disabled = true;
    otherInput.required = false;
    otherInput.value = '';
  }
}

function openModal(modalId) {
  document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.classList.remove('is-open');
  });
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('is-open');
  }
}

/* ============ THEMES ============ */
const THEMES_BY_EVENT = {
  birthday: ['Cartoon Theme', '7th Birthday', '50th Birthday', 'Princess Theme', 'Color Theme', 'Superhero Theme', 'Sports Theme', 'Garden Party', 'Jungle Safari', 'Space Adventure', 'Custom'],
  debut: ['Debut Classic', 'Rustic Debut', 'Princess Debut', 'Color Theme', 'Royal Elegance', 'Garden Debut', 'Neon / Modern', 'Vintage Debut', 'Custom'],
  wedding: ['Garden', 'Rustic', 'Classic / Traditional', 'Ballroom', 'Beach / Destination', 'Royal Elegance', 'Fairytale', 'Minimalist', 'Vintage', 'Custom'],
  anniversary: ['Romantic Dinner', 'Classic Elegant', 'Vintage', 'Garden Party', 'Gold Celebration', 'Custom'],
  christening: ['Sky Blue / Pastel', 'Garden', 'Angel Theme', 'Classic White', 'Tea Party', 'Custom'],
  'gender reveal': ['Blue vs Pink', 'Black & Gold', 'Confetti Party', 'Neutral Elegance', 'Custom'],
  reunion: ['Family Picnic', 'Grand Gathering', 'Backyard Party', 'Classic Filipino', 'Nostalgia', 'Custom'],
  default: ['Classic', 'Garden', 'Elegant', 'Modern', 'Custom']
};

const PACKAGES_BY_EVENT = {
  birthday: [
    { name: 'Basic Birthday', price: 25000, includes: 'Venue + Catering + Host', services: ['venue', 'catering', 'host'] },
    { name: 'Standard Birthday', price: 50000, includes: 'Venue + Catering + Host + Sounds & Lights', services: ['venue', 'catering', 'host', 'sounds_lights'] },
    { name: 'Premium Birthday', price: 85000, includes: 'Venue + Catering + Host + S&L + Photographer + Styling', services: ['venue', 'catering', 'host', 'sounds_lights', 'photographer', 'clothes'] }
  ],
  debut: [
    { name: 'Basic Debut', price: 40000, includes: 'Venue + Catering + Host + 18 Roses/Candles setup', services: ['venue', 'catering', 'host'] },
    { name: 'Standard Debut', price: 80000, includes: 'Venue + Catering + Host + S&L + Photographer + Debut production', services: ['venue', 'catering', 'host', 'sounds_lights', 'photographer'] },
    { name: 'Premium Debut', price: 150000, includes: 'All services + Full styling + Audiovisual + Entourage', services: ['venue', 'catering', 'host', 'sounds_lights', 'photographer', 'clothes'] }
  ],
  wedding: [
    { name: 'Basic Wedding', price: 60000, includes: 'Venue + Catering + Host', services: ['venue', 'catering', 'host'] },
    { name: 'Standard Wedding', price: 120000, includes: 'Venue + Catering + Host + S&L + Photographer', services: ['venue', 'catering', 'host', 'sounds_lights', 'photographer'] },
    { name: 'Premium Wedding', price: 250000, includes: 'All services + Church + Rental Car + Full styling', services: ['venue', 'catering', 'host', 'sounds_lights', 'photographer', 'clothes', 'church', 'rental_car'] }
  ],
  anniversary: [
    { name: 'Basic Anniversary', price: 30000, includes: 'Venue + Catering + Host', services: ['venue', 'catering', 'host'] },
    { name: 'Standard Anniversary', price: 60000, includes: 'Venue + Catering + Host + Photographer', services: ['venue', 'catering', 'host', 'photographer'] },
    { name: 'Premium Anniversary', price: 100000, includes: 'Venue + Catering + Host + S&L + Photographer', services: ['venue', 'catering', 'host', 'sounds_lights', 'photographer'] }
  ],
  christening: [
    { name: 'Basic Christening', price: 20000, includes: 'Venue + Catering + Host', services: ['venue', 'catering', 'host'] },
    { name: 'Standard Christening', price: 40000, includes: 'Venue + Catering + Host + Photographer', services: ['venue', 'catering', 'host', 'photographer'] },
    { name: 'Premium Christening', price: 70000, includes: 'Venue + Catering + Host + S&L + Photographer', services: ['venue', 'catering', 'host', 'sounds_lights', 'photographer'] }
  ],
  'gender reveal': [
    { name: 'Basic Reveal', price: 15000, includes: 'Venue + Catering + Host', services: ['venue', 'catering', 'host'] },
    { name: 'Standard Reveal', price: 35000, includes: 'Venue + Catering + Host + Photographer', services: ['venue', 'catering', 'host', 'photographer'] },
    { name: 'Premium Reveal', price: 60000, includes: 'Venue + Catering + Host + S&L + Photographer', services: ['venue', 'catering', 'host', 'sounds_lights', 'photographer'] }
  ],
  reunion: [
    { name: 'Basic Reunion', price: 20000, includes: 'Venue + Catering + Host', services: ['venue', 'catering', 'host'] },
    { name: 'Standard Reunion', price: 45000, includes: 'Venue + Catering + Host + Photographer', services: ['venue', 'catering', 'host', 'photographer'] },
    { name: 'Premium Reunion', price: 80000, includes: 'Venue + Catering + Host + S&L + Photographer', services: ['venue', 'catering', 'host', 'sounds_lights', 'photographer'] }
  ],
  default: [
    { name: 'Basic Package', price: 25000, includes: 'Venue + Catering + Host', services: ['venue', 'catering', 'host'] },
    { name: 'Standard Package', price: 50000, includes: 'Venue + Catering + Host + S&L + Photographer', services: ['venue', 'catering', 'host', 'sounds_lights', 'photographer'] },
    { name: 'Premium Package', price: 90000, includes: 'Venue + Catering + Host + S&L + Photographer + Styling', services: ['venue', 'catering', 'host', 'sounds_lights', 'photographer', 'clothes'] }
  ]
};

let selectedTheme = '';

function normalizeEventKey(value) {
  return String(value || '').trim().toLowerCase();
}

function getThemesForEvent(eventType) {
  const key = normalizeEventKey(eventType);
  return THEMES_BY_EVENT[key] || THEMES_BY_EVENT.default;
}

function getPackagesForEvent(eventType) {
  const key = normalizeEventKey(eventType);
  return PACKAGES_BY_EVENT[key] || PACKAGES_BY_EVENT.default;
}

function renderThemeChips(eventType) {
  const container = document.getElementById('themeChips');
  const customInput = document.getElementById('customThemeInput');
  const error = document.getElementById('themeError');
  selectedTheme = '';
  document.getElementById('theme_input').value = '';
  if (error) error.style.display = 'none';

  const themes = getThemesForEvent(eventType);
  container.innerHTML = '';
  themes.forEach(function(theme) {
    const chip = document.createElement('button');
    chip.type = 'button';
    chip.className = 'theme-chip';
    chip.textContent = theme;
    chip.setAttribute('data-theme', theme);
    chip.addEventListener('click', function() {
      container.querySelectorAll('.theme-chip').forEach(function(c) {
        c.classList.remove('selected');
        if (c.getAttribute('data-theme') === 'Custom') c.style.display = 'none';
      });
      chip.classList.add('selected');
      selectedTheme = theme;
      document.getElementById('theme_input').value = theme;
      if (error) error.style.display = 'none';

      if (theme === 'Custom') {
        customInput.style.display = 'block';
        customInput.focus();
      } else {
        customInput.style.display = 'none';
        customInput.value = '';
      }
    });
    container.appendChild(chip);
  });
  customInput.style.display = 'none';
  customInput.value = '';
}

function prefillPackageServices(services) {
  const svcs = Array.isArray(services) ? services.map(String).map(s => s.trim()).filter(Boolean) : [];
  // If the package includes church/rental_car, make sure those rows are visible (Traditional wedding)
  if (svcs.includes('church') || svcs.includes('rental_car')) {
    const wtInput = document.getElementById('wedding_type_input');
    if (wtInput) wtInput.value = 'Traditional';
    const wtSelect = document.getElementById('weddingType');
    if (wtSelect) wtSelect.value = 'Traditional';
  }
  svcs.forEach(function(key) {
    const cb = document.getElementById('check-' + key);
    if (cb) {
      cb.checked = true;
      const row = cb.closest('.service-row');
      if (row) row.style.borderColor = 'rgba(243,197,71,0.45)';
    }
  });
  adjustServiceRowsBasedOnWeddingType();
}

function renderPackageSuggestions(eventType) {
  const panel = document.getElementById('packagePanel');
  const container = document.getElementById('packageChips');
  const packages = getPackagesForEvent(eventType);
  container.innerHTML = '';
  packages.forEach(function(pkg) {
    const card = document.createElement('div');
    card.className = 'package-card';
    card.innerHTML =
      '<h4>' + pkg.name + '</h4>' +
      '<div class="pkg-price">₱' + pkg.price.toLocaleString() + '</div>' +
      '<div class="pkg-includes">' + pkg.includes + '</div>';
    card.addEventListener('click', function() {
      container.querySelectorAll('.package-card').forEach(function(c) {
        c.classList.remove('selected');
      });
      card.classList.add('selected');
      document.getElementById('eventBudgetInput').value = pkg.price;
      document.getElementById('budget_input').value = pkg.price;
      // Pre-select the services included in this package so the user doesn't have to hunt for them
      prefillPackageServices(pkg.services || []);
    });
    container.appendChild(card);
  });
  panel.style.display = 'block';
}

function showThemeAndPackages() {
  const selected = document.querySelector('input[name="event_type"]:checked');
  if (!selected) return;
  const eventType = selected.value === 'Others'
    ? (document.getElementById('otherEventType').value.trim() || 'default')
    : selected.value;

  renderThemeChips(eventType);
  renderPackageSuggestions(eventType);
  document.getElementById('themePanel').style.display = 'block';
}

document.getElementById('customThemeInput').addEventListener('input', function() {
  const value = this.value.trim();
  if (value) {
    selectedTheme = 'Custom: ' + value;
    document.getElementById('theme_input').value = selectedTheme;
    document.getElementById('themeError').style.display = 'none';
  }
});

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('is-open');
  }
}

function goToServices() {
  const requiredInputs = Array.from(document.querySelectorAll('#eventModal input[required]'));
  const isComplete = requiredInputs.every(function(input) {
    return input.value.trim() !== '';
  });

  if (!isComplete) {
    alert('Please complete all event and schedule details before continuing.');
    return;
  }

  // Validate theme has been selected
  const themeVal = (document.getElementById('theme_input') || {}).value || '';
  if (!themeVal.trim()) {
    document.getElementById('themePanel').style.display = 'block';
    const err = document.getElementById('themeError');
    if (err) err.style.display = 'block';
    alert('Please select a theme for your event first.');
    return;
  }

  // Sync budget input to hidden field on next
  const budgetInput = document.getElementById('eventBudgetInput');
  const budgetHidden = document.getElementById('budget_input');
  if (budgetInput && budgetHidden) {
    budgetHidden.value = budgetInput.value;
  }

  // If Wedding selected, show wedding details modal first
  const selectedEvent = document.querySelector('input[name="event_type"]:checked');
  closeModal('eventModal');
  if (selectedEvent && selectedEvent.value === 'Wedding') {
    openModal('weddingModal');
  } else {
    adjustServiceRowsBasedOnWeddingType();
    openModal('servicesModal');
  }
}

function goBackToEventModal() {
  closeModal('servicesModal');
  openModal('eventModal');
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('is-open');
  }
}

function openService(service) {
  // Create modal if it doesn't exist
  let modal = document.getElementById('serviceModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'serviceModal';
    modal.style.cssText = `
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
      overflow: auto;
      padding: 20px;
    `;
    document.body.appendChild(modal);
  }

  // Create iframe container
  const container = document.createElement('div');
  container.style.cssText = `
    position: relative;
    width: 95%;
    height: 90vh;
    background: white;
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
  `;

  // Create close button
  const closeBtn = document.createElement('button');
  closeBtn.innerHTML = '<i class="fas fa-times"></i>';
  closeBtn.style.cssText = `
    position: absolute;
    top: 15px;
    right: 15px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f3c547;
    border: none;
    color: #222;
    font-size: 18px;
    cursor: pointer;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.3s;
  `;
  closeBtn.onmouseover = () => closeBtn.style.background = '#e8b70f';
  closeBtn.onmouseout = () => closeBtn.style.background = '#f3c547';
  closeBtn.onclick = () => {
    modal.style.display = 'none';
    container.remove();
  };

  // Create iframe
  const iframe = document.createElement('iframe');
  const form = document.querySelector('form[action="save_event.php"]');
  const queryParts = [
    'from=createevent',
    'modal=true',
    'event_date=' + encodeURIComponent(form?.querySelector('input[name="event_date"]')?.value || ''),
    'event_time=' + encodeURIComponent(form?.querySelector('input[name="event_time"]')?.value || ''),
    'event_end_time=' + encodeURIComponent(form?.querySelector('input[name="event_end_time"]')?.value || ''),
    'guest_count=' + encodeURIComponent(form?.querySelector('input[name="guest_count"]')?.value || '')
  ];
  iframe.src = service + '.php?' + queryParts.join('&');
  iframe.style.cssText = `
    flex: 1;
    border: none;
    border-radius: 20px;
  `;

  container.appendChild(closeBtn);
  container.appendChild(iframe);
  modal.innerHTML = '';
  modal.appendChild(container);
  modal.style.display = 'flex';

  // NOTE: clicking on the backdrop will NOT close this modal;
  // modal is closed only via the close button or explicit actions.
}

function getCookie(name) {
  const cookies = document.cookie ? document.cookie.split(';') : [];
  for (let i = 0; i < cookies.length; i += 1) {
    const cookie = cookies[i].trim();
    if (cookie.indexOf(name + '=') === 0) {
      return decodeURIComponent(cookie.substring(name.length + 1));
    }
  }
  return '';
}

function normalizeSelection(value) {
  if (value === null || value === undefined) return '';
  const normalized = String(value).trim();
  if (normalized.toLowerCase() === 'null') return '';
  return normalized;
}

function persistSelection(key, value) {
  const normalized = normalizeSelection(value);
  if (!normalized) return;
  try {
    sessionStorage.setItem('event_selection_' + key, normalized);
    document.cookie = 'event_selection_' + key + '=' + encodeURIComponent(normalized) + '; path=/; max-age=3600';
  } catch (err) {
    console.warn('Could not persist selection', err);
  }
}

function clearPersistedSelections() {
  const keys = ['venue', 'venue_name', 'clothes', 'catering', 'host', 'photographer', 'sounds_lights', 'church', 'rental_car'];
  keys.forEach(function(key) {
    try {
      sessionStorage.removeItem('event_selection_' + key);
      document.cookie = 'event_selection_' + key + '=; path=/; max-age=0';
    } catch (err) {
      console.warn('Could not clear persisted selection', err);
    }
  });

  document.getElementById('selectedVenue').value = '';
  document.getElementById('selectedVenueName').value = '';
  document.getElementById('selectedClothes').value = '';
  document.getElementById('selectedCatering').value = '';
  document.getElementById('selectedHost').value = '';
  document.getElementById('selectedPhotographer').value = '';
  document.getElementById('selectedSoundsLights').value = '';
  if (document.getElementById('selectedChurch')) document.getElementById('selectedChurch').value = '';
  if (document.getElementById('selectedRentalCar')) document.getElementById('selectedRentalCar').value = '';

  document.querySelectorAll('.service-check').forEach(function(cb) {
    cb.checked = false;
    const row = cb.closest('.service-row');
    if (row) {
      row.style.borderColor = '';
    }
  });
}

function syncSelectionsToForm() {
  const venue = normalizeSelection(sessionStorage.getItem('event_selection_venue') || getCookie('event_selection_venue') || sessionStorage.getItem('event_selection_venue_name') || getCookie('event_selection_venue_name'));
  const clothes = normalizeSelection(sessionStorage.getItem('event_selection_clothes') || getCookie('event_selection_clothes'));
  const catering = normalizeSelection(sessionStorage.getItem('event_selection_catering') || getCookie('event_selection_catering'));
  const host = normalizeSelection(sessionStorage.getItem('event_selection_host') || getCookie('event_selection_host'));
  const photographer = normalizeSelection(sessionStorage.getItem('event_selection_photographer') || getCookie('event_selection_photographer'));
  const soundsLights = normalizeSelection(sessionStorage.getItem('event_selection_sounds_lights') || getCookie('event_selection_sounds_lights'));
  const church = normalizeSelection(sessionStorage.getItem('event_selection_church') || getCookie('event_selection_church'));
  const rentalCar = normalizeSelection(sessionStorage.getItem('event_selection_rental_car') || getCookie('event_selection_rental_car'));

  document.getElementById('selectedVenue').value = venue;
  document.getElementById('selectedVenueName').value = venue;
  document.getElementById('selectedClothes').value = clothes;
  document.getElementById('selectedCatering').value = catering;
  document.getElementById('selectedHost').value = host;
  document.getElementById('selectedPhotographer').value = photographer;
  document.getElementById('selectedSoundsLights').value = soundsLights;
  if (document.getElementById('selectedChurch')) document.getElementById('selectedChurch').value = church;
  if (document.getElementById('selectedRentalCar')) document.getElementById('selectedRentalCar').value = rentalCar;

  if (venue) {
    const venueCheckbox = document.getElementById('check-venue');
    if (venueCheckbox) {
      venueCheckbox.checked = true;
      const row = venueCheckbox.closest('.service-row');
      if (row) row.style.borderColor = 'rgba(243,197,71,0.45)';
    }
  }
}

const eventForm = document.querySelector('form[action="save_event.php"]');
if (eventForm) {
  eventForm.addEventListener('submit', function() {
    syncSelectionsToForm();
  });
}

// Restore persisted selections immediately when the page loads.
syncSelectionsToForm();

// Listen for messages from the service-selection iframe/popup
window.addEventListener('message', function(e) {
  if (e.data && e.data.type === 'serviceSelected') {
    const service = e.data.service;

    const checkbox = document.getElementById('check-' + service);
    if (checkbox) {
      checkbox.checked = true;
      const row = checkbox.closest('.service-row');
      if (row) row.style.borderColor = 'rgba(243,197,71,0.45)';
    }

    // Venue
    if (service === 'venue' && (e.data.venue || e.data.venue_name)) {
      const venueValue = normalizeSelection(e.data.venue_name || e.data.venue || '');
      if (venueValue) {
        document.getElementById('selectedVenue').value = venueValue;
        document.getElementById('selectedVenueName').value = venueValue;
        persistSelection('venue', venueValue);
      }
    }

    // Clothes
    if (service === 'clothes' && e.data.clothes) {
      document.getElementById('selectedClothes').value = e.data.clothes;
      persistSelection('clothes', e.data.clothes);
    }

    // Catering
    if (service === 'catering' && e.data.catering) {
      document.getElementById('selectedCatering').value = e.data.catering;
      persistSelection('catering', e.data.catering);
    }

    // Host
    if (service === 'host' && e.data.host) {
      document.getElementById('selectedHost').value = e.data.host;
      persistSelection('host', e.data.host);
    }

    // Photographer
    if (service === 'photographer' && e.data.photographer) {
      document.getElementById('selectedPhotographer').value = e.data.photographer;
      persistSelection('photographer', e.data.photographer);
    }

    // Sounds & Lights
    if (service === 'sounds_lights' && e.data.sounds_lights) {
      document.getElementById('selectedSoundsLights').value = e.data.sounds_lights;
      persistSelection('sounds_lights', e.data.sounds_lights);
    }

    // Church
    if (service === 'church' && (e.data.church || e.data.church_name || e.data.name)) {
      const val = normalizeSelection(e.data.church_name || e.data.church || e.data.name || '');
      if (val) {
        if (document.getElementById('selectedChurch')) document.getElementById('selectedChurch').value = val;
        persistSelection('church', val);
      }
    }

    // Rental Car
    if (service === 'rental_car' && (e.data.rental_car || e.data.car || e.data.name)) {
      const val = normalizeSelection(e.data.rental_car || e.data.car || e.data.name || '');
      if (val) {
        if (document.getElementById('selectedRentalCar')) document.getElementById('selectedRentalCar').value = val;
        persistSelection('rental_car', val);
      }
    }

    // Selection complete — close the modal and return to the main form
    const modal = document.getElementById('serviceModal');
    if (modal) {
      modal.style.display = 'none';
      modal.innerHTML = '';
    }
  }
});

// Also handle addon list from venue selection: check related services and tag them with the venue name
window.addEventListener('message', function(e) {
  const addons = Array.isArray(e.data?.addons)
    ? e.data.addons.filter(Boolean)
    : String(e.data?.addons || '').split(',').map(s => s.trim()).filter(Boolean);

  if (e.data && e.data.type === 'serviceSelected' && e.data.service === 'venue' && addons.length > 0) {
    const venueName = normalizeSelection(e.data.venue || e.data.venue_name || '');
    const map = {
      'clothing': 'clothes',
      'clothes': 'clothes',
      'catering': 'catering',
      'host': 'host',
      'sounds_lights': 'sounds_lights',
      'photographer': 'photographer',
      'church': 'church',
      'rental_car': 'rental_car'
    };

    addons.forEach(function(a) {
      const key = map[a] || a;
      const cb = document.getElementById('check-' + key);
      if (cb) {
        cb.checked = true;
        const row = cb.closest('.service-row');
        if (row) row.style.borderColor = 'rgba(243,197,71,0.45)';
      }

      // Set the corresponding hidden input to the venue name so it will be submitted
      if (venueName) {
        document.getElementById('selectedVenue').value = venueName;
        document.getElementById('selectedVenueName').value = venueName;
        persistSelection('venue', venueName);
      }

      if (key === 'catering') {
        document.getElementById('selectedCatering').value = venueName;
        persistSelection('catering', venueName);
      }
      if (key === 'clothes') {
        document.getElementById('selectedClothes').value = venueName;
        persistSelection('clothes', venueName);
      }
      if (key === 'host') {
        document.getElementById('selectedHost').value = venueName;
        persistSelection('host', venueName);
      }
      if (key === 'sounds_lights') {
        document.getElementById('selectedSoundsLights').value = venueName;
        persistSelection('sounds_lights', venueName);
      }
      if (key === 'photographer') {
        document.getElementById('selectedPhotographer').value = venueName;
        persistSelection('photographer', venueName);
      }
      if (key === 'church') {
        if (document.getElementById('selectedChurch')) document.getElementById('selectedChurch').value = venueName;
        persistSelection('church', venueName);
      }
      if (key === 'rental_car') {
        if (document.getElementById('selectedRentalCar')) document.getElementById('selectedRentalCar').value = venueName;
        persistSelection('rental_car', venueName);
      }
    });
  }
});

function updateStep(step) {
  document.querySelectorAll('.step-pill').forEach(function(pill) {
    pill.classList.toggle('active', pill.dataset.step === String(step));
  });
}

function setModalAvailability() {
  const selectedEvent = document.querySelector('input[name="event_type"]:checked');
  const scheduleBtn = document.getElementById('scheduleModalBtn');
  const servicesBtn = document.getElementById('servicesModalBtn');
  const hasEvent = Boolean(selectedEvent);

  if (scheduleBtn) {
    scheduleBtn.classList.toggle('disabled', !hasEvent);
  }

  if (servicesBtn) {
    servicesBtn.classList.toggle('disabled', !hasEvent);
  }
}

function showScheduleIfReady() {
  const selectedEvent = document.querySelector('input[name="event_type"]:checked');
  if (selectedEvent) {
    updateStep(2);
  } else {
    updateStep(1);
  }
  setModalAvailability();
  checkScheduleComplete();
}

document.querySelectorAll('input[name="event_type"]').forEach(function(input) {
  input.addEventListener('change', function() {
    toggleOtherInput();
    showScheduleIfReady();
    showThemeAndPackages();
    // clear hidden budget when type changes
    if (document.getElementById('budget_input')) document.getElementById('budget_input').value = '';
  });
});

document.getElementById('otherEventType').addEventListener('input', function() {
  const selected = document.querySelector('input[name="event_type"]:checked');
  if (selected && selected.value === 'Others') {
    renderThemeChips(this.value.trim() || 'default');
    renderPackageSuggestions(this.value.trim() || 'default');
  }
});

// Set minimum selectable date based on selected event type
function pad(n){return n<10? '0'+n: n}
function formatDate(d){return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())}

function setDateMinBasedOnEvent() {
  const dateInput = document.querySelector('input[name="event_date"]');
  if (!dateInput) return;
  const selected = document.querySelector('input[name="event_type"]:checked');
  // default: one week from today
  const today = new Date();
  let minDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  if (selected && selected.value === 'Wedding') {
    // add approximately 3 months
    minDate.setMonth(minDate.getMonth() + 3);
  } else {
    minDate.setDate(minDate.getDate() + 7);
  }
  // If current input value is before minDate, clear it
  dateInput.min = formatDate(minDate);
  const cur = dateInput.value ? new Date(dateInput.value) : null;
  if (cur && cur < minDate) {
    dateInput.value = '';
  }
}

// Call when event type changes and on init
document.querySelectorAll('input[name="event_type"]').forEach(function(input) {
  input.addEventListener('change', setDateMinBasedOnEvent);
});
window.addEventListener('load', setDateMinBasedOnEvent);

// Wedding modal Next handler: validate and move to services modal
document.getElementById('weddingNextBtn').addEventListener('click', function() {
  const wt = document.getElementById('weddingType').value;
  const rt = document.getElementById('receptionType').value;
  if (!wt) { alert('Please choose a wedding type.'); return; }
  if (!rt) { alert('Please choose a reception type.'); return; }

  // persist into hidden inputs so save_event.php receives them
  document.getElementById('wedding_type_input').value = wt;
  document.getElementById('reception_type_input').value = rt;

  closeModal('weddingModal');
  adjustServiceRowsBasedOnWeddingType();
  openModal('servicesModal');
});

function adjustServiceRowsBasedOnWeddingType() {
  const wt = (document.getElementById('wedding_type_input') && document.getElementById('wedding_type_input').value) || (document.getElementById('weddingType') ? document.getElementById('weddingType').value : '');
  const show = wt === 'Traditional';
  const rowChurch = document.getElementById('row-church');
  if (rowChurch) rowChurch.style.display = show ? '' : 'none';
  if (!show) {
    const cbChurch = document.getElementById('check-church');
    if (cbChurch) { cbChurch.checked = false; const r = cbChurch.closest('.service-row'); if (r) r.style.borderColor = ''; }
    if (document.getElementById('selectedChurch')) {
      document.getElementById('selectedChurch').value = '';
      try { sessionStorage.removeItem('event_selection_church'); document.cookie='event_selection_church=;path=/;max-age=0'; } catch (e) {}
    }
  }
}

document.querySelectorAll('.modal-open-btn').forEach(function(button) {
  button.addEventListener('click', function() {
    if (!button.classList.contains('disabled')) {
      openModal(button.dataset.modal);
    }
  });
});


// Backdrop clicks should not close modal overlays anymore.
// If you want to allow closing by backdrop for specific modals,
// add explicit handlers to those modal elements instead.

const scheduleInputs = document.querySelectorAll('.schedule-grid input');
function checkScheduleComplete() {
  const servicesBtn = document.getElementById('servicesModalBtn');
  const hint = document.getElementById('servicePanelHint');
  const filled = Array.from(scheduleInputs).every(function(input) {
    return input.value.trim() !== '';
  });
  const hasEvent = Boolean(document.querySelector('input[name="event_type"]:checked'));

  if (servicesBtn) {
    servicesBtn.classList.toggle('disabled', !hasEvent || !filled);
  }

  if (filled) {
    if (hint) hint.style.display = 'none';
    updateStep(3);
  } else {
    if (hint) hint.style.display = 'block';
    if (hasEvent) {
      updateStep(2);
    } else {
      updateStep(1);
    }
  }
}
scheduleInputs.forEach(function(input) {
  input.addEventListener('input', checkScheduleComplete);
  input.addEventListener('change', checkScheduleComplete);
});

// Check URL params for returned selections from redirect flow
(function() {
  const query = window.location.search.replace(/^\?/, '');
  const params = {};
  query.split('&').forEach(function(part) {
    if (!part) return;
    const [key, value] = part.split('=');
    params[decodeURIComponent(key)] = decodeURIComponent(value || '');
  });
  const selected = params.selected || '';
  const selectedItems = selected.split(',').map(function(item) {
    return String(item || '').trim();
  }).filter(Boolean);

  const fromPackageEarly = params.from === 'package';

  if (selectedItems.length) {
    selectedItems.forEach(function(s) {
      const cb = document.getElementById('check-' + s);
      if (cb) {
        cb.checked = true;
        const row = cb.closest('.service-row');
        if (row) row.style.borderColor = 'rgba(243,197,71,0.45)';
      }
    });
  }

  // Don't wipe previously persisted selections when coming from a package,
  // because the package auto-fill depends on those persisted values.
  if (!selectedItems.length && !fromPackageEarly) {
    clearPersistedSelections();
  }

  // If arriving from packages.php with a budget, prefill the budget field
  const fromPackage = params.from === 'package';
  const budgetParam = params.budget ? parseFloat(params.budget) : 0;
  const eventTypeParam = params.event_type || '';
  const packageServicesParam = params.services || '';
  if (fromPackage && budgetParam > 0) {
    const budgetInput = document.getElementById('eventBudgetInput');
    const budgetHidden = document.getElementById('budget_input');
    if (budgetInput) budgetInput.value = budgetParam;
    if (budgetHidden) budgetHidden.value = budgetParam;
    if (eventTypeParam) {
      const radio = document.querySelector('input[name="event_type"][value="' + eventTypeParam + '"]');
      if (radio) {
        radio.checked = true;
        toggleOtherInput();
        showThemeAndPackages();
      }
    }
    // Auto-check the services included in the chosen package
    const pkgServices = packageServicesParam.split(',').map(s => s.trim()).filter(Boolean);
    if (pkgServices.length > 0) {
      prefillPackageServices(pkgServices);
    }
  }

  // Server-side auto-fill: the best-rated/cheapest supplier per package service
  const packageAutoFill = <?= json_encode($packageAutoFill) ?>;
  if (packageAutoFill && Object.keys(packageAutoFill).length > 0) {
    const fillMap = {
      venue: ['selectedVenue', 'selectedVenueName'],
      catering: ['selectedCatering'],
      host: ['selectedHost'],
      photographer: ['selectedPhotographer'],
      sounds_lights: ['selectedSoundsLights'],
      clothes: ['selectedClothes'],
      church: ['selectedChurch'],
      rental_car: ['selectedRentalCar']
    };
    Object.keys(packageAutoFill).forEach(function(key) {
      const val = packageAutoFill[key];
      if (!val) return;
      const targets = fillMap[key] || [];
      targets.forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.value = val;
      });
      persistSelection(key, val);
    });
    // Show a small notice that suppliers were auto-selected
    const hint = document.getElementById('servicePanelHint');
    if (hint) {
      hint.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Suppliers for your package were auto-selected based on the best ratings and prices. You can review or change them below.';
      hint.style.display = 'block';
    }
  }

  showScheduleIfReady();

  if (selectedItems.includes('venue')) {
    openModal('servicesModal');
  } else {
    openModal('eventModal');
  }
})();
</script>
</body>
</html>
