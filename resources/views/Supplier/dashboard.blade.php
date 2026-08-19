@extends('supplier.layout')

@section('title', 'Supplier Dashboard')

@section('content')
<section>
    <h2>Supplier Dashboard</h2>
    <p style="color: var(--muted); margin-top: -12px; margin-bottom: 24px;">Welcome back, {{ auth()->user()->full_name ?? auth()->user()->username }}.</p>

    @if($serviceCount == 0)
        <div class="alert-box" style="margin-bottom: 24px;">
            <div>
                <h3 style="margin:0 0 6px;">Your shop needs a few more details!</h3>
                <p style="margin:0; color: var(--muted);">Add your business details and your first services so clients can find and book you.</p>
            </div>
            <a href="{{ route('supplier.setup') }}" class="accept-btn" style="display:inline-block; text-decoration:none;">Complete Setup</a>
        </div>
    @endif

    <div class="services-grid" style="margin-bottom: 30px;">
        <div class="service-card">
            <h4>Total Requests</h4>
            <p class="rating" style="font-size: 32px; margin: 8px 0 0;">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="service-card">
            <h4>Pending</h4>
            <p class="rating" style="font-size: 32px; margin: 8px 0 0;">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="service-card">
            <h4>Accepted</h4>
            <p class="rating" style="font-size: 32px; margin: 8px 0 0; color: #2e9f4d;">{{ $stats['accepted'] ?? 0 }}</p>
        </div>
        <div class="service-card">
            <h4>Rejected</h4>
            <p class="rating" style="font-size: 32px; margin: 8px 0 0; color: #d9534f;">{{ $stats['rejected'] ?? 0 }}</p>
        </div>
    </div>

    <div class="services-messages">
        <div class="services" style="width:100%;">
            <h3>Your Services</h3>
            <div class="services-grid">
                @forelse($services as $service)
                    <div class="service-card">
                        <img src="{{ $service->service_pic ? route('supplier.services.image', $service->service_id) : asset('images/AdminLTELogo.png') }}" alt="{{ $service->name }}" onerror="this.onerror=null;this.src='{{ asset('images/AdminLTELogo.png') }}';" />
                        <h4>{{ $service->name }}</h4>
                        <p>{{ $service->category }}</p>
                        <p class="rating"><i class="fas fa-star"></i> {{ number_format($service->rating ?? 5, 1) }}</p>
                        <p style="color: var(--gold); font-weight:800;">₱{{ number_format($service->price ?? 0) }}</p>
                    </div>
                @empty
                    <div class="service-card" style="grid-column:1/-1; text-align:center; padding:40px;">
                        <p style="color:var(--muted); margin:0;">No services yet. <a href="{{ route('supplier.setup') }}" style="color: var(--gold);">Add your first service</a></p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
