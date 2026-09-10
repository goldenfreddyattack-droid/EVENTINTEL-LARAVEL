@extends('coordinator.layout')

@section('title', 'Coordinator Dashboard')

@section('styles')
<style>
    /* ===== Container & Floating Panels ===== */
    .coord-container { max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; padding-top: 4px; }
    .coord-card-floating { background: #ffffff; border: 1px solid #ebebeb; border-radius: 16px; padding: 20px 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.02); transition: box-shadow 0.2s ease; }
    .coord-card-floating:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.04); }

    /* Header */
    .coord-header-bar { border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; margin-bottom: 16px; }
    .coord-header-bar h2 { font-size: 22px; font-weight: 700; color: var(--text); margin: 0 0 2px; }
    .coord-header-bar p { color: var(--muted); font-size: 13px; margin: 0; }

    /* Stats Grid */
    .dash-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
    .stat-box { background: #fafafa; border: 1px solid #eaeaea; border-radius: 14px; padding: 16px 18px; display: flex; flex-direction: column; gap: 4px; transition: border-color 0.2s ease, background 0.2s ease; }
    .stat-box:hover { border-color: var(--border2); background: #ffffff; }
    .stat-label { font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.4px; }
    .stat-num { font-size: 26px; font-weight: 700; line-height: 1.1; color: var(--gold3); }

    /* Assigned Events List */
    .section-title-group { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid #f4f4f4; padding-bottom: 10px; }
    .section-title-group h3 { font-size: 16px; font-weight: 700; color: var(--text); margin: 0; }
    .event-rows-list { display: flex; flex-direction: column; gap: 10px; }
    .event-row-item { display: flex; align-items: center; justify-content: space-between; background: #fafafa; border: 1px solid #ebebeb; border-radius: 12px; padding: 12px 16px; transition: background 0.2s ease; }
    .event-row-item:hover { background: #ffffff; border-color: var(--border2); }
    .event-info { font-size: 14px; font-weight: 600; color: var(--text); }
    .view-btn-pill { padding: 6px 14px; border-radius: 6px; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; font-size: 12px; font-weight: 700; text-decoration: none; }

    /* AI Generator Action Box */
    .ai-action-box { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .ai-action-btn { padding: 9px 16px; border-radius: 8px; border: 1px solid var(--border2); background: #ffffff; color: var(--text); font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.2s ease; }
    .ai-action-btn:hover { background: rgba(243,197,71,0.12); color: var(--gold3); }
</style>
@endsection

@section('content')
<div class="coord-container">
    
    {{-- FLOATING PANEL 1: Page Header --}}
    <div class="coord-card-floating">
        <header class="coord-header-bar">
            <h2>Dashboard</h2>
            <p>Welcome back! Here is an overview of your active event coordination requests.</p>
        </header>

        {{-- Stats Grid --}}
        <div class="dash-stats-grid">
            <div class="stat-box">
                <span class="stat-label">Pending Events</span>
                <span class="stat-num">{{ $pending }}</span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Ongoing Events</span>
                <span class="stat-num">{{ $ongoing }}</span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Total Suppliers</span>
                <span class="stat-num">{{ $totalSuppliers }}</span>
            </div>
        </div>
    </div>

    {{-- FLOATING PANEL 2: Assigned Events --}}
    <div class="coord-card-floating">
        <div class="section-title-group">
            <h3>Assigned Events</h3>
        </div>
        <div class="event-rows-list">
            @forelse($events as $event)
                <div class="event-row-item">
                    <span class="event-info">{{ $event->title ?: 'Unnamed Event' }}{{ $event->event_date ? ' - '.$event->event_date : '' }}</span>
                    <a class="view-btn-pill" href="{{ route('coordinator.events') }}">View</a>
                </div>
            @empty
                <div class="event-row-item" style="justify-content: center; color: var(--muted); font-size: 13px;">
                    No assigned events found.
                </div>
            @endforelse
        </div>
    </div>

    {{-- FLOATING PANEL 3: AI Program Flow --}}
    <div class="coord-card-floating">
        <div class="ai-action-box">
            <div>
                <h3 style="font-size: 16px; font-weight: 700; margin: 0 0 2px;">AI Program Flow Generator</h3>
                <p style="font-size: 13px; color: var(--muted); margin: 0;">Automatically build customized timelines for confirmed client events.</p>
            </div>
            <button type="button" class="ai-action-btn"><i class="fas fa-magic text-gold"></i> Choose Confirmed Event</button>
        </div>
    </div>

</div>
@endsection