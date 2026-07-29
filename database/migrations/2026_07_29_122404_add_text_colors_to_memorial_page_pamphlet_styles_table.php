<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memorial_page_pamphlet_styles', function (Blueprint $table) {
            $table->string('heading_color', 7)->default('#FFFFFF')->after('date_format');
            $table->string('name_color', 7)->default('#FFFFFF')->after('heading_color');
            $table->string('short_text_color', 7)->default('#FFFFFF')->after('name_color');
            $table->dropColumn('text_color');
        });
    }

    public function down(): void
    {
        Schema::table('memorial_page_pamphlet_styles', function (Blueprint $table) {
            $table->string('text_color')->nullable()->after('date_format');
            $table->dropColumn(['heading_color', 'name_color', 'short_text_color']);
        });
    }
};
