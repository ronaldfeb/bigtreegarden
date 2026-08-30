<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 8)->unique();
            $table->string('discount_type');
            $table->unsignedTinyInteger('percent')->nullable();
            $table->unsignedInteger('amount_cents')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('applies_to_all')->default(false);
            $table->nullableUuidMorphs('discountable');
            $table->foreignUuid('reserved_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->foreignUuid('used_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('used_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignUuid('created_by_staff_user_id')->nullable()->constrained('staff_users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['starts_at', 'ends_at']);
            $table->index('used_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
