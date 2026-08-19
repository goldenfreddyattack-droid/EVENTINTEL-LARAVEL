@extends('coordinator.layout')
@section('title','Coordinator Dashboard')
@section('content')
<section class="dashboard-page"><h2>DASHBOARD</h2><section class="dashboard-stats"><div class="stat-card active"><h3>PENDING EVENTS</h3><p>{{ $pending }}</p></div><div class="stat-card"><h3>ONGOING EVENTS</h3><p>{{ $ongoing }}</p></div><div class="stat-card"><h3>TOTAL SUPPLIERS</h3><p>{{ $totalSuppliers }}</p></div></section><section class="assigned-events-box"><h2>ASSIGNED EVENTS</h2>@forelse($events as $event)<div class="event-row"><span>{{ $event->title ?: 'Unnamed Event' }}{{ $event->event_date ? ' - '.$event->event_date : '' }}</span><div class="event-actions"><a class="view-btn" href="{{ route('coordinator.events') }}">View</a></div></div>@empty<div class="event-row"><span>No assigned events.</span></div>@endforelse</section><section class="ai-box"><h3>AI Program Flow Generator</h3><button type="button">Choose Confirmed Event</button></section></section>
@endsection
