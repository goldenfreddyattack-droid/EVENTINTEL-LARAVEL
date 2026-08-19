@extends('admin.layout')

@section('title', 'Admin Summary Dashboard')

@section('content')
<div class="admin-topbar"><div><h1>Admin Dashboard</h1><p>Monitor users, events, and supplier or coordinator verification.</p></div></div>
<div class="admin-cards">
    <article class="admin-card"><div class="num">{{ $stats['users'] }}</div><p>Total Users</p></article>
    <article class="admin-card"><div class="num">{{ $stats['pending'] }}</div><p>Pending Verification</p></article>
    <article class="admin-card"><div class="num">{{ $stats['events'] }}</div><p>Total Events</p></article>
</div>
@endsection
