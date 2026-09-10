@extends('coordinator.layout')

@section('title', 'Assigned Events')

@section('styles')
<style>
    .coord-container { max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; padding-top: 4px; }
    .coord-card-floating { background: #ffffff; border: 1px solid #ebebeb; border-radius: 16px; padding: 20px 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.02); }
    
    .coord-header-bar { border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; margin-bottom: 16px; }
    .coord-header-bar h2 { font-size: 22px; font-weight: 700; color: var(--text); margin: 0 0 2px; }
    .coord-header-bar p { color: var(--muted); font-size: 13px; margin: 0; }

    /* Alert */
    .alert-success-box { background: rgba(46,159,77,0.08); border: 1px solid rgba(46,159,77,0.2); color: #2a8a43; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }

    /* Clean Data Table */
    .table-wrapper { overflow-x: auto; }
    .booking-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
    .booking-table th { font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.4px; padding: 10px 14px; text-align: left; }
    .booking-table td { background: #fafafa; border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0; padding: 12px 14px; font-size: 13px; color: var(--text); }
    .booking-table tr td:first-child { border-left: 1px solid #f0f0f0; border-radius: 10px 0 0 10px; font-weight: 600; }
    .booking-table tr td:last-child { border-right: 1px solid #f0f0f0; border-radius: 0 10px 10px 0; }

    /* Status Badges & Buttons */
    .status-badge { display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .status-badge.status-approved { background: rgba(46,159,77,0.1); color: #2e9f4d; }
    .status-badge.status-rejected { background: rgba(217,83,79,0.1); color: #d9534f; }
    .status-badge.status-pending { background: rgba(243,197,71,0.15); color: #b07c00; }

    .btn-action-sm { border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; transition: transform 0.15s ease; }
    .btn-action-sm.accept { background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; }
    .btn-action-sm.decline { background: transparent; color: #d9534f; border: 1px solid rgba(217,83,79,0.3); }
    .btn-action-sm:hover { transform: translateY(-1px); }
</style>
@endsection

@section('content')
<div class="coord-container">
    <div class="coord-card-floating">
        <header class="coord-header-bar">
            <h2>Assigned Events</h2>
            <p>Review and confirm coordination assignments for upcoming events.</p>
        </header>

        @if(session('success'))
            <div class="alert-success-box"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        <div class="table-wrapper">
            <table class="booking-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        @php($eventStatus = strtolower(str_replace('_', ' ', trim($event->coordinator_status ?? 'pending'))))
                        <tr>
                            <td>{{ $event->client_name ?? 'Unknown Client' }}</td>
                            <td>{{ $event->title ?: ($event->event_type ?: 'Event') }}</td>
                            <td>{{ $event->event_date ? \Illuminate\Support\Carbon::parse($event->event_date)->format('M d, Y') : 'N/A' }}</td>
                            <td>₱{{ number_format($event->budget ?? 0) }}</td>
                            <td>
                                <span class="status-badge {{ $eventStatus === 'accepted' || $eventStatus === 'paid' ? 'status-approved' : ($eventStatus === 'declined' ? 'status-rejected' : 'status-pending') }}">
                                    {{ ucfirst($eventStatus) }}
                                </span>
                            </td>
                            <td>
                                @if($eventStatus === 'pending')
                                    <form method="POST" action="{{ route('coordinator.events.update',$event->event_id) }}" style="display:inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="action" value="accepted">
                                        <button class="btn-action-sm accept">Accept</button>
                                    </form> 
                                    <form method="POST" action="{{ route('coordinator.events.update',$event->event_id) }}" style="display:inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="action" value="declined">
                                        <button class="btn-action-sm decline">Decline</button>
                                    </form>
                                @elseif($eventStatus === 'pending confirmation')
                                    <form method="POST" action="{{ route('coordinator.events.update',$event->event_id) }}" style="display:inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="action" value="paid">
                                        <button class="btn-action-sm accept">Receive Payment</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; color:var(--muted); padding:24px;">No events assigned to you yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection