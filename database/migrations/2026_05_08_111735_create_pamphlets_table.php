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
        Schema::create('pamphlets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('background_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_token_hash')->nullable()->unique();
            $table->timestamp('guest_token_expires_at')->nullable();
            $table->string('status')->default('draft');
            $table->string('heading');
            $table->string('person_full_name');
            $table->date('date_of_birth');
            $table->date('date_of_passing');
            $table->string('date_format')->default('d M Y');
            $table->string('image_shape')->default('square');
            $table->string('image_crop_mode')->default('cover');
            $table->text('short_text');
            $table->string('uploaded_image_path')->nullable();
            $table->string('public_slug')->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['guest_token_expires_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pamphlets');
    }
};
