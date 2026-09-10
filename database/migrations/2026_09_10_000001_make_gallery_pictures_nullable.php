<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_services', function (Blueprint $table) {
            // Make gallery picture columns nullable
            $table->longBlob('service_pic3')->nullable()->change();
            $table->longBlob('service_pic4')->nullable()->change();
            $table->longBlob('service_pic5')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_services', function (Blueprint $table) {
            // Revert back to NOT NULL
            $table->longBlob('service_pic3')->nullable(false)->change();
            $table->longBlob('service_pic4')->nullable(false)->change();
            $table->longBlob('service_pic5')->nullable(false)->change();
        });
    }
};
