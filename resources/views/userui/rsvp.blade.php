<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSVP - {{ $event->title ?? 'Event Invitation' }}</title>
    <style>
        :root {
            --bg: #f8f4ee;
            --panel: #fffdfb;
            --card: #fff;
            --gold: #f3c547;
            --gold-deep: #d7a51d;
            --text: #1f2430;
            --muted: #5f6b7a;
            --border: #eae1d5;
            --soft: #f1e9df;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(180deg, #f7f2ea 0%, #f3efe9 100%);
            color: var(--text); display: flex; align-items: center; justify-content: center; padding: 32px 18px;
        }
        .rsvp-shell {
            width: min(760px, 100%); background: var(--panel); border: 1px solid var(--border);
            border-radius: 26px; overflow: hidden; box-shadow: 0 18px 38px rgba(31,36,48,0.08);
        }
        .rsvp-top {
            padding: 26px 28px 20px; border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, #fffaf1 0%, #f6f0e9 100%);
        }
        .eyebrow { display: inline-block; font-size: 11px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--gold-deep); font-weight: 700; }
        h1 { margin: 12px 0 6px; font-size: clamp(26px, 4vw, 40px); line-height: 1.1; color: var(--text); }
        .sub { color: var(--muted); margin: 0; }
        .rsvp-body { padding: 26px 28px 30px; }
        .invite-card {
            background: var(--card); color: var(--text); border-radius: 18px; padding: 22px 20px; border: 1px solid var(--border);
            box-shadow: 0 10px 24px rgba(31,36,48,0.04);
        }
        .invite-card h2 { margin: 0 0 10px; font-size: clamp(22px, 3vw, 32px); }
        .invite-card p { margin: 0 0 10px; line-height: 1.6; color: var(--muted); }
        .invite-card .button {
            display: inline-block; background: var(--gold); color: #1c1d1f; border: none; text-decoration: none;
            font-weight: 800; padding: 12px 18px; border-radius: 10px; margin-top: 8px;
        }
        form { margin-top: 22px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .field { display: flex; flex-direction: column; gap: 8px; }
        .field.full { grid-column: 1 / -1; }
        label { font-size: 13px; font-weight: 700; color: var(--text); }
        input {
            width: 100%; padding: 12px 14px; border: 1px solid var(--border); border-radius: 10px;
            background: #fff; color: var(--text); font-size: 15px; outline: none;
        }
        input:focus { border-color: rgba(243,197,71,0.9); box-shadow: 0 0 0 4px rgba(243,197,71,0.12); }
        button {
            margin-top: 8px; grid-column: 1 / -1; background: linear-gradient(135deg, #f9d86d, #f3c547, #dcae1d);
            color: #1f2430; border: none; font-weight: 800; border-radius: 12px; padding: 14px 18px; cursor: pointer;
            box-shadow: 0 12px 22px rgba(243,197,71,0.18);
        }
        .status-box {
            margin-top: 22px; background: rgba(30, 174, 116, 0.08); border: 1px solid rgba(30, 174, 116, 0.2);
            border-radius: 14px; padding: 18px; color: #1c3d32;
        }
        .status-box strong { color: var(--text); }
        .qr-code { font-size: 18px; letter-spacing: 1px; }
        @media (max-width: 640px) { form { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="rsvp-shell">
        <div class="rsvp-top">
            <span class="eyebrow">Invitation</span>
            <h1>{{ $event->title ?? 'You are invited' }}</h1>
            <p class="sub">Please confirm your attendance below.</p>
        </div>
        <div class="rsvp-body">
            <div class="invite-card">
                <h2>{{ $invitation->title ?? "You're Invited" }}</h2>
                <p>{{ $invitation->message ?? 'Please confirm your attendance.' }}</p>
                @if(!empty($invitation->button_text))
                    <span class="button">{{ $invitation->button_text }}</span>
                @endif
            </div>

            @if($success && $guest)
                <div class="status-box">
                    <strong>RSVP Confirmed.</strong>
                    <p>You are now marked as confirmed for this event.</p>
                    <p>Save this QR code as a screenshot and show it to the event staff at the entrance on the day of the event.</p>
                    <div class="qr-box">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($guest->qr_code) }}" alt="Guest QR code">
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('rsvp') }}">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                    <div class="field full">
                        <label for="name">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Enter your full name" required>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com">
                    </div>
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}" placeholder="09xxxxxxxxx">
                    </div>
                    <button type="submit">Confirm RSVP</button>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
