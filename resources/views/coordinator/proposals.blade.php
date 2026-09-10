@extends('coordinator.layout')

@section('title', 'Event Proposals')

@section('styles')
<style>
    .coord-container { max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; padding-top: 4px; }
    .coord-card-floating { background: #ffffff; border: 1px solid #ebebeb; border-radius: 16px; padding: 20px 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.02); }
    
    .coord-header-bar { border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; margin-bottom: 16px; }
    .coord-header-bar h2 { font-size: 22px; font-weight: 700; color: var(--text); margin: 0 0 2px; }
    .coord-header-bar p { color: var(--muted); font-size: 13px; margin: 0; }

    .proposals-layout { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 20px; }

    /* Sidebar Event List */
    .evt-list { display: flex; flex-direction: column; gap: 8px; border-right: 1px solid #f0f0f0; padding-right: 16px; }
    .evt-item { display: block; padding: 12px; border-radius: 10px; border: 1px solid #ebebeb; background: #fafafa; text-decoration: none; color: var(--text); transition: background 0.2s ease; }
    .evt-item:hover, .evt-item.active { background: #ffffff; border-color: var(--border2); box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
    .evt-item h4 { font-size: 14px; font-weight: 600; margin: 0 0 2px; }
    .evt-item p { font-size: 12px; color: var(--muted); margin: 0; }

    /* Form Fields */
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px; }
    .form-grid input, .proposal-form textarea { width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #e0e0e0; background: #fafafa; color: var(--text); font-size: 13px; outline: none; transition: border-color 0.2s ease; }
    .form-grid input:focus, .proposal-form textarea:focus { border-color: var(--gold); background: #ffffff; }
    .proposal-form textarea { width: 100%; min-height: 70px; resize: vertical; margin-bottom: 12px; }

    .btn-submit { border: none; padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; }
</style>
@endsection

@section('content')
<div class="coord-container">
    <div class="coord-card-floating">
        <header class="coord-header-bar">
            <h2>Event Proposals</h2>
            <p>Build and send structured proposals to your assigned clients.</p>
        </header>

        <div class="proposals-layout">
            {{-- Left Assigned Events List --}}
            <div class="evt-list">
                <h3 style="font-size: 14px; font-weight: 700; color: var(--muted); text-transform: uppercase; margin: 0 0 8px;">Assigned Events</h3>
                @forelse($events as $event)
                    <a class="evt-item {{ optional($selectedEvent)->event_id === $event->event_id ? 'active' : '' }}" href="{{ route('coordinator.proposals',['event_id'=>$event->event_id]) }}">
                        <h4>{{ $event->title ?: 'Untitled Event' }}</h4>
                        <p>{{ $event->event_date ?: 'TBD' }}</p>
                    </a>
                @empty
                    <p style="font-size: 13px; color: var(--muted);">No assigned events yet.</p>
                @endforelse
            </div>

            {{-- Right Proposal Form --}}
            <div class="proposal-form">
                @if($selectedEvent)
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0 0 16px;"><i class="fas fa-file-signature text-gold"></i> Proposal for {{ $selectedEvent->title ?: 'Event' }}</h3>
                    <form method="POST" action="{{ route('coordinator.proposals.store') }}">
                        @csrf
                        <input type="hidden" name="event_id" value="{{ $selectedEvent->event_id }}">
                        
                        <div class="form-grid">
                            <input name="venue" placeholder="Suggested Venue">
                            <input name="catering" placeholder="Catering Service">
                            <input name="clothing" placeholder="Clothing / Attire">
                            <input name="decorations" placeholder="Decorations">
                            <input name="host" placeholder="Host / Emcee">
                            <input name="photography" placeholder="Photography">
                            <input name="total_quotation" type="number" placeholder="Total Quotation (₱)">
                        </div>
                        
                        <textarea name="timeline" placeholder="Event Timeline"></textarea>
                        <textarea name="cost_breakdown" placeholder="Estimated Cost Breakdown"></textarea>
                        <textarea name="recommendations" placeholder="Additional Recommendations"></textarea>
                        
                        <button class="btn-submit" type="submit"><i class="fas fa-save"></i> Save Proposal</button>
                    </form>
                @else
                    <div style="text-align:center; padding:60px 20px; color:var(--muted); font-size: 13px;">
                        Select an event from the left list to create or edit its proposal.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection