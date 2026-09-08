@extends('supplier.layout')

@section('title', 'Settings')

@section('styles')
<style>
    /* ===== Floating Container & Grid Layout ===== */
    .settings-wrapper { max-width: 980px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; padding-top: 4px; }
    .settings-card-floating { background: #ffffff; border: 1px solid #ebebeb; border-radius: 20px; padding: 24px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03); transition: box-shadow 0.2s ease; }
    .settings-card-floating:hover { box-shadow: 0 14px 36px rgba(0, 0, 0, 0.05); }

    /* ===== Top Header Bar ===== */
    .settings-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; border-bottom: 1px solid #f0f0f0; padding-bottom: 16px; margin-bottom: 20px; }
    .settings-header h2 { font-size: 22px; font-weight: 700; color: var(--text); margin: 0; display: flex; align-items: center; gap: 8px; }
    .settings-header p { color: var(--muted); font-size: 13px; margin: 2px 0 0; }

    /* ===== Alerts ===== */
    .settings-alert { padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .settings-alert.success { background: rgba(46, 159, 77, 0.08); border: 1px solid rgba(46, 159, 77, 0.2); color: #2a8a43; }
    .settings-alert.error { background: rgba(217, 83, 79, 0.08); border: 1px solid rgba(217, 83, 79, 0.2); color: #d9534f; }

    /* ===== Settings Cards Grid ===== */
    .settings-grid-layout { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
    .setting-section-card { background: #fafafa; border: 1px solid #eaeaea; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; transition: border-color 0.2s ease, background 0.2s ease; }
    .setting-section-card:hover { border-color: var(--border2); background: #ffffff; }
    .setting-section-card h3 { font-size: 16px; font-weight: 700; color: var(--text); margin: 0 0 16px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px; }

    /* ===== Form Fields ===== */
    .setting-form { display: flex; flex-direction: column; flex: 1; gap: 12px; }
    .setting-field { display: flex; flex-direction: column; gap: 4px; }
    .setting-field label { font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.4px; }
    .setting-field input[type="text"], .setting-field input[type="email"], .setting-field input[type="password"] { width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #e0e0e0; background: #ffffff; color: var(--text); font-size: 13px; outline: none; transition: border-color 0.2s ease; }
    .setting-field input:focus { border-color: var(--gold); }

    /* Checkboxes Group */
    .checkbox-group { display: flex; flex-direction: column; gap: 10px; margin: 4px 0 16px; }
    .checkbox-item { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--text); font-weight: 500; cursor: pointer; user-select: none; }
    .checkbox-item input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--gold); cursor: pointer; margin: 0; }

    /* Action Buttons */
    .setting-submit-btn { margin-top: auto; padding: 10px 16px; border: none; border-radius: 8px; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; font-size: 13px; font-weight: 700; cursor: pointer; transition: transform 0.2s ease; align-self: flex-start; width: 100%; }
    .setting-submit-btn:hover { transform: translateY(-1px); }
</style>
@endsection

@section('content')
<div class="settings-wrapper">

    {{-- FLOATING PANEL: Settings Container --}}
    <div class="settings-card-floating">
        
        <header class="settings-header">
            <div>
                <h2><i class="fas fa-cog text-gold"></i> Settings</h2>
                <p>Manage your account credentials, profile information, and preferences.</p>
            </div>
        </header>

        @if(session('success'))
            <div class="settings-alert success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="settings-alert error">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="settings-grid-layout">
            
            {{-- Profile Information --}}
            <div class="setting-section-card">
                <h3><i class="fas fa-user-edit text-gold"></i> Profile Information</h3>
                <form method="POST" action="{{ route('supplier.settings.update') }}" class="setting-form">
                    @csrf
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="setting-field">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $user->full_name ?? $user->username) }}" required>
                    </div>
                    
                    <div class="setting-field">
                        <label>Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    
                    <div class="setting-field">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
                    </div>

                    <button type="submit" class="setting-submit-btn">Save Changes</button>
                </form>
            </div>

            {{-- Change Password --}}
            <div class="setting-section-card">
                <h3><i class="fas fa-lock text-gold"></i> Change Password</h3>
                <form method="POST" action="{{ route('supplier.settings.update') }}" class="setting-form">
                    @csrf
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="setting-field">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    
                    <div class="setting-field">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    
                    <div class="setting-field">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="setting-submit-btn">Update Password</button>
                </form>
            </div>

            {{-- Notification Preferences --}}
            <div class="setting-section-card">
                <h3><i class="fas fa-bell text-gold"></i> Notifications</h3>
                <form method="POST" action="{{ route('supplier.settings.update') }}" class="setting-form">
                    @csrf
                    <input type="hidden" name="save_notifications" value="1">
                    
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="booking_alerts" value="1" {{ !empty($notificationSettings['booking_alerts']) ? 'checked' : '' }}>
                            Booking Alerts
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="messages" value="1" {{ !empty($notificationSettings['messages']) ? 'checked' : '' }}>
                            Direct Messages
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="promotions" value="1" {{ !empty($notificationSettings['promotions']) ? 'checked' : '' }}>
                            Promotional Updates
                        </label>
                    </div>

                    <button type="submit" class="setting-submit-btn">Save Preferences</button>
                </form>
            </div>

        </div>
    </div>

</div>
@endsection