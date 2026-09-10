@extends('coordinator.layout')

@section('title', 'Coordinator Settings')

@section('styles')
<style>
    .coord-container { max-width: 980px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; padding-top: 4px; }
    .coord-card-floating { background: #ffffff; border: 1px solid #ebebeb; border-radius: 20px; padding: 24px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03); }

    .coord-header-bar { border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; margin-bottom: 20px; }
    .coord-header-bar h2 { font-size: 22px; font-weight: 700; color: var(--text); margin: 0 0 2px; }
    .coord-header-bar p { color: var(--muted); font-size: 13px; margin: 0; }

    .alert-success-box { background: rgba(46,159,77,0.08); border: 1px solid rgba(46,159,77,0.2); color: #2a8a43; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }

    .settings-grid-layout { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
    .setting-section-card { background: #fafafa; border: 1px solid #eaeaea; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; }
    .setting-section-card h3 { font-size: 16px; font-weight: 700; color: var(--text); margin: 0 0 16px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px; }

    .setting-form { display: flex; flex-direction: column; flex: 1; gap: 12px; }
    .setting-field { display: flex; flex-direction: column; gap: 4px; }
    .setting-field label { font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.4px; }
    .setting-field input { width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #e0e0e0; background: #ffffff; color: var(--text); font-size: 13px; outline: none; transition: border-color 0.2s ease; }
    .setting-field input:focus { border-color: var(--gold); }

    .checkbox-group { display: flex; flex-direction: column; gap: 10px; margin: 4px 0 16px; }
    .checkbox-item { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--text); font-weight: 500; cursor: pointer; }
    .checkbox-item input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--gold); margin: 0; }

    .btn-submit { margin-top: auto; padding: 10px 16px; border: none; border-radius: 8px; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; font-size: 13px; font-weight: 700; cursor: pointer; transition: transform 0.2s ease; width: 100%; }
    .btn-submit:hover { transform: translateY(-1px); }
</style>
@endsection

@section('content')
<div class="coord-container">
    <div class="coord-card-floating">
        <header class="coord-header-bar">
            <h2><i class="fas fa-cog text-gold"></i> Settings</h2>
            <p>Manage your coordinator account details and preference settings.</p>
        </header>

        @if(session('success'))
            <div class="alert-success-box"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        <div class="settings-grid-layout">
            {{-- Profile Card --}}
            <div class="setting-section-card">
                <h3><i class="fas fa-user-edit text-gold"></i> Profile Information</h3>
                <form method="POST" action="{{ route('coordinator.settings.update') }}" class="setting-form">
                    @csrf
                    <div class="setting-field">
                        <label>Full Name</label>
                        <input name="full_name" value="{{ $user->full_name }}" placeholder="Full Name" required>
                    </div>
                    <div class="setting-field">
                        <label>Email Address</label>
                        <input type="email" name="email" value="{{ $user->email }}" placeholder="Email Address" required>
                    </div>
                    <div class="setting-field">
                        <label>Business Name</label>
                        <input name="business_name" value="{{ $user->business_name }}" placeholder="Business Name">
                    </div>
                    <button class="btn-submit" type="submit">Save Changes</button>
                </form>
            </div>

            {{-- Notifications Card --}}
            <div class="setting-section-card">
                <h3><i class="fas fa-bell text-gold"></i> Notifications</h3>
                <form class="setting-form">
                    <div class="checkbox-group">
                        <label class="checkbox-item"><input type="checkbox" checked> Booking Alerts</label>
                        <label class="checkbox-item"><input type="checkbox" checked> Messages</label>
                        <label class="checkbox-item"><input type="checkbox"> Promotions</label>
                    </div>
                    <button type="button" class="btn-submit">Save Preferences</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection