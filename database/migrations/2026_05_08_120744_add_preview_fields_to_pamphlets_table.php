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
        if (! Schema::hasColumn('pamphlets', 'date_format')) {
            Schema::table('pamphlets', function (Blueprint $table) {
                $table->string('date_format')->default('d M Y')->after('date_of_passing');
            });
        }

        if (! Schema::hasColumn('pamphlets', 'image_shape')) {
            Schema::table('pamphlets', function (Blueprint $table) {
                $table->string('image_shape')->default('square')->after('date_format');
            });
        }

        if (! Schema::hasColumn('pamphlets', 'image_crop_mode')) {
            Schema::table('pamphlets', function (Blueprint $table) {
                $table->string('image_crop_mode')->default('cover')->after('image_shape');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pamphlets', function (Blueprint $table) {
            $columns = collect(['date_format', 'image_shape', 'image_crop_mode'])
                ->filter(fn (string $column): bool => Schema::hasColumn('pamphlets', $column))
                ->values()
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
