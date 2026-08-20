<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');
$pdo = db();
$user_id = $_SESSION['user_id'];

// Fetch user's events
$events = $pdo->prepare("
    SELECT event_id, title, event_type, event_date, budget, guest_count
    FROM events
    WHERE user_id = ?
    ORDER BY event_date DESC
");
$events->execute([$user_id]);
$user_events = $events->fetchAll();
// If user has no events, fetch recent events as fallback
$fallback_events = [];
if (empty($user_events)) {
  $f = $pdo->query("SELECT event_id, title, event_type, event_date, budget, guest_count FROM events ORDER BY event_date DESC LIMIT 10");
  $fallback_events = $f->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EventIntel - Recommendation</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
*{
  margin:0;
  padding:0;
  box-sizing:border-box;
  font-family:'Segoe UI',sans-serif;
}

/* BACKGROUND GLOW EFFECT */
body::before,
body::after {
  content: "";
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  z-index: 0;
  pointer-events: none;
}

/* TOP LEFT GOLD GLOW */
body::before {
  width: 420px;
  height: 420px;
  background: rgba(255, 196, 0, 0.10);
  top: -140px;
  left: -120px;
}

/* BOTTOM RIGHT GOLD GLOW */
body::after {
  width: 520px;
  height: 520px;
  background: rgba(255, 215, 0, 0.08);
  bottom: -200px;
  right: -140px;
}

body{
  background:#ffffff;
  color:#222;
  height:100vh;
  overflow:hidden;
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
}

.profile-btn i {
  color: #f3c547;
}

/* LAYOUT */
.container{
  display:grid;
  grid-template-columns: 52% 48%;
  height: calc(100vh - 80px);
  overflow:hidden;
  position: relative;
  z-index: 1;
}

/* LEFT IMAGE */
.left{
  position: relative;
  height: 100%;
  overflow: hidden;
  display: flex;
  border-top-right-radius: 30px;
  border-bottom-right-radius: 30px;
}

.left::before{
  content: "";
  position: absolute;
  inset: 0;
  background: url('https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80') center center / cover no-repeat;
  transform: scale(1.05);
}

.left::after{
  content:'';
  position:absolute;
  inset:0;
  background: linear-gradient(
    to right,
    rgba(255,255,255,0.92) 0%,
    rgba(255,255,255,0.60) 50%,
    rgba(255,255,255,0.20) 100%
  );
}

/* RIGHT PANEL */
.right{
  padding:40px 60px;
  overflow-y:auto;
  height:100%;
}

.right::-webkit-scrollbar{
  width:6px;
}

.right::-webkit-scrollbar-thumb{
  background:rgba(255,215,0,0.35);
  border-radius:10px;
}

h1{
  font-size:42px;
  margin-bottom:20px;
  color:#111;
}

/* INPUTS */
.input-group{
  display:flex;
  gap:20px;
  margin-bottom:20px;
}

.input{
  flex:1;
}

.input label{
  font-size:12px;
  color:#777;
}

.input input{
  width:100%;
  padding:12px;
  margin-top:6px;
  border-radius:10px;
  border:1px solid rgba(255,215,0,.15);
  background:#fff;
  color:#222;
}

/* SERVICES */
.services{
  margin-top:20px;
  display:flex;
  flex-direction:column;
  gap:12px;
}

.service{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:14px 18px;
  border-radius:14px;
  background:#fff;
  border:1px solid rgba(255,215,0,.12);
  cursor:pointer;
  transition:.3s;
  color:#222;
  box-shadow:0 4px 12px rgba(0,0,0,.04);
}

.service:hover{
  border-color:rgba(255,215,0,.35);
}

.service.active{
  background:linear-gradient(135deg,#fff7dc,#ffffff);
  border-color:#f3c547;
}

.checkbox{
  width:20px;
  height:20px;
  border:2px solid #f3c547;
  border-radius:6px;
  display:flex;
  align-items:center;
  justify-content:center;
}

.checkbox i{
  display:none;
  font-size:12px;
}

.service.active .checkbox i{
  display:block;
}

/* BUTTON */
.generate{
  margin-top:30px;
  width:100%;
  padding:14px;
  border:none;
  border-radius:14px;
  font-weight:bold;
  font-size:16px;
  cursor:pointer;
  background:linear-gradient(135deg,#fff2ab,#f3c547,#c99208);
  color:#111;
}

.generate:hover{
  transform:translateY(-2px);
  box-shadow:0 10px 20px rgba(243,197,71,.25);
}

/* RESULT BOX */
.result{
  margin-top:25px;
  padding:20px;
  border-radius:16px;
  background:#fff;
  border:1px solid rgba(255,215,0,.18);
  display:none;
  color:#222;
  box-shadow:0 6px 18px rgba(0,0,0,.05);
}

.result h3{
  margin-bottom:10px;
  color:#f3c547;
}
</style>
</head>
<body>

<div class="navbar">
  <div class="logo-text">EventIntel</div>

  <div class="nav-links">
    <button onclick="window.location.href='homepage.php'">Home</button>
    <button onclick="window.location.href='createevent.php'">Create Event</button>
    <button onclick="window.location.href='yourevents.php'">Your Events</button>
    <button class="active" onclick="window.location.href='recommendation.php'">Recommendations</button>
    <button onclick="window.location.href='packages.php'">Packages</button>
    <button onclick="window.location.href='newsfeed.php'">Newsfeed</button>
    <button class="profile-btn" type="button" aria-label="Profile" title="Profile" onclick="window.location.href='profile.php'">
      <i class="fas fa-user"></i>
    </button>
  </div>
</div>

<div class="container">

  <!-- LEFT IMAGE -->
  <div class="left"></div>

  <!-- RIGHT PANEL -->
  <div class="right">

    <h1>Smart Recommendation Engine</h1>
    <p style="color: #999; margin-bottom: 20px;">Get AI-powered event planning suggestions with detailed timeline</p>

    <div class="input">
      <label>SELECT YOUR EVENT</label>
      <div style="display:flex;gap:8px;align-items:center;">
        <select id="eventSelect" onchange="loadEventDetails()" style="flex:1;">
          <option value="">-- Select an event --</option>
          <?php if (!empty($user_events)): ?>
            <optgroup label="Your Events">
              <?php foreach ($user_events as $evt): ?>
                <option value="<?= $evt['event_id'] ?>" data-type="<?= esc($evt['event_type']) ?>" data-budget="<?= esc($evt['budget']) ?>" data-pax="<?= esc($evt['guest_count']) ?>">
                  <?= esc($evt['title']) ?> - <?= esc($evt['event_type']) ?> (<?= date('M j, Y', strtotime($evt['event_date'])) ?>)
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endif; ?>

          <?php if (!empty($fallback_events)): ?>
            <optgroup label="Recent Events">
              <?php foreach ($fallback_events as $evt): ?>
                <option value="<?= $evt['event_id'] ?>" data-type="<?= esc($evt['event_type']) ?>" data-budget="<?= esc($evt['budget']) ?>" data-pax="<?= esc($evt['guest_count']) ?>">
                  <?= esc($evt['title']) ?> - <?= esc($evt['event_type']) ?> (<?= date('M j, Y', strtotime($evt['event_date'])) ?>)
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endif; ?>
        </select>
        <button onclick="window.location.href='createevent.php'" style="padding:8px 12px;border-radius:8px;background:#f3c547;border:none;cursor:pointer;color:#000;">Create Event</button>
      </div>
    </div>

    <div class="input-group">
      <div class="input">
        <label>BUDGET (PHP)</label>
        <input type="number" id="budget" placeholder="35000">
      </div>

      <div class="input">
        <label>NUMBER OF GUESTS</label>
        <input type="number" id="pax" placeholder="50">
      </div>
    </div>

    <div class="input">
      <label>EVENT TYPE</label>
      <input type="text" id="event" placeholder="e.g., Birthday, Wedding, Corporate">
    </div>

    <!-- SERVICES -->
    <div class="services" id="services">
      <div class="service" data-service="venue">Venue <div class="checkbox"><i class="fa-solid fa-check"></i></div></div>
      <div class="service" data-service="catering">Catering/Food <div class="checkbox"><i class="fa-solid fa-check"></i></div></div>
      <div class="service" data-service="host">Host/MC <div class="checkbox"><i class="fa-solid fa-check"></i></div></div>
      <div class="service" data-service="sounds_lights">Sounds & Lights <div class="checkbox"><i class="fa-solid fa-check"></i></div></div>
      <div class="service" data-service="photographer">Photographer <div class="checkbox"><i class="fa-solid fa-check"></i></div></div>
      <div class="service" data-service="clothes">Clothing/Attire <div class="checkbox"><i class="fa-solid fa-check"></i></div></div>
      <div class="service" data-service="decorations">Decorations <div class="checkbox"><i class="fa-solid fa-check"></i></div></div>
    </div>

    <div style="display: flex; gap: 12px; margin-top: 30px;">
      <button class="generate" onclick="generateRecommendation()">Generate Timeline & Recommendations</button>
      <button class="generate" onclick="regenerateRecommendation()" style="background: linear-gradient(135deg, #e8b70f, #c99208); display: none;" id="regenerateBtn">Regenerate</button>
    </div>

    <div class="result" id="result">
      <h3>📅 Your Event Timeline & Recommendations</h3>
      <div id="resultText"></div>
      <div id="recommendationActionArea" style="margin-top: 12px;"></div>
      <button class="generate" onclick="regenerateRecommendation()" style="margin-top: 15px; width: auto; padding: 10px 20px; font-size: 14px;">↻ Regenerate Different Suggestions</button>
    </div>

  </div>

</div>

<style>
  select, #event {
    width: 100%;
    padding: 12px;
    margin-top: 6px;
    border-radius: 10px;
    border: 1px solid rgba(255,215,0,.15);
    background: #fff;
    color: #222;
    font-family: 'Segoe UI', sans-serif;
    font-size: 14px;
  }

  select:focus, #event:focus {
    outline: none;
    border-color: #f3c547;
  }

  .result h3 {
    color: #f3c547;
    margin-bottom: 15px;
    font-size: 18px;
  }

  .timeline-item {
    display: flex;
    gap: 15px;
    margin: 12px 0;
    padding: 12px;
    background: rgba(243, 197, 71, 0.06);
    border-left: 3px solid #f3c547;
    border-radius: 6px;
  }

  .timeline-time {
    font-weight: 600;
    color: #f3c547;
    min-width: 80px;
  }

  .timeline-event {
    color: #333;
    flex: 1;
  }

  .service-recommendation {
    background: rgba(243, 197, 71, 0.08);
    border: 1px solid rgba(243, 197, 71, 0.2);
    padding: 10px 12px;
    border-radius: 8px;
    margin: 8px 0;
    color: #333;
  }
</style>

<script>
  let currentGenerationParams = null;

  const recommendationServiceMap = {
    venue: 'Venue',
    catering: 'Catering',
    host: 'Host',
    sounds_lights: 'Sounds',
    photographer: 'Photographer',
    clothes: 'Clothing',
    decorations: 'Decorations'
  };

  function loadEventDetails() {
    const select = document.getElementById('eventSelect');
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption && selectedOption.value) {
      const eventType = selectedOption.getAttribute('data-type') || '';
      document.getElementById('event').value = eventType;
      document.getElementById('budget').value = selectedOption.getAttribute('data-budget') || '';
      document.getElementById('pax').value = selectedOption.getAttribute('data-pax') || '';
    } else {
      document.getElementById('event').value = '';
      document.getElementById('budget').value = '';
      document.getElementById('pax').value = '';
    }
  }

  function getSelectedRecommendationServices() {
    return Array.from(document.querySelectorAll('.service.active')).map(item => {
      const key = item.getAttribute('data-service') || '';
      return recommendationServiceMap[key] || item.innerText.trim();
    });
  }

  function renderUseRecommendationButton() {
    const actionArea = document.getElementById('recommendationActionArea');
    if (!actionArea) return;

    actionArea.innerHTML = '<button class="generate" type="button" onclick="applyRecommendationToCreateEvent()" style="margin-top: 0; width: 100%;">Use This Recommendation</button>';
  }

  function applyRecommendationToCreateEvent() {
    const event = document.getElementById('event').value.trim();
    const pax = document.getElementById('pax').value.trim();
    const services = currentGenerationParams?.services?.length ? currentGenerationParams.services : getSelectedRecommendationServices();

    if (!event || !pax) {
      alert('Please generate a recommendation first and make sure your event and guest count are filled in.');
      return;
    }

    const payload = {
      eventType: event,
      guestCount: pax,
      services: services
    };

    try {
      sessionStorage.setItem('event_recommendation_prefill', JSON.stringify(payload));
      document.cookie = 'event_recommendation_prefill=' + encodeURIComponent(JSON.stringify(payload)) + '; path=/; max-age=3600';
    } catch (err) {
      console.warn('Could not save recommendation data', err);
    }

    window.location.href = 'createevent.php?from=recommendation';
  }

  // Toggle services
  document.querySelectorAll('.service').forEach(item => {
    item.addEventListener('click', () => {
      item.classList.toggle('active');
    });
  });

  // Generate recommendation
  async function generateRecommendation() {
    const budget = document.getElementById('budget').value.trim();
    const pax = document.getElementById('pax').value.trim();
    const eventInput = document.getElementById('event');
    const eventSelect = document.getElementById('eventSelect');
    const selectedOption = eventSelect.options[eventSelect.selectedIndex];
    let event = (eventInput.value || '').trim();
    const eventId = eventSelect.value;
    const selected = getSelectedRecommendationServices();

    if (!event && selectedOption && selectedOption.value) {
      event = selectedOption.getAttribute('data-type') || '';
    }

    if (!event) {
      event = 'Event';
    }

    if (!budget || !pax) {
      alert('Please fill in your budget and guest count before generating recommendations.');
      return;
    }

    const resultBox = document.getElementById('result');
    const text = document.getElementById('resultText');
    text.innerHTML = '<p style="color: #999;">🤖 Generating smart timeline and recommendations...</p>';
    resultBox.style.display = 'block';
    document.getElementById('regenerateBtn').style.display = 'inline-block';

    // Store params for regenerate
    currentGenerationParams = {budget, pax, event, eventId, services: selected};

    const res = await fetch('../../api/ai_recommend.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(currentGenerationParams)
    });

    const data = await res.json();
    text.innerHTML = data.html;
    renderUseRecommendationButton();
  }

  // Regenerate recommendation
  async function regenerateRecommendation() {
    if (!currentGenerationParams) {
      generateRecommendation();
      return;
    }

    const text = document.getElementById('resultText');
    text.innerHTML = '<p style="color: #999;">🤖 Generating alternative timeline and recommendations...</p>';

    const payload = {
      ...currentGenerationParams,
      regenerate: true
    };

    const res = await fetch('../../api/ai_recommend.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(payload)
    });

    const data = await res.json();
    text.innerHTML = data.html;
    renderUseRecommendationButton();
  }
</script>

</body>
</html>
