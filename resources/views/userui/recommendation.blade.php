<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EventIntel - Recommendations</title>
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/userui/recommendation.css') }}">
</head>
<body>
    <div class="recommendation-page">
        @include('userui.partials.navbar', ['active' => 'recommendation'])

        <main class="recommendation-layout">
            <div class="recommendation-visual" aria-hidden="true"></div>

            <section class="recommendation-panel">
                <h1>Smart Recommendation Engine</h1>
                <p class="recommendation-subtitle">Get event planning suggestions with a detailed timeline</p>

                <div class="recommendation-input">
                    <label for="eventSelect">SELECT YOUR EVENT</label>
                    <div class="recommendation-event-row">
                        <select id="eventSelect" onchange="loadEventDetails()">
                            <option value="">-- Select an event --</option>
                            @if ($userEvents->isNotEmpty())
                                <optgroup label="Your Events">
                                    @foreach ($userEvents as $event)
                                        <option value="{{ $event->event_id }}"
                                            data-type="{{ $event->event_type }}"
                                            data-budget="{{ $event->budget }}"
                                            data-pax="{{ $event->guest_count }}">
                                            {{ $event->title ?: 'Untitled event' }} - {{ $event->event_type ?: 'Event' }}
                                            ({{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('M j, Y') : 'Date TBD' }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if ($fallbackEvents->isNotEmpty())
                                <optgroup label="Recent Events">
                                    @foreach ($fallbackEvents as $event)
                                        <option value="{{ $event->event_id }}"
                                            data-type="{{ $event->event_type }}"
                                            data-budget="{{ $event->budget }}"
                                            data-pax="{{ $event->guest_count }}">
                                            {{ $event->title ?: 'Untitled event' }} - {{ $event->event_type ?: 'Event' }}
                                            ({{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('M j, Y') : 'Date TBD' }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        <a class="recommendation-create" href="{{ route('home') }}">Create Event</a>
                    </div>
                </div>

                <div class="recommendation-input-group">
                    <div class="recommendation-input">
                        <label for="budget">BUDGET (PHP)</label>
                        <input type="number" id="budget" placeholder="35000" min="0">
                    </div>
                    <div class="recommendation-input">
                        <label for="pax">NUMBER OF GUESTS</label>
                        <input type="number" id="pax" placeholder="50" min="1">
                    </div>
                </div>

                <div class="recommendation-input">
                    <label for="event">EVENT TYPE</label>
                    <input type="text" id="event" placeholder="e.g., Birthday, Wedding, Corporate">
                </div>

                <div class="recommendation-services" id="services">
                    @foreach ([
                        'venue' => 'Venue',
                        'catering' => 'Catering/Food',
                        'host' => 'Host/MC',
                        'sounds_lights' => 'Sounds & Lights',
                        'photographer' => 'Photographer',
                        'clothes' => 'Clothing/Attire',
                        'decorations' => 'Decorations',
                    ] as $key => $label)
                        <button class="recommendation-service" type="button" data-service="{{ $key }}">
                            <span>{{ $label }}</span>
                            <span class="recommendation-checkbox"><i class="fas fa-check" aria-hidden="true"></i></span>
                        </button>
                    @endforeach
                </div>

                <button class="recommendation-generate" type="button" onclick="generateRecommendation()">Generate Timeline &amp; Recommendations</button>

                <div class="recommendation-result" id="result" aria-live="polite">
                    <h3><i class="fas fa-calendar-days" aria-hidden="true"></i> Your Event Timeline &amp; Recommendations</h3>
                    <div id="resultText"></div>
                    <div class="recommendation-result-actions">
                        <button class="recommendation-generate" type="button" onclick="applyRecommendationToCreateEvent()">Use This Recommendation</button>
                        <button class="recommendation-generate" type="button" onclick="generateRecommendation()">Regenerate</button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        const recommendationEndpoint = @json(route('recommendation.generate'));

        const serviceLabels = {
            venue: 'Venue',
            catering: 'Catering/Food',
            host: 'Host/MC',
            sounds_lights: 'Sounds & Lights',
            photographer: 'Photographer',
            clothes: 'Clothing/Attire',
            decorations: 'Decorations'
        };

        let currentRecommendation = null;

        function loadEventDetails() {
            const select = document.getElementById('eventSelect');
            const option = select.options[select.selectedIndex];
            document.getElementById('event').value = option?.dataset.type || '';
            document.getElementById('budget').value = option?.dataset.budget || '';
            document.getElementById('pax').value = option?.dataset.pax || '';
        }

        function selectedServices() {
            return [...document.querySelectorAll('.recommendation-service.active')]
                .map(service => serviceLabels[service.dataset.service]);
        }

        async function generateRecommendation() {
            const event = document.getElementById('event').value.trim() || 'Event';
            const budget = Number(document.getElementById('budget').value);
            const guests = Number(document.getElementById('pax').value);
            const services = selectedServices();
            const result = document.getElementById('result');
            const resultText = document.getElementById('resultText');

            if (!budget || !guests) {
                alert('Please fill in your budget and guest count before generating recommendations.');
                return;
            }

            currentRecommendation = { eventType: event, guestCount: guests, services };
            resultText.innerHTML = '<p class="recommendation-status">Generating timeline and supplier recommendations...</p>';
            result.style.display = 'block';
            const response = await fetch(recommendationEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ event, budget, pax: guests, services })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Unable to generate recommendations.');
            resultText.innerHTML = data.html;
            result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function applyRecommendationToCreateEvent() {
            if (!currentRecommendation) {
                alert('Please generate a recommendation first.');
                return;
            }

            const payload = JSON.stringify(currentRecommendation);
            sessionStorage.setItem('event_recommendation_prefill', payload);
            document.cookie = `event_recommendation_prefill=${encodeURIComponent(payload)}; path=/; max-age=3600`;
            window.location.href = @json(route('coordinator.events')) + '?from=recommendation';
        }

        document.querySelectorAll('.recommendation-service').forEach(service => {
            service.addEventListener('click', () => service.classList.toggle('active'));
        });
    </script>
</body>
</html>
