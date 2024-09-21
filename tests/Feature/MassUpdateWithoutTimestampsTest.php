<?php

use Iksaku\Laravel\MassUpdate\Tests\App\Models\CustomUser;
use Iksaku\Laravel\MassUpdate\Tests\App\Models\Expense;
use Iksaku\Laravel\MassUpdate\Tests\App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

it('does not update timestamp of touched records without events', function () {
    $this->travelTo(now()->subDay());

    [$jorge, $gladys] = User::factory()->createMany([
        ['name' => 'Jorge Gonzales'],
        ['name' => 'Gladys Martines'],
        ['name' => 'Elena González'],
    ]);

    $this->travelBack();

    DB::enableQueryLog();

    User::withoutTimestamps(function () use ($jorge, $gladys) {
        User::query()->massUpdate([
            ['id' => $jorge->id, 'name' => 'Jorge González'],
            ['id' => $gladys->id, 'name' => 'Gladys Martínez'],
        ]);
    });

    expect(
        User::query()->where('updated_at', '>=', now()->startOfDay())->count()
    )->toBe(0);

    expect(
        User::query()->where('updated_at', '<', now())->count()
    )->toBe(3);

    expect(Arr::first(DB::getQueryLog())['query'])->not()->toContain('updated_at');
});

it('does not update custom timestamp column of touched records without events', function () {
    $this->travelTo(now()->subDay());

    [$jorge, $gladys] = CustomUser::factory()->createMany([
        ['name' => 'Jorge Gonzales'],
        ['name' => 'Gladys Martines'],
        ['name' => 'Elena González'],
    ]);

    $this->travelBack();

    DB::enableQueryLog();

    CustomUser::withoutTimestamps(function () use ($jorge, $gladys) {
        CustomUser::query()->massUpdate([
            ['id' => $jorge->id, 'name' => 'Jorge González'],
            ['id' => $gladys->id, 'name' => 'Gladys Martínez'],
        ]);
    });

    expect(
        CustomUser::query()->where('custom_updated_at', '>=', now()->startOfDay())->count()
    )->toBe(0);

    expect(
        CustomUser::query()->where('custom_updated_at', '<', now())->count()
    )->toBe(3);

    expect(Arr::first(DB::getQueryLog())['query'])->not()->toContain('custom_updated_at');
});

it('does not touch update timestamp if model does not use it even without events', function () {
    [$a, $b] = Expense::factory()->createMany([
        ['total' => 20],
        ['total' => 4],
    ]);

    DB::enableQueryLog();

    Expense::withoutTimestamps(function () use ($a, $b) {
        Expense::query()->massUpdate([
            ['id' => $a->id, 'total' => 4],
            ['id' => $b->id, 'total' => 20],
        ]);
    });

    expect(Expense::all())->sequence(
        fn ($expense) => $expense->total->toBe(4.0),
        fn ($expense) => $expense->total->toBe(20.0),
    );

    expect(Arr::first(DB::getQueryLog())['query'])->not->toContain('updated_at');
});
