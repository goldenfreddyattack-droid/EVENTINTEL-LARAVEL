<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PackageController extends Controller
{
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
        $activePackages = $packages[$eventKey] ?? [
            ['tier' => 'Basic', 'name' => 'Basic Package', 'price' => 25000, 'services' => ['venue', 'catering', 'host'], 'desc' => 'Essential event services'],
            ['tier' => 'Standard', 'name' => 'Standard Package', 'price' => 50000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer'], 'desc' => 'Popular balanced choice'],
            ['tier' => 'Premium', 'name' => 'Premium Package', 'price' => 90000, 'services' => ['venue', 'catering', 'host', 'sounds_lights', 'photographer', 'clothes'], 'desc' => 'Complete event experience'],
        ];

        $allocation = [
            'Venue' => 0.30,
            'Catering/Food' => 0.35,
            'Host/MC' => 0.08,
            'Photographer' => 0.10,
            'Sounds & Lights' => 0.08,
            'Clothing/Attire' => 0.05,
            'Decorations' => 0.04,
        ];

        $serviceNames = [
            'venue' => 'Venue',
            'catering' => 'Catering/Food',
            'host' => 'Host/MC',
            'sounds_lights' => 'Sounds & Lights',
            'photographer' => 'Photographer',
            'clothes' => 'Clothing/Attire',
            'church' => 'Church',
            'rental_car' => 'Rental Car',
        ];
        $serviceIcons = [
            'venue' => 'fa-location-dot',
            'catering' => 'fa-utensils',
            'host' => 'fa-microphone',
            'sounds_lights' => 'fa-lightbulb',
            'photographer' => 'fa-camera',
            'clothes' => 'fa-shirt',
            'church' => 'fa-church',
            'rental_car' => 'fa-car',
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
            'selectedBudget',
            'serviceIcons',
            'serviceNames'
        ));
    }
}
