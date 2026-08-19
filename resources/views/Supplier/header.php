<div class="navbar">
  <div class="logo-text">EventIntel</div>

  <div class="nav-links">
    <button onclick="window.location.href='<?= route('supplier.dashboard') ?>'">Home</button>
    <button onclick="window.location.href='<?= route('supplier.services') ?>'">Manage Services</button>
    <button onclick="window.location.href='<?= route('supplier.bookings') ?>'">Bookings</button>
    <button onclick="window.location.href='<?= route('supplier.reviews') ?>'">Reviews</button>
    <button onclick="window.location.href='<?= route('supplier.feed') ?>'">Newsfeed</button>
    <div class="profile-btn" onclick="window.location.href='<?= route('supplier.profile') ?>'" title="Profile">
      <i class="fa-regular fa-user"></i>
    </div>
    <div class="profile-btn" onclick="window.location.href='<?= route('logout') ?>'" title="Logout">
      <i class="fas fa-sign-out-alt"></i>
    </div>
  </div>
</div>

<style>
  .navbar { 
    width: 100%;
    padding: 12px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border-bottom: 1px solid rgba(243,197,71,0.08);
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  }

  .logo-text {
    font-size: 22px;
    font-weight: 800;
    color: #f3c547;
  }

  .nav-links { display:flex; align-items:center; gap:14px; }
  .nav-links button { padding:8px 14px; border-radius:12px; border:1px solid rgba(243,197,71,0.18); background:transparent; color:#222; cursor:pointer; }
  .nav-links button:hover { background: rgba(243,197,71,0.12); color:#000; }
  .profile-btn { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; background: #fff; border:1px solid rgba(243,197,71,0.18); color:#f3c547; cursor:pointer; }
</style>
