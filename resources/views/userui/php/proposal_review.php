<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');

$pdo = db();
$clientId = $_SESSION['user_id'];
$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$proposal = null;
$event = null;
$customRequest = null;

// Load event + proposal
if ($eventId) {
    $evStmt = $pdo->prepare("SELECT * FROM events WHERE event_id=? AND user_id=?");
    $evStmt->execute([$eventId, $clientId]);
    $event = $evStmt->fetch();

    if ($event) {
        $prStmt = $pdo->prepare("SELECT * FROM coordinator_proposals WHERE event_id=? AND client_id=? ORDER BY proposal_id DESC LIMIT 1");
        $prStmt->execute([$eventId, $clientId]);
        $proposal = $prStmt->fetch();

        $csStmt = $pdo->prepare("SELECT * FROM custom_event_requests WHERE event_id=? AND client_id=? ORDER BY request_id DESC LIMIT 1");
        $csStmt->execute([$eventId, $clientId]);
        $customRequest = $csStmt->fetch();
    }
}

// Handle actions (accept / reject / request revision)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $proposal) {
    $action = $_POST['proposal_action'] ?? '';
    $comments = trim($_POST['comments'] ?? '');

    if ($action === 'accept') {
        $pdo->prepare("UPDATE coordinator_proposals SET status='accepted', client_comments=? WHERE proposal_id=? AND client_id=?")
            ->execute([$comments ?: null, $proposal['proposal_id'], $clientId]);
        // Sync event status
        $pdo->prepare("UPDATE events SET coordinator_status='proposal_accepted', payment_status='pending' WHERE event_id=? AND user_id=?")
            ->execute([$eventId, $clientId]);
        $msg = 'Proposal accepted! Your event is now pending payment.';
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE coordinator_proposals SET status='rejected', client_comments=? WHERE proposal_id=? AND client_id=?")
            ->execute([$comments ?: null, $proposal['proposal_id'], $clientId]);
        $pdo->prepare("UPDATE events SET coordinator_status='proposal_declined' WHERE event_id=? AND user_id=?")
            ->execute([$eventId, $clientId]);
        $msg = 'Proposal rejected. The coordinator will be notified.';
    } elseif ($action === 'revision') {
        if ($comments === '') {
            $msg = 'Please provide feedback/comments when requesting a revision.';
            $err = true;
        } else {
            $pdo->prepare("UPDATE coordinator_proposals SET status='revision_requested', client_comments=? WHERE proposal_id=? AND client_id=?")
                ->execute([$comments, $proposal['proposal_id'], $clientId]);
            $pdo->prepare("UPDATE events SET coordinator_status='revision_requested' WHERE event_id=? AND user_id=?")
                ->execute([$eventId, $clientId]);
            $msg = 'Revision requested. Your feedback has been sent to the coordinator.';
        }
    }

    // Reload proposal after update
    $prStmt = $pdo->prepare("SELECT * FROM coordinator_proposals WHERE event_id=? AND client_id=? ORDER BY proposal_id DESC LIMIT 1");
    $prStmt->execute([$eventId, $clientId]);
    $proposal = $prStmt->fetch();
}

if (!$event) {
    header('Location: yourevents.php');
    exit;
}
if (!$proposal) {
    header('Location: yourevents.php?status=all');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventIntel - Review Proposal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
    body { background:#ffffff; color:#222; min-height:100vh; }
    .container { max-width:1400px; margin:auto; padding:6px 48px 40px; }
    .navbar { display:flex; justify-content:space-between; align-items:center; gap:20px; flex-wrap:wrap; padding:12px 0 24px; }
    .logo { font-size:26px; font-weight:800; color:#f3c547; letter-spacing:1px; }
    .nav-links { display:flex; gap:12px; flex-wrap:wrap; }
    .nav-links button { padding:8px 18px; border-radius:12px; border:1px solid rgba(212,160,23,.35); background:rgba(255,255,255,.55); color:#222; font-size:14px; cursor:pointer; }
    .nav-links button:hover, .nav-links .active { background:linear-gradient(to right,#ffe17a,#d4a017); color:black; }
    .header { margin-bottom:24px; }
    .header h1 { font-size:40px; margin-bottom:8px; }
    .header p { color:#666; }
    .alert { padding:14px 18px; border-radius:12px; margin-bottom:20px; font-size:14px; }
    .alert.success { background:rgba(74,222,128,.12); color:#2a9d6f; border:1px solid #2a9d6f; }
    .alert.error { background:rgba(239,68,68,.12); color:#d32f2f; border:1px solid #d32f2f; }
    .status-banner { display:inline-block; padding:8px 18px; border-radius:999px; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; margin-bottom:20px; }
    .status-banner.sent, .status-banner.draft { background:rgba(251,191,36,.15); color:#b07c00; border:1px solid rgba(251,191,36,.4); }
    .status-banner.accepted { background:rgba(74,222,128,.15); color:#2a9d6f; border:1px solid rgba(74,222,128,.4); }
    .status-banner.rejected { background:rgba(239,68,68,.15); color:#d32f2f; border:1px solid rgba(239,68,68,.4); }
    .status-banner.revision_requested { background:rgba(59,130,246,.15); color:#1d4ed8; border:1px solid rgba(59,130,246,.4); }
    .proposal-card { background:#fff; border:1px solid rgba(212,160,23,.15); border-radius:24px; padding:30px; box-shadow:0 12px 30px rgba(0,0,0,.08); }
    .proposal-card h2 { font-size:22px; margin-bottom:20px; color:#111; }
    .section { margin-bottom:24px; }
    .section h3 { font-size:16px; color:#d4a017; margin-bottom:12px; text-transform:uppercase; letter-spacing:1px; }
    .info-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; }
    .info-item { background:rgba(243,197,71,.06); border:1px solid rgba(212,160,23,.15); border-radius:12px; padding:14px; }
    .info-item .label { font-size:11px; text-transform:uppercase; letter-spacing:1px; color:#888; margin-bottom:6px; font-weight:700; }
    .info-item .value { font-size:15px; color:#111; font-weight:600; }
    .breakdown { background:#fafafa; border:1px solid rgba(212,160,23,.12); border-radius:14px; padding:16px; white-space:pre-wrap; line-height:1.7; color:#555; font-size:14px; }
    .total { display:flex; justify-content:space-between; align-items:center; background:linear-gradient(135deg,#fff2ab,#f3c547,#c99208); border-radius:14px; padding:18px 24px; margin-top:20px; }
    .total .lbl { font-size:14px; font-weight:700; color:#111; }
    .total .amt { font-size:28px; font-weight:900; color:#111; }
    .actions { display:flex; gap:12px; margin-top:24px; flex-wrap:wrap; }
    .btn { padding:14px 24px; border-radius:14px; border:none; font-weight:800; cursor:pointer; transition:.3s; font-size:14px; }
    .btn:hover { transform:translateY(-2px); }
    .btn-accept { background:linear-gradient(135deg,#fff2ab,#f3c547,#c99208); color:#111; }
    .btn-reject { background:rgba(239,68,68,.12); color:#d32f2f; border:1px solid rgba(239,68,68,.4); }
    .btn-revision { background:rgba(59,130,246,.12); color:#1d4ed8; border:1px solid rgba(59,130,246,.4); }
    .comments-box { margin-top:20px; }
    .comments-box textarea { width:100%; padding:14px; border:1px solid rgba(212,160,23,.2); border-radius:12px; font-size:14px; min-height:90px; resize:vertical; outline:none; }
    .back-link { display:inline-block; margin-top:20px; color:#d4a017; text-decoration:none; font-weight:700; }
    .back-link:hover { text-decoration:underline; }
    .custom-box { background:rgba(59,130,246,.06); border:1px dashed rgba(59,130,246,.3); border-radius:14px; padding:16px; margin-bottom:20px; }
    .custom-box h4 { color:#1d4ed8; margin-bottom:8px; font-size:15px; }
    .custom-box .kv { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; font-size:13px; color:#666; }
    .custom-box .kv b { color:#111; }
    @media(max-width:900px){ .container{padding:6px 20px 30px;} }
  </style>
</head>
<body>
  <div class="container">
    <div class="navbar">
      <div class="logo">EventIntel</div>
      <div class="nav-links">
        <button onclick="window.location.href='homepage.php'">Home</button>
        <button onclick="window.location.href='yourevents.php'">Your Events</button>
        <button onclick="window.location.href='recommendation.php'">Recommendations</button>
        <button onclick="window.location.href='newsfeed.php'">Newsfeed</button>
      </div>
    </div>

    <div class="header">
      <h1>Coordinator Proposal</h1>
      <p><?= esc($event['title'] ?: 'Event') ?> • <?= esc($event['event_date'] ?: 'Date TBD') ?></p>
    </div>

    <?php if (isset($msg)): ?><div class="alert <?= isset($err) ? 'error' : 'success' ?>"><?= esc($msg) ?></div><?php endif; ?>

    <span class="status-banner <?= esc($proposal['status']) ?>">
      <?= esc(ucfirst(str_replace('_', ' ', $proposal['status']))) ?>
    </span>

    <?php if ($customRequest): ?>
      <div class="custom-box">
        <h4><i class="fas fa-clipboard-list"></i> Your Custom Event Request</h4>
        <div class="kv">
          <span><b>Event Type:</b> <?= esc($customRequest['event_type'] ?: '-') ?></span>
          <span><b>Date:</b> <?= esc($customRequest['event_date'] ?: '-') ?></span>
          <span><b>Guests:</b> <?= esc($customRequest['guest_count'] ?: '-') ?></span>
          <span><b>Theme:</b> <?= esc($customRequest['theme'] ?: '-') ?></span>
          <span><b>Budget:</b> ₱<?= number_format((float)$customRequest['budget'], 2) ?></span>
          <span><b>Services:</b> <?= esc($customRequest['required_services'] ?: '-') ?></span>
          <span><b>Notes:</b> <?= esc($customRequest['additional_notes'] ?: '-') ?></span>
        </div>
      </div>
    <?php endif; ?>

    <div class="proposal-card">
      <h2><i class="fas fa-file-signature" style="color:#d4a017;"></i> Proposal Details</h2>

      <div class="section">
        <h3>Services & Suggestions</h3>
        <div class="info-grid">
          <?php if ($proposal['venue']): ?><div class="info-item"><div class="label">Venue</div><div class="value"><?= esc($proposal['venue']) ?></div></div><?php endif; ?>
          <?php if ($proposal['catering']): ?><div class="info-item"><div class="label">Catering</div><div class="value"><?= esc($proposal['catering']) ?></div></div><?php endif; ?>
          <?php if ($proposal['clothing']): ?><div class="info-item"><div class="label">Clothing / Attire</div><div class="value"><?= esc($proposal['clothing']) ?></div></div><?php endif; ?>
          <?php if ($proposal['decorations']): ?><div class="info-item"><div class="label">Decorations</div><div class="value"><?= esc($proposal['decorations']) ?></div></div><?php endif; ?>
          <?php if ($proposal['host']): ?><div class="info-item"><div class="label">Host / Emcee</div><div class="value"><?= esc($proposal['host']) ?></div></div><?php endif; ?>
          <?php if ($proposal['photography']): ?><div class="info-item"><div class="label">Photography</div><div class="value"><?= esc($proposal['photography']) ?></div></div><?php endif; ?>
          <?php if ($proposal['videography']): ?><div class="info-item"><div class="label">Videography</div><div class="value"><?= esc($proposal['videography']) ?></div></div><?php endif; ?>
        </div>
      </div>

      <?php if ($proposal['timeline']): ?>
        <div class="section">
          <h3>Event Timeline</h3>
          <div class="breakdown"><?= nl2br(esc($proposal['timeline'])) ?></div>
        </div>
      <?php endif; ?>

      <?php if ($proposal['cost_breakdown']): ?>
        <div class="section">
          <h3>Estimated Cost Breakdown</h3>
          <div class="breakdown"><?= nl2br(esc($proposal['cost_breakdown'])) ?></div>
        </div>
      <?php endif; ?>

      <?php if ($proposal['recommendations']): ?>
        <div class="section">
          <h3>Additional Recommendations</h3>
          <div class="breakdown"><?= nl2br(esc($proposal['recommendations'])) ?></div>
        </div>
      <?php endif; ?>

      <div class="total">
        <span class="lbl">TOTAL QUOTATION</span>
        <span class="amt">₱<?= number_format((float)$proposal['total_quotation'], 2) ?></span>
      </div>

      <?php if (in_array($proposal['status'], ['sent', 'draft', 'revision_requested'])): ?>
        <form method="POST">
          <div class="comments-box">
            <label style="font-size:13px;font-weight:700;color:#666;display:block;margin-bottom:8px;">Feedback / Comments (required for revisions)</label>
            <textarea name="comments" placeholder="Add any comments or feedback for the coordinator..."><?= esc($proposal['client_comments'] ?? '') ?></textarea>
          </div>
          <div class="actions">
            <button type="submit" name="proposal_action" value="accept" class="btn btn-accept"><i class="fas fa-check"></i> Accept Proposal</button>
            <button type="submit" name="proposal_action" value="revision" class="btn btn-revision"><i class="fas fa-edit"></i> Request Revision</button>
            <button type="submit" name="proposal_action" value="reject" class="btn btn-reject" onclick="return confirm('Reject this proposal?')"><i class="fas fa-times"></i> Reject</button>
          </div>
        </form>
      <?php else: ?>
        <?php if ($proposal['client_comments']): ?>
          <div class="section" style="margin-top:20px;">
            <h3>Your Feedback</h3>
            <div class="breakdown"><?= nl2br(esc($proposal['client_comments'])) ?></div>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <a class="back-link" href="yourevents.php"><i class="fas fa-arrow-left"></i> Back to Your Events</a>
    </div>
  </div>
</body>
</html>
