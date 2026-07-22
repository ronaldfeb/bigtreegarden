<?php

namespace Database\Factories;

use App\Enums\CrmInteractionType;
use App\Models\CrmInteraction;
use App\Models\CrmOrganisation;
use App\Models\StaffUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<CrmInteraction>
 */
class CrmInteractionFactory extends Factory
{
    protected $model = CrmInteraction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_type' => CrmOrganisation::class,
            'subject_id' => CrmOrganisation::factory(),
            'staff_user_id' => StaffUser::factory(),
            'type' => fake()->randomElement(CrmInteractionType::cases()),
            'summary' => fake()->sentence(),
            'body' => fake()->optional()->paragraph(),
            'occurred_at' => fake()->dateTimeBetween('-2 months'),
        ];
    }

    public function forSubject(Model $subject): static
    {
        return $this->state(fn (array $attributes): array => [
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
        ]);
    }
}
