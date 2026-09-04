<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventIntel - {{ $serviceLabel }}</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/navbar.css') }}">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f8f8f8; color: #111; font-family: 'Segoe UI', sans-serif; }
        .page { width: min(1180px, 100%); margin: auto; padding: 6px 32px 48px; }
        .heading { display: flex; justify-content: space-between; align-items: end; gap: 18px; margin: 38px 0 28px; }
        .heading h1 { margin: 0 0 8px; font-size: 42px; }
        .heading p { margin: 0; color: #666; }
        .back { color: #a77700; text-decoration: none; font-weight: 700; }
        .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
        .card { height: 420px; display: flex; flex-direction: column; background: #fff; border: 1px solid #eee2b7; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 24px #0000000b; }
        .card-image { height: 150px; flex: 0 0 150px; background: #f3c547; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 42px; }
        .card-body { min-height: 0; flex: 1; display: flex; flex-direction: column; padding: 18px; }
        .card h2 { height: 46px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; font-size: 19px; margin: 0 0 5px; }
        .supplier { height: 20px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; color: #777; font-size: 13px; }
        .meta { height: 20px; display: flex; gap: 14px; overflow: hidden; white-space: nowrap; color: #777; font-size: 13px; margin: 14px 0; }
        .description { height: 48px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; color: #666; line-height: 1.5; margin: 0; }
        .actions { height: 42px; display: flex; justify-content: flex-end; align-items: center; margin-top: auto; }
        .button { width: 106px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 10px; padding: 10px 15px; background: #f3c547; color: #fff; font-weight: 800; text-decoration: none; cursor: pointer; }
        .empty { background: #fff; border: 1px solid #eee2b7; border-radius: 16px; padding: 35px; text-align: center; color: #666; }
        @media (max-width: 800px) { .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 520px) { .page { padding: 6px 18px 32px; } .heading { display: block; } .heading h1 { font-size: 32px; } .heading .back { display: inline-block; margin-top: 18px; } .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="page">
        <header class="heading">
            <div>
                <h1>{{ $serviceLabel }}</h1>
                <p>Browse all available {{ strtolower($serviceLabel) }} services.</p>
            </div>
            <a class="back" href="{{ route('home') }}">Back to Homepage</a>
        </header>

        @if (count($services) === 0)
            <div class="empty">No {{ strtolower($serviceLabel) }} services are available yet.</div>
        @else
            <main class="grid">
                @foreach ($services as $serviceRecord)
                    <article class="card">
                        <div class="card-image"><i class="fas fa-{{ $serviceKey === 'venue' ? 'location-dot' : ($serviceKey === 'photographer' ? 'camera' : ($serviceKey === 'host' ? 'microphone' : 'briefcase')) }}"></i></div>
                        <div class="card-body">
                            <h2>{{ $serviceRecord->name }}</h2>
                            <div class="supplier">{{ $serviceRecord->business_name ?: ($serviceRecord->supplier_name ?: 'EventIntel supplier') }}</div>
                            <div class="meta">
                                <span>★ {{ number_format((float) ($serviceRecord->rating ?? 0), 1) }}</span>
                                <span>{{ $serviceRecord->price ? '₱'.number_format((float) $serviceRecord->price) : 'Price on request' }}</span>
                            </div>
                            <p class="description">{{ $serviceRecord->description ?: 'Professional service for your event.' }}</p>
                            <div class="actions">
                                <a class="button" href="{{ route('carousel.services.show', [$serviceKey, $serviceRecord->service_id]) }}">View</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </main>
        @endif
    </div>
</body>
</html>
