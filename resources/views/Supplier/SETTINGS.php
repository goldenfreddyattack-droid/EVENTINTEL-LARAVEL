<?php
require_once __DIR__ . '/../config/db.php';
require_role('supplier');

$pdo = db();
$message = '';

$notificationDefaults = [
    'booking_alerts' => true,
    'messages' => true,
    'promotions' => false,
];

if (!isset($_SESSION['notification_settings'])) {
    $_SESSION['notification_settings'] = $notificationDefaults;
}

$user = $pdo->prepare("SELECT user_id, username, full_name, email, phone, password, role FROM users WHERE user_id = ?");
$user->execute([$_SESSION['user_id']]);
$userData = $user->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if ($fullName === '' || $email === '') {
            $message = 'Full name and email are required.';
        } else {
            $updates = [];
            $params = [];

            if (method_exists('\Illuminate\Support\Facades\Schema', 'hasColumn') && \Illuminate\Support\Facades\Schema::hasColumn('users', 'full_name')) {
                $updates[] = 'full_name = ?';
                $params[] = $fullName;
            }

            if (method_exists('\Illuminate\Support\Facades\Schema', 'hasColumn') && \Illuminate\Support\Facades\Schema::hasColumn('users', 'email')) {
                $updates[] = 'email = ?';
                $params[] = $email;
            }

            if (method_exists('\Illuminate\Support\Facades\Schema', 'hasColumn') && \Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
                $updates[] = 'phone = ?';
                $params[] = $phone;
            }

            if (!empty($updates)) {
                $params[] = $_SESSION['user_id'];
                $pdo->prepare('UPDATE users SET ' . implode(', ', $updates) . ' WHERE user_id = ?')->execute($params);
                $_SESSION['full_name'] = $fullName;
                $message = 'Profile updated successfully!';

                $user->execute([$_SESSION['user_id']]);
                $userData = $user->fetch();
            } else {
                $message = 'This database does not have the required user profile columns.';
            }
        }
    }

    if (isset($_POST['change_password'])) {
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if ($new === '' || $confirm === '') {
            $message = 'Please enter a new password and confirm it.';
        } elseif ($new !== $confirm) {
            $message = 'New passwords do not match!';
        } elseif (!isset($userData['password']) || !password_verify($current, (string) $userData['password'])) {
            $message = 'Current password is incorrect!';
        } else {
            $pdo->prepare('UPDATE users SET password = ? WHERE user_id = ?')->execute([
                password_hash($new, PASSWORD_DEFAULT),
                $_SESSION['user_id'],
            ]);
            $message = 'Password changed successfully!';
        }
    }

    if (isset($_POST['save_notifications'])) {
        $_SESSION['notification_settings'] = [
            'booking_alerts' => isset($_POST['booking_alerts']),
            'messages' => isset($_POST['messages']),
            'promotions' => isset($_POST['promotions']),
        ];
        $message = 'Notification preferences saved.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SETTINGS</title>
    <link rel="stylesheet" href="../css/supplier.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container">
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <main class="main-content">
            <div id="header"></div>

            <section class="settings-page">
                <h2>Settings</h2>

                <?php if ($message): ?>
                <div style="padding:16px 20px;border-radius:14px;margin-bottom:24px;background:<?= strpos($message, 'Error') !== false || strpos($message, 'incorrect') !== false || strpos($message, 'not match') !== false ? 'rgba(255,80,80,.12)' : 'rgba(100,255,150,.12)' ?>;border:1px solid <?= strpos($message, 'Error') !== false || strpos($message, 'incorrect') !== false || strpos($message, 'not match') !== false ? 'rgba(255,80,80,.3)' : 'rgba(100,255,150,.3)' ?>;color:<?= strpos($message, 'Error') !== false || strpos($message, 'incorrect') !== false || strpos($message, 'not match') !== false ? '#ff8b8b' : '#64ff96' ?>;">
                    <?= esc($message) ?>
                </div>
                <?php endif; ?>

                <div class="settings-grid">
                    <div class="setting-card">
                        <h3><i class="fas fa-user" style="color:var(--gold);margin-right:8px;"></i>Profile Information</h3>
                        <form method="POST">
                            <input type="text" name="username" placeholder="Username" value="<?= esc($userData['username'] ?? 'Supplier') ?>" readonly style="width:100%;padding:13px 15px;margin-bottom:12px;border-radius:14px;border:1px solid var(--border);background:#f5f5f5;color:var(--text);outline:none;">
                            <input type="text" name="full_name" placeholder="Full Name" value="<?= esc($userData['full_name'] ?? '') ?>" style="width:100%;padding:13px 15px;margin-bottom:12px;border-radius:14px;border:1px solid var(--border);background:white;color:var(--text);outline:none;">
                            <input type="email" name="email" placeholder="Email Address" value="<?= esc($userData['email'] ?? '') ?>" style="width:100%;padding:13px 15px;margin-bottom:12px;border-radius:14px;border:1px solid var(--border);background:white;color:var(--text);outline:none;">
                            <input type="text" name="phone" placeholder="Phone Number" value="<?= esc($userData['phone'] ?? '') ?>" style="width:100%;padding:13px 15px;margin-bottom:16px;border-radius:14px;border:1px solid var(--border);background:white;color:var(--text);outline:none;">
                            <button type="submit" name="update_profile" class="setting-card button" style="width:100%;">Save Changes</button>
                        </form>
                    </div>

                    <div class="setting-card">
                        <h3><i class="fas fa-lock" style="color:var(--gold);margin-right:8px;"></i>Change Password</h3>
                        <form method="POST">
                            <input type="password" name="current_password" placeholder="Current Password" style="width:100%;padding:13px 15px;margin-bottom:12px;border-radius:14px;border:1px solid var(--border);background:white;color:var(--text);outline:none;">
                            <input type="password" name="new_password" placeholder="New Password" style="width:100%;padding:13px 15px;margin-bottom:12px;border-radius:14px;border:1px solid var(--border);background:white;color:var(--text);outline:none;">
                            <input type="password" name="confirm_password" placeholder="Confirm Password" style="width:100%;padding:13px 15px;margin-bottom:16px;border-radius:14px;border:1px solid var(--border);background:white;color:var(--text);outline:none;">
                            <button type="submit" name="change_password" class="setting-card button" style="width:100%;">Update Password</button>
                        </form>
                    </div>

                    <div class="setting-card">
                        <h3><i class="fas fa-bell" style="color:var(--gold);margin-right:8px;"></i>Notification Settings</h3>
                        <form method="POST">
                            <label style="display:flex;align-items:center;gap:10px;margin-bottom:12px;color:var(--text);">
                                <input type="checkbox" name="booking_alerts" value="1" <?= !empty($_SESSION['notification_settings']['booking_alerts']) ? 'checked' : '' ?> style="width:16px;height:16px;accent-color:var(--gold);">
                                Booking Alerts
                            </label>
                            <label style="display:flex;align-items:center;gap:10px;margin-bottom:12px;color:var(--text);">
                                <input type="checkbox" name="messages" value="1" <?= !empty($_SESSION['notification_settings']['messages']) ? 'checked' : '' ?> style="width:16px;height:16px;accent-color:var(--gold);">
                                Messages
                            </label>
                            <label style="display:flex;align-items:center;gap:10px;margin-bottom:16px;color:var(--text);">
                                <input type="checkbox" name="promotions" value="1" <?= !empty($_SESSION['notification_settings']['promotions']) ? 'checked' : '' ?> style="width:16px;height:16px;accent-color:var(--gold);">
                                Promotions
                            </label>
                            <button type="submit" name="save_notifications" class="setting-card button" style="width:100%;">Save Preferences</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="../js/header.js"></script>
</body>
</html>
