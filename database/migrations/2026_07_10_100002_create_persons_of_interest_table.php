<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persons_of_interest', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_token_hash')->nullable()->unique();
            $table->timestamp('guest_token_expires_at')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('display_name');
            $table->date('date_of_birth');
            $table->date('date_of_passing');
            $table->string('place_of_birth')->nullable();
            $table->string('place_of_passing')->nullable();
            $table->string('profile_image_path')->nullable();
            $table->string('public_slug')->unique();
            $table->string('qr_code_path')->nullable();
            $table->timestamp('qr_generated_at')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['created_by_user_id', 'status']);
            $table->index(['guest_token_expires_at', 'status']);
        });

        Schema::create('user_persons_of_interest', function (Blueprint $table) {
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('person_of_interest_id')->constrained('persons_of_interest')->cascadeOnDelete();
            $table->string('role')->default('manager');
            $table->timestamps();

            $table->primary(['user_id', 'person_of_interest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_persons_of_interest');
        Schema::dropIfExists('persons_of_interest');
    }
};
