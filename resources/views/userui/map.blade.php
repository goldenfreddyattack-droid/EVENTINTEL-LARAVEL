<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventIntel - Venue Location</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/navbar.css') }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { min-height: 100vh; color: #222; background: #fff; }
        .container { width: 100%; min-height: 100vh; padding: 6px 48px 40px; }
        .map-wrapper { display: grid; grid-template-columns: 2fr 1fr; gap: 28px; margin-top: 10px; }
        .map-box { width: 100%; height: 600px; overflow: hidden; border: 1px solid rgba(212,160,23,.12); border-radius: 28px; background: #f8f8f8; box-shadow: 0 12px 30px rgba(0,0,0,.08); }
        iframe { width: 100%; height: 100%; border: 0; filter: grayscale(.2) contrast(1.05); }
        .info-card { display: flex; flex-direction: column; justify-content: space-between; padding: 28px; border: 1px solid rgba(212,160,23,.12); border-radius: 28px; background: rgba(255,255,255,.95); box-shadow: 0 12px 30px rgba(0,0,0,.08); }
        .info-card h1 { margin-bottom: 10px; color: #111; font-size: 30px; }
        .info-card p { margin-bottom: 20px; color: #666; line-height: 1.6; }
        .location-details { display: flex; flex-direction: column; gap: 12px; margin-bottom: 25px; }
        .location-details div { display: flex; align-items: center; gap: 10px; color: #555; font-size: 14px; }
        .location-details i { color: #d4a017; }
        .action-buttons { display: flex; gap: 14px; }
        .btn { flex: 1; height: 52px; border: 0; border-radius: 14px; font-weight: 700; cursor: pointer; text-align: center; text-decoration: none; line-height: 52px; transition: .3s ease; }
        .btn-primary { color: #fff; background: linear-gradient(135deg, #ffe27a, #d4a017, #b8860b); }
        .btn-outline { color: #d4a017; border: 1px solid rgba(212,160,23,.25); background: #fff; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(243,197,71,.18); }
        @media (max-width: 900px) { .container { padding: 6px 20px 30px; } .map-wrapper { grid-template-columns: 1fr; } .map-box { height: 440px; } }
        @media (max-width: 520px) { .action-buttons { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="container">
        @include('userui.partials.navbar', ['active' => 'events'])

        <main class="map-wrapper">
            <div class="map-box">
                <iframe title="{{ $event->venue_name }} map" src="https://www.google.com/maps?q={{ urlencode($event->venue_address) }}&output=embed"></iframe>
            </div>
            <section class="info-card">
                <div>
                    <h1>{{ $event->venue_name ?: 'Event Venue' }}</h1>
                    <p>Located in the heart of the city, this premium venue is easily accessible and surrounded by hotels, restaurants, and transport hubs.</p>
                    <div class="location-details">
                        <div><i class="fas fa-location-dot" aria-hidden="true"></i> {{ $event->venue_address }}</div>
                        <div><i class="fas fa-road" aria-hidden="true"></i> Near major highways</div>
                        <div><i class="fas fa-car" aria-hidden="true"></i> Parking available</div>
                    </div>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" type="button" onclick="openDirections()">Show Way</button>
                    <a class="btn btn-outline" href="{{ route('your.events') }}">Back</a>
                </div>
            </section>
        </main>
    </div>

    <script>
        const destination = @json($event->venue_address);

        function openDirections() {
            const encodedDestination = encodeURIComponent(destination);
            const openMap = url => window.open(url, '_blank', 'noopener');

            if (!navigator.geolocation) {
                openMap(`https://www.google.com/maps/dir/?api=1&destination=${encodedDestination}`);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                position => openMap(`https://www.google.com/maps/dir/${position.coords.latitude},${position.coords.longitude}/${encodedDestination}`),
                () => openMap(`https://www.google.com/maps/dir/?api=1&destination=${encodedDestination}`)
            );
        }
    </script>
</body>
</html>
