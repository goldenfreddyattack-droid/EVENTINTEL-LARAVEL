<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify coordinator_package field to have a default value
        Schema::table('events', function (Blueprint $table) {
            $table->string('coordinator_package', 255)->nullable()->default('')->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('coordinator_package', 255)->change();
        });
    }
};
