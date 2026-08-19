<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Coordinator Panel')</title>
    @vite(['resources/css/coordinator.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .nav-menu a { display:block; width:100%; padding:14px 18px; border:1px solid var(--border); border-radius:14px; background:transparent; color:var(--text); font-size:14px; font-weight:800; letter-spacing:.5px; text-decoration:none; transition:.3s; }
        .nav-menu a:hover, .nav-menu li.active a { background:rgba(212,175,55,.1); color:#d4af37; border-color:var(--border2); box-shadow:0 0 14px rgba(212,175,55,.15); transform:translateX(5px); }
        .sidebar-footer .logout-btn { display:block; width:100%; border:0; background:transparent; color:var(--danger); font:inherit; text-align:left; cursor:pointer; }
    </style>
    @yield('styles')
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="brand"><h1><span class="blue-text">Event</span><span class="pink-text">Intel</span></h1><div class="user-info"><strong>{{ Auth::user()->full_name ?? 'Coordinator' }}</strong><span class="supplier"><i class="fas fa-circle"></i> Coordinator</span></div></div>
        <nav class="nav-menu"><ul>
            <li class="{{ request()->routeIs('coordinator.dashboard') ? 'active' : '' }}"><a href="{{ route('coordinator.dashboard') }}">DASHBOARD</a></li>
            <li class="{{ request()->routeIs('coordinator.events') ? 'active' : '' }}"><a href="{{ route('coordinator.events') }}">ASSIGNED EVENTS</a></li>
            <li class="{{ request()->routeIs('coordinator.proposals') ? 'active' : '' }}"><a href="{{ route('coordinator.proposals') }}">PROPOSALS</a></li>
            <li class="{{ request()->routeIs('coordinator.packages') ? 'active' : '' }}"><a href="{{ route('coordinator.packages') }}">PACKAGES</a></li>
            <li><a href="{{ route('coordinator.messages') }}">MESSAGES</a></li>
            <li class="{{ request()->routeIs('coordinator.suppliers') ? 'active' : '' }}"><a href="{{ route('coordinator.suppliers') }}">MY SUPPLIERS</a></li>
            <li class="{{ request()->routeIs('coordinator.reports') ? 'active' : '' }}"><a href="{{ route('coordinator.reports') }}">REPORTS</a></li>
            <li class="{{ request()->routeIs('coordinator.settings') ? 'active' : '' }}"><a href="{{ route('coordinator.settings') }}">SETTINGS</a></li>
        </ul></nav>
        <form method="POST" action="{{ route('logout') }}" class="sidebar-footer">@csrf<button class="logout-btn" type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button></form>
    </aside>
    <main class="main-content">@yield('content')</main>
</div>
@yield('scripts')
</body>
</html>
