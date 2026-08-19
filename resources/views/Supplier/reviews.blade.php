@extends('supplier.layout')

@section('title', 'Customer Reviews')

@section('content')
<section>
    <h2>Customer Reviews</h2>
    <div class="reviews-page">
        <div class="review-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:12px;">
                <div>
                    <h3>Jane D.</h3>
                    <p style="font-size:13px; color:var(--muted); margin:4px 0;">Wedding Venue • Wedding Event</p>
                </div>
                <span class="stars">★★★★★</span>
            </div>
            <p>Very professional and fast response. The service was excellent.</p>
        </div>

        <div class="review-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:12px;">
                <div>
                    <h3>Mark T.</h3>
                    <p style="font-size:13px; color:var(--muted); margin:4px 0;">Photographer • Birthday Party</p>
                </div>
                <span class="stars">★★★★☆</span>
            </div>
            <p>Great quality and timely communication.</p>
        </div>
    </div>
</section>
@endsection
