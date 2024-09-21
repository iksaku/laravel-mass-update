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
            'username' => $this->faker->userName,
            'name' => $this->faker->name,
        ];
    }

    public function canCode(bool $value = true): static
    {
        return $this->state([
            'can_code' => $value,
        ]);
    }
}
