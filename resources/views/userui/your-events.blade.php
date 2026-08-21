<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EventIntel - Your Events</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/your-events.css') }}">
    <style>
        .pagination nav { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; }
        .pagination nav > div { display: flex; align-items: center; justify-content: center; gap: 8px; }
        .pagination nav > div > div { display: flex; align-items: center; gap: 8px; }
        .pagination ul, .pagination ol { display: flex; align-items: center; gap: 8px; margin: 0; padding: 0; list-style: none; }
        .pagination li { display: flex; list-style: none; }
        .pagination a, .pagination span { min-height: 40px; }
        .pagination a:hover { background: #fff5d7; }
        .pagination [aria-current="page"] span { background: #f6c84c; color: #242a2f; }
        .pagination [aria-disabled="true"] span { color: #b9b09a; background: #fafafa; }
    </style>
</head>
<body>
    <div class="your-events-page">
        @include('userui.partials.navbar', ['active' => 'events'])

        <main class="your-events-content">
            <header class="events-heading">
                <p class="events-eyebrow">Your event workspace</p>
                <h1>Your Events</h1>
                <p>Manage and track all your upcoming and completed events.</p>
            </header>

            <nav class="event-filters" aria-label="Filter events">
                @foreach ($counts as $filter => $count)
                    <a class="filter-chip {{ $status === $filter ? 'active' : '' }}" href="{{ route('your.events', ['status' => $filter, 'page' => 1]) }}">
                        {{ ucfirst($filter) }} <span>{{ $count }}</span>
                    </a>
                @endforeach
            </nav>

            @if ($events->isEmpty())
                <section class="empty-events"><i class="fas fa-calendar-plus" aria-hidden="true"></i><h2>No events yet</h2><p>Create your first event to start planning.</p><a href="{{ route('coordinator.events') }}">Create an event</a></section>
            @else
                <section class="events-grid" aria-label="Your events">
                    @foreach ($events as $event)
                        @php($eventStatus = strtolower(trim((string) ($event->status ?: 'planning'))))
                        <article class="event-card">
                            <div class="event-image"><img src="https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=800&q=80" alt="Event celebration"></div>
                            <div class="event-card-content">
                                <span class="event-status status-{{ $eventStatus }}">{{ ucfirst($event->status ?: 'Planning') }}</span>
                                <h2>{{ $event->title ?: 'Untitled event' }}</h2>
                                <div class="event-details">
                                    <span><i class="fas fa-calendar" aria-hidden="true"></i> {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('M j, Y') : 'Date TBD' }} {{ $event->event_time ? \Carbon\Carbon::parse($event->event_time)->format('g:i A') : '' }}</span>
                                    <span><i class="fas fa-users" aria-hidden="true"></i> {{ $event->guest_count ?: 0 }} guests</span>
                                </div>
                                <div class="event-actions">
                                    <a class="event-button" href="{{ route('your.events.guests', $event->event_id) }}">Guests / QR</a>
                                    <a class="event-button" href="{{ route('your.events.invitation', $event->event_id) }}">Edit Invitation</a>
                                    <a class="event-button" href="{{ route('your.events.map', $event->event_id) }}">GPS</a>
                                    <button class="event-button" type="button" data-status-event="{{ $event->event_id }}">Status</button>
                                    <a class="event-button" href="{{ route('your.messages', ['event_id' => $event->event_id]) }}">Messages</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>
                @if ($events->hasPages())
                    <div class="pagination">{{ $events->links() }}</div>
                @endif
            @endif
        </main>
    </div>

    <div class="events-modal" id="statusModal" aria-hidden="true">
        <div class="events-modal-content">
            <header><h2>Service Status</h2><button type="button" data-close-modal>&times;</button></header>
            <div id="statusContent"><p class="modal-loading">Loading service status...</p></div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const statusModal = document.getElementById('statusModal');
        const statusContent = document.getElementById('statusContent');

        document.querySelectorAll('[data-status-event]').forEach(button => button.addEventListener('click', async () => {
            statusModal.classList.add('show');
            statusModal.setAttribute('aria-hidden', 'false');
            statusContent.innerHTML = '<p class="modal-loading">Loading service status...</p>';
            const response = await fetch(`{{ url('/your-events') }}/${button.dataset.statusEvent}/status`, {headers: {'Accept': 'application/json'}});
            const data = await response.json();
            if (!data.services?.length) {
                statusContent.innerHTML = '<p class="modal-loading">No services assigned yet.</p>';
                return;
            }
            statusContent.innerHTML = `<div class="status-table">${data.services.map(service => `<div class="status-row"><div><strong>${service.name}</strong><small>${service.type}${service.price ? ` · ₱${Number(service.price).toLocaleString()}` : ''}</small></div><div class="status-actions"><span class="status-badge ${String(service.status).toLowerCase().replace(/\s+/g, '-')} ">${service.status}</span>${['accepted', 'proposal_accepted'].includes(String(service.status).toLowerCase()) ? `<button class="pay-service" data-event="${button.dataset.statusEvent}" data-service="${service.service_key}">Pay</button>` : ''}</div></div>`).join('')}</div>`;
            statusContent.querySelectorAll('.pay-service').forEach(payButton => payButton.addEventListener('click', () => payService(payButton)));
        }));

        async function payService(button) {
            const method = window.prompt('Enter payment method: cash or online', 'cash');
            if (!['cash', 'online'].includes(method)) return;
            const response = await fetch(`{{ url('/your-events') }}/${button.dataset.event}/pay`, {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'}, body: JSON.stringify({service_type: button.dataset.service, payment_method: method})});
            if (response.ok) window.location.reload();
        }

        document.querySelector('[data-close-modal]').addEventListener('click', closeModal);
        statusModal.addEventListener('click', event => { if (event.target === statusModal) closeModal(); });
        function closeModal() { statusModal.classList.remove('show'); statusModal.setAttribute('aria-hidden', 'true'); }
    </script>
</body>
</html>
