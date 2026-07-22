<?php

namespace Database\Factories;

use App\Enums\CrmFollowUpStatus;
use App\Enums\CrmFollowUpType;
use App\Models\CrmFollowUp;
use App\Models\CrmOrganisation;
use App\Models\StaffUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<CrmFollowUp>
 */
class CrmFollowUpFactory extends Factory
{
    protected $model = CrmFollowUp::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_type' => CrmOrganisation::class,
            'subject_id' => CrmOrganisation::factory(),
            'assigned_staff_user_id' => StaffUser::factory(),
            'type' => fake()->randomElement(CrmFollowUpType::cases()),
            'status' => CrmFollowUpStatus::Pending,
            'title' => fake()->sentence(4),
            'notes' => fake()->optional()->paragraph(),
            'due_at' => fake()->dateTimeBetween('-1 week', '+1 month'),
            'completed_at' => null,
        ];
    }

    public function forSubject(Model $subject): static
    {
        return $this->state(fn (array $attributes): array => [
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CrmFollowUpStatus::Pending,
            'due_at' => now()->subDays(3),
        ]);
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CrmFollowUpStatus::Done,
            'completed_at' => now(),
        ]);
    }
}
