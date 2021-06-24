<?php

use Iksaku\Laravel\MassUpdate\Tests\App\Models\CustomUser;
use Iksaku\Laravel\MassUpdate\Tests\App\Models\Expense;
use Iksaku\Laravel\MassUpdate\Tests\App\Models\User;

it('updates timestamps of touched records', function () {
    $this->travelTo(now()->subDay());

    User::factory()->createMany([
        ['name' => 'Jorge Gonzales'],
        ['name' => 'Elena Gonzales'],
        ['name' => 'Gladys Martinez'],
    ]);

    $this->travelBack();

    DB::enableQueryLog();

    User::query()->massUpdate([
        ['id' => 1, 'name' => 'Jorge González'],
        ['id' => 2, 'name' => 'Elena González'],
    ]);

    expect(
        User::query()->where('updated_at', '>=', now()->startOfDay())->count()
    )->toBe(2);

    expect(
        User::query()->where('updated_at', '<', now())->count()
    )->toBe(1);

    expect(Arr::first(DB::getQueryLog())['query'])->toContain('updated_at');
});

it('updates custom timestamp column of touched records', function () {
    $this->travelTo(now()->subDay());

    CustomUser::factory()->createMany([
        ['name' => 'Jorge Gonzales'],
        ['name' => 'Elena Gonzales'],
        ['name' => 'Gladys Martinez'],
    ]);

    $this->travelBack();

    DB::enableQueryLog();

    CustomUser::query()->massUpdate([
        ['id' => 1, 'name' => 'Jorge González'],
        ['id' => 2, 'name' => 'Elena González'],
    ]);

    expect(
        CustomUser::query()->where('custom_updated_at', '>=', now()->startOfDay())->count()
    )->toBe(2);

    expect(
        CustomUser::query()->where('custom_updated_at', '<', now())->count()
    )->toBe(1);

    expect(Arr::first(DB::getQueryLog())['query'])->toContain('custom_updated_at');
});

it('does not touch update timestamp if model does not use it', function () {
    Expense::factory()->createMany([
        ['total' => 20],
        ['total' => 4],
    ]);

    DB::enableQueryLog();

    Expense::query()->massUpdate([
        ['id' => 1, 'total' => 4],
        ['id' => 2, 'total' => 20],
    ]);

    expect(Expense::all())->sequence(
        fn ($expense) => $expense->total->toBe(4.0),
        fn ($expense) => $expense->total->toBe(20.0),
    );

    expect(Arr::first(DB::getQueryLog())['query'])->not->toContain('updated_at');
});
