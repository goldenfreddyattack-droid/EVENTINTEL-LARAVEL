@php($active = $active ?? '')

<nav class="userui-navbar">
    <div class="userui-logo">EventIntel</div>
    <div class="userui-nav-links">
        @if (request()->routeIs('home'))
            <span class="userui-welcome">Welcome, {{ Auth::user()->full_name ?? Auth::user()->name ?? 'User' }}!</span>
        @endif
        <a class="{{ $active === 'home' ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
        <a class="{{ $active === 'create-event' ? 'active' : '' }}" href="{{ route('coordinator.events') }}">Create Event</a>
        <a class="{{ $active === 'events' ? 'active' : '' }}" href="{{ route('your.events') }}">Your Events</a>
        <a class="{{ $active === 'recommendation' ? 'active' : '' }}" href="{{ route('recommendation') }}">Recommendations</a>
        <a class="{{ $active === 'packages' ? 'active' : '' }}" href="{{ route('packages') }}">Packages</a>
        <a class="{{ $active === 'newsfeed' ? 'active' : '' }}" href="{{ route('newsfeed') }}">Newsfeed</a>
        <a class="userui-profile" href="{{ route('profile.show') }}" aria-label="Profile" title="Profile">
            <i class="fas fa-user"></i>
        </a>
    </div>
</nav>
