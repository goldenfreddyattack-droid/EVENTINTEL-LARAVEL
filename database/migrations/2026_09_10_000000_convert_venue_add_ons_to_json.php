<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing comma-separated venue_add_ons to JSON arrays
        $services = DB::table('supplier_services')
            ->whereNotNull('venue_add_ons')
            ->where('venue_add_ons', '!=', '')
            ->get();

        foreach ($services as $service) {
            // Check if it's already JSON
            $decoded = json_decode($service->venue_add_ons, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Already JSON, skip
                continue;
            }

            // Convert comma-separated string to array
            $addons = array_map('trim', explode(',', $service->venue_add_ons));
            $addons = array_filter($addons, function($addon) {
                return !empty($addon);
            });
            $addons = array_values($addons);

            // Update to JSON format
            DB::table('supplier_services')
                ->where('service_id', $service->service_id)
                ->update(['venue_add_ons' => json_encode($addons)]);
        }
    }

    public function down(): void
    {
        // Convert back to comma-separated format
        $services = DB::table('supplier_services')
            ->whereNotNull('venue_add_ons')
            ->where('venue_add_ons', '!=', '')
            ->get();

        foreach ($services as $service) {
            $decoded = json_decode($service->venue_add_ons, true);
            if (is_array($decoded)) {
                $commaSeparated = implode(', ', $decoded);
                DB::table('supplier_services')
                    ->where('service_id', $service->service_id)
                    ->update(['venue_add_ons' => $commaSeparated]);
            }
        }
    }
};
