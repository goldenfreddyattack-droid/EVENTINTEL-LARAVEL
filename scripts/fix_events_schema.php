<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=eventintel;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$indexes = $pdo->query("SHOW INDEX FROM events WHERE Key_name = 'PRIMARY'")->fetchAll();
if (empty($indexes)) {
    $pdo->exec('ALTER TABLE events ADD PRIMARY KEY (event_id)');
    echo "Added primary key to events.event_id\n";
}

$pdo->exec('ALTER TABLE events MODIFY event_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
$pdo->exec('ALTER TABLE events AUTO_INCREMENT = 1');

foreach ($pdo->query('DESCRIBE events') as $row) {
    if ($row['Field'] === 'event_id') {
        echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Key'] . ' | ' . $row['Extra'] . PHP_EOL;
    }
}

$pdo->exec("INSERT INTO events (user_id, title, event_type, theme, budget, event_date, event_time, event_end_time, guest_count, venue_name, clothes, catering, host, photographer, soundsnlights, coordinator_package, status, payment_method, payment_status, created_at) VALUES (2, 'temporary_test_event', 'Birthday', 'Test Theme', 0, '2026-12-31', '08:00:00', '13:00:00', 50, 'test venue', NULL, NULL, NULL, NULL, NULL, '', 'planning', 'cash', 'pending', NOW())");
$testId = $pdo->lastInsertId();
$pdo->exec("DELETE FROM events WHERE event_id = $testId");

echo "Test insert succeeded with new event_id: {$testId}\n";
