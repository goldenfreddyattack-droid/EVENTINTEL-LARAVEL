<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('event_supplier_reviews')) {
            return;
        }

        Schema::create('event_supplier_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('service_column', 50);
            $table->string('supplier_name', 150);
            $table->unsignedTinyInteger('rating');
            $table->text('review_text')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'service_column']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_supplier_reviews');
    }
};
