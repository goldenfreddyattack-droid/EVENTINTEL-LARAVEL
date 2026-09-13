<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Scanner - {{ $event->title ?? 'Event' }}</title>
    <style>
        :root {
            --bg: #f5f1ea; --panel: #fffdfb; --ink: #1f2430; --muted: #60707d; --border: #e7e0d5;
            --gold: #f3c547; --gold-deep: #ce9d14; --green: #2d9c62; --red: #d54747; --soft: #f1e9df;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--ink);
            min-height: 100vh; padding: 28px 18px;
        }
        .shell {
            max-width: 1100px; margin: 0 auto; background: var(--panel); border: 1px solid var(--border);
            border-radius: 26px; box-shadow: 0 20px 40px rgba(31,36,48,0.08); overflow: hidden;
        }
        .topbar {
            padding: 24px 28px 18px; border-bottom: 1px solid var(--border); background: linear-gradient(135deg, #fffaf1 0%, #f5efe8 100%);
        }
        .eyebrow {
            display: inline-block; font-size: 11px; letter-spacing: 0.14em; text-transform: uppercase; font-weight: 800; color: var(--gold-deep);
        }
        h1 { margin: 10px 0 6px; font-size: clamp(30px, 4vw, 42px); }
        .subtitle { margin: 0; color: var(--muted); }
        .content {
            display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 22px; padding: 24px 28px 28px;
        }
        .scanner-panel, .summary-panel {
            border: 1px solid var(--border); border-radius: 20px; background: #fff; padding: 18px;
        }
        #reader {
            width: 100%; min-height: 340px; border-radius: 18px; overflow: hidden; background: #101828;
        }
        .result {
            margin-top: 16px; padding: 14px 16px; border-radius: 12px; font-weight: 700;
            background: rgba(243, 197, 71, 0.08); border: 1px solid rgba(243, 197, 71, 0.2); color: var(--ink);
        }
        .result.success { background: rgba(45, 156, 98, 0.08); border-color: rgba(45, 156, 98, 0.2); color: #123f2d; }
        .result.error { background: rgba(213, 71, 71, 0.08); border-color: rgba(213, 71, 71, 0.2); color: #4f1e1e; }
        .stats {
            display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px;
        }
        .stat {
            background: var(--soft); border: 1px solid var(--border); border-radius: 14px; padding: 14px 12px; text-align: center;
        }
        .stat .label { font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); }
        .stat .value { font-size: 28px; font-weight: 800; margin-top: 8px; }
        .guest-list {
            list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;
            max-height: 370px; overflow: auto;
        }
        .guest-item {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 12px; border-radius: 12px; border: 1px solid var(--border); background: #fbfaf8;
        }
        .guest-item strong { display: block; }
        .guest-item small { color: var(--muted); }
        .status-pill {
            padding: 7px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.03em;
            white-space: nowrap;
        }
        .status-pill.checked { background: rgba(45, 156, 98, 0.1); color: #123f2d; }
        .status-pill.pending { background: rgba(243, 197, 71, 0.12); color: #71540d; }
        .actions { margin-top: 18px; display: flex; gap: 10px; }
        .back-btn, .btn-soft {
            display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 700;
            padding: 11px 14px; border-radius: 10px; border: 1px solid var(--border);
        }
        .back-btn { background: #fff; color: var(--ink); }
        .btn-soft { background: var(--soft); color: var(--ink); }
        @media (max-width: 860px) {
            .content { grid-template-columns: 1fr; }
            .stats { grid-template-columns: repeat(3, minmax(100px, 1fr)); }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="topbar">
            <span class="eyebrow">Check-In</span>
            <h1>{{ $event->title ?? 'Event Entry' }}</h1>
            <p class="subtitle">Scan each guest QR at the venue before they enter.</p>
        </div>

        <div class="content">
            <section class="scanner-panel">
                <div id="reader"></div>
                <div id="result" class="result">Waiting for scan...</div>
                <div class="actions">
                    <a class="back-btn" href="{{ route('your.events.guests', $event->event_id) }}">Back to Guest List</a>
                    <a class="btn-soft" href="{{ route('your.events', []) }}">Your Events</a>
                </div>
            </section>

            <aside class="summary-panel">
                <div class="stats">
                    <div class="stat">
                        <div class="label">Total</div>
                        <div class="value" id="guest-total">{{ $guests->count() }}</div>
                    </div>
                    <div class="stat">
                        <div class="label">Checked</div>
                        <div class="value" id="guest-checked">{{ $guests->filter(fn($g) => (int) ($g->attended ?? 0) === 1)->count() }}</div>
                    </div>
                    <div class="stat">
                        <div class="label">Left</div>
                        <div class="value" id="guest-left">{{ $guests->filter(fn($g) => (int) ($g->attended ?? 0) !== 1)->count() }}</div>
                    </div>
                </div>

                <ul class="guest-list" id="guest-list">
                    @foreach($guests as $guest)
                        <li class="guest-item" data-guest-id="{{ $guest->guest_id }}" data-attended="{{ (int) ($guest->attended ?? 0) }}">
                            <div>
                                <strong>{{ $guest->name }}</strong>
                                <small>{{ $guest->qr_code }}</small>
                            </div>
                            <span class="status-pill {{ (int) ($guest->attended ?? 0) === 1 ? 'checked' : 'pending' }}">
                                {{ (int) ($guest->attended ?? 0) === 1 ? 'Checked' : 'Pending' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </aside>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script>
        const resultBox = document.getElementById('result');
        const guestTotal = document.getElementById('guest-total');
        const guestChecked = document.getElementById('guest-checked');
        const guestLeft = document.getElementById('guest-left');
        const guestList = document.getElementById('guest-list');

        function setResult(message, isSuccess) {
            resultBox.textContent = message;
            resultBox.classList.remove('success', 'error');
            resultBox.classList.add(isSuccess ? 'success' : 'error');
        }

        function updateGuestStatus(name, qrCode) {
            const guestItem = Array.from(guestList.querySelectorAll('.guest-item')).find((item) => {
                const code = item.querySelector('small')?.textContent?.trim();
                return code === qrCode;
            });

            if (guestItem) {
                guestItem.dataset.attended = '1';
                guestItem.querySelector('.status-pill').className = 'status-pill checked';
                guestItem.querySelector('.status-pill').textContent = 'Checked';
            }

            const checkedCount = Array.from(guestList.querySelectorAll('.guest-item')).filter((item) => item.dataset.attended === '1').length;
            const leftCount = Math.max(0, Number(guestTotal.textContent) - checkedCount);
            guestChecked.textContent = checkedCount;
            guestLeft.textContent = leftCount;

            if (name) {
                setResult(`${name} checked in successfully.`, true);
            }
        }

        function verify(qrCode) {
            const token = document.querySelector('meta[name="csrf-token"]').content;

            fetch('{{ route("your.events.scanner", $event->event_id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: 'qr=' + encodeURIComponent(qrCode)
            })
            .then(async response => {
                const data = await response.json().catch(() => ({ ok: false, msg: 'Unable to process QR scan.' }));
                if (!response.ok) {
                    throw new Error(data.msg || 'Scan failed.');
                }
                return data;
            })
            .then(data => {
                if (data && data.msg) {
                    const guestName = data.msg.replace(/^Welcome\s+/i, '');
                    updateGuestStatus(guestName, qrCode);
                }
            })
            .catch((error) => {
                setResult(error.message || 'Scan failed.', false);
            });
        }

        const scanner = new Html5Qrcode("reader");
        scanner.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 260, height: 260 } }, (decodedText) => {
            verify(decodedText);
        }).catch(() => {
            setResult('Camera access is unavailable or blocked by the browser.', false);
        });
    </script>
</body>
</html>
