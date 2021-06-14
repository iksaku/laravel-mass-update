<?php

use iksaku\Laravel\MassUpdate\Tests\App\Models\User;

it('updates multiple records in a single query', function () {
    User::factory()->createMany([
        ['name' => 'Jorge Gonzales'],
        ['name' => 'Elena Gonzales'],
    ]);

    User::query()->massUpdate([
        ['id' => 1, 'name' => 'Jorge González'],
        ['id' => 2, 'name' => 'Elena González'],
    ]);

    expect(User::all())->sequence(
        fn ($user) => $user->name->toEqual('Jorge González'),
        fn ($user) => $user->name->toEqual('Elena González'),
    );
});

it('uses model\'s default key column if no other filtering columns are provided', function () {
    User::factory()->createMany([
        ['name' => 'Jorge Gonzales'],
        ['name' => 'Elena Gonzales'],
    ]);

    DB::enableQueryLog();

    User::query()->massUpdate([
        ['id' => 1, 'name' => 'Jorge González'],
        ['id' => 2, 'name' => 'Elena González'],
    ]);

    expect(User::all())->sequence(
        fn ($user) => $user->name->toEqual('Jorge González'),
        fn ($user) => $user->name->toEqual('Elena González'),
    );

    expect(Arr::first(DB::getQueryLog())['query'])->toContain('id');
});
