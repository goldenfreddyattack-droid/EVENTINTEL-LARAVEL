@php
    $imageMap = ['venue' => 'venue.avif', 'catering' => 'catering.jpg', 'clothes' => 'clothing_stylist.jpg', 'host' => 'images.jpg', 'photographer' => 'photographer.avif', 'sounds_lights' => 'ledlights.jpg', 'church' => 'venue.avif', 'rental_car' => 'images.jpg'];
    $image = asset('images/userui/' . ($imageMap[$serviceKey] ?? 'venue.avif'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventIntel - {{ $serviceRecord->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f8f6f0; color: #171717; font-family: 'Segoe UI', sans-serif; }
        .page { width: min(1320px, 100%); margin: auto; padding: 28px 40px 42px; }
        .detail { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(360px, .8fr); gap: 28px; background: #fffdf8; border: 1px solid #eee2b7; border-radius: 24px; padding: 40px; box-shadow: 0 18px 45px #00000012; }
        .main-image { height: 510px; border-radius: 22px; overflow: hidden; background: #f3c547; }
        .main-image img { width: 100%; height: 100%; object-fit: cover; }
        .back { display: inline-block; color: #a77700; text-decoration: none; font-weight: 700; margin-bottom: 24px; }
        .badge { display: inline-block; padding: 8px 14px; border-radius: 18px; background: #fff1b8; color: #916b00; font-size: 13px; font-weight: 800; }
        .info h1 { font-size: clamp(32px, 4vw, 54px); line-height: 1.08; margin: 18px 0 12px; }
        .supplier { color: #777; font-size: 15px; }
        .description { color: #555; line-height: 1.7; margin: 24px 0; }
        .facts { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .fact { min-height: 88px; padding: 16px; border: 1px solid #eee2d2; border-radius: 16px; background: #fff; }
        .fact small { display: block; color: #777; margin-bottom: 7px; }
        .fact strong { display: block; font-size: 20px; line-height: 1.25; }
        .offers-title { font-size: 18px; font-weight: 800; margin: 26px 0 12px; }
        .offer { display: flex; align-items: center; gap: 12px; padding: 13px 16px; margin-bottom: 10px; border: 1px solid #f0e3bc; border-radius: 15px; background: #fff8e8; color: #555; }
        .offer i { width: 30px; height: 30px; display: grid; place-items: center; border-radius: 10px; background: #fff1c9; color: #987100; }
        @media (max-width: 800px) { .page { padding: 20px; } .detail { grid-template-columns: 1fr; padding: 22px; } .main-image { height: 340px; } }
    </style>
</head>
<body>
    <div class="page">
        <article class="detail">
            <section><div class="main-image"><img src="{{ $image }}" alt="{{ $serviceRecord->name }}"></div></section>
            <section class="info">
                <a class="back" href="{{ route('carousel.services.index', $serviceKey) }}">&larr; Back to {{ $serviceLabel }} Services</a>
                <span class="badge">{{ $serviceLabel }}</span>
                <h1>{{ $serviceRecord->name }}</h1>
                <p class="supplier">{{ $serviceRecord->business_name ?: ($serviceRecord->supplier_name ?: 'EventIntel supplier') }}</p>
                <p class="description">{{ $serviceRecord->description ?: 'Professional service for your event, delivered by an experienced EventIntel supplier.' }}</p>
                <div class="facts">
                    <div class="fact"><small>Rating</small><strong>★ {{ number_format((float) ($serviceRecord->rating ?? 0), 1) }}</strong></div>
                    <div class="fact"><small>Starting Price</small><strong>{{ $serviceRecord->price ? '₱'.number_format((float) $serviceRecord->price) : 'On request' }}</strong></div>
                    <div class="fact"><small>Capacity</small><strong>{{ $serviceRecord->capacity ? number_format($serviceRecord->capacity).' Guests' : 'Flexible' }}</strong></div>
                    <div class="fact"><small>Location</small><strong>{{ $serviceRecord->address ?: 'Available from supplier' }}</strong></div>
                </div>
                <div class="offers-title">Included Offers</div>
                <div class="offer"><i class="fas fa-check"></i><span>Professional {{ strtolower($serviceLabel) }} service</span></div>
                <div class="offer"><i class="fas fa-check"></i><span>Service planning and coordination support</span></div>
                <div class="offer"><i class="fas fa-check"></i><span>Direct supplier details for your event</span></div>
            </section>
        </article>
    </div>
</body>
</html>
