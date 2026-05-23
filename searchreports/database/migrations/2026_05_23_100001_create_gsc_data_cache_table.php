<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gsc_data_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('gsc_properties')->cascadeOnDelete();
            $table->date('date_range_start');
            $table->date('date_range_end');
            $table->json('dimensions');
            $table->json('metrics');
            $table->unsignedInteger('query_count')->default(0);
            $table->timestamp('fetched_at');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['property_id', 'date_range_start', 'date_range_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gsc_data_cache');
    }
};
