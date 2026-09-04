<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EventIntel - Create Event</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/navbar.css') }}">
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f8f8f8;color:#111;font-family:'Segoe UI',sans-serif}.page{width:100%;min-height:100vh;padding:6px 48px 48px}.heading{position:relative;z-index:5;width:260px;margin:34px 0 24px 6vw;pointer-events:none}.heading h1{font-size:34px;line-height:1.15;margin:0 0 8px}.heading p{color:#666;font-size:14px;line-height:1.6}.page:after{content:'';position:fixed;inset:0;background:rgba(18,22,25,.58);z-index:4;pointer-events:none}.wizard-layout{position:fixed;z-index:5;inset:55% auto auto 50%;transform:translate(-50%,-50%);display:flex;gap:20px;width:min(820px,calc(100vw - 36px));max-height:calc(100vh - 56px);align-items:stretch}.wizard-steps{width:160px;padding:20px 0;display:flex;flex-direction:column;gap:12px}.step-pill{border:1px solid #eee2b7;border-radius:22px;background:rgba(255,255,255,.8);padding:14px 16px;font-weight:800;font-size:13px}.step-pill.active{background:#f3c547;border-color:#d8ab24}.form-card{width:100%;max-width:600px;max-height:calc(100vh - 110px);overflow:auto;background:#fff;border:1px solid #eee2b7;border-radius:24px;padding:24px 20px;box-shadow:0 20px 60px rgba(0,0,0,.25)}.section{display:none;border:0;padding:0;margin:0}.section.active{display:block}.section h2{color:#b07c00;font-size:18px;margin:0 0 18px}.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.field{display:flex;flex-direction:column;gap:7px}.field.full{grid-column:1/-1}.field label{font-weight:700;font-size:13px;text-transform:uppercase;letter-spacing:.08em;color:#777}.field input,.field select{width:100%;padding:13px 14px;border:1px solid #dfd6b7;border-radius:12px;background:#fff;font:inherit}.types,.services{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}.choice{border:1px solid #e8e2d2;border-radius:16px;padding:14px;text-align:left;cursor:pointer;font-weight:600}.choice:has(input:checked){background:#fff1b8;border-color:#d6a91d}.choice input{margin-right:10px}.theme-grid,.package-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px}.theme-chip,.package-card{border:1px solid #e8e2d2;border-radius:14px;background:#fff;padding:11px;text-align:center;cursor:pointer;font:inherit}.theme-chip.selected,.package-card.selected{background:#fff1b8;border-color:#d6a91d}.package-card strong,.package-card b{display:block;color:#b07c00}.package-card small{display:block;color:#777;margin-top:6px;font-size:11px}.service-list{display:grid;gap:10px}.service-row{display:grid;grid-template-columns:1fr auto auto;align-items:center;gap:12px;border:1px solid #eee2b7;border-radius:14px;padding:12px 14px}.service-row strong{display:block}.service-row small{display:block;color:#777;margin-top:3px}.service-row input[type=checkbox]{width:24px;height:24px;accent-color:#d6a609;cursor:default;pointer-events:none}.service-view{border:0;border-radius:14px;padding:8px 13px;background:#fff1b8;color:#8a6800;font-weight:800;cursor:pointer}.service-option{display:block;width:100%;border:1px solid #eee2b7;border-radius:12px;background:#fff;text-align:left;padding:13px 15px;margin-bottom:10px;cursor:pointer}.service-option:hover{background:#fff1b8}.service-option strong,.service-option small{display:block}.service-option small{color:#777;margin-top:5px}.service-catalog-link{display:inline-block;margin-bottom:14px;color:#a77700;font-size:13px;font-weight:800}.catalog-frame{display:block;width:100%;height:70vh;border:0;border-radius:12px;background:#f8f8f8}.error{color:#a33;font-size:13px;margin:6px 0 0}.actions{display:flex;justify-content:flex-end;gap:12px;margin-top:28px}.button{border:0;border-radius:20px;padding:12px 22px;font-weight:800;text-decoration:none;cursor:pointer}.button.secondary{background:#f1f1f1;color:#333}.button.primary{background:#f3c547;color:#111}.step-note{color:#777;line-height:1.5;font-size:13px;margin:-6px 0 18px}.service-modal{position:fixed;inset:0;z-index:10;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(0,0,0,.5)}.service-modal.open{display:flex}.service-panel{width:min(1000px,100%);max-height:90vh;overflow:auto;background:#fff;border-radius:20px;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,.3)}.service-panel header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}.service-panel h3{margin:0;color:#b07c00}.close-service{border:0;background:#f1f1f1;border-radius:50%;width:34px;height:34px;cursor:pointer;font-size:20px}@media(max-width:700px){.page{padding:6px 18px 32px}.heading{width:220px;margin-left:18px}.wizard-layout{inset:50% auto auto 50%;width:calc(100vw - 24px);gap:0}.wizard-steps{display:none}.form-card{max-height:calc(100vh - 28px);padding:24px 18px}.grid{grid-template-columns:1fr}.types,.services,.theme-grid,.package-grid{grid-template-columns:1fr 1fr}}@media(max-width:430px){.types,.services,.theme-grid,.package-grid{grid-template-columns:1fr}}
        .review-modal{display:none;position:fixed;inset:0;z-index:20;align-items:center;justify-content:center;padding:20px;background:#12161999}.review-modal.open{display:flex}.review-panel{width:min(560px,100%);max-height:90vh;overflow:auto;background:#fff;border:1px solid #eee2b7;border-radius:24px;padding:24px;box-shadow:0 20px 60px #0004}.review-list{display:grid;gap:10px;margin:18px 0}.review-row{display:flex;justify-content:space-between;gap:16px;padding:13px 14px;border:1px solid #eee2d2;border-radius:12px}.review-row small{display:block;color:#777;margin-top:4px}.review-price{color:#a77700;font-weight:800;white-space:nowrap}.review-total{display:flex;justify-content:space-between;border-top:2px solid #eee2b7;padding-top:16px;font-size:20px;font-weight:900}.review-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px}.review-actions button{height:42px;border:0;border-radius:10px;padding:10px 16px;font-weight:800;cursor:pointer}.review-actions .secondary{background:#f1f1f1;color:#222}.review-actions .primary{background:#f3c547;color:#111}
    </style>
</head>
<body>
<div class="page">
    @include('userui.partials.navbar', ['active' => 'create-event', 'disabled' => true])

    @if (count($errors))
        @php($validationErrors = is_object($errors) ? $errors->all() : $errors)
        <div class="form-card" style="margin-bottom:18px;color:#8b1e1e;background:#fff7f7;"><strong>Please review these details:</strong><ul style="margin:8px 0 0 18px;">@foreach ($validationErrors as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="wizard-layout">
        <aside class="wizard-steps" aria-label="Event creation steps">
            <div class="step-pill active" data-step-label="1">1. Choose Event</div>
            <div class="step-pill" data-step-label="2">2. Schedule</div>
            <div class="step-pill" data-step-label="3">3. Services</div>
        </aside>
    <form class="form-card" method="POST" action="{{ route('events.store') }}">
        @csrf
        <section class="section active" data-step="1">
            <h2>Event Details</h2>
            <div class="field" style="margin-bottom:16px;"><label for="event_name">Event name</label><input id="event_name" name="event_name" value="{{ old('event_name') }}" maxlength="150" placeholder="Enter a name for your event" required></div>
            @error('event_name')<p class="error">{{ $message }}</p>@enderror
            <div class="types">
                @foreach ($eventTypes as $type)
                    <label class="choice"><input type="radio" name="event_type" value="{{ $type }}" required {{ old('event_type', $prefill['event_type']) === $type ? 'checked' : '' }}>{{ $type }}</label>
                @endforeach
            </div>
            <div class="field" style="margin-top:16px;"><label for="other_event_type">Other event type</label><input id="other_event_type" name="other_event_type" value="{{ old('other_event_type') }}" placeholder="Only needed when Others is selected"></div>
            @error('event_type')<p class="error">{{ $message }}</p>@enderror
            @error('other_event_type')<p class="error">{{ $message }}</p>@enderror
            <div class="section-subtitle" style="color:#b07c00;font-weight:800;margin:22px 0 10px;">Theme</div>
            <div id="themeChips" class="theme-grid"></div>
            <input id="theme" name="theme" value="{{ old('theme') }}" placeholder="Choose a theme or type your own" style="display:none;">
            @error('theme')<p class="error">{{ $message }}</p>@enderror
            <div class="section-subtitle" style="color:#b07c00;font-weight:800;margin:22px 0 10px;">Suggested Packages</div>
            <p class="step-note">Choose a package to pre-fill your budget and services.</p>
            <div id="packageCards" class="package-grid"></div>
            <div class="actions"><a class="button secondary" href="{{ route('home') }}">Cancel</a><button class="button primary" type="button" data-next>Next</button></div>
        </section>

        <section class="section" data-step="2">
            <h2>Schedule and Attendees</h2>
            <p class="step-note">Choose a date and time so availability can be checked before the event is created.</p>
            <div class="grid">
                <div class="field"><label for="event_date">Date</label><input id="event_date" type="date" name="event_date" min="{{ now()->toDateString() }}" value="{{ old('event_date') }}" required>@error('event_date')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="guest_count">Number of attendees</label><input id="guest_count" type="number" name="guest_count" min="1" value="{{ old('guest_count', 1) }}" required>@error('guest_count')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="event_time">Start time</label><input id="event_time" type="time" name="event_time" value="{{ old('event_time') }}" required>@error('event_time')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="event_end_time">End time</label><input id="event_end_time" type="time" name="event_end_time" value="{{ old('event_end_time') }}" required>@error('event_end_time')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="event_budget">Estimated budget (PHP)</label><input id="event_budget" type="number" name="event_budget" min="0" step="0.01" value="{{ old('event_budget', $prefill['budget']) }}"></div>
            </div>
            <div class="actions"><button class="button secondary" type="button" data-back>Back</button><button class="button primary" type="button" data-next>Next</button></div>
        </section>

        <section class="section" data-step="3">
            <h2>Choose Services</h2>
            <p class="step-note">View available suppliers, then choose the services you want for this event.</p>
            <div class="service-list">
                @foreach ([['venue','Venue'],['clothes','Clothes'],['catering','Food & Catering'],['host','Host'],['sounds_lights','Sounds & Lights'],['photographer','Photographer']] as [$service, $label])
                    <div class="service-row" data-service-row="{{ $service }}"><div><strong>{{ $label }}</strong><small id="selected-{{ $service }}">No service selected</small></div><button class="service-view" type="button" data-service-view="{{ $service }}">View</button><input type="checkbox" name="services[]" value="{{ $service }}" {{ in_array($service, old('services', $prefill['services']), true) ? 'checked' : '' }} aria-label="Include {{ $label }}" tabindex="-1"></div>
                @endforeach
            </div>
            <input id="venue_name" name="venue_name" value="{{ old('venue_name') }}" required hidden>
            @foreach (['clothes','catering','host','photographer','sounds_lights'] as $service)
                <input id="{{ $service }}" name="{{ $service }}" value="{{ old($service) }}" hidden>
            @endforeach
            @error('venue_name')<p class="error">{{ $message }}</p>@enderror
            <div class="actions"><button class="button secondary" type="button" data-back>Back</button><button class="button primary" type="button" data-review>Review and Create Event</button></div>
        </section>
    </form>
    </div>
</div>
<div class="review-modal" id="reviewModal" aria-hidden="true"><div class="review-panel" role="dialog" aria-modal="true" aria-labelledby="reviewTitle"><h2 id="reviewTitle">Review Selected Services</h2><p class="step-note">Check your selected services and estimated total before creating the event.</p><div class="review-list" id="reviewList"></div><div class="review-total"><span>Total</span><span id="reviewTotal">₱0.00</span></div><div class="review-actions"><button type="button" class="secondary" data-close-review>Back</button><button type="button" class="primary" data-confirm-review>Confirm and Create Event</button></div></div></div>
<div class="service-modal" id="serviceModal" aria-hidden="true"><div class="service-panel"><header><div><h3 id="serviceModalTitle">Available Services</h3><a class="service-catalog-link" id="serviceCatalogLink" href="#">Open full catalog</a></div><button class="close-service" type="button" data-close-service aria-label="Close">&times;</button></header><div id="serviceResults"></div></div></div>
<style>
    .venue-modal{display:none;position:fixed;inset:0;z-index:30;align-items:center;justify-content:center;padding:20px;background:#12161999}.venue-modal.open{display:flex}.venue-panel{width:min(560px,100%);max-height:90vh;overflow:auto;background:#fff;border-radius:24px;padding:24px;box-shadow:0 20px 60px #0004}.venue-panel h2{margin:0 0 8px}.venue-panel p{color:#666;margin:0 0 18px}.venue-dates,.venue-addons{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.venue-date,.venue-addon{border:1px solid #d7eadb;border-radius:14px;padding:13px;background:#effbf1;color:#17652b;font-weight:800}.venue-date.booked{border-color:#f4caca;background:#fff0f0;color:#ad2929}.venue-date{display:flex;justify-content:space-between}.venue-addon{display:flex;gap:10px;align-items:center;background:#fff;border-color:#eee2b7;color:#222}.venue-addon input{accent-color:#d6a91d}.venue-modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px}.venue-modal-actions button{height:42px;border:0;border-radius:10px;padding:10px 16px;font-weight:800;cursor:pointer}.venue-modal-actions .secondary{background:#f1f1f1}.venue-modal-actions .primary{background:#f3c547}@media(max-width:520px){.venue-dates,.venue-addons{grid-template-columns:1fr}}
</style>
<div class="venue-modal" id="venueAvailabilityModal" aria-hidden="true"><div class="venue-panel"><h2>Venue Availability</h2><p id="venueAvailabilityText">Check available dates for this venue.</p><div class="venue-dates" id="venueDates"></div><div class="venue-modal-actions"><button class="secondary" type="button" data-close-venue>Cancel</button><button class="primary" type="button" data-venue-addons>Continue to Add-ons</button></div></div></div>
<div class="venue-modal" id="venueAddonsModal" aria-hidden="true"><div class="venue-panel"><h2>Venue Add-ons</h2><p>Select additional services this venue can provide.</p><div class="venue-addons" id="venueAddons"></div><div class="venue-modal-actions"><button class="secondary" type="button" data-back-venue>Back</button><button class="primary" type="button" data-confirm-venue>Confirm and Select Venue</button></div></div></div>
<script>
    const eventTypeInputs = document.querySelectorAll('input[name="event_type"]');
    const otherInput = document.getElementById('other_event_type');
    const themesByEvent = {
        birthday:['Cartoon Theme','7th Birthday','50th Birthday','Princess Theme','Color Theme','Superhero Theme','Sports Theme','Garden Party','Jungle Safari','Space Adventure','Custom'],
        debut:['Debut Classic','Rustic Debut','Princess Debut','Color Theme','Royal Elegance','Garden Debut','Neon / Modern','Vintage Debut','Custom'],
        wedding:['Garden','Rustic','Classic / Traditional','Ballroom','Beach / Destination','Royal Elegance','Fairytale','Minimalist','Vintage','Custom'],
        anniversary:['Romantic Dinner','Classic Elegant','Vintage','Garden Party','Gold Celebration','Custom'],
        christening:['Sky Blue / Pastel','Garden','Angel Theme','Classic White','Tea Party','Custom'],
        'gender reveal':['Blue vs Pink','Black & Gold','Confetti Party','Neutral Elegance','Custom'],
        reunion:['Family Picnic','Grand Gathering','Backyard Party','Classic Filipino','Nostalgia','Custom'],
        default:['Classic','Garden','Elegant','Modern','Custom']
    };
    const packagesByEvent = {
        birthday:[['Basic Birthday',25000,['venue','catering','host']],['Standard Birthday',50000,['venue','catering','host','sounds_lights']],['Premium Birthday',85000,['venue','catering','host','sounds_lights','photographer','clothes']]],
        debut:[['Basic Debut',40000,['venue','catering','host']],['Standard Debut',80000,['venue','catering','host','sounds_lights','photographer']],['Premium Debut',150000,['venue','catering','host','sounds_lights','photographer','clothes']]],
        wedding:[['Basic Wedding',60000,['venue','catering','host']],['Standard Wedding',120000,['venue','catering','host','sounds_lights','photographer']],['Premium Wedding',250000,['venue','catering','host','sounds_lights','photographer','clothes']]],
        default:[['Basic Package',25000,['venue','catering','host']],['Standard Package',50000,['venue','catering','host','sounds_lights','photographer']],['Premium Package',90000,['venue','catering','host','sounds_lights','photographer','clothes']]]
    };
    function renderEventOptions() {
        const selected = document.querySelector('input[name="event_type"]:checked')?.value || 'default';
        const eventKey = selected === 'Others' ? (otherInput.value.trim() || 'default').toLowerCase() : selected.toLowerCase();
        const key = eventKey;
        const themeChips = document.getElementById('themeChips');
        themeChips.innerHTML = (themesByEvent[key] || themesByEvent.default).map(theme => `<button type="button" class="theme-chip" data-theme="${theme}">${theme}</button>`).join('');
        themeChips.querySelectorAll('[data-theme]').forEach(button => button.addEventListener('click', () => {
            document.getElementById('theme').value = button.dataset.theme;
            themeChips.querySelectorAll('.theme-chip').forEach(chip => chip.classList.remove('selected'));
            button.classList.add('selected');
        }));
        const packageCards = document.getElementById('packageCards');
        packageCards.innerHTML = (packagesByEvent[key] || packagesByEvent.default).map(([name, price, services]) => `<button type="button" class="package-card" data-price="${price}" data-services="${services.join(',')}"><strong>${name}</strong><b>₱${price.toLocaleString()}</b><small>${services.join(' + ').replace('sounds_lights','Sounds & Lights')}</small></button>`).join('');
        packageCards.querySelectorAll('.package-card').forEach(card => card.addEventListener('click', () => {
            document.getElementById('event_budget').value = card.dataset.price;
            const selectedServices = card.dataset.services.split(',');
            document.querySelectorAll('input[name="services[]"]').forEach(input => { input.checked = selectedServices.includes(input.value); });
            packageCards.querySelectorAll('.package-card').forEach(item => item.classList.remove('selected'));
            card.classList.add('selected');
        }));
    }
    function toggleOther() {
        const selected = document.querySelector('input[name="event_type"]:checked');
        otherInput.required = selected?.value === 'Others';
        otherInput.disabled = selected?.value !== 'Others';
    }
    eventTypeInputs.forEach(input => input.addEventListener('change', toggleOther));
    toggleOther();
    eventTypeInputs.forEach(input => input.addEventListener('change', renderEventOptions));
    otherInput.addEventListener('input', renderEventOptions);
    renderEventOptions();

    const dateInput = document.getElementById('event_date');
    function updateMinimumDate() {
        const selected = document.querySelector('input[name="event_type"]:checked')?.value;
        const minimum = new Date();
        if (selected === 'Wedding') minimum.setMonth(minimum.getMonth() + 3);
        else minimum.setDate(minimum.getDate() + 7);
        dateInput.min = minimum.toISOString().slice(0, 10);
    }
    eventTypeInputs.forEach(input => input.addEventListener('change', updateMinimumDate));
    updateMinimumDate();

    const availableServices = @json($availableServices);
    const serviceLabels = {venue:'Venue', clothes:'Clothes', catering:'Food & Catering', host:'Host', sounds_lights:'Sounds & Lights', photographer:'Photographer'};
    const selectedServicePrices = {};
    function serviceKey(category) {
        const value = String(category || '').toLowerCase().replace(/[^a-z]/g, '');
        if (value.includes('sound') || value.includes('light')) return 'sounds_lights';
        if (value.includes('cater')) return 'catering';
        if (value.includes('photo')) return 'photographer';
        if (value.includes('cloth') || value.includes('attir')) return 'clothes';
        if (value.includes('host') || value.includes('mc')) return 'host';
        if (value.includes('venue')) return 'venue';
        return value;
    }
    const serviceModal = document.getElementById('serviceModal');
    const serviceResults = document.getElementById('serviceResults');
    function openServicePicker(key) {
        document.getElementById('serviceModalTitle').textContent = `Available ${serviceLabels[key] || 'Services'}`;
        document.getElementById('serviceCatalogLink').style.display = 'none';
        const catalogUrl = `{{ url('/services') }}/${key}?modal=1&return=${encodeURIComponent(window.location.href)}`;
        serviceResults.innerHTML = `<iframe class="catalog-frame" title="${serviceLabels[key] || 'Service'} catalog" src="${catalogUrl}"></iframe>`;
        serviceModal.classList.add('open');
        serviceModal.setAttribute('aria-hidden', 'false');
    }
    function closeServicePicker() {
        serviceModal.classList.remove('open');
        serviceModal.setAttribute('aria-hidden', 'true');
    }
    document.querySelectorAll('[data-service-view]').forEach(button => button.addEventListener('click', () => openServicePicker(button.dataset.serviceView)));
    document.querySelector('[data-close-service]').addEventListener('click', closeServicePicker);
    serviceModal.addEventListener('click', event => { if (event.target === serviceModal) closeServicePicker(); });
    document.querySelectorAll('[data-service-row] input[type="checkbox"]').forEach(input => input.addEventListener('change', () => {
        if (input.checked) return;
        const key = input.closest('[data-service-row]').dataset.serviceRow;
        const field = document.getElementById(key === 'venue' ? 'venue_name' : key);
        if (field) field.value = '';
        const selected = document.getElementById(`selected-${key}`);
        if (selected) selected.textContent = 'No service selected';
    }));
    serviceResults.addEventListener('click', event => {
        const option = event.target.closest('[data-service-name]');
        if (!option) return;
        const key = option.dataset.serviceKey;
        const value = option.dataset.serviceName;
        document.getElementById(key === 'venue' ? 'venue_name' : key).value = value;
        document.getElementById(`selected-${key}`).textContent = value;
        document.querySelector(`[data-service-row="${key}"] input[type="checkbox"]`).checked = true;
        closeServicePicker();
    });
    const venueAvailabilityModal = document.getElementById('venueAvailabilityModal');
    const venueAddonsModal = document.getElementById('venueAddonsModal');
    const venueDates = document.getElementById('venueDates');
    const venueAddons = document.getElementById('venueAddons');
    let pendingVenue = null;
    function closeVenueModals() {
        venueAvailabilityModal.classList.remove('open');
        venueAddonsModal.classList.remove('open');
    }
    async function openVenueAvailability(name, price) {
        pendingVenue = {name, price};
        document.getElementById('venueAvailabilityText').textContent = `Availability for ${name}`;
        venueDates.innerHTML = '<p>Checking availability...</p>';
        venueAvailabilityModal.classList.add('open');
        const response = await fetch(`{{ route('events.venue-availability') }}?venue=${encodeURIComponent(name)}`);
        const data = await response.json();
        venueDates.innerHTML = data.dates.map(item => `<div class="venue-date ${item.available ? '' : 'booked'}"><strong>${item.label}</strong><span>${item.available ? 'Open' : 'Booked'}</span></div>`).join('');
        venueAddons.innerHTML = (data.addons || []).map(addon => `<label class="venue-addon"><input type="checkbox" value="${addon}"><span>${serviceLabels[serviceKey(addon)] || addon}</span></label>`).join('') || '<p>This venue has no listed add-ons.</p>';
    }
    document.querySelector('[data-close-venue]').addEventListener('click', closeVenueModals);
    document.querySelector('[data-venue-addons]').addEventListener('click', () => { venueAvailabilityModal.classList.remove('open'); venueAddonsModal.classList.add('open'); });
    document.querySelector('[data-back-venue]').addEventListener('click', () => { venueAddonsModal.classList.remove('open'); venueAvailabilityModal.classList.add('open'); });
    document.querySelector('[data-confirm-venue]').addEventListener('click', () => {
        if (!pendingVenue) return;
        document.getElementById('venue_name').value = pendingVenue.name;
        document.getElementById('selected-venue').textContent = pendingVenue.name;
        document.querySelector('[data-service-row="venue"] input[type="checkbox"]').checked = true;
        if (Number.isFinite(pendingVenue.price)) selectedServicePrices.venue = pendingVenue.price;
        document.querySelectorAll('#venueAddons input:checked').forEach(input => {
            const key = serviceKey(input.value);
            const field = document.getElementById(key);
            if (field) { field.value = pendingVenue.name; document.getElementById(`selected-${key}`).textContent = `${pendingVenue.name} (${input.value})`; document.querySelector(`[data-service-row="${key}"] input`).checked = true; }
        });
        closeVenueModals();
    });
    window.addEventListener('message', event => {
        if (!event.data || event.data.type !== 'serviceSelected') return;
        const key = event.data.service;
        const value = event.data[key];
        if (!key || !value || !document.querySelector(`[data-service-row="${key}"]`)) return;
        const price = Number(event.data.price);
        if (Number.isFinite(price) && price >= 0) selectedServicePrices[key] = price;
        if (key === 'venue') {
            closeServicePicker();
            openVenueAvailability(value, price);
            return;
        }
        document.getElementById(key === 'venue' ? 'venue_name' : key).value = value;
        document.getElementById(`selected-${key}`).textContent = value;
        document.querySelector(`[data-service-row="${key}"] input[type="checkbox"]`).checked = true;
        closeServicePicker();
    });

    function openReview() {
        const form = document.querySelector('form[action="{{ route('events.store') }}"]');
        if (!form || !form.checkValidity()) {
            alert('Please complete the event details before creating your event.');
            showStep(2);
            return;
        }
        const startTime = document.getElementById('event_time').value;
        const endTime = document.getElementById('event_end_time').value;
        if (startTime && endTime && endTime <= startTime) {
            alert('End time must be after the start time.');
            showStep(2);
            document.getElementById('event_end_time').focus();
            return;
        }
        const list = document.getElementById('reviewList');
        let total = 0;
        const selected = [...document.querySelectorAll('[data-service-row] input[type="checkbox"]:checked')];
        list.innerHTML = selected.length ? selected.map(input => {
            const key = input.closest('[data-service-row]').dataset.serviceRow;
            const name = document.getElementById(`selected-${key}`)?.textContent || serviceLabels[key];
            const price = selectedServicePrices[key];
            if (Number.isFinite(price)) total += price;
            return `<div class="review-row"><div><strong>${serviceLabels[key] || key}</strong><small>${name}</small></div><span class="review-price">${Number.isFinite(price) ? '₱' + price.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2}) : 'Price unavailable'}</span></div>`;
        }).join('') : '<div class="review-row"><span>No services selected.</span></div>';
        document.getElementById('reviewTotal').textContent = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
        document.getElementById('reviewModal').classList.add('open');
        document.getElementById('reviewModal').setAttribute('aria-hidden', 'false');
    }
    function closeReview() {
        document.getElementById('reviewModal').classList.remove('open');
        document.getElementById('reviewModal').setAttribute('aria-hidden', 'true');
    }
    document.querySelector('[data-review]').addEventListener('click', openReview);
    document.querySelector('[data-close-review]').addEventListener('click', closeReview);
    document.querySelector('[data-confirm-review]').addEventListener('click', () => {
        const form = document.querySelector('form[action="{{ route('events.store') }}"]');
        if (!form || !form.checkValidity()) {
            closeReview();
            alert('Please complete the event details before creating your event.');
            showStep(2);
            return;
        }
        const startTime = document.getElementById('event_time').value;
        const endTime = document.getElementById('event_end_time').value;
        if (startTime && endTime && endTime <= startTime) {
            closeReview();
            alert('End time must be after the start time.');
            showStep(2);
            document.getElementById('event_end_time').focus();
            return;
        }
        form.submit();
    });

    const steps = [...document.querySelectorAll('.section[data-step]')];
    const stepPills = [...document.querySelectorAll('[data-step-label]')];
    let currentStep = 1;
    function showStep(step) {
        currentStep = step;
        steps.forEach(section => section.classList.toggle('active', Number(section.dataset.step) === step));
        stepPills.forEach(pill => pill.classList.toggle('active', Number(pill.dataset.stepLabel) === step));
    }
    document.querySelectorAll('[data-next]').forEach(button => button.addEventListener('click', () => {
        if (currentStep === 1 && !document.getElementById('theme').value.trim()) {
            alert('Please select a theme for your event first.');
            return;
        }
        const visibleFields = steps[currentStep - 1].querySelectorAll('input[required]');
        if (![...visibleFields].every(field => field.reportValidity())) return;
        showStep(Math.min(currentStep + 1, steps.length));
    }));
    document.querySelectorAll('[data-back]').forEach(button => button.addEventListener('click', () => showStep(Math.max(currentStep - 1, 1))));
</script>
</body>
</html>
