<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ambassadors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->string('handle_linkedin')->nullable();
            $table->string('handle_facebook')->nullable();
            $table->string('handle_instagram')->nullable();
            $table->string('website_url')->nullable();
            $table->string('profile_image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ambassador_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ambassador_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ambassador_images');
        Schema::dropIfExists('ambassadors');
    }
};
