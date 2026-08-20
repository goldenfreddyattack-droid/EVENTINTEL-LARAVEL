<?php
require_once __DIR__ . '/../../config/db.php';
require_login();
$pdo = db();
$userId = $_SESSION['user_id'];
$message = '';
$messageType = 'info';

$save_upload = function ($field, $folder) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return null;
    }
    $dir = __DIR__ . '/../../uploads/' . $folder;
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $name = $folder . '/' . uniqid($field . '_', true) . '.' . $ext;
    $dest = __DIR__ . '/../../uploads/' . $name;
    move_uploaded_file($_FILES[$field]['tmp_name'], $dest);
    return 'uploads/' . $name;
};

$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$stmt->execute([$userId]);
$currentUser = $stmt->fetch();
if (!$currentUser) {
    redirect_to('/html/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_role'])) {
    $applyRole = $_POST['apply_role'] === 'supplier' ? 'supplier' : 'coordinator';
    $businessName = trim($_POST['business_name'] ?? '');
    $businessAddress = trim($_POST['business_address'] ?? '');

    if ($businessName === '' || $businessAddress === '') {
        $message = 'Please fill in the business name and address.';
        $messageType = 'error';
    } else {
        $validId = $save_upload('valid_id', 'ids') ?? $currentUser['valid_id'];
        $permit = $save_upload('business_permit', 'permits') ?? $currentUser['business_permit'];

        $facePath = $currentUser['face_capture'];
        $faceData = $_POST['face_capture'] ?? '';
        if (is_string($faceData) && str_starts_with($faceData, 'data:image')) {
            $faceData = preg_replace('#^data:image/\w+;base64,#i', '', $faceData);
            $faceRaw = base64_decode($faceData);
            if ($faceRaw) {
                $faceDir = __DIR__ . '/../../uploads/faces';
                if (!is_dir($faceDir)) mkdir($faceDir, 0777, true);
                $faceName = 'faces/face_' . uniqid('', true) . '.png';
                file_put_contents(__DIR__ . '/../../uploads/' . $faceName, $faceRaw);
                $facePath = 'uploads/' . $faceName;
            }
        }

        $update = $pdo->prepare('UPDATE users SET role = ?, status = ?, business_name = ?, business_address = ?, valid_id = ?, business_permit = ?, face_capture = ? WHERE user_id = ?');
        $update->execute([
            $applyRole,
            'pending',
            $businessName,
            $businessAddress,
            $validId,
            $permit,
            $facePath,
            $userId,
        ]);

        $_SESSION['role'] = $applyRole;
        $_SESSION['status'] = 'pending';
        $currentUser['role'] = $applyRole;
        $currentUser['status'] = 'pending';
        $currentUser['business_name'] = $businessName;
        $currentUser['business_address'] = $businessAddress;
        $currentUser['valid_id'] = $validId;
        $currentUser['business_permit'] = $permit;
        $currentUser['face_capture'] = $facePath;

        $message = 'Application submitted as ' . ucfirst($applyRole) . '. Admin will review it shortly.';
        $messageType = 'success';
    }
}

$displayRole = $currentUser['role'] === 'client' ? 'Client' : ucfirst($currentUser['role']);
$displayStatus = ucfirst($currentUser['status'] ?? 'approved');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root{--bg:#ffffff;--panel:#ffffff;--panel2:rgba(212,160,23,.05);--gold:#d4a017;--muted:#6b6b6b;--text:#171717;--border:rgba(212,160,23,.22);--radius:26px}
        *{box-sizing:border-box}
        body{margin:0;background:#ffffff;color:var(--text);font-family:'Segoe UI',sans-serif;min-height:100vh}
        .page-shell{max-width:1180px;margin:0 auto;padding:28px 22px}
        .breadcrumbs{display:flex;align-items:center;gap:10px;font-size:14px;color:var(--muted);margin-bottom:18px}
        .breadcrumbs a{color:var(--text);text-decoration:none}
        .profile-grid{display:grid;grid-template-columns:320px minmax(0,1fr);gap:26px}
        .panel{background:var(--panel);border:1px solid var(--border);border-radius:var(--radius);padding:28px;box-shadow:0 14px 40px rgba(0,0,0,.06)}
        .profile-card{display:grid;gap:18px}
        .profile-avatar{width:94px;height:94px;border-radius:22px;background:linear-gradient(135deg,#f3c547,#eec762);display:grid;place-items:center;font-size:42px;color:#111}
        .profile-card h1{font-size:32px;margin:0;color:#171717}
        .profile-card p{margin:0;color:var(--muted);line-height:1.6}
        .status-pill{display:inline-flex;padding:10px 16px;border-radius:999px;background:rgba(212,160,23,.12);color:var(--gold);font-weight:700}
        .stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .stat-tile{padding:18px;border-radius:24px;background:rgba(212,160,23,.05);border:1px solid rgba(212,160,23,.15)}
        .stat-tile strong{display:block;font-size:14px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;margin-bottom:10px}
        .stat-tile span{font-size:26px;font-weight:800;color:#171717}
        .profile-actions{display:grid;gap:12px;margin-top:16px}
        .profile-actions button{border:none;border-radius:16px;padding:14px 18px;background:rgba(212,160,23,.1);color:#171717;font-weight:700;cursor:pointer;transition:.25s}
        .profile-actions button:hover{background:rgba(212,160,23,.18)}
        .panel h2{margin-top:0;font-size:28px;color:#171717}
        .panel p{color:var(--muted);line-height:1.75}
        .notice{background:rgba(212,160,23,.07);border:1px solid rgba(212,160,23,.2);border-radius:20px;padding:18px;color:#171717}
        .message-box{background:rgba(67,181,129,.1);border:1px solid rgba(67,181,129,.3);border-radius:20px;padding:18px;margin-bottom:22px;color:#1f7a44}
        .form-group{display:grid;gap:12px;margin-top:18px}
        .form-group label{font-size:14px;color:var(--muted)}
        .form-group input[type=text],.form-group input[type=file]{width:100%;padding:14px 16px;border-radius:16px;border:1px solid rgba(212,160,23,.25);background:#fff;color:var(--text);outline:none}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:18px}
        .video-card{border-radius:22px;overflow:hidden;border:1px solid rgba(212,160,23,.2);background:#fff;margin-top:12px}
        .video-card video{width:100%;display:block}
        .form-footer{display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-top:20px}
        .button{border:none;border-radius:16px;padding:14px 20px;background:linear-gradient(135deg,#fff1a8,#f3c547);color:#111;font-weight:800;cursor:pointer;transition:.25s}
        .button:hover{transform:translateY(-2px)}
        .logout-btn{display:inline-flex;align-items:center;gap:10px;padding:14px 20px;border-radius:16px;background:rgba(255,80,80,.08);border:1px solid rgba(255,80,80,.22);color:#c23b3b;text-decoration:none;font-weight:700;transition:.25s}
        .logout-btn:hover{background:rgba(255,80,80,.14)}
        .application-panel{margin-top:22px;padding:26px;border-radius:28px;border:1px solid rgba(212,160,23,.2);background:rgba(212,160,23,.03)}
        .application-panel label{display:block;margin-bottom:8px;color:var(--muted);font-size:14px}
        .application-panel h2{margin-top:0;font-size:24px;color:#171717}
        .application-panel .hint{color:#8a8a8a;font-size:14px;margin-bottom:18px}
        .hidden{display:none}
        @media(max-width:900px){.profile-grid{grid-template-columns:1fr}.form-row{grid-template-columns:1fr}.profile-actions{grid-template-columns:1fr}}    
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="breadcrumbs"><a href="homepage.php">Home</a><span>›</span><span>Profile</span></div>
        <div class="profile-grid">
            <section class="panel profile-card">
                <div class="profile-avatar"><i class="fa-regular fa-user"></i></div>
                <div>
                    <h1><?= esc($currentUser['full_name'] ?? $currentUser['username']) ?></h1>
                    <p><?= esc($currentUser['username']) ?></p>
                    <span class="status-pill"><?= esc($displayRole) ?> • <?= esc($displayStatus) ?></span>
                </div>
                <div class="stats-grid">
                    <div class="stat-tile"><strong>Account</strong><span><?= esc($currentUser['role'] === 'client' ? 'Client' : ucfirst($currentUser['role'])) ?></span></div>
                    <div class="stat-tile"><strong>Status</strong><span><?= esc($displayStatus) ?></span></div>
                    <div class="stat-tile"><strong>Joined</strong><span><?= esc(date('M j, Y', strtotime($currentUser['created_at'] ?? 'now'))) ?></span></div>
                    <div class="stat-tile"><strong>Business</strong><span><?= esc($currentUser['business_name'] ?? 'None') ?></span></div>
                </div>
                <div class="profile-actions">
                    <button type="button" onclick="window.location.href='homepage.php'">Back to Home</button>
                    <button type="button" onclick="window.location.href='recommendation.php'">Recommendations</button>
                    <button type="button" onclick="window.location.href='newsfeed.php'">Newsfeed</button>
                    <button type="button" onclick="window.location.href='../../auth/logout.php'" style="background: #dc3545; color: white;">Logout</button>
                </div>
            </section>

            <main class="panel">
                <?php if ($message): ?>
                    <div class="message-box"><?= esc($message) ?></div>
                <?php endif; ?>

                <?php if ($currentUser['business_name'] || $currentUser['business_address']): ?>
                    <div class="notice">
                        <strong>Application details</strong><br>
                        Business: <?= esc($currentUser['business_name'] ?? 'N/A') ?><br>
                        Address: <?= esc($currentUser['business_address'] ?? 'N/A') ?><br>
                        <?php if ($currentUser['valid_id']): ?><a href="../../<?= esc($currentUser['valid_id']) ?>" target="_blank" style="color:var(--gold);text-decoration:none;">View ID</a><?php endif; ?>
                        <?php if ($currentUser['business_permit']): ?> | <a href="../../<?= esc($currentUser['business_permit']) ?>" target="_blank" style="color:var(--gold);text-decoration:none;">View Permit</a><?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($currentUser['status'] === 'approved' && $currentUser['role'] !== 'client'): ?>
                    <div class="notice">Your application is approved. You now have <?= esc($currentUser['role']) ?> access and can publish supplier services.</div>
                    <a href="../../auth/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
                <?php else: ?>
                    <div class="notice">Apply here for supplier or coordinator access. The admin will review it and update your profile status.</div>
                    <div class="profile-actions">
                        <button class="button" type="button" onclick="showApplication('coordinator')">Apply as Coordinator</button>
                        <button class="button" type="button" onclick="showApplication('supplier')">Apply as Supplier</button>
                    </div>

                    <form id="applicationForm" class="application-panel hidden" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="apply_role" id="apply_role" value="supplier">
                        <h2>Apply as <span id="applyRoleLabel">Supplier</span></h2>
                        <p class="hint">Your application will be reviewed by administration. Upload your business details and ID for verification.</p>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Business / Organization Name</label>
                                <input type="text" name="business_name" value="<?= esc($currentUser['business_name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Business Address</label>
                                <input type="text" name="business_address" value="<?= esc($currentUser['business_address'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Upload Valid ID</label>
                                <input type="file" name="valid_id" accept="image/*,.pdf">
                            </div>
                            <div class="form-group">
                                <label>Upload Business Permit</label>
                                <input type="file" name="business_permit" accept="image/*,.pdf">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="grid-column:1/-1;">
                                <label>Live Face Scan</label>
                                <div class="video-card"><video id="faceVideo" autoplay playsinline></video></div>
                                <canvas id="faceCanvas" width="360" height="250" style="display:none"></canvas>
                                <button type="button" class="button" onclick="captureFace()">Capture Face</button>
                                <p id="faceStatus" style="margin-top:12px;color:#aaa;font-size:14px;">Camera will open when you choose an application type.</p>
                            </div>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="button">Submit Application</button>
                            <a href="../../auth/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
                        </div>
                    </form>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        function showApplication(role) {
            document.getElementById('applicationForm').classList.remove('hidden');
            document.getElementById('apply_role').value = role;
            document.getElementById('applyRoleLabel').textContent = role === 'supplier' ? 'Supplier' : 'Coordinator';
            startFaceCamera();
        }

        async function startFaceCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                document.getElementById('faceVideo').srcObject = stream;
                document.getElementById('faceStatus').textContent = 'Camera ready. Capture your face when ready.';
            } catch (error) {
                document.getElementById('faceStatus').textContent = 'Camera unavailable. You can still submit the application with files.';
            }
        }

        function captureFace() {
            const video = document.getElementById('faceVideo');
            const canvas = document.getElementById('faceCanvas');
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            document.getElementById('face_capture').value = canvas.toDataURL('image/png');
            document.getElementById('faceStatus').textContent = 'Face captured for admin verification.';
        }
    </script>
</body>
</html>
