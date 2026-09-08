@extends('supplier.layout')

@section('title', 'My Services')

@section('content')
<style>
    /* ===== Services Layout Wrappers ===== */
    .services-wrapper { display: flex; flex-direction: column; gap: 20px; padding-top: 4px; }
    .services-card-floating { background: #ffffff; border: 1px solid #ebebeb; border-radius: 16px; padding: 20px 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.02); transition: box-shadow 0.2s ease; }
    .services-card-floating:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.04); }

    /* ===== Top Header & Navigation Bar ===== */
    .services-header-bar { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .services-header-bar h2 { font-size: 22px; font-weight: 700; color: var(--text); margin: 0 0 2px; }
    .services-header-bar p { color: var(--muted); font-size: 13px; margin: 0; }
    .add-service-btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; font-size: 13px; font-weight: 600; text-decoration: none; border-radius: 8px; border: none; cursor: pointer; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; }

    /* ===== Alert Messaging ===== */
    .alert-success-box { background: rgba(46,159,77,0.08); border: 1px solid rgba(46,159,77,0.2); color: #2a8a43; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-top: 14px; display: flex; align-items: center; gap: 8px; }

    /* ===== Form Layout ===== */
    .form-title-group { margin-bottom: 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #f4f4f4; padding-bottom: 10px; }
    .form-title-group h3 { font-size: 16px; font-weight: 700; color: var(--text); margin: 0; }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px 16px; }
    .form-field { display: flex; flex-direction: column; gap: 4px; }
    .form-field.full-width { grid-column: 1 / -1; }
    .form-field label { font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.3px; }
    .form-field input, .form-field select, .form-field textarea { width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #e0e0e0; background: #fafafa; color: var(--text); font-size: 13px; outline: none; transition: border-color 0.2s ease, background 0.2s ease; }
    .form-field input:focus, .form-field select:focus, .form-field textarea:focus { border-color: var(--gold); background: #ffffff; }

    /* Custom File Input Styling */
    .service-file-input { height: 40px; padding: 4px 6px !important; font-size: 12px; }
    .service-file-input::file-selector-button { height: 30px; border: 0; border-radius: 6px; padding: 4px 10px; margin-right: 8px; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; font-size: 11px; font-weight: 700; cursor: pointer; transition: filter 0.2s ease; }
    .service-file-input::file-selector-button:hover { filter: brightness(0.95); }

    .form-submit-row { display: flex; justify-content: flex-end; margin-top: 14px; }

    /* ===== Services Display Grid ===== */
    .services-grid-wrapper { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 16px; }
    .service-card-item { background: #ffffff; border: 1px solid #ebebeb; border-radius: 14px; overflow: hidden; transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease; position: relative; display: flex; flex-direction: column; }
    .service-card-item:hover { transform: translateY(-3px); border-color: var(--border); box-shadow: 0 8px 20px rgba(0,0,0,0.04); }
    
    .service-card-img-wrapper { position: relative; width: 100%; height: 140px; background: #f8f8f8; }
    .service-card-img { width: 100%; height: 100%; object-fit: cover; display: block; }
    
    .service-card-body { padding: 14px; display: flex; flex-direction: column; flex-grow: 1; gap: 4px; }
    .service-card-body h4 { font-size: 15px; font-weight: 600; color: var(--text); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .service-category { font-size: 12px; color: var(--muted); font-weight: 500; }
    .service-desc { color: var(--muted); font-size: 12px; line-height: 1.4; margin: 4px 0 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    
    .service-card-footer { display: flex; align-items: center; justify-content: space-between; padding-top: 8px; border-top: 1px solid #f4f4f4; margin-top: auto; }
    .service-rating { color: var(--gold); font-weight: 600; font-size: 12px; }
    .service-price { color: var(--gold3); font-weight: 700; font-size: 15px; }

    /* Floating Trash Button Pill */
    .delete-action-form { position: absolute; top: 8px; right: 8px; z-index: 2; }
    .delete-btn-pill { width: 28px; height: 28px; border-radius: 50%; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(4px); border: none; color: #d9534f; display: flex; align-items: center; justify-content: center; font-size: 11px; cursor: pointer; transition: background 0.2s ease, transform 0.2s ease; }
    .delete-btn-pill:hover { background: #ffffff; color: #c9302c; transform: scale(1.06); }

    /* Empty State Container */
    .services-empty-state { grid-column: 1 / -1; background: #fafafa; border: 1px dashed #e0e0e0; border-radius: 14px; padding: 36px 20px; text-align: center; }
    .empty-icon { font-size: 28px; color: #ccc; margin-bottom: 8px; }
    .services-empty-state h4 { font-size: 15px; font-weight: 600; margin-bottom: 2px; color: var(--text); }
    .services-empty-state p { color: var(--muted); font-size: 13px; margin: 0; }
</style>

<div class="services-wrapper">

    {{-- FLOATING PANEL 1: Page Header & Quick Actions --}}
    <div class="services-card-floating">
        <header class="services-header-bar">
            <div>
                <h2>My Services</h2>
                <p>Manage offerings and publish new services for your clients.</p>
            </div>
            <a href="#add-service-form" class="add-service-btn">
                <i class="fas fa-plus"></i> Add Service
            </a>
        </header>

        @if(session('success'))
            <div class="alert-success-box">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
    </div>

    {{-- FLOATING PANEL 2: Active Services Grid --}}
    <div class="services-card-floating">
        <div class="form-title-group">
            <h3>Offered Services ({{ count($services) }})</h3>
        </div>

        <div class="services-grid-wrapper">
            @forelse($services as $service)
                <div class="service-card-item">
                    {{-- Floating Delete Action --}}
                    <form method="POST" action="{{ route('supplier.services.destroy', $service->service_id) }}" onsubmit="return confirm('Delete this service?')" class="delete-action-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-btn-pill" title="Delete Service">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>

                    <div class="service-card-img-wrapper">
                        <img 
                            class="service-card-img" 
                            src="{{ $service->service_pic ? route('supplier.services.image', $service->service_id) : asset('images/AdminLTELogo.png') }}" 
                            alt="{{ $service->name }}" 
                            onerror="this.onerror=null;this.src='{{ asset('images/AdminLTELogo.png') }}';" 
                            loading="lazy"
                        />
                    </div>

                    <div class="service-card-body">
                        <h4>{{ $service->name }}</h4>
                        <span class="service-category">{{ $service->category }}{{ $service->style ? ' • ' . $service->style : '' }}</span>
                        <p class="service-desc">{{ $service->description ?? 'No description provided.' }}</p>

                        <div class="service-card-footer">
                            <span class="service-rating"><i class="fas fa-star"></i> {{ number_format($service->rating ?? 5, 1) }}</span>
                            <span class="service-price">₱{{ number_format($service->price ?? 0) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="services-empty-state">
                    <i class="fas fa-box-open empty-icon"></i>
                    <h4>No services yet</h4>
                    <p>Get started by adding your first service below.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- FLOATING PANEL 3: Add New Service Form --}}
    <div class="services-card-floating" id="add-service-form">
        <div class="form-title-group">
            <h3><i class="fas fa-plus-circle text-gold"></i> Add New Service</h3>
        </div>

        <form method="POST" action="{{ route('supplier.services.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
                <div class="form-field">
                    <label>Service Name *</label>
                    <input type="text" name="name" placeholder="e.g. Grand Ballroom Rental" required>
                </div>
                <div class="form-field">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="Venue">Venue</option>
                        <option value="Catering">Catering</option>
                        <option value="Clothing">Clothing</option>
                        <option value="Host">Host</option>
                        <option value="Photographer">Photographer</option>
                        <option value="Sounds & Lights">Sounds & Lights</option>
                    </select>
                </div>
                <div class="form-field">
                    <label>Style / Cuisine</label>
                    <input type="text" name="style" placeholder="e.g. Modern, Italian">
                </div>
                <div class="form-field">
                    <label>Price (₱)</label>
                    <input type="number" name="price" placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="form-field">
                    <label>Location</label>
                    <input type="text" name="address" placeholder="City or Full Address">
                </div>
                <div class="form-field">
                    <label>Latitude</label>
                    <input type="text" name="latitude" placeholder="Optional coordinate">
                </div>
                <div class="form-field">
                    <label>Cover Photo</label>
                    <input class="service-file-input" type="file" name="service_pic" accept="image/jpeg,image/png,image/webp">
                </div>
                <div class="form-field full-width">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Briefly describe what's included in this service..."></textarea>
                </div>
            </div>

            <div class="form-submit-row">
                <button type="submit" class="add-service-btn">
                    <i class="fas fa-plus"></i> Add Service
                </button>
            </div>
        </form>
    </div>

</div>
@endsection