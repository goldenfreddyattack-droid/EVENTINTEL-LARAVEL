<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invitations') && !Schema::hasColumn('invitations', 'template')) {
            Schema::table('invitations', function (Blueprint $table) {
                $table->string('template', 50)->default('Classic')->after('background_image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invitations') && Schema::hasColumn('invitations', 'template')) {
            Schema::table('invitations', function (Blueprint $table) {
                $table->dropColumn('template');
            });
        }
    }
};
