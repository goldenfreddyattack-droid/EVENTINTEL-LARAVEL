<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('event_supplier_reviews') && !Schema::hasColumn('event_supplier_reviews', 'reviewer_name')) {
            Schema::table('event_supplier_reviews', function (Blueprint $table) {
                $table->string('reviewer_name', 100)->nullable()->after('review_text');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('event_supplier_reviews') && Schema::hasColumn('event_supplier_reviews', 'reviewer_name')) {
            Schema::table('event_supplier_reviews', function (Blueprint $table) {
                $table->dropColumn('reviewer_name');
            });
        }
    }
};
