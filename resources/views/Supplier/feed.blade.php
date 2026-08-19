@extends('supplier.layout')

@section('title', 'Supplier Feed')

@section('content')
<section>
    <h2>Supplier Feed</h2>
    <div class="services-messages">
        <div class="services" style="width:100%;">
            <h3>Latest Updates</h3>

            @forelse($feedItems as $item)
                <div class="review-card" style="margin-bottom:16px;">
                    <h4>{{ $item->category ?? 'Update' }}</h4>
                    <p>{{ $item->name }} — {{ Str::limit($item->description ?? 'No details available.', 140) }}</p>
                    <small style="color:var(--muted);">Posted {{ \Illuminate\Support\Facades\Date::parse($item->created_at)->format('M j, Y') }}</small>
                </div>
            @empty
                <div class="review-card" style="margin-bottom:16px;">
                    <h4>No service updates yet</h4>
                    <p>You can add services in your supplier dashboard and they will appear here.</p>
                </div>
            @endforelse
        </div>

        <div class="services" style="width:100%;">
            <h3>Quick Stats</h3>
            <ul style="list-style:none; padding:0; margin:0; display:grid; gap:12px; color:var(--text);">
                <li><strong>Active services:</strong> {{ $serviceCount }}</li>
                <li><strong>Pending requests:</strong> {{ $pendingCount }}</li>
                <li><strong>Accepted bookings:</strong> {{ $approvedCount }}</li>
            </ul>
        </div>
    </div>
</section>
@endsection
