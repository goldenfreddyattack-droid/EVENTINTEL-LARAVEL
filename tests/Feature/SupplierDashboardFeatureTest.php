<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupplierDashboardFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('user_id');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role')->default('client');
                $table->string('full_name')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('supplier_services')) {
            Schema::create('supplier_services', function (Blueprint $table) {
                $table->increments('service_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('category', 100)->nullable();
                $table->string('name', 150)->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->increments('event_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('title', 150)->nullable();
                $table->string('event_type', 100)->nullable();
                $table->date('event_date')->nullable();
                $table->string('venue_name', 150)->nullable();
                $table->string('venue_status', 50)->nullable();
                $table->string('payment_method', 50)->nullable();
                $table->string('status', 50)->default('planning');
                $table->timestamp('created_at')->nullable();
            });
        }

        DB::table('users')->delete();
        DB::table('supplier_services')->delete();
        DB::table('events')->delete();
    }

    public function test_supplier_dashboard_loads_without_collection_mutation_error(): void
    {
        $supplier = User::create([
            'email' => 'supplier@example.com',
            'password' => bcrypt('password123'),
            'role' => 'supplier',
            'full_name' => 'Supplier One',
        ]);

        $serviceId = DB::table('supplier_services')->insertGetId([
            'user_id' => $supplier->user_id,
            'category' => 'Venue',
            'name' => 'Green Valley Resort',
            'price' => 25000.00,
            'created_at' => now(),
        ]);

        DB::table('events')->insert([
            'user_id' => $supplier->user_id + 1,
            'title' => 'Sample Event',
            'event_type' => 'Wedding',
            'event_date' => now()->addDays(5)->toDateString(),
            'venue_name' => 'Green Valley Resort',
            'venue_status' => 'pending',
            'payment_method' => 'cash',
            'status' => 'planning',
            'created_at' => now(),
        ]);

        $this->actingAs($supplier)
            ->get('/supplier/dashboard')
            ->assertOk();
    }
}
