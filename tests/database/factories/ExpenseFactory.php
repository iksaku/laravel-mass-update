<?php

namespace iksaku\Laravel\MassUpdate\Tests\Database\Factories;

use iksaku\Laravel\MassUpdate\Tests\App\Models\Expense;
use iksaku\Laravel\MassUpdate\Tests\App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'year' => $this->faker->year(),
            'quarter' => 'Q' . $this->faker->numberBetween(0, 4),
            'total' => $this->faker->randomFloat(2, 10, 100),
        ];
    }
}
