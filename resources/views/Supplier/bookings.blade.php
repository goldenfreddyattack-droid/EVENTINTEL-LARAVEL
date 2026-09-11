@extends('supplier.layout')

@section('title', 'Supplier Bookings')

@section('content')
<style>
    .filter-btn {
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        border: 1px solid #d0d0d0;
        background: #f3f3f3;
        color: #333;
        font-weight: 600;
        display: inline-block;
    }

    .filter-btn-active {
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        border: 1px solid #d5a200;
        background: linear-gradient(135deg, #ffe27d, #f3c547);
        color: #111;
        font-weight: 700;
        display: inline-block;
    }

    .booking-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    .booking-table thead {
        background: #f5f5f5;
        border-bottom: 2px solid #ddd;
    }

    .booking-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
    }

    .booking-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }

    .accept-btn {
        background: #4caf50;
        color: white;
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        cursor: pointer;
        border: none;
        font-size: 12px;
    }

    .decline-btn {
        background: #f44336;
        color: white;
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        cursor: pointer;
        border: none;
        font-size: 12px;
    }

    .booking-actions { display: flex; align-items: center; gap: 6px; flex-wrap: nowrap; white-space: nowrap; }
    .booking-actions .accept-btn,
    .booking-actions .decline-btn { margin: 0; }

    .details-btn {
        display: inline-block;
        margin: 0 4px 4px 0;
        padding: 6px 12px;
        border: 1px solid #d5a200;
        border-radius: 6px;
        background: #fff8dc;
        color: #a77700;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .details-btn:hover { background: #fff1b8; }
    .booking-details-modal { display: none; position: fixed; inset: 0; z-index: 9998; align-items: center; justify-content: center; padding: 20px; background: rgba(0,0,0,.45); }
    .booking-details-modal.open { display: flex; }
    .booking-details-panel { width: min(560px, 100%); max-height: 90vh; overflow-y: auto; padding: 24px; border-radius: 18px; background: #fff; box-shadow: 0 18px 50px rgba(0,0,0,.2); }
    .booking-details-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 14px; border-bottom: 1px solid #eee2b7; }
    .booking-details-header h3 { margin: 0; color: #a77700; }
    .booking-details-close { width: 34px; height: 34px; border: 0; border-radius: 50%; background: #f3f3f3; color: #555; font-size: 22px; cursor: pointer; }
    .booking-details-list { display: grid; gap: 0; margin: 8px 0 0; }
    .booking-details-list div { display: grid; grid-template-columns: 150px minmax(0, 1fr); gap: 16px; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
    .booking-details-list dt { color: #777; font-size: 13px; font-weight: 700; }
    .booking-details-list dd { margin: 0; color: #222; font-size: 14px; white-space: pre-wrap; overflow-wrap: anywhere; }
    @media (max-width: 560px) { .booking-details-list div { grid-template-columns: 1fr; gap: 4px; } }
</style>

<section class="booking-request">
    <h2>All Bookings</h2>

    <div style="overflow-x:auto; margin-bottom: 20px;">
        <div style="margin-bottom:20px;display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('supplier.bookings', ['status' => 'all', 'page' => 1]) }}"
               class="{{ $statusFilter == 'all' ? 'filter-btn-active' : 'filter-btn' }}">
                All
            </a>

            <a href="{{ route('supplier.bookings', ['status' => 'pending', 'page' => 1]) }}"
               class="{{ $statusFilter == 'pending' ? 'filter-btn-active' : 'filter-btn' }}">
                Pending
            </a>

            <a href="{{ route('supplier.bookings', ['status' => 'accepted', 'page' => 1]) }}"
               class="{{ $statusFilter == 'accepted' ? 'filter-btn-active' : 'filter-btn' }}">
                Accepted
            </a>

            <a href="{{ route('supplier.bookings', ['status' => 'declined', 'page' => 1]) }}"
               class="{{ $statusFilter == 'declined' ? 'filter-btn-active' : 'filter-btn' }}">
                Declined
            </a>

            <a href="{{ route('supplier.bookings', ['status' => 'Paid', 'page' => 1]) }}"
               class="{{ $statusFilter == 'Paid' ? 'filter-btn-active' : 'filter-btn' }}">
                Paid
            </a>
        </div>

        <table class="booking-table">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Supplier/Business</th>
                    <th>Type of Event</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Price</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>View Details</th>
                </tr>
            </thead>
            <tbody>
                @if(empty($paginatedRows))
                <tr>
                    <td colspan="10" style="text-align:center;padding:40px;color:#999;">No bookings yet</td>
                </tr>
                @else
                @foreach($paginatedRows as $r)
                <tr>
                    <td>{{ $r['client_name'] ?? 'N/A' }}</td>
                    <td>{{ $r['business_name'] }}</td>
                    <td>{{ $r['event_type'] ?? 'N/A' }}</td>
                    <td>{{ $r['service'] }}</td>
                    <td>{{ $r['event_date'] ?? 'TBD' }}</td>
                    <td>₱{{ number_format($r['service_price'] ?? 0, 2) }}</td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;{{ $r['payment_method'] === 'online' ? 'background:rgba(100,150,255,.15);color:#6496ff;' : 'background:rgba(76,175,80,.15);color:#4caf50;' }}">
                            <i class="fas {{ $r['payment_method'] === 'online' ? 'fa-credit-card' : 'fa-money-bill-wave' }}"></i>
                            {{ ucfirst($r['payment_method']) }}
                        </span>
                    </td>
                    <td>
                        <span style="display:inline-block;padding:6px 14px;border-radius:999px;font-size:12px;font-weight:700;white-space:nowrap;
                            {{ $r['status'] === 'accepted' ? 'background:rgba(100,255,150,.15);color:#64ff96;' : ($r['status'] === 'declined' ? 'background:rgba(255,100,100,.15);color:#ff6464;' : ($r['status'] === 'Paid' ? 'background:rgba(76,175,80,.15);color:#388e3c;' : 'background:rgba(243,197,71,.15);color:var(--gold);')) }}">
                            {{ ucfirst(str_replace('_', ' ', $r['status'])) }}
                        </span>
                    </td>
                    <td>
                        @php($bookingDetails = [
                            'title' => $r['title'] ?? 'N/A',
                            'event_type' => $r['event_type'] ?? 'N/A',
                            'theme' => $r['theme'] ?? 'N/A',
                            'event_date' => $r['event_date'] ?? 'N/A',
                            'event_time' => $r['event_time'] ?? 'N/A',
                            'event_end_time' => $r['event_end_time'] ?? 'N/A',
                            'guest_count' => $r['guest_count'] ?? 'N/A',
                            'addons' => $r['addons'] ?? 'None',
                        ])
                        <div class="booking-actions">
                            @if($r['status'] === 'pending')
                                <button onclick="acceptBooking({{ $r['event_id'] }}, '{{ $r['service_key'] }}')" class="accept-btn">Accept</button>
                                <button onclick="openDeclineModal({{ $r['event_id'] }}, '{{ $r['service_key'] }}')" class="decline-btn">Decline</button>
                            @elseif($r['status'] === 'Pending Confirmation')
                                <button onclick="acceptSupplierPayment({{ $r['event_id'] }}, '{{ $r['service_key'] }}')" class="accept-btn">Receive Payment</button>
                            @endif
                        </div>
                    </td>
                    <td>
                        <button type="button" class="details-btn" data-details="{{ json_encode($bookingDetails, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}" onclick="openBookingDetailsFromButton(this)">View Details</button>
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>

        @if($totalPages > 1)
        <div style="display:flex;justify-content:center;gap:8px;margin-top:20px;flex-wrap:wrap;">
            @if($page > 1)
            <a href="{{ route('supplier.bookings', ['status' => $statusFilter, 'page' => $page - 1]) }}" class="filter-btn">Previous</a>
            @endif

            @for($i = 1; $i <= $totalPages; $i++)
            <a href="{{ route('supplier.bookings', ['status' => $statusFilter, 'page' => $i]) }}" 
               class="{{ $i === $page ? 'filter-btn-active' : 'filter-btn' }}">{{ $i }}</a>
            @endfor

            @if($page < $totalPages)
            <a href="{{ route('supplier.bookings', ['status' => $statusFilter, 'page' => $page + 1]) }}" class="filter-btn">Next</a>
            @endif
        </div>
        @endif
    </div>
</section>

<div id="bookingDetailsModal" class="booking-details-modal" aria-hidden="true">
    <div class="booking-details-panel" role="dialog" aria-modal="true" aria-labelledby="bookingDetailsTitle">
        <div class="booking-details-header">
            <h3 id="bookingDetailsTitle">Event Details</h3>
            <button type="button" class="booking-details-close" onclick="closeBookingDetails()" aria-label="Close">&times;</button>
        </div>
        <dl class="booking-details-list">
            <div><dt>Event Name</dt><dd id="detailEventName"></dd></div>
            <div><dt>Event Type</dt><dd id="detailEventType"></dd></div>
            <div><dt>Theme</dt><dd id="detailTheme"></dd></div>
            <div><dt>Event Date</dt><dd id="detailEventDate"></dd></div>
            <div><dt>Event Time</dt><dd id="detailEventTime"></dd></div>
            <div><dt>End Time</dt><dd id="detailEventEndTime"></dd></div>
            <div><dt>Guest Count</dt><dd id="detailGuestCount"></dd></div>
            <div><dt>Add-ons</dt><dd id="detailAddons"></dd></div>
        </dl>
    </div>
</div>

<!-- Decline modal -->
<div id="declineModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);align-items:center;justify-content:center;z-index:9999;">
    <div style="background:#fff;max-width:600px;width:90%;margin:auto;padding:20px;border-radius:8px;box-shadow:0 6px 24px rgba(0,0,0,.2);">
        <h3 style="margin-top:0;margin-bottom:8px;">Reason for declining</h3>
        <p style="margin-top:0;margin-bottom:8px;color:#666;font-size:14px;">Optionally provide a short note to send to the client.</p>
        <textarea id="declineNote" rows="6" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:14px;"></textarea>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;">
            <button id="declineCancelBtn" style="padding:8px 14px;border-radius:8px;background:#f3f3f3;border:1px solid #ccc;cursor:pointer;">Cancel</button>
            <button id="declineSendBtn" style="padding:8px 14px;border-radius:8px;background:#d9534f;color:#fff;border:0;cursor:pointer;">Send</button>
        </div>
    </div>
</div>

<script>
    const currentStatus = '{{ $statusFilter }}';
    let _declineEventId = null;
    let _declineServiceKey = null;

    function formatBookingTime(value) {
        if (!value || value === 'N/A') return 'N/A';
        const parts = String(value).split(':');
        const hour = Number(parts[0]);
        const minute = parts[1] || '00';
        if (!Number.isFinite(hour)) return value;
        const suffix = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minute} ${suffix}`;
    }

    function openBookingDetailsFromButton(button) {
        try {
            openBookingDetails(JSON.parse(button.getAttribute('data-details')));
        } catch (error) {
            console.error('Unable to read booking details', error);
            alert('Unable to load the event details.');
        }
    }

    function openBookingDetails(details) {
        const fields = {
            detailEventName: details.title,
            detailEventType: details.event_type,
            detailTheme: details.theme,
            detailEventDate: details.event_date,
            detailEventTime: formatBookingTime(details.event_time),
            detailEventEndTime: formatBookingTime(details.event_end_time),
            detailGuestCount: details.guest_count,
            detailAddons: details.addons
        };
        Object.entries(fields).forEach(([id, value]) => {
            document.getElementById(id).textContent = value || 'N/A';
        });
        const modal = document.getElementById('bookingDetailsModal');
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeBookingDetails() {
        const modal = document.getElementById('bookingDetailsModal');
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }

    document.getElementById('bookingDetailsModal').addEventListener('click', event => {
        if (event.target.id === 'bookingDetailsModal') closeBookingDetails();
    });

    function openDeclineModal(eventId, serviceKey) {
        _declineEventId = eventId;
        _declineServiceKey = serviceKey;
        document.getElementById('declineNote').value = '';
        document.getElementById('declineModal').style.display = 'flex';
    }

    function closeDeclineModal() {
        document.getElementById('declineModal').style.display = 'none';
        _declineEventId = null;
        _declineServiceKey = null;
    }

    const declineSendBtn = document.getElementById('declineSendBtn');
    const declineCancelBtn = document.getElementById('declineCancelBtn');

    if (declineSendBtn) {
        declineSendBtn.addEventListener('click', function () {
            if (!_declineEventId || !_declineServiceKey) return closeDeclineModal();
            const note = document.getElementById('declineNote').value || '';
            const body = new URLSearchParams({
                action: 'declined',
                id: _declineEventId,
                service: _declineServiceKey,
                decline_note: note
            });

            fetch('{{ route("supplier.bookings.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: body
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to send decline note');
                }
            }).catch(err => {
                console.error(err);
                alert('Error: ' + err.message);
            });
        });
    }

    if (declineCancelBtn) {
        declineCancelBtn.addEventListener('click', closeDeclineModal);
    }

    function acceptBooking(eventId, serviceType) {
        const body = new URLSearchParams({
            action: 'accepted',
            id: eventId,
            service: serviceType
        });

        fetch('{{ route("supplier.bookings.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: body
        }).then(r => r.json()).then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to accept booking');
            }
        }).catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        });
    }

    function acceptSupplierPayment(eventId, serviceType) {
        const body = new URLSearchParams({
            action: 'paid',
            id: eventId,
            service: serviceType
        });

        fetch('{{ route("supplier.bookings.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: body
        }).then(r => r.json()).then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to record payment');
            }
        }).catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        });
    }
</script>
@endsection
