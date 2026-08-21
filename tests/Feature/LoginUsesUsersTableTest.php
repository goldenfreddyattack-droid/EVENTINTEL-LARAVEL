<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoginUsesUsersTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_username_from_users_table(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('username')->unique();
            $table->string('full_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->enum('role', ['client', 'supplier', 'coordinator', 'admin'])->nullable()->default('client');
            $table->enum('status', ['approved', 'pending', 'rejected'])->nullable()->default('approved');
            $table->timestamp('created_at')->useCurrent();
        });

        $user = User::create([
            'username' => 'admin',
            'full_name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_rejected_user_cannot_login(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('username')->unique();
            $table->string('full_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->enum('role', ['client', 'supplier', 'coordinator', 'admin'])->nullable()->default('client');
            $table->enum('status', ['approved', 'pending', 'rejected'])->nullable()->default('approved');
            $table->timestamp('created_at')->useCurrent();
        });

        User::create([
            'username' => 'rejected-user',
            'email' => 'rejected@test.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'status' => 'rejected',
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'rejected-user',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }
}
