<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>
    @vite(['resources/css/coordinator.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .admin-card, .admin-panel, .admin-table-wrap { background:var(--panel); border:1px solid var(--border); border-radius:30px; box-shadow:var(--shadow); }
        .admin-card { padding:24px; }
        .admin-card .num { color:var(--gold); font-size:42px; font-weight:900; }
        .admin-card p { margin:12px 0 0; color:var(--muted); }
        .admin-panel { padding:30px; }
        .admin-panel-head { margin-bottom:24px; }
        .admin-panel-head h2 { margin:0 0 8px; font-size:32px; }
        .admin-panel-head p { margin:0; color:var(--muted); line-height:1.6; }
        .admin-button { display:inline-flex; align-items:center; gap:10px; padding:11px 18px; border:1px solid var(--gold); border-radius:14px; background:linear-gradient(135deg,var(--gold2),var(--gold),var(--gold3)); color:#111; font-weight:800; text-decoration:none; cursor:pointer; }
        .nav-menu a { display:block; width:100%; padding:11px 18px; border:1px solid var(--border); border-radius:14px; background:transparent; color:var(--text); font-size:13px; font-weight:600; letter-spacing:.5px; text-decoration:none; transition:.3s; }
        .nav-menu a:hover, .nav-menu li.active a { background:rgba(212,175,55,.1); color:var(--gold); border-color:var(--border2); box-shadow:0 0 14px rgba(212,175,55,.15); transform:translateX(5px); }
        .admin-topbar { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin-bottom:28px; }
        .admin-topbar h1 { margin:0 0 10px; }
        .admin-topbar p { max-width:700px; margin:0; color:var(--muted); line-height:1.7; }
        .admin-cards { display:grid; grid-template-columns:repeat(3,minmax(180px,1fr)); gap:18px; margin-bottom:28px; }
        .admin-alert { padding:14px 18px; border-radius:14px; margin-bottom:22px; }
        .admin-alert.success { background:#e8f7ed; border:1px solid #b7e3c3; color:#24743b; }
        .admin-alert.error { background:#fff0f0; border:1px solid #f0bcbc; color:#a52a2a; }
        .nav-menu .logout-btn { width:100%; padding:11px 18px; font-size:13px; font-weight:600; letter-spacing:.5px; text-align:left; background:rgba(255,80,80,.08); color:#ff8b8b; border-color:rgba(255,80,80,.28); }
        .admin-table-wrap { overflow-x:auto; }
        @media(max-width:980px) { .admin-cards { grid-template-columns:1fr 1fr; } }
        @media(max-width:700px) { .admin-topbar { flex-direction:column; } }
    </style>
    @yield('styles')
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="brand">
            <h1><span class="blue-text">Event</span><span class="pink-text">Intel</span></h1>
            <div class="user-info">
                <span class="supplier"><i class="fas fa-circle"></i> Administrator</span>
            </div>
        </div>
        <nav class="nav-menu"><ul>
            <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><a href="{{ route('admin.dashboard') }}">DASHBOARD</a></li>
            <li class="{{ request()->routeIs('admin.requests') ? 'active' : '' }}"><a href="{{ route('admin.requests') }}">VERIFICATION REQUESTS</a></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-btn" type="submit">LOGOUT</button>
                </form>
            </li>
        </ul></nav>
    </aside>
    <main class="main-content">
        @yield('content')
    </main>
</div>
@yield('scripts')
</body>
</html>
