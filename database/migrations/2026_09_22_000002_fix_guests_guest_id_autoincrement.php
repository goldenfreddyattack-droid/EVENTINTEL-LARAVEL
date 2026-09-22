<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('guests') && Schema::hasColumn('guests', 'guest_id')) {
            DB::statement('ALTER TABLE guests MODIFY guest_id INT NOT NULL AUTO_INCREMENT');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('guests') && Schema::hasColumn('guests', 'guest_id')) {
            DB::statement('ALTER TABLE guests MODIFY guest_id INT NOT NULL');
        }
    }
};
