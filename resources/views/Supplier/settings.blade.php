@extends('supplier.layout')

@section('title', 'Settings')

@section('content')
<section>
    <h2>Settings</h2>

    @if(session('success'))
        <div class="alert success" style="margin-bottom: 16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert error" style="margin-bottom: 16px;">{{ session('error') }}</div>
    @endif

    <div class="services-grid">
        <div class="setting-card">
            <h3>Profile Information</h3>
            <form method="POST" action="{{ route('supplier.settings.update') }}">
                @csrf
                <input type="hidden" name="update_profile" value="1">
                <label>Full Name</label>
                <input type="text" name="full_name" value="{{ old('full_name', $user->full_name ?? $user->username) }}" required>
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                <label>Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
                <button type="submit" class="accept-btn">Save Changes</button>
            </form>
        </div>

        <div class="setting-card">
            <h3>Change Password</h3>
            <form method="POST" action="{{ route('supplier.settings.update') }}">
                @csrf
                <input type="hidden" name="change_password" value="1">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
                <label>New Password</label>
                <input type="password" name="new_password" required>
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
                <button type="submit" class="accept-btn">Update Password</button>
            </form>
        </div>

        <div class="setting-card" style="display:flex;flex-direction:column;">
            <h3>Notification Settings</h3>
            <form method="POST" action="{{ route('supplier.settings.update') }}" style="display:flex;flex-direction:column;flex:1;">
                @csrf
                <input type="hidden" name="save_notifications" value="1">
                <div style="display:flex;align-items:center;gap:24px;margin-bottom:16px;flex-wrap:wrap;">
                    <label style="display:flex;align-items:center;gap:10px;margin:0;">
                        <input type="checkbox" name="booking_alerts" value="1" {{ !empty($notificationSettings['booking_alerts']) ? 'checked' : '' }}>
                        Booking Alerts
                    </label>
                    <label style="display:flex;align-items:center;gap:10px;margin:0;">
                        <input type="checkbox" name="messages" value="1" {{ !empty($notificationSettings['messages']) ? 'checked' : '' }}>
                        Messages
                    </label>
                    <label style="display:flex;align-items:center;gap:10px;margin:0;">
                        <input type="checkbox" name="promotions" value="1" {{ !empty($notificationSettings['promotions']) ? 'checked' : '' }}>
                        Promotions
                    </label>
                </div>
                <button type="submit" class="accept-btn" style="margin-top:auto;">Save Preferences</button>
            </form>
        </div>
    </div>
</section>
@endsection
