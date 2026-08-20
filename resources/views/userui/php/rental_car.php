<?php require_once __DIR__ . '/../../config/db.php'; require_role('client'); 

$pdo = db();
$isModal = ($_GET['modal'] ?? '') === 'true';
$styleFilter = trim($_GET['style'] ?? '');

// Fetch rental car services from supplier_services table
$query = "
    SELECT s.*, u.full_name as supplier_name
    FROM supplier_services s
    JOIN users u ON s.user_id = u.user_id
    WHERE s.category = 'Rental Car'";
$params = [];

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

function getCarImage($index) {
  $images = [
    'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1502870337652-7c7d3c3d0f4a?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=900&q=80',
  ];
  return $images[$index % count($images)];
}

function getCarGallery($index) {
  $images = [
    'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=900&q=80',
  ];
  return $images[$index % count($images)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventIntel - Select Rental Car</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
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
    <h1>Select Rental Car</h1>
    <?php if (empty($services)): ?>
      <p style="color:#777">No rental car services available yet.</p>
    <?php else: ?>
    <div class="services-grid">
      <?php foreach ($services as $i => $service): ?>
      <div class="card">
        <img src="<?= esc(getCarImage($i)) ?>" alt="<?= esc($service['name']) ?>">
        <h3><?= esc($service['name'] ?? 'Rental Car') ?></h3>
        <p><?= esc($service['supplier_name'] ?? '') ?></p>
        <div class="footer">
          <button class="select-btn" data-name="<?= esc($service['name']) ?>" data-type="rental_car">Select</button>
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
    const name = this.dataset.name || 'Rental Car';
    selectService(name, this.dataset.type || 'rental_car');
  });
});
</script>
</body>
</html>
