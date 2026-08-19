@extends('supplier.layout')

@section('title', 'Supplier Profile')

@section('content')
<section>
    <h2>Supplier Profile</h2>

    @if(session('success'))
        <div class="alert success" style="margin-bottom: 16px;">{{ session('success') }}</div>
    @endif

    <div class="services-grid">
        <div class="setting-card" style="grid-column: 1 / -1;">
            <h3>Profile Details</h3>
            <form method="POST" action="{{ route('supplier.profile.update') }}">
                @csrf
                <div class="settings-grid">
                    <div>
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $profile->full_name ?? auth()->user()->full_name ?? auth()->user()->username) }}" required>
                    </div>
                    <div>
                        <label>Business Name</label>
                        <input type="text" name="business_name" value="{{ old('business_name', $profile->business_name ?? '') }}" required>
                    </div>
                    <div>
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $profile->email ?? auth()->user()->email) }}" required>
                    </div>
                    <div>
                        <label>Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $profile->phone ?? '') }}">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label>Business Address</label>
                        <input type="text" name="business_address" value="{{ old('business_address', $profile->business_address ?? '') }}">
                    </div>
                </div>
                <button type="submit" class="accept-btn" style="max-width:220px; margin-top:16px;">Save Profile</button>
            </form>
        </div>
    </div>
</section>
@endsection
