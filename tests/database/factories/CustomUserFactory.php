<?php

namespace Iksaku\Laravel\MassUpdate\Tests\Database\Factories;

use Iksaku\Laravel\MassUpdate\Tests\App\Models\CustomUser;
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
