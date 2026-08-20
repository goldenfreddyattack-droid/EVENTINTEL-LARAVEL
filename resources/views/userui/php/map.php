<?php require_once __DIR__ . '/../../config/db.php'; require_login();
$event_id=intval($_GET['id']??0);
$event=['venue_name'=>'The Grand Pavilion','venue_address'=>'Apalit, Pampanga','latitude'=>'14.9533','longitude'=>'120.7690'];
if($event_id){$st=db()->prepare("SELECT * FROM events WHERE event_id=?");$st->execute([$event_id]);$e=$st->fetch(); if($e)$event=array_merge($event,$e);}
?><!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventIntel - Venue Location</title>
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
      border: 1px solid rgba(212,160,23,0.25);
      background: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #d4a017;
      cursor: pointer;
    }

    /* MAP SECTION */
    .map-wrapper {
      margin-top: 10px;
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 28px;
    }

    .map-box {
      width: 100%;
      height: 600px;
      border-radius: 28px;
      overflow: hidden;
      border: 1px solid rgba(212,160,23,.12);
      background: #f8f8f8;
      box-shadow: 0 12px 30px rgba(0,0,0,.08);
    }

    iframe {
      width: 100%;
      height: 100%;
      border: none;
      filter: grayscale(.2) contrast(1.05) brightness(1);
    }

    /* SIDE INFO */
    .info-card {
      background: rgba(255,255,255,.95);
      border: 1px solid rgba(212,160,23,.12);
      border-radius: 28px;
      padding: 28px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-shadow: 0 12px 30px rgba(0,0,0,.08);
    }

    .info-card h2 {
      font-size: 30px;
      margin-bottom: 10px;
      color: #111;
    }

    .info-card p {
      color: #666;
      line-height: 1.6;
      margin-bottom: 20px;
    }

    .location-details {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-bottom: 25px;
    }

    .location-details div {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #555;
      font-size: 14px;
    }

    .location-details i {
      color: #d4a017;
    }

    .action-buttons {
      display: flex;
      gap: 14px;
    }

    .btn {
      flex: 1;
      height: 52px;
      border-radius: 14px;
      font-weight: 700;
      cursor: pointer;
      border: none;
      transition: 0.3s ease;
    }

    .btn-primary {
      background: linear-gradient(135deg, #ffe27a, #d4a017, #b8860b);
      color: white;
    }

    .btn-outline {
      background: #fff;
      color: #d4a017;
      border: 1px solid rgba(212,160,23,.25);
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 18px rgba(243,197,71,.18);
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
    <button onclick="window.location.href='recommendation.php'">Recommendations</button>
    <button onclick="window.location.href='newsfeed.php'">Newsfeed</button>
  </div>
</div>

<div class="map-wrapper">

  <div class="map-box">
    <iframe src="https://www.google.com/maps?q=<?=urlencode($event['venue_address'])?>&output=embed"></iframe>
  </div>

  <div class="info-card">
    <div>
      <h2><?=esc($event['venue_name'] ?: 'Event Venue')?></h2>
      <p>
        Located in the heart of the city, this premium venue is easily accessible
        and surrounded by hotels, restaurants, and transport hubs.
      </p>

      <div class="location-details">
        <div><i class="fa-solid fa-location-dot"></i> <?=esc($event['venue_address'])?></div>
        <div><i class="fa-solid fa-road"></i> Near major highways</div>
        <div><i class="fa-solid fa-car"></i> Parking available</div>
      </div>
    </div>

    <div class="action-buttons">
      <button class="btn btn-primary" onclick="openDirections()">Show Way</button>
      <button class="btn btn-outline" onclick="history.back()">Back</button>
    </div>
  </div>

</div>

  </div>

<script>
function openDirections(){
  const dest = encodeURIComponent("<?=esc($event['venue_address'])?>");
  if(navigator.geolocation){
    navigator.geolocation.getCurrentPosition(pos=>{
      window.open(`https://www.google.com/maps/dir/${pos.coords.latitude},${pos.coords.longitude}/${dest}`,'_blank');
    },()=> window.open(`https://www.google.com/maps/dir/?api=1&destination=${dest}`,'_blank'));
  } else {
    window.open(`https://www.google.com/maps/dir/?api=1&destination=${dest}`,'_blank');
  }
}
</script></body>
</html>
