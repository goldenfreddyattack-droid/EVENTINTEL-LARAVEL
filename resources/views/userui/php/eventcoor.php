<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');

$pdo = db();

// Fetch all coordinators with their portfolio data, average rating, and minimum package price
$query = "SELECT u.user_id, u.full_name, u.business_name, u.business_address,
                 (SELECT IFNULL(AVG(rating), 0) FROM coordinator_reviews cr WHERE cr.coordinator_id = u.user_id) as avg_rating,
                 (SELECT COUNT(*) FROM coordinator_reviews cr WHERE cr.coordinator_id = u.user_id) as total_reviews,
                 (SELECT MIN(price) FROM coordinator_packages cp WHERE cp.coordinator_id = u.user_id) as min_package,
                 (SELECT COUNT(*) FROM coordinator_packages cp WHERE cp.coordinator_id = u.user_id) as total_packages
          FROM users u
          WHERE u.role = 'coordinator'
          ORDER BY u.full_name";
$stmt = $pdo->query($query);
$coordinators = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventIntel - Select Event Coordinator</title>
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

    .background-strip {
      position: fixed;
      inset: 0;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      opacity: 0.22;
      z-index: 0;
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
      border: 1px solid rgba(212,160,23,0.14);
      background: rgba(255,255,255,0.82);
      color: #111;
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

    .coordinator-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 26px;
    }

    .coordinator-card {
      background: rgba(255,255,255,.82);
      border: 1px solid rgba(212,160,23,.14);
      border-radius: 30px;
      overflow: hidden;
      transition: .35s ease;
      box-shadow: 0 18px 40px rgba(0,0,0,.08);
      backdrop-filter: blur(16px);
    }

    .coordinator-card:hover {
      transform: translateY(-8px);
      border-color: rgba(212,160,23,.3);
      box-shadow: 0 24px 50px rgba(243,197,71,.12);
    }

    .coordinator-image {
      position: relative;
      height: 320px;
      overflow: hidden;
    }

    .coordinator-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(.95);
      transition: .35s ease;
    }

    .coordinator-card:hover .coordinator-image img {
      transform: scale(1.05);
      filter: brightness(1);
    }

    .coordinator-image::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(255,255,255,.90), rgba(255,255,255,.08));
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

    .coordinator-content {
      padding: 24px;
    }

    .coordinator-content h3 {
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

    .coordinator-content p {
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

    .select-btn {
      padding: 14px 24px;
      border-radius: 16px;
      border: none;
      background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208);
      color: #111;
      font-weight: 800;
      cursor: pointer;
      transition: .3s ease;
      text-decoration: none;
    }

    .select-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 24px rgba(243,197,71,.25);
    }
  </style>
</head>
<body>
  <div class="background-strip">
    <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80">
    <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80">
    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=80">
    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=1200&q=80">
  </div>

  <div class="container">
    <div class="navbar">
      <div class="logo">EventIntel</div>

      <div class="nav-links">
        <button onclick="window.location.href='homepage.php'">Home</button>
        <button onclick="window.location.href='createevent.php'">Create Event</button>
        <button class="active" onclick="window.location.href='yourevents.php'">Your Events</button>
        <button onclick="window.location.href='recommendation.php'">Recommendations</button>
        <button onclick="window.location.href='newsfeed.php'">Newsfeed</button>
      </div>
    </div>

    <div class="hero">
      <div>
        <h1>Select Event Coordinator</h1>
        <p>Choose a professional coordinator to manage your event from planning to execution with ease and precision.</p>
      </div>

      <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search coordinator or specialty...">
      </div>
    </div>

    <div class="coordinator-grid">
      <?php if (empty($coordinators)): ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #999;">
          <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 20px; display: block;"></i>
          <h3>No Coordinators Available</h3>
          <p>Check back later for available event coordinators</p>
        </div>
      <?php else: ?>
        <?php foreach ($coordinators as $coordinator): ?>
        <div class="coordinator-card">
          <div class="coordinator-image">
            <span class="badge">Professional</span>
            <img src="../images/logo.png" alt="<?= esc($coordinator['full_name']) ?>">
          </div>
<div class="coordinator-content">
            <h3><?= esc($coordinator['full_name']) ?></h3>
            <?php if (!empty($coordinator['business_name'])): ?>
              <div style="font-size:13px;color:#d4a017;font-weight:700;margin-bottom:8px;"><?= esc($coordinator['business_name']) ?></div>
            <?php endif; ?>
            <div class="details">
              <span><i class="fa-solid fa-star"></i> <?= number_format((float)$coordinator['avg_rating'], 1) ?> (<?= (int)$coordinator['total_reviews'] ?>)</span>
              <span><i class="fa-solid fa-box"></i> <?= (int)$coordinator['total_packages'] ?> Packages</span>
            </div>
            <p><?= esc($coordinator['business_address'] ?: 'Professional event coordinator with extensive experience in managing all types of events.') ?></p>
            <div class="footer">
              <div class="price"><?= $coordinator['min_package'] ? '₱' . number_format((float)$coordinator['min_package']) : 'View Profile' ?></div>
              <a href="orgbio.php?coordinator_id=<?= $coordinator['user_id'] ?>" class="select-btn">Select</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
