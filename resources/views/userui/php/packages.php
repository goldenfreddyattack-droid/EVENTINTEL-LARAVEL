<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');
$pdo = db();

$eventType = trim($_GET['event_type'] ?? ($_SESSION['selected_event_type'] ?? ''));
$selectedBudget = (int) ($_GET['budget'] ?? 0);

// Fallback to recommendation prefill if set
if ($eventType === '' && isset($_COOKIE['event_recommendation_prefill'])) {
    try {
        $prefill = json_decode($_COOKIE['event_recommendation_prefill'], true);
        $eventType = $prefill['eventType'] ?? '';
    } catch (Exception $e) {}
}

// Tiered package definitions per event type
$eventKey = strtolower(trim($eventType));
$packages = [
  'birthday' => [
    ['tier' => 'Basic', 'name' => 'Basic Birthday', 'price' => 25000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'A simple and affordable celebration package'],
    ['tier' => 'Standard', 'name' => 'Standard Birthday', 'price' => 50000, 'services' => ['venue', 'catering', 'host', 'sounds_lights'], 'desc' => 'Most popular choice with sounds & lights'],
    ['tier' => 'Premium', 'name' => 'Premium Birthday', 'price' => 85000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer', 'clothes'], 'desc' => 'Complete celebration with full styling'],
  ],
  'debut' => [
    ['tier' => 'Basic', 'name' => 'Basic Debut', 'price' => 40000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'A classic debut celebration package with 18 roses setup'],
    ['tier' => 'Standard', 'name' => 'Standard Debut', 'price' => 80000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'Includes debut production and photo coverage'],
    ['tier' => 'Premium', 'name' => 'Premium Debut', 'price' => 150000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer', 'clothes'], 'desc' => 'Full debut production with styling and entourage'],
  ],
  'wedding' => [
    ['tier' => 'Basic', 'name' => 'Basic Wedding', 'price' => 60000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'An intimate wedding essentials package'],
    ['tier' => 'Standard', 'name' => 'Standard Wedding', 'price' => 120000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'Balanced package for a memorable day'],
    ['tier' => 'Premium', 'name' => 'Premium Wedding', 'price' => 250000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer', 'clothes', 'church', 'rental_car'], 'desc' => 'Everything you need for a grand wedding'],
  ],
  'anniversary' => [
    ['tier' => 'Basic', 'name' => 'Basic Anniversary', 'price' => 30000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'Celebrate your milestone simply'],
    ['tier' => 'Standard', 'name' => 'Standard Anniversary', 'price' => 60000, 'services' => ['venue', 'catering', 'host', 'photographer'], 'desc' => 'Include photography to capture the moment'],
    ['tier' => 'Premium', 'name' => 'Premium Anniversary', 'price' => 100000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'A premium celebration for your special day'],
  ],
  'christening' => [
    ['tier' => 'Basic', 'name' => 'Basic Christening', 'price' => 20000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'Essential services for a blessed day'],
    ['tier' => 'Standard', 'name' => 'Standard Christening', 'price' => 40000, 'services' => ['venue', 'catering', 'host', 'photographer'], 'desc' => 'Adds photo coverage for memories'],
    ['tier' => 'Premium', 'name' => 'Premium Christening', 'price' => 70000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'Complete celebration package'],
  ],
  'gender reveal' => [
    ['tier' => 'Basic', 'name' => 'Basic Reveal', 'price' => 15000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'Simple reveal celebration'],
    ['tier' => 'Standard', 'name' => 'Standard Reveal', 'price' => 35000, 'services' => ['venue', 'catering', 'host', 'photographer'], 'desc' => 'Capture the big moment'],
    ['tier' => 'Premium', 'name' => 'Premium Reveal', 'price' => 60000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'A full surprise party production'],
  ],
  'reunion' => [
    ['tier' => 'Basic', 'name' => 'Basic Reunion', 'price' => 20000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'Great for intimate family reunions'],
    ['tier' => 'Standard', 'name' => 'Standard Reunion', 'price' => 45000, 'services' => ['venue', 'catering', 'host', 'photographer'], 'desc' => 'Add photography to preserve memories'],
    ['tier' => 'Premium', 'name' => 'Premium Reunion', 'price' => 80000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'A grand family gathering experience'],
  ],
];
if (!isset($packages[$eventKey])) {
  $packages[$eventKey] = [
    ['tier' => 'Basic', 'name' => 'Basic Package', 'price' => 25000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'Essential event services'],
    ['tier' => 'Standard', 'name' => 'Standard Package', 'price' => 50000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'Popular balanced choice'],
    ['tier' => 'Premium', 'name' => 'Premium Package', 'price' => 90000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer', 'clothes'], 'desc' => 'Complete event experience'],
  ];
}
$activePackages = $packages[$eventKey];

// Budget distribution percentages
$allocation = [
  'Venue' => 0.30,
  'Catering/Food' => 0.35,
  'Host/MC' => 0.08,
  'Photographer' => 0.10,
  'Sounds & Lights' => 0.08,
  'Clothing/Attire' => 0.05,
  'Decorations' => 0.04,
];

$serviceNames = [
  'venue' => 'Venue',
  'catering' => 'Catering/Food',
  'host' => 'Host/MC',
  'sounds_lights' => 'Sounds & Lights',
  'photographer' => 'Photographer',
  'clothes' => 'Clothing/Attire',
  'church' => 'Church',
  'rental_car' => 'Rental Car',
];
$serviceIcons = [
  'venue' => 'fa-location-dot',
  'catering' => 'fa-utensils',
  'host' => 'fa-microphone',
  'sounds_lights' => 'fa-lightbulb',
  'photographer' => 'fa-camera',
  'clothes' => 'fa-shirt',
  'church' => 'fa-church',
  'rental_car' => 'fa-car',
];

// Fetch real supplier services to show starting prices
$servicesRaw = $pdo->query("SELECT category, MIN(price) as min_price, name FROM supplier_services WHERE price > 0 GROUP BY category, name ORDER BY category")->fetchAll();
$minByCategory = [];
foreach ($servicesRaw as $svc) {
  $cat = strtolower($svc['category']);
  $key = match($cat) {
    'venue' => 'venue',
    'catering' => 'catering',
    'host' => 'host',
    'photographer' => 'photographer',
    'sounds & lights' => 'sounds_lights',
    'clothing' => 'clothes',
    default => null,
  };
  if ($key && (!isset($minByCategory[$key]) || (float)$svc['min_price'] < (float)$minByCategory[$key])) {
    $minByCategory[$key] = (float)$svc['min_price'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EventIntel - Packages</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{
  background:#ffffff;
  color:#222;
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
  border: 1px solid rgba(255, 215, 0, 0.35);
  background: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
}

.profile-btn i {
  color: #f3c547;
}

/* CONTENT */
.container{max-width:1300px;margin:auto;padding:20px 50px 60px;}
h1{font-size:42px;margin-bottom:8px;}
.subtitle{color:#777;margin-bottom:30px;}

.event-type-bar {
  display:flex;
  align-items:center;
  gap:12px;
  flex-wrap:wrap;
  margin-bottom:30px;
  padding:16px 20px;
  background:rgba(243,197,71,.08);
  border:1px solid rgba(243,197,71,.25);
  border-radius:18px;
}
.event-type-bar label{font-size:13px;font-weight:700;color:#666;}
.event-type-bar select{
  padding:12px 16px;
  border-radius:12px;
  border:1px solid rgba(212,160,23,.25);
  background:#fff;
  color:#222;
  min-width:220px;
  font-size:14px;
}

.packages{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;margin-bottom:40px;}

.package-card{
  position:relative;
  background:#fff;
  border:1px solid rgba(255,215,0,.15);
  border-radius:24px;
  padding:28px 24px;
  transition:.3s;
  box-shadow:0 12px 30px rgba(0,0,0,.06);
  display:flex;
  flex-direction:column;
}
.package-card:hover{transform:translateY(-6px);border-color:rgba(212,160,23,.4);box-shadow:0 18px 40px rgba(243,197,71,.16);}

.package-card.recommended{
  border:2px solid #d4a017;
  background:linear-gradient(180deg,#fffdf4,#ffffff);
  box-shadow:0 18px 45px rgba(212,160,23,.2);
}

.badge{
  position:absolute;
  top:-14px;
  left:50%;
  transform:translateX(-50%);
  background:linear-gradient(135deg,#ffe27d,#c78f08);
  color:#111;
  font-size:11px;
  font-weight:800;
  padding:6px 16px;
  border-radius:999px;
  letter-spacing:.5px;
  box-shadow:0 6px 16px rgba(243,197,71,.35);
}

.tier-tag{font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#d4a017;margin-bottom:6px;}
.package-card h3{font-size:22px;margin-bottom:10px;color:#111;}
.package-desc{color:#777;font-size:13px;line-height:1.6;margin-bottom:16px;min-height:42px;}
.price{font-size:32px;font-weight:900;color:#d4a017;margin-bottom:18px;}
.price small{font-size:14px;color:#999;font-weight:400;}

.service-list{margin-bottom:22px;flex:1;}
.service-list li{
  list-style:none;
  display:flex;
  align-items:center;
  gap:10px;
  padding:9px 12px;
  border-radius:12px;
  margin-bottom:6px;
  background:rgba(212,160,23,.06);
  border:1px solid rgba(212,160,23,.1);
  font-size:13px;
  color:#333;
}
.service-list li i{color:#d4a017;width:18px;text-align:center;}
.service-list .missing{opacity:.45;background:rgba(0,0,0,.03);border-color:rgba(0,0,0,.05);}
.service-list .missing i{color:#aaa;}

.price-row{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:10px 0;
  border-top:1px dashed rgba(212,160,23,.25);
  font-size:13px;
  color:#666;
}
.price-row span:last-child{font-weight:800;color:#d4a017;}

.choose-btn{
  width:100%;
  padding:14px;
  border:none;
  border-radius:14px;
  font-weight:800;
  font-size:15px;
  cursor:pointer;
  transition:.3s;
  background:linear-gradient(135deg,#fff2ab,#f3c547,#c99208);
  color:#111;
  margin-top:16px;
}
.choose-btn:hover{transform:translateY(-2px);box-shadow:0 10px 22px rgba(243,197,71,.3);}
.choose-btn.outline{
  background:#fff;
  border:1px solid rgba(212,160,23,.35);
  color:#d4a017;
}

/* BUDGET ALLOCATION SECTION */
.budget-section{
  margin-top:10px;
  padding:32px;
  border-radius:26px;
  border:1px solid rgba(212,160,23,.18);
  background:rgba(255,255,255,.85);
  box-shadow:0 12px 30px rgba(0,0,0,.05);
}
.budget-section h2{font-size:24px;margin-bottom:6px;color:#111;}
.budget-section>p{color:#777;margin-bottom:24px;}

.budget-controls{
  display:flex;
  gap:16px;
  align-items:flex-end;
  margin-bottom:28px;
  flex-wrap:wrap;
}
.budget-controls .input-group{display:flex;flex-direction:column;gap:6px;}
.budget-controls label{font-size:12px;font-weight:700;color:#666;letter-spacing:.5px;}
.budget-controls input,.budget-controls select{
  padding:13px 16px;
  border-radius:12px;
  border:1px solid rgba(212,160,23,.2);
  background:#fff;
  color:#222;
  font-size:14px;
  min-width:200px;
  outline:none;
}
.budget-controls input:focus,.budget-controls select:focus{border-color:#d4a017;}

.alloc-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;margin-bottom:26px;}
.alloc-item{
  background:#fff;
  border:1px solid rgba(212,160,23,.12);
  border-radius:16px;
  padding:18px;
}
.alloc-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;}
.alloc-head h4{font-size:14px;color:#333;}
.alloc-head .percent{font-size:13px;font-weight:800;color:#d4a017;}
.bar{height:10px;border-radius:999px;background:rgba(212,160,23,.12);overflow:hidden;}
.bar-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,#ffe27d,#d4a017);transition:width .4s ease;}
.alloc-amount{font-size:16px;font-weight:800;color:#111;margin-top:8px;}

.total-row{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:18px 22px;
  border-radius:16px;
  background:linear-gradient(135deg,#fff7dc,#ffffff);
  border:1px solid rgba(212,160,23,.3);
}
.total-row .label{font-size:14px;color:#666;}
.total-row .amount{font-size:28px;font-weight:900;color:#d4a017;}

.budget-note{
  margin-top:20px;
  padding:14px 18px;
  border-radius:14px;
  background:rgba(243,197,71,.08);
  border:1px solid rgba(243,197,71,.22);
  color:#7a5b00;
  font-size:13px;
  line-height:1.7;
}

@media (max-width:900px){
  .packages{grid-template-columns:1fr;}
  .container{padding:20px 22px 40px;}
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
    <button onclick="window.location.href='recommendation.php'">Recommendations</button>
    <button onclick="window.location.href='newsfeed.php'">Newsfeed</button>
    <button class="profile-btn" type="button" aria-label="Profile" title="Profile" onclick="window.location.href='profile.php'"><i class="fas fa-user"></i></button>
  </div>
</div>

<div class="container">

<h1>Package View</h1>
<p class="subtitle">Choose a package that fits your event and see exactly how your budget is distributed.</p>

<div class="event-type-bar">
  <label for="eventTypeSelect">EVENT TYPE</label>
  <select id="eventTypeSelect" onchange="changeEventType()">
    <option value="Birthday" <?= $eventKey==='birthday'?'selected':'' ?>>Birthday</option>
    <option value="Debut" <?= $eventKey==='debut'?'selected':'' ?>>Debut</option>
    <option value="Wedding" <?= $eventKey==='wedding'?'selected':'' ?>>Wedding</option>
    <option value="Anniversary" <?= $eventKey==='anniversary'?'selected':'' ?>>Anniversary</option>
    <option value="Christening" <?= $eventKey==='christening'?'selected':'' ?>>Christening</option>
    <option value="Gender Reveal" <?= $eventKey==='gender reveal'?'selected':'' ?>>Gender Reveal</option>
    <option value="Reunion" <?= $eventKey==='reunion'?'selected':'' ?>>Reunion</option>
  </select>
  <button class="choose-btn outline" style="margin:0;padding:12px 20px;font-size:13px;" onclick="window.location.href='createevent.php'">← Back to Create Event</button>
</div>

<div class="packages">
  <?php foreach ($activePackages as $i => $pkg): ?>
  <div class="package-card <?= $i === 1 ? 'recommended' : '' ?>">
    <?php if ($i === 1): ?><span class="badge">★ MOST POPULAR</span><?php endif; ?>
    <div class="tier-tag"><?= esc($pkg['tier']) ?> Tier</div>
    <h3><?= esc($pkg['name']) ?></h3>
    <p class="package-desc"><?= esc($pkg['desc']) ?></p>
    <div class="price">₱<?= number_format($pkg['price']) ?> <small>total</small></div>
    <ul class="service-list">
      <?php
      $keys = ['venue','catering','host','sounds_lights','photographer','clothes','church','rental_car'];
      foreach ($keys as $sk):
        $included = in_array($sk, $pkg['services'], true);
        $startPrice = isset($minByCategory[$sk]) ? 'from ₱' . number_format($minByCategory[$sk]) : '';
      ?>
      <li class="<?= $included ? '' : 'missing' ?>">
        <i class="fa-solid <?= $serviceIcons[$sk] ?>"></i>
        <span><?= $serviceNames[$sk] ?> <?= $included && $startPrice ? '<small style="margin-left:auto;color:#d4a017;">'.$startPrice.'</small>' : '' ?></span>
      </li>
      <?php endforeach; ?>
    </ul>
    <div class="price-row"><span>Package total</span><span>₱<?= number_format($pkg['price']) ?></span></div>
    <button class="choose-btn" onclick="choosePackage('<?= esc($pkg['name']) ?>', <?= (int)$pkg['price'] ?>, '<?= esc($eventType) ?>', <?= htmlspecialchars(json_encode($pkg['services']), ENT_QUOTES, 'UTF-8') ?>)">Choose This Package</button>
  </div>
  <?php endforeach; ?>
</div>

<div class="budget-section">
  <h2>💰 Budget Recommendations Distribution</h2>
  <p>Enter your total budget and the tool will show you how much to allocate to each service based on industry-standard percentages.</p>

  <div class="budget-controls">
    <div class="input-group">
      <label>TOTAL BUDGET (₱)</label>
      <input type="number" id="budgetInput" value="<?= $selectedBudget ?: 50000 ?>" min="1000">
    </div>
    <div class="input-group">
      <label>NUMBER OF GUESTS</label>
      <input type="number" id="paxInput" value="100" min="1">
    </div>
    <button class="choose-btn" style="margin:0;padding:13px 24px;" onclick="updateAllocation()">Compute Distribution</button>
  </div>

  <div class="alloc-grid" id="allocGrid"></div>

  <div class="total-row">
    <div class="label">Total allocated</div>
    <div class="amount">₱<span id="allocTotal">0</span></div>
  </div>

  <div class="budget-note">
    <i class="fa-solid fa-lightbulb" style="margin-right:8px;"></i>
    <strong>Tip:</strong> The distribution follows common event-industry benchmarks. Adjust your priorities based on what matters most — for example, a wedding usually weighs venue & catering higher, while a birthday party may balance food, entertainment, and styling more evenly. Per-person cost guides stay within reach of your total budget.
  </div>
</div>

</div>

<script>
const ALLOCATION = <?= json_encode($allocation) ?>;
const SERVICE_NAMES = <?= json_encode($serviceNames) ?>;
const SERVICE_ICONS = <?= json_encode($serviceIcons) ?>;

function changeEventType() {
  const type = document.getElementById('eventTypeSelect').value;
  window.location.href = 'packages.php?event_type=' + encodeURIComponent(type);
}

function choosePackage(name, price, eventType, services) {
  const svcs = Array.isArray(services) ? services.map(String).map(s => s.trim()).filter(Boolean) : [];
  const payload = {
    selectedPackage: name,
    budget: price,
    eventType: eventType,
    services: svcs
  };
  try {
    sessionStorage.setItem('event_package_selection', JSON.stringify(payload));
    document.cookie = 'event_package_name=' + encodeURIComponent(name) + '; path=/; max-age=3600';
    document.cookie = 'event_budget=' + price + '; path=/; max-age=3600';
    document.cookie = 'event_package_services=' + encodeURIComponent(svcs.join(',')) + '; path=/; max-age=3600';
  } catch (err) { console.warn(err); }
  window.location.href = 'createevent.php?from=package&budget=' + price + '&event_type=' + encodeURIComponent(eventType) + '&services=' + encodeURIComponent(svcs.join(','));
}

function updateAllocation() {
  const budget = parseFloat(document.getElementById('budgetInput').value) || 0;
  const pax = parseInt(document.getElementById('paxInput').value) || 1;
  const grid = document.getElementById('allocGrid');
  grid.innerHTML = '';
  if (budget <= 0) return;

  let total = 0;
  Object.keys(ALLOCATION).forEach(function(cat) {
    const share = ALLOCATION[cat];
    const amount = Math.round(budget * share);
    total += amount;
    const item = document.createElement('div');
    item.className = 'alloc-item';
    item.innerHTML =
      '<div class="alloc-head"><h4>' + cat + '</h4><span class="percent">' + Math.round(share * 100) + '%</span></div>' +
      '<div class="bar"><div class="bar-fill" style="width:' + (share * 100) + '%;"></div></div>' +
      '<div class="alloc-amount">₱' + amount.toLocaleString() + '</div>' +
      '<div style="font-size:11px;color:#999;margin-top:6px;">~ ₱' + Math.round(amount / pax).toLocaleString() + ' / guest</div>';
    grid.appendChild(item);
  });
  document.getElementById('allocTotal').textContent = total.toLocaleString();
}

// Init
updateAllocation();
window.addEventListener('DOMContentLoaded', updateAllocation);
</script>

</body>
</html>

