<?php
require_once __DIR__ . '/../../config/db.php';
require_role('client');

$pdo = db();
$eventId = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

if ($eventId > 0) {
    $eventCheck = $pdo->prepare("SELECT event_id FROM events WHERE event_id = ? AND user_id = ?");
    $eventCheck->execute([$eventId, $_SESSION['user_id']]);

    if ($eventCheck->fetch()) {
        $pendingStmt = $pdo->prepare("UPDATE events SET payment_status = 'pending' WHERE event_id = ? AND user_id = ?");
        $pendingStmt->execute([$eventId, $_SESSION['user_id']]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['event_id'])) {
    $postedEventId = (int) $_POST['event_id'];
    $paymentMethod = trim($_POST['payment_method'] ?? 'cash');

    $updateStmt = $pdo->prepare("UPDATE events SET payment_method = ?, payment_status = 'pending' WHERE event_id = ? AND user_id = ?");
    $updateStmt->execute([$paymentMethod, $postedEventId, $_SESSION['user_id']]);

    header('Location: yourevents.php');
    exit;
}

// Get the temporary event data from POST
$event_type = $_POST['event_type'] ?? '';
$date = $_POST['event_date'] ?? '';
$time = $_POST['event_time'] ?? '';
$guest_count = $_POST['guest_count'] ?? '';
$services = $_POST['services'] ?? [];
$venue_name = trim($_POST['venue'] ?? $_POST['venue_name'] ?? '');
if (strtolower($venue_name) === 'null') {
    $venue_name = '';
}
if ($venue_name === '' && isset($_COOKIE['event_selection_venue'])) {
    $venue_name = trim($_COOKIE['event_selection_venue']);
}
if (strtolower($venue_name) === 'null') {
    $venue_name = '';
}
$clothes = trim($_POST['clothes'] ?? '');
if ($clothes === '' && isset($_COOKIE['event_selection_clothes'])) {
    $clothes = trim($_COOKIE['event_selection_clothes']);
}
$catering = trim($_POST['catering'] ?? '');
if ($catering === '' && isset($_COOKIE['event_selection_catering'])) {
    $catering = trim($_COOKIE['event_selection_catering']);
}
$host = trim($_POST['host'] ?? '');
if ($host === '' && isset($_COOKIE['event_selection_host'])) {
    $host = trim($_COOKIE['event_selection_host']);
}
$photographer = trim($_POST['photographer'] ?? '');
if ($photographer === '' && isset($_COOKIE['event_selection_photographer'])) {
    $photographer = trim($_COOKIE['event_selection_photographer']);
}
$sounds_lights = trim($_POST['sounds_lights'] ?? '');
if ($sounds_lights === '' && isset($_COOKIE['event_selection_sounds_lights'])) {
    $sounds_lights = trim($_COOKIE['event_selection_sounds_lights']);
}
$event_end_time = $_POST['event_end_time'] ?? '';

if ($event_type === 'Others') {
    $event_type = $_POST['other_event_type'] ?? $event_type;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventIntel - Select Payment Method</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 100%);
            color: #222;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(243, 197, 71, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            font-size: 32px;
            font-weight: 800;
            color: #f3c547;
            margin-bottom: 15px;
        }

        h1 {
            font-size: 24px;
            color: #222;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #999;
            font-size: 14px;
        }

        .payment-options {
            display: grid;
            gap: 16px;
            margin-bottom: 30px;
        }

        .payment-option {
            position: relative;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            border: 2px solid rgba(243, 197, 71, 0.2);
            border-radius: 14px;
            cursor: pointer;
            transition: 0.3s ease;
            background: #f9f9f9;
        }

        .payment-option:hover {
            border-color: #f3c547;
            background: rgba(243, 197, 71, 0.05);
            transform: translateY(-2px);
        }

        .payment-option input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #f3c547;
            cursor: pointer;
        }

        .payment-option input[type="radio"]:checked + .option-content {
            color: #f3c547;
        }

        .option-content {
            flex: 1;
        }

        .option-title {
            font-size: 16px;
            font-weight: 600;
            color: #222;
            margin-bottom: 4px;
        }

        .option-description {
            font-size: 13px;
            color: #999;
        }

        .option-icon {
            font-size: 28px;
            color: #f3c547;
        }

        .payment-option input[type="radio"]:checked ~ .option-icon {
            color: #f3c547;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        button {
            padding: 12px 28px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s ease;
            font-size: 14px;
        }

        .cancel-btn {
            background: rgba(0, 0, 0, 0.05);
            color: #222;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .cancel-btn:hover {
            background: rgba(0, 0, 0, 0.1);
        }

        .proceed-btn {
            background: linear-gradient(135deg, #ffe27d, #f3c547);
            color: #222;
            box-shadow: 0 4px 15px rgba(243, 197, 71, 0.3);
        }

        .proceed-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(243, 197, 71, 0.4);
        }

        .proceed-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .info-box {
            background: rgba(243, 197, 71, 0.08);
            border-left: 4px solid #f3c547;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #666;
        }

        @media (max-width: 640px) {
            .card {
                padding: 24px;
            }

            .logo {
                font-size: 24px;
            }

            h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="logo">EventIntel</div>
                <h1>Payment Method</h1>
                <p class="subtitle">Choose how you want to pay for your event</p>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                Suppliers and coordinators can see your payment method choice and may decline if they require a different payment option.
            </div>

            <form id="paymentForm" method="POST" action="payment_method.php">
                <?php if ($eventId > 0): ?>
                    <input type="hidden" name="event_id" value="<?= esc($eventId) ?>">
                <?php endif; ?>

                <!-- Hidden fields for event data -->
                <input type="hidden" name="event_type" value="<?= esc($event_type) ?>">
                <input type="hidden" name="event_date" value="<?= esc($date) ?>">
                <input type="hidden" name="event_time" value="<?= esc($time) ?>">
                <input type="hidden" name="guest_count" value="<?= esc($guest_count) ?>">
                <input type="hidden" name="event_end_time" value="<?= esc($event_end_time) ?>">
                <input type="hidden" name="venue" value="<?= esc($venue_name) ?>">
                <input type="hidden" name="venue_name" value="<?= esc($venue_name) ?>">
                <input type="hidden" name="clothes" value="<?= esc($clothes) ?>">
                <input type="hidden" name="catering" value="<?= esc($catering) ?>">
                <input type="hidden" name="host" value="<?= esc($host) ?>">
                <input type="hidden" name="photographer" value="<?= esc($photographer) ?>">
                <input type="hidden" name="sounds_lights" value="<?= esc($sounds_lights) ?>">

                <?php if ($event_type === 'Others'): ?>
                    <input type="hidden" name="other_event_type" value="<?= esc($_POST['other_event_type'] ?? '') ?>">
                <?php endif; ?>

                <?php foreach ($services as $service): ?>
                    <input type="hidden" name="services[]" value="<?= esc($service) ?>">
                <?php endforeach; ?>

                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cash" required>
                        <div class="option-content">
                            <div class="option-title">Cash Payment</div>
                            <div class="option-description">Pay directly on the day of the event</div>
                        </div>
                        <div class="option-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="online" required>
                        <div class="option-content">
                            <div class="option-title">Online Payment</div>
                            <div class="option-description">Secure payment via GCash or bank transfer</div>
                        </div>
                        <div class="option-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </label>
                </div>

                <div class="actions">
                    <button type="button" class="cancel-btn" onclick="window.history.back()">Back</button>
                    <button type="submit" class="proceed-btn" id="proceedBtn" disabled>Proceed</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const radioButtons = document.querySelectorAll('input[type="radio"]');
        const proceedBtn = document.getElementById('proceedBtn');

        radioButtons.forEach(radio => {
            radio.addEventListener('change', () => {
                proceedBtn.disabled = false;
            });
        });

        // Allow Enter key to submit
        document.getElementById('paymentForm').addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !proceedBtn.disabled) {
                document.getElementById('paymentForm').submit();
            }
        });
    </script>
</body>
</html>
