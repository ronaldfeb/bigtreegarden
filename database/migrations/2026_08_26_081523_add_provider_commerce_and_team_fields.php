<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->string('vat_number')->nullable()->after('registration_number');
            $table->unsignedInteger('credits_remaining')->default(0)->after('status');
        });

        Schema::table('service_provider_users', function (Blueprint $table) {
            $table->unique('user_id');
        });

        Schema::create('service_provider_backgrounds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_provider_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('image_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['service_provider_id', 'sort_order']);
        });

        Schema::table('persons_of_interest', function (Blueprint $table) {
            $table->foreignUuid('service_provider_id')
                ->nullable()
                ->after('created_by_user_id')
                ->constrained('service_providers')
                ->nullOnDelete();
        });

        Schema::table('memorial_page_pamphlets', function (Blueprint $table) {
            $table->foreignUuid('service_provider_background_id')
                ->nullable()
                ->after('background_id')
                ->constrained('service_provider_backgrounds')
                ->nullOnDelete();
        });

        Schema::create('service_provider_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('invited_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('role')->default('staff');
            $table->string('token_hash')->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['service_provider_id', 'email']);
        });

        Schema::create('service_provider_credit_packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('page_count');
            $table->unsignedInteger('price_cents');
            $table->string('currency', 3)->default('ZAR');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('service_provider_credit_purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('service_provider_credit_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('purchased_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('package_name');
            $table->unsignedInteger('page_count');
            $table->unsignedInteger('price_cents');
            $table->string('currency', 3)->default('ZAR');
            $table->string('payment_method');
            $table->string('status')->default('pending_payment');
            $table->string('payment_reference')->unique();
            $table->string('proof_of_payment_path')->nullable();
            $table->foreignUuid('reviewed_by_staff_user_id')->nullable()->constrained('staff_users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['service_provider_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('service_provider_credit_ledger', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_provider_id')->constrained()->cascadeOnDelete();
            $table->integer('delta');
            $table->unsignedInteger('balance_after');
            $table->string('reason');
            $table->nullableUuidMorphs('reference');
            $table->foreignUuid('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['service_provider_id', 'created_at']);
        });

        Schema::create('platform_bank_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('branch_code')->nullable();
            $table->text('reference_note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_bank_details');
        Schema::dropIfExists('service_provider_credit_ledger');
        Schema::dropIfExists('service_provider_credit_purchases');
        Schema::dropIfExists('service_provider_credit_packages');
        Schema::dropIfExists('service_provider_invitations');

        Schema::table('memorial_page_pamphlets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_provider_background_id');
        });

        Schema::table('persons_of_interest', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_provider_id');
        });

        Schema::dropIfExists('service_provider_backgrounds');

        Schema::table('service_provider_users', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });

        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn(['vat_number', 'credits_remaining']);
        });
    }
};
