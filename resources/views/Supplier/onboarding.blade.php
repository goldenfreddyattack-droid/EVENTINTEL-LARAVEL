@extends('supplier.layout')

@section('title', 'Complete Your Setup')

@section('styles')
<style>
    .setup-wrap { max-width: 1100px; }
    .setup-hero { text-align: center; margin-bottom: 28px; }
    .setup-hero h2 { margin-bottom: 8px; }
    .setup-hero p { color: var(--muted); margin: 0 auto; max-width: 640px; line-height: 1.6; }
    .progress-bar { max-width: 520px; height: 10px; margin: 20px auto 0; background: #eee; border-radius: 999px; overflow: hidden; }
    .progress-fill { height: 100%; background: linear-gradient(90deg, #fff1a8, #f3c547, #c99208); border-radius: 999px; }
    .progress-label { color: var(--muted); font-size: 13px; margin-top: 7px; }
    .setup-card { background: #fff; border: 1px solid rgba(212,175,55,.25); border-radius: 16px; padding: 28px; margin-bottom: 28px; box-shadow: 0 10px 24px rgba(0,0,0,.05); }
    .setup-card h3 { display: flex; align-items: center; gap: 9px; margin: 0 0 6px; color: var(--gold); }
    .setup-card .sub { color: var(--muted); font-size: 14px; margin: 0 0 18px; }
    .step-num { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: var(--gold); color: #111; font-size: 13px; }
    .status { margin-left: auto; font-size: 12px; color: #2a9d6f; }
    .setup-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
    .setup-field { margin-bottom: 14px; }
    .setup-field label { display: block; font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; margin-bottom: 6px; }
    .setup-field input, .setup-field select, .setup-field textarea { width: 100%; padding: 11px 13px; border: 1px solid rgba(212,175,55,.35); border-radius: 9px; background: #fafafa; }
    .setup-file-input { height: 43px; padding: 5px 8px !important; font-size: 12px; }
    .setup-file-input::file-selector-button { height: 30px; border: 0; border-radius: 7px; padding: 5px 10px; margin-right: 7px; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; font-size: 12px; font-weight: 700; cursor: pointer; transition: filter .2s ease, transform .2s ease; }
    .setup-file-input::file-selector-button:hover { filter: brightness(.96); transform: translateY(-1px); }
    .setup-field textarea { min-height: 90px; resize: vertical; }
    .setup-actions { display: flex; justify-content: flex-end; gap: 10px; }
    .setup-btn { border: 0; border-radius: 9px; padding: 11px 18px; font-weight: 700; cursor: pointer; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); }
    .setup-item { position: relative; padding: 14px; border: 1px solid rgba(212,175,55,.2); border-radius: 10px; background: #fafafa; }
    .setup-item h4 { margin: 0 0 5px; }
    .setup-item p { color: var(--muted); font-size: 13px; margin: 4px 0; }
    .setup-item img { width: 100%; height: 130px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; }
    .setup-item .price { color: var(--gold); font-weight: 700; margin-top: 8px; }
    .delete-link { position: absolute; top: 10px; right: 10px; color: #d9534f; }
    .setup-alert { padding: 12px 14px; border-radius: 9px; margin-bottom: 18px; background: rgba(74,222,128,.12); color: #2a9d6f; }
    .setup-complete { text-align: center; border: 1px solid var(--gold); padding: 30px 24px; margin-bottom: 30px; }
    .setup-complete h3 { justify-content: center; margin-bottom: 12px; }
    .setup-complete p { margin: 0; color: var(--muted); }
</style>
@endsection

@section('content')
<section class="setup-wrap">
    <div class="setup-hero">
        <h2>Let's Set Up Your Shop</h2>
        <p>Add your business details and first services so clients can find and book you.</p>
        <div class="progress-bar"><div class="progress-fill" style="width: {{ $progressPct }}%"></div></div>
        <div class="progress-label">{{ $doneSteps }} of 2 steps completed</div>
    </div>

    @if(session('success'))
        <div class="setup-alert">{{ session('success') }}</div>
    @endif

    @if($setupComplete)
        <div class="setup-card setup-complete"><h3><i class="fas fa-check-circle"></i> You're all set!</h3><p>Your shop is ready for clients.</p></div>
    @endif

    <div class="setup-card">
        <h3><span class="step-num">1</span> Business Details <span class="status">{{ $detailsDone ? 'Done' : 'Incomplete' }}</span></h3>
        <p class="sub">Fill in the basic information clients will see.</p>
        <form method="POST" action="{{ route('supplier.setup.details') }}">
            @csrf
            <div class="setup-grid">
                <div class="setup-field"><label>Business Name *</label><input name="business_name" value="{{ old('business_name', $user->business_name) }}" required></div>
                <div class="setup-field"><label>Phone Number</label><input name="phone" value="{{ old('phone', $user->phone) }}"></div>
            </div>
            <div class="setup-field"><label>Business Address *</label><input name="business_address" value="{{ old('business_address', $user->business_address) }}" required></div>
            <div class="setup-actions"><button class="setup-btn" type="submit"><i class="fas fa-save"></i> Save Details</button></div>
        </form>
    </div>

    <div class="setup-card">
        <h3><span class="step-num">2</span> Add Your Services <span class="status">{{ $servicesDone ? $services->count() . ' service(s)' : 'Incomplete' }}</span></h3>
        <p class="sub">List the services you offer so clients can book you.</p>
        <form method="POST" action="{{ route('supplier.setup.services.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="setup-grid">
                <div class="setup-field"><label>Service Name *</label><input name="name" required></div>
                <div class="setup-field"><label>Category *</label><select name="category" required><option>Venue</option><option>Catering</option><option>Clothing</option><option>Host</option><option>Sounds & Lights</option><option>Photographer</option></select></div>
                <div class="setup-field"><label>Style / Cuisine</label><input name="style"></div>
                <div class="setup-field"><label>Price</label><input name="price" type="number" min="0" step="0.01" placeholder="₱"></div>
                <div class="setup-field"><label>Location</label><input name="address" placeholder="Address / Location"></div>
                <div class="setup-field"><label>Latitude</label><input name="latitude" placeholder="Optional"></div>
                <div class="setup-field"><label>Profile Picture</label><input class="setup-file-input" type="file" name="service_pic" accept="image/jpeg,image/png,image/webp"></div>
            </div>
            <div class="setup-field"><label>Description</label><textarea name="description"></textarea></div>
            <div class="setup-actions"><button class="setup-btn" type="submit"><i class="fas fa-plus"></i> Add Service</button></div>
        </form>
        @if($services->isNotEmpty())
            <div class="setup-grid" style="margin-top: 20px;">
                @foreach($services as $service)
                    <div class="setup-item"><form method="POST" action="{{ route('supplier.setup.services.destroy', $service->service_id) }}" onsubmit="return confirm('Delete this service?')" style="position:absolute;top:10px;right:10px;z-index:1;">@csrf @method('DELETE')<button type="submit" class="delete-link" style="border:0;background:transparent;cursor:pointer;"><i class="fas fa-trash"></i></button></form>@if($service->service_pic)<img src="{{ route('supplier.services.image', $service->service_id) }}" alt="{{ $service->name }}">@endif<h4>{{ $service->name }}</h4><p>{{ $service->category }}{{ $service->style ? ' | ' . $service->style : '' }}</p><p>{{ \Illuminate\Support\Str::limit($service->description ?? '', 90) }}</p><div class="price">₱{{ number_format((float) ($service->price ?? 0), 2) }}</div></div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection