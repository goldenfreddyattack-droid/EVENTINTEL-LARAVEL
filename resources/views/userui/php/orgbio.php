<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');

$pdo = db();
$coordinator_id = intval($_GET['coordinator_id'] ?? 0);
$coordinator = null;

if ($coordinator_id) {
    $stmt = $pdo->prepare("SELECT user_id, full_name, email, business_name, business_address, phone FROM users WHERE user_id = ? AND role = 'coordinator'");
    $stmt->execute([$coordinator_id]);
    $coordinator = $stmt->fetch();
}

// Coordinator profile (about/services)
$profile = null;
if ($coordinator) {
    try {
        $pStmt = $pdo->prepare("SELECT * FROM coordinator_profile WHERE coordinator_id=?");
        $pStmt->execute([$coordinator_id]);
        $profile = $pStmt->fetch();
    } catch (Exception $e) { $profile = null; }
}

// Packages
$coordinatorPackages = [];
if ($coordinator) {
    try {
        $pkgStmt = $pdo->prepare("SELECT * FROM coordinator_packages WHERE coordinator_id = ? ORDER BY is_featured DESC, price ASC");
        $pkgStmt->execute([$coordinator_id]);
        $coordinatorPackages = $pkgStmt->fetchAll();
    } catch (Exception $e) { error_log('Coordinator packages query error: ' . $e->getMessage()); }
}

// Gallery
$gallery = [];
if ($coordinator) {
    try {
        $gStmt = $pdo->prepare("SELECT * FROM coordinator_gallery WHERE coordinator_id=? ORDER BY created_at DESC");
        $gStmt->execute([$coordinator_id]);
        $gallery = $gStmt->fetchAll();
    } catch (Exception $e) { $gallery = []; }
}

// Reviews
$reviews = [];
$avgRating = 0;
$totalReviews = 0;
if ($coordinator) {
    try {
        $rStmt = $pdo->prepare("SELECT cr.*, u.full_name AS reviewer_name FROM coordinator_reviews cr LEFT JOIN users u ON cr.user_id=u.user_id WHERE cr.coordinator_id=? ORDER BY cr.created_at DESC");
        $rStmt->execute([$coordinator_id]);
        $reviews = $rStmt->fetchAll();
        $totalReviews = count($reviews);
        if ($totalReviews > 0) $avgRating = round(array_sum(array_column($reviews, 'rating')) / $totalReviews, 1);
    } catch (Exception $e) { $reviews = []; }
}

// Handle package booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_coordinator'])) {
    if ($coordinator) {
        try {
            $coordinatorPackage = trim($_POST['coordinator_package'] ?? '');
            $eventStmt = $pdo->prepare(
                "INSERT INTO events (user_id, coordinator, coordinator_status, status, coordinator_package, created_at)
                 VALUES (?, ?, 'pending', 'planning', ?, NOW())"
            );
            $eventStmt->execute([$_SESSION['user_id'], $coordinator['full_name'], $coordinatorPackage]);
            echo json_encode(['success' => true, 'message' => 'Booking confirmed! Event created successfully.']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error creating booking: ' . $e->getMessage()]);
            exit;
        }
    }
}

if (!$coordinator) {
    header('Location: homepage.php');
    exit;
}

$aboutText = $profile['about'] ?? ($coordinator['business_address'] ?? '');
$servicesText = $profile['services'] ?? '';
$servicesList = array_filter(array_map('trim', $servicesText ? explode('|', $servicesText) : []));
if (empty($servicesList)) {
    $servicesList = ['Wedding Planning', 'Corporate Events', 'Birthday Parties', 'Full Coordination', 'On-the-day Coordination'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventIntel - <?= esc($coordinator['full_name']) ?> Portfolio</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
    body { background:#ffffff; color:#222; min-height:100vh; }
    .container { max-width:1400px; margin:auto; padding:6px 48px 40px; }
    .navbar { width:100%; padding:12px 0 24px; display:flex; justify-content:space-between; align-items:center; gap:20px; flex-wrap:wrap; }
    .logo { font-size:26px; font-weight:800; color:#f3c547; letter-spacing:1px; }
    .nav-links { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
    .nav-links button { padding:8px 18px; border-radius:12px; border:1px solid rgba(212,160,23,0.35); background:rgba(255,255,255,0.55); color:#222; font-size:14px; cursor:pointer; transition:0.3s ease; }
    .nav-links button:hover, .nav-links .active { background:linear-gradient(to right,#ffe17a,#d4a017); color:black; box-shadow:0 0 14px rgba(255,215,0,0.12); }
    .profile-btn { width:44px; height:44px; border-radius:50%; border:1px solid rgba(212,160,23,0.25); background:#fff; display:flex; justify-content:center; align-items:center; color:#d4a017; cursor:pointer; }
    .profile { display:grid; grid-template-columns:1fr 2fr; gap:40px; margin-top:20px; }
    .profile-card { background:rgba(255,255,255,.95); border:1px solid rgba(212,160,23,.12); border-radius:28px; padding:30px; text-align:center; box-shadow:0 12px 30px rgba(0,0,0,.08); align-self:flex-start; }
    .profile-img { width:140px; height:140px; border-radius:50%; overflow:hidden; margin:auto; margin-bottom:18px; border:2px solid rgba(212,160,23,.25); }
    .profile-img img { width:100%; height:100%; object-fit:cover; }
    .profile-card h2 { margin-bottom:8px; color:#111; }
    .role { color:#d4a017; font-size:14px; margin-bottom:14px; }
    .rating { color:#d4a017; margin-bottom:20px; }
    .action-buttons { display:flex; flex-direction:column; gap:12px; }
    .btn { padding:12px; border-radius:12px; cursor:pointer; font-weight:700; transition:.3s ease; text-align:center; border:none; }
    .btn-primary { background:linear-gradient(135deg,#ffe27a,#d4a017,#b8860b); color:white; text-decoration:none; }
    .btn-outline, .msg { background:#fff; border:1px solid rgba(212,160,23,.25); color:#d4a017; text-decoration:none; padding:12px; border-radius:12px; font-weight:700; text-align:center; }
    .btn:hover, .msg:hover { transform:translateY(-2px); box-shadow:0 8px 18px rgba(243,197,71,.18); }
    .details { display:flex; flex-direction:column; gap:24px; }
    .section { background:rgba(255,255,255,.95); border:1px solid rgba(212,160,23,.12); border-radius:28px; padding:26px; box-shadow:0 12px 30px rgba(0,0,0,.08); }
    .section h3 { margin-bottom:12px; font-size:20px; color:#111; }
    .section p { color:#666; line-height:1.6; }
    .services { display:flex; flex-wrap:wrap; gap:10px; }
    .service { padding:8px 14px; border-radius:999px; background:rgba(243,197,71,.12); color:#d4a017; font-size:13px; border:1px solid rgba(212,160,23,.15); }

    /* Packages */
    .pkg-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:20px; margin-top:16px; }
    .pkg-card { border:1px solid rgba(212,160,23,.2); border-radius:20px; padding:22px; background:#fff; transition:.3s; position:relative; }
    .pkg-card.featured { border:2px solid #d4a017; box-shadow:0 0 24px rgba(212,160,23,.15); }
    .pkg-card h4 { font-size:18px; margin-bottom:8px; color:#111; }
    .pkg-card .price { font-size:24px; color:#d4a017; font-weight:900; margin-bottom:10px; }
    .pkg-card ul { list-style:none; margin-bottom:14px; }
    .pkg-card li { padding:6px 0; color:#666; font-size:14px; border-bottom:1px solid rgba(212,160,23,.1); }
    .pkg-card li:before { content:'✔ '; color:#d4a017; font-weight:800; }
    .pkg-card button { width:100%; padding:12px; border-radius:12px; border:none; background:linear-gradient(135deg,#fff2ab,#f3c547,#c99208); color:#111; font-weight:800; cursor:pointer; }
    .featured-tag { position:absolute; top:12px; right:12px; background:linear-gradient(135deg,#fff1a8,#f3c547,#c99208); color:#111; font-size:11px; font-weight:800; padding:4px 10px; border-radius:999px; }

    /* Gallery */
    .gallery-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:14px; margin-top:16px; }
    .gallery-item { border-radius:16px; overflow:hidden; border:1px solid rgba(212,160,23,.15); background:#f5f5f5; }
    .gallery-item img, .gallery-item video { width:100%; height:150px; object-fit:cover; display:block; }
    .gallery-item .cap { padding:8px; font-size:12px; color:#666; }

    /* Reviews */
    .review-list { margin-top:16px; display:flex; flex-direction:column; gap:12px; }
    .review-card { border:1px solid rgba(212,160,23,.15); border-radius:16px; padding:16px; background:#fff; }
    .review-card .stars { color:#ffc107; }
    .review-card p { color:#666; font-size:14px; margin-top:8px; }
    .review-card small { color:#aaa; }

    /* Modal */
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; padding:24px; overflow-y:auto; }
    .modal-card { width:min(500px,100%); background:#fff; border-radius:28px; box-shadow:0 18px 60px rgba(0,0,0,.18); overflow:hidden; animation:fadeInUp 220ms ease; max-height:90vh; overflow-y:auto; }
    .modal-header { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:24px 24px 18px; border-bottom:1px solid rgba(221,221,221,.8); }
    .modal-title { font-size:22px; font-weight:800; color:#111; }
    .modal-close { border:none; background:transparent; cursor:pointer; color:#666; font-size:20px; width:38px; height:38px; border-radius:50%; }
    .modal-close:hover { background:rgba(212,160,23,.12); color:#d4a017; }
    .modal-body { padding:0 24px 24px; color:#444; }
    .modal-body p { margin-bottom:16px; color:#555; line-height:1.75; }
    .modal-actions { display:flex; gap:12px; padding:0 24px 24px; }
    .modal-actions button { flex:1; border:none; border-radius:14px; padding:14px 16px; font-weight:700; cursor:pointer; }
    .modal-actions .cancel-btn { background:#f5f5f5; color:#444; border:1px solid #ddd; }
    .modal-actions .confirm-btn { background:linear-gradient(135deg,#ffe27a,#d4a017); color:#111; }
    .package-list { display:flex; flex-direction:column; gap:10px; margin-top:14px; }
    .package-card-modal { border:1px solid rgba(212,160,23,.2); border-radius:14px; padding:14px; cursor:pointer; transition:.2s; }
    .package-card-modal.selected { border-color:#d4a017; background:rgba(243,197,71,.08); box-shadow:0 0 0 2px rgba(212,160,23,.2); }
    .package-card-modal h4 { font-size:16px; margin-bottom:4px; }
    .package-card-modal .price { color:#d4a017; font-weight:800; }
    .package-card-modal .pill { display:inline-block; font-size:10px; padding:3px 8px; border-radius:999px; background:rgba(243,197,71,.15); color:#b07c00; font-weight:700; margin-bottom:6px; }
    .package-card-modal .subtitle { font-size:12px; color:#888; margin-top:4px; }

    /* Custom form */
    .custom-form { display:flex; flex-direction:column; gap:14px; }
    .custom-form label { font-size:12px; text-transform:uppercase; letter-spacing:1px; color:#666; font-weight:700; }
    .custom-form input, .custom-form select, .custom-form textarea { padding:12px 14px; border:1px solid rgba(212,160,23,.2); border-radius:12px; font-size:14px; outline:none; }
    .custom-form textarea { min-height:70px; resize:vertical; }
    .custom-form .row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    @keyframes fadeInUp { from{opacity:0;transform:translateY(12px);} to{opacity:1;transform:translateY(0);} }
    @media(max-width:900px){ .profile{grid-template-columns:1fr;} .custom-form .row{grid-template-columns:1fr;} }
  </style>
</head>
<body>
  <div class="container">
    <div class="navbar">
      <div class="logo">EventIntel</div>
      <div class="nav-links">
        <button onclick="window.location.href='homepage.php'">Home</button>
        <button onclick="window.location.href='createevent.php'">Create Event</button>
        <button onclick="window.location.href='yourevents.php'">Your Events</button>
        <button onclick="window.location.href='recommendation.php'">Recommendations</button>
        <button onclick="window.location.href='newsfeed.php'">Newsfeed</button>
        <button class="profile-btn" onclick="window.location.href='profile.php'"><i class="fas fa-user"></i></button>
      </div>
    </div>

    <div class="profile">
      <div class="profile-card">
        <div class="profile-img"><img src="../images/logo.png" alt="<?= esc($coordinator['full_name']) ?>"></div>
        <h2><?= esc($coordinator['full_name']) ?></h2>
        <div class="role"><?= esc($coordinator['business_name'] ?: 'Professional Event Coordinator') ?></div>
        <div class="rating"><?= str_repeat('★', 5) ?> (<?= $avgRating ? $avgRating : '4.9' ?>)</div>
        <div class="action-buttons">
          <button class="btn btn-primary" onclick="bookCoordinator()">Book a Package</button>
          <button class="btn btn-outline" onclick="openCustomBooking()">Custom Event Booking</button>
          <a class="msg" href="message.php?user_id=<?= $coordinator['user_id'] ?>">Message</a>
        </div>
      </div>

      <div class="details">
        <div class="section">
          <h3>About Me</h3>
          <p><?= nl2br(esc($aboutText ?: 'Professional event coordinator with extensive experience in managing all types of events.')) ?></p>
        </div>

        <div class="section">
          <h3>Services Offered</h3>
          <div class="services">
            <?php foreach ($servicesList as $svc): ?>
              <div class="service"><?= esc($svc) ?></div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="section">
          <h3>Event Packages</h3>
          <?php if (empty($coordinatorPackages)): ?>
            <p>This coordinator has not added package offerings yet.</p>
          <?php else: ?>
          <div class="pkg-grid">
            <?php foreach ($coordinatorPackages as $pkg): ?>
              <div class="pkg-card <?= $pkg['is_featured'] ? 'featured' : '' ?>">
                <?php if ($pkg['is_featured']): ?><span class="featured-tag">FEATURED</span><?php endif; ?>
                <h4><?= esc($pkg['name']) ?></h4>
                <div class="price">₱<?= number_format((float)$pkg['price'], 2) ?></div>
                <?php if ($pkg['description']): ?><p style="font-size:13px;color:#666;margin-bottom:10px;"><?= esc($pkg['description']) ?></p><?php endif; ?>
                <ul>
                  <?php foreach (array_filter(array_map('trim', explode('|', $pkg['inclusions'] ?? ''))) as $inc): ?>
                    <li><?= esc($inc) ?></li>
                  <?php endforeach; ?>
                </ul>
                <button onclick="bookSpecificPackage('<?= esc($pkg['name']) ?>')">Book This Package</button>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <div class="section">
          <h3>Gallery</h3>
          <?php if (empty($gallery)): ?>
            <p>No gallery items yet.</p>
          <?php else: ?>
          <div class="gallery-grid">
            <?php foreach ($gallery as $g): ?>
              <div class="gallery-item">
                <?php if ($g['type'] === 'video'): ?>
                  <video src="<?= esc($g['url']) ?>" controls></video>
                <?php else: ?>
                  <img src="<?= esc($g['url']) ?>" alt="<?= esc($g['caption']) ?>">
                <?php endif; ?>
                <?php if ($g['caption']): ?><div class="cap"><?= esc($g['caption']) ?></div><?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <div class="section">
          <h3>Customer Reviews (<?= $totalReviews ?>)</h3>
          <?php if (empty($reviews)): ?>
            <p>No reviews yet.</p>
          <?php else: ?>
          <div class="review-list">
            <?php foreach ($reviews as $r): ?>
              <div class="review-card">
                <div style="display:flex;justify-content:space-between;">
                  <strong><?= esc($r['reviewer_name'] ?? 'Anonymous') ?></strong>
                  <span class="stars"><?= str_repeat('⭐', (int)$r['rating']) ?></span>
                </div>
                <p><?= esc($r['comment'] ?? '') ?></p>
                <small><?= esc($r['created_at'] ?? '') ?></small>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Package Booking Modal -->
  <div id="confirmModal" class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header">
        <div><div class="modal-title">Book a Package</div><div style="margin-top:6px;color:#666;font-size:14px;">Choose a package and confirm your booking.</div></div>
        <button class="modal-close" onclick="cancelBooking()"><i class="fas fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to book <strong><?= esc($coordinator['full_name']) ?></strong> as your event coordinator?</p>
        <?php if (!empty($coordinatorPackages)): ?>
          <div style="margin-top:14px;font-weight:700;color:#111;">Choose a package</div>
          <div class="package-list">
            <?php foreach ($coordinatorPackages as $pkg): ?>
              <div class="package-card-modal" data-package-name="<?= esc($pkg['name']) ?>" onclick="selectCoordinatorPackage(this)">
                <span class="pill"><?= $pkg['is_featured'] ? 'Featured' : 'Package' ?></span>
                <h4><?= esc($pkg['name']) ?></h4>
                <div class="price">₱<?= number_format((float)$pkg['price'], 2) ?></div>
                <?php if ($pkg['description']): ?><div class="subtitle"><?= esc($pkg['description']) ?></div><?php endif; ?>
                <div class="subtitle"><?= esc(implode(' • ', array_filter(array_map('trim', explode('|', $pkg['inclusions'] ?? ''))))) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div style="margin-top:14px;color:#666;font-size:14px;">This coordinator has not added package offerings yet. Try custom booking instead.</div>
        <?php endif; ?>
      </div>
      <div class="modal-actions">
        <button class="cancel-btn" onclick="cancelBooking()">Cancel</button>
        <button class="confirm-btn" onclick="confirmBooking()">Confirm</button>
      </div>
    </div>
  </div>

  <!-- Custom Booking Modal -->
  <div id="customModal" class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header">
        <div><div class="modal-title">Custom Event Booking</div><div style="margin-top:6px;color:#666;font-size:14px;">Tell the coordinator about your dream event.</div></div>
        <button class="modal-close" onclick="closeCustomBooking()"><i class="fas fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <form id="customForm" class="custom-form">
          <div>
            <label>Event Type *</label>
            <input type="text" name="event_type" placeholder="e.g. Wedding, Birthday, Corporate" required>
          </div>
          <div>
            <label>Event Date *</label>
            <input type="date" name="event_date" required>
          </div>
          <div class="row">
            <div>
              <label>Venue Preference (optional)</label>
              <input type="text" name="venue_preference" placeholder="e.g. Beach resort, Garden">
            </div>
            <div>
              <label>Number of Guests</label>
              <input type="number" name="guest_count" min="1" placeholder="e.g. 100">
            </div>
          </div>
          <div class="row">
            <div>
              <label>Event Theme</label>
              <input type="text" name="theme" placeholder="e.g. Rustic, Garden">
            </div>
            <div>
              <label>Budget (₱)</label>
              <input type="number" name="budget" min="0" placeholder="e.g. 100000">
            </div>
          </div>
          <div>
            <label>Required Services</label>
            <input type="text" name="required_services" placeholder="e.g. Catering, Photography, Host, Decor">
          </div>
          <div>
            <label>Special Requests</label>
            <textarea name="special_requests" placeholder="Any special requests..."></textarea>
          </div>
          <div>
            <label>Additional Notes</label>
            <textarea name="additional_notes" placeholder="Anything else we should know?"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-actions">
        <button class="cancel-btn" onclick="closeCustomBooking()">Cancel</button>
        <button class="confirm-btn" onclick="submitCustomBooking()">Send Request</button>
      </div>
    </div>
  </div>

  <script>
  let selectedCoordinatorPackage = '';

  function bookCoordinator() { document.getElementById('confirmModal').style.display = 'flex'; }
  function cancelBooking() { document.getElementById('confirmModal').style.display = 'none'; }
  function openCustomBooking() { document.getElementById('customModal').style.display = 'flex'; }
  function closeCustomBooking() { document.getElementById('customModal').style.display = 'none'; }

  function selectCoordinatorPackage(card) {
    document.querySelectorAll('.package-card-modal').forEach(n => n.classList.remove('selected'));
    card.classList.add('selected');
    selectedCoordinatorPackage = card.dataset.packageName || '';
  }

  function bookSpecificPackage(name) {
    selectedCoordinatorPackage = name;
    bookCoordinator();
  }

  function confirmBooking() {
    const coordinatorId = <?= $coordinator['user_id'] ?>;
    let packageName = selectedCoordinatorPackage || '';
    if (!packageName) {
      const sel = document.querySelector('.package-card-modal.selected');
      if (sel) packageName = sel.dataset.packageName || '';
    }
    if (!packageName) {
      document.querySelectorAll('.package-card-modal').forEach(n => n.classList.remove('selected'));
      const first = document.querySelector('.package-card-modal');
      if (first) { first.classList.add('selected'); packageName = first.dataset.packageName || ''; }
    }
    if (!packageName) { alert('Please select a package first.'); return; }

    const body = new URLSearchParams();
    body.append('book_coordinator', '1');
    body.append('coordinator_package', packageName);

    fetch('orgbio.php?coordinator_id=' + coordinatorId, {
      method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: body.toString()
    })
    .then(r => r.json())
    .then(data => {
      document.getElementById('confirmModal').style.display = 'none';
      if (data.success) {
        alert('Booking confirmed! ' + '<?= esc($coordinator['full_name']) ?>' + ' has been added to your event.');
        window.location.href = 'yourevents.php';
      } else alert('Error: ' + data.message);
    })
    .catch(e => { alert('Error processing booking: ' + e); document.getElementById('confirmModal').style.display = 'none'; });
  }

  function submitCustomBooking() {
    const form = document.getElementById('customForm');
    const fields = form.querySelectorAll('input, textarea');
    let valid = true;
    fields.forEach(f => { if (f.hasAttribute('required') && !f.value.trim()) valid = false; });
    if (!valid) { alert('Please fill in all required fields.'); return; }

    const body = new URLSearchParams(new FormData(form));
    body.append('coordinator_id', '<?= $coordinator['user_id'] ?>');

    fetch('process_custom_booking.php', {
      method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: body.toString()
    })
    .then(r => r.json())
    .then(data => {
      document.getElementById('customModal').style.display = 'none';
      if (data.success) {
        alert(data.message);
        window.location.href = 'yourevents.php';
      } else alert('Error: ' + data.message);
    })
    .catch(e => { alert('Error sending request: ' + e); document.getElementById('customModal').style.display = 'none'; });
  }
  </script>
</body>
</html>
