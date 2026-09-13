<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RecommendationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::user()->role === 'client', 403, 'Client access only.');
            return $next($request);
        });
    }

    public function index()
    {
        $userEvents = DB::table('events')
            ->where('user_id', Auth::id())
            ->orderByDesc('event_date')
            ->get(['event_id', 'title', 'event_type', 'event_date', 'budget', 'guest_count']);

        $fallbackEvents = $userEvents->isEmpty()
            ? DB::table('events')
                ->orderByDesc('event_date')
                ->limit(10)
                ->get(['event_id', 'title', 'event_type', 'event_date', 'budget', 'guest_count'])
            : collect();

        $bookmarkedServices = app(\App\Http\Controllers\ServiceCatalogController::class)->bookmarkedServices();

        return view('userui.recommendation', compact('userEvents', 'fallbackEvents', 'bookmarkedServices'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'event' => ['nullable', 'string', 'max:100'],
            'budget' => ['required', 'numeric', 'min:1'],
            'pax' => ['required', 'integer', 'min:1'],
            'services' => ['nullable', 'array'],
            'services.*' => ['nullable', 'string', 'max:100'],
            'regenerate' => ['nullable', 'boolean'],
        ]);

        $event = trim($data['event'] ?? '') ?: 'Event';
        $budget = (float) $data['budget'];
        $pax = (int) $data['pax'];
        $this->validateRecommendationInputs($event, $budget, $pax);

        $services = collect($data['services'] ?? [])
            ->map(fn ($service) => $this->normalizeServiceCategory($service))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $bookmarkedServices = app(\App\Http\Controllers\ServiceCatalogController::class)->bookmarkedServices();
        if (empty($services) && $bookmarkedServices->isNotEmpty()) {
            $services = ['Venue'];
        }

        $html = '<div class="recommendation-response">';
        $html .= '<p><strong>Event Type:</strong> ' . e($event) . ' | <strong>Guests:</strong> ' . $pax . ' | <strong>Budget:</strong> PHP ' . number_format($budget, 2) . '</p>';

        if ($bookmarkedServices->isNotEmpty()) {
            $bookmarkNames = $bookmarkedServices->take(3)->pluck('name')->filter()->map(fn ($name) => e($name))->implode(', ');
            $html .= '<div class="recommendation-service-note"><strong>Bookmarked place picks:</strong> ' . $bookmarkNames . '</div>';
        }
        $html .= $this->timelineHtml($event);
        $html .= $this->budgetHtml($budget);
        $html .= $this->serviceHtml($services, $budget, $pax);

        $tip = $this->aiTip($event, $pax, $budget, $data['regenerate'] ?? false, $services);
        if ($tip) {
            $html .= '<h4 class="recommendation-section-title">AI Planning Tips</h4><p class="recommendation-ai-tip">' . e($tip) . '</p>';
        }

        return response()->json(['html' => $html . '</div>']);
    }

    public function useRecommendation(Request $request)
    {
        $data = $request->validate([
            'event_type' => ['nullable', 'string', 'max:100'],
            'guest_count' => ['nullable', 'integer', 'min:1'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'services' => ['nullable', 'array'],
            'services.*' => ['nullable', 'string', 'max:100'],
        ]);

        $eventType = trim((string) ($data['event_type'] ?? 'Event')) ?: 'Event';
        $guestCount = (int) ($data['guest_count'] ?? 1);
        $budget = (float) ($data['budget'] ?? 0);
        $services = collect($data['services'] ?? [])
            ->map(fn ($service) => $this->normalizeServiceCategory($service))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $messageBody = 'A client selected a ' . e($eventType) . ' recommendation for ' . $guestCount . ' guests with a PHP ' . number_format($budget, 2) . ' budget. Please review the recommendation and confirm availability for the requested services.';

        $eventId = DB::table('events')->insertGetId([
            'user_id' => Auth::id(),
            'title' => $eventType . ' Recommendation',
            'event_type' => $eventType,
            'theme' => 'Recommendation',
            'budget' => $budget,
            'event_date' => now()->addDays(30)->toDateString(),
            'event_time' => '09:00:00',
            'event_end_time' => '18:00:00',
            'guest_count' => $guestCount,
            'venue_name' => '',
            'status' => 'planning',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'created_at' => now(),
        ]);

        $matchedSuppliers = $this->matchedSuppliersForServices($services);
        $count = 0;
        foreach ($matchedSuppliers as $supplier) {
            $supplierId = (int) ($supplier->user_id ?? 0);
            if ($supplierId <= 0) {
                continue;
            }

            $this->sendSupplierMessage((int) $eventId, $supplierId, $messageBody);
            $count++;
        }

        $prefill = ['event_type' => $eventType, 'budget' => $budget, 'services' => $services];
        session(['event_recommendation_prefill' => json_encode($prefill)]);

        $redirectUrl = route('events.create') . '?' . http_build_query([
            'event_type' => $eventType,
            'budget' => (string) $budget,
            'services' => implode(',', $services),
            'from' => 'recommendation',
        ]);

        return response()->json([
            'success' => true,
            'supplier_count' => $count,
            'event_id' => $eventId,
            'redirect_url' => $redirectUrl,
        ]);
    }

    private function validateRecommendationInputs(string $event, float $budget, int $pax): void
    {
        $eventLower = strtolower(trim($event));
        $minBudget = match (true) {
            str_contains($eventLower, 'wedding') => 30000,
            str_contains($eventLower, 'birthday') => 7000,
            str_contains($eventLower, 'debut') => 15000,
            str_contains($eventLower, 'anniversary') => 10000,
            str_contains($eventLower, 'christening') => 8000,
            str_contains($eventLower, 'reunion') => 6000,
            str_contains($eventLower, 'gender reveal') => 8000,
            default => 5000,
        };

        $minGuests = match (true) {
            str_contains($eventLower, 'wedding') => 20,
            str_contains($eventLower, 'birthday') => 10,
            str_contains($eventLower, 'debut') => 20,
            str_contains($eventLower, 'anniversary') => 10,
            str_contains($eventLower, 'christening') => 10,
            str_contains($eventLower, 'reunion') => 10,
            str_contains($eventLower, 'gender reveal') => 10,
            default => 5,
        };

        if ($budget < $minBudget) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'budget' => 'Budget is too low for a ' . trim($event) . ' event. Minimum recommended budget is PHP ' . number_format($minBudget, 2) . '.',
            ]);
        }

        if ($pax < $minGuests) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'guest_count' => 'Guest count is too low for a ' . trim($event) . ' event. Minimum recommended guest count is ' . $minGuests . '.',
            ]);
        }
    }

    private function matchedSuppliersForServices(array $services): \Illuminate\Support\Collection
    {
        if ($services === []) {
            return collect();
        }

        $categories = collect($services)
            ->map(fn ($service) => $this->serviceSearchTerms($service))
            ->flatten()
            ->map(fn ($term) => strtolower(trim((string) $term)))
            ->filter()
            ->unique()
            ->values();

        if ($categories->isEmpty()) {
            return collect();
        }

        $query = DB::table('supplier_services as ss')
            ->join('users as u', 'u.user_id', '=', 'ss.user_id')
            ->whereNotNull('ss.user_id')
            ->select('ss.*', 'u.full_name as supplier_name', 'u.role as supplier_role');

        $query->where(function ($where) use ($categories) {
            foreach ($categories as $category) {
                $where->orWhereRaw('LOWER(TRIM(ss.category)) = ?', [$category])
                    ->orWhereRaw('LOWER(TRIM(ss.name)) LIKE ?', ['%' . $category . '%']);
            }
        });

        return $query->orderByDesc('ss.service_id')->get();
    }

    private function serviceSearchTerms(string $service): array
    {
        return match ($this->normalizeServiceCategory($service)) {
            'Venue' => ['venue'],
            'Catering' => ['catering', 'food'],
            'Host' => ['host', 'mc'],
            'Sounds & Lights' => ['sounds', 'lights', 'sounds and lights'],
            'Photographer' => ['photographer', 'photo', 'videographer'],
            'Clothing' => ['clothing', 'attire', 'styling'],
            'Decorations' => ['decor', 'decorations', 'styling'],
            default => [strtolower(trim($service))],
        };
    }

    private function sendSupplierMessage(int $eventId, int $supplierId, string $message): void
    {
        $firebase = app(\App\Services\FirebaseService::class);
        $firebase->saveMessage(
            $eventId,
            (int) Auth::id(),
            $supplierId,
            $message,
            Auth::user()->full_name ?? 'Client'
        );
    }

    private function normalizeServiceCategory(string $service): ?string
    {
        $normalized = strtolower(trim($service));

        return [
            'venue' => 'Venue', 'venue space' => 'Venue', 'venue rental' => 'Venue',
            'catering' => 'Catering', 'catering/food' => 'Catering', 'food' => 'Catering', 'food service' => 'Catering',
            'host' => 'Host', 'host/mc' => 'Host', 'mc' => 'Host', 'master of ceremonies' => 'Host',
            'sounds' => 'Sounds & Lights', 'sounds & lights' => 'Sounds & Lights', 'sounds and lights' => 'Sounds & Lights', 'lights' => 'Sounds & Lights',
            'photographer' => 'Photographer', 'photo' => 'Photographer', 'videographer' => 'Photographer',
            'clothing' => 'Clothing', 'attire' => 'Clothing', 'clothing/attire' => 'Clothing', 'styling' => 'Clothing',
            'decorations' => 'Decorations', 'decor' => 'Decorations', 'decorations and styling' => 'Decorations',
        ][$normalized] ?? (trim($service) ?: null);
    }

    private function supplierCategoryMatches(string $service): array
    {
        return match ($this->normalizeServiceCategory($service)) {
            'Venue' => ['venue'],
            'Catering' => ['catering', 'catering/food', 'food'],
            'Host' => ['host', 'mc'],
            'Sounds & Lights' => ['sounds & lights', 'sounds and lights', 'sounds_lights', 'lights'],
            'Photographer' => ['photographer', 'photo', 'videographer'],
            'Clothing' => ['clothing', 'attire', 'clothes', 'styling'],
            'Decorations' => ['decorations', 'decor', 'styling'],
            default => [strtolower(trim($service))],
        };
    }

    private function timelineHtml(string $event): string
    {
        $flows = [
            'wedding' => [['08:00 AM', 'Guest Arrival & Registration', 'Venue preparation, coat check'], ['09:00 AM', 'Ceremony Starts', 'Bride entrance, vows, rings'], ['10:00 AM', 'Reception & Cocktail Hour', 'Photos, mingling, appetizers'], ['11:30 AM', 'Grand Entrance & First Dance', 'Music cues, lighting effects'], ['12:00 PM', 'Lunch Service', 'Multi-course meal service'], ['01:00 PM', 'Toasts & Speeches', 'Best man, bridesmaids, parents'], ['02:00 PM', 'Cake Cutting & Entertainment', 'Music, dancing, photo booth'], ['04:00 PM', 'Evening Activities & Dessert', 'DJ transitions, special dances'], ['06:00 PM', 'Farewell & Send-off', 'Guest departure arrangements']],
            'birthday' => [['02:00 PM', 'Guest Arrival', 'Welcome drinks, games setup'], ['02:30 PM', 'Icebreaker Activities & Games', 'Team games, music playing'], ['03:30 PM', 'Snack Break', 'Light appetizers, drinks'], ['04:00 PM', 'Main Activities & Entertainment', 'DJ performance, dancing'], ['05:00 PM', 'Dinner Service', 'Main course buffet or plated meal'], ['06:00 PM', 'Birthday Cake & Singing', 'Special lighting, candles'], ['06:30 PM', 'Gifts & Photos', 'Gift opening, group photos'], ['07:30 PM', 'Dessert & Closing Activities', 'Dessert service, farewells']],
            'corporate' => [['08:00 AM', 'Registration & Breakfast', 'Coffee, pastries, name badges'], ['09:00 AM', 'Opening Remarks', 'CEO/Director presentation'], ['09:30 AM', 'Keynote Speech', 'Main speaker presentation'], ['10:30 AM', 'Break & Networking', 'Refreshments, mingling'], ['11:00 AM', 'Breakout Sessions', 'Panel discussions, workshops'], ['12:00 PM', 'Lunch', 'Catered meal, table seating'], ['01:00 PM', 'Awards & Recognition', 'Recognition ceremony'], ['02:00 PM', 'Networking & Team Building', 'Games, activities, mingling'], ['04:00 PM', 'Closing Remarks & Departure', 'Thank you speech, farewell']],
        ];
        $flow = collect($flows)->first(fn ($timeline, $type) => str_contains(strtolower($event), $type)) ?? [['09:00 AM', 'Event Start & Guest Arrival', 'Registration, welcome drinks'], ['10:00 AM', 'Opening Program', 'Opening remarks, introductions'], ['11:00 AM', 'Main Activities', 'Core event activities'], ['12:00 PM', 'Lunch Service', 'Food service to guests'], ['01:00 PM', 'Afternoon Program', 'Continued activities, entertainment'], ['03:00 PM', 'Snack & Break', 'Refreshment time'], ['04:00 PM', 'Closing Program', 'Final remarks, group photos'], ['05:00 PM', 'Farewell & Departure', 'Thank you, guest exit']];

        $html = '<h4 class="recommendation-section-title">Recommended Event Timeline</h4>';
        foreach ($flow as [$time, $activity, $prep]) {
            $html .= '<div class="recommendation-timeline-item"><strong class="recommendation-timeline-time">' . e($time) . '</strong><span class="recommendation-timeline-event"><strong>' . e($activity) . '</strong><br><small>' . e($prep) . '</small></span></div>';
        }
        return $html;
    }

    private function budgetHtml(float $budget): string
    {
        $allocation = ['Catering' => .35, 'Venue' => .30, 'Photographer' => .10, 'Host' => .08, 'Sounds & Lights' => .08, 'Clothing' => .05, 'Decorations' => .04];
        $html = '<h4 class="recommendation-section-title">Budget Allocation</h4><div class="recommendation-budget-grid">';
        foreach ($allocation as $category => $share) {
            $percentage = (int) round($share * 100);
            $html .= '<div class="recommendation-budget-card"><strong>' . e($category) . '</strong><span>' . $percentage . '%</span><div class="recommendation-budget-bar"><i style="width:' . $percentage . '%"></i></div><b>PHP ' . number_format(round($budget * $share)) . '</b></div>';
        }
        return $html . '</div>';
    }

    private function serviceHtml(array $services, float $budget, int $pax): string
    {
        $shares = ['Venue' => .30, 'Catering' => .35, 'Host' => .08, 'Photographer' => .10, 'Sounds & Lights' => .08, 'Clothing' => .05, 'Decorations' => .04];
        $html = '<h4 class="recommendation-section-title">Service Recommendations</h4>';
        foreach ($services as $service) {
            if (!isset($shares[$service])) continue;
            $note = $service === 'Venue' ? 'Choose a venue that fits your guest count.' : 'Keep this service within the planned allocation.';
            $html .= '<div class="recommendation-service-note"><strong>' . e($service) . ':</strong> PHP ' . number_format(round($budget * $shares[$service])) . ' (' . (int) round($shares[$service] * 100) . '%) - ' . e($note) . '</div>';
        }

        $supplierCategories = collect($services)
            ->flatMap(fn ($service) => $this->supplierCategoryMatches((string) $service))
            ->map(fn ($category) => strtolower(trim((string) $category)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $supplierQuery = DB::table('supplier_services')->whereNotNull('price')->where('price', '>', 0);
        if ($supplierCategories !== []) {
            $supplierQuery->where(function ($query) use ($supplierCategories) {
                foreach ($supplierCategories as $category) {
                    $query->orWhereRaw('LOWER(TRIM(category)) = ?', [$category]);
                }
            });
        }

        $suppliers = $supplierQuery->orderBy('price')->orderByDesc('rating')->limit(6)->get(['name', 'category', 'price', 'address', 'rating']);
        if ($suppliers->isNotEmpty()) {
            $html .= '<div class="recommendation-service-note"><strong>Best supplier matches for your budget:</strong></div>';
            foreach ($suppliers as $index => $supplier) {
                $badge = $index === 0 ? ' <span class="recommendation-best-fit">Best fit</span>' : '';
                $html .= '<div class="recommendation-service-note"><strong>' . e($supplier->name) . '</strong>' . $badge . '<br><small>' . e($supplier->category) . ' - PHP ' . number_format((float) $supplier->price, 2) . ' - Rating ' . number_format((float) $supplier->rating, 1) . '<br>' . e($supplier->address ?: 'Address available on supplier profile') . '</small></div>';
            }
        } elseif (!$services) {
            $html .= '<div class="recommendation-service-note">Select services to get budget allocation recommendations.</div>';
        } else {
            $html .= '<div class="recommendation-service-note">No matching supplier records were found for this service selection.</div>';
        }
        return $html;
    }

    private function localTip(string $event, int $pax, float $budget, array $services): string
    {
        $selected = $services !== [] ? implode(', ', $services) : 'venue, catering, and décor';
        return "For a {$event} with {$pax} guests and a PHP " . number_format($budget) . " budget, prioritize your top essentials: {$selected}. Keep a 30% buffer for vendor changes, guest count adjustments, and last-minute add-ons so the event still feels premium without overspending.";
    }

    private function aiTip(string $event, int $pax, float $budget, bool $regenerate = false, array $services = []): ?string
    {
        if (empty(config('services.openai.key')) && ! $regenerate) {
            return $this->localTip($event, $pax, $budget, $services);
        }

        if (empty(config('services.openai.key'))) {
            return $this->localTip($event, $pax, $budget, $services);
        }

        $response = Http::withToken(config('services.openai.key'))->timeout(10)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [['role' => 'user', 'content' => "Create a brief 3-4 sentence creative event planning tip for a {$event} with {$pax} guests and PHP " . number_format($budget) . ' budget. Include one unique suggestion.']],
        ]);

        if ($response->successful()) {
            $tip = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
            return $tip !== '' ? $tip : $this->localTip($event, $pax, $budget, $services);
        }

        return $this->localTip($event, $pax, $budget, $services);
    }
}
