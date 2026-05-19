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
        Schema::table('memorial_gallery_images', function (Blueprint $table) {
            $table->foreign('memorial_page_id')
                ->references('id')
                ->on('memorial_pages')
                ->cascadeOnDelete();
        });

        Schema::table('memorial_additional_sections', function (Blueprint $table) {
            $table->foreign('memorial_page_id')
                ->references('id')
                ->on('memorial_pages')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memorial_gallery_images', function (Blueprint $table) {
            $table->dropForeign(['memorial_page_id']);
        });

        Schema::table('memorial_additional_sections', function (Blueprint $table) {
            $table->dropForeign(['memorial_page_id']);
        });
    }
};
