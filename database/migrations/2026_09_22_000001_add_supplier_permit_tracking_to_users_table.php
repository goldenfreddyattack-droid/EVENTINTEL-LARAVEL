<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'business_permit_expiry_date')) {
                $table->date('business_permit_expiry_date')->nullable()->after('business_permit');
            }

            if (! Schema::hasColumn('users', 'permit_status')) {
                $table->string('permit_status')->nullable()->default('valid')->after('business_permit_expiry_date');
            }

            if (! Schema::hasColumn('users', 'permit_notice_sent_at')) {
                $table->timestamp('permit_notice_sent_at')->nullable()->after('permit_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'permit_notice_sent_at')) {
                $table->dropColumn('permit_notice_sent_at');
            }

            if (Schema::hasColumn('users', 'permit_status')) {
                $table->dropColumn('permit_status');
            }

            if (Schema::hasColumn('users', 'business_permit_expiry_date')) {
                $table->dropColumn('business_permit_expiry_date');
            }
        });
    }
};
