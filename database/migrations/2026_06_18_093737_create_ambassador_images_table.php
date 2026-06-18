<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ambassador_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ambassador_id')
                ->constrained('ambassadors')
                ->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['ambassador_id', 'sort_order']);
        });

        $now = now();

        DB::table('ambassadors')->orderBy('id')->each(function (object $ambassador) use ($now): void {
            DB::table('ambassador_images')->insert([
                'id' => (string) Str::uuid(),
                'ambassador_id' => $ambassador->id,
                'image_path' => 'ambassadors/placeholder.svg',
                'caption' => null,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        Schema::table('ambassadors', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ambassadors', function (Blueprint $table) {
            $table->string('image_url')->default('ambassadors/placeholder.svg');
        });

        DB::table('ambassadors')->orderBy('id')->each(function (object $ambassador): void {
            $imagePath = DB::table('ambassador_images')
                ->where('ambassador_id', $ambassador->id)
                ->orderBy('sort_order')
                ->value('image_path');

            DB::table('ambassadors')
                ->where('id', $ambassador->id)
                ->update(['image_url' => $imagePath ?? 'ambassadors/placeholder.svg']);
        });

        Schema::dropIfExists('ambassador_images');
    }
};
