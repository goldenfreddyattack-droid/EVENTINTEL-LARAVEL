<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierPageController extends Controller
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

    public function show(Request $request, $page = 'DASHBOARD')
    {
        $page = strtoupper((string) $page);
        $legacyMap = [
            'DASHBOARD' => 'supplier.dashboard',
            'BOOKINGS' => 'supplier.bookings',
            'SERVICES' => 'supplier.services',
            'MESSAGES' => 'supplier.messages',
            'REVIEWS' => 'supplier.reviews',
            'SETTINGS' => 'supplier.settings',
            'FEED' => 'supplier.feed',
            'PROFILE' => 'supplier.profile',
        ];

        if (isset($legacyMap[$page])) {
            return redirect()->route($legacyMap[$page]);
        }

        abort(404, 'Supplier page not found.');
    }

    /**
     * Replace relative asset paths with proper Laravel URLs
     */
    protected function replaceAssetPaths(string $content): string
    {
        // CSS paths
        $content = preg_replace_callback(
            '/href=["\']\.\.\/css\/([^"\']+)["\']/',
            fn ($m) => 'href="' . asset('css/' . $m[1]) . '"',
            $content
        );

        // JS paths
        $content = preg_replace_callback(
            '/src=["\']\.\.\/js\/([^"\']+)["\']/',
            fn ($m) => 'src="' . asset('js/' . $m[1]) . '"',
            $content
        );

        // Image paths
        $content = preg_replace_callback(
            '/src=["\']\.\.\/images\/([^"\']+)["\']/',
            fn ($m) => 'src="' . asset('images/' . $m[1]) . '"',
            $content
        );

        // Additional relative path fixes
        $content = preg_replace_callback(
            '/href=["\']\.\.\/([^"\']+)["\']/',
            fn ($m) => 'href="' . asset($m[1]) . '"',
            $content
        );

        return $content;
    }
}
