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
      background: #050505;
      color: white;
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
      background: rgba(243, 197, 71, 0.08);
      top: -140px;
      left: -120px;
    }

    body::after {
      width: 540px;
      height: 540px;
      background: rgba(243, 197, 71, 0.05);
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
      color: #f3c547;
      letter-spacing: 1px;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 18px;
    }

    .nav-links button {
      padding: 8px 18px;
      border-radius: 12px;
      border: 1px solid rgba(255, 215, 0, 0.3);
      background: transparent;
      color: white;
      font-size: 14px;
      cursor: pointer;
      transition: 0.3s ease;
    }

    .nav-links button:hover,
    .nav-links .active {
      background: rgba(255, 215, 0, 0.12);
      color: #f3c547;
      box-shadow: 0 0 14px rgba(255, 215, 0, 0.12);
    }

    .profile-btn {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      border: 1px solid rgba(255, 215, 0, 0.35);
      background: #111;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #f3c547;
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
      height: 470px;
      overflow: hidden;
      border-radius: 28px;
      border: 1px solid rgba(255,215,0,.12);
      background: #111;
    }

    .main-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(.78);
      transition: .4s ease;
    }

    .main-image:hover img {
      transform: scale(1.03);
      filter: brightness(.92);
    }

    .main-image::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(0,0,0,.55), rgba(0,0,0,.05));
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
      border: 1px solid rgba(255,215,0,.1);
      cursor: pointer;
      transition: .3s ease;
      background: #111;
    }

    .thumbnail img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(.65);
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
      background: rgba(15,15,15,.92);
      border: 1px solid rgba(255,215,0,.12);
      border-radius: 30px;
      padding: 34px;
      backdrop-filter: blur(16px);
      box-shadow: 0 20px 40px rgba(0,0,0,.35);
    }

    .tag {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 16px;
      border-radius: 999px;
      background: rgba(243,197,71,.12);
      border: 1px solid rgba(243,197,71,.2);
      color: #f3c547;
      font-size: 13px;
      margin-bottom: 20px;
    }

    .details-card h1 {
      font-size: 48px;
      margin-bottom: 14px;
      line-height: 1.1;
    }

    .subtitle {
      color: #a6a6a6;
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
      background: rgba(255,255,255,.03);
      border: 1px solid rgba(255,215,0,.08);
    }

    .stat-box span {
      display: block;
      color: #8f8f8f;
      font-size: 13px;
      margin-bottom: 8px;
    }

    .stat-box strong {
      color: #f3c547;
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
      background: rgba(255,255,255,.03);
      border: 1px solid rgba(255,215,0,.08);
      color: #c8c8c8;
    }

    .offer-item i {
      width: 38px;
      height: 38px;
      border-radius: 12px;
      display: flex;
      justify-content: center;
      align-items: center;
      background: rgba(243,197,71,.12);
      color: #f3c547;
      flex-shrink: 0;
    }

    .actions {
      display: flex;
      gap: 16px;
    }

    .book-btn,
    .location-btn {
      flex: 1;
      height: 58px;
      border-radius: 18px;
      border: none;
      cursor: pointer;
      font-size: 15px;
      font-weight: 800;
      transition: .3s ease;
    }

    .book-btn {
      background: linear-gradient(135deg, #fff2ab, #f3c547, #c99208);
      color: #111;
    }

    .book-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 24px rgba(243,197,71,.25);
    }

    .location-btn {
      background: transparent;
      color: #f3c547;
      border: 1px solid rgba(255,215,0,.25);
    }

    .location-btn:hover {
      background: rgba(243,197,71,.08);
      transform: translateY(-2px);
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="navbar">
      <div class="logo">EventIntel</div>

      <div class="nav-links">
        <button>Home</button>
        <button class="active">Create Event</button>
        <button>Your Events</button>
        <div class="profile-btn">
          <i class="fa-regular fa-user"></i>
        </div>
      </div>
    </div>

    <div class="hero">
      <div class="gallery">
        <div class="main-image">
          <img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1400&q=80" alt="Venue Main">
        </div>

        <div class="thumbnail-row">
          <div class="thumbnail active">
            <img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=600&q=80" alt="Thumbnail 1">
          </div>

          <div class="thumbnail">
            <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=600&q=80" alt="Thumbnail 2">
          </div>

          <div class="thumbnail">
            <img src="https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=600&q=80" alt="Thumbnail 3">
          </div>

          <div class="thumbnail">
            <img src="https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=600&q=80" alt="Thumbnail 4">
          </div>
        </div>
      </div>

      <div class="details-card">
        <div class="tag">
          <i class="fa-solid fa-crown"></i>
          Premium Wedding & Event Venue
        </div>

        <h1>Casa de Alvin</h1>

        <p class="subtitle">
          An elegant indoor venue designed for weddings, receptions, debuts, and formal celebrations. Featuring luxurious interiors, modern lighting, and flexible layouts.
        </p>

        <div class="stats">
          <div class="stat-box">
            <span>Capacity</span>
            <strong>350 Guests</strong>
          </div>

          <div class="stat-box">
            <span>Starting Price</span>
            <strong>₱45,000</strong>
          </div>
        </div>

        <div class="offers-title">Included Offers</div>

        <div class="offers">
          <div class="offer-item">
            <i class="fa-solid fa-chair"></i>
            Complete table and chair arrangement for up to 350 guests
          </div>

          <div class="offer-item">
            <i class="fa-solid fa-lightbulb"></i>
            Decorative ambient lighting and chandelier setup
          </div>

          <div class="offer-item">
            <i class="fa-solid fa-music"></i>
            Basic sound system with microphones and stage access
          </div>

          <div class="offer-item">
            <i class="fa-solid fa-car"></i>
            Free parking area and private entrance access
          </div>
        </div>

        <div class="actions">
          <button class="book-btn">
            <i class="fa-solid fa-calendar-check"></i>
            Book Venue
          </button>

          <button class="location-btn">
            <i class="fa-solid fa-location-dot"></i>
            View Location
          </button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>