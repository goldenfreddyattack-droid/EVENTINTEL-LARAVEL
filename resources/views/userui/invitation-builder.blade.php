<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EventIntel - Edit Invitation</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/navbar.css') }}">
    <style>
        :root { --border: #e3e6e8; --muted: #707980; --text: #242a2f; --gold: #f6c84c; --shadow: 0 12px 28px rgba(52,62,70,.12); }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { min-height: 100vh; color: var(--text); background: #fff; }
        .container { width: 100%; min-height: 100vh; padding: 6px 48px 40px; }
        .page { display: flex; flex-direction: column; gap: 24px; }
        .back-link { color: #b07c00; font-weight: 700; text-decoration: none; }
        .heading h1 { margin-bottom: 6px; font-size: 34px; }
        .heading p { color: var(--muted); }
        .builder { display: grid; grid-template-columns: minmax(280px, 1fr) minmax(360px, 1fr); gap: 24px; }
        .card { padding: 24px; border: 1px solid var(--border); border-radius: 24px; background: #fff; box-shadow: var(--shadow); }
        .card h2 { margin-bottom: 18px; font-size: 22px; }
        label { display: block; margin: 14px 0 6px; color: #4c5551; font-size: 13px; font-weight: 700; }
        input, textarea, select { width: 100%; padding: 12px 13px; border: 1px solid #d7dcde; border-radius: 12px; color: var(--text); background: #fafafa; outline: none; }
        input:focus, textarea:focus, select:focus { border-color: #d4a017; box-shadow: 0 0 0 3px rgba(212,160,23,.15); }
        textarea { min-height: 130px; resize: vertical; }
        input[type="color"] { width: 64px; height: 42px; padding: 4px; cursor: pointer; }
        .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; padding: 12px 18px; border: 0; border-radius: 12px; color: #242a2f; background: var(--gold); cursor: pointer; font-weight: 700; }
        .btn:hover { background: #e0b536; }
        .save-row { margin-top: 20px; }
        .success { margin-bottom: 0; padding: 12px 16px; border-radius: 12px; color: #176b3a; background: rgba(46,160,87,.12); }
        .error { margin-top: 5px; color: #b42318; font-size: 13px; }
        .preview { min-height: 560px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden; padding: 42px; border-radius: 18px; text-align: center; background: linear-gradient(135deg, #fff8d9, #fff, #f7e8a7); background-size: cover; background-position: center; }
        .preview::before, .preview::after { position: absolute; color: var(--preview-color, #f3c547); font-size: 44px; }
        .preview::before { top: 18px; left: 24px; content: '✦'; }
        .preview::after { right: 24px; bottom: 18px; content: '✦'; }
        .preview h3 { position: relative; z-index: 1; margin-bottom: 24px; color: var(--preview-color, #f3c547); font-size: 44px; line-height: 1.1; }
        .preview p { position: relative; z-index: 1; max-width: 520px; margin-bottom: 28px; font-size: 21px; line-height: 1.7; white-space: pre-line; }
        .preview .preview-button { position: relative; z-index: 1; padding: 12px 18px; border-radius: 12px; color: #242a2f; background: var(--preview-color, #f3c547); font-weight: 700; }
        .share-link { margin-top: 16px; color: var(--muted); font-size: 13px; overflow-wrap: anywhere; }
        .share-link a { color: #b07c00; font-weight: 700; }
        @media (max-width: 900px) { .container { padding: 6px 20px 30px; } .builder { grid-template-columns: 1fr; } .preview { min-height: 430px; } }
    </style>
</head>
<body>
    <div class="container page">
        @include('userui.partials.navbar', ['active' => 'events'])
        <a class="back-link" href="{{ route('your.events') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Your Events</a>
        <header class="heading">
            <h1>Edit Invitation</h1>
            <p>Customize the RSVP invitation for {{ $event->title ?: 'your event' }}.</p>
        </header>

        @if(session('success'))<div class="success">{{ session('success') }}</div>@endif

        <main class="builder">
            <form class="card" method="POST" enctype="multipart/form-data" action="{{ route('your.events.invitation', $event->event_id) }}">
                @csrf
                <h2>Invitation Details</h2>
                <label for="template">Invitation Template</label>
                <select name="template" id="template">
                    @foreach(['Classic', 'Wedding', 'Birthday', 'Corporate', 'Elegant'] as $template)
                        <option value="{{ $template }}" {{ $invitation->template === $template ? 'selected' : '' }}>{{ $template }}</option>
                    @endforeach
                </select>
                @error('template')<p class="error">{{ $message }}</p>@enderror

                <label for="title">Invitation Title</label>
                <input id="title" name="title" value="{{ old('title', $invitation->title) }}" required>
                @error('title')<p class="error">{{ $message }}</p>@enderror

                <label for="message">Message</label>
                <textarea id="message" name="message" required>{{ old('message', $invitation->message) }}</textarea>
                @error('message')<p class="error">{{ $message }}</p>@enderror

                <label for="theme_color">Theme Color</label>
                <input id="theme_color" type="color" name="theme_color" value="{{ old('theme_color', $invitation->theme_color) }}">
                @error('theme_color')<p class="error">{{ $message }}</p>@enderror

                <label for="font_style">Font</label>
                <select id="font_style" name="font_style">
                    @foreach(['Segoe UI', 'Georgia', 'Arial'] as $font)
                        <option value="{{ $font }}" {{ old('font_style', $invitation->font_style) === $font ? 'selected' : '' }}>{{ $font }}</option>
                    @endforeach
                </select>

                <label for="button_text">RSVP Button Text</label>
                <input id="button_text" name="button_text" value="{{ old('button_text', $invitation->button_text) }}" required>
                @error('button_text')<p class="error">{{ $message }}</p>@enderror

                <label for="background">Background Image</label>
                <input id="background" type="file" name="background" accept="image/*">
                @error('background')<p class="error">{{ $message }}</p>@enderror

                <div class="save-row"><button class="btn" type="submit"><i class="fas fa-save" aria-hidden="true"></i>&nbsp; Save Invitation</button></div>
            </form>

            <section class="card">
                <h2>Preview / Share</h2>
                <div class="preview" id="preview" style="--preview-color: {{ $invitation->theme_color }}; font-family: {{ $invitation->font_style }};{{ $invitation->background_image ? ' background-image: url(' . asset('storage/' . $invitation->background_image) . ');' : '' }}">
                    <h3 id="previewTitle">{{ $invitation->title }}</h3>
                    <p id="previewMessage">{{ $invitation->message }}</p>
                    <span class="preview-button" id="previewButton">{{ $invitation->button_text }}</span>
                </div>
                <p class="share-link">Guest link: <a href="{{ url('/rsvp?event=' . $event->event_id) }}">{{ url('/rsvp?event=' . $event->event_id) }}</a></p>
            </section>
        </main>
    </div>

    <script>
        const preview = document.getElementById('preview');
        const title = document.getElementById('title');
        const message = document.getElementById('message');
        const color = document.getElementById('theme_color');
        const font = document.getElementById('font_style');
        const button = document.getElementById('button_text');
        const background = document.getElementById('background');

        title.addEventListener('input', () => document.getElementById('previewTitle').textContent = title.value);
        message.addEventListener('input', () => document.getElementById('previewMessage').textContent = message.value);
        color.addEventListener('input', () => preview.style.setProperty('--preview-color', color.value));
        font.addEventListener('change', () => preview.style.fontFamily = font.value);
        button.addEventListener('input', () => document.getElementById('previewButton').textContent = button.value);
        background.addEventListener('change', () => {
            const file = background.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = event => preview.style.backgroundImage = `linear-gradient(rgba(255,255,255,.35),rgba(255,255,255,.35)), url('${event.target.result}')`;
            reader.readAsDataURL(file);
        });
    </script>
</body>
</html>
