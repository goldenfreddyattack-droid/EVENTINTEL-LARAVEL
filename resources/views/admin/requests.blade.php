@extends('admin.layout')

@section('title', 'Verification Requests')

@section('styles')
<style>
    .admin-table-wrap { overflow-x:auto; background:#fff; border:1px solid #e5e5e5; border-radius:20px; box-shadow:var(--shadow); }
    .admin-table { width:100%; border-collapse:separate; border-spacing:0 12px; min-width:900px; }
    .admin-table th, .admin-table td { padding:16px; text-align:left; vertical-align:top; }
    .admin-table th { color:var(--gold); background:#fffdf6; }
    .admin-table td { background:#fafafa; border:1px solid #ececec; color:#222; }
    .status-pill { display:inline-flex; padding:8px 14px; border-radius:999px; background:#fff5d8; color:#b8860b; font-weight:700; }
    .request-actions { display:flex; gap:8px; flex-wrap:wrap; }
    .request-actions button { border:0; border-radius:10px; padding:9px 12px; color:#fff; font-weight:700; cursor:pointer; }
    .request-actions .approve { background:#28a745; }
    .request-actions .reject { background:#dc3545; }
    .admin-file { color:#007bff; text-decoration:none; }
    .admin-file:hover { text-decoration:underline; }
    .admin-thumb { display:block; width:95px; height:95px; object-fit:cover; border-radius:12px; border:1px solid #ddd; margin-top:10px; }
    .admin-pagination { display:flex; justify-content:center; gap:8px; margin-top:20px; }
    .admin-pagination a, .admin-pagination span { min-width:38px; padding:9px 12px; border:1px solid #ddd; border-radius:10px; background:#fff; color:#555; text-align:center; text-decoration:none; }
    .admin-pagination .active { border-color:var(--gold); background:#fff7df; color:var(--gold); font-weight:700; }
    .admin-pagination .disabled { color:#aaa; }
</style>
@endsection

@section('content')
<div class="admin-topbar"><div><h1>Verification Requests</h1><p>Review supplier and coordinator applications, verify documentation, and approve or reject directly from the admin panel.</p></div></div>
<div class="admin-cards"><article class="admin-card"><div class="num">{{ $stats['users'] }}</div><p>Total users</p></article><article class="admin-card"><div class="num">{{ $stats['pending'] }}</div><p>Pending approvals</p></article><article class="admin-card"><div class="num">{{ $stats['events'] }}</div><p>Total events</p></article></div>
@if(session('success'))<div class="admin-alert success">{{ session('success') }}</div>@endif
<div class="admin-table-wrap">
<table class="admin-table">
<thead><tr><th>Name</th><th>Role</th><th>Business</th><th>Status</th><th>Documents</th><th>Action</th></tr></thead>
<tbody>
@forelse($requests as $user)
<tr>
    <td>{{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->full_name ?? $user->username) }}<br><small>{{ $user->email }}</small><br><small>{{ $user->phone ?? '' }}</small></td>
    <td>{{ $user->role }}</td>
    <td>{{ $user->business_name ?? '' }}<br><small>{{ $user->business_address ?? '' }}</small></td>
    <td><span class="status-pill">{{ $user->status }}</span></td>
    <td>
        @if($user->valid_id)<a class="admin-file" target="_blank" href="{{ asset(ltrim($user->valid_id, '/')) }}">Valid ID</a><br>@endif
        @if($user->business_permit)<a class="admin-file" target="_blank" href="{{ asset(ltrim($user->business_permit, '/')) }}">Permit</a><br>@endif
        @if($user->face_capture)<a class="admin-file" target="_blank" href="{{ asset(ltrim($user->face_capture, '/')) }}">Live Face Capture</a><img class="admin-thumb" src="{{ asset(ltrim($user->face_capture, '/')) }}" alt="Face capture">@endif
        @if(!$user->valid_id && !$user->business_permit && !$user->face_capture)<small>No documents uploaded</small>@endif
    </td>
    <td>
        @if($user->status === 'pending')
            <div class="request-actions">
                <form method="POST" action="{{ route('admin.requests.update', $user->user_id) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="approve" type="submit">Approve</button></form>
                <form method="POST" action="{{ route('admin.requests.update', $user->user_id) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="reject" type="submit">Reject</button></form>
            </div>
        @else
            &mdash;
        @endif
    </td>
</tr>
@empty
<tr><td colspan="6" style="text-align:center;padding:40px;">No supplier or coordinator requests found.</td></tr>
@endforelse
</tbody>
</table>
</div>
@if($requests->hasPages())
    <div class="admin-pagination">
        @if($requests->onFirstPage())
            <span class="disabled">Previous</span>
        @else
            <a href="{{ $requests->previousPageUrl() }}">Previous</a>
        @endif

        @for($page = 1; $page <= $requests->lastPage(); $page++)
            @if($page === $requests->currentPage())
                <span class="active">{{ $page }}</span>
            @else
                <a href="{{ $requests->url($page) }}">{{ $page }}</a>
            @endif
        @endfor

        @if($requests->hasMorePages())
            <a href="{{ $requests->nextPageUrl() }}">Next</a>
        @else
            <span class="disabled">Next</span>
        @endif
    </div>
@endif
@endsection
