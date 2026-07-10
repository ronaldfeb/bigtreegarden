<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memorial_pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('person_of_interest_id')->constrained('persons_of_interest')->cascadeOnDelete();
            $table->string('title');
            $table->string('public_slug')->unique();
            $table->string('status')->default('draft');
            $table->string('active_day_type')->nullable();
            $table->date('active_day_date')->nullable();
            $table->longText('obituary')->nullable();
            $table->longText('funeral_programme')->nullable();
            $table->longText('hymns')->nullable();
            $table->boolean('gallery_enabled')->default(true);
            $table->boolean('live_comments_enabled')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['person_of_interest_id', 'status']);
        });

        Schema::create('memorial_page_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('memorial_page_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('memorial_page_pamphlets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('memorial_page_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignUuid('background_id')->nullable()->constrained('memorial_page_pamphlet_backgrounds')->nullOnDelete();
            $table->string('heading');
            $table->text('short_text');
            $table->string('date_format')->default('d M Y');
            $table->string('image_shape')->default('square');
            $table->string('image_crop_mode')->default('cover');
            $table->string('uploaded_image_path')->nullable();
            $table->string('preview_image_path')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('memorial_page_pamphlet_styles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pamphlet_id')->unique()->constrained('memorial_page_pamphlets')->cascadeOnDelete();
            $table->string('font_family')->nullable();
            $table->boolean('is_bold')->default(false);
            $table->boolean('is_italic')->default(false);
            $table->string('date_format')->default('d M Y');
            $table->string('text_color')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('memorial_page_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('memorial_page_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('body');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('memorial_sites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('person_of_interest_id')->constrained('persons_of_interest')->cascadeOnDelete();
            $table->string('name');
            $table->string('site_type');
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('geofence_radius_m')->default(5);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memorial_sites');
        Schema::dropIfExists('memorial_page_sections');
        Schema::dropIfExists('memorial_page_pamphlet_styles');
        Schema::dropIfExists('memorial_page_pamphlets');
        Schema::dropIfExists('memorial_page_images');
        Schema::dropIfExists('memorial_pages');
    }
};
