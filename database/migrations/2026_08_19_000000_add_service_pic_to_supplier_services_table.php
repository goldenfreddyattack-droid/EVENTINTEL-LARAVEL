<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('supplier_services') && !Schema::hasColumn('supplier_services', 'service_pic')) {
            Schema::table('supplier_services', function (Blueprint $table) {
                $table->binary('service_pic')->nullable()->after('latitude');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('supplier_services') && Schema::hasColumn('supplier_services', 'service_pic')) {
            Schema::table('supplier_services', function (Blueprint $table) {
                $table->dropColumn('service_pic');
            });
        }
    }
};
