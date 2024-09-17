<?php

namespace Database\Factories;

use App\Models\ProjectUser;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectUserFactory extends Factory
{
    public function definition()
    {
        return [
            'project_id' => $this->faker->numberBetween(1, 10),
            'user_id' => $this->faker->numberBetween(1, 10),
            'role' => $this->faker->randomElement(['Manager', 'Developer', 'Tester']),
            'contribution_hours' => $this->faker->numberBetween(0, 100),
            'last_activity' => $this->faker->optional()->dateTime,
        ];
    }
}
