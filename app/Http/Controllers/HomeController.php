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
        $planningCount = DB::table('events')
            ->where('user_id', Auth::id())
            ->where('status', 'planning')
            ->count();

        return view('userui.homepage', [
            'planningCount' => $planningCount,
            'planningLimitReached' => $planningCount >= 3,
        ]);
    }
}
