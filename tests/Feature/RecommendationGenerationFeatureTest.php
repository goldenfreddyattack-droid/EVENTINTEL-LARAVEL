<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RecommendationGenerationFeatureTest extends TestCase
{
    private function ensureRecommendationTestTables(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('user_id');
                $table->string('username')->nullable();
                $table->string('full_name')->nullable();
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role')->default('client');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->increments('event_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('title', 150)->nullable();
                $table->string('event_type', 100)->nullable();
                $table->string('theme', 120)->nullable();
                $table->decimal('budget', 12, 2)->nullable();
                $table->date('event_date')->nullable();
                $table->time('event_time')->nullable();
                $table->time('event_end_time')->nullable();
                $table->integer('guest_count')->nullable();
                $table->string('venue_name', 150)->nullable();
                $table->string('status', 50)->default('planning');
                $table->string('payment_method', 50)->nullable();
                $table->string('payment_status', 50)->default('pending');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('supplier_services')) {
            Schema::create('supplier_services', function (Blueprint $table) {
                $table->id('service_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('category', 100)->nullable();
                $table->string('style', 150)->nullable();
                $table->string('name', 150)->nullable();
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->integer('capacity')->nullable();
                $table->text('address')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->decimal('rating', 3, 2)->default(5.00);
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    private function insertSupplierService(array $overrides = []): void
    {
        $columns = Schema::getColumnListing('supplier_services');
        $row = [
            'user_id' => 1,
            'category' => 'Venue',
            'style' => 'Resort',
            'name' => 'The Grand Pavilion',
            'description' => 'Sample venue for recommendation tests.',
            'price' => 45000,
            'capacity' => 200,
            'venue_add_ons' => null,
            'venueaddons_price1' => 0,
            'venueaddons_price2' => 0,
            'venueaddons_price3' => 0,
            'venueaddons_price4' => 0,
            'venueaddons_price5' => 0,
            'venueaddons_details1' => null,
            'venueaddons_details2' => null,
            'venueaddons_details3' => null,
            'venueaddons_details4' => null,
            'venueaddons_details5' => '',
            'service_pic' => null,
            'service_pic1' => null,
            'service_pic2' => null,
            'service_pic3' => null,
            'service_pic4' => null,
            'address' => 'Makati City',
            'latitude' => null,
            'longitude' => null,
            'rating' => 4.9,
            'created_at' => now(),
        ];

        $filtered = [];
        foreach ($row as $key => $value) {
            if (in_array($key, $columns, true)) {
                $filtered[$key] = $value;
            }
        }

        foreach ($overrides as $key => $value) {
            if (in_array($key, $columns, true)) {
                $filtered[$key] = $value;
            }
        }

        if (!isset($filtered['service_id'])) {
            $filtered['service_id'] = ((int) (DB::table('supplier_services')->max('service_id') ?? 0)) + 1;
        }

        DB::table('supplier_services')->insert($filtered);
    }

    public function test_it_generates_ai_tip_even_without_openai_key(): void
    {
        $this->ensureRecommendationTestTables();

        $nextUserId = (int) (DB::table('users')->max('user_id') ?? 0) + 1;
        $user = User::query()->firstOrCreate(
            ['email' => 'client-recommendation@example.com'],
            [
                'user_id' => $nextUserId,
                'username' => 'clientuser',
                'full_name' => 'Client User',
                'password' => bcrypt('password123'),
                'role' => 'client',
            ]
        );

        if (DB::table('supplier_services')->count() === 0) {
            $this->insertSupplierService(['user_id' => $user->user_id]);
        }

        config()->set('services.openai.key', null);

        $this->actingAs($user);

        $response = $this->postJson(route('recommendation.generate'), [
            'event' => 'Wedding',
            'budget' => 50000,
            'pax' => 100,
            'services' => ['Venue', 'Catering'],
        ]);

        $response->assertOk();
        $response->assertJsonPath('html', fn ($html) => str_contains((string) $html, 'Recommended Event Timeline'));
        $response->assertJsonPath('html', fn ($html) => str_contains((string) $html, 'AI Planning Tips'));
    }

    public function test_it_notifies_matching_suppliers_when_a_recommendation_is_used(): void
    {
        $this->ensureRecommendationTestTables();

        $clientId = (int) (DB::table('users')->max('user_id') ?? 0) + 1;
        $supplierId = $clientId + 1;

        $client = User::query()->firstOrCreate(
            ['email' => 'client-recommendation-booking@example.com'],
            [
                'user_id' => $clientId,
                'username' => 'recommendationclient',
                'full_name' => 'Recommendation Client',
                'password' => bcrypt('password123'),
                'role' => 'client',
            ]
        );

        $supplier = User::query()->firstOrCreate(
            ['email' => 'supplier-recommendation-booking@example.com'],
            [
                'user_id' => $supplierId,
                'username' => 'supplierone',
                'full_name' => 'Supplier One',
                'password' => bcrypt('password123'),
                'role' => 'supplier',
            ]
        );

        $this->insertSupplierService([
            'user_id' => $supplierId,
            'category' => 'venue',
            'style' => 'Resort',
            'name' => 'The Grand Pavilion',
            'description' => 'Sample venue for recommendation tests.',
            'price' => 45000,
            'capacity' => 200,
            'address' => 'Makati City',
            'rating' => 4.9,
        ]);

        app()->instance(\App\Services\FirebaseService::class, new class {
            public array $sent = [];

            public function saveMessage(int $eventId, int $senderId, ?int $receiverId, string $message, string $senderName = 'User', ?int $messageId = null): bool
            {
                $this->sent[] = compact('eventId', 'senderId', 'receiverId', 'message', 'senderName', 'messageId');
                return true;
            }
        });

        $this->actingAs($client);

        $response = $this->postJson(route('recommendation.use'), [
            'event_type' => 'Wedding',
            'budget' => 50000,
            'guest_count' => 100,
            'services' => ['Venue'],
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('supplier_count', 1);
    }

    public function test_it_rejects_unrealistic_budget_or_guest_count_for_recommendations(): void
    {
        $this->ensureRecommendationTestTables();

        $nextUserId = (int) (DB::table('users')->max('user_id') ?? 0) + 1;
        $client = User::query()->firstOrCreate(
            ['email' => 'client-recommendation-validation@example.com'],
            [
                'user_id' => $nextUserId,
                'username' => 'lowbudgetclient',
                'full_name' => 'Low Budget Client',
                'password' => bcrypt('password123'),
                'role' => 'client',
            ]
        );

        $this->actingAs($client);

        $response = $this->postJson(route('recommendation.generate'), [
            'event' => 'Wedding',
            'budget' => 2500,
            'pax' => 4,
            'services' => ['Venue'],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn ($message) => str_contains((string) $message, 'Budget') || str_contains((string) $message, 'guest'));
    }
}
