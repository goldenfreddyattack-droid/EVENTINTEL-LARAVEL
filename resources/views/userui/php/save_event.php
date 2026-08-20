<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: createevent.php');
  exit;
}

function hasColumn($pdo, $table, $column) {
  $check = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE :column");
  $check->execute([':column' => $column]);
  return $check->rowCount() > 0;
}

function normalizeTimeValue($value) {
  if ($value === null || $value === '') {
    return null;
  }
  $value = trim((string) $value);
  if (strlen($value) === 5) {
    $value .= ':00';
  }
  $dt = DateTime::createFromFormat('H:i:s', $value);
  if ($dt) {
    return $dt->format('H:i:s');
  }
  $dt = DateTime::createFromFormat('H:i', $value);
  if ($dt) {
    return $dt->format('H:i:s');
  }
  return $value;
}

function timeToSeconds($value) {
  $normalized = normalizeTimeValue($value);
  if ($normalized === null) {
    return null;
  }
  $parts = explode(':', $normalized);
  if (count($parts) < 2) {
    return null;
  }
  return ((int) $parts[0] * 3600) + ((int) $parts[1] * 60) + ((int) ($parts[2] ?? 0));
}

function getVenueCapacity($pdo, $venueName) {
  $venueName = trim((string) $venueName);
  if ($venueName === '') {
    return null;
  }

  $stmt = $pdo->prepare("SELECT capacity, name FROM supplier_services WHERE category = 'Venue' AND LOWER(name) LIKE LOWER(?) LIMIT 1");
  $stmt->execute(['%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $venueName) . '%']);
  $service = $stmt->fetch();

  if ($service && !empty($service['capacity'])) {
    return intval($service['capacity']);
  }

  $fallbackMap = [
    'Casa de Alvin' => 300,
    'LIOS Resort and Events Place' => 250,
    'Casa de Consuelo Private Resort and Events Place' => 220,
    'La Tehillah Private Resort and Events Place' => 200,
  ];

  foreach ($fallbackMap as $knownVenue => $fallbackCapacity) {
    if (stripos($venueName, $knownVenue) !== false) {
      return $fallbackCapacity;
    }
  }

  return 200;
}

function isVenueAvailable($pdo, $venueName, $date, $startTime, $endTime) {
  if ($venueName === '' || $date === '' || $startTime === '') {
    return false;
  }

  $hasEndTimeColumn = hasColumn($pdo, 'events', 'event_end_time');
  $selectColumns = $hasEndTimeColumn ? 'event_time, event_end_time' : 'event_time';
  $stmt = $pdo->prepare("SELECT $selectColumns FROM events WHERE venue_name = ? AND event_date = ? AND status <> 'cancelled'");
  $stmt->execute([$venueName, $date]);
  $events = $stmt->fetchAll();

  $requestedStart = timeToSeconds($startTime);
  $requestedEnd = !empty($endTime) ? timeToSeconds($endTime) : $requestedStart;

  if ($requestedStart === null || $requestedEnd === null) {
    return false;
  }

  foreach ($events as $event) {
    $existingStart = timeToSeconds($event['event_time']);
    $existingEnd = $hasEndTimeColumn && !empty($event['event_end_time']) ? timeToSeconds($event['event_end_time']) : $existingStart;

    if ($existingStart !== null && $existingEnd !== null) {
      if ($requestedStart < $existingEnd && $requestedEnd > $existingStart) {
        return false;
      }
    }
  }

  return true;
}

$event_type = trim($_POST['event_type'] ?? '');
if ($event_type === 'Others' && trim($_POST['other_event_type'] ?? '') !== '') {
  $event_type = trim($_POST['other_event_type']);
}

$date = trim($_POST['event_date'] ?? '');
$time = trim($_POST['event_time'] ?? '');
$event_end_time = trim($_POST['event_end_time'] ?? '');
$guest_count = max(0, intval($_POST['guest_count'] ?? 0));
$services = $_POST['services'] ?? [];
$venue_name = trim($_POST['venue'] ?? $_POST['venue_name'] ?? '');
if (strtolower($venue_name) === 'null') {
  $venue_name = '';
}
if ($venue_name === '' && isset($_COOKIE['event_selection_venue'])) {
  $venue_name = trim($_COOKIE['event_selection_venue']);
}
if ($venue_name === '' && isset($_COOKIE['event_selection_venue_name'])) {
  $venue_name = trim($_COOKIE['event_selection_venue_name']);
}
if (strtolower($venue_name) === 'null') {
  $venue_name = '';
}
$clothes = trim($_POST['clothes'] ?? '');
if ($clothes === '' && in_array('clothes', $services, true) && isset($_COOKIE['event_selection_clothes'])) {
  $clothes = trim($_COOKIE['event_selection_clothes']);
}
$catering = trim($_POST['catering'] ?? '');
if ($catering === '' && in_array('catering', $services, true) && isset($_COOKIE['event_selection_catering'])) {
  $catering = trim($_COOKIE['event_selection_catering']);
}
$host = trim($_POST['host'] ?? '');
if ($host === '' && in_array('host', $services, true) && isset($_COOKIE['event_selection_host'])) {
  $host = trim($_COOKIE['event_selection_host']);
}
$photographer = trim($_POST['photographer'] ?? '');
if ($photographer === '' && in_array('photographer', $services, true) && isset($_COOKIE['event_selection_photographer'])) {
  $photographer = trim($_COOKIE['event_selection_photographer']);
}
$sounds_lights = trim($_POST['sounds_lights'] ?? '');
if ($sounds_lights === '' && in_array('sounds_lights', $services, true) && isset($_COOKIE['event_selection_sounds_lights'])) {
  $sounds_lights = trim($_COOKIE['event_selection_sounds_lights']);
}
$payment_method = trim($_POST['payment_method'] ?? 'cash');
$theme = trim($_POST['theme'] ?? '');
if ($theme === '') {
  $theme = null;
}
$budget = $_POST['budget'] ?? $_POST['event_budget'] ?? '';
$budget = ($budget !== '' && is_numeric($budget)) ? floatval($budget) : null;
$title = ($event_type !== '' ? $event_type : 'Event') . ' Event';

$errors = [];
if ($event_type === '') {
  $errors[] = 'Please select an event type.';
}
if ($date === '') {
  $errors[] = 'Please select an event date.';
}
if ($time === '') {
  $errors[] = 'Please select a start time.';
}
if ($event_end_time === '') {
  $event_end_time = $time;
}
if ($guest_count <= 0) {
  $errors[] = 'Guest count must be greater than zero.';
}
if ($venue_name === '') {
  $errors[] = 'Please select a venue before continuing.';
}

if (!empty($errors)) {
  header('Location: createevent.php?error=' . urlencode(implode(' ', $errors)));
  exit;
}

$pdo = db();
$hasEndTimeColumn = hasColumn($pdo, 'events', 'event_end_time');

$venueCapacity = getVenueCapacity($pdo, $venue_name);
if ($venueCapacity === null) {
  $venueCapacity = 200;
}

if ($guest_count > $venueCapacity) {
  header('Location: createevent.php?error=' . urlencode('The selected venue cannot accommodate that many guests.'));
  exit;
}

if (!isVenueAvailable($pdo, $venue_name, $date, $time, $event_end_time)) {
  header('Location: createevent.php?error=' . urlencode('The selected venue is not available at that date and time.'));
  exit;
}

$fields = ['user_id','title','event_type','theme','budget','event_date','event_time','guest_count','venue_name','clothes','catering','host','photographer','soundsnlights','status','payment_method','payment_status'];
$values = [
  $_SESSION['user_id'],
  $title,
  $event_type,
  $theme,
  $budget,
  $date,
  $time,
  $guest_count,
  $venue_name,
  $clothes,
  $catering,
  $host,
  $photographer,
  $sounds_lights,
  'planning',
  $payment_method,
  'pending'
];

if ($hasEndTimeColumn) {
  array_splice($fields, 5, 0, 'event_end_time');
  array_splice($values, 5, 0, $event_end_time);
}

$sql = 'INSERT INTO events (' . implode(',', $fields) . ') VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')';
$stmt = $pdo->prepare($sql);
$stmt->execute($values);

$event_id = $pdo->lastInsertId();

$stmt = $pdo->prepare("INSERT INTO event_services (event_id,service_name) VALUES (?,?)");
foreach ($services as $s) {
  $stmt->execute([$event_id, preg_replace('/[^a-zA-Z0-9_ -]/', '', $s)]);
}

/* Generate default editable invitation template */
$inv = $pdo->prepare("INSERT INTO invitations (event_id,title,message,theme_color,button_text) VALUES (?,?,?,?,?)");
$inv->execute([$event_id, "You're Invited to $title", "Please confirm your attendance.", "#f3c547", "Confirm RSVP"]);

header("Location: confirmation.php?event_id=$event_id");
exit;
?>
