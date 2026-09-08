@extends('supplier.layout')

@section('title', 'Complete Your Setup')

@section('styles')
<style>
    /* ===== Main Outer Wrapper ===== */
    .setup-wrap { max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px; }

    /* ===== Hero Banner Container ===== */
    .setup-hero-card { background: #ffffff; border: 1px solid #ebebeb; border-radius: 20px; padding: 28px 24px; text-align: center; box-shadow: 0 8px 24px rgba(0,0,0,0.03); }
    .setup-hero-card h2 { font-size: 26px; font-weight: 800; margin-bottom: 6px; color: var(--text); }
    .setup-hero-card p { color: var(--muted); margin: 0 auto; max-width: 580px; font-size: 14px; line-height: 1.5; }
    .progress-bar { max-width: 420px; height: 8px; margin: 16px auto 0; background: #f0f0f0; border-radius: 999px; overflow: hidden; }
    .progress-fill { height: 100%; background: linear-gradient(90deg, #fff1a8, #f3c547, #c99208); border-radius: 999px; }
    .progress-label { color: var(--muted); font-size: 12px; margin-top: 6px; font-weight: 600; }

    /* ===== Section Floating Panels ===== */
    .setup-section-card { background: #ffffff; border: 1px solid #ebebeb; border-radius: 20px; padding: 24px; box-shadow: 0 8px 24px rgba(0,0,0,0.03); transition: box-shadow 0.3s ease; }
    .setup-section-card:hover { box-shadow: 0 12px 32px rgba(0,0,0,0.05); }
    .setup-section-card h3 { display: flex; align-items: center; gap: 8px; margin: 0 0 4px; font-size: 18px; font-weight: 800; color: var(--gold3); }
    .setup-section-card .sub { color: var(--muted); font-size: 13px; margin: 0 0 18px; }
    .step-num { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: var(--gold); color: #111; font-size: 12px; font-weight: 800; }
    .status { margin-left: auto; font-size: 12px; font-weight: 700; color: #2e9f4d; }

    /* ===== Form Fields & Grids ===== */
    .setup-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
    .setup-field { margin-bottom: 12px; }
    .setup-field label { display: block; font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 4px; }
    .setup-field input, .setup-field select, .setup-field textarea { width: 100%; padding: 10px 12px; border: 1px solid #e2e2e2; border-radius: 8px; background: #fafafa; font-size: 14px; outline: none; transition: border-color 0.2s ease, background 0.2s ease; }
    .setup-field input:focus, .setup-field select:focus, .setup-field textarea:focus { border-color: var(--gold); background: #ffffff; }

    /* Custom File Input Styling */
    .setup-file-input { height: 42px; padding: 5px 8px !important; font-size: 12px; }
    .setup-file-input::file-selector-button { height: 30px; border: 0; border-radius: 6px; padding: 4px 10px; margin-right: 8px; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; font-size: 12px; font-weight: 700; cursor: pointer; transition: filter 0.2s ease; }
    .setup-file-input::file-selector-button:hover { filter: brightness(0.95); }
    .setup-field textarea { min-height: 80px; resize: vertical; }

    /* ===== Form Action Buttons ===== */
    .setup-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }
    .setup-btn { border: 0; border-radius: 8px; padding: 10px 18px; font-size: 13px; font-weight: 700; cursor: pointer; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; transition: transform 0.2s ease; }
    .setup-btn:hover { transform: translateY(-1px); }

    /* ===== Added Services Section Wrapper ===== */
    .setup-services-container { margin-top: 24px; padding-top: 20px; border-top: 1px solid #f0f0f0; }
    .setup-services-container h4 { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 14px; }
    .setup-item { position: relative; padding: 14px; border: 1px solid #ebebeb; border-radius: 12px; background: #ffffff; transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .setup-item:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.04); }
    .setup-item h4 { margin: 0 0 4px; font-size: 15px; font-weight: 700; color: var(--text); }
    .setup-item p { color: var(--muted); font-size: 12px; margin: 2px 0; line-height: 1.4; }
    .setup-item img { width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; }
    .setup-item .price { color: var(--gold3); font-weight: 800; font-size: 14px; margin-top: 6px; }
    .delete-btn { position: absolute; top: 10px; right: 10px; border: 0; background: transparent; color: #d9534f; cursor: pointer; padding: 4px; font-size: 14px; }

    /* ===== Alert & Complete Containers ===== */
    .setup-alert { padding: 12px 16px; border-radius: 12px; background: rgba(46,159,77,0.1); color: #2e9f4d; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .setup-complete { text-align: center; border: 1px solid var(--gold); padding: 24px; background: #fffdf5; border-radius: 20px; }
    .setup-complete h3 { justify-content: center; margin-bottom: 6px; }
    .setup-complete p { margin: 0; color: var(--muted); font-size: 14px; }
</style>
@endsection

@section('content')
<section class="setup-wrap">
    
    {{-- CONTAINER 1: Hero Banner & Progress Bar --}}
    <div class="setup-hero-card">
        <h2>Let's Set Up Your Shop</h2>
        <p>Add your business details and first services so clients can find and book you.</p>
        <div class="progress-bar">
            <div class="progress-fill" style="width: {{ $progressPct }}%"></div>
        </div>
        <div class="progress-label">{{ $doneSteps }} of 2 steps completed</div>
    </div>

    @if(session('success'))
        <div class="setup-alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if($setupComplete)
        <div class="setup-complete">
            <h3><i class="fas fa-check-circle"></i> You're all set!</h3>
            <p>Your shop is ready for clients.</p>
        </div>
    @endif

    {{-- CONTAINER 2: Step 1 - Business Details --}}
    <div class="setup-section-card">
        <h3>
            <span class="step-num">1</span> Business Details 
            <span class="status">{{ $detailsDone ? 'Done' : 'Incomplete' }}</span>
        </h3>
        <p class="sub">Fill in the basic information clients will see.</p>
        
        <form method="POST" action="{{ route('supplier.setup.details') }}">
            @csrf
            <div class="setup-grid">
                <div class="setup-field">
                    <label>Business Name *</label>
                    <input name="business_name" value="{{ old('business_name', $user->business_name) }}" required>
                </div>
                <div class="setup-field">
                    <label>Phone Number</label>
                    <input name="phone" value="{{ old('phone', $user->phone) }}">
                </div>
            </div>
            
            <div class="setup-field">
                <label>Business Address *</label>
                <input name="business_address" value="{{ old('business_address', $user->business_address) }}" required>
            </div>
            
            <div class="setup-actions">
                <button class="setup-btn" type="submit">
                    <i class="fas fa-save"></i> Save Details
                </button>
            </div>
        </form>
    </div>

    {{-- CONTAINER 3: Step 2 - Services Section --}}
    <div class="setup-section-card">
        <h3>
            <span class="step-num">2</span> Add Your Services 
            <span class="status">{{ $servicesDone ? $services->count() . ' service(s)' : 'Incomplete' }}</span>
        </h3>
        <p class="sub">List the services you offer so clients can book you.</p>
        
        <form method="POST" action="{{ route('supplier.setup.services.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="setup-grid">
                <div class="setup-field">
                    <label>Service Name *</label>
                    <input name="name" required>
                </div>
                <div class="setup-field">
                    <label>Category *</label>
                    <select name="category" required>
                        <option>Venue</option>
                        <option>Catering</option>
                        <option>Clothing</option>
                        <option>Host</option>
                        <option>Sounds & Lights</option>
                        <option>Photographer</option>
                    </select>
                </div>
                <div class="setup-field">
                    <label>Style / Cuisine</label>
                    <input name="style">
                </div>
                <div class="setup-field">
                    <label>Price</label>
                    <input name="price" type="number" min="0" step="0.01" placeholder="₱">
                </div>
                <div class="setup-field">
                    <label>Location</label>
                    <input name="address" placeholder="Address / Location">
                </div>
                <div class="setup-field">
                    <label>Latitude</label>
                    <input name="latitude" placeholder="Optional">
                </div>
                <div class="setup-field">
                    <label>Profile Picture</label>
                    <input class="setup-file-input" type="file" name="service_pic" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>
            
            <div class="setup-field">
                <label>Description</label>
                <textarea name="description"></textarea>
            </div>
            
            <div class="setup-actions">
                <button class="setup-btn" type="submit">
                    <i class="fas fa-plus"></i> Add Service
                </button>
            </div>
        </form>

        {{-- SUB-CONTAINER: Existing Services Cards --}}
        @if($services->isNotEmpty())
            <div class="setup-services-container">
                <h4>Added Services ({{ $services->count() }})</h4>
                <div class="setup-grid">
                    @foreach($services as $service)
                        <div class="setup-item">
                            <form method="POST" action="{{ route('supplier.setup.services.destroy', $service->service_id) }}" onsubmit="return confirm('Delete this service?')">
                                @csrf 
                                @method('DELETE')
                                <button type="submit" class="delete-btn" aria-label="Delete service">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            
                            @if($service->service_pic)
                                <img src="{{ route('supplier.services.image', $service->service_id) }}" alt="{{ $service->name }}">
                            @endif
                            
                            <h4>{{ $service->name }}</h4>
                            <p>{{ $service->category }}{{ $service->style ? ' | ' . $service->style : '' }}</p>
                            <p>{{ \Illuminate\Support\Str::limit($service->description ?? '', 90) }}</p>
                            <div class="price">₱{{ number_format((float) ($service->price ?? 0), 2) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

</section>
@endsection