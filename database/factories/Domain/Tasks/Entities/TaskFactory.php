<?php

namespace Database\Factories\Domain\Tasks\Entities;

use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Enums\TaskStatus;
use App\Domain\Users\Entities\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status' => TaskStatus::PENDING,
            'created_by' => User::factory(),
            'assigned_to' => User::factory(),
        ];
    }
}
