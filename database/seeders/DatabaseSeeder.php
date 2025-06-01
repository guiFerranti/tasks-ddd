<?php

namespace Database\Seeders;

use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Enums\TaskStatus;
use App\Domain\Users\Entities\User;
use App\Domain\Users\Enums\UserRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'role' => UserRole::ADMIN->value
        ]);

        $regularUser = User::factory()->create([
            'name' => 'Usuário Regular',
            'email' => 'user@example.com',
            'password' => bcrypt('user123'),
            'role' => UserRole::USER->value
        ]);

        $users = User::factory()->count(5)->create();

        Task::factory()->count(3)->create([
            'created_by' => $admin->id,
            'assigned_to' => $regularUser->id,
            'status' => TaskStatus::COMPLETED->value
        ]);

        Task::factory()->count(2)->create([
            'created_by' => $regularUser->id,
            'assigned_to' => $admin->id,
            'status' => TaskStatus::IN_PROGRESS->value
        ]);

        Task::factory()->count(4)->create([
            'status' => TaskStatus::PENDING->value
        ]);

        Task::factory()->count(2)->create([
            'deleted_at' => now()
        ]);
    }
}
