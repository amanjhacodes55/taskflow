<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => ucfirst(fake()->words(fake()->numberBetween(2, 6), true)),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'due_at' => fake()->optional(0.7)->dateTimeBetween('-3 days', '+14 days'),
        ];
    }

    public function todo(): static
    {
        return $this->state(['status' => TaskStatus::Todo]);
    }

    public function done(): static
    {
        return $this->state(['status' => TaskStatus::Done]);
    }

    /** Due a given number of hours from now (negative = already overdue). */
    public function dueIn(int $hours): static
    {
        return $this->state(['due_at' => now()->addHours($hours)]);
    }
}
