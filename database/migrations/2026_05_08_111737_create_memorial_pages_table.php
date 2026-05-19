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
        Schema::create('memorial_pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pamphlet_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('funeral_programme')->nullable();
            $table->longText('obituary')->nullable();
            $table->longText('hymns')->nullable();
            $table->boolean('gallery_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memorial_pages');
    }
};
