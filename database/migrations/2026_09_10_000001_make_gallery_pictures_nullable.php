<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_services', function (Blueprint $table) {
            // Laravel does not support longBlob() in this project's schema builder setup,
            // so use the closest supported binary column type for the existing MySQL columns.
            $table->binary('service_pic3')->nullable()->change();
            $table->binary('service_pic4')->nullable()->change();
            $table->binary('service_pic5')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_services', function (Blueprint $table) {
            $table->binary('service_pic3')->nullable(false)->change();
            $table->binary('service_pic4')->nullable(false)->change();
            $table->binary('service_pic5')->nullable(false)->change();
        });
    }
};
