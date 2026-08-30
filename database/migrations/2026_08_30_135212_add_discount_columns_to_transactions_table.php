<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignUuid('discount_code_id')->nullable()->after('currency')->constrained('discount_codes')->nullOnDelete();
            $table->unsignedInteger('original_amount_cents')->nullable()->after('amount_cents');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_code_id');
            $table->dropColumn('original_amount_cents');
        });
    }
};
