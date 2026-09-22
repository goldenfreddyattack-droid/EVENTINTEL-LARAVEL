<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=eventintel;charset=utf8mb4', 'root', '');

$columns = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_ASSOC);
$keys = $pdo->query("SHOW KEYS FROM users")->fetchAll(PDO::FETCH_ASSOC);
$data = $pdo->query('SELECT user_id, username, email FROM users ORDER BY user_id LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);

var_export($columns);
echo "\n---KEYS---\n";
var_export($keys);
echo "\n---DATA---\n";
var_export($data);
