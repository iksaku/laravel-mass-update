<?php

use Iksaku\Laravel\MassUpdate\Tests\App\Models\User;

it('updates multiple records in a single query', function () {
    User::factory()->createMany([
        ['name' => 'Jorge Gonzales'],
        ['name' => 'Gladys Martines'],
    ]);

    User::query()->massUpdate([
        ['id' => 1, 'name' => 'Jorge González'],
        ['id' => 2, 'name' => 'Gladys Martínez'],
    ]);

    expect(User::all())->sequence(
        fn ($user) => $user->name->toEqual('Jorge González'),
        fn ($user) => $user->name->toEqual('Gladys Martínez'),
    );
});

it('uses model\'s default key column if no other filtering columns are provided', function () {
    User::factory()->createMany([
        ['name' => 'Jorge Gonzales'],
        ['name' => 'Gladys Martines'],
    ]);

    DB::enableQueryLog();

    User::query()->massUpdate([
        ['id' => 1, 'name' => 'Jorge González'],
        ['id' => 2, 'name' => 'Gladys Martínez'],
    ]);

    expect(User::all())->sequence(
        fn ($user) => $user->name->toEqual('Jorge González'),
        fn ($user) => $user->name->toEqual('Gladys Martínez'),
    );

    expect(Arr::first(DB::getQueryLog())['query'])->toContain('id');
});
