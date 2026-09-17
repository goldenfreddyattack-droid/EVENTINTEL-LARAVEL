<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Review {{ $event->title ?: 'Event' }} | EventIntel</title>
    <style>
        :root { --ink:#242a2f; --muted:#6e706d; --gold:#f3c547; --paper:#fffdf7; --line:#e9e2d2; }
        * { box-sizing:border-box; } body { margin:0; min-height:100vh; background:linear-gradient(145deg,#fffaf0,#f4f1e8); color:var(--ink); font-family:Georgia, 'Times New Roman', serif; }
        .shell { width:min(720px,calc(100% - 32px)); margin:0 auto; padding:42px 0 56px; }
        .masthead { text-align:center; margin-bottom:28px; } .mark { color:#9d7612; font:700 12px/1.2 Arial,sans-serif; letter-spacing:.16em; text-transform:uppercase; }
        h1 { margin:12px 0 8px; font-size:clamp(30px,7vw,52px); line-height:1.02; } .intro { color:var(--muted); font:16px/1.6 Arial,sans-serif; margin:0 auto; max-width:520px; }
        .review-list { display:grid; gap:14px; } .review-card { background:rgba(255,255,255,.84); border:1px solid var(--line); border-radius:14px; padding:20px; box-shadow:0 10px 30px rgba(73,62,35,.06); }
        .category { color:#9d7612; font:700 11px Arial,sans-serif; letter-spacing:.12em; text-transform:uppercase; } h2 { margin:7px 0 16px; font-size:22px; }
        .stars { display:flex; gap:5px; } .star { color:#d8d4c8; background:transparent; border:0; cursor:pointer; font-size:32px; line-height:1; padding:0 2px; } .star.selected,.star:hover { color:var(--gold); }
        textarea,input { width:100%; border:1px solid var(--line); border-radius:9px; background:#fffefb; font:14px Arial,sans-serif; padding:11px; } textarea { min-height:82px; resize:vertical; margin-top:14px; }
        .reviewer { margin-top:10px; } .save { margin-top:13px; border:0; border-radius:9px; background:var(--gold); color:var(--ink); cursor:pointer; font:700 14px Arial,sans-serif; padding:11px 16px; } .save:disabled { opacity:.55; cursor:wait; }
        .message { display:none; margin-top:10px; color:#39734b; font:13px Arial,sans-serif; } .message.show { display:block; } .empty { text-align:center; color:var(--muted); font:15px Arial,sans-serif; padding:30px; }
    </style>
</head>
<body>
    <main class="shell">
        <header class="masthead"><div class="mark">EventIntel guest review</div><h1>{{ $event->title ?: 'Your event' }}</h1><p class="intro">How was your experience? Rate the suppliers who helped make this event happen.</p></header>
        <section class="review-list" aria-label="Selected event services">
            @forelse ($services as $service)
                <article class="review-card" data-column="{{ $service['service_column'] }}">
                    <div class="category">{{ $service['category'] }}</div><h2>{{ $service['name'] }}</h2>
                    <div class="stars" role="group" aria-label="Rate {{ $service['name'] }}">
                        @for ($star = 1; $star <= 5; $star++)<button type="button" class="star {{ $star <= $service['rating'] ? 'selected' : '' }}" data-value="{{ $star }}" aria-label="{{ $star }} stars">★</button>@endfor
                    </div>
                    <textarea placeholder="Share a note about this service...">{{ $service['review_text'] }}</textarea>
                    <input class="reviewer" type="text" maxlength="100" placeholder="Your name (optional)">
                    <button class="save" type="button">Save review</button><div class="message" role="status"></div>
                </article>
            @empty
                <div class="empty">No selected services are available for review yet.</div>
            @endforelse
        </section>
    </main>
    <script>
        const token = @json($token);
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        document.querySelectorAll('.review-card').forEach(card => {
            const stars = [...card.querySelectorAll('.star')];
            let rating = stars.filter(star => star.classList.contains('selected')).length;
            const paint = value => stars.forEach(star => star.classList.toggle('selected', Number(star.dataset.value) <= value));
            stars.forEach(star => star.addEventListener('click', () => { rating = Number(star.dataset.value); paint(rating); }));
            card.querySelector('.save').addEventListener('click', async () => {
                const button = card.querySelector('.save'); const message = card.querySelector('.message');
                if (!rating) { message.textContent = 'Please choose a star rating first.'; message.style.color = '#9a3d32'; message.classList.add('show'); return; }
                button.disabled = true;
                try {
                    const response = await fetch(`/review/${encodeURIComponent(token)}`, { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify({service_column:card.dataset.column,rating,review_text:card.querySelector('textarea').value,reviewer_name:card.querySelector('.reviewer').value}) });
                    const data = await response.json(); if (!response.ok || !data.success) throw new Error(data.message || 'Unable to save review.');
                    message.textContent = 'Thanks, your review was saved.'; message.style.color = '#39734b'; message.classList.add('show');
                } catch (error) { message.textContent = error.message; message.style.color = '#9a3d32'; message.classList.add('show'); } finally { button.disabled = false; }
            });
        });
    </script>
</body>
</html>
