<?php

namespace Database\Factories;

use App\Models\Background;
use App\Models\Pamphlet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pamphlet>
 */
class PamphletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'background_id' => Background::factory(),
            'status' => 'draft',
            'heading' => $this->faker->sentence(3),
            'person_full_name' => $this->faker->name(),
            'date_of_birth' => $this->faker->dateTimeBetween('-70 years', '-20 years'),
            'date_of_passing' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'date_format' => 'd M Y',
            'image_shape' => 'square',
            'image_crop_mode' => 'cover',
            'short_text' => $this->faker->paragraph(),
            'uploaded_image_path' => 'pamphlets/images/'.$this->faker->uuid().'.jpg',
            'public_slug' => Str::lower((string) Str::ulid()),
            'paid_at' => null,
        ];
    }
}
