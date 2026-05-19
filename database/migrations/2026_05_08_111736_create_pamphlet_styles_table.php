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
        Schema::create('pamphlet_styles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pamphlet_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('font_family')->nullable();
            $table->boolean('is_bold')->default(false);
            $table->boolean('is_italic')->default(false);
            $table->string('date_format')->default('d M Y');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pamphlet_styles');
    }
};
