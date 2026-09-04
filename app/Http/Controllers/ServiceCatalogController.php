<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $services = $this->queryServices($definition['categories']);

        return view('userui.service-catalog', [
            'serviceKey' => $service,
            'serviceLabel' => $definition['label'],
            'services' => $services,
            'returnUrl' => $request->query('return'),
            'modal' => $request->boolean('modal'),
            'readonly' => $request->boolean('readonly'),
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
        $serviceRecord = $this->queryServices($definition['categories'])->firstWhere('service_id', $serviceId);
        abort_unless($serviceRecord, 404);

        return view('userui.service-detail', [
            'serviceKey' => $service,
            'serviceLabel' => $definition['label'],
            'serviceRecord' => $serviceRecord,
            'returnUrl' => $request->query('return'),
            'modal' => $request->boolean('modal'),
            'readonly' => $request->boolean('readonly'),
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

    private function queryServices(array $categories)
    {
        if (!Schema::hasTable('supplier_services')) {
            return collect();
        }

        $normalizedCategories = array_map(fn (string $category) => strtolower(trim($category)), $categories);
        return DB::table('supplier_services as services')
            ->leftJoin('users', 'services.user_id', '=', 'users.user_id')
            ->whereNotNull('services.name')
            ->whereIn(DB::raw('LOWER(TRIM(services.category))'), $normalizedCategories)
            ->select('services.*', 'users.full_name as supplier_name', 'users.business_name')
            ->orderByDesc('services.rating')
            ->orderBy('services.price')
            ->get();
    }

    private function definition(string $service): array
    {
        abort_unless(isset(self::CATEGORIES[$service]), 404);
        return self::CATEGORIES[$service];
    }
}
