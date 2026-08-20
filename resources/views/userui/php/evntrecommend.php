<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EventIntel - Recommendations</title>
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
  min-height:100vh;
}

/* NAVBAR */
.navbar { 
  width: 100%;
  padding: 12px 48px 24px 48px;
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
}

.profile-btn i {
  color: #d4a017;
}

/* HEADER */
.container{
  padding: 6px 48px 40px;
}

h1{
  font-size: 42px;
  margin-bottom: 10px;
  color:#111;
}

.subtitle{
  color:#666;
  margin-bottom:30px;
}

/* GRID */
.grid{
  display:grid;
  grid-template-columns: repeat(4, 1fr);
  gap:20px;
}

/* CARD */
.card{
  background:rgba(255,255,255,.82);
  border:1px solid rgba(212,160,23,.12);
  border-radius:20px;
  overflow:hidden;
  transition:.3s;
  box-shadow:0 12px 28px rgba(0,0,0,.06);
}

.card:hover{
  transform:translateY(-6px);
  border-color:rgba(212,160,23,.3);
  box-shadow:0 15px 35px rgba(243,197,71,.15);
}

.card img{
  width:100%;
  height:240px;
  object-fit:cover;
  object-position: 0% 15%;
  filter:brightness(.95);
}

.card-content{
  padding:14px;
}

.card-content h3{
  font-size:16px;
  margin-bottom:6px;
  color:#111;
}

.card-content p{
  font-size:13px;
  color:#666;
  margin-bottom:10px;
}

/* TAG */
.tag{
  display:inline-block;
  padding:5px 10px;
  border-radius:999px;
  background:rgba(255,215,0,.1);
  color:#d4a017;
  font-size:11px;
  margin-bottom:10px;
}

/* BUTTONS */
.actions{
  display:flex;
  gap:8px;
}

.btn{
  flex:1;
  padding:8px;
  border-radius:10px;
  border:none;
  cursor:pointer;
  font-size:12px;
  font-weight:bold;
}

.btn-primary{
  background:linear-gradient(135deg,#fff2ab,#f3c547,#c99208);
  color:#111;
}

.btn-outline{
  background:rgba(255,255,255,0.78);
  border:1px solid rgba(212,160,23,.2);
  color:#d4a017;
}

</style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
  <div class="logo-text">EventIntel</div>

  <div class="nav-links">
    <button onclick=\"window.location.href='newsfeed.php'\">Home</button>
    <button class=\"active\" onclick=\"window.location.href='createevent.php'\">Create Event</button>
    <button onclick=\"window.location.href='yourevents.php'\">Your Events</button>
    <button onclick=\"window.location.href='recommendation.php'\">Recommendations</button>
    <button onclick=\"window.location.href='newsfeed.php'\">Newsfeed</button>
  </div>
</div>

<div class="container">

<h1>Recommended for You</h1>
<p class="subtitle">Based on your preferences and budget</p>

<div class="grid">

<!-- FOOD -->
<div class="card">
  <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1">
  <div class="card-content">
    <span class="tag">Food</span>
    <h3>Premium Buffet</h3>
    <p>High-quality dishes for all guests</p>
    <div class="actions">
      <button class="btn btn-primary">Select</button>
      <button class="btn btn-outline">View</button>
    </div>
  </div>
</div>

<!-- HOST -->
<div class="card">
  <img src="images/vince.jpg">
  <div class="card-content">
    <span class="tag">Host</span>
    <h3>Professional Host</h3>
    <p>Engaging and experienced MC</p>
    <div class="actions">
      <button class="btn btn-primary">Select</button>
      <button class="btn btn-outline">View</button>
    </div>
  </div>
</div>

<!-- CATERING -->
<div class="card">
  <img src="https://images.unsplash.com/photo-1529692236671-f1f6cf9683ba">
  <div class="card-content">
    <span class="tag">Catering</span>
    <h3>Elite Catering</h3>
    <p>Delicious menu packages</p>
    <div class="actions">
      <button class="btn btn-primary">Select</button>
      <button class="btn btn-outline">View</button>
    </div>
  </div>
</div>

<!-- PHOTOGRAPHER -->
<div class="card">
  <img src="https://images.unsplash.com/photo-1500530855697-b586d89ba3ee">
  <div class="card-content">
    <span class="tag">Photographer</span>
    <h3>Event Photography</h3>
    <p>Capture every special moment</p>
    <div class="actions">
      <button class="btn btn-primary">Select</button>
      <button class="btn btn-outline">View</button>
    </div>
  </div>
</div>

<!-- VENUE -->
<div class="card">
  <img src="https://images.unsplash.com/photo-1505236858219-8359eb29e329">
  <div class="card-content">
    <span class="tag">Venue</span>
    <h3>Grand Ballroom</h3>
    <p>Elegant and spacious venue</p>
    <div class="actions">
      <button class="btn btn-primary">Select</button>
      <button class="btn btn-outline">View</button>
    </div>
  </div>
</div>

<!-- SOUNDS -->
<div class="card">
  <img src="images/rm.jpg">
  <div class="card-content">
    <span class="tag">Sounds & Lights</span>
    <h3>Pro Audio Setup</h3>
    <p>High-quality sound system</p>
    <div class="actions">
      <button class="btn btn-primary">Select</button>
      <button class="btn btn-outline">View</button>
    </div>
  </div>
</div>

<!-- CLOTHES -->
<div class="card">
  <img src="https://images.unsplash.com/photo-1520975922284-9e0f71b4a1b6">
  <div class="card-content">
    <span class="tag">Clothes</span>
    <h3>Formal Attire</h3>
    <p>Stylish outfits for your event</p>
    <div class="actions">
      <button class="btn btn-primary">Select</button>
      <button class="btn btn-outline">View</button>
    </div>
  </div>
</div>

<!-- PACKAGE -->
<div class="card">
  <img src="https://images.unsplash.com/photo-1527529482837-4698179dc6ce">
  <div class="card-content">
    <span class="tag">Package</span>
    <h3>All-in-One Package</h3>
    <p>Complete event solution</p>
    <div class="actions">
      <button class="btn btn-primary">Select</button>
      <button class="btn btn-outline">View</button>
    </div>
  </div>
</div>

</div>

</div>

</body>
</html>