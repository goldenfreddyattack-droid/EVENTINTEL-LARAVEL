<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Supplier Panel')</title>
    <link rel="stylesheet" href="{{ asset('css/supplier.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    @yield('styles')
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="brand">
                <button class="menu-btn" type="button" aria-label="Toggle menu"><i class="fas fa-bars"></i></button>
                <h1><span class="blue-text">Event</span><span class="pink-text">Intel</span></h1>
                <div class="user-info">
                    <strong>{{ Auth::user()->full_name ?? Auth::user()->username ?? 'Supplier' }}</strong>
                    <span class="supplier"><i class="fas fa-circle"></i> Supplier</span>
                </div>
            </div>

            <nav class="nav-menu">
                <ul>
                    <li class="{{ request()->routeIs('supplier.dashboard') ? 'active' : '' }}">
                        <button type="button" onclick="location.href='{{ route('supplier.dashboard') }}'">DASHBOARD</button>
                    </li>
                    <li class="{{ request()->routeIs('supplier.setup*') ? 'active' : '' }}">
                        <button type="button" onclick="location.href='{{ route('supplier.setup') }}'">SETUP</button>
                    </li>
                    <li class="{{ request()->routeIs('supplier.feed') ? 'active' : '' }}">
                        <button type="button" onclick="location.href='{{ route('supplier.feed') }}'">NEWSFEED</button>
                    </li>
                    <li class="{{ request()->routeIs('supplier.bookings') ? 'active' : '' }}">
                        <button type="button" onclick="location.href='{{ route('supplier.bookings') }}'">BOOKINGS</button>
                    </li>
                    <li class="{{ request()->routeIs('supplier.services') ? 'active' : '' }}">
                        <button type="button" onclick="location.href='{{ route('supplier.services') }}'">SERVICES</button>
                    </li>
                    <li class="{{ request()->routeIs('supplier.messages') ? 'active' : '' }}">
                        <button type="button" onclick="location.href='{{ route('supplier.messages') }}'">MESSAGES</button>
                    </li>
                    <li class="{{ request()->routeIs('supplier.reviews') ? 'active' : '' }}">
                        <button type="button" onclick="location.href='{{ route('supplier.reviews') }}'">REVIEWS</button>
                    </li>
                    <li class="{{ request()->routeIs('supplier.settings') ? 'active' : '' }}">
                        <button type="button" onclick="location.href='{{ route('supplier.settings') }}'">SETTINGS</button>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Are you sure you want to logout?');">
                            @csrf
                            <button type="submit" style="background:rgba(255,80,80,.08);color:#ff8b8b;border-color:rgba(255,80,80,.28);">LOGOUT</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>
