@php
    $imageMap = ['venue' => 'venue.avif', 'catering' => 'catering.jpg', 'clothes' => 'clothing_stylist.jpg', 'host' => 'images.jpg', 'photographer' => 'photographer.avif', 'sounds_lights' => 'ledlights.jpg', 'church' => 'venue.avif', 'rental_car' => 'images.jpg'];
    $image = asset('images/userui/' . ($imageMap[$serviceKey] ?? 'venue.avif'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="csrf-token" content="{{ csrf_token() }}"><title>EventIntel - {{ $serviceRecord->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <style>
        .venue-modal{display:none;position:fixed;inset:0;z-index:30;align-items:center;justify-content:center;padding:20px;background:#12161999}.venue-modal.open{display:flex}.venue-panel{width:min(560px,100%);max-height:90vh;overflow:auto;background:#fff;border-radius:24px;padding:24px;box-shadow:0 20px 60px #0004}.venue-panel h2{margin:0 0 8px}.venue-panel p{color:#666;margin:0 0 18px}.venue-dates,.venue-addons{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.venue-date,.venue-addon{border:1px solid #d7eadb;border-radius:14px;padding:13px;background:#effbf1;color:#17652b;font-weight:800}.venue-date.booked{border-color:#f4caca;background:#fff0f0;color:#ad2929}.venue-date{display:flex;justify-content:space-between}.venue-addon{display:flex;gap:10px;align-items:center;background:#fff;border-color:#eee2b7;color:#222}.venue-addon input{accent-color:#d6a91d}.venue-modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px}.venue-modal-actions button{height:42px;border:0;border-radius:10px;padding:10px 16px;font-weight:800;cursor:pointer}.venue-modal-actions .secondary{background:#f1f1f1}.venue-modal-actions .primary{background:#f3c547}@media(max-width:520px){.venue-dates,.venue-addons{grid-template-columns:1fr}}
        *{box-sizing:border-box}body{margin:0;background:#f8f6f0;color:#171717;font-family:'Segoe UI',sans-serif}.page{width:min(1320px,100%);margin:auto;padding:28px 40px 42px}.detail{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(360px,.8fr);gap:28px;background:#fffdf8;border:1px solid #eee2b7;border-radius:24px;padding:40px;box-shadow:0 18px 45px #00000012}.gallery{min-width:0}.main-image{height:510px;border-radius:22px;overflow:hidden;background:#f3c547}.main-image img{width:100%;height:100%;object-fit:cover}.thumbnails{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:14px}.thumbnail{height:100px;border-radius:14px;overflow:hidden;background:#f3c547}.thumbnail img{width:100%;height:100%;object-fit:cover}.info{display:flex;flex-direction:column;min-width:0}.back{color:#a77700;text-decoration:none;font-weight:700;margin-bottom:24px}.badge{align-self:flex-start;padding:8px 14px;border-radius:18px;background:#fff1b8;color:#916b00;font-size:13px;font-weight:800}.info h1{font-size:clamp(32px,4vw,54px);line-height:1.08;margin:18px 0 12px}.supplier{color:#777;font-size:15px}.description{color:#555;line-height:1.7;margin:24px 0}.facts{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.fact{min-height:88px;padding:16px;border:1px solid #eee2d2;border-radius:16px;background:#fff}.fact small{display:block;color:#777;margin-bottom:7px}.fact strong{display:block;font-size:20px;line-height:1.25;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}.offers-title{font-size:18px;font-weight:800;margin:26px 0 12px}.offer{display:flex;align-items:center;gap:12px;padding:13px 16px;margin-bottom:10px;border:1px solid #f0e3bc;border-radius:15px;background:#fff8e8;color:#555}.offer i{width:30px;height:30px;display:grid;place-items:center;border-radius:10px;background:#fff1c9;color:#987100}.actions{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:auto;padding-top:24px}.button{height:50px;border:0;border-radius:12px;padding:0 20px;background:#f3c547;color:#111;font-weight:800;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}.button.secondary{background:#f1f1f1;color:#333}.button:hover{filter:brightness(.96)}@media(max-width:1000px){.detail{grid-template-columns:1fr}.main-image{height:420px}.actions{grid-template-columns:1fr;max-width:280px}}@media(max-width:680px){.page{padding:20px}.detail{padding:22px}.thumbnails{grid-template-columns:repeat(2,1fr)}}
    </style>
</head>
<body><div class="page"><article class="detail">
    <section class="gallery"><div class="main-image"><img src="{{ $image }}" alt="{{ $serviceRecord->name }}"></div><div class="thumbnails"><div class="thumbnail"><img src="{{ $image }}" alt=""></div><div class="thumbnail"><img src="{{ $image }}" alt=""></div><div class="thumbnail"><img src="{{ $image }}" alt=""></div><div class="thumbnail"><img src="{{ $image }}" alt=""></div></div></section>
    <section class="info"><a class="back" href="{{ $returnUrl ?: route('your.events') }}">&larr; Back to Event Status</a><span class="badge">{{ $serviceLabel }}</span><h1>{{ $serviceRecord->name }}</h1><p class="supplier">{{ $serviceRecord->business_name ?: ($serviceRecord->supplier_name ?: 'EventIntel supplier') }}</p><p class="description">{{ $serviceRecord->description ?: 'Professional service for your event, delivered by an experienced EventIntel supplier.' }}</p><div class="facts"><div class="fact"><small>Rating</small><strong>★ {{ number_format((float) ($serviceRecord->rating ?? 0), 1) }}</strong></div><div class="fact"><small>Starting Price</small><strong>{{ $serviceRecord->price ? '₱'.number_format((float)$serviceRecord->price) : 'On request' }}</strong></div><div class="fact"><small>Capacity</small><strong>{{ $serviceRecord->capacity ? number_format($serviceRecord->capacity).' Guests' : 'Flexible' }}</strong></div><div class="fact"><small>Location</small><strong>{{ $serviceRecord->address ?: 'Available from supplier' }}</strong></div></div><div class="offers-title">Included Offers</div><div class="offer"><i class="fas fa-check"></i><span>Professional {{ strtolower($serviceLabel) }} service</span></div><div class="offer"><i class="fas fa-check"></i><span>Service planning and coordination support</span></div><div class="offer"><i class="fas fa-check"></i><span>Direct supplier details for your event</span></div><div class="actions"><a class="button secondary" href="{{ $returnUrl ?: route('your.events') }}">Back</a><button class="button select-service" type="button">Select Service</button></div></section>
</article></div>
<div class="venue-modal" id="venueAvailabilityModal" aria-hidden="true"><div class="venue-panel"><h2>Venue Availability</h2><p id="venueAvailabilityText">Check available dates for this venue.</p><div class="venue-dates" id="venueDates"></div><div class="venue-modal-actions"><button class="secondary" type="button" data-close-venue>Cancel</button><button class="primary" type="button" data-confirm-venue>Confirm and Select Venue</button></div></div></div>
@if($readonly)<style>.select-service{display:none!important}</style>@endif
@unless($readonly)<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const serviceKey = @json($serviceKey);
const eventId = Number(@json($eventId ?? 0));
const returnUrl = @json($returnUrl ?: route('your.events'));
const serviceName = @json($serviceRecord->name);
const servicePrice = Number(@json($serviceRecord->price)) || null;
const serviceLabels = {venue:'Venue', clothes:'Clothes', catering:'Food & Catering', host:'Host', sounds_lights:'Sounds & Lights', photographer:'Photographer'};
const venueAvailabilityModal = document.getElementById('venueAvailabilityModal');
const venueDates = document.getElementById('venueDates');
let pendingVenue = null;
function closeVenueModals() {
    venueAvailabilityModal.classList.remove('open');
    venueAvailabilityModal.setAttribute('aria-hidden','true');
}
function serviceKeyFromCategory(category) {
    const value = String(category || '').toLowerCase().replace(/[^a-z]/g, '');
    if (value.includes('sound') || value.includes('light')) return 'sounds_lights';
    if (value.includes('cater')) return 'catering';
    if (value.includes('photo')) return 'photographer';
    if (value.includes('cloth') || value.includes('attir')) return 'clothes';
    if (value.includes('host') || value.includes('mc')) return 'host';
    if (value.includes('venue')) return 'venue';
    return value;
}
async function openVenueAvailability(name, price) {
    pendingVenue = {name, price};
    document.getElementById('venueAvailabilityText').textContent = `Availability for ${name}`;
    venueDates.innerHTML = '<p>Checking availability...</p>';
    venueAvailabilityModal.classList.add('open');
    venueAvailabilityModal.setAttribute('aria-hidden','false');
    const response = await fetch(`{{ route('events.venue-availability') }}?venue=${encodeURIComponent(name)}`);
    const data = await response.json();
    venueDates.innerHTML = data.dates.map(item => `<div class="venue-date ${item.available ? '' : 'booked'}"><strong>${item.label}</strong><span>${item.available ? 'Open' : 'Booked'}</span></div>`).join('');
}
async function completeReselect(serviceType, serviceName, addons = []) {
    if (!eventId) return;
    await fetch(`{{ url('/your-events') }}/${eventId}/reselect`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({service_type: serviceType, service_name: serviceName, addons: addons}),
    });
}
function doSelection(serviceType, serviceName, price) {
    const data = {type:'serviceSelected', service:serviceType, [serviceType]:serviceName, price:price || null};
    if (window.parent !== window) window.parent.postMessage(data, '*');
    else if (window.opener) { window.opener.postMessage(data, '*'); window.close(); }
    else window.location.href = returnUrl + '?selected=' + encodeURIComponent(serviceName);
}
document.querySelector('[data-close-venue]').addEventListener('click', closeVenueModals);
document.querySelector('[data-confirm-venue]').addEventListener('click', async () => {
    if (!pendingVenue) return;
    await completeReselect(serviceKey, pendingVenue.name);
    closeVenueModals();
    doSelection(serviceKey, pendingVenue.name, servicePrice);
});
document.querySelector('.select-service').addEventListener('click', async () => {
    if (serviceKey === 'venue') {
        openVenueAvailability(serviceName, servicePrice);
        return;
    }
    await completeReselect(serviceKey, serviceName);
    doSelection(serviceKey, serviceName, servicePrice);
});
</script>@endunless
</body></html>
