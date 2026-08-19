<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --gold:#d4a017; --gold2:#ffe08a; --gold3:#c99208; --text:#222; --muted:#666; --border:#e2e8f0; --shadow:0 10px 25px rgba(0,0,0,.08); }
        * { box-sizing:border-box; font-family:'Segoe UI', Arial, sans-serif; }
        body { margin:0; min-height:100vh; background:#f4f6f9; color:var(--text); }
        .admin-wrap { min-height:100vh; }
        .admin-side { position:fixed; inset:0 auto 0 0; z-index:10; width:280px; min-width:280px; height:100vh; overflow-y:auto; padding:30px 22px; background:#fff; border-right:1px solid var(--border); }
        .admin-brand { margin-bottom:22px; color:var(--gold); font-size:30px; font-weight:900; }
        .admin-user { margin-bottom:6px; font-weight:700; }
        .admin-role { display:inline-flex; align-items:center; gap:8px; padding:9px 14px; border:1px solid rgba(212,160,23,.25); border-radius:999px; color:var(--gold); font-weight:700; }
        .admin-role::before { content:''; width:9px; height:9px; border-radius:50%; background:var(--gold); }
        .admin-divider { height:1px; margin:28px 0 20px; border:0; background:rgba(212,160,23,.25); }
        .admin-nav { display:flex; flex-direction:column; gap:10px; }
        .admin-nav a { display:block; padding:14px 16px; border:1px solid #111; border-radius:14px; color:#222; text-decoration:none; transition:.2s; }
        .admin-nav a:hover, .admin-nav a.active { background:#fff7df; border-color:var(--gold); color:var(--gold); }
        .admin-main { min-width:0; margin-left:280px; padding:28px 34px; }
        .admin-topbar { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin-bottom:28px; }
        .admin-topbar h1 { margin:0 0 10px; font-size:clamp(30px,4vw,44px); }
        .admin-topbar p { max-width:700px; margin:0; color:var(--muted); line-height:1.7; }
        .admin-button { display:inline-flex; align-items:center; gap:10px; padding:11px 18px; border:1px solid var(--gold); border-radius:14px; background:linear-gradient(135deg,var(--gold2),var(--gold),var(--gold3)); color:#111; font-weight:800; text-decoration:none; cursor:pointer; }
        .admin-cards { display:grid; grid-template-columns:repeat(3,minmax(180px,1fr)); gap:18px; margin-bottom:28px; }
        .admin-card, .admin-panel { background:#fff; border:1px solid #e5e5e5; border-radius:22px; box-shadow:var(--shadow); }
        .admin-card { padding:24px; }
        .admin-card .num { color:var(--gold); font-size:42px; font-weight:900; }
        .admin-card p { margin:12px 0 0; color:var(--muted); }
        .admin-panel { padding:28px; }
        .admin-panel-head { margin-bottom:24px; }
        .admin-panel-head h2 { margin:0 0 8px; font-size:32px; }
        .admin-panel-head p { margin:0; color:var(--muted); line-height:1.6; }
        .admin-alert { padding:14px 18px; border-radius:14px; margin-bottom:22px; }
        .admin-alert.success { background:#e8f7ed; border:1px solid #b7e3c3; color:#24743b; }
        .admin-alert.error { background:#fff0f0; border:1px solid #f0bcbc; color:#a52a2a; }
        @media(max-width:980px) { .admin-cards { grid-template-columns:1fr 1fr; } }
        @media(max-width:700px) { .admin-side { position:static; width:100%; min-width:0; height:auto; overflow:visible; } .admin-main { margin-left:0; padding:22px; } .admin-topbar { flex-direction:column; } }
    </style>
    @yield('styles')
</head>
<body>
<div class="admin-wrap">
    <aside class="admin-side">
        <div class="admin-brand">EventIntel</div>
        <span class="admin-role">Admin</span>
        <hr class="admin-divider">
        <nav class="admin-nav">
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a class="{{ request()->routeIs('admin.requests') ? 'active' : '' }}" href="{{ route('admin.requests') }}"><i class="fas fa-user-check"></i> Verification Requests</a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button class="admin-button" type="submit" style="width:100%;justify-content:flex-start;background:#fff0f0;border-color:#f0bcbc;color:#c44;"><i class="fas fa-right-from-bracket"></i> Logout</button>
            </form>
        </nav>
    </aside>
    <main class="admin-main">
        @yield('content')
    </main>
</div>
@yield('scripts')
</body>
</html>
