<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class EventController extends Controller
{
    private const MAX_PLANNING_EVENTS = 3;

    private function hasReachedPlanningEventLimit(int $userId): bool
    {
        $planningCount = DB::table('events')
            ->where('user_id', $userId)
            ->where('status', 'planning')
            ->count();

        return $planningCount >= self::MAX_PLANNING_EVENTS;
    }

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(Request $request)
    {
        abort_unless(Auth::user()->role === 'client', 403, 'Client access only.');
        $availableServices = Schema::hasTable('supplier_services')
            ? DB::table('supplier_services')->select('name', 'category', 'price', 'capacity', 'address')->whereNotNull('name')->orderBy('category')->orderBy('price')->get()
            : collect();

        $planningCount = DB::table('events')
            ->where('user_id', Auth::id())
            ->where('status', 'planning')
            ->count();

        return view('userui.create-event', [
            'eventTypes' => ['Birthday', 'Debut', 'Wedding', 'Anniversary', 'Christening', 'Gender Reveal', 'Reunion', 'Others'],
            'prefill' => [
                'event_type' => $request->input('event_type'),
                'budget' => $request->input('budget'),
                'services' => array_filter(array_map('trim', explode(',', (string) $request->input('services')))),
            ],
            'availableServices' => $availableServices,
            'planningCount' => $planningCount,
            'planningLimitReached' => $planningCount >= 3,
        ]);
    }

    public function venueAvailability(Request $request)
    {
        abort_unless(Auth::user()->role === 'client', 403, 'Client access only.');

        $venueName = trim((string) $request->query('venue'));
        abort_unless($venueName !== '', 422, 'A venue is required.');

        $monthStart = now()->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $normalizedVenueName = strtolower($venueName);
        $bookedDates = DB::table('events')
            ->whereRaw('LOWER(TRIM(venue_name)) = ?', [$normalizedVenueName])
            ->whereBetween('event_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->whereNotIn('status', ['cancelled', 'Cancelled'])
            ->pluck('event_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip();

        $dates = collect(range(0, $monthStart->daysInMonth - 1))->map(function (int $offset) use ($monthStart, $bookedDates) {
            $date = $monthStart->copy()->addDays($offset);
            $booked = $bookedDates->has($date->toDateString());

            return ['date' => $date->toDateString(), 'label' => $date->format('M j'), 'available' => ! $booked];
        });

        $venue = DB::table('supplier_services')
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedVenueName])
            ->first();
        $addonMap = [
            'catering' => 'catering',
            'clothing' => 'clothes',
            'clothes' => 'clothes',
            'styling' => 'clothes',
            'sounds & lights' => 'sounds_lights',
            'sounds and lights' => 'sounds_lights',
            'sounds_lights' => 'sounds_lights',
            'host' => 'host',
            'mc' => 'host',
            'photographer' => 'photographer',
        ];
        $addonPriceColumns = [
            'catering' => 'venueaddons_price1',
            'clothes' => 'venueaddons_price2',
            'host' => 'venueaddons_price3',
            'photographer' => 'venueaddons_price4',
            'sounds_lights' => 'venueaddons_price5',
        ];
        $addons = collect();
        if ($venue && Schema::hasColumn('supplier_services', 'venue_add_ons')) {
            $addons = collect(json_decode($venue->venue_add_ons ?? '[]', true) ?: [])
                ->values()
                ->map(function (string $addon) use ($addonMap, $addonPriceColumns, $venue) {
                    $key = $addonMap[strtolower(trim($addon))] ?? null;
                    if (!$key) {
                        return null;
                    }

                    $priceColumn = $addonPriceColumns[$key];
                    return [
                        'key' => $key,
                        'label' => $key === 'sounds_lights' ? 'Sounds & Lights' : ucfirst($key),
                        'price' => (float) ($venue->{$priceColumn} ?? 0),
                    ];
                })
                ->filter()
                ->unique('key')
                ->values();
        }

        return response()->json(['dates' => $dates, 'addons' => $addons]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->role === 'client', 403, 'Client access only.');

        if ($this->hasReachedPlanningEventLimit(Auth::id())) {
            return back()->withInput()->withErrors([
                'event_name' => 'You already have three planning events. Finish one before creating another event.',
            ]);
        }

        $data = $request->validate([
            'event_name' => ['required', 'string', 'max:150'],
            'event_type' => ['required', 'string', 'max:100'],
            'other_event_type' => ['nullable', 'required_if:event_type,Others', 'string', 'max:100'],
            'event_date' => ['required', 'date'],
            'event_time' => ['required', 'date_format:H:i'],
            'event_end_time' => ['nullable', 'date_format:H:i'],
            'guest_count' => ['required', 'integer', 'min:1'],
            'event_budget' => ['nullable', 'numeric', 'min:0'],
            'theme' => ['required', 'string', 'max:120'],
            'venue_name' => ['required', 'string', 'max:150'],
            'clothes' => ['nullable', 'string', 'max:255'],
            'catering' => ['nullable', 'string', 'max:255'],
            'host' => ['nullable', 'string', 'max:255'],
            'photographer' => ['nullable', 'string', 'max:255'],
            'sounds_lights' => ['nullable', 'string', 'max:255'],
            'services' => ['array'],
            'services.*' => ['string', 'in:venue,clothes,catering,host,sounds_lights,photographer'],
        ]);

        $eventType = $data['event_type'] === 'Others'
            ? trim($data['other_event_type'])
            : $data['event_type'];
        $endTime = $data['event_end_time'] ?: $data['event_time'];

        if (Carbon::parse($data['event_date'] . ' ' . $endTime)->lessThanOrEqualTo(Carbon::parse($data['event_date'] . ' ' . $data['event_time']))) {
            return back()->withInput()->withErrors(['event_end_time' => 'End time must be after the start time.']);
        }

        $venue = DB::table('supplier_services')->where('name', $data['venue_name'])->first();
        $capacity = $venue?->capacity ?: 200;
        if ($data['guest_count'] > $capacity) {
            return back()->withInput()->withErrors(['guest_count' => "The selected venue can accommodate up to {$capacity} guests."]); 
        }

        $overlap = DB::table('events')
            ->where('venue_name', $data['venue_name'])
            ->where('event_date', $data['event_date'])
            ->whereNotIn('status', ['cancelled', 'Cancelled'])
            ->where('event_time', '<', $endTime)
            ->where('event_end_time', '>', $data['event_time'])
            ->exists();
        if ($overlap) {
            return back()->withInput()->withErrors(['venue_name' => 'The selected venue is not available at that date and time.']);
        }

        $services = array_values(array_unique($data['services'] ?? []));
        if (!in_array('venue', $services, true)) {
            $services[] = 'venue';
        }

        $eventId = DB::transaction(function () use ($data, $eventType, $endTime, $services) {
            $eventId = DB::table('events')->insertGetId([
                'user_id' => Auth::id(),
                'title' => $data['event_name'],
                'event_type' => $eventType,
                'theme' => $data['theme'] ?? null,
                'budget' => $data['event_budget'] ?? null,
                'event_date' => $data['event_date'],
                'event_time' => $data['event_time'],
                'event_end_time' => $endTime,
                'guest_count' => $data['guest_count'],
                'venue_name' => $data['venue_name'],
                'clothes' => $data['clothes'] ?? null,
                'catering' => $data['catering'] ?? null,
                'host' => $data['host'] ?? null,
                'photographer' => $data['photographer'] ?? null,
                'soundsnlights' => $data['sounds_lights'] ?? null,
                'coordinator_package' => '',
                'status' => 'planning',
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'created_at' => now(),
            ]);

            if (Schema::hasTable('event_services')) {
                foreach ($services as $service) {
                    DB::table('event_services')->insert(['event_id' => $eventId, 'service_name' => $service, 'created_at' => now()]);
                }
            }
            if (Schema::hasTable('invitations')) {
                DB::table('invitations')->insert([
                    'event_id' => $eventId,
                    'title' => "You're Invited to {$eventType} Event",
                    'message' => 'Please confirm your attendance.',
                    'theme_color' => '#f3c547',
                    'font_style' => 'Segoe UI',
                    'button_text' => 'Confirm RSVP',
                    'created_at' => now(),
                ]);
            }

            return $eventId;
        });

        return redirect()->route('your.events')->with('success', 'Event created successfully.');
    }
}
