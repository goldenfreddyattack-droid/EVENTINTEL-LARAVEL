@extends('supplier.layout')

@section('title', 'Supplier Dashboard')

@section('content')
<style>
    /* ===== Dashboard Container & Floating Panels ===== */
    .dashboard-container {padding-top: 6px; display: flex; flex-direction: column; gap: 24px;}
    .dash-card-floating {background: #ffffff; border: 1px solid #ebebeb; border-radius: 20px; padding: 24px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03); transition: box-shadow 0.3s ease;}
    .dash-card-floating:hover {box-shadow: 0 14px 36px rgba(0, 0, 0, 0.05);}

    /* ===== Section 1: Top Floating Header ===== */
    .dash-header {display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;}
    .dash-header h2 {font-size: 28px; font-weight: 800; margin-bottom: 4px;}
    .dash-subtitle {color: var(--muted); font-size: 14px;}
    .header-action-btn {display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; font-size: 14px; text-decoration: none;}
    .dash-alert {background: #fffdf5; border: 1px solid rgba(212, 175, 55, 0.35); border-radius: 16px; padding: 16px 20px; margin-top: 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;}
    .dash-alert-info h4 {font-size: 15px; font-weight: 700; margin-bottom: 4px; color: var(--text);}
    .dash-alert-info p {font-size: 13px; color: var(--muted); margin: 0;}

    /* ===== Section 2: Floating Stats Grid ===== */
    .dash-stats-grid {display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;}
    .stat-box {background: #fafafa; border: 1px solid #eaeaea; border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 4px; transition: border-color 0.2s ease, transform 0.2s ease, background 0.2s ease;}
    .stat-box:hover {background: #ffffff; border-color: var(--border2); transform: translateY(-2px);}
    .stat-label {font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px;}
    .stat-num {font-size: 24px; font-weight: 800; line-height: 1.1;}
    
    /* Stat Custom Colors */
    .text-gold {color: var(--gold);}
    .text-pending {color: #f39c12;}
    .text-accepted {color: #27ae60;}
    .text-pending-pay {color: #e67e22;}
    .text-danger {color: #c0392b;}
    .text-done {color: #2980b9;}

    /* ===== Section 3: Floating Services Section ===== */
    .section-title {display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;}
    .section-title h3 {font-size: 20px; font-weight: 800; margin: 0;}
    .section-subtitle {font-size: 13px; color: var(--muted); margin-top: 2px;}
    .filter-pills {display: flex; gap: 8px; background: #f5f5f5; padding: 4px; border-radius: 999px; border: 1px solid #eaeaea;}
    .pill-btn {border: none; background: transparent; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600; color: var(--muted); cursor: pointer; transition: all 0.2s ease;}
    .pill-btn.active, .pill-btn:hover {background: #ffffff; color: var(--text); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);}

    /* Services Inner Grid */
    .dash-services-grid {display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px;}
    .dash-service-card {background: #ffffff; border: 1px solid #ebebeb; border-radius: 16px; overflow: hidden; transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease, opacity 0.25s ease;}
    .dash-service-card:hover {transform: translateY(-4px); border-color: var(--border); box-shadow: 0 12px 24px rgba(0, 0, 0, 0.05);}
    .card-img-wrapper {position: relative; width: 100%; height: 150px; background: #f8f8f8;}
    .card-img-wrapper img {width: 100%; height: 100%; object-fit: cover; filter: none;}
    .card-badge {position: absolute; top: 10px; right: 10px; background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(4px); padding: 4px 8px; border-radius: 8px; font-size: 12px; font-weight: 700; color: #111;}
    .card-badge i {color: var(--gold); margin-right: 2px;}

    /* Action Dropdown Menu */
    .card-actions-dropdown {position: absolute; top: 10px; left: 10px;}
    .card-action-btn {width: 28px; height: 28px; border-radius: 50%; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(4px); border: none; color: var(--text); display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px; transition: background 0.2s ease;}
    .card-action-btn:hover {background: #ffffff; color: var(--gold3);}
    .card-menu {display: none; position: absolute; top: 34px; left: 0; background: #ffffff; border: 1px solid #ebebeb; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); padding: 6px 0; z-index: 10; min-width: 130px;}
    .card-menu.show {display: block;}
    .card-menu-item {display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 14px; font-size: 13px; color: var(--text); text-decoration: none; background: none; border: none; cursor: pointer; text-align: left;}
    .card-menu-item:hover {background: #f8f8f8; color: var(--gold3);}

    .card-body {padding: 16px;}
    .card-body h4 {font-size: 16px; font-weight: 700; margin-bottom: 4px; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}
    .card-category {font-size: 12px; color: var(--muted); display: block; margin-bottom: 12px;}
    .card-footer {display: flex; align-items: center; justify-content: space-between; padding-top: 10px; border-top: 1px solid #f4f4f4;}
    .card-price {font-size: 16px; font-weight: 800; color: var(--gold3);}
    .status-indicator {font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.4px;}
    .status-indicator.active {background: rgba(46, 159, 77, 0.1); color: #2e9f4d;}
    .status-indicator.inactive {background: rgba(102, 102, 102, 0.1); color: var(--muted);}

    /* ===== Empty State ===== */
    .dash-empty-state {grid-column: 1 / -1; background: #fafafa; border: 2px dashed #e0e0e0; border-radius: 16px; padding: 40px 20px; text-align: center;}
    .empty-icon {font-size: 32px; color: #ccc; margin-bottom: 10px;}
    .dash-empty-state p {color: var(--muted); margin-bottom: 16px; font-size: 14px;}

    /* ===== Chart Container Styling ===== */
    .chart-wrapper {position: relative; width: 100%; height: 300px; margin-top: 10px;}
</style>

<section class="dashboard-container">
    {{-- FLOATING PANEL 1: Page Header & Alert --}}
    <div class="dash-card-floating">
        <header class="dash-header">
            <div>
                <h2>Supplier Dashboard</h2>
                <p class="dash-subtitle">Welcome back, <strong>{{ auth()->user()->full_name ?? auth()->user()->username }}</strong></p>
            </div>
            <a href="{{ route('supplier.setup') }}" class="accept-btn header-action-btn">
                <i class="fas fa-plus"></i> New Service
            </a>
        </header>

        @if($serviceCount == 0)
            <div class="dash-alert">
                <div class="dash-alert-info">
                    <h4><i class="fas fa-exclamation-circle text-gold"></i> Your shop needs a few details!</h4>
                    <p>Add your business details and services so clients can discover and book your services.</p>
                </div>
                <a href="{{ route('supplier.setup') }}" class="accept-btn">Complete Setup</a>
            </div>
        @endif
    </div>

    {{-- FLOATING PANEL 2: Detailed Stats Grid --}}
    <div class="dash-card-floating">
        <div class="dash-stats-grid">
            <div class="stat-box">
                <span class="stat-label">Total</span>
                <span class="stat-num text-gold">{{ $stats['total'] ?? 0 }}</span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Pending</span>
                <span class="stat-num text-pending">{{ $stats['pending'] ?? 0 }}</span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Accepted</span>
                <span class="stat-num text-accepted">{{ $stats['accepted'] ?? 0 }}</span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Pending Pay</span>
                <span class="stat-num text-pending-pay">{{ $stats['pending_payment'] ?? 0 }}</span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Declined</span>
                <span class="stat-num text-danger">{{ $stats['rejected'] ?? 0 }}</span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Finished / Paid</span>
                <span class="stat-num text-done">{{ $stats['completed'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    {{-- FLOATING PANEL 3: Analytics Chart --}}
    <div class="dash-card-floating">
        <div class="section-title">
            <div>
                <h3>Request Analytics</h3>
                <p class="section-subtitle">Visual overview of all booking statuses</p>
            </div>
        </div>
        <div class="chart-wrapper">
            <canvas id="requestsChart"></canvas>
        </div>
    </div>

    {{-- FLOATING PANEL 4: Services Section --}}
    <div class="dash-card-floating">
        <div class="section-title">
            <div>
                <h3>Your Services</h3>
                <p class="section-subtitle">Manage and monitor your active offerings</p>
            </div>
            
            <div class="filter-pills">
                <button class="pill-btn active" data-filter="all">All ({{ count($services) }})</button>
                <button class="pill-btn" data-filter="active">Active</button>
            </div>
        </div>

        <div class="dash-services-grid">
            @forelse($services as $service)
                <div class="dash-service-card" data-status="{{ $service->status ?? 'active' }}" data-category="{{ strtolower($service->category) }}">
                    <div class="card-img-wrapper">
                        <img 
                            src="{{ $service->service_pic ? route('supplier.services.image', $service->service_id) : asset('images/AdminLTELogo.png') }}" 
                            alt="{{ $service->name }}" 
                            onerror="this.onerror=null;this.src='{{ asset('images/AdminLTELogo.png') }}';" 
                            loading="lazy"
                        />
                        <span class="card-badge"><i class="fas fa-star"></i> {{ number_format($service->rating ?? 5, 1) }}</span>
                        
                        <div class="card-actions-dropdown">
                            <button class="card-action-btn" type="button" aria-label="Service actions">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="card-menu">
                                <a href="{{ route('supplier.setup') }}" class="card-menu-item"><i class="fas fa-edit"></i> Edit</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <h4>{{ $service->name }}</h4>
                        <span class="card-category">{{ $service->category }}</span>
                        
                        <div class="card-footer">
                            <span class="card-price">₱{{ number_format($service->price ?? 0) }}</span>
                            <span class="status-indicator {{ $service->status ?? 'active' }}">{{ ucfirst($service->status ?? 'active') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="dash-empty-state">
                    <i class="fas fa-concierge-bell empty-icon"></i>
                    <p>No services added yet.</p>
                    <a href="{{ route('supplier.setup') }}" class="accept-btn">Add Your First Service</a>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- Load Chart.js Library --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- Interactive Behavior & Chart Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Chart.js Initialization
        const ctx = document.getElementById('requestsChart').getContext('2d');
        const requestsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Accepted', 'Pending Payment', 'Declined', 'Finished / Paid'],
                datasets: [{
                    label: 'Requests',
                    data: [
                        {{ $stats['pending'] ?? 0 }},
                        {{ $stats['accepted'] ?? 0 }},
                        {{ $stats['pending_payment'] ?? 0 }},
                        {{ $stats['rejected'] ?? 0 }},
                        {{ $stats['completed'] ?? 0 }}
                    ],
                    backgroundColor: [
                        '#f39c12', // Pending
                        '#27ae60', // Accepted
                        '#e67e22', // Pending Payment
                        '#c0392b', // Declined
                        '#2980b9'  // Finished / Paid
                    ],
                    borderRadius: 8,
                    maxBarThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    }
                }
            }
        });

        // Filter Buttons
        const pillBtns = document.querySelectorAll('.pill-btn');
        const serviceCards = document.querySelectorAll('.dash-service-card');

        pillBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                pillBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.getAttribute('data-filter');

                serviceCards.forEach(card => {
                    const status = card.getAttribute('data-status') || 'active';
                    if (filter === 'all' || status === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Action Dropdown Toggle
        document.querySelectorAll('.card-action-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const menu = this.nextElementSibling;
                document.querySelectorAll('.card-menu').forEach(m => {
                    if (m !== menu) m.classList.remove('show');
                });
                if (menu) menu.classList.toggle('show');
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function () {
            document.querySelectorAll('.card-menu').forEach(m => m.classList.remove('show'));
        });
    });
</script>
@endsection