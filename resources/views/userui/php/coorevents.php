<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EventIntel - Organizer Services</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif}
body{background:#ffffff;color:#222;min-height:100vh}

.container{max-width:1500px;margin:auto;padding:6px 48px 40px}

/* NAVBAR */
.navbar{width:100%;padding:12px 0 24px;display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap}
.logo-text{font-size:26px;font-weight:800;color:#f3c547;letter-spacing:1px}
.nav-links{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.nav-links button{padding:8px 18px;border-radius:12px;border:1px solid rgba(212,160,23,.35);background:rgba(255,255,255,.55);color:#222;font-size:14px;cursor:pointer;transition:.3s ease}
.nav-links button:hover,.nav-links .active{background:linear-gradient(to right,#ffe17a,#d4a017);color:black;box-shadow:0 0 14px rgba(255,215,0,.12)}
.profile-btn{width:44px;height:44px;border-radius:50%;border:1px solid rgba(255,215,0,.30);background:#fff;display:flex;align-items:center;justify-content:center;color:#f3c547}

/* HEADER */
.header{margin:30px 0}
.header h1{font-size:44px;margin-bottom:8px;color:#111}
.header p{color:#555}

/* GRID */
.services-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}

.card{background:#fff;border:1px solid rgba(255,215,0,.12);border-radius:26px;overflow:hidden;transition:.3s;box-shadow:0 8px 20px rgba(0,0,0,.08)}
.card:hover{transform:translateY(-6px);border-color:rgba(255,215,0,.3);box-shadow:0 15px 35px rgba(243,197,71,.15)}

.card img{width:100%;height:200px;object-fit:cover;filter:brightness(.95);transition:.3s}
.card:hover img{transform:scale(1.05);filter:brightness(1)}

.card-content{padding:18px}
.card-content h3{margin-bottom:8px;color:#111}
.card-content p{font-size:14px;color:#555;margin-bottom:12px}

.tags{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
.tag{padding:6px 10px;border-radius:999px;background:rgba(255,215,0,.1);color:#f3c547;font-size:12px}

.actions{display:flex;gap:10px}
.btn{flex:1;padding:10px;border-radius:12px;border:none;font-weight:700;cursor:pointer}
.btn-primary{background:linear-gradient(135deg,#fff2ab,#f3c547,#c99208);color:#111}
.btn-outline{background:transparent;border:1px solid rgba(255,215,0,.3);color:#f3c547}
.btn:hover{transform:translateY(-2px);box-shadow:0 8px 18px rgba(243,197,71,.2)}
</style>


</head>
<body>

<div class="navbar">
<div class="logo-text">EventIntel</div>
<div class="nav-links">
  <button onclick="window.location.href='homepage.php'">Home</button>
  <button class="active" onclick="window.location.href='createevent.php'">Create Event</button>
  <button onclick="window.location.href='yourevents.php'">Your Events</button>
  <button onclick="window.location.href='recommendation.php'">Recommendations</button>
  <button onclick="window.location.href='newsfeed.php'">Newsfeed</button>
</div>
</div>

<div class="container">

<div class="header">
<h1>Events We Organize</h1>
<p>Explore the types of events this organizer specializes in.</p>
</div>

<div class="services-grid">

<div class="card">
<img src="https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80">
<div class="card-content">
<h3>Wedding Events</h3>
<p>Elegant and unforgettable wedding experiences tailored to your dream day.</p>
<div class="tags">
<span class="tag">Luxury</span>
<span class="tag">Full Planning</span>
</div>
<div class="actions">
<button class="btn btn-primary">Select</button>
<button class="btn btn-outline">View</button>
</div>
</div>
</div>

<div class="card">
<img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=800&q=80">
<div class="card-content">
<h3>Concerts & Festivals</h3>
<p>High-energy events with full production, sound, and lighting coordination.</p>
<div class="tags">
<span class="tag">Live</span>
<span class="tag">Large Scale</span>
</div>
<div class="actions">
<button class="btn btn-primary">Select</button>
<button class="btn btn-outline">View</button>
</div>
</div>
</div>

<div class="card">
<img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=800&q=80">
<div class="card-content">
<h3>Corporate Events</h3>
<p>Professional seminars, conferences, and corporate gatherings.</p>
<div class="tags">
<span class="tag">Business</span>
<span class="tag">Formal</span>
</div>
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
