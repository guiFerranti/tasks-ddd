<?php

namespace Database\Factories\Domain\Users\Entities;

use App\Domain\Users\Entities\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'cpf' => $this->faker->numerify('###.###.###-##'),
            'password' => bcrypt('password'),
            'role' => 'user',
        ];
    }
}
