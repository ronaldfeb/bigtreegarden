<?php

namespace Database\Factories;

use App\Enums\PamphletStatus;
use App\Models\MemorialPage;
use App\Models\MemorialPagePamphlet;
use App\Models\MemorialPagePamphletBackground;
use App\Models\MemorialPagePamphletStyle;
use App\Models\PersonOfInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemorialPagePamphlet>
 */
class MemorialPagePamphletFactory extends Factory
{
    protected bool $shouldCreateStyle = true;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'memorial_page_id' => MemorialPage::factory(),
            'background_id' => MemorialPagePamphletBackground::factory(),
            'heading' => fake()->sentence(3),
            'short_text' => fake()->paragraph(),
            'date_format' => 'd M Y',
            'image_shape' => 'square',
            'image_crop_mode' => 'cover',
            'uploaded_image_path' => 'pamphlets/images/'.fake()->uuid().'.jpg',
            'preview_image_path' => null,
            'status' => PamphletStatus::Draft,
            'paid_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (MemorialPagePamphlet $pamphlet): void {
            if (! $this->shouldCreateStyle) {
                return;
            }

            MemorialPagePamphletStyle::factory()->create([
                'pamphlet_id' => $pamphlet->id,
            ]);
        });
    }

    public function withoutStyle(): static
    {
        $factory = clone $this;
        $factory->shouldCreateStyle = false;

        return $factory;
    }

    public function guest(): static
    {
        return $this->state([
            'memorial_page_id' => MemorialPage::factory()->for(
                PersonOfInterest::factory()->state([
                    'created_by_user_id' => null,
                ])
            ),
        ]);
    }
}
