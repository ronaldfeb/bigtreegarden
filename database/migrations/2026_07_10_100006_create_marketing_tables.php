<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_adverts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('staff_user_id')->constrained()->cascadeOnDelete();
            $table->string('client_name');
            $table->string('campaign_name')->nullable();
            $table->string('code')->unique();
            $table->string('destination_url');
            $table->string('utm_source');
            $table->string('utm_medium')->default('qr');
            $table->string('utm_campaign');
            $table->string('utm_content')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('marketing_advert_visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('marketing_advert_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_hash')->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamp('visited_at');
            $table->timestamps();
        });

        Schema::create('marketing_leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('staff_user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('marketing_advert_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('organisation')->nullable();
            $table->string('source');
            $table->string('status')->default('new');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('marketing_lead_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('marketing_lead_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('staff_user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_lead_notes');
        Schema::dropIfExists('marketing_leads');
        Schema::dropIfExists('marketing_advert_visits');
        Schema::dropIfExists('marketing_adverts');
    }
};
