<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierServiceController extends Controller
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
        $services = DB::table('supplier_services')
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('supplier.services', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric'],
            'address' => ['nullable', 'string'],
            'style' => ['nullable', 'string', 'max:150'],
            'service_pic' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $servicePic = null;
        if ($request->hasFile('service_pic')) {
            $servicePic = file_get_contents($request->file('service_pic')->getRealPath());
        }

        DB::table('supplier_services')->insert([
            'user_id' => Auth::id(),
            'category' => $validated['category'],
            'style' => $validated['style'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? 0,
            'address' => $validated['address'] ?? null,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'service_pic' => $servicePic,
            'rating' => 5.00,
            'created_at' => now(),
        ]);

        return redirect()->route('supplier.services')->with('success', 'Service added successfully.');
    }

    public function image($id): Response
    {
        $service = DB::table('supplier_services')
            ->where('service_id', $id)
            ->where('user_id', Auth::id())
            ->first(['service_pic']);

        abort_unless($service && $service->service_pic, 404);

        $imageInfo = getimagesizefromstring($service->service_pic);
        abort_unless($imageInfo !== false, 404);

        return response($service->service_pic, 200, [
            'Content-Type' => $imageInfo['mime'],
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function destroy($id)
    {
        DB::table('supplier_services')
            ->where('service_id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return redirect()->route('supplier.services')->with('success', 'Service removed successfully.');
    }
}
