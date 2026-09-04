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
        .decline-note { margin-top: 8px; padding: 8px 10px; border-left: 3px solid #d9534f; border-radius: 6px; background: #fff1f1; color: #8b2622; font-size: 13px; line-height: 1.45; }
        .note-modal-text { margin: 0; padding: 18px; border-radius: 12px; background: #fff1f1; color: #8b2622; line-height: 1.6; white-space: pre-wrap; }
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

    <div class="events-modal" id="noteModal" aria-hidden="true">
        <div class="events-modal-content" style="max-width:520px;">
            <header><h2>Decline Note</h2><button type="button" data-close-note>&times;</button></header>
            <div id="noteContent" style="padding:16px;"></div>
        </div>
    </div>

    <div class="events-modal" id="paymentModal" aria-hidden="true">
        <div class="events-modal-content" style="max-width:560px;">
            <header><h2><i class="fas fa-coins" style="color:#f3c547;"></i> Pay for Service</h2><button type="button" data-close-payment>&times;</button></header>
            <div style="padding:16px;border-radius:14px;background:rgba(243,197,71,.08);border:1px solid rgba(243,197,71,.25);margin-bottom:18px;">
                <div style="font-size:14px;color:#666;">Service</div>
                <div id="payServiceName" style="font-size:18px;font-weight:700;color:#111;margin-bottom:6px;"></div>
                <div style="font-size:14px;color:#666;">Amount</div>
                <div id="payAmount" style="font-size:28px;font-weight:800;color:#f3c547;"></div>
            </div>
            <p style="font-size:14px;color:#555;margin-bottom:14px;">Choose how you would like to pay this supplier:</p>
            <label style="display:flex;gap:12px;align-items:center;padding:16px;border:2px solid rgba(243,197,71,.2);border-radius:14px;margin-bottom:10px;cursor:pointer;background:#fafafa;">
                <input type="radio" name="payment_method_choice" value="cash" style="width:18px;height:18px;accent-color:#f3c547;">
                <i class="fas fa-money-bill-wave" style="font-size:22px;color:#f3c547;"></i>
                <span><strong>Cash Payment</strong><small style="display:block;color:#888;">Pay directly on the day of the event</small></span>
            </label>
            <label style="display:flex;gap:12px;align-items:center;padding:16px;border:2px solid rgba(243,197,71,.2);border-radius:14px;margin-bottom:10px;cursor:pointer;background:#fafafa;">
                <input type="radio" name="payment_method_choice" value="online" style="width:18px;height:18px;accent-color:#f3c547;">
                <i class="fas fa-credit-card" style="font-size:22px;color:#f3c547;"></i>
                <span><strong>Online Payment</strong><small style="display:block;color:#888;">GCash / Bank transfer</small></span>
            </label>
            <div id="gcashSection" style="display:none;margin-top:14px;padding:16px;border-radius:14px;background:rgba(243,197,71,.05);border:1px dashed rgba(243,197,71,.4);text-align:center;">
                <p style="font-size:13px;color:#666;">After paying online, the supplier will be notified and can confirm your payment.</p>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:20px;">
                <button type="button" data-close-payment style="background:#eee;color:#333;padding:10px 20px;border:0;border-radius:10px;">Cancel</button>
                <button type="button" id="confirmPayment" style="background:linear-gradient(135deg,#ffe27d,#f3c547);color:#111;padding:10px 20px;border:0;border-radius:10px;">Confirm Payment</button>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const statusModal = document.getElementById('statusModal');
        const statusContent = document.getElementById('statusContent');
        const noteModal = document.getElementById('noteModal');
        const noteContent = document.getElementById('noteContent');
        const paymentModal = document.getElementById('paymentModal');
        let paymentContext = null;

        const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[character]));
        const statusKey = value => String(value ?? '').toLowerCase().replace(/\s+/g, '_');

        document.querySelectorAll('[data-status-event]').forEach(button => button.addEventListener('click', async () => {
            statusModal.classList.add('show');
            statusModal.setAttribute('aria-hidden', 'false');
            statusContent.innerHTML = '<p class="modal-loading">Loading service status...</p>';
            try {
                const response = await fetch(`{{ url('/your-events') }}/${button.dataset.statusEvent}/status`, {headers: {'Accept': 'application/json'}});
                const data = await response.json();
                if (!response.ok || !data.services?.length) {
                    statusContent.innerHTML = '<p class="modal-loading">No services assigned yet.</p>';
                    return;
                }
                const totalToPay = data.services.reduce((total, service) => {
                    const key = statusKey(service.status);
                    const isUnpaid = !['paid', 'declined', 'proposal_declined'].includes(key);
                    return isUnpaid && Number(service.price) > 0
                        ? total + Number(service.price)
                        : total;
                }, 0);
                const totalHtml = totalToPay > 0
                    ? `<div class="payment-info"><h3>Total to be paid:</h3><p style="font-size:22px;">₱${totalToPay.toLocaleString()}</p><small style="color:#888;">Payments are processed per service. Pay each accepted supplier directly from this list.</small></div>`
                    : '';
                statusContent.innerHTML = `<div class="status-table">${data.services.map(service => {
                    const key = statusKey(service.status);
                    const badgeClass = key === 'pending_confirmation' ? 'pending' : key.replace(/_/g, '-');
                    const canPay = ['accepted', 'proposal_accepted', 'payment_pending'].includes(key);
                    const messageUrl = service.supplier_user_id ? `{{ url('/messages') }}?event_id=${button.dataset.statusEvent}&user_id=${service.supplier_user_id}` : `{{ url('/messages') }}?event_id=${button.dataset.statusEvent}`;
                    const declineNote = ['declined', 'proposal_declined'].includes(key) && service.note
                        ? `<div class="decline-note"><strong>Decline note:</strong> ${escapeHtml(service.note)}</div>`
                        : '';
                    const isDeclined = ['declined', 'proposal_declined'].includes(key);
                    const noteButton = isDeclined && service.note
                        ? `<button class="pay-service view-note" type="button" data-note="${escapeHtml(service.note)}">View Note</button>`
                        : '';
                    const messageButton = isDeclined ? '' : `<a class="pay-service" href="${messageUrl}">Message</a>`;
                    return `<div class="status-row"><div><strong>${escapeHtml(service.name)}</strong><small>${escapeHtml(service.type)}${service.price ? ` · ₱${Number(service.price).toLocaleString()}` : ''}</small></div><div class="status-actions"><span class="status-badge ${badgeClass}" >${escapeHtml(service.status)}</span>${noteButton}${canPay ? `<button class="pay-service" data-event="${button.dataset.statusEvent}" data-service="${escapeHtml(service.service_key)}" data-price="${Number(service.price || 0)}" data-name="${escapeHtml(service.name)}">Pay</button>` : ''}${messageButton}</div></div>`;
                }).join('')}</div>${totalHtml}`;
                statusContent.querySelectorAll('.pay-service[data-service]').forEach(payButton => payButton.addEventListener('click', () => openPaymentModal(payButton)));
                statusContent.querySelectorAll('.view-note').forEach(noteButton => noteButton.addEventListener('click', () => {
                    noteContent.innerHTML = `<p class="note-modal-text">${escapeHtml(noteButton.dataset.note)}</p>`;
                    noteModal.classList.add('show');
                    noteModal.setAttribute('aria-hidden', 'false');
                }));
            } catch (error) {
                statusContent.innerHTML = '<p class="modal-loading">Error loading service status.</p>';
            }
        }));

        function openPaymentModal(button) {
            paymentContext = {event: button.dataset.event, service: button.dataset.service};
            document.getElementById('payServiceName').textContent = button.dataset.name;
            document.getElementById('payAmount').textContent = `₱${Number(button.dataset.price || 0).toLocaleString()}`;
            document.querySelectorAll('input[name="payment_method_choice"]').forEach(input => { input.checked = false; });
            document.getElementById('gcashSection').style.display = 'none';
            paymentModal.classList.add('show');
            paymentModal.setAttribute('aria-hidden', 'false');
        }

        document.querySelector('[data-close-modal]').addEventListener('click', closeModal);
        statusModal.addEventListener('click', event => { if (event.target === statusModal) closeModal(); });
        function closeModal() { statusModal.classList.remove('show'); statusModal.setAttribute('aria-hidden', 'true'); }

        document.querySelector('[data-close-note]').addEventListener('click', closeNoteModal);
        noteModal.addEventListener('click', event => { if (event.target === noteModal) closeNoteModal(); });
        function closeNoteModal() { noteModal.classList.remove('show'); noteModal.setAttribute('aria-hidden', 'true'); }

        document.querySelectorAll('[data-close-payment]').forEach(button => button.addEventListener('click', closePaymentModal));
        paymentModal.addEventListener('click', event => { if (event.target === paymentModal) closePaymentModal(); });
        document.querySelectorAll('input[name="payment_method_choice"]').forEach(input => input.addEventListener('change', event => {
            document.getElementById('gcashSection').style.display = event.target.value === 'online' ? 'block' : 'none';
        }));
        document.getElementById('confirmPayment').addEventListener('click', async () => {
            const method = document.querySelector('input[name="payment_method_choice"]:checked');
            if (!method || !paymentContext) return alert('Please choose a payment method.');
            const confirmButton = document.getElementById('confirmPayment');
            confirmButton.disabled = true;
            try {
                const response = await fetch(`{{ url('/your-events') }}/${paymentContext.event}/pay`, {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'}, body: JSON.stringify({service_type: paymentContext.service, payment_method: method.value})});
                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Payment could not be recorded.');
                }
                window.location.reload();
            } catch (error) {
                alert(error.message);
                confirmButton.disabled = false;
            }
        });
        function closePaymentModal() { paymentModal.classList.remove('show'); paymentModal.setAttribute('aria-hidden', 'true'); paymentContext = null; }
    </script>
</body>
</html>
