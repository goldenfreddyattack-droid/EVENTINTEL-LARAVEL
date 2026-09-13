<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ServiceCatalogController extends Controller
{
    private const CATEGORIES = [
        'venue' => ['label' => 'Venue', 'categories' => ['venue']],
        'catering' => ['label' => 'Food & Catering', 'categories' => ['catering', 'food']],
        'church' => ['label' => 'Church', 'categories' => ['church']],
        'clothes' => ['label' => 'Clothes', 'categories' => ['clothes', 'clothing', 'attire']],
        'host' => ['label' => 'Host', 'categories' => ['host', 'mc']],
        'photographer' => ['label' => 'Photographer', 'categories' => ['photographer', 'photography']],
        'sounds_lights' => ['label' => 'Sounds & Lights', 'categories' => ['sounds_lights', 'sounds & lights', 'sound and lights', 'lights and sound']],
        'rental_car' => ['label' => 'Rental Car', 'categories' => ['rental_car', 'rental car', 'car rental']],
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, string $service)
    {
        $definition = $this->definition($service);
        $reselect = in_array($request->query('reselect'), ['1', 'true', 'yes', 'on'], true) || $request->boolean('reselect');
        $excludeSupplierName = $reselect ? $this->excludeSupplierName($request, $service) : null;
        $services = $this->queryServices($definition['categories'], $excludeSupplierName);

        return view($reselect ? 'userui.reselect-service-catalog' : 'userui.service-catalog', [
            'serviceKey' => $service,
            'serviceLabel' => $definition['label'],
            'services' => $services,
            'returnUrl' => $request->query('return') ?: route('your.events'),
            'modal' => $request->boolean('modal'),
            'readonly' => $request->boolean('readonly'),
            'eventId' => (int) $request->query('event_id'),
        ]);
    }

    public function carousel(Request $request, string $service)
    {
        $definition = $this->definition($service);
        $services = $this->queryServices($definition['categories']);

        return view('userui.service-carousel', [
            'serviceKey' => $service,
            'serviceLabel' => $definition['label'],
            'services' => $services,
        ]);
    }

    public function show(Request $request, string $service, int $serviceId)
    {
        $definition = $this->definition($service);
        $reselect = in_array($request->query('reselect'), ['1', 'true', 'yes', 'on'], true) || $request->boolean('reselect');
        $excludeSupplierName = $reselect ? $this->excludeSupplierName($request, $service) : null;
        $serviceRecord = $this->queryServices($definition['categories'], $excludeSupplierName)->firstWhere('service_id', $serviceId);
        abort_unless($serviceRecord, 404);

        return view($reselect ? 'userui.reselect-service-detail' : 'userui.service-detail', [
            'serviceKey' => $service,
            'serviceLabel' => $definition['label'],
            'serviceRecord' => $serviceRecord,
            'returnUrl' => $request->query('return') ?: route('your.events'),
            'modal' => $request->boolean('modal'),
            'readonly' => $request->boolean('readonly'),
            'eventId' => (int) $request->query('event_id'),
        ]);
    }

    public function carouselShow(string $service, int $serviceId)
    {
        $definition = $this->definition($service);
        $serviceRecord = $this->queryServices($definition['categories'])->firstWhere('service_id', $serviceId);
        abort_unless($serviceRecord, 404);

        return view('userui.service-carousel-detail', [
            'serviceKey' => $service,
            'serviceLabel' => $definition['label'],
            'serviceRecord' => $serviceRecord,
        ]);
    }

    public function bookmark(Request $request, string $service, int $serviceId)
    {
        $definition = $this->definition($service);
        $serviceRecord = $this->queryServices($definition['categories'])->firstWhere('service_id', $serviceId);
        abort_unless($serviceRecord, 404);

        if (!Schema::hasTable('user_service_bookmarks')) {
            return response()->json([
                'bookmarked' => false,
                'count' => 0,
                'message' => 'Bookmark table is not available yet.',
            ], 409);
        }

        $existing = DB::table('user_service_bookmarks')
            ->where('user_id', Auth::id())
            ->where('service_id', $serviceId)
            ->exists();

        if ($existing) {
            DB::table('user_service_bookmarks')
                ->where('user_id', Auth::id())
                ->where('service_id', $serviceId)
                ->delete();

            return response()->json([
                'bookmarked' => false,
                'count' => DB::table('user_service_bookmarks')->where('user_id', Auth::id())->count(),
            ]);
        }

        DB::table('user_service_bookmarks')->insert([
            'user_id' => Auth::id(),
            'service_id' => $serviceId,
            'created_at' => now(),
        ]);

        return response()->json([
            'bookmarked' => true,
            'count' => DB::table('user_service_bookmarks')->where('user_id', Auth::id())->count(),
            'serviceName' => $serviceRecord->name,
        ]);
    }

    public function bookmarkedServices()
    {
        if (!Schema::hasTable('user_service_bookmarks') || !Schema::hasTable('supplier_services')) {
            return collect();
        }

        $userJoinColumn = Schema::hasColumn('users', 'user_id') ? 'users.user_id' : 'users.id';
        $query = DB::table('user_service_bookmarks as bookmarks')
            ->where('bookmarks.user_id', Auth::id())
            ->join('supplier_services as services', 'services.service_id', '=', 'bookmarks.service_id')
            ->leftJoin('users', 'services.user_id', '=', DB::raw($userJoinColumn));

        $select = ['services.*'];
        if (Schema::hasColumn('users', 'full_name')) {
            $select[] = 'users.full_name as supplier_name';
        }
        if (Schema::hasColumn('users', 'business_name')) {
            $select[] = 'users.business_name';
        }

        return $query->select($select)
            ->orderByDesc('bookmarks.created_at')
            ->get();
    }

    public function isBookmarked(int $serviceId): bool
    {
        if (!Schema::hasTable('user_service_bookmarks')) {
            return false;
        }

        return DB::table('user_service_bookmarks')
            ->where('user_id', Auth::id())
            ->where('service_id', $serviceId)
            ->exists();
    }

    private function queryServices(array $categories, ?string $excludeSupplierName = null)
    {
        if (!Schema::hasTable('supplier_services')) {
            return collect();
        }

        $normalizedCategories = array_map(fn (string $category) => strtolower(trim($category)), $categories);
        $userJoinColumn = Schema::hasColumn('users', 'user_id') ? 'users.user_id' : 'users.id';
        $query = DB::table('supplier_services as services')
            ->leftJoin('users', 'services.user_id', '=', DB::raw($userJoinColumn))
            ->whereNotNull('services.name')
            ->whereIn(DB::raw('LOWER(TRIM(services.category))'), $normalizedCategories);

        $select = ['services.*'];
        if (Schema::hasColumn('users', 'full_name')) {
            $select[] = 'users.full_name as supplier_name';
        }
        if (Schema::hasColumn('users', 'business_name')) {
            $select[] = 'users.business_name';
        }
        $query->select($select);

        if ($excludeSupplierName) {
            $query->where('services.name', '!=', $excludeSupplierName);
        }

        return $query->orderByDesc('services.rating')
            ->orderBy('services.price')
            ->get();
    }

    private function excludeSupplierName(Request $request, string $service): ?string
    {
        $eventId = (int) $request->query('event_id');
        if ($eventId <= 0) {
            return null;
        }

        $event = DB::table('events')
            ->where('event_id', $eventId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$event) {
            return null;
        }

        $map = [
            'venue' => 'venue_name',
            'catering' => 'catering',
            'host' => 'host',
            'sounds_lights' => 'soundsnlights',
            'photographer' => 'photographer',
            'clothes' => 'clothes',
            'coordinator' => 'coordinator',
        ];

        return trim((string) ($event->{$map[$service]} ?? '')) ?: null;
    }

    private function definition(string $service): array
    {
        abort_unless(isset(self::CATEGORIES[$service]), 404);
        return self::CATEGORIES[$service];
    }
}
