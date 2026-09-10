@extends('coordinator.layout')

@section('title', 'Event Packages')

@section('styles')
<style>
    .coord-container { max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; padding-top: 4px; }
    .coord-card-floating { background: #ffffff; border: 1px solid #ebebeb; border-radius: 16px; padding: 20px 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.02); }
    
    .coord-header-bar { border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; margin-bottom: 16px; }
    .coord-header-bar h2 { font-size: 22px; font-weight: 700; color: var(--text); margin: 0 0 2px; }
    .coord-header-bar p { color: var(--muted); font-size: 13px; margin: 0; }

    .alert-success-box { background: rgba(46,159,77,0.08); border: 1px solid rgba(46,159,77,0.2); color: #2a8a43; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }

    /* Form Fields */
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 12px; }
    .form-grid input, .form-field-full textarea { width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #e0e0e0; background: #fafafa; color: var(--text); font-size: 13px; outline: none; transition: border-color 0.2s ease; }
    .form-grid input:focus, .form-field-full textarea:focus { border-color: var(--gold); background: #ffffff; }
    .form-field-full { margin-bottom: 12px; }
    .form-field-full textarea { min-height: 70px; resize: vertical; }

    .btn-submit { border: none; padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; }

    /* Package Cards Grid */
    .pkg-cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; margin-top: 10px; }
    .pkg-card-item { background: #fafafa; border: 1px solid #ebebeb; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; position: relative; transition: border-color 0.2s ease; }
    .pkg-card-item:hover { border-color: var(--border2); background: #ffffff; }
    .pkg-card-item h3 { font-size: 16px; font-weight: 700; margin: 0 0 4px; color: var(--text); }
    .pkg-card-item .price { color: var(--gold3); font-weight: 800; font-size: 16px; margin-bottom: 8px; }
    .pkg-card-item p { font-size: 12px; color: var(--muted); margin-bottom: 12px; line-height: 1.4; }
    .pkg-card-item ul { list-style: none; padding: 0; margin: 0 0 16px; font-size: 12px; color: var(--text); display: flex; flex-direction: column; gap: 4px; }
    .pkg-card-item ul li::before { content: "• "; color: var(--gold); }

    .delete-btn-sm { border: none; background: transparent; color: #d9534f; font-size: 12px; font-weight: 600; cursor: pointer; margin-top: auto; align-self: flex-start; padding: 0; }
</style>
@endsection

@section('content')
<div class="coord-container">
    
    {{-- FLOATING PANEL 1: Form & Header --}}
    <div class="coord-card-floating">
        <header class="coord-header-bar">
            <h2>Event Packages</h2>
            <p>Manage the packages clients can book for your coordination services.</p>
        </header>

        @if(session('success'))
            <div class="alert-success-box"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('coordinator.packages.store') }}">
            @csrf
            <input type="hidden" name="package_id" id="package_id">
            
            <div class="form-grid">
                <input name="name" placeholder="Package name *" required>
                <input name="price" type="number" min="0" step=".01" placeholder="Price (₱) *" required>
            </div>
            
            <div class="form-field-full">
                <textarea name="description" placeholder="Short description"></textarea>
            </div>
            
            <div class="form-field-full">
                <textarea name="inclusions" placeholder="Inclusions, one per line (or separated by |)"></textarea>
            </div>

            <div style="margin-bottom: 14px; font-size: 13px;">
                <label style="cursor:pointer; font-weight: 600; color: var(--text);"><input type="checkbox" name="is_featured" style="accent-color: var(--gold);"> Featured package</label>
            </div>

            <button class="btn-submit" type="submit"><i class="fas fa-plus"></i> Save Package</button>
        </form>
    </div>

    {{-- FLOATING PANEL 2: Package List Grid --}}
    <div class="coord-card-floating">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0 0 14px; padding-bottom: 8px; border-bottom: 1px solid #f4f4f4;">Your Packages ({{ count($packages) }})</h3>
        
        <div class="pkg-cards-grid">
            @forelse($packages as $package)
                <article class="pkg-card-item">
                    <h3>{{ $package->name }}</h3>
                    <div class="price">₱{{ number_format($package->price,2) }}</div>
                    <p>{{ $package->description }}</p>
                    <ul>
                        @foreach(explode('|',$package->inclusions ?? '') as $item) 
                            @if(trim($item))
                                <li>{{ trim($item) }}</li>
                            @endif 
                        @endforeach
                    </ul>
                    
                    <form method="POST" action="{{ route('coordinator.packages.delete',$package->package_id) }}" onsubmit="return confirm('Delete this package?')">
                        @csrf @method('DELETE')
                        <button class="delete-btn-sm" type="submit"><i class="fas fa-trash"></i> Delete Package</button>
                    </form>
                </article>
            @empty
                <div style="grid-column: 1/-1; text-align: center; color: var(--muted); padding: 30px; font-size: 13px;">
                    No packages added yet.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection