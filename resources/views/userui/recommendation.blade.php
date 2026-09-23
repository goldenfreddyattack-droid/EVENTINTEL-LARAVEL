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
                <h1>AI Recommendation</h1>
                <p class="recommendation-subtitle">Get event planning suggestions with a detailed timeline</p>

                <div class="recommendation-input">
                    <label for="eventSelect">SELECT YOUR EVENT</label>
                    <div class="recommendation-event-row">
                        <select id="eventSelect">
                            <option value="">-- Select an event --</option>
                            @if ($userEvents->isNotEmpty())
                                <optgroup label="Your Events">
                                    @foreach ($userEvents as $event)
                                        <option value="{{ $event->event_id }}" @selected((int) request('event_id') === (int) $event->event_id)>
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

                @if ($bookmarkedServices->isNotEmpty())
                    <div class="recommendation-bookmarks">
                        <h3>Bookmarked place picks</h3>
                        <div class="recommendation-bookmark-list">
                            @foreach ($bookmarkedServices as $saved)
                                <div class="recommendation-bookmark-item">
                                    <span>{{ $saved->name }}</span>
                                    <small>{{ $saved->category ?: 'Venue' }} · ★ {{ number_format((float) ($saved->rating ?? 0), 1) }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <button class="recommendation-generate" type="button" onclick="generateRecommendation()">Generate Timeline &amp; Recommendations</button>

                <div class="recommendation-result" id="result" aria-live="polite">
                    <h3><i class="fas fa-calendar-days" aria-hidden="true"></i> Your Event Timeline &amp; Recommendations</h3>
                    <div id="resultText"></div>
                    <div class="recommendation-result-actions">
                        <button class="recommendation-generate" type="button" onclick="useRecommendationFlow()">Use This Flow &amp; Notify Suppliers</button>
                        <button class="recommendation-generate" type="button" onclick="generateRecommendation(true)">Regenerate</button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        const recommendationEndpoint = @json(route('recommendation.generate'));
        const useRecommendationEndpoint = @json(route('recommendation.use'));
        const initialEventId = @json(request('event_id'));
        const savedFlow = @json($savedFlow);
        let currentAiFlow = @json($savedAiFlow);

        async function generateRecommendation(regenerate = false) {
            const eventId = document.getElementById('eventSelect').value;
            const result = document.getElementById('result');
            const resultText = document.getElementById('resultText');

            if (!eventId) {
                alert('Please select a created event before generating the flow.');
                return;
            }

            resultText.innerHTML = '<p class="recommendation-status">Generating timeline and supplier recommendations...</p>';
            result.style.display = 'block';
            const response = await fetch(recommendationEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ event_id: Number(eventId), regenerate })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Unable to generate recommendations.');
            resultText.innerHTML = data.html;
            currentAiFlow = data.flow || '';
            result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        async function useRecommendationFlow() {
            const eventId = document.getElementById('eventSelect').value;
            const flow = currentAiFlow;

            if (!eventId || !flow) {
                alert('Generate the event flow first.');
                return;
            }

            try {
                const response = await fetch(useRecommendationEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ event_id: Number(eventId), flow }),
                });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Unable to notify suppliers.');
                }

                alert(`The flow was sent to ${data.supplier_count} supplier(s) for this event.`);
            } catch (error) {
                console.error(error);
                alert(error.message || 'Unable to notify suppliers right now.');
            }
        }

        if (initialEventId && savedFlow) {
            document.getElementById('resultText').innerHTML = savedFlow;
            document.getElementById('result').style.display = 'block';
        }

    </script>
</body>
</html>
