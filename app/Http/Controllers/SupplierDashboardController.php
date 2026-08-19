<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'supplier') {
                abort(403, 'Unauthorized. Supplier access only.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $userId = Auth::id();
        $serviceNames = DB::table('supplier_services')->where('user_id', $userId)->pluck('name')->filter()->values()->all();

        $stats = ['total' => 0, 'pending' => 0, 'accepted' => 0, 'rejected' => 0];

        if (!empty($serviceNames)) {
            $eventRows = DB::table('events')
                ->select(
                    'event_id',
                    'title',
                    'venue_name', 'venue_status',
                    'clothes', 'clothes_status',
                    'catering', 'catering_status',
                    'host', 'host_status',
                    'photographer', 'photographer_status',
                    'soundsnlights', 'soundsnlights_status'
                )
                ->where(function ($query) use ($serviceNames) {
                    foreach ($serviceNames as $serviceName) {
                        $query->orWhere('venue_name', $serviceName)
                            ->orWhere('clothes', $serviceName)
                            ->orWhere('catering', $serviceName)
                            ->orWhere('host', $serviceName)
                            ->orWhere('photographer', $serviceName)
                            ->orWhere('soundsnlights', $serviceName);
                    }
                })
                ->get();

            foreach ($eventRows as $event) {
                $serviceFields = [
                    'venue' => 'venue_status',
                    'clothes' => 'clothes_status',
                    'catering' => 'catering_status',
                    'host' => 'host_status',
                    'photographer' => 'photographer_status',
                    'soundsnlights' => 'soundsnlights_status',
                ];

                foreach ($serviceFields as $field => $statusField) {
                    $value = $event->{$field} ?? null;
                    if (!in_array($value, $serviceNames, true)) {
                        continue;
                    }

                    $status = $event->{$statusField} ?? 'pending';
                    $stats['total']++;

                    if (in_array($status, ['pending'], true)) {
                        $stats['pending']++;
                    } elseif (in_array($status, ['accepted', 'Payment Pending', 'Paid'], true)) {
                        $stats['accepted']++;
                    } elseif (in_array($status, ['declined', 'rejected'], true)) {
                        $stats['rejected']++;
                    }
                }
            }
        }

        $services = DB::table('supplier_services')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return view('supplier.dashboard', [
            'stats' => $stats,
            'serviceCount' => DB::table('supplier_services')->where('user_id', $userId)->count(),
            'services' => $services,
            'newsFeed' => [
                ['title' => 'New supplier marketplace update', 'time' => '2 hours ago'],
                ['title' => 'Booking trends are rising this week', 'time' => 'Today'],
                ['title' => 'Remember to keep service profiles up to date', 'time' => 'Yesterday'],
            ],
        ]);
    }
}
