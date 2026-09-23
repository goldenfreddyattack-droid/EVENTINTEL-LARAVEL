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
        .star-btn:hover { transform: scale(1.15); }
        .supplier-review-form textarea:focus { border-color: #f3c547 !important; outline: none; box-shadow: 0 0 0 3px rgba(243, 197, 71, 0.15); }
        .flow-warning-toast { position: fixed; right: 24px; bottom: 24px; z-index: 1000; max-width: 360px; padding: 14px 18px; border: 1px solid #e3b52d; border-radius: 10px; background: #fff8dc; color: #765400; box-shadow: 0 10px 28px rgba(50, 39, 10, .18); font-weight: 700; opacity: 0; transform: translateY(12px); pointer-events: none; transition: opacity .2s ease, transform .2s ease; }
        .flow-warning-toast.show { opacity: 1; transform: translateY(0); }
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
                                    @if ((int) session('recommendation_flow_event_id') === (int) $event->event_id)
                                        <a class="event-button" href="{{ route('recommendation', ['event_id' => $event->event_id]) }}">My Flow</a>
                                    @else
                                        <button class="event-button" type="button" aria-disabled="true" data-flow-warning title="Generate a Flow first on the AI Recommendation Page">My Flow</button>
                                    @endif
                                    <button class="event-button" type="button" data-status-event="{{ $event->event_id }}">Status</button>
                                    @if (in_array($eventStatus, ['ongoing', 'completed'], true))
                                        <button class="event-button" type="button" data-review-event="{{ $event->event_id }}">⭐ Review Folder</button>
                                        <button class="event-button" type="button" data-qr-event="{{ $event->event_id }}">🔗 QR & Link</button>
                                    @endif
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
                <span><strong>Online Payment</strong><small style="display:block;color:#888;">GCash / Maya / Card</small></span>
            </label>
            <div id="gcashSection" style="display:none;margin-top:14px;padding:16px;border-radius:14px;background:rgba(243,197,71,.05);border:1px dashed rgba(243,197,71,.4);text-align:center;">
                <p style="font-size:13px;color:#666;">Scan the QR code with GCash, Maya, or a supported bank app. The QR expires after 30 minutes.</p>
                <div id="paymentQrResult" style="display:none;margin-top:14px;">
                    <p style="font-size:13px;color:#555;margin-bottom:10px;">Scan this QR Ph code to pay securely.</p>
                    <img id="paymentQr" width="220" height="220" alt="Payment checkout QR code" style="display:block;width:220px;height:220px;margin:0 auto 12px;background:#fff;padding:8px;border-radius:8px;">
                    <span style="display:block;color:#856404;font-weight:700;">Use GCash, Maya, or your bank app to scan.</span>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:20px;">
                <button type="button" data-close-payment style="background:#eee;color:#333;padding:10px 20px;border:0;border-radius:10px;">Cancel</button>
                <button type="button" id="confirmPayment" style="background:linear-gradient(135deg,#ffe27d,#f3c547);color:#111;padding:10px 20px;border:0;border-radius:10px;">Confirm Payment</button>
            </div>
        </div>
    </div>

    <!-- Review Folder Modal -->
    <div class="events-modal" id="reviewModal" aria-hidden="true">
        <div class="events-modal-content" style="max-width: 600px;">
            <header>
                <h2><i class="fas fa-folder-open" style="color:#f3c547;"></i> Event Supplier Reviews Folder</h2>
                <button type="button" data-close-review>&times;</button>
            </header>
            <div id="reviewContent" style="padding: 16px; max-height: 70vh; overflow-y: auto;">
                <p class="modal-loading">Loading reviewable suppliers...</p>
            </div>
        </div>
    </div>

    <!-- QR Code & Link Share Modal -->
    <div class="events-modal" id="qrModal" aria-hidden="true">
        <div class="events-modal-content" style="max-width: 440px; text-align: center;">
            <header>
                <h2>Review Folder QR & Link</h2>
                <button type="button" data-close-qr>&times;</button>
            </header>
            <div style="padding: 20px;">
                <p style="font-size: 13px; color: #666; margin-bottom: 14px;">Scan this QR code or copy the link to rate suppliers directly:</p>
                <img id="qrCodeImage" src="" alt="Review Folder QR Code" style="width: 200px; height: 200px; margin: 0 auto 14px; display: block; background: #fff; padding: 6px; border: 1px solid #ddd; border-radius: 8px;">
                <input type="text" id="reviewFolderLinkInput" readonly style="width: 100%; padding: 8px; font-size: 12px; border: 1px solid #ccc; border-radius: 6px; background: #f9f9f9; text-align: center; margin-bottom: 12px;">
                <button type="button" id="copyReviewLinkBtn" style="background: #f3c547; border: 0; padding: 8px 16px; border-radius: 6px; font-weight: 700; cursor: pointer;">Copy Link</button>
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

        document.querySelectorAll('[data-flow-warning]').forEach(button => button.addEventListener('click', () => {
            let toast = document.getElementById('flowWarningToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'flowWarningToast';
                toast.className = 'flow-warning-toast';
                document.body.appendChild(toast);
            }

            toast.textContent = 'You did not generate a flow for this event. Please generate it on the AI Recommendation Page.';
            toast.classList.add('show');
            window.clearTimeout(window.flowWarningTimeout);
            window.flowWarningTimeout = window.setTimeout(() => toast.classList.remove('show'), 4500);
        }));

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
                    const canPay = ['payment_pending', 'accepted', 'proposal_accepted', 'pending_verification'].includes(key);
                    const messageUrl = service.supplier_user_id ? `{{ url('/messages') }}?event_id=${button.dataset.statusEvent}&user_id=${service.supplier_user_id}` : `{{ url('/messages') }}?event_id=${button.dataset.statusEvent}`;
                    const isDeclined = ['declined', 'proposal_declined'].includes(key);
                    const noteButton = isDeclined && service.note
                        ? `<button class="pay-service view-note" type="button" data-note="${escapeHtml(service.note)}">View Note</button>`
                        : '';
                    const reselectButton = isDeclined
                        ? `<button class="pay-service reselect-service" type="button" data-event="${button.dataset.statusEvent}" data-service="${escapeHtml(service.service_key)}">Re-select</button>`
                        : '';
                    const messageButton = isDeclined ? '' : `<a class="pay-service" href="${messageUrl}">Message</a>`;
                    return `<div class="status-row"><div><strong>${escapeHtml(service.name)}</strong><small>${escapeHtml(service.type)}${service.price ? ` · ₱${Number(service.price).toLocaleString()}` : ''}</small></div><div class="status-actions"><span class="status-badge ${badgeClass}" >${escapeHtml(service.status)}</span>${noteButton}${reselectButton}${canPay ? `<button class="pay-service" data-event="${button.dataset.statusEvent}" data-service="${escapeHtml(service.service_key)}" data-price="${Number(service.price || 0)}" data-name="${escapeHtml(service.name)}">Pay</button>` : ''}${messageButton}</div></div>`;
                }).join('')}</div>${totalHtml}`;
                statusContent.querySelectorAll('.pay-service[data-service][data-price]').forEach(payButton => payButton.addEventListener('click', () => openPaymentModal(payButton)));
                statusContent.querySelectorAll('.view-note').forEach(noteButton => noteButton.addEventListener('click', () => {
                    noteContent.innerHTML = `<p class="note-modal-text">${escapeHtml(noteButton.dataset.note)}</p>`;
                    noteModal.classList.add('show');
                    noteModal.setAttribute('aria-hidden', 'false');
                }));
                statusContent.querySelectorAll('.reselect-service[data-service]').forEach(reselectButton => reselectButton.addEventListener('click', async () => {
                    const serviceType = reselectButton.dataset.service;
                    try {
                        const response = await fetch(`{{ url('/your-events') }}/${button.dataset.statusEvent}/reselect`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({service_type: serviceType})
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Unable to clear the declined service.');
                        }
                        const serviceCatalogUrl = `{{ url('/services') }}/${serviceType}?return=${encodeURIComponent('{{ route('your.events') }}')}&reselect=1&event_id=${encodeURIComponent(button.dataset.statusEvent)}`;
                        window.location.href = serviceCatalogUrl;
                    } catch (error) {
                        alert(error.message);
                    }
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
            document.getElementById('paymentQrResult').style.display = 'none';
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
                if (method.value === 'online') {
                    const amount = Number(document.getElementById('payAmount').textContent.replace(/[^0-9.-]+/g, '')) || 0;
                    const checkoutResponse = await fetch(`{{ url('/payments/gcash/create') }}`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'},
                        body: JSON.stringify({
                            event_id: Number(paymentContext.event),
                            service_type: paymentContext.service,
                            amount: amount,
                            success_url: `{{ url('/your-events') }}?payment_status=success&event_id=${encodeURIComponent(paymentContext.event)}&service=${encodeURIComponent(paymentContext.service)}`,
                            cancel_url: `{{ url('/your-events') }}?payment_status=cancelled&event_id=${encodeURIComponent(paymentContext.event)}&service=${encodeURIComponent(paymentContext.service)}`
                        })
                    });
                    const paymentData = await checkoutResponse.json().catch(() => ({}));
                    const payment = paymentData.payment || {};
                    if (!checkoutResponse.ok || !paymentData.success || !payment.payment_intent_id || !payment.client_key || !payment.public_key) {
                        throw new Error(payment.message || paymentData.message || 'QR Ph payment could not be started.');
                    }

                    const paymongoBaseUrl = @json(config('services.paymongo.base_url'));
                    const paymongoHeaders = {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Basic ${btoa(`${payment.public_key}:`)}`
                    };
                    const paymentMethodResponse = await fetch(`${paymongoBaseUrl}/payment_methods`, {
                        method: 'POST',
                        headers: paymongoHeaders,
                        body: JSON.stringify({data: {attributes: {type: 'qrph'}}})
                    });
                    const paymentMethodData = await paymentMethodResponse.json().catch(() => ({}));
                    const paymentMethodId = paymentMethodData.data?.id;
                    if (!paymentMethodResponse.ok || !paymentMethodId) {
                        throw new Error(paymentMethodData.errors?.[0]?.detail || 'PayMongo could not create the QR Ph payment method.');
                    }

                    const attachResponse = await fetch(`${paymongoBaseUrl}/payment_intents/${encodeURIComponent(payment.payment_intent_id)}/attach`, {
                        method: 'POST',
                        headers: paymongoHeaders,
                        body: JSON.stringify({data: {attributes: {payment_method: paymentMethodId, client_key: payment.client_key}}})
                    });
                    const attachData = await attachResponse.json().catch(() => ({}));
                    const qrImage = attachData.data?.attributes?.next_action?.code?.image_url;
                    if (!attachResponse.ok || !qrImage) {
                        throw new Error(attachData.errors?.[0]?.detail || 'PayMongo could not generate the QR Ph code.');
                    }

                    document.getElementById('paymentQr').src = qrImage.startsWith('data:') ? qrImage : `data:image/png;base64,${qrImage}`;
                    document.getElementById('paymentQrResult').style.display = 'block';
                    confirmButton.textContent = 'QR Code Ready';
                    return;
                }

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

        // --- Review Folder Modal Logic ---
        const reviewModal = document.getElementById('reviewModal');
        const reviewContent = document.getElementById('reviewContent');

        document.querySelectorAll('[data-review-event]').forEach(button => {
            button.addEventListener('click', async () => {
                const eventId = button.dataset.reviewEvent;
                reviewModal.classList.add('show');
                reviewModal.setAttribute('aria-hidden', 'false');
                reviewContent.innerHTML = '<p class="modal-loading">Loading suppliers folder...</p>';

                try {
                    const response = await fetch(`{{ url('/your-events') }}/${eventId}/reviews`, {headers: {'Accept': 'application/json'}});
                    const data = await response.json();

                    if (!response.ok || !data.services?.length) {
                        reviewContent.innerHTML = '<p class="modal-loading">No suppliers selected for this event yet to review.</p>';
                        return;
                    }

                    reviewContent.innerHTML = `<div class="review-list" style="display:flex; flex-direction:column; gap:16px;">
                        ${data.services.map(service => {
                            const currentRating = Number(service.rating) || 0;
                            return `
                            <div class="review-card" style="padding:16px; border:1px solid #e5e5e5; border-radius:12px; background:#fafafa;">
                                <div style="font-weight:700; font-size:16px; margin-bottom:2px;">${escapeHtml(service.name)}</div>
                                <div style="font-size:13px; color:#666; margin-bottom:12px;">Category: ${escapeHtml(service.category)}</div>

                                <form class="supplier-review-form" data-event="${eventId}" data-column="${escapeHtml(service.service_column)}" data-review-token="${escapeHtml(data.review_token)}">
                                    <div style="margin-bottom:12px;">
                                        <label style="font-size:13px; font-weight:600; display:block; margin-bottom:6px;">Rating Scale</label>
                                        <div class="star-rating-group" data-rating="${currentRating}" style="display:flex; gap:6px; cursor:pointer;">
                                            ${[1, 2, 3, 4, 5].map(star => `
                                                <button type="button" class="star-btn" data-value="${star}" style="background:none; border:none; font-size:24px; cursor:pointer; padding:0; color:${star <= currentRating ? '#f3c547' : '#dcdcdc'}; transition:color 0.2s;">★</button>
                                            `).join('')}
                                        </div>
                                        <input type="hidden" name="rating" value="${currentRating}" class="rating-input" required>
                                    </div>
                                    <div style="margin-bottom:12px;">
                                        <textarea name="review_text" placeholder="Write your review about this supplier's service..." style="width:100%; height:70px; padding:8px; border-radius:8px; border:1px solid #ccc; font-family:inherit; font-size:13px;">${escapeHtml(service.review_text)}</textarea>
                                    </div>
                                    <button type="submit" style="background:linear-gradient(135deg,#ffe27d,#f3c547); color:#111; border:0; padding:8px 16px; border-radius:8px; font-weight:700; cursor:pointer;">Save Review</button>
                                </form>
                            </div>
                        `;}).join('')}
                    </div>`;

                    // Star hover and selection logic
                    reviewContent.querySelectorAll('.star-rating-group').forEach(group => {
                        const stars = group.querySelectorAll('.star-btn');
                        const hiddenInput = group.parentElement.querySelector('.rating-input');

                        stars.forEach(star => {
                            star.addEventListener('mouseenter', () => {
                                const val = Number(star.dataset.value);
                                stars.forEach(s => { s.style.color = Number(s.dataset.value) <= val ? '#f3c547' : '#dcdcdc'; });
                            });
                            group.addEventListener('mouseleave', () => {
                                const currentVal = Number(hiddenInput.value) || 0;
                                stars.forEach(s => { s.style.color = Number(s.dataset.value) <= currentVal ? '#f3c547' : '#dcdcdc'; });
                            });
                            star.addEventListener('click', () => {
                                const val = Number(star.dataset.value);
                                hiddenInput.value = val;
                                stars.forEach(s => { s.style.color = Number(s.dataset.value) <= val ? '#f3c547' : '#dcdcdc'; });
                            });
                        });
                    });

                    // Review form submission handler
                    reviewContent.querySelectorAll('.supplier-review-form').forEach(form => {
                        form.addEventListener('submit', async (e) => {
                            e.preventDefault();
                            const formData = new FormData(form);
                            const ratingVal = Number(formData.get('rating'));

                            if (!ratingVal || ratingVal < 1) {
                                alert('Please select at least 1 star before saving.');
                                return;
                            }

                            try {
                                const res = await fetch(`{{ url('/your-events') }}/${form.dataset.event}/reviews`, {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'},
                                    body: JSON.stringify({
                                        service_column: form.dataset.column,
                                        rating: ratingVal,
                                        review_text: formData.get('review_text'),
                                        review_token: form.dataset.reviewToken
                                    })
                                });
                                const result = await res.json();
                                if (!res.ok || !result.success) throw new Error(result.message || 'Failed to save review.');
                                alert('Review saved successfully!');
                            } catch (err) {
                                alert(err.message);
                            }
                        });
                    });

                } catch (error) {
                    reviewContent.innerHTML = '<p class="modal-loading">Error loading review folder.</p>';
                }
            });
        });

        document.querySelector('[data-close-review]').addEventListener('click', () => { reviewModal.classList.remove('show'); reviewModal.setAttribute('aria-hidden', 'true'); });
        reviewModal.addEventListener('click', e => { if (e.target === reviewModal) { reviewModal.classList.remove('show'); reviewModal.setAttribute('aria-hidden', 'true'); } });

        // --- QR & Link Modal Logic ---
        const qrModal = document.getElementById('qrModal');
        const qrCodeImage = document.getElementById('qrCodeImage');
        const reviewFolderLinkInput = document.getElementById('reviewFolderLinkInput');

        document.querySelectorAll('[data-qr-event]').forEach(button => {
            button.addEventListener('click', async () => {
                const eventId = button.dataset.qrEvent;
                const linkResponse = await fetch(`{{ url('/your-events') }}/${eventId}/review-link`, {method: 'POST', headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'}});
                const linkData = await linkResponse.json().catch(() => ({}));
                if (!linkResponse.ok || !linkData.success) {
                    alert(linkData.message || 'Unable to create the review link.');
                    return;
                }
                const publicUrl = linkData.url;

                qrCodeImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(publicUrl)}`;
                reviewFolderLinkInput.value = publicUrl;

                qrModal.classList.add('show');
                qrModal.setAttribute('aria-hidden', 'false');
            });
        });

        document.getElementById('copyReviewLinkBtn').addEventListener('click', () => {
            reviewFolderLinkInput.select();
            navigator.clipboard.writeText(reviewFolderLinkInput.value);
            alert('Review link copied to clipboard!');
        });

        document.querySelector('[data-close-qr]').addEventListener('click', () => { qrModal.classList.remove('show'); qrModal.setAttribute('aria-hidden', 'true'); });
        qrModal.addEventListener('click', e => { if (e.target === qrModal) { qrModal.classList.remove('show'); qrModal.setAttribute('aria-hidden', 'true'); } });
    </script>
</body>
</html>
