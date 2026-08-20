<?php require_once __DIR__ . '/../../config/db.php'; require_role('client');
$event_id=intval($_GET['id']??0);
if($_SERVER['REQUEST_METHOD']==='POST'){
 header('Content-Type: application/json'); $qr=$_POST['qr']??'';
 $st=db()->prepare("SELECT * FROM guests WHERE event_id=? AND qr_code=?"); $st->execute([$event_id,$qr]); $g=$st->fetch();
 if(!$g) echo json_encode(['ok'=>false,'msg'=>'QR not found']);
 elseif($g['attended']) echo json_encode(['ok'=>false,'msg'=>'Already scanned']);
 else{ db()->prepare("UPDATE guests SET attended=1, scanned_at=NOW(), rsvp_status='confirmed' WHERE guest_id=?")->execute([$g['guest_id']]); echo json_encode(['ok'=>true,'msg'=>'Welcome '.$g['name']]); }
 exit;
}
?><!DOCTYPE html><html><head><title>QR Scanner</title><script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script><style>body{background:#050505;color:white;font-family:Segoe UI;padding:30px}#reader{max-width:500px}.btn{padding:12px 16px;background:#f3c547;border:0;border-radius:10px}</style></head><body><a style="color:#f3c547" href="guests.php?id=<?=$event_id?>">← Guests</a><h1>QR Scanner</h1><div id="reader"></div><input id="manual" placeholder="Manual QR"><button class="btn" onclick="verify(document.getElementById('manual').value)">Verify</button><h2 id="result"></h2><script>
function verify(qr){fetch('scanner.php?id=<?=$event_id?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'qr='+encodeURIComponent(qr)}).then(r=>r.json()).then(d=>{result.textContent=d.msg; result.style.color=d.ok?'#7dffb0':'#ff8080';});}
new Html5QrcodeScanner("reader",{fps:10,qrbox:250}).render(decoded=>verify(decoded));
</script></body></html>