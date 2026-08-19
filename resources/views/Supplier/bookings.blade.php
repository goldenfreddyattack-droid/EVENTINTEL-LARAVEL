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
                    <th>Supplier/Business</th>
                    <th>Type of Event</th>
                    <th>Service</th>
                    <th>Client Name</th>
                    <th>Date</th>
                    <th>Budget</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @if(empty($paginatedRows))
                <tr>
                    <td colspan="9" style="text-align:center;padding:40px;color:#999;">No bookings yet</td>
                </tr>
                @else
                @foreach($paginatedRows as $r)
                <tr>
                    <td>{{ $r['business_name'] }}</td>
                    <td>{{ $r['event_type'] ?? 'N/A' }}</td>
                    <td>{{ $r['service'] }}</td>
                    <td>{{ $r['client_name'] ?? 'N/A' }}</td>
                    <td>{{ $r['event_date'] ?? 'TBD' }}</td>
                    <td>₱{{ number_format($r['budget'] ?? 0) }}</td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;{{ $r['payment_method'] === 'online' ? 'background:rgba(100,150,255,.15);color:#6496ff;' : 'background:rgba(76,175,80,.15);color:#4caf50;' }}">
                            <i class="fas {{ $r['payment_method'] === 'online' ? 'fa-credit-card' : 'fa-money-bill-wave' }}"></i>
                            {{ ucfirst($r['payment_method']) }}
                        </span>
                    </td>
                    <td>
                        <span style="display:inline-block;padding:6px 14px;border-radius:999px;font-size:12px;font-weight:700;
                            {{ $r['status'] === 'accepted' ? 'background:rgba(100,255,150,.15);color:#64ff96;' : ($r['status'] === 'declined' ? 'background:rgba(255,100,100,.15);color:#ff6464;' : ($r['status'] === 'Paid' ? 'background:rgba(76,175,80,.15);color:#388e3c;' : 'background:rgba(243,197,71,.15);color:var(--gold);')) }}">
                            {{ ucfirst(str_replace('_', ' ', $r['status'])) }}
                        </span>
                    </td>
                    <td>
                        @if($r['status'] === 'pending')
                            <button onclick="acceptBooking({{ $r['event_id'] }}, '{{ $r['service_key'] }}')" class="accept-btn">
                                Accept
                            </button>
                            <button onclick="openDeclineModal({{ $r['event_id'] }}, '{{ $r['service_key'] }}')" class="decline-btn" style="margin-left: 4px;">
                                Decline
                            </button>
                        @elseif($r['status'] === 'Payment Pending')
                            <button onclick="acceptSupplierPayment({{ $r['event_id'] }}, '{{ $r['service_key'] }}')" class="accept-btn">
                                Receive Payment
                            </button>
                        @endif
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
