<?php

namespace Database\Factories;

use App\Models\Ambassador;
use App\Models\AmbassadorImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;

/**
 * @extends Factory<AmbassadorImage>
 */
class AmbassadorImageFactory extends Factory
{
    /**
     * @return list<string>
     */
    private function ambassadorAssetPaths(): array
    {
        return collect(File::files(public_path('assets/ambassadors')))
            ->map(fn (\SplFileInfo $file): string => 'assets/ambassadors/'.$file->getFilename())
            ->values()
            ->all();
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ambassador_id' => Ambassador::factory(),
            'image_path' => fake()->randomElement($this->ambassadorAssetPaths()),
            'caption' => $this->faker->optional()->sentence(),
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
