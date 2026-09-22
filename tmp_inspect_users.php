<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=eventintel;charset=utf8mb4', 'root', '');
$stmt = $pdo->query('SHOW CREATE TABLE users');
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo $row['Create Table'];
