@extends('coordinator.layout')

@section('title', 'Coordinator Profile')

@section('styles')
<style>
    .coord-container { max-width: 980px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; padding-top: 4px; }
    .coord-card-floating { background: #ffffff; border: 1px solid #ebebeb; border-radius: 20px; padding: 24px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03); }

    .coord-header-bar { border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; margin-bottom: 20px; }
    .coord-header-bar h2 { font-size: 22px; font-weight: 700; color: var(--text); margin: 0 0 2px; }
    .coord-header-bar p { color: var(--muted); font-size: 13px; margin: 0; }

    .alert-success-box { background: rgba(46,159,77,0.08); border: 1px solid rgba(46,159,77,0.2); color: #2a8a43; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }

    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 12px; }
    .form-grid input, .form-section-card textarea { width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #e0e0e0; background: #fafafa; color: var(--text); font-size: 13px; outline: none; transition: border-color 0.2s ease; }
    .form-grid input:focus, .form-section-card textarea:focus { border-color: var(--gold); background: #ffffff; }
    .form-section-card textarea { min-height: 80px; resize: vertical; margin-bottom: 12px; width: 100%; }

    .form-section-card { background: #fafafa; border: 1px solid #eaeaea; border-radius: 16px; padding: 20px; margin-bottom: 16px; }
    .form-section-card h3 { font-size: 16px; font-weight: 700; color: var(--text); margin: 0 0 14px; border-bottom: 1px solid #f0f0f0; padding-bottom: 8px; }

    .btn-submit { border: none; padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; }
</style>
@endsection

@section('content')
<div class="coord-container">
    <div class="coord-card-floating">
        <header class="coord-header-bar">
            <h2><i class="fas fa-id-card text-gold"></i> Coordinator Profile</h2>
            <p>Customize your public profile, portfolio, and offered services visible to clients.</p>
        </header>

        @if(session('success'))
            <div class="alert-success-box"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        {{-- Portfolio Section --}}
        <div class="form-section-card">
            <h3>Portfolio: About & Services</h3>
            <form method="POST" action="{{ route('coordinator.profile.update') }}">
                @csrf
                <div class="form-grid">
                    <input name="business_name" value="{{ old('business_name',$user->business_name) }}" placeholder="Business name">
                    <input name="business_address" value="{{ old('business_address',$user->business_address) }}" placeholder="Business address">
                </div>
                <textarea name="about" placeholder="Tell clients about yourself...">{{ old('about',$profile->about ?? '') }}</textarea>
                <textarea name="services" placeholder="Services offered, one per line">{{ old('services',str_replace('|',"\n",$profile->services ?? '')) }}</textarea>
                <button class="btn-submit" type="submit"><i class="fas fa-save"></i> Save Portfolio</button>
            </form>
        </div>

        {{-- Gallery Section Placeholder --}}
        <div class="form-section-card" style="margin-bottom:0;">
            <h3>Gallery</h3>
            <p style="font-size:13px; color:var(--muted); margin:0;">Gallery management and portfolio showcase options are available from your coordinator profile settings.</p>
        </div>
    </div>
</div>
@endsection