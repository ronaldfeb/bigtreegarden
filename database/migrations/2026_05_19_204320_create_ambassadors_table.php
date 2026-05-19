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
        Schema::create('ambassadors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url');
            $table->string('status')->default(config('constants.ambassador.status.active'));
            $table->string('handle_linkedin')->nullable();
            $table->string('handle_facebook')->nullable();
            $table->string('handle_instagram')->nullable();
            $table->string('website_url')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ambassadors');
    }
};
