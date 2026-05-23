<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gsc_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('site_url');
            $table->string('site_type')->default('url'); // url|domain
            $table->string('permission_level')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('display_name')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'site_url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gsc_properties');
    }
};
