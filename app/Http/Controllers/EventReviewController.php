<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EventReviewController extends Controller
{
    public function createShareLink(int $eventId)
    {
        $event = DB::table('events')->where('event_id', $eventId)->where('user_id', Auth::id())->first();
        abort_unless((bool) $event, 404);

        $link = DB::table('event_review_links')->where('event_id', $eventId)->first();
        if (!$link) {
            $token = Str::random(48);
            DB::table('event_review_links')->insert([
                'event_id' => $eventId,
                'token' => $token,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $token = $link->token;
        }

        return response()->json(['success' => true, 'url' => route('reviews.public', $token)]);
    }

    public function publicReview(string $token)
    {
        $link = DB::table('event_review_links')->where('token', $token)->first();
        abort_unless((bool) $link, 404);

        $event = DB::table('events')->where('event_id', $link->event_id)->first();
        abort_unless((bool) $event, 404);

        return view('userui.public-review', [
            'event' => $event,
            'token' => $token,
            'services' => $this->reviewableServices($event, true),
        ]);
    }

    public function storePublicReview(Request $request, string $token)
    {
        $link = DB::table('event_review_links')->where('token', $token)->first();
        abort_unless((bool) $link, 404);

        $data = $request->validate([
            'service_column' => 'required|string|in:venue,catering,host,soundsnlights,photographer,clothes,coordinator',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
            'reviewer_name' => 'nullable|string|max:100',
        ]);
        $event = DB::table('events')->where('event_id', $link->event_id)->first();
        abort_unless((bool) $event, 404);
        $definition = $this->serviceFields()[$data['service_column']];
        $supplierName = trim((string) ($event->{$definition['field']} ?? ''));
        abort_unless($supplierName !== '', 422, 'That service is not selected for this event.');

        DB::table('event_supplier_reviews')->updateOrInsert(
            ['event_id' => $event->event_id, 'service_column' => $data['service_column']],
            [
                'supplier_name' => $supplierName,
                'rating' => $data['rating'],
                'review_text' => $data['review_text'] ?? null,
                'reviewer_name' => $data['reviewer_name'] ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->updateSupplierRating($supplierName);
        return response()->json(['success' => true, 'message' => 'Thanks for sharing your review.']);
    }

    // Fetch all active/selected suppliers for a specific event folder
    public function getReviewableServices($eventId)
    {
        $event = DB::table('events')
            ->where('event_id', $eventId)
            ->where('user_id', Auth::id())
            ->first();
        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found'], 404);
        }

        // Map your event table columns that contain supplier names
        $serviceFields = $this->serviceFields();

        $services = $this->reviewableServices($event);
        return response()->json(['success' => true, 'services' => $services]);
    }

    private function reviewableServices(object $event, bool $public = false): array
    {
        $services = [];
        foreach ($this->serviceFields() as $column => $definition) {
            $supplierName = $event->{$definition['field']} ?? null;
            
            // Only include if a supplier/name is actually chosen for this event
            if (!empty($supplierName)) {
                $existingReview = Schema::hasTable('event_supplier_reviews')
                    ? DB::table('event_supplier_reviews')
                        ->where('event_id', $event->event_id)
                        ->where('service_column', $column)
                        ->first()
                    : null;

                $supplier = Schema::hasTable('supplier_services')
                    ? DB::table('supplier_services')
                        ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim((string) $supplierName))])
                        ->first()
                    : null;

                $service = [
                    'service_column' => $column,
                    'category' => $definition['category'],
                    'name' => $supplierName,
                    'supplier_user_id' => $supplier?->user_id,
                    'rating' => $existingReview?->rating ?? 0,
                    'review_text' => $existingReview?->review_text ?? '',
                ];
                if ($public) {
                    unset($service['supplier_user_id']);
                }
                $services[] = $service;
            }
        }
        return $services;
    }

    // Save or update star rating and review text
    public function storeReview(Request $request, $eventId)
    {
        $data = $request->validate([
            'service_column' => 'required|string|in:venue,catering,host,soundsnlights,photographer,clothes,coordinator',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
        ]);

        $event = DB::table('events')
            ->where('event_id', $eventId)
            ->where('user_id', Auth::id())
            ->first();
        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found.'], 404);
        }

        abort_unless(Schema::hasTable('event_supplier_reviews'), 503, 'Review storage is unavailable.');

        $column = $data['service_column'];
        $supplierName = trim((string) ($event->{$this->serviceFields()[$column]['field']} ?? ''));
        abort_unless($supplierName !== '', 422, 'That service is not selected for this event.');

        DB::table('event_supplier_reviews')->updateOrInsert(
            [
                'event_id' => $eventId,
                'service_column' => $column,
            ],
            [
                'supplier_name' => $supplierName,
                'rating' => $data['rating'],
                'review_text' => $data['review_text'] ?? null,
                'updated_at' => now(),
            ]
        );

        $this->updateSupplierRating($supplierName);

        return response()->json(['success' => true, 'message' => 'Review saved successfully!']);
    }

    private function serviceFields(): array
    {
        return [
            'venue' => ['field' => 'venue_name', 'category' => 'Venue'],
            'catering' => ['field' => 'catering', 'category' => 'Catering'],
            'host' => ['field' => 'host', 'category' => 'Host'],
            'soundsnlights' => ['field' => 'soundsnlights', 'category' => 'Sounds & Lights'],
            'photographer' => ['field' => 'photographer', 'category' => 'Photographer'],
            'clothes' => ['field' => 'clothes', 'category' => 'Clothing'],
            'coordinator' => ['field' => 'coordinator', 'category' => 'Coordinator'],
        ];
    }

    private function updateSupplierRating(string $supplierName): void
    {
        if (!Schema::hasTable('supplier_services') || !Schema::hasColumn('supplier_services', 'rating')) {
            return;
        }
        $supplier = DB::table('supplier_services')->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($supplierName)])->first();
        if (!$supplier) {
            return;
        }
        $averageRating = DB::table('event_supplier_reviews')->where('supplier_name', $supplier->name)->avg('rating');
        DB::table('supplier_services')->where('service_id', $supplier->service_id)->update(['rating' => round((float) $averageRating, 2)]);
    }
}