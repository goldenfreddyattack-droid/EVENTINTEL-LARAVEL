<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ServiceBookmarkFeatureTest extends TestCase
{
    public function test_user_can_toggle_a_service_bookmark_and_it_appears_in_recommendation_feed(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->primary();
                $table->string('full_name')->nullable();
                $table->string('business_name')->nullable();
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->string('role')->default('client');
            });
        }

        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id('event_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('title')->nullable();
                $table->string('event_type')->nullable();
                $table->date('event_date')->nullable();
                $table->decimal('budget', 12, 2)->nullable();
                $table->integer('guest_count')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('supplier_services')) {
            Schema::create('supplier_services', function (Blueprint $table) {
                $table->id('service_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('category')->nullable();
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->decimal('rating', 3, 2)->default(5.00);
                $table->text('address')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('user_service_bookmarks')) {
            Schema::create('user_service_bookmarks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('service_id');
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['user_id', 'service_id']);
            });
        }

        $user = new User([
            'user_id' => 1,
            'full_name' => 'Test Client',
            'email' => 'client@example.com',
            'password' => bcrypt('secret'),
            'role' => 'client',
        ]);

        $serviceId = DB::table('supplier_services')->insertGetId([
            'user_id' => 1,
            'category' => 'venue',
            'name' => 'The Grand Pavilion',
            'description' => 'Elegant venue for lifelong celebrations.',
            'price' => 45000,
            'rating' => 4.90,
            'address' => 'Davao City',
            'created_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->post(route('services.bookmark', ['service' => 'venue', 'serviceId' => $serviceId]));
        $response->assertOk();
        $response->assertJsonPath('bookmarked', true);
        $this->assertDatabaseHas('user_service_bookmarks', [
            'user_id' => $user->getAuthIdentifier(),
            'service_id' => $serviceId,
        ]);

        $recommendationResponse = $this->get(route('recommendation'));
        $recommendationResponse->assertOk();
        $recommendationResponse->assertSee('Bookmarked place picks');
    }
}
