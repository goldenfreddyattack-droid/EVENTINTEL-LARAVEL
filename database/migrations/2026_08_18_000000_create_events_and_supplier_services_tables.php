<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create events table if it doesn't exist
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id('event_id');
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
                $table->string('venue_status', 50)->default('pending');
                $table->text('venue_address')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('status', 50)->default('planning');
                $table->string('clothes', 255)->nullable();
                $table->string('clothes_status', 50)->default('pending');
                $table->string('catering', 255)->nullable();
                $table->string('catering_status', 50)->default('pending');
                $table->string('host', 255)->nullable();
                $table->string('host_status', 50)->default('pending');
                $table->string('soundsnlights', 255)->nullable();
                $table->string('soundsnlights_status', 50)->default('pending');
                $table->string('photographer', 255)->nullable();
                $table->string('photographer_status', 50)->default('pending');
                $table->string('coordinator', 255)->nullable();
                $table->string('coordinator_package', 255)->nullable()->default('');
                $table->string('coordinator_status', 255)->default('pending');
                $table->text('coordinator_proposal')->nullable();
                $table->string('payment_method', 50)->nullable();
                $table->string('payment_status', 50)->default('pending');
                $table->text('clothes_note')->nullable();
                $table->text('venue_note')->nullable();
                $table->text('catering_note')->nullable();
                $table->text('host_note')->nullable();
                $table->text('s&l_note')->nullable();
                $table->text('photographer_note')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // Create supplier_services table if it doesn't exist
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
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('supplier_services');
    }
};
