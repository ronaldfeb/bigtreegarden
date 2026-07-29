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
        Schema::table('memorial_page_pamphlet_styles', function (Blueprint $table) {
            $table->string('dates_color', 7)->default('#000000')->after('short_text_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memorial_page_pamphlet_styles', function (Blueprint $table) {
            $table->dropColumn('dates_color');
        });
    }
};
