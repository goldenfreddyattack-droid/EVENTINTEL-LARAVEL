<?php require_once __DIR__ . '/../../config/db.php';
$event_id=intval($_GET['event']??0);
$event=db()->prepare("SELECT * FROM events WHERE event_id=?"); $event->execute([$event_id]); $event=$event->fetch();
if(!$event) die('Event not found');
$inv=db()->prepare("SELECT * FROM invitations WHERE event_id=?"); $inv->execute([$event_id]); $inv=$inv->fetch() ?: ['title'=>"You're invited",'message'=>'Please RSVP','theme_color'=>'#f3c547','font_style'=>'Segoe UI','button_text'=>'Confirm RSVP','background_image'=>null];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $qr='EI-'.$event_id.'-'.strtoupper(bin2hex(random_bytes(4)));
    db()->prepare("INSERT INTO guests(event_id,name,email,phone,qr_code,rsvp_status) VALUES(?,?,?,?,?,'confirmed')")->execute([$event_id,$_POST['name'],$_POST['email'],$_POST['phone'],$qr]);
    echo "<body style='background:#050505;color:white;font-family:Segoe UI;text-align:center;padding:50px'><h1 style='color:#f3c547'>RSVP Confirmed</h1><p>Your QR code:</p><h2>$qr</h2><p>Show this at event entrance.</p></body>"; exit;
}
?><!DOCTYPE html><html><head><title>RSVP</title>
<style>
body {
  margin: 0;
  background: #ffffff;
  color: #222;
  font-family: <?=esc($inv['font_style'])?>;
}

.hero {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-size: cover;
  background-position: center;
}

.card {
  background: rgba(255,255,255,.92);
  border: 1px solid rgba(255,215,0,.25);
  border-radius: 28px;
  padding: 35px;
  width: min(520px,90%);
  text-align: center;
  box-shadow: 0 8px 20px rgba(0,0,0,.08);
}

input {
  width: 100%;
  padding: 14px;
  margin: 8px 0;
  border-radius: 12px;
  border: 1px solid rgba(255,215,0,.15);
  background: #fafafa;
  color: #222;
}

.btn {
  padding: 14px 20px;
  border: 0;
  border-radius: 14px;
  background: <?=esc($inv['theme_color'])?>;
  font-weight: 800;
  color: #111;
  cursor: pointer;
  transition: .3s ease;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(243,197,71,.25);
}
</style>



</head><body><div class="hero" style="background-image:url('/EVENTINTELmayAPI/<?=esc($inv['background_image'])?>')"><form class="card" method="POST"><h1 style="color:<?=esc($inv['theme_color'])?>"><?=esc($inv['title'])?></h1><p><?=nl2br(esc($inv['message']))?></p><input name="name" placeholder="Your name" required><input name="email" placeholder="Email"><input name="phone" placeholder="Phone"><button class="btn"><?=esc($inv['button_text'])?></button></form></div></body></html>