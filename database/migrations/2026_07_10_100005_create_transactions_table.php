<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->uuidMorphs('payable');
            $table->string('type');
            $table->string('provider')->default('payfast');
            $table->string('merchant_reference')->unique();
            $table->string('provider_payment_id')->nullable();
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 3)->default('ZAR');
            $table->string('status')->default('initiated');
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['payable_type', 'payable_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('memorial_page_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('memorial_page_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('author_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('memorial_site_id')->nullable()->constrained('memorial_sites')->nullOnDelete();
            $table->foreignUuid('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->string('type')->default('text');
            $table->text('body')->nullable();
            $table->string('image_path')->nullable();
            $table->string('context');
            $table->decimal('posted_latitude', 10, 7)->nullable();
            $table->decimal('posted_longitude', 10, 7)->nullable();
            $table->boolean('is_gps_verified')->default(false);
            $table->string('status')->default('pending');
            $table->foreignUuid('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memorial_page_messages');
        Schema::dropIfExists('transactions');
    }
};
