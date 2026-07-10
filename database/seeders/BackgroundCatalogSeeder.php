<?php

namespace Database\Seeders;

use App\Models\MemorialPagePamphletBackground;
use App\Models\MemorialPagePamphletBackgroundCollection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BackgroundCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $collections = collect([
            ['slug' => 'kids', 'name' => 'Kids', 'description' => '0 to 12 years'],
            ['slug' => 'teens', 'name' => 'Teens', 'description' => '13 to 18 years'],
            ['slug' => 'adults', 'name' => 'Adults', 'description' => '19 years and above'],
        ])->mapWithKeys(fn (array $collection): array => [
            $collection['slug'] => MemorialPagePamphletBackgroundCollection::query()->updateOrCreate(
                ['slug' => $collection['slug']],
                $collection
            ),
        ]);

        collect(File::allFiles(public_path('assets/banners')))
            ->filter(fn (\SplFileInfo $file): bool => in_array(
                Str::lower($file->getExtension()),
                ['webp', 'png', 'jpg', 'jpeg'],
                true
            ))
            ->map(fn (\SplFileInfo $file): string => $file->getPathname())
            ->sort()
            ->values()
            ->each(function (string $absolutePath, int $index) use ($collections): void {
                $relativePath = str_replace(public_path().'/', '', $absolutePath);
                $normalizedPath = Str::lower($relativePath);

                $slug = str_contains($normalizedPath, 'kids') ? 'kids' : (
                    str_contains($normalizedPath, 'teens') ? 'teens' : 'adults'
                );

                MemorialPagePamphletBackground::query()->updateOrCreate(
                    ['image_path' => $relativePath],
                    [
                        'collection_id' => $collections[$slug]->id,
                        'name' => Str::of(pathinfo($absolutePath, PATHINFO_FILENAME))
                            ->replace(['_', '-'], ' ')
                            ->title()
                            ->toString(),
                        'thumbnail_path' => $relativePath,
                        'is_active' => true,
                        'sort_order' => $index,
                    ]
                );
            });
    }
}
