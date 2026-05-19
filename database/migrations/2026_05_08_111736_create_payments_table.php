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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pamphlet_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('payfast');
            $table->string('provider_payment_id')->nullable();
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 3)->default('ZAR');
            $table->string('status')->default('initiated');
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['pamphlet_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
