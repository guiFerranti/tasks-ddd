<?php

namespace Database\Factories;

use App\Domain\Users\Entities\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
