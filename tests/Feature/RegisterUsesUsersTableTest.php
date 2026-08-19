<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RegisterUsesUsersTableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('username')->unique();
            $table->string('full_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->enum('role', ['client', 'supplier', 'coordinator', 'admin'])->default('client');
            $table->enum('status', ['approved', 'pending', 'rejected'])->default('pending');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('middle_initial')->nullable();
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->string('barangay')->nullable();
            $table->string('postal_code')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function test_user_can_register_using_copyeventintel_users_schema(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Jane',
            'middle_initial' => 'D',
            'last_name' => 'Doe',
            'username' => 'janedoe',
            'email' => 'janedoe@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'age' => 25,
            'gender' => 'female',
            'phone' => '09123456789',
            'province' => 'Pampanga',
            'municipality' => 'Apalit',
            'barangay' => 'Balucuc',
            'postal_code' => '2016',
            'role' => 'client',
            'data_privacy_consent' => 'on',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('users', [
            'username' => 'janedoe',
            'email' => 'janedoe@example.com',
            'role' => 'client',
            'status' => 'pending',
        ]);
    }
}
