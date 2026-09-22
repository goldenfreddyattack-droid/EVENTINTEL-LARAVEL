<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::user()->role === 'coordinator', 403, 'Only coordinators can create packages.');
            return $next($request);
        })->only('store');
    }

    public function index(Request $request)
    {
        $eventType = trim((string) $request->query('event_type', ''));
        $selectedBudget = (int) $request->query('budget', 0);

        if ($eventType === '') {
            $prefill = json_decode((string) $request->cookie('event_recommendation_prefill', ''), true);
            $eventType = is_array($prefill) ? (string) ($prefill['eventType'] ?? '') : '';
        }

        $packages = [
            'birthday' => [
                ['tier' => 'Basic', 'name' => 'Basic Birthday', 'price' => 25000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'A simple and affordable celebration package'],
                ['tier' => 'Standard', 'name' => 'Standard Birthday', 'price' => 50000, 'services' => ['venue', 'catering', 'host', 'sounds_lights'], 'desc' => 'Most popular choice with sounds & lights'],
                ['tier' => 'Premium', 'name' => 'Premium Birthday', 'price' => 85000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer', 'clothes'], 'desc' => 'Complete celebration with full styling'],
            ],
            'debut' => [
                ['tier' => 'Basic', 'name' => 'Basic Debut', 'price' => 40000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'A classic debut celebration package with 18 roses setup'],
                ['tier' => 'Standard', 'name' => 'Standard Debut', 'price' => 80000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'Includes debut production and photo coverage'],
                ['tier' => 'Premium', 'name' => 'Premium Debut', 'price' => 150000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer', 'clothes'], 'desc' => 'Full debut production with styling and entourage'],
            ],
            'wedding' => [
                ['tier' => 'Basic', 'name' => 'Basic Wedding', 'price' => 60000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'An intimate wedding essentials package'],
                ['tier' => 'Standard', 'name' => 'Standard Wedding', 'price' => 120000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'Balanced package for a memorable day'],
                ['tier' => 'Premium', 'name' => 'Premium Wedding', 'price' => 250000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer', 'clothes', 'church', 'rental_car'], 'desc' => 'Everything you need for a grand wedding'],
            ],
            'anniversary' => [
                ['tier' => 'Basic', 'name' => 'Basic Anniversary', 'price' => 30000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'Celebrate your milestone simply'],
                ['tier' => 'Standard', 'name' => 'Standard Anniversary', 'price' => 60000, 'services' => ['venue', 'catering', 'host', 'photographer'], 'desc' => 'Include photography to capture the moment'],
                ['tier' => 'Premium', 'name' => 'Premium Anniversary', 'price' => 100000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'A premium celebration for your special day'],
            ],
            'christening' => [
                ['tier' => 'Basic', 'name' => 'Basic Christening', 'price' => 20000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'Essential services for a blessed day'],
                ['tier' => 'Standard', 'name' => 'Standard Christening', 'price' => 40000, 'services' => ['venue', 'catering', 'host', 'photographer'], 'desc' => 'Adds photo coverage for memories'],
                ['tier' => 'Premium', 'name' => 'Premium Christening', 'price' => 70000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'Complete celebration package'],
            ],
            'gender reveal' => [
                ['tier' => 'Basic', 'name' => 'Basic Reveal', 'price' => 15000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'Simple reveal celebration'],
                ['tier' => 'Standard', 'name' => 'Standard Reveal', 'price' => 35000, 'services' => ['venue', 'catering', 'host', 'photographer'], 'desc' => 'Capture the big moment'],
                ['tier' => 'Premium', 'name' => 'Premium Reveal', 'price' => 60000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'A full surprise party production'],
            ],
            'reunion' => [
                ['tier' => 'Basic', 'name' => 'Basic Reunion', 'price' => 20000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'Great for intimate family reunions'],
                ['tier' => 'Standard', 'name' => 'Standard Reunion', 'price' => 45000, 'services' => ['venue', 'catering', 'host', 'photographer'], 'desc' => 'Add photography to preserve memories'],
                ['tier' => 'Premium', 'name' => 'Premium Reunion', 'price' => 80000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'A grand family gathering experience'],
            ],
        ];

        $eventKey = strtolower($eventType);
        $activePackages = [];

        $serviceNames = [
            'venue' => 'Venue',
            'catering' => 'Catering/Food',
            'host' => 'Host/MC',
            'sounds_lights' => 'Sounds & Lights',
            'photographer' => 'Photographer',
            'clothes' => 'Clothing/Attire',
        ];
        $serviceIcons = [
            'venue' => 'fa-location-dot',
            'catering' => 'fa-utensils',
            'host' => 'fa-microphone',
            'sounds_lights' => 'fa-lightbulb',
            'photographer' => 'fa-camera',
            'clothes' => 'fa-shirt',
        ];
        $serviceCatalog = Schema::hasTable('supplier_services')
            ? DB::table('supplier_services')->select('service_id', 'name', 'category', 'price')->whereNotNull('name')->orderBy('category')->orderBy('name')->get()
            : collect();
        $activePackages = Schema::hasTable('event_packages')
            ? DB::table('event_packages')->where(function ($query) use ($eventKey) {
                $query->whereRaw('LOWER(event_type) = ?', [$eventKey])->orWhere('event_type', 'All');
            })->orderBy('price')->get()->map(function ($package) use ($serviceCatalog) {
                $ids = json_decode($package->service_ids, true) ?: [];
                $selected = $serviceCatalog->whereIn('service_id', $ids);
                $services = $selected->map(function ($service) {
                    return match (strtolower(trim((string) $service->category))) {
                        'venue' => 'venue',
                        'catering' => 'catering',
                        'host', 'mc' => 'host',
                        'sounds & lights', 'sounds and lights', 'sounds_lights' => 'sounds_lights',
                        'photographer' => 'photographer',
                        'clothing', 'clothes', 'styling' => 'clothes',
                        default => null,
                    };
                })->filter()->unique()->values()->all();
                $serviceOptions = $selected->mapWithKeys(function ($service) {
                    $key = match (strtolower(trim((string) $service->category))) {
                        'venue' => 'venue',
                        'catering' => 'catering',
                        'host', 'mc' => 'host',
                        'sounds & lights', 'sounds and lights', 'sounds_lights' => 'sounds_lights',
                        'photographer' => 'photographer',
                        'clothing', 'clothes', 'styling' => 'clothes',
                        default => null,
                    };
                    return $key ? [$key => $service->name] : [];
                })->all();
                return [
                    'package_id' => $package->package_id,
                    'tier' => 'Community package',
                    'name' => $package->name,
                    'price' => (float) $package->price,
                    'services' => $services,
                    'service_names' => $selected->pluck('name')->values()->all(),
                    'service_options' => $serviceOptions,
                    'desc' => $package->description ?: 'Created from real supplier services.',
                ];
            })->filter(fn ($package) => count($package['services']) > 0)->values()->all()
            : [];

        $activeEventCount = Auth::check()
            ? DB::table('events')
                ->where('user_id', Auth::id())
                ->whereIn('status', ['planning', 'pending', 'ongoing'])
                ->count()
            : 0;
        $planningLimitReached = $activeEventCount >= 3;

        $allocation = [
            'Venue' => 0.30,
            'Catering/Food' => 0.35,
            'Host/MC' => 0.08,
            'Photographer' => 0.10,
            'Sounds & Lights' => 0.08,
            'Clothing/Attire' => 0.05,
            'Decorations' => 0.04,
        ];

        $minByCategory = [];
        if (Schema::hasTable('supplier_services')) {
            $services = DB::table('supplier_services')
                ->select('category', 'price')
                ->where('price', '>', 0)
                ->get();

            foreach ($services as $service) {
                $key = match (strtolower(trim((string) $service->category))) {
                    'venue' => 'venue',
                    'catering' => 'catering',
                    'host' => 'host',
                    'photographer' => 'photographer',
                    'sounds & lights' => 'sounds_lights',
                    'clothing' => 'clothes',
                    default => null,
                };

                if ($key && (!isset($minByCategory[$key]) || (float) $service->price < $minByCategory[$key])) {
                    $minByCategory[$key] = (float) $service->price;
                }
            }
        }

        return view('userui.packages', compact(
            'activePackages',
            'allocation',
            'eventKey',
            'eventType',
            'minByCategory',
            'serviceCatalog',
            'selectedBudget',
            'serviceIcons',
            'serviceNames',
            'planningLimitReached'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'event_type' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'distinct'],
        ]);

        $serviceIds = DB::table('supplier_services')->whereIn('service_id', $data['service_ids'])->pluck('service_id')->map(fn ($id) => (int) $id)->values()->all();
        if (!$serviceIds) {
            return back()->withInput()->withErrors(['service_ids' => 'Select at least one real supplier service.']);
        }
        $hasVenue = DB::table('supplier_services')->whereIn('service_id', $serviceIds)->whereRaw('LOWER(TRIM(category)) = ?', ['venue'])->exists();
        if (!$hasVenue) {
            return back()->withInput()->withErrors(['service_ids' => 'Every package must include a real venue.']);
        }

        DB::table('event_packages')->insert([
            'user_id' => Auth::id(),
            'event_type' => $data['event_type'],
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'] ?? null,
            'service_ids' => json_encode($serviceIds),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('packages', ['event_type' => $data['event_type']])->with('success', 'Real package created successfully.');
    }
}
