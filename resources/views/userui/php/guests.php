<?php require_once __DIR__ . '/../../config/db.php'; require_role('client');
$event_id=intval($_GET['id']??0);
$ev=db()->prepare("SELECT * FROM events WHERE event_id=? AND user_id=?"); $ev->execute([$event_id,$_SESSION['user_id']]); $event=$ev->fetch(); if(!$event) die('Not found');
if($_SERVER['REQUEST_METHOD']==='POST'){
 $qr='EI-'.$event_id.'-'.strtoupper(bin2hex(random_bytes(4)));
 db()->prepare("INSERT INTO guests(event_id,name,email,phone,qr_code) VALUES(?,?,?,?,?)")->execute([$event_id,$_POST['name'],$_POST['email'],$_POST['phone'],$qr]);
}
$guests=db()->prepare("SELECT * FROM guests WHERE event_id=?"); $guests->execute([$event_id]); $guests=$guests->fetchAll();
?><!DOCTYPE html><html><head><title>Guests</title>
<style>
body{
    background:#f4f6f9;
    color:#222;
    font-family:'Segoe UI',sans-serif;
    padding:30px;
}

a{
    text-decoration:none;
}

h1{
    margin-bottom:25px;
}

.card{
    background:#fff;
    border:1px solid #e5e5e5;
    border-radius:18px;
    padding:20px;
    margin:20px 0;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

form.card{
    display:flex;
    flex-wrap:wrap;
    gap:15px;
}

input{
    flex:1;
    min-width:220px;
    padding:12px;
    border-radius:10px;
    border:1px solid #d9d9d9;
    background:#fff;
    color:#222;
    outline:none;
    font-size:15px;
}

input:focus{
    border-color:#d4a017;
    box-shadow:0 0 0 3px rgba(212,160,23,.15);
}

.btn{
    display:inline-block;
    padding:12px 20px;
    border-radius:10px;
    background:#d4a017;
    color:#fff;
    border:none;
    cursor:pointer;
    font-weight:600;
    transition:.3s;
}

.btn:hover{
    background:#b8860b;
}

.back-link{
    color:#d4a017;
    font-weight:600;
}

.qr-code{
    color:#007bff;
    font-weight:bold;
}

.status-attended{
    color:#28a745;
    font-weight:bold;
}

.status-pending{
    color:#dc3545;
    font-weight:bold;
}
</style>
</head><body><a style="color:#f3c547" href="yourevents.php">← Events</a><h1>Guest QR Management - <?=esc($event['title'])?></h1><form class="card" method="POST"><input name="name" placeholder="Guest name" required><input name="email" placeholder="Email"><input name="phone" placeholder="Phone"><button class="btn">Add Guest + Generate QR</button></form><a class="btn" href="scanner.php?id=<?=$event_id?>">Open QR Scanner</a><?php foreach($guests as $g): ?><div class="card"><b><?=esc($g['name'])?></b><p>QR: <?=esc($g['qr_code'])?></p><p>Status: <?=$g['attended']?'Attended':'Not yet scanned'?></p></div><?php endforeach; ?></body></html>