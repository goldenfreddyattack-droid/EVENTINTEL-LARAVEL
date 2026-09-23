@extends('coordinator.layout')

@section('title', 'AI Program Flow')

@section('styles')
<style>
    .flow-page { max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; }
    .flow-card { background: #fff; border: 1px solid #ebebeb; border-radius: 16px; padding: 24px; box-shadow: 0 4px 16px rgba(0,0,0,.03); }
    .flow-card h2 { margin: 0 0 6px; color: var(--text); font-size: 24px; }
    .flow-card p { color: var(--muted); margin: 0 0 20px; }
    .flow-select-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .flow-select { flex: 1; min-width: 240px; padding: 11px 12px; border: 1px solid var(--border2); border-radius: 8px; background: #fff; color: var(--text); }
    .flow-btn { border: 1px solid var(--border2); border-radius: 8px; padding: 11px 16px; background: linear-gradient(135deg, #fff1a8, #f3c547); color: #111; font-weight: 700; cursor: pointer; }
    .flow-btn:hover { filter: brightness(.98); }
    .flow-result { display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
    .flow-result-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 18px; }
    .flow-status { color: var(--muted); }
    .recommendation-ai-status { margin: 16px 0; padding: 10px 12px; border-radius: 8px; font-size: 13px; }
    .recommendation-ai-status-warning { background: #fff8dc; border: 1px solid #e3b52d; color: #765400; }
    .recommendation-ai-status-success { background: #edf9f0; border: 1px solid #9bd2a6; color: #246b32; }
    .recommendation-response { color: #333; line-height: 1.45; }
    .recommendation-response > p { margin: 0 0 14px; padding-bottom: 12px; border-bottom: 1px solid #eee; }
    .recommendation-section-title { margin: 22px 0 10px; color: #b78300; font-size: 16px; }
    .recommendation-timeline-item { display: flex; gap: 16px; margin: 10px 0; padding: 13px 14px; border-left: 3px solid #f3c547; border-radius: 8px; background: #fffaf0; }
    .recommendation-timeline-time { min-width: 82px; color: #a87500; font-weight: 800; }
    .recommendation-timeline-event { flex: 1; color: #333; }
    .recommendation-budget-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(145px, 1fr)); gap: 10px; }
    .recommendation-budget-card, .recommendation-service-note { padding: 13px 14px; border: 1px solid #ead9a0; border-radius: 10px; background: #fff; }
    .recommendation-budget-card { display: grid; gap: 8px; }
    .recommendation-budget-card span { color: #b78300; font-weight: 800; }
    .recommendation-budget-bar { height: 8px; overflow: hidden; border-radius: 999px; background: #f8edc8; }
    .recommendation-budget-bar i { display: block; height: 100%; border-radius: inherit; background: #e0ae24; }
    .recommendation-service-note { margin: 8px 0; }
    .recommendation-best-fit { color: #b78300; font-weight: 800; }
    .recommendation-ai-tip { padding: 13px; border-left: 3px solid #f3c547; border-radius: 8px; background: #fff8dc; }
    .recommendation-result-actions .flow-btn { flex: 1; min-width: 190px; }
    @media (max-width: 640px) { .recommendation-timeline-item { display: block; } .recommendation-timeline-time { display: block; margin-bottom: 4px; } }
</style>
@endsection

@section('content')
<div class="flow-page">
    <section class="flow-card">
        <h2>AI Program Flow</h2>
        <p>Generate a detailed OpenAI event flow for one of your assigned client events.</p>
        <div class="flow-select-row">
            <select id="eventSelect" class="flow-select">
                <option value="">Choose an assigned event</option>
                @foreach ($userEvents as $event)
                    <option value="{{ $event->event_id }}" @selected((int) request('event_id') === (int) $event->event_id)>
                        {{ $event->title ?: 'Untitled event' }} - {{ $event->event_type ?: 'Event' }}
                        ({{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('M j, Y') : 'Date TBD' }})
                    </option>
                @endforeach
            </select>
            <button class="flow-btn" type="button" onclick="generateCoordinatorFlow()">Generate AI Flow</button>
        </div>

        <div class="flow-result" id="result" aria-live="polite">
            <div id="resultText"></div>
            <div class="flow-result-actions">
                <button class="flow-btn" type="button" onclick="useCoordinatorFlow()">Use This Flow &amp; Notify Suppliers</button>
                <button class="flow-btn" type="button" onclick="generateCoordinatorFlow(true)">Regenerate</button>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    const recommendationEndpoint = @json(route('recommendation.generate'));
    const useRecommendationEndpoint = @json(route('recommendation.use'));
    const initialEventId = @json(request('event_id'));
    const savedFlow = @json($savedFlow);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    async function generateCoordinatorFlow(regenerate = false) {
        const eventId = document.getElementById('eventSelect').value;
        const result = document.getElementById('result');
        const resultText = document.getElementById('resultText');
        if (!eventId) {
            alert('Please select an assigned event first.');
            return;
        }

        result.style.display = 'block';
        resultText.innerHTML = '<p class="flow-status">Generating the AI event flow...</p>';
        try {
            const response = await fetch(recommendationEndpoint, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken},
                body: JSON.stringify({event_id: Number(eventId), regenerate})
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Unable to generate the AI flow.');
            resultText.innerHTML = data.html;
        } catch (error) {
            resultText.innerHTML = `<p class="flow-status">${error.message}</p>`;
        }
    }

    async function useCoordinatorFlow() {
        const eventId = document.getElementById('eventSelect').value;
        const flow = document.getElementById('resultText').innerText.trim();
        if (!eventId || !flow) {
            alert('Generate the AI flow first.');
            return;
        }

        try {
            const response = await fetch(useRecommendationEndpoint, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken},
                body: JSON.stringify({event_id: Number(eventId), flow})
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Unable to notify suppliers.');
            alert(`The flow was sent to ${data.supplier_count} supplier(s).`);
        } catch (error) {
            alert(error.message || 'Unable to notify suppliers.');
        }
    }

    if (initialEventId && savedFlow) {
        document.getElementById('resultText').innerHTML = savedFlow;
        document.getElementById('result').style.display = 'block';
    }
</script>
@endsection
