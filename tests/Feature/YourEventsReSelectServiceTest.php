<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class YourEventsReSelectServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reselect_request_without_service_name_keeps_existing_service_supplier_in_event_record(): void
    {
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('username')->unique();
            $table->string('full_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('role', ['client', 'supplier', 'coordinator', 'admin'])->nullable()->default('client');
            $table->enum('status', ['approved', 'pending', 'rejected'])->nullable()->default('approved');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::dropIfExists('events');
        Schema::create('events', function (Blueprint $table) {
            $table->increments('event_id');
            $table->unsignedInteger('user_id');
            $table->string('title')->nullable();
            $table->string('event_type')->nullable();
            $table->string('theme')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('venue_status')->nullable()->default('pending');
            $table->string('catering')->nullable();
            $table->string('catering_status')->nullable()->default('pending');
            $table->string('host')->nullable();
            $table->string('host_status')->nullable()->default('pending');
            $table->string('soundsnlights')->nullable();
            $table->string('soundsnlights_status')->nullable()->default('pending');
            $table->string('photographer')->nullable();
            $table->string('photographer_status')->nullable()->default('pending');
            $table->string('clothes')->nullable();
            $table->string('clothes_status')->nullable()->default('pending');
            $table->string('coordinator')->nullable();
            $table->string('coordinator_status')->nullable()->default('pending');
            $table->timestamp('created_at')->useCurrent();
        });

        $user = User::create([
            'username' => 'reselect-preserve-user',
            'full_name' => 'ReSelect Preserve Client',
            'email' => 'reselect-preserve@test.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $eventId = DB::table('events')->insertGetId([
            'user_id' => $user->user_id,
            'title' => 'Birthday Event',
            'event_type' => 'Birthday',
            'venue_name' => 'Venue A',
            'venue_status' => 'declined',
            'created_at' => now(),
        ]);

        $response = $this->postJson("/your-events/{$eventId}/reselect", [
            'service_type' => 'venue',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $event = DB::table('events')->where('event_id', $eventId)->first();
        $this->assertSame('Venue A', $event->venue_name);
        $this->assertSame('pending', strtolower((string) $event->venue_status));
    }

    public function test_declined_service_can_be_reselected_from_your_events_status_modal(): void
    {
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('username')->unique();
            $table->string('full_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('role', ['client', 'supplier', 'coordinator', 'admin'])->nullable()->default('client');
            $table->enum('status', ['approved', 'pending', 'rejected'])->nullable()->default('approved');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::dropIfExists('events');
        Schema::create('events', function (Blueprint $table) {
            $table->increments('event_id');
            $table->unsignedInteger('user_id');
            $table->string('title')->nullable();
            $table->string('event_type')->nullable();
            $table->string('theme')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('venue_status')->nullable()->default('pending');
            $table->string('catering')->nullable();
            $table->string('catering_status')->nullable()->default('pending');
            $table->string('host')->nullable();
            $table->string('host_status')->nullable()->default('pending');
            $table->string('soundsnlights')->nullable();
            $table->string('soundsnlights_status')->nullable()->default('pending');
            $table->string('photographer')->nullable();
            $table->string('photographer_status')->nullable()->default('pending');
            $table->string('clothes')->nullable();
            $table->string('clothes_status')->nullable()->default('pending');
            $table->string('coordinator')->nullable();
            $table->string('coordinator_status')->nullable()->default('pending');
            $table->timestamp('created_at')->useCurrent();
        });

        $user = User::create([
            'username' => 'reselect-user',
            'full_name' => 'ReSelect Client',
            'email' => 'reselect@test.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $eventId = DB::table('events')->insertGetId([
            'user_id' => $user->user_id,
            'title' => 'Birthday Event',
            'event_type' => 'Birthday',
            'venue_name' => 'Venue A',
            'venue_status' => 'declined',
            'created_at' => now(),
        ]);

        $response = $this->postJson("/your-events/{$eventId}/reselect", [
            'service_type' => 'venue',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $event = DB::table('events')->where('event_id', $eventId)->first();
        $this->assertSame('Venue A', $event->venue_name);
        $this->assertSame('declined', strtolower((string) $event->venue_status));
    }
}
