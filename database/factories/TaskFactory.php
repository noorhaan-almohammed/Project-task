<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Task::class;

    public function definition()
    {
        return [
            'project_id' => $this->faker->numberBetween(1, 10),
            'user_id' => $this->faker->numberBetween(1, 10),
            'title' => $this->faker->sentence,
            'description' => $this->faker->text,
            'status' => $this->faker->randomElement(['New', 'In Progress', 'Pending', 'Completed','In Testing' , 'Successed' , 'Failed']),
            'priority' => $this->faker->randomElement(['Low', 'Medium', 'High']),
            'execute_time' => $this->faker->numberBetween(1, 10),
            'due_date' => $this->faker->date,
            'start_date' => $this->faker->date,
            'tester_note' => $this->faker->optional()->text,
        ];
    }
}
