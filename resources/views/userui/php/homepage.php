<?php
require_once __DIR__ . '/../../config/db.php';
require_login();
// Navbar includes Home, Create Event, Your Events, Recommendations, and Newsfeed
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventIntel Homepage</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      min-height: 100vh;
      overflow-x: hidden;
      overflow-y: auto;
      background: #ffffff;
      color: #111;
      position: relative;
    }

    body::before,
    body::after {
      content: "";
      position: absolute;
      border-radius: 50%;
      filter: blur(120px);
      z-index: 0;
    }

    body::before {
      width: 450px;
      height: 450px;
      background: rgba(255, 196, 0, 0.10);
      top: -160px;
      left: -120px;
    }

    body::after {
      width: 520px;
      height: 520px;
      background: rgba(255, 215, 0, 0.07);
      bottom: -220px;
      right: -140px;
    }

    .container {
      width: 100%;
      min-height: 100vh;
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
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
      background: transparent;
      color: #444;
      font-size: 14px;
      cursor: pointer;
      transition: 0.3s ease;
      border: 1px solid rgba(212,160,23,0.35);
      background: rgba(255,255,255,0.55);
      color: #222;
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
      border: 1px solid rgba(255, 215, 0, 0.35);
      background: #f5f5f5;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      transition: 0.3s ease;
      color: #f3c547;
    }

    .profile-btn:hover {
      background: rgba(255, 215, 0, 0.12);
      box-shadow: 0 0 14px rgba(255, 215, 0, 0.18);
    }

    .profile-btn i {
      font-size: 18px;
    }

    .hero {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 60px 30px;
      margin-top: 0;
      position: relative;
      z-index: 2;
    }

    .subtitle {
      color: #d4a017;
      font-size: 40px;
      line-height: 1.2;
      font-weight: 800;
      max-width: 780px;
      margin-bottom: 16px;
    }

    /* CHANGED: Made "Plan Better Events with" significantly smaller and more refined */
    .hero h1 {
      font-size: 13px;
      font-weight: 600;
      letter-spacing: 4px;
      text-transform: uppercase;
      color: #555;
    }

    /* EventIntel heading line style stays prominent and large */
    .hero h1 span {
      display: block;
      font-size: 52px;
      line-height: 1.1;
      font-weight: 900;
      letter-spacing: normal;
      text-transform: none;
      margin-top: 10px;
      background: linear-gradient(to right, #ffe17a, #d4a017, #b8860b);
      background-clip: text;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .button-group {
      margin-top: 40px;
      display: flex;
      flex-wrap: wrap;
      gap: 18px;
    }

    .action-btn {
      width: 240px;
      height: 72px;
      border-radius: 18px;
      border: 2px solid #d4a017;
      background: rgba(255, 255, 255, 0.95);
      color: #b8860b;
      font-size: 18px;
      font-weight: 700;
      cursor: pointer;
      transition: 0.3s ease;
      box-shadow: 0 0 14px rgba(255, 215, 0, 0.10);
    }

    .action-btn.primary {
      background: linear-gradient(to right, #ffd54a, #b8860b);
      color: black;
      border: none;
      box-shadow: 0 0 28px rgba(255, 215, 0, 0.25);
    }

    .action-btn:hover {
      transform: translateY(-6px) scale(1.01);
      background: rgba(255, 215, 0, 0.08);
    }

    .action-btn.primary:hover {
      background: linear-gradient(to right, #ffe17a, #c99700);
    }

    .event-gallery {
      position: fixed;
      inset: 0;
      width: 100%;
      height: 100%;
      display: flex;
      overflow: hidden;
      z-index: -1;
      opacity: 0.12;
      pointer-events: none;
    }

    .welcome-msg {
      color: #c99700;
      font-size: 14px;
      font-weight: 600;
      margin-right: 12px;
    }

    @media (max-width: 768px) {
      .container { padding: 15px; }
      .navbar { flex-direction: column; align-items: center; }
      .nav-links { justify-content: center; }
      .subtitle { font-size: 32px; }
      .hero h1 { font-size: 11px; letter-spacing: 2px; }
      .hero h1 span { font-size: 36px; }
      .button-group { flex-direction: column; align-items: center; }
      .action-btn { width: 100%; max-width: 320px; }
    }

    /* ================= MAXIMUM-WIDE 3-CARD HIGHLIGHT CAROUSEL ================= */

    .service-carousel-section {
      margin-top: 50px;
      padding: 20px 0;
    }

    .service-carousel-section h2 {
      font-size: 28px;
      margin-bottom: 6px;
    }

    .service-carousel-section p {
      color: #555;
      margin-bottom: 18px;
    }

    .service-carousel-wrapper {
      position: relative;
      overflow: hidden;
      padding: 40px 90px;
      display: flex;
      align-items: center;
      max-width: 1600px;
      margin: 0 auto;
    }

    .service-track-container {
      overflow: hidden;
      width: 100%;
      padding: 30px 0;
      margin: -30px 0;
    }

    .service-track {
      display: flex;
      gap: 44px;
      align-items: center;
      transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1);
      will-change: transform;
    }

    .service-card {
      width: calc((100% - 88px) / 3);
      min-width: calc((100% - 88px) / 3);
      background: #fff;
      border-radius: 26px;
      padding: 40px;
      border: 1px solid rgba(212,160,23,0.18);
      box-shadow: 0 8px 24px rgba(0,0,0,0.03);
      text-align: center;
      transition: transform 0.4s ease, opacity 0.4s ease, box-shadow 0.4s ease, border-color 0.4s ease;
      display: flex;
      flex-direction: column;
      align-items: center;

      transform: scale(0.93);
      opacity: 0.65;
    }

    .service-card.visible {
      transform: scale(0.98);
      opacity: 0.9;
      border-color: rgba(212,160,23,0.45);
      box-shadow: 0 12px 32px rgba(212,160,23,0.12);
    }

    .service-card.active {
      transform: scale(1.06);
      opacity: 1;
      border-color: rgba(212,160,23,0.6);
      box-shadow: 0 20px 45px rgba(212,160,23,0.18);
      z-index: 2;
    }

    .service-card i {
      font-size: 36px;
      color: #d4a017;
      margin: 12px 0 6px;
    }

    .service-card h3 {
      margin-bottom: 8px;
      font-size: 26px;
      color: #111;
    }

    .service-card p {
      font-size: 16px;
      color: #666;
      margin-bottom: 26px;
    }

    .service-card button {
      padding: 15px 26px;
      border-radius: 14px;
      border: none;
      background: linear-gradient(to right, #ffd54a, #b8860b);
      color: #000;
      cursor: pointer;
      font-weight: 600;
      font-size: 16px;
      width: 100%;
      margin-top: auto;
    }

    .service-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 56px;
      height: 56px;
      border-radius: 50%;
      border: 1px solid rgba(212,160,23,0.3);
      background: #fff;
      box-shadow: 0 4px 14px rgba(0,0,0,0.08);
      cursor: pointer;
      z-index: 10;
      font-size: 24px;
      display: flex;
      justify-content: center;
      align-items: center;
      transition: 0.2s ease;
    }

    .service-btn:hover {
      background: #ffd54a;
      color: #000;
    }

    .service-btn.left { left: 15px; }
    .service-btn.right { right: 15px; }

    .service-card .service-image {
      width: 100%;
      height: 240px;
      border-radius: 18px;
      overflow: hidden;
      margin-bottom: 20px;
      background: #f5f5f5;
    }

    .service-card .service-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    @media (max-width: 1100px) {
      .service-card {
        width: calc((100% - 44px) / 2);
        min-width: calc((100% - 44px) / 2);
      }
    }
    @media (max-width: 720px) {
      .service-card {
        width: 100%;
        min-width: 100%;
      }
    }
  </style>
</head>
<body>
  <script>
    let serviceIndex = 0;

    function moveServiceCarousel(direction) {
      const track = document.getElementById("serviceTrack");
      const cards = document.querySelectorAll(".service-card");

      if (!track || cards.length === 0) return;

      const cardWidth = cards[0].offsetWidth + 44;
      const maxScrollable = cards.length - 3;

      serviceIndex += direction;

      if (serviceIndex < 0) serviceIndex = 0;
      if (serviceIndex > maxScrollable) serviceIndex = maxScrollable;

      track.style.transform = `translateX(-${serviceIndex * cardWidth}px)`;
      updateActiveCard();
    }

    function selectService(service) {
      // Map service names to their corresponding PHP pages
      const servicePages = {
        'Photographer': 'photographers.php',
        'Catering': 'catering.php',
        'Host / MC': 'host.php',
        'Event Coordinator': 'eventcoor.php',
        'Venue': 'venue.php',
        'Stylist': 'clothing.php',
        'Lights & Sound': 's&l.php'
      };

      const page = servicePages[service] || 'newsfeed.php?service=' + encodeURIComponent(service);
      window.location.href = page;
    }

    function updateActiveCard() {
      const cards = document.querySelectorAll(".service-card");
      cards.forEach(card => {
        card.classList.remove("active");
        card.classList.remove("visible");
      });

      // Make the 3 visible cards (left, middle, right) visible with gold styling
      const leftIndex = serviceIndex;
      const middleIndex = serviceIndex + 1;
      const rightIndex = serviceIndex + 2;

      if (cards[leftIndex]) cards[leftIndex].classList.add("visible");
      if (cards[middleIndex]) {
        cards[middleIndex].classList.add("active");
        cards[middleIndex].classList.add("visible");
      }
      if (cards[rightIndex]) cards[rightIndex].classList.add("visible");
    }

    window.addEventListener("load", updateActiveCard);
    window.addEventListener("resize", () => {
      moveServiceCarousel(0);
    });
  </script>

  <div class="container">
    <nav class="navbar">
      <div class="logo">EventIntel</div>
      <div class="nav-links">
        <span class="welcome-msg">
          Welcome, <?= esc($_SESSION['full_name'] ?? 'User') ?>!
        </span>
        <button class="active" onclick="window.location.href='homepage.php'">Home</button>
        <button onclick="window.location.href='createevent.php'">Create Event</button>
        <button onclick="window.location.href='yourevents.php'">Your Events</button>
        <button onclick="window.location.href='recommendation.php'">Recommendations</button>
        <button onclick="window.location.href='packages.php'">Packages</button>
        <button onclick="window.location.href='newsfeed.php'">Newsfeed</button>
        <button class="profile-btn" type="button" aria-label="Profile" title="Profile" onclick="window.location.href='profile.php'">
          <i class="fas fa-user"></i>
        </button>
      </div>
    </nav>

    <section class="hero">
      <h1>Plan Better Events with<span>EventIntel</span></h1>
      <div class="subtitle">Smart Event Planning Platform</div>
      <p>Organize memorable events, connect with professional coordinators, and receive intelligent recommendations tailored to your needs.</p>
      <div class="button-group">
        <button class="action-btn primary" onclick="window.location.href='createevent.php'">Create an Event</button>
        <button class="action-btn" onclick="window.location.href='eventcoor.php'">Find an Event Coordinator</button>
        <button class="action-btn" onclick="window.location.href='newsfeed.php'">View Supplier Newsfeed</button>
      </div>
    </section>

    <section class="service-carousel-section">
      <h2>Browse Supplier Categories</h2>
      <p>Select the service you need for your event</p>

      <div class="service-carousel-wrapper">
        <button class="service-btn left" onclick="moveServiceCarousel(-1)">&#10094;</button>

        <div class="service-track-container">
          <div class="service-track" id="serviceTrack">
            <?php
            $services = [
              ['name' => 'Photographer', 'icon' => 'fa-camera', 'desc' => 'Capture every moment', 'image' => '../images/photographer.avif'],
              ['name' => 'Catering', 'icon' => 'fa-utensils', 'desc' => 'Delicious food services', 'image' => '../images/catering.jpg'],
              ['name' => 'Host / MC', 'icon' => 'fa-microphone', 'desc' => 'Professional event hosting', 'image' => '../images/images.jpg'],
              ['name' => 'Event Coordinator', 'icon' => 'fa-clipboard-list', 'desc' => 'Full event planning', 'image' => '../images/eri-neeman-24-scaled.jpeg'],
              ['name' => 'Venue', 'icon' => 'fa-building', 'desc' => 'Perfect event locations', 'image' => '../images/venue.avif'],
              ['name' => 'Stylist', 'icon' => 'fa-wand-magic-sparkles', 'desc' => 'Event styling & design', 'image' => '../images/clothing_stylist.jpg'],
              ['name' => 'Lights & Sound', 'icon' => 'fa-music', 'desc' => 'Audio & lighting setup', 'image' => '../images/ledlights.jpg']
            ];
            ?>

            <?php foreach ($services as $s): ?>
              <div class="service-card">
                <div class="service-image">
                  <img src="<?= $s['image'] ?>" alt="<?= $s['name'] ?>">
                </div>
                <div>
                  <i class="fas <?= $s['icon'] ?>"></i>
                  <h3><?= $s['name'] ?></h3>
                  <p><?= $s['desc'] ?></p>
                </div>
                <button onclick="selectService('<?= $s['name'] ?>')">View Providers</button>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <button class="service-btn right" onclick="moveServiceCarousel(1)">&#10095;</button>
      </div>
    </section>
  </div>
</body>
</html>
