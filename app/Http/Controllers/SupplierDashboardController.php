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

        $services = DB::table('supplier_services')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $categoryMap = [
            'Venue' => ['column' => 'venue_name', 'status' => 'venue_status'],
            'Clothing' => ['column' => 'clothes', 'status' => 'clothes_status'],
            'Catering' => ['column' => 'catering', 'status' => 'catering_status'],
            'Host' => ['column' => 'host', 'status' => 'host_status'],
            'Photographer' => ['column' => 'photographer', 'status' => 'photographer_status'],
            'Sounds & Lights' => ['column' => 'soundsnlights', 'status' => 'soundsnlights_status'],
        ];

        $bookingRows = [];

        foreach ($services as $service) {
            if (!isset($categoryMap[$service->category])) {
                continue;
            }

            $column = $categoryMap[$service->category]['column'];
            $statusColumn = $categoryMap[$service->category]['status'];

            $events = DB::table('events')
                ->join('users', 'events.user_id', '=', 'users.user_id')
                ->select(
                    'events.event_id',
                    'events.title',
                    'events.event_type',
                    'events.event_date',
                    'events.budget',
                    "events.$column",
                    "events.$statusColumn",
                    'events.payment_method',
                    'users.full_name as client_name'
                )
                ->where("events.$column", $service->name)
                ->orderByDesc('events.event_date')
                ->get();

            foreach ($events as $event) {
                $bookingRows[] = [
                    'service_id' => $service->service_id,
                    'event_id' => $event->event_id,
                    'title' => $event->title,
                    'event_type' => $event->event_type,
                    'event_date' => $event->event_date,
                    'service_price' => $service->price,
                    'client_name' => $event->client_name,
                    'service' => $service->category,
                    'status' => $event->{$statusColumn},
                    'payment_method' => $event->payment_method ?? 'cash',
                    'business_name' => $service->name,
                ];
            }
        }

        $stats = [
            'total'           => count($bookingRows),
            'pending'         => 0,
            'accepted'        => 0,
            'pending_payment' => 0,
            'rejected'        => 0,
            'completed'       => 0,
        ];

        foreach ($bookingRows as $row) {
            $status = strtolower(trim((string) ($row['status'] ?? 'pending')));

            if (in_array($status, ['pending', 'waiting'], true)) {
                $stats['pending']++;
            } elseif (in_array($status, ['accepted', 'approved', 'confirmed'], true)) {
                $stats['accepted']++;
            } elseif (in_array($status, ['payment pending', 'pending confirmation'], true)) {
                $stats['pending_payment']++;
            } elseif (in_array($status, ['declined', 'rejected', 'cancelled'], true)) {
                $stats['rejected']++;
            } elseif (in_array($status, ['paid', 'finish', 'finished', 'done', 'completed'], true)) {
                $stats['completed']++;
            } else {
                $stats['pending']++;
            }
        }

        return view('supplier.dashboard', [
            'stats'        => $stats,
            'serviceCount' => DB::table('supplier_services')->where('user_id', $userId)->count(),
            'services'     => $services,
            'newsFeed'     => [
                ['title' => 'New supplier marketplace update', 'time' => '2 hours ago'],
                ['title' => 'Booking trends are rising this week', 'time' => 'Today'],
                ['title' => 'Remember to keep service profiles up to date', 'time' => 'Yesterday'],
            ],
        ]);
    }
}