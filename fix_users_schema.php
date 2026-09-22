<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=eventintel;charset=utf8mb4', 'root', '');

$pk = $pdo->query("SHOW KEYS FROM users WHERE Key_name = 'PRIMARY'")->fetch(PDO::FETCH_ASSOC);
if ($pk) {
    $pdo->exec('ALTER TABLE users DROP PRIMARY KEY');
}

$pdo->exec('ALTER TABLE users ADD PRIMARY KEY (user_id)');
$pdo->exec('ALTER TABLE users MODIFY user_id INT NOT NULL AUTO_INCREMENT');

$row = $pdo->query("SHOW CREATE TABLE users")->fetch(PDO::FETCH_ASSOC);
echo $row['Create Table'];
