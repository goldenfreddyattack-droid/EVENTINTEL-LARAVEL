<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $activeEventCount = DB::table('events')
            ->where('user_id', Auth::id())
            ->whereIn('status', ['planning', 'pending', 'ongoing'])
            ->count();

        return view('userui.homepage', [
            'planningCount' => $activeEventCount,
            'planningLimitReached' => $activeEventCount >= 3,
        ]);
    }
}
