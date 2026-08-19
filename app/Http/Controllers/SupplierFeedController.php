<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierFeedController extends Controller
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

        $feedItems = DB::table('supplier_services as s')
            ->join('users as u', 's.user_id', '=', 'u.user_id')
            ->where('s.user_id', $userId)
            ->select('s.*', 'u.full_name', 'u.business_name')
            ->orderByDesc('s.created_at')
            ->limit(10)
            ->get();

        $bookingItems = DB::table('bookings as b')
            ->join('events as e', 'b.event_id', '=', 'e.event_id')
            ->join('supplier_services as s', 'b.service_id', '=', 's.service_id')
            ->where('s.user_id', $userId)
            ->select('b.*', 'e.title as event_title', 's.name as service_name', 'e.event_date')
            ->orderByDesc('b.created_at')
            ->limit(5)
            ->get();

        $serviceCount = DB::table('supplier_services')->where('user_id', $userId)->count();
        $pendingCount = DB::table('bookings as b')
            ->join('supplier_services as s', 'b.service_id', '=', 's.service_id')
            ->where('s.user_id', $userId)
            ->where('b.status', 'pending')
            ->count();

        $approvedCount = DB::table('bookings as b')
            ->join('supplier_services as s', 'b.service_id', '=', 's.service_id')
            ->where('s.user_id', $userId)
            ->where('b.status', 'accepted')
            ->count();

        return view('supplier.feed', [
            'feedItems' => $feedItems,
            'bookingItems' => $bookingItems,
            'serviceCount' => $serviceCount,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
        ]);
    }
}
