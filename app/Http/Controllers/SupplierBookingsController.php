<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierBookingsController extends Controller
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

    public function index(Request $request)
    {
        $userId = Auth::id();

        // Fetch all services for this supplier
        $services = DB::table('supplier_services')
            ->where('user_id', $userId)
            ->orderBy('category')
            ->get();

        // Category to column mapping
        $categoryMap = [
            'Venue' => ['column' => 'venue_name', 'status' => 'venue_status', 'key' => 'venue'],
            'Clothing' => ['column' => 'clothes', 'status' => 'clothes_status', 'key' => 'clothes'],
            'Catering' => ['column' => 'catering', 'status' => 'catering_status', 'key' => 'catering'],
            'Host' => ['column' => 'host', 'status' => 'host_status', 'key' => 'host'],
            'Photographer' => ['column' => 'photographer', 'status' => 'photographer_status', 'key' => 'photographer'],
            'Sounds & Lights' => ['column' => 'soundsnlights', 'status' => 'soundsnlights_status', 'key' => 'soundsnlights']
        ];

        // Build booking rows
        $bookingRows = [];
        foreach ($services as $service) {
            if (!isset($categoryMap[$service->category])) {
                continue;
            }

            $colInfo = $categoryMap[$service->category];
            $column = $colInfo['column'];
            $statusColumn = $colInfo['status'];
            $serviceKey = $colInfo['key'];

            $events = DB::table('events')
                ->join('users', 'events.user_id', '=', 'users.user_id')
                ->select(
                    'events.event_id',
                    'events.title',
                    'events.event_type',
                    'events.theme',
                    'events.event_date',
                    'events.event_time',
                    'events.event_end_time',
                    'events.guest_count',
                    'events.budget',
                    "events.$column",
                    "events.$statusColumn",
                    'events.payment_method',
                    'events.clothes',
                    'events.catering',
                    'events.host',
                    'events.photographer',
                    'events.soundsnlights',
                    'users.full_name as client_name'
                )
                ->where("events.$column", $service->name)
                ->orderByDesc('events.event_date')
                ->get();

            foreach ($events as $event) {
                $addonLabels = [];
                $addonTotal = 0;

                // Build addon list by checking each service field for values
                // Only show addons that match the venue name (were selected as venue add-ons)
                $addonMap = [
                    'catering' => ['label' => 'Catering', 'price' => 'venueaddons_price1'],
                    'clothes' => ['label' => 'Clothes', 'price' => 'venueaddons_price2'],
                    'host' => ['label' => 'Host', 'price' => 'venueaddons_price3'],
                    'photographer' => ['label' => 'Photographer', 'price' => 'venueaddons_price4'],
                    'soundsnlights' => ['label' => 'Sounds & Lights', 'price' => 'venueaddons_price5'],
                ];

                // Get the venue name to compare against
                $venueName = $event->venue_name ?? null;

                // Only show addons if we're looking at a venue booking
                if ($serviceKey === 'venue' && $venueName) {
                    foreach ($addonMap as $field => $addon) {
                        $value = $event->$field ?? null;
                        // Only include if the addon value matches the venue name (was selected as a venue add-on)
                        if (is_string($value) && strtolower(trim($value)) === strtolower(trim($venueName))) {
                            $addonLabels[] = $addon['label'];
                            $addonTotal += (float) ($service->{$addon['price']} ?? 0);
                        }
                    }
                }

                $bookingRows[] = [
                    'service_id' => $service->service_id,
                    'event_id' => $event->event_id,
                    'title' => $event->title,
                    'event_type' => $event->event_type,
                    'theme' => $event->theme,
                    'event_date' => $event->event_date,
                    'event_time' => $event->event_time,
                    'event_end_time' => $event->event_end_time,
                    'guest_count' => $event->guest_count,
                    'service_price' => (float) $service->price + $addonTotal,
                    'client_name' => $event->client_name,
                    'service' => $service->category,
                    'service_key' => $serviceKey,
                    'status' => $event->$statusColumn,
                    'payment_method' => $event->payment_method ?? 'cash',
                    'business_name' => $service->name,
                    'addons' => empty($addonLabels) ? 'None' : implode(', ', array_unique($addonLabels)),
                ];
            }
        }

        // Filter by status
        $statusFilter = $request->get('status', 'all');
        if ($statusFilter !== 'all') {
            $bookingRows = array_filter($bookingRows, function ($row) use ($statusFilter) {
                if ($statusFilter === 'accepted') {
                    return in_array($row['status'], ['accepted', 'Payment Pending', 'Pending Confirmation', 'Paid'], true);
                }
                if ($statusFilter === 'Paid') {
                    return $row['status'] === 'Paid';
                }
                return $row['status'] === $statusFilter;
            });
        }

        // Pagination
        $page = max(1, (int) $request->get('page', 1));
        $perPage = 15;
        $totalRows = count($bookingRows);
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $paginatedRows = array_slice($bookingRows, $offset, $perPage);

        return view('supplier.bookings', [
            'paginatedRows' => $paginatedRows,
            'statusFilter' => $statusFilter,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    public function updateStatus(Request $request)
    {
        $action = $request->input('action');
        $eventId = (int) $request->input('id');
        $service = $request->input('service');

        if (!in_array($action, ['accepted', 'declined', 'pending', 'paid'])) {
            return response()->json(['success' => false, 'error' => 'Invalid action']);
        }

        if (!in_array($service, ['venue', 'clothes', 'catering', 'host', 'photographer', 'soundsnlights'])) {
            return response()->json(['success' => false, 'error' => 'Invalid service']);
        }

        $map = [
            'venue' => ['status' => 'venue_status', 'note' => 'venue_note'],
            'clothes' => ['status' => 'clothes_status', 'note' => 'clothes_note'],
            'catering' => ['status' => 'catering_status', 'note' => 'catering_note'],
            'host' => ['status' => 'host_status', 'note' => 'host_note'],
            'photographer' => ['status' => 'photographer_status', 'note' => 'photographer_note'],
            'soundsnlights' => ['status' => 'soundsnlights_status', 'note' => 's&l_note'],
        ];

        $statusColumn = $map[$service]['status'];
        $noteColumn = $map[$service]['note'];

        if ($action === 'accepted') {
            $newStatus = 'Payment Pending';
        } elseif ($action === 'paid') {
            $newStatus = 'Paid';
        } else {
            $newStatus = $action;
        }

        $updates = [$statusColumn => $newStatus];

        if ($newStatus === 'declined') {
            $declineNote = $request->input('decline_note', '');
            $updates[$noteColumn] = $declineNote;
        }

        // If updating venue status, also update all addon statuses to match
        if ($service === 'venue') {
            $event = DB::table('events')->where('event_id', $eventId)->first();
            if ($event && $event->venue_name) {
                $addonStatusMap = [
                    'catering_status' => 'catering',
                    'clothes_status' => 'clothes',
                    'host_status' => 'host',
                    'photographer_status' => 'photographer',
                    'soundsnlights_status' => 'soundsnlights',
                ];
                
                foreach ($addonStatusMap as $statusField => $valueField) {
                    // Check if this addon has the venue name (meaning it was selected as a venue add-on)
                    if ($event->$valueField === $event->venue_name) {
                        $updates[$statusField] = $newStatus;
                    }
                }
            }
        }

        DB::table('events')
            ->where('event_id', $eventId)
            ->update($updates);

        return response()->json(['success' => true]);
    }
}
