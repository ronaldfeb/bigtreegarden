<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_of_interest_vaults', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('person_of_interest_id')->unique()->constrained('persons_of_interest')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('status')->default('sealed');
            $table->timestamp('released_at')->nullable();
            $table->unsignedInteger('storage_limit_mb')->default(1024);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('person_of_interest_vault_beneficiaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vault_id')->constrained('person_of_interest_vaults')->cascadeOnDelete();
            $table->string('type')->default('beneficiary');
            $table->string('full_name');
            $table->string('email');
            $table->string('contact_number');
            $table->text('physical_address');
            $table->string('access_code_hash');
            $table->string('access_code_hint')->nullable();
            $table->timestamp('first_accessed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('person_of_interest_vault_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vault_id')->constrained('person_of_interest_vaults')->cascadeOnDelete();
            $table->foreignUuid('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('title')->nullable();
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size_bytes');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('person_of_interest_vault_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vault_id')->constrained('person_of_interest_vaults')->cascadeOnDelete();
            $table->foreignUuid('author_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->longText('body');
            $table->string('visibility')->default('all');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('person_of_interest_vault_post_beneficiaries', function (Blueprint $table) {
            $table->foreignUuid('vault_post_id')->constrained('person_of_interest_vault_posts')->cascadeOnDelete();
            $table->foreignUuid('vault_beneficiary_id')->constrained('person_of_interest_vault_beneficiaries')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['vault_post_id', 'vault_beneficiary_id'], 'vault_post_beneficiary_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_of_interest_vault_post_beneficiaries');
        Schema::dropIfExists('person_of_interest_vault_posts');
        Schema::dropIfExists('person_of_interest_vault_media');
        Schema::dropIfExists('person_of_interest_vault_beneficiaries');
        Schema::dropIfExists('person_of_interest_vaults');
    }
};
