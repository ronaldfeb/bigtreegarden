<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memorial_pages', function (Blueprint $table) {
            $table->dropColumn(['obituary', 'funeral_programme', 'hymns']);
        });
    }

    public function down(): void
    {
        Schema::table('memorial_pages', function (Blueprint $table) {
            $table->longText('obituary')->nullable();
            $table->longText('funeral_programme')->nullable();
            $table->longText('hymns')->nullable();
        });
    }
};
