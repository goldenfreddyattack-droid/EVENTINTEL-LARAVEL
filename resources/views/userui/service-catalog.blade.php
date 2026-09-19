<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>EventIntel - {{ $serviceLabel }}</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}"><link rel="stylesheet" href="{{ asset('css/userui/navbar.css') }}">
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f8f8f8;color:#111;font-family:'Segoe UI',sans-serif}.page{width:min(1180px,100%);margin:auto;padding:6px 32px 48px}.heading{display:flex;justify-content:space-between;align-items:end;gap:18px;margin:38px 0 28px}.heading h1{margin:0 0 8px;font-size:42px}.heading p{margin:0;color:#666}.back{color:#a77700;text-decoration:none;font-weight:700}.grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.card{height:420px;display:flex;flex-direction:column;background:#fff;border:1px solid #eee2b7;border-radius:16px;overflow:hidden;box-shadow:0 10px 24px #0000000b}.card-image{height:150px;flex:0 0 150px;background:#f3c547;display:flex;align-items:center;justify-content:center;color:#fff;font-size:42px}.card-body{min-height:0;flex:1;display:flex;flex-direction:column;padding:18px}.card h2{height:46px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:19px;margin:0 0 5px}.supplier{height:20px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;color:#777;font-size:13px}.meta{height:20px;display:flex;gap:14px;overflow:hidden;white-space:nowrap;color:#777;font-size:13px;margin:14px 0}.description{height:48px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;color:#666;line-height:1.5;margin:0}.actions{height:42px;display:flex;justify-content:flex-end;gap:8px;align-items:center;margin-top:auto}.button{width:106px;height:40px;display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:10px;padding:10px 15px;background:#f3c547;color:#111;font-weight:800;text-decoration:none;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.empty{background:#fff;border:1px solid #eee2b7;border-radius:16px;padding:35px;text-align:center;color:#666}@media(max-width:800px){.grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:520px){.page{padding:6px 18px 32px}.heading{display:block}.grid{grid-template-columns:1fr}.heading h1{font-size:34px}}
        .card-image img{width:100%;height:100%;object-fit:cover}
        .booking-modal{display:none;position:fixed;inset:0;z-index:20;align-items:center;justify-content:center;padding:20px;background:#12161999}.booking-modal.open{display:flex}.booking-panel{width:min(440px,100%);padding:28px;border-radius:20px;background:#fff;text-align:center;box-shadow:0 20px 60px #0004}.booking-panel h2{margin:0 0 10px;color:#a77700;font-size:24px}.booking-panel p{margin:0;color:#666;line-height:1.6}.booking-actions{display:flex;justify-content:center;gap:10px;margin-top:22px}.booking-actions button{min-width:120px;height:42px;border:0;border-radius:10px;padding:10px 16px;font-weight:800;cursor:pointer}.booking-actions .secondary{background:#f1f1f1}.booking-actions .primary{background:#f3c547}
    </style>
</head>
<body><div class="page">
    <header class="heading"><div><h1>{{ $serviceLabel }}</h1><p>Browse available {{ strtolower($serviceLabel) }} services for your event.</p></div><a class="back" href="{{ $readonly ? route('home') : ($returnUrl ?: route('events.create')) }}">Back to {{ $readonly ? 'Homepage' : 'Create Event' }}</a></header>
    @if (count($services) === 0)<div class="empty">No {{ strtolower($serviceLabel) }} services are available yet.</div>@else
        <main class="grid">@foreach($services as $serviceRecord)@php($catalogImage = $serviceRecord->service_pic ? route('supplier.services.image', $serviceRecord->service_id) : ($serviceRecord->service_pic1 ? route('supplier.services.image', ['id' => $serviceRecord->service_id, 'pic' => 'service_pic1']) : null))<article class="card"><div class="card-image">@if($catalogImage)<img src="{{ $catalogImage }}" alt="{{ $serviceRecord->name }}">@else<i class="fas fa-{{ $serviceKey === 'venue' ? 'location-dot' : ($serviceKey === 'photographer' ? 'camera' : ($serviceKey === 'host' ? 'microphone' : 'briefcase')) }}"></i>@endif</div><div class="card-body"><h2>{{ $serviceRecord->name }}</h2><div class="supplier">{{ $serviceRecord->business_name ?: ($serviceRecord->supplier_name ?: 'EventIntel supplier') }}</div><div class="meta"><span>★ {{ number_format((float) ($serviceRecord->rating ?? 0), 1) }}</span><span>{{ $serviceRecord->price ? '₱'.number_format((float)$serviceRecord->price) : 'Price on request' }}</span></div><p class="description">{{ $serviceRecord->description ?: 'Professional service for your event.' }}</p><div class="actions"><a class="button" href="{{ route('services.show', [$serviceKey, $serviceRecord->service_id]) }}?{{ http_build_query(array_filter(['return' => $returnUrl, 'readonly' => $readonly ? 1 : null])) }}">View</a>@unless($readonly)<button class="button select-service" type="button" data-name="{{ $serviceRecord->name }}">Select</button>@endunless</div></div></article>@endforeach</main>
    @endif
</div>
<div class="booking-modal" id="bookingConfirmModal" aria-hidden="true"><div class="booking-panel" role="dialog" aria-modal="true" aria-labelledby="bookingConfirmTitle"><h2 id="bookingConfirmTitle">Confirm Booking</h2><p id="bookingConfirmText"></p><div class="booking-actions"><button class="secondary" type="button" data-close-booking>Cancel</button><button class="primary" type="button" data-confirm-booking>Yes, Book Service</button></div></div></div>
<div class="booking-modal" id="bookingSuccessModal" aria-hidden="true"><div class="booking-panel" role="dialog" aria-modal="true" aria-labelledby="bookingSuccessTitle"><h2 id="bookingSuccessTitle">Booking Confirmed</h2><p>Your service has been booked successfully.</p><div class="booking-actions"><button class="primary" type="button" data-back-services>Back to Services</button></div></div></div>
<script>
    const selection = {type:'serviceSelected', service:@json($serviceKey)};
    const bookingConfirmModal = document.getElementById('bookingConfirmModal');
    const bookingSuccessModal = document.getElementById('bookingSuccessModal');
    let pendingSelection = null;
    function setBookingModal(modal, open) { modal.classList.toggle('open', open); modal.setAttribute('aria-hidden', open ? 'false' : 'true'); }
    function completeSelection() {
        selection[@json($serviceKey)] = pendingSelection.name;
        selection.price = pendingSelection.price;
        setBookingModal(bookingConfirmModal, false);
        setBookingModal(bookingSuccessModal, true);
    }
    function returnToServices() {
        if (window.parent !== window) window.parent.postMessage(selection, '*');
        else if (window.opener) { window.opener.postMessage(selection, '*'); window.close(); }
        else window.location.href = @json(route('services.index', [$serviceKey]));
    }
    document.querySelectorAll('.select-service').forEach(button => button.addEventListener('click', () => {
        if (@json($serviceKey) === 'venue') {
            const priceText = button.closest('.card').querySelector('.meta span:last-child').textContent;
            const venueSelection = {type:'serviceSelected', service:'venue', venue:button.dataset.name, price:Number(priceText.replace(/[^0-9.]/g, '')) || null};
            if (window.parent !== window) window.parent.postMessage(venueSelection, '*');
            else if (window.opener) { window.opener.postMessage(venueSelection, '*'); window.close(); }
            else window.location.href = @json(route('services.index', ['venue']));
            return;
        }
        const priceText = button.closest('.card').querySelector('.meta span:last-child').textContent;
        pendingSelection = {name: button.dataset.name, price: Number(priceText.replace(/[^0-9.]/g, '')) || null};
        document.getElementById('bookingConfirmText').textContent = `Are you sure you want to book ${pendingSelection.name}?`;
        setBookingModal(bookingConfirmModal, true);
    }));
    document.querySelector('[data-close-booking]').addEventListener('click', () => setBookingModal(bookingConfirmModal, false));
    document.querySelector('[data-confirm-booking]').addEventListener('click', completeSelection);
    document.querySelector('[data-back-services]').addEventListener('click', returnToServices);
</script>
</body></html>
