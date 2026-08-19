<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierOnboardingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::user()->role === 'supplier', 403, 'Unauthorized. Supplier access only.');

            return $next($request);
        });
    }

    public function index()
    {
        $user = Auth::user();
        $services = DB::table('supplier_services')
            ->where('user_id', $user->user_id)
            ->orderByDesc('created_at')
            ->get();
        $detailsDone = filled(trim((string) ($user->business_name ?? '')));
        $servicesDone = $services->isNotEmpty();
        $doneSteps = (int) $detailsDone + (int) $servicesDone;

        return view('supplier.onboarding', [
            'user' => $user,
            'services' => $services,
            'detailsDone' => $detailsDone,
            'servicesDone' => $servicesDone,
            'setupComplete' => $detailsDone && $servicesDone,
            'doneSteps' => $doneSteps,
            'progressPct' => (int) round(($doneSteps / 2) * 100),
        ]);
    }

    public function updateDetails(Request $request)
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:150'],
            'business_address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        DB::table('users')->where('user_id', Auth::id())->update($validated);

        return redirect()->route('supplier.setup')->with('success', 'Business details saved successfully.');
    }

    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'style' => ['nullable', 'string', 'max:150'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
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
            'latitude' => $validated['latitude'] ?? null,
            'service_pic' => $servicePic,
            'rating' => 5.00,
            'created_at' => now(),
        ]);

        return redirect()->route('supplier.setup')->with('success', 'Service added successfully.');
    }

    public function destroyService($id)
    {
        DB::table('supplier_services')
            ->where('service_id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return redirect()->route('supplier.setup')->with('success', 'Service removed successfully.');
    }
}