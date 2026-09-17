<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['service_pic2', 'service_pic3', 'service_pic4', 'service_pic5'] as $column) {
            if (Schema::hasColumn('supplier_services', $column)) {
                Schema::table('supplier_services', function (Blueprint $table) use ($column) {
                    $table->binary($column)->nullable()->change();
                });
            } else {
                Schema::table('supplier_services', function (Blueprint $table) use ($column) {
                    $table->binary($column)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['service_pic2', 'service_pic3', 'service_pic4', 'service_pic5'] as $column) {
            if (Schema::hasColumn('supplier_services', $column)) {
                Schema::table('supplier_services', function (Blueprint $table) use ($column) {
                    $table->binary($column)->nullable(false)->change();
                });
            }
        }
    }
};
