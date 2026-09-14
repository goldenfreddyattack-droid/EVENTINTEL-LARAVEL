<?php

namespace Tests\Feature;

use App\Http\Controllers\SupplierServiceController;
use Illuminate\Http\Request;
use Tests\TestCase;

class SupplierCustomAddonsTest extends TestCase
{
    public function test_it_normalizes_supplier_custom_addons_with_prices_and_details(): void
    {
        $request = new Request([
            'venue_addon_name' => ['Filipino Style Catering', 'Western Buffet', 'Halal Menu'],
            'venue_addon_price' => ['2500', '3200', '4100'],
            'venue_addon_details' => [
                'Rice, grilled meats, and native dishes.',
                'Steak, pasta, roasted vegetables, and desserts.',
                'Muslim-friendly dishes and clean ingredients.',
            ],
        ]);

        $addons = SupplierServiceController::normalizeCustomAddOns($request);

        $this->assertSame([
            [
                'name' => 'Filipino Style Catering',
                'price' => 2500.0,
                'details' => 'Rice, grilled meats, and native dishes.',
            ],
            [
                'name' => 'Western Buffet',
                'price' => 3200.0,
                'details' => 'Steak, pasta, roasted vegetables, and desserts.',
            ],
            [
                'name' => 'Halal Menu',
                'price' => 4100.0,
                'details' => 'Muslim-friendly dishes and clean ingredients.',
            ],
        ], $addons);
    }
}
