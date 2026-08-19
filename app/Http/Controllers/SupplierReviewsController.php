<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierReviewsController extends Controller
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
        $reviewRows = DB::table('reviews as r')
            ->join('events as e', 'r.event_id', '=', 'e.event_id')
            ->join('supplier_services as s', 'r.service_id', '=', 's.service_id')
            ->join('users as u', 'r.user_id', '=', 'u.user_id')
            ->where('s.user_id', Auth::id())
            ->select('r.*', 'e.title as event_title', 's.name as service_name', 'u.full_name as reviewer_name')
            ->orderByDesc('r.created_at')
            ->get();

        return view('supplier.reviews', compact('reviewRows'));
    }
}
