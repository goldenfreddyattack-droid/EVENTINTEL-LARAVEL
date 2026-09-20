<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_supplier_reviews')) {
            return;
        }

        if (!Schema::hasColumn('event_supplier_reviews', 'review_token')) {
            Schema::table('event_supplier_reviews', function (Blueprint $table) {
                $table->string('review_token', 64)->nullable()->after('event_id');
            });
        }

        $indexes = collect(DB::select('SHOW INDEX FROM event_supplier_reviews'))
            ->groupBy('Key_name');
        foreach ($indexes as $name => $columns) {
            $columnNames = $columns->pluck('Column_name')->sort()->values()->all();
            if ($name !== 'PRIMARY' && $columns->first()->Non_unique === 0 && $columnNames === ['event_id', 'service_column']) {
                Schema::table('event_supplier_reviews', function (Blueprint $table) use ($name) {
                    $table->dropUnique($name);
                });
            }
        }

        $hasSessionIndex = collect(DB::select('SHOW INDEX FROM event_supplier_reviews'))
            ->groupBy('Key_name')
            ->contains(function ($columns) {
                return $columns->first()->Non_unique === 0
                    && $columns->pluck('Column_name')->sort()->values()->all() === ['event_id', 'review_token', 'service_column'];
            });

        if (!$hasSessionIndex) {
            Schema::table('event_supplier_reviews', function (Blueprint $table) {
                $table->unique(['event_id', 'service_column', 'review_token'], 'event_supplier_reviews_session_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('event_supplier_reviews') || !Schema::hasColumn('event_supplier_reviews', 'review_token')) {
            return;
        }

        Schema::table('event_supplier_reviews', function (Blueprint $table) {
            $table->dropUnique('event_supplier_reviews_session_unique');
            $table->dropColumn('review_token');
            $table->unique(['event_id', 'service_column']);
        });
    }
};
