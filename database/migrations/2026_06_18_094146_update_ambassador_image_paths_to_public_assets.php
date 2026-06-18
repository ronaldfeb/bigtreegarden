<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $assetPaths = collect(File::files(public_path('assets/ambassadors')))
            ->map(fn (SplFileInfo $file): string => 'assets/ambassadors/'.$file->getFilename())
            ->values()
            ->all();

        if ($assetPaths === []) {
            return;
        }

        $images = DB::table('ambassador_images')->orderBy('ambassador_id')->orderBy('sort_order')->get();

        foreach ($images as $index => $image) {
            DB::table('ambassador_images')
                ->where('id', $image->id)
                ->update([
                    'image_path' => $assetPaths[$index % count($assetPaths)],
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ambassador_images')->update([
            'image_path' => 'ambassadors/placeholder.svg',
        ]);
    }
};
