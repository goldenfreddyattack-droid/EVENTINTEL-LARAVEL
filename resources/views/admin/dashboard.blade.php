@extends('admin.layout')

@section('title', 'Admin Control Panel')

@section('styles')
<style>
    .admin-form { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:20px; }
    .admin-form .full { grid-column:1/-1; }
    .admin-field { display:flex; flex-direction:column; gap:10px; }
    .admin-field label { color:#555; font-size:14px; font-weight:700; }
    .admin-field input, .admin-field select, .admin-field textarea { width:100%; padding:14px 16px; border:1px solid #d9d9d9; border-radius:16px; background:#fff; color:var(--text); outline:none; }
    .admin-field textarea { min-height:120px; resize:vertical; }
    .admin-field input:focus, .admin-field select:focus, .admin-field textarea:focus { border-color:var(--gold); box-shadow:0 0 0 3px rgba(212,160,23,.15); }
    .role-options { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
    .role-options label { display:flex; align-items:center; gap:10px; padding:14px 16px; border:1px solid #ddd; border-radius:14px; background:#fafafa; cursor:pointer; }
    .role-options input { accent-color:var(--gold); }
    @media(max-width:700px) { .admin-form { grid-template-columns:1fr; } .admin-form .full { grid-column:auto; } }
</style>
@endsection

@section('content')
<div class="admin-topbar">
    <div><h1>Admin Control Panel</h1><p>Manage site accounts, verify suppliers and coordinators, and register new businesses directly from the admin interface.</p></div>
</div>

<div class="admin-cards">
    <article class="admin-card"><div class="num">{{ $stats['users'] }}</div><p>Total users</p></article>
    <article class="admin-card"><div class="num">{{ $stats['pending'] }}</div><p>Pending approvals</p></article>
    <article class="admin-card"><div class="num">{{ $stats['events'] }}</div><p>Total events</p></article>
</div>

@if(session('success'))<div class="admin-alert success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="admin-alert error">{{ $errors->first() }}</div>@endif

<section class="admin-panel">
    <div class="admin-panel-head"><h2>Create a new account</h2><p>Register a client, supplier, event coordinator, or administrator. Accounts can be approved immediately or submitted as pending.</p></div>
    <form class="admin-form" method="POST" action="{{ route('admin.users.store') }}" autocomplete="off">
        @csrf
        <div class="admin-field"><label for="first_name">First Name</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder="First name"></div>
        <div class="admin-field"><label for="middle_initial">Middle Initial</label><input id="middle_initial" name="middle_initial" value="{{ old('middle_initial') }}" placeholder="M"></div>
        <div class="admin-field"><label for="last_name">Last Name</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder="Last name"></div>
        <div class="admin-field"><label for="username">Username</label><input id="username" name="username" value="{{ old('username') }}" required placeholder="user123"></div>
        <div class="admin-field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="email@example.com"></div>
        <div class="admin-field"><label for="password">Password</label><input id="password" type="password" name="password" required placeholder="Create a password"></div>
        <div class="admin-field"><label for="confirm_password">Confirm Password</label><input id="confirm_password" type="password" name="confirm_password" required placeholder="Repeat password"></div>
        <div class="admin-field full"><label>Account role</label><div class="role-options"><label><input type="radio" name="role" value="client" @checked(old('role','client') === 'client')> Client</label><label><input type="radio" name="role" value="supplier" @checked(old('role') === 'supplier')> Supplier</label><label><input type="radio" name="role" value="coordinator" @checked(old('role') === 'coordinator')> Coordinator</label><label><input type="radio" name="role" value="admin" @checked(old('role') === 'admin')> Admin</label></div></div>
        <div class="admin-field"><label for="status">Account status</label><select id="status" name="status"><option value="approved" @selected(old('status','approved') === 'approved')>Approved</option><option value="pending" @selected(old('status') === 'pending')>Pending</option><option value="rejected" @selected(old('status') === 'rejected')>Rejected</option></select></div>
        <div class="admin-field"><label for="phone">Phone</label><input id="phone" name="phone" value="{{ old('phone') }}" placeholder="0912 345 6789"></div>
        <div class="admin-field"><label for="age">Age</label><input id="age" type="number" min="13" name="age" value="{{ old('age') }}" placeholder="26"></div>
        <div class="admin-field"><label for="gender">Gender</label><input id="gender" name="gender" value="{{ old('gender') }}" placeholder="Female"></div>
        <div class="admin-field"><label for="province">Province</label><input id="province" name="province" value="{{ old('province') }}" placeholder="Pampanga"></div>
        <div class="admin-field"><label for="municipality">Municipality</label><input id="municipality" name="municipality" value="{{ old('municipality') }}" placeholder="Apalit"></div>
        <div class="admin-field"><label for="barangay">Barangay</label><input id="barangay" name="barangay" value="{{ old('barangay') }}" placeholder="San Jose"></div>
        <div class="admin-field"><label for="postal_code">Postal Code</label><input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" placeholder="2007"></div>
        <div class="admin-field full"><label for="business_name">Business / Organization Name</label><input id="business_name" name="business_name" value="{{ old('business_name') }}" placeholder="Event Intel Catering"></div>
        <div class="admin-field full"><label for="business_address">Business Address</label><textarea id="business_address" name="business_address" placeholder="Street, City, Province">{{ old('business_address') }}</textarea></div>
        <button type="submit" class="admin-button">Create Account</button>
    </form>
</section>
@endsection
