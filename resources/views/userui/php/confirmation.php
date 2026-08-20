<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');

$event_id = $_GET['event_id'] ?? null;
$event = null;
if ($event_id) {
    $stmt = db()->prepare("SELECT payment_method FROM events WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$event_id, $_SESSION['user_id']]);
    $event = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EventIntel - Booking Confirmed</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
    *{
      margin:0;
      padding:0;
      box-sizing:border-box;
      font-family:'Segoe UI',sans-serif;
    }

    body{
      background:#f8f8f8;
      color:#111;
      height:100vh;
      display:flex;
      flex-direction:column;
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

    .logo-text {
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
      background: rgba(255,255,255,0.8);
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .profile-btn i {
      color: #d4a017;
    }

    /* MAIN */
    .container{
      flex:1;
      display:flex;
      justify-content:center;
      align-items:center;
      padding:6px 48px 40px;
    }

    /* CONFIRMATION CARD */
    .card{
      background:rgba(255,255,255,0.82);
      border:1px solid rgba(212,160,23,.14);
      border-radius:30px;
      padding:50px;
      text-align:center;
      max-width:600px;
      width:100%;
      box-shadow:0 20px 50px rgba(0,0,0,.08);
      backdrop-filter:blur(14px);
    }

    /* ICON */
    .success-icon{
      width:90px;
      height:90px;
      margin:auto;
      border-radius:50%;
      background:linear-gradient(135deg,#fff2ab,#f3c547,#c99208);
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:40px;
      color:#111;
      margin-bottom:20px;
    }

    /* TEXT */
    .card h1{
      font-size:32px;
      margin-bottom:10px;
      color:#111;
    }

    .card p{
      color:#666;
      margin-bottom:25px;
      line-height:1.5;
    }

    /* BUTTONS */
    .actions{
      display:flex;
      gap:15px;
      justify-content:center;
    }

    .btn{
      padding:12px 22px;
      border-radius:12px;
      font-weight:700;
      cursor:pointer;
      border:none;
      transition:.3s ease;
    }

    .btn-primary{
      background:linear-gradient(135deg,#fff2ab,#f3c547,#c99208);
      color:#111;
    }

    .btn-outline{
      background:rgba(255,255,255,0.75);
      border:1px solid rgba(212,160,23,.2);
      color:#d4a017;
    }

    .btn:hover{
      transform:translateY(-2px);
      box-shadow:0 10px 20px rgba(243,197,71,.2);
    }

  </style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
  <div class="logo-text">EventIntel</div>

  <div class="nav-links">
    <button onclick="window.location.href='homepage.php'">Home</button>
    <button onclick="window.location.href='createevent.php'">Create Event</button>
    <button class="active" onclick="window.location.href='yourevents.php'">Your Events</button>
    <button onclick="window.location.href='recommendation.php'">Recommendations</button>
    <button onclick="window.location.href='newsfeed.php'">Newsfeed</button>
  </div>
</div>

<!-- CONTENT -->
<div class="container">
  <div class="card">

    <div class="success-icon">
      <i class="fa-solid fa-check"></i>
    </div>

    <h1>Booking Confirmed</h1>
    <p>
      Your event has been successfully booked! All details have been saved and 
      our team is preparing everything for your special event.
    </p>


    <div class="actions">
      <button class="btn btn-primary" onclick="window.location.href='yourevents.php'">View Event</button>
      <button class="btn btn-outline" onclick="window.location.href='homepage.php'">Back to Home</button>
    </div>

  </div>
</div>

</body>
</html>