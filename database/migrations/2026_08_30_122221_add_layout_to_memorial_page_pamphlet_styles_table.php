<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memorial_page_pamphlet_styles', function (Blueprint $table) {
            $table->json('layout')->nullable()->after('dates_color');
        });
    }

    public function down(): void
    {
        Schema::table('memorial_page_pamphlet_styles', function (Blueprint $table) {
            $table->dropColumn('layout');
        });
    }
};
