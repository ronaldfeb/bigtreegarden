<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_organisations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type')->default('other');
            $table->string('relationship_kind')->default('strategic_partner');
            $table->string('partner_stage')->nullable();
            $table->string('relationship_status')->default('prospect');
            $table->foreignUuid('relationship_owner_staff_user_id')->nullable()->constrained('staff_users')->nullOnDelete();
            $table->foreignUuid('service_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website_url')->nullable();
            $table->string('geographic_coverage')->nullable();
            $table->text('services_offered')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('next_review_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('crm_organisation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('relationship_owner_staff_user_id')->nullable()->constrained('staff_users')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('marketing_lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('role_title')->nullable();
            $table->string('journey')->nullable();
            $table->string('lifecycle_stage')->default('enquiry');
            $table->text('notes')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('crm_interactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('subject');
            $table->foreignUuid('staff_user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('note');
            $table->string('summary')->nullable();
            $table->text('body')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('crm_follow_ups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('subject');
            $table->foreignUuid('assigned_staff_user_id')->nullable()->constrained('staff_users')->nullOnDelete();
            $table->string('type')->default('follow_up');
            $table->string('status')->default('pending');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->timestamp('due_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_follow_ups');
        Schema::dropIfExists('crm_interactions');
        Schema::dropIfExists('crm_contacts');
        Schema::dropIfExists('crm_organisations');
    }
};
