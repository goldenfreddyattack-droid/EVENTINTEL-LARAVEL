<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_packages')) {
            Schema::create('event_packages', function (Blueprint $table) {
                $table->id('package_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('event_type', 100);
                $table->string('name', 150);
                $table->decimal('price', 12, 2);
                $table->text('description')->nullable();
                $table->text('service_ids');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_packages');
    }
};
