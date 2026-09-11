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
            if ($request->routeIs('supplier.services.image')) {
                return $next($request);
            }

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
            'capacity' => ['nullable', 'integer', 'min:0'],
            'venueaddons_price1' => ['nullable', 'numeric', 'min:0'],
            'venueaddons_price2' => ['nullable', 'numeric', 'min:0'],
            'venueaddons_price3' => ['nullable', 'numeric', 'min:0'],
            'venueaddons_price4' => ['nullable', 'numeric', 'min:0'],
            'venueaddons_price5' => ['nullable', 'numeric', 'min:0'],
            'venueaddons_details1' => ['nullable', 'string', 'max:1000'],
            'venueaddons_details2' => ['nullable', 'string', 'max:1000'],
            'venueaddons_details3' => ['nullable', 'string', 'max:1000'],
            'venueaddons_details4' => ['nullable', 'string', 'max:1000'],
            'venueaddons_details5' => ['nullable', 'string', 'max:1000'],
            'service_pic' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'service_pic1' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'service_pic2' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'service_pic3' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'service_pic4' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $servicePic = null;
        if ($request->hasFile('service_pic')) {
            $servicePic = file_get_contents($request->file('service_pic')->getRealPath());
        }

        // Process additional gallery pictures
        $galleryPics = [];
        for ($i = 1; $i <= 4; $i++) {
            $galleryPics[$i] = null;
            if ($request->hasFile("service_pic{$i}")) {
                $galleryPics[$i] = file_get_contents($request->file("service_pic{$i}")->getRealPath());
            }
        }

        // Process venue add-ons
        $venueAddOns = null;
        $venueAddOnsInput = $request->input('venue_add_ons');
        
        if ($validated['category'] === 'Venue' && !empty($venueAddOnsInput)) {
            // Ensure it's an array, convert if needed
            $addonArray = is_array($venueAddOnsInput) ? $venueAddOnsInput : [$venueAddOnsInput];
            // Filter out empty strings
            $addonArray = array_filter($addonArray, function($addon) {
                return !empty(trim((string)$addon));
            });
            
            if (!empty($addonArray)) {
                $venueAddOns = json_encode(array_values($addonArray));
            }
        }

        DB::table('supplier_services')->insert([
            'user_id' => Auth::id(),
            'category' => $validated['category'],
            'style' => $validated['style'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? 0,
            'address' => $validated['address'] ?? null,
            'capacity' => $validated['capacity'] ?? null,
            'latitude' => $request->input('latitude') ?? null,
            'longitude' => $request->input('longitude') ?? null,
            'service_pic' => $servicePic,
            'service_pic1' => $galleryPics[1],
            'service_pic2' => $galleryPics[2],
            'service_pic3' => $galleryPics[3],
            'service_pic4' => $galleryPics[4],
            'service_pic5' => null,
            'venue_add_ons' => $venueAddOns,
            'venueaddons_price1' => $validated['venueaddons_price1'] ?? 0,
            'venueaddons_price2' => $validated['venueaddons_price2'] ?? 0,
            'venueaddons_price3' => $validated['venueaddons_price3'] ?? 0,
            'venueaddons_price4' => $validated['venueaddons_price4'] ?? 0,
            'venueaddons_price5' => $validated['venueaddons_price5'] ?? 0,
            'venueaddons_details1' => $validated['venueaddons_details1'] ?? null,
            'venueaddons_details2' => $validated['venueaddons_details2'] ?? null,
            'venueaddons_details3' => $validated['venueaddons_details3'] ?? null,
            'venueaddons_details4' => $validated['venueaddons_details4'] ?? null,
            'venueaddons_details5' => $validated['venueaddons_details5'] ?? null,
            'rating' => 5.00,
            'created_at' => now(),
        ]);

        return redirect()->route('supplier.services')->with('success', 'Service added successfully.');
    }

    public function image($id, $pic = 'service_pic'): Response
    {
        // Validate pic parameter to prevent injection
        if (!in_array($pic, ['service_pic', 'service_pic1', 'service_pic2', 'service_pic3', 'service_pic4'], true)) {
            abort(404);
        }

        $service = DB::table('supplier_services')
            ->where('service_id', $id)
            ->first([$pic]);

        abort_unless($service && $service->$pic, 404);

        $imageInfo = getimagesizefromstring($service->$pic);
        abort_unless($imageInfo !== false, 404);

        return response($service->$pic, 200, [
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
