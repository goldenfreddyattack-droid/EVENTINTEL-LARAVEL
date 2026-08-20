<?php require_once __DIR__ . '/../../config/db.php'; require_role('client'); 

$pdo = db();
$isModal = ($_GET['modal'] ?? '') === 'true';
$styleFilter = trim($_GET['style'] ?? '');

// Fetch church services from supplier_services table
$query = "
    SELECT s.*, u.full_name as supplier_name
    FROM supplier_services s
    JOIN users u ON s.user_id = u.user_id
    WHERE s.category = 'Church'";
$params = [];

// NOTE: style filtering is currently UI/design-only and does not affect backend results.
$query .= "\n    ORDER BY s.rating DESC, s.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$services = $stmt->fetchAll();

$serviceId = trim($_GET['service_id'] ?? '');
$selectedService = null;
$selectedIndex = 0;
foreach ($services as $i => $service) {
  if ((string)($service['service_id'] ?? $service['id'] ?? '') === $serviceId) {
    $selectedService = $service;
    $selectedIndex = $i;
    break;
  }
}

$queryParams = array_filter([
  'modal' => $_GET['modal'] ?? '',
  'from' => $_GET['from'] ?? '',
  'event_date' => $_GET['event_date'] ?? '',
  'event_time' => $_GET['event_time'] ?? '',
  'event_end_time' => $_GET['event_end_time'] ?? '',
  'guest_count' => $_GET['guest_count'] ?? '',
  'style' => $_GET['style'] ?? '',
]);
$preserveQuery = http_build_query($queryParams);

function getChurchImage($index) {
  $images = [
    'https://images.unsplash.com/photo-1527443154391-507e9dc6c5cc?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1508873699372-7ae6c23d1f32?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=900&q=80',
  ];
  return $images[$index % count($images)];
}

function getChurchGallery($index) {
  $images = [
    'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1505765058029-1f6a6d0d8b56?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1502585597120-3e8a49b2f4a5?auto=format&fit=crop&w=900&q=80',
  ];
  return $images[$index % count($images)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventIntel - Select Church</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
  /* Minimal styles copied from other service pages for consistency */
  * { box-sizing: border-box; margin:0; padding:0; font-family: 'Segoe UI', sans-serif; }
  body { background:#f8f8f8; color:#111; }
  .container { max-width:1100px; margin:18px auto; padding:18px; }
  .services-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
  .card { background:#fff;border-radius:14px;padding:12px;border:1px solid rgba(0,0,0,0.06); }
  .card img{ width:100%; height:160px; object-fit:cover; border-radius:10px; }
  .card h3{ margin-top:10px; font-size:16px }
  .card p{ color:#666; font-size:13px }
  .footer { display:flex; justify-content:space-between; align-items:center; margin-top:12px }
  .select-btn, .view-btn { padding:8px 12px; border-radius:10px; background:linear-gradient(135deg,#ffe27d,#d4a017); border:none; cursor:pointer; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Select Church</h1>
    <?php if (empty($services)): ?>
      <p style="color:#777">No church services available yet.</p>
    <?php else: ?>
    <div class="services-grid">
      <?php foreach ($services as $i => $service): ?>
      <div class="card">
        <img src="<?= esc(getChurchImage($i)) ?>" alt="<?= esc($service['name']) ?>">
        <h3><?= esc($service['name'] ?? 'Church') ?></h3>
        <p><?= esc($service['supplier_name'] ?? '') ?></p>
        <div class="footer">
          <button class="select-btn" data-name="<?= esc($service['name']) ?>" data-type="church">Select</button>
          <a class="view-btn" href="?service_id=<?= esc($service['service_id'] ?? $service['id'] ?? '') ?>&<?= $preserveQuery ?>">Details</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
<script>
function selectService(serviceName, serviceType) {
  const params = new URLSearchParams(window.location.search);
  const from = params.get('from');
  const isModal = params.get('modal') === 'true';
  if (from === 'createevent') {
    const message = { type: 'serviceSelected', service: serviceType };
    message[serviceType] = serviceName;
    if (isModal && window.parent && window.parent !== window) {
      window.parent.postMessage(message, '*');
    } else if (window.opener && !window.opener.closed) {
      window.opener.postMessage(message, '*');
      window.close();
    } else {
      const returnUrl = params.get('return') || 'createevent.php';
      window.location.href = returnUrl + '?selected=' + serviceType;
    }
  } else {
    alert(serviceName + ' selected!');
  }
}

document.querySelectorAll('.select-btn').forEach(function(btn){
  btn.addEventListener('click', function(){
    const name = this.dataset.name || 'Church';
    selectService(name, this.dataset.type || 'church');
  });
});
</script>
</body>
</html>
