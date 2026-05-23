<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('report_templates')->nullOnDelete();
            $table->foreignId('property_id')->constrained('gsc_properties')->cascadeOnDelete();
            $table->string('name');
            $table->date('date_from');
            $table->date('date_to');
            $table->string('status')->default('pending'); // pending|generating|ready|failed
            $table->string('output_format')->default('pdf'); // pdf|html|json
            $table->string('file_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
