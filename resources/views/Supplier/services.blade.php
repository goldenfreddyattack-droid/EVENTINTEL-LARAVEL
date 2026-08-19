@extends('supplier.layout')

@section('title', 'My Services')

@section('content')
<style>
    .service-file-input { width: 100%; height: 48px; padding: 7px 8px; border-radius: 14px; border: 1px solid var(--border); background: rgba(255,255,255,.9); color: var(--text); margin-bottom: 12px; font-size: 12px; }
    .service-file-input::file-selector-button { height: 32px; border: 0; border-radius: 7px; padding: 5px 10px; margin-right: 7px; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; font-size: 12px; font-weight: 700; cursor: pointer; transition: filter .2s ease, transform .2s ease; }
    .service-file-input::file-selector-button:hover { filter: brightness(.96); transform: translateY(-1px); }
</style>
<section>
    <h2>My Services</h2>

    @if(session('success'))
        <div class="alert success" style="margin-bottom: 16px;">{{ session('success') }}</div>
    @endif

    <div class="services" style="margin-bottom: 30px;">
        <h3>Add New Service</h3>
        <form method="POST" action="{{ route('supplier.services.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="settings-grid">
                <div>
                    <label>Service name</label>
                    <input type="text" name="name" placeholder="Enter service name" required style="width:100%;padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:rgba(255,255,255,.9);color:var(--text);margin-bottom:12px;">
                </div>
                <div>
                    <label>Category</label>
                    <select name="category" style="width:100%;padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:rgba(255,255,255,.9);color:var(--text);margin-bottom:12px;">
                        <option value="Venue">Venue</option>
                        <option value="Catering">Catering</option>
                        <option value="Clothing">Clothing</option>
                        <option value="Host">Host</option>
                        <option value="Photographer">Photographer</option>
                        <option value="Sounds & Lights">Sounds & Lights</option>
                    </select>
                </div>
                <div>
                    <label>Style</label>
                    <input type="text" name="style" placeholder="Style / cuisine" style="width:100%;padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:rgba(255,255,255,.9);color:var(--text);margin-bottom:12px;">
                </div>
                <div>
                    <label>Price</label>
                    <input type="number" name="price" placeholder="₱" style="width:100%;padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:rgba(255,255,255,.9);color:var(--text);margin-bottom:12px;">
                </div>
                <div>
                    <label>Location</label>
                    <input type="text" name="address" placeholder="Address / Location" style="width:100%;padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:rgba(255,255,255,.9);color:var(--text);margin-bottom:12px;">
                </div>
                <div>
                    <label>Latitude</label>
                    <input type="text" name="latitude" placeholder="Optional" style="width:100%;padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:rgba(255,255,255,.9);color:var(--text);margin-bottom:12px;">
                </div>
                <div>
                    <label>Profile Picture</label>
                    <input class="service-file-input" type="file" name="service_pic" accept="image/jpeg,image/png,image/webp">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label>Description</label>
                    <textarea name="description" rows="4" placeholder="Describe your service" style="width:100%;padding:14px 16px;border-radius:14px;border:1px solid var(--border);background:rgba(255,255,255,.9);color:var(--text);resize:vertical;"></textarea>
                </div>
            </div>
            <button type="submit" class="accept-btn" style="margin-top:16px;">Add Service</button>
        </form>
    </div>

    <div class="services-grid">
        @forelse($services as $service)
            <div class="service-card">
                <img src="{{ $service->service_pic ? route('supplier.services.image', $service->service_id) : asset('images/AdminLTELogo.png') }}" alt="{{ $service->name }}" onerror="this.onerror=null;this.src='{{ asset('images/AdminLTELogo.png') }}';" />
                <h4>{{ $service->name }}</h4>
                <p>{{ $service->category }}</p>
                <p style="color:var(--muted); font-size:14px;">{{ $service->description ?? 'No description' }}</p>
                <p class="rating"><i class="fas fa-star"></i> {{ number_format($service->rating ?? 5, 1) }}</p>
                <p style="color:var(--gold); font-weight:800; font-size:18px;">₱{{ number_format($service->price ?? 0) }}</p>
                <form method="POST" action="{{ route('supplier.services.destroy', $service->service_id) }}" onsubmit="return confirm('Delete this service?')" style="margin-top:12px;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="display:inline-block; color:#ff8b8b; background:none; border:none; font-size:13px; cursor:pointer;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        @empty
            <div class="service-card" style="grid-column:1/-1; text-align:center; padding:60px 40px;">
                <i class="fas fa-box-open" style="font-size:48px;color:var(--gold);margin-bottom:16px;"></i>
                <h4>No services yet</h4>
                <p>Add your first service using the form above.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
