<?php
require_once __DIR__ . '/../config/db.php';
require_role('supplier');

$pdo = db();
$message = '';

// Handle add service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    try {
        $pdo->prepare("INSERT INTO supplier_services(user_id, category, style, name, description, price, address, latitude, longitude) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([
                $_SESSION['user_id'],
                $_POST['category'],
                $_POST['style'] ?: null,
                $_POST['name'],
                $_POST['description'],
                $_POST['price'] ?: 0,
                $_POST['address'],
                $_POST['latitude'] ?: null,
                $_POST['longitude'] ?: null
            ]);
        $message = 'Service added successfully!';
    } catch (Exception $e) {
        $message = 'Error adding service: ' . $e->getMessage();
    }
}

// Handle delete service
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM supplier_services WHERE service_id = ? AND user_id = ?")
        ->execute([intval($_GET['delete']), $_SESSION['user_id']]);
    header('Location: SERVICES.php');
    exit;
}

$rows = $pdo->prepare("SELECT * FROM supplier_services WHERE user_id = ? ORDER BY created_at DESC");
$rows->execute([$_SESSION['user_id']]);
$serviceRows = $rows->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Services</title>
    <link rel="stylesheet" href="../css/supplier.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <main class="main-content">
            <div id="header"></div>

            <section>
                <h2>My Services</h2>

                <?php if ($message): ?>
                <div style="padding:16px 20px;border-radius:14px;margin-bottom:20px;background:<?= strpos($message, 'Error') !== false ? 'rgba(255,80,80,.12)' : 'rgba(100,255,150,.12)' ?>;border:1px solid <?= strpos($message, 'Error') !== false ? 'rgba(255,80,80,.3)' : 'rgba(100,255,150,.3)' ?>;color:<?= strpos($message, 'Error') !== false ? '#ff8b8b' : '#64ff96' ?>;">
                    <?= esc($message) ?>
                </div>
                <?php endif; ?>

                <form method="POST" style="background:var(--panel);border:1px solid var(--border);border-radius:24px;padding:28px;margin-bottom:30px;box-shadow:var(--shadow);">
                    <h3 style="font-size:20px;margin-bottom:18px;color:var(--gold);">Add New Service</h3>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:14px;margin-bottom:16px;">
                        <input name="name" placeholder="Service name" class="input-field" style="padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:whitesmoke;color:var(--text);outline:none;" required>
                        <select name="category" style="padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:whitesmoke;color:var(--text);outline:none;">
                            <option value="Venue">Venue</option>
                            <option value="Catering">Catering</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Host">Host</option>
                            <option value="Sounds & Lights">Sounds & Lights</option>
                            <option value="Photographer">Photographer</option>
                        </select>
                        <input name="style" placeholder="Style / cuisine (e.g. Filipino, Formal, Garden)" style="padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:whitesmoke;color:var(--text);outline:none;">
                        <input name="price" type="number" placeholder="Price (₱)" style="padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:whitesmoke;color:var(--text);outline:none;">
                        <input name="address" placeholder="Address / Location" style="padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:whitesmoke;color:var(--text);outline:none;">
                        <input name="latitude" placeholder="Latitude (optional)" style="padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:whitesmoke;color:var(--text);outline:none;">
                        <input name="longitude" placeholder="Longitude (optional)" style="padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:whitesmoke;color:var(--text);outline:none;">
                    </div>
                    <textarea name="description" placeholder="Description" style="width:100%;padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:whitesmoke;color:var(--text);outline:none;min-height:100px;margin-bottom:16px;resize:vertical;"></textarea>
                    <button type="submit" name="add_service" class="accept-btn" style="width:auto;padding:14px 28px;">Add Service</button>
                </form>

                <div class="services-grid">
                    <?php if (empty($serviceRows)): ?>
                    <div class="service-card" style="grid-column:1/-1;text-align:center;padding:60px 40px;">
                        <i class="fas fa-box-open" style="font-size:48px;color:var(--gold);margin-bottom:16px;"></i>
                        <h4>No services yet</h4>
                        <p>Add your first service using the form above</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($serviceRows as $s): ?>
                    <div class="service-card">
                        <img src="../userui/images/logo.png" alt="<?= esc($s['name']) ?>" />
                        <h4><?= esc($s['name']) ?></h4>
                        <p><?= esc($s['category']) ?></p>
                        <p style="color:var(--muted);font-size:14px;"><?= esc($s['description'] ?? 'No description') ?></p>
                        <p class="rating"><i class="fas fa-star"></i> <?= esc($s['rating'] ?? '5.0') ?></p>
                        <p style="color:var(--gold);font-weight:800;font-size:18px;">₱<?= number_format($s['price'] ?? 0) ?></p>
                        <a href="?delete=<?= $s['service_id'] ?>" onclick="return confirm('Delete this service?')" style="display:inline-block;margin-top:12px;color:#ff8b8b;text-decoration:none;font-size:13px;">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
    <script src="../js/header.js"></script>
</body>
</html>
