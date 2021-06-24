<?php

namespace Iksaku\Laravel\MassUpdate\Tests\Database\Factories;

use Iksaku\Laravel\MassUpdate\Tests\App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
