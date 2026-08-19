<?php
require_once __DIR__ . '/../config/db.php';
require_role('supplier');

$pdo = db();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$stmt->execute([$userId]);
$supplier = $stmt->fetch();

if (!$supplier) {
    redirect_to('/auth/logout.php');
}

$message = '';
$messageType = 'info';

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $businessName = trim($_POST['business_name'] ?? '');
    $businessAddress = trim($_POST['business_address'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!$businessName || !$businessAddress || !$fullName || !$email) {
        $message = 'Please fill in all required fields';
        $messageType = 'error';
    } else {
        try {
            $update = $pdo->prepare('
                UPDATE users 
                SET business_name = ?, business_address = ?, full_name = ?, email = ?, phone = ?
                WHERE user_id = ?
            ');
            $update->execute([$businessName, $businessAddress, $fullName, $email, $phone, $userId]);
            $message = 'Profile updated successfully!';
            $messageType = 'success';
            
            // Refresh supplier data
            $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
            $stmt->execute([$userId]);
            $supplier = $stmt->fetch();
        } catch (Exception $e) {
            $message = 'Error updating profile: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get supplier services count
$services = $pdo->prepare('SELECT COUNT(*) as count FROM supplier_services WHERE user_id = ?');
$services->execute([$userId]);
$servicesCount = $services->fetch()['count'];

// Get bookings count
$bookingsCount = 0;
if (function_exists('table_exists') && table_exists('bookings')) {
    $bookings = $pdo->prepare('SELECT COUNT(*) as count FROM bookings WHERE service_id IN (SELECT service_id FROM supplier_services WHERE user_id = ?)');
    $bookings->execute([$userId]);
    $bookingsCount = $bookings->fetch()['count'];
}

// Get reviews
$avgRating = 0;
$totalReviews = 0;
if (function_exists('table_exists') && table_exists('reviews')) {
    $reviews = $pdo->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE service_id IN (SELECT service_id FROM supplier_services WHERE user_id = ?)');
    $reviews->execute([$userId]);
    $reviewData = $reviews->fetch();
    $avgRating = $reviewData['avg_rating'] ?? 0;
    $totalReviews = $reviewData['total_reviews'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Profile - EventIntel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .profile-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .profile-info h1 {
            color: #222;
            margin-bottom: 10px;
        }

        .profile-info p {
            color: #666;
            margin: 5px 0;
            font-size: 14px;
        }

        .stats {
            display: flex;
            gap: 20px;
        }

        .stat-card {
            text-align: center;
            padding: 15px 20px;
            background: rgba(243,197,71,0.08);
            border-radius: 8px;
            border-left: 3px solid #f3c547;
        }

        .stat-card .number {
            font-size: 24px;
            font-weight: 700;
            color: #f3c547;
        }

        .stat-card .label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .rating {
            color: #ffc107;
        }

        .form-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .form-section h2 {
            color: #222;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(243,197,71,0.2);
            border-radius: 8px;
            font-size: 14px;
            color: #333;
        }

        .form-group input:focus {
            outline: none;
            border-color: #f3c547;
            box-shadow: 0 0 0 2px rgba(243,197,71,0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #fff2ab, #f3c547);
            color: #222;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(243,197,71,0.3);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 3px solid #28a745;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 3px solid #dc3545;
        }

        .message.info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 3px solid #17a2b8;
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .stats {
                flex-direction: column;
                width: 100%;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="profile-header">
            <div class="profile-info">
                <h1><?= esc($supplier['business_name'] ?? $supplier['full_name']) ?></h1>
                <p><strong>Account Type:</strong> Service Supplier</p>
                <p><strong>Status:</strong> <span style="color: #28a745;">✓ Approved</span></p>
                <p><?= esc($supplier['business_address'] ?? 'Address not set') ?></p>
            </div>

            <div class="stats">
                <div class="stat-card">
                    <div class="number"><?= $servicesCount ?></div>
                    <div class="label">Services Listed</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?= $bookingsCount ?></div>
                    <div class="label">Total Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="number"><span class="rating"><?= number_format($avgRating, 1) ?></span> / 5</div>
                    <div class="label"><?= $totalReviews ?> Reviews</div>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h2>Edit Profile Information</h2>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" value="<?= esc($supplier['full_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?= esc($supplier['email']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="business_name">Business Name *</label>
                        <input type="text" id="business_name" name="business_name" value="<?= esc($supplier['business_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?= esc($supplier['phone']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="business_address">Business Address *</label>
                    <input type="text" id="business_address" name="business_address" value="<?= esc($supplier['business_address']) ?>" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='DASHBOARD.php'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'sidebar.php'; ?>
</body>
</html>
