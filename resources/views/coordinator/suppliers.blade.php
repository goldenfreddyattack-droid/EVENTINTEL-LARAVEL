@extends('coordinator.layout')

@section('title', 'My Suppliers')

@section('styles')
<style>
    .coord-container { max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; padding-top: 4px; }
    .coord-card-floating { background: #ffffff; border: 1px solid #ebebeb; border-radius: 20px; padding: 24px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03); }

    .coord-header-bar { border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; margin-bottom: 20px; }
    .coord-header-bar h2 { font-size: 22px; font-weight: 700; color: var(--text); margin: 0 0 2px; }
    .coord-header-bar p { color: var(--muted); font-size: 13px; margin: 0; }

    /* Supplier Cards Grid */
    .sup-cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 16px; }
    .sup-card-item { background: #fafafa; border: 1px solid #ebebeb; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; position: relative; transition: border-color 0.2s ease, background 0.2s ease; }
    .sup-card-item:hover { border-color: var(--border2); background: #ffffff; }
    .sup-card-item h3 { font-size: 15px; font-weight: 700; margin: 0 0 2px; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sup-cat-badge { font-size: 11px; font-weight: 600; color: var(--gold3); text-transform: uppercase; margin-bottom: 8px; display: block; }
    .sup-card-item p { font-size: 12px; color: var(--muted); margin: 2px 0; }
    .sup-price { color: var(--text); font-weight: 700; font-size: 15px; margin-top: 6px; }
    .sup-rating { color: var(--gold); font-size: 12px; font-weight: 600; margin-bottom: 12px; }

    .btn-msg-sm { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 12px; border-radius: 8px; background: linear-gradient(135deg, #fff1a8, #f3c547, #c99208); color: #111; font-size: 12px; font-weight: 700; text-decoration: none; margin-top: auto; }
</style>
@endsection

@section('content')
<div class="coord-container">
    <div class="coord-card-floating">
        <header class="coord-header-bar">
            <h2><i class="fas fa-users text-gold"></i> My Suppliers</h2>
            <p>Suppliers you collaborate with across your assigned events.</p>
        </header>

        <div class="sup-cards-grid">
            @forelse($suppliers as $supplier)
                <article class="sup-card-item">
                    <h3>{{ $supplier->business_name ?: $supplier->full_name }}</h3>
                    <span class="sup-cat-badge">{{ $supplier->category }}</span>
                    <p><i class="fas fa-tag text-gold"></i> {{ $supplier->name }}</p>
                    <p><i class="fas fa-map-marker-alt"></i> {{ $supplier->business_address }}</p>
                    <div class="sup-price">₱{{ number_format($supplier->price,2) }}</div>
                    <div class="sup-rating"><i class="fas fa-star"></i> {{ number_format($supplier->rating ?? 5,1) }}</div>
                    <a class="btn-msg-sm" href="{{ route('coordinator.messages') }}"><i class="fas fa-comments"></i> Message</a>
                </article>
            @empty
                <div style="grid-column: 1/-1; text-align: center; color: var(--muted); padding: 40px; font-size: 13px;">
                    <i class="fas fa-user-friends" style="font-size: 32px; color: #ccc; margin-bottom: 8px;"></i>
                    <h4>No suppliers found</h4>
                    <p>Suppliers assigned to your events will appear here.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection