<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EventIntel - Guests</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/navbar.css') }}">
    <style>
        :root { --border: #e3e6e8; --muted: #707980; --text: #242a2f; --shadow: 0 12px 28px rgba(52,62,70,.12); }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { min-height: 100vh; color: var(--text); background: #fff; }
        .container { width: 100%; min-height: 100vh; padding: 6px 48px 40px; }
        .page { display: flex; flex-direction: column; gap: 24px; }
        .heading h1 { margin-bottom: 6px; font-size: 34px; }
        .heading p { color: var(--muted); }
        .back-link { color: #b07c00; font-weight: 700; text-decoration: none; }
        .card { padding: 20px; border: 1px solid var(--border); border-radius: 20px; background: #fff; box-shadow: var(--shadow); }
        .guest-form { display: flex; flex-wrap: wrap; gap: 12px; }
        .guest-form input { flex: 1 1 220px; min-width: 180px; padding: 13px 14px; border: 1px solid var(--border); border-radius: 12px; color: var(--text); background: #fff; outline: none; }
        .guest-form input:focus { border-color: #d4a017; box-shadow: 0 0 0 3px rgba(212,160,23,.15); }
        .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; padding: 12px 18px; border: 0; border-radius: 12px; color: #242a2f; background: #f6c84c; cursor: pointer; font-weight: 700; text-decoration: none; }
        .btn:hover { background: #e0b536; }
        .success { padding: 12px 16px; border-radius: 12px; color: #176b3a; background: rgba(46, 160, 87, .12); }
        .error { margin-top: 12px; color: #b42318; font-size: 13px; }
        .guest-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
        .guest-card { position: relative; padding: 18px; border: 1px solid var(--border); border-radius: 18px; background: #fff; box-shadow: 0 8px 20px rgba(52,62,70,.08); }
        .guest-card h2 { margin-bottom: 12px; font-size: 19px; }
        .guest-card p { margin-top: 7px; color: var(--muted); font-size: 14px; overflow-wrap: anywhere; }
        .guest-card strong { color: var(--text); }
        .qr-code { color: #0875c1 !important; font-weight: 700; }
        .status-attended { color: #238636 !important; font-weight: 700; }
        .status-pending { color: #b42318 !important; font-weight: 700; }
        .empty-state { color: var(--muted); }
        @media (max-width: 900px) { .container { padding: 6px 20px 30px; } }
    </style>
</head>
<body>
    <div class="container page">
        @include('userui.partials.navbar', ['active' => 'events'])

        <a class="back-link" href="{{ route('your.events') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Events</a>
        <header class="heading">
            <h1>Guest QR Management</h1>
            <p>{{ $event->title ?: 'Untitled event' }}</p>
        </header>

        @if(session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        <form class="card guest-form" method="POST" action="{{ route('your.events.guests', $event->event_id) }}">
            @csrf
            <input name="name" value="{{ old('name') }}" placeholder="Guest name" required>
            <input name="email" type="email" value="{{ old('email') }}" placeholder="Email">
            <input name="phone" value="{{ old('phone') }}" placeholder="Phone">
            <button class="btn" type="submit"><i class="fas fa-qrcode" aria-hidden="true"></i>&nbsp; Add Guest + Generate QR</button>
            @error('name')<p class="error">{{ $message }}</p>@enderror
            @error('email')<p class="error">{{ $message }}</p>@enderror
            @error('phone')<p class="error">{{ $message }}</p>@enderror
        </form>

        <section class="card">
            <h2>Guests <span style="color:var(--muted);font-size:15px;font-weight:600;">({{ $guests->count() }})</span></h2>
            @if($guests->isEmpty())
                <p class="empty-state" style="margin-top:12px;">No guests added yet.</p>
            @else
                <div class="guest-list" style="margin-top:16px;">
                    @foreach($guests as $guest)
                        <article class="guest-card">
                            <h2>{{ $guest->name }}</h2>
                            @if($guest->email)<p><strong>Email:</strong> {{ $guest->email }}</p>@endif
                            @if($guest->phone)<p><strong>Phone:</strong> {{ $guest->phone }}</p>@endif
                            <p class="qr-code"><strong>QR:</strong> {{ $guest->qr_code }}</p>
                            <p class="{{ $guest->attended ? 'status-attended' : 'status-pending' }}">
                                <strong>Status:</strong> {{ $guest->attended ? 'Attended' : 'Not yet scanned' }}
                            </p>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</body>
</html>
