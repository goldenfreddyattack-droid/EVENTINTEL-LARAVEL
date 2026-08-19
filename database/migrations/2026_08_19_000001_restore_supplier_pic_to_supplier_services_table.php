<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('supplier_services') && !Schema::hasColumn('supplier_services', 'supplier_pic')) {
            DB::statement('ALTER TABLE supplier_services ADD supplier_pic LONGBLOB NULL AFTER service_pic');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('supplier_services') && Schema::hasColumn('supplier_services', 'supplier_pic')) {
            DB::statement('ALTER TABLE supplier_services DROP COLUMN supplier_pic');
        }
    }
};
