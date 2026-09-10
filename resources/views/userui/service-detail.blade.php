@php
    $imageMap = ['venue' => 'venue.avif', 'catering' => 'catering.jpg', 'clothes' => 'clothing_stylist.jpg', 'host' => 'images.jpg', 'photographer' => 'photographer.avif', 'sounds_lights' => 'ledlights.jpg', 'church' => 'venue.avif', 'rental_car' => 'images.jpg'];
    $image = asset('images/userui/' . ($imageMap[$serviceKey] ?? 'venue.avif'));
    $serviceImage = $serviceRecord->service_pic1
        ? route('supplier.services.image', ['id' => $serviceRecord->service_id, 'pic' => 'service_pic1'])
        : ($serviceRecord->service_pic ? route('supplier.services.image', $serviceRecord->service_id) : $image);
    $galleryImages = [];
    for ($pictureNumber = 1; $pictureNumber <= 5; $pictureNumber++) {
        $pictureColumn = 'service_pic' . $pictureNumber;
        if ($serviceRecord->{$pictureColumn}) {
            $galleryImages[] = route('supplier.services.image', ['id' => $serviceRecord->service_id, 'pic' => $pictureColumn]);
        }
    }
    if (!$galleryImages && $serviceRecord->service_pic) {
        $galleryImages[] = route('supplier.services.image', $serviceRecord->service_id);
    }
    if (!$galleryImages) {
        $galleryImages[] = $image;
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>EventIntel - {{ $serviceRecord->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f8f6f0;color:#171717;font-family:'Segoe UI',sans-serif}.page{width:min(1320px,100%);margin:auto;padding:28px 40px 42px}.detail{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(360px,.8fr);gap:28px;background:#fffdf8;border:1px solid #eee2b7;border-radius:24px;padding:40px;box-shadow:0 18px 45px #00000012}.gallery{min-width:0}.main-image{height:510px;border-radius:22px;overflow:hidden;background:#f3c547}.main-image img{width:100%;height:100%;object-fit:cover}.thumbnails{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:14px}.thumbnail{height:100px;border-radius:14px;overflow:hidden;background:#f3c547}.thumbnail img{width:100%;height:100%;object-fit:cover}.info{display:flex;flex-direction:column;min-width:0}.back{color:#a77700;text-decoration:none;font-weight:700;margin-bottom:24px}.badge{align-self:flex-start;padding:8px 14px;border-radius:18px;background:#fff1b8;color:#916b00;font-size:13px;font-weight:800}.info h1{font-size:clamp(32px,4vw,54px);line-height:1.08;margin:18px 0 12px}.supplier{color:#777;font-size:15px}.description{color:#555;line-height:1.7;margin:24px 0}.facts{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.fact{min-height:88px;padding:16px;border:1px solid #eee2d2;border-radius:16px;background:#fff}.fact small{display:block;color:#777;margin-bottom:7px}.fact strong{display:block;font-size:20px;line-height:1.25;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}.offers-title{font-size:18px;font-weight:800;margin:26px 0 12px}.offer{display:flex;align-items:center;gap:12px;padding:13px 16px;margin-bottom:10px;border:1px solid #f0e3bc;border-radius:15px;background:#fff8e8;color:#555}.offer i{width:30px;height:30px;display:grid;place-items:center;border-radius:10px;background:#fff1c9;color:#987100}.actions{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:auto;padding-top:24px}.button{height:50px;border:0;border-radius:14px;background:#f3c547;color:#111;font-weight:800;text-decoration:none;display:flex;align-items:center;justify-content:center;cursor:pointer}.button.secondary{background:#f0f0f0}@media(max-width:900px){.page{padding:18px}.detail{grid-template-columns:1fr;padding:24px}.main-image{height:380px}}@media(max-width:560px){.detail{padding:16px}.main-image{height:280px}.thumbnails{gap:7px}.thumbnail{height:68px}.facts{grid-template-columns:1fr}.actions{grid-template-columns:1fr}}
        .thumbnail{border:0;padding:0;cursor:pointer}
    </style>
</head>
<body><div class="page"><article class="detail">
    <section class="gallery"><div class="main-image"><img src="{{ $serviceImage }}" alt="{{ $serviceRecord->name }}"></div><div class="thumbnails">@foreach($galleryImages as $galleryImage)<button class="thumbnail" type="button" data-image="{{ $galleryImage }}"><img src="{{ $galleryImage }}" alt=""></button>@endforeach</div></section>
    <section class="info"><a class="back" href="{{ route('services.index', [$serviceKey]) }}">&larr; Back to {{ $serviceLabel }}</a><span class="badge">{{ $serviceLabel }}</span><h1>{{ $serviceRecord->name }}</h1><p class="supplier">{{ $serviceRecord->business_name ?: ($serviceRecord->supplier_name ?: 'EventIntel supplier') }}</p><p class="description">{{ $serviceRecord->description ?: 'Professional service for your event, delivered by an experienced EventIntel supplier.' }}</p><div class="facts"><div class="fact"><small>Rating</small><strong>★ {{ number_format((float) ($serviceRecord->rating ?? 0), 1) }}</strong></div><div class="fact"><small>Starting Price</small><strong>{{ $serviceRecord->price ? '₱'.number_format((float)$serviceRecord->price) : 'On request' }}</strong></div><div class="fact"><small>Capacity</small><strong>{{ $serviceRecord->capacity ? number_format($serviceRecord->capacity).' Guests' : 'Flexible' }}</strong></div><div class="fact"><small>Location</small><strong>{{ $serviceRecord->address ?: 'Available from supplier' }}</strong></div></div><div class="offers-title">Included Offers</div><div class="offer"><i class="fas fa-check"></i><span>Professional {{ strtolower($serviceLabel) }} service</span></div><div class="offer"><i class="fas fa-check"></i><span>Service planning and coordination support</span></div><div class="offer"><i class="fas fa-check"></i><span>Direct supplier details for your event</span></div><div class="actions"><a class="button secondary" href="{{ route('services.index', [$serviceKey]) }}">Back</a><button class="button select-service" type="button">Select Service</button></div></section>
</article></div>
@if($readonly)<style>.select-service{display:none!important}</style>@endif
<script>document.querySelectorAll('.thumbnail').forEach(thumbnail => thumbnail.addEventListener('click', () => { document.querySelector('.main-image img').src = thumbnail.dataset.image; }));</script>
@unless($readonly)<script>document.querySelector('.select-service').addEventListener('click', () => { const data = {type:'serviceSelected', service:@json($serviceKey), [@json($serviceKey)]:@json($serviceRecord->name), price:Number(@json($serviceRecord->price)) || null}; if (window.parent !== window) window.parent.postMessage(data, '*'); else if (window.opener) { window.opener.postMessage(data, '*'); window.close(); } else window.location.href = @json($returnUrl ?: route('events.create')) + '?selected=' + encodeURIComponent(@json($serviceRecord->name)); });</script>@endunless
</body></html>
