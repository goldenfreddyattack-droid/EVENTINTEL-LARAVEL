@php
    $active = $active ?? '';
    $disabled = $disabled ?? false;
    $activeEventLimitReached = false;
    if (Auth::check()) {
        $activeEventCount = \Illuminate\Support\Facades\DB::table('events')
            ->where('user_id', Auth::id())
            ->whereIn('status', ['planning', 'pending', 'ongoing'])
            ->count();
        $activeEventLimitReached = $activeEventCount >= 3;
    }
@endphp

<nav class="userui-navbar">
    <div class="userui-logo" aria-label="EventIntel"><span class="brand-event">Event</span><span class="brand-intel">Intel</span></div>
    <div class="userui-nav-links">
        @if (request()->routeIs('home'))
            <span class="userui-welcome">Welcome, {{ Auth::user()->full_name ?? Auth::user()->name ?? 'User' }}!</span>
        @endif
        @if ($disabled)
            <span class="{{ $active === 'home' ? 'active' : '' }}">Home</span>
            <span class="{{ $active === 'create-event' ? 'active' : '' }}">Create Event</span>
            <span class="{{ $active === 'events' ? 'active' : '' }}">Your Events</span>
            <span class="{{ $active === 'recommendation' ? 'active' : '' }}">AI Recommendation</span>
            <span class="{{ $active === 'packages' ? 'active' : '' }}">Packages</span>
            <span class="{{ $active === 'newsfeed' ? 'active' : '' }}">Newsfeed</span>
            <span class="userui-profile" aria-label="Profile" title="Profile">
                <i class="fas fa-user"></i>
            </span>
        @else
            <a class="{{ $active === 'home' ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
            <a id="navbarCreateEvent" class="{{ $active === 'create-event' ? 'active' : '' }}" href="{{ route('events.create') }}" data-active-event-limit="{{ $activeEventLimitReached ? '1' : '0' }}">Create Event</a>
            <a class="{{ $active === 'events' ? 'active' : '' }}" href="{{ route('your.events') }}">Your Events</a>
            <a class="{{ $active === 'recommendation' ? 'active' : '' }}" href="{{ route('recommendation') }}">AI Recommendation</a>
            <a class="{{ $active === 'packages' ? 'active' : '' }}" href="{{ route('packages') }}">Packages</a>
            <a class="{{ $active === 'newsfeed' ? 'active' : '' }}" href="{{ route('newsfeed') }}">Newsfeed</a>
            <a class="userui-profile" href="{{ route('profile.show') }}" aria-label="Profile" title="Profile">
                <i class="fas fa-user"></i>
            </a>
        @endif
    </div>
</nav>

@if ($activeEventLimitReached && !$disabled)
    <div class="homepage-modal-overlay" id="planningLimitModal" role="dialog" aria-modal="true" aria-labelledby="planningLimitTitle">
        <div class="homepage-modal-card">
            <button class="homepage-modal-close" id="closePlanningLimitModal" type="button" aria-label="Close">×</button>
            <div class="homepage-modal-icon" aria-hidden="true"><i class="fas fa-exclamation-triangle"></i></div>
            <h2 id="planningLimitTitle">Three active events already</h2>
            <p>You already have three planning, pending, or ongoing events. Finish one before creating another event.</p>
            <div class="homepage-modal-actions">
                <a class="homepage-modal-button secondary" href="{{ route('your.events') }}"><i class="fas fa-list-alt"></i> View Events</a>
                <button class="homepage-modal-button primary" id="dismissPlanningLimit" type="button">OK</button>
            </div>
        </div>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const navbarCreateEvent = document.getElementById('navbarCreateEvent');
        const homepageCreateEvent = document.getElementById('homepageCreateEvent');
        const planningLimitModal = document.getElementById('planningLimitModal');
        const closePlanningLimitModal = document.getElementById('closePlanningLimitModal');
        const dismissPlanningLimit = document.getElementById('dismissPlanningLimit');

        const planningCreateLinks = [navbarCreateEvent, homepageCreateEvent].filter(Boolean);

        planningCreateLinks.forEach((link) => {
            if (planningLimitModal && link.dataset.activeEventLimit === '1') {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    planningLimitModal.style.display = 'flex';
                });
            }
        });

        if (closePlanningLimitModal) {
            closePlanningLimitModal.addEventListener('click', () => {
                if (planningLimitModal) planningLimitModal.style.display = 'none';
            });
        }

        if (dismissPlanningLimit) {
            dismissPlanningLimit.addEventListener('click', () => {
                if (planningLimitModal) planningLimitModal.style.display = 'none';
            });
        }
    });
</script>
