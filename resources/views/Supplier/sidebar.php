<?php
// Supplier sidebar - included by all supplier pages
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="brand">
        <h1><span class="blue-text">Event</span><span class="pink-text">Intel</span></h1>
        <div class="user-info">
            <strong><?= esc($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Supplier') ?></strong>
            <span class="supplier"><i class="fas fa-circle"></i> Supplier</span>
        </div>
    </div>
    <nav class="nav-menu">
        <ul>
            <li class="<?= $currentPage === 'DASHBOARD.php' ? 'active' : '' ?>"><button onclick="location.href='<?= route('supplier.dashboard') ?>'">DASHBOARD</button></li>
            <li><button onclick="location.href='<?= route('supplier.setup') ?>'">SETUP</button></li>
            <li><button onclick="location.href='<?= route('supplier.feed') ?>'">NEWSFEED</button></li>
            <li class="<?= $currentPage === 'BOOKINGS.php' ? 'active' : '' ?>"><button onclick="location.href='<?= route('supplier.bookings') ?>'">BOOKINGS</button></li>
            <li class="<?= $currentPage === 'SERVICES.php' ? 'active' : '' ?>"><button onclick="location.href='<?= route('supplier.services') ?>'">SERVICES</button></li>
            <li class="<?= $currentPage === 'MESSAGES.php' ? 'active' : '' ?>"><button onclick="location.href='<?= route('supplier.messages') ?>'">MESSAGES</button></li>
            <li class="<?= $currentPage === 'REVIEWS.php' ? 'active' : '' ?>"><button onclick="location.href='<?= route('supplier.reviews') ?>'">REVIEWS</button></li>
            <li class="<?= $currentPage === 'SETTINGS.php' ? 'active' : '' ?>"><button onclick="location.href='<?= route('supplier.settings') ?>'">SETTINGS</button></li>
            <li>
                <form method="POST" action="<?= route('logout') ?>" onsubmit="return confirm('Are you sure you want to logout?');">
                    <?= csrf_field() ?>
                    <button type="submit" style="background:rgba(255,80,80,.1);color:#ff8b8b;">LOGOUT</button>
                </form>
            </li>
        </ul>
    </nav>
</aside>
