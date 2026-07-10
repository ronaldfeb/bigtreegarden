<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->string('job_title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('invited_by_staff_user_id')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('staff_users', function (Blueprint $table) {
            $table->foreign('invited_by_staff_user_id')
                ->references('id')
                ->on('staff_users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_users');
    }
};
