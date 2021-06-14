<?php

namespace iksaku\Laravel\MassUpdate\Tests\Database\Factories;

use iksaku\Laravel\MassUpdate\Tests\App\Models\CustomUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomUserFactory extends Factory
{
    protected $model = CustomUser::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
