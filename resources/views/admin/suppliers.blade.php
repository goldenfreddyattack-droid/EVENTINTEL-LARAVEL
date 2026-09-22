@extends('admin.layout')

@section('title', 'Supplier Registry')

@section('content')
<div class="admin-topbar">
    <div>
        <h1>Supplier Registry</h1>
        <p>Review registered suppliers, check permit status, and notify businesses when their business permit is close to expiry.</p>
    </div>
</div>

<div class="admin-cards">
    <article class="admin-card"><div class="num">{{ $stats['users'] }}</div><p>Total users</p></article>
    <article class="admin-card"><div class="num">{{ $stats['pending'] }}</div><p>Pending approvals</p></article>
    <article class="admin-card"><div class="num">{{ $stats['events'] }}</div><p>Total events</p></article>
</div>

@if(session('success'))
    <div class="admin-alert success">{{ session('success') }}</div>
@endif

<div class="admin-filter-bar">
    <form method="GET" action="{{ route('admin.suppliers') }}" class="admin-filter-form">
        <label for="permitStatus">Permit status</label>
        <select id="permitStatus" name="permit_status">
            <option value="all" {{ $selectedPermitStatus === 'all' ? 'selected' : '' }}>All</option>
            <option value="valid" {{ $selectedPermitStatus === 'valid' ? 'selected' : '' }}>Valid</option>
            <option value="expiring_soon" {{ $selectedPermitStatus === 'expiring_soon' ? 'selected' : '' }}>Expiring soon</option>
            <option value="expired" {{ $selectedPermitStatus === 'expired' ? 'selected' : '' }}>Expired</option>
            <option value="notified" {{ $selectedPermitStatus === 'notified' ? 'selected' : '' }}>Notified</option>
        </select>
        <button type="submit" class="admin-button small">Apply</button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Supplier</th>
                <th>Business</th>
                <th>Contact</th>
                <th>Permit Expiry</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse($suppliers as $supplier)
            @php
                $resolved = $supplier->resolved_permit_status ?? 'valid';
                $expiry = $supplier->business_permit_expiry_date ? \Carbon\Carbon::parse($supplier->business_permit_expiry_date)->format('M d, Y') : 'Not set';
            @endphp
            <tr>
                <td>
                    {{ trim(($supplier->first_name ?? '') . ' ' . ($supplier->last_name ?? '')) ?: ($supplier->full_name ?? $supplier->username) }}
                    <br><small>{{ $supplier->email }}</small>
                </td>
                <td>{{ $supplier->business_name ?? 'Not provided' }}<br><small>{{ $supplier->business_address ?? 'No address' }}</small></td>
                <td>{{ $supplier->phone ?? 'No phone' }}</td>
                <td>{{ $expiry }}</td>
                <td><span class="permit-pill permit-{{ $resolved }}">{{ str_replace('_', ' ', ucfirst($resolved)) }}</span></td>
                <td>
                    @if(in_array($resolved, ['expiring_soon', 'expired'], true))
                        <span class="muted-text">Supplier has reported an expiry issue</span>
                    @else
                        <span class="muted-text">No action needed</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;padding:40px;">No suppliers found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($suppliers->hasPages())
    <div class="admin-pagination">
        @if($suppliers->onFirstPage())
            <span class="disabled">Previous</span>
        @else
            <a href="{{ $suppliers->previousPageUrl() }}">Previous</a>
        @endif

        @for($page = 1; $page <= $suppliers->lastPage(); $page++)
            @if($page === $suppliers->currentPage())
                <span class="active">{{ $page }}</span>
            @else
                <a href="{{ $suppliers->url($page) }}">{{ $page }}</a>
            @endif
        @endfor

        @if($suppliers->hasMorePages())
            <a href="{{ $suppliers->nextPageUrl() }}">Next</a>
        @else
            <span class="disabled">Next</span>
        @endif
    </div>
@endif
@endsection

@section('styles')
<style>
    .admin-filter-bar { margin: 20px 0 24px; display:flex; justify-content:flex-end; }
    .admin-filter-form { display:flex; align-items:center; gap:12px; background:var(--panel); border:1px solid var(--border); border-radius:16px; padding:12px 14px; box-shadow:var(--shadow); }
    .admin-filter-form label { color:var(--muted); font-size:13px; font-weight:700; }
    .admin-filter-form select { padding:10px 12px; border:1px solid var(--border); border-radius:12px; background:#fff; color:var(--text); }
    .admin-button.small { padding:10px 16px; font-size:13px; }
    .admin-button.small.warn { background:linear-gradient(135deg,#f8d664,#f2b700,#d99a00); }
    .permit-pill { display:inline-flex; padding:8px 14px; border-radius:999px; font-weight:700; }
    .permit-valid { background:#e7f9ee; color:#247a44; }
    .permit-expiring_soon { background:#fff5d6; color:#b98200; }
    .permit-expired { background:#fdecec; color:#b63e3e; }
    .permit-notified { background:#eaf4ff; color:#2d6bc6; }
    .muted-text { color:var(--muted); font-size:12px; }
    .admin-table-wrap { overflow-x:auto; background:#fff; border:1px solid #e5e5e5; border-radius:20px; box-shadow:var(--shadow); }
    .admin-table { width:100%; border-collapse:separate; border-spacing:0 12px; min-width:900px; }
    .admin-table th, .admin-table td { padding:16px; text-align:left; vertical-align:top; }
    .admin-table th { color:var(--gold); background:#fffdf6; }
    .admin-table td { background:#fafafa; border:1px solid #ececec; color:#222; }
    .admin-pagination { display:flex; justify-content:center; gap:8px; margin-top:20px; }
    .admin-pagination a, .admin-pagination span { min-width:38px; padding:9px 12px; border:1px solid var(--border); border-radius:12px; background:var(--panel); color:var(--text); text-align:center; text-decoration:none; }
    .admin-pagination a:hover, .admin-pagination .active { border-color:var(--gold); background:rgba(212,175,55,.1); color:var(--gold); font-weight:700; }
    .admin-pagination .disabled { color:#aaa; }
</style>
@endsection
