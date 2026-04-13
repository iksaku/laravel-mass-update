<?php

use Iksaku\Laravel\MassUpdate\Tests\App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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

it('can use a different filter column', function () {
    User::factory()->createMany([
        ['username' => 'iksaku', 'name' => 'Jorge Gonzalez'],
        ['username' => 'gm_mtz', 'name' => 'Gladys Martines'],
    ]);

    DB::enableQueryLog();

    User::query()->massUpdate(
        values: [
            ['username' => 'iksaku', 'name' => 'Jorge González'],
            ['username' => 'gm_mtz', 'name' => 'Gladys Martínez'],
        ],
        uniqueBy: 'username'
    );

    expect(User::all())->sequence(
        fn ($user) => $user->name->toEqual('Jorge González'),
        fn ($user) => $user->name->toEqual('Gladys Martínez'),
    );

    expect(Arr::first(DB::getQueryLog())['query'])->toContain('name');
});

it('can use multiple filter columns', function () {
    User::factory()->createMany([
        ['username' => 'iksaku', 'name' => 'Jorge Gonzalez'],
        ['username' => 'gm_mtz', 'name' => 'Gladys Martines'],
    ]);

    DB::enableQueryLog();

    User::query()->massUpdate(
        values: [
            ['id' => 1, 'username' => 'iksaku', 'name' => 'Jorge González'],
            ['id' => 2, 'username' => 'gm_mtz', 'name' => 'Gladys Martínez'],
        ],
        uniqueBy: ['id', 'username']
    );

    expect(User::all())->sequence(
        fn ($user) => $user->name->toEqual('Jorge González'),
        fn ($user) => $user->name->toEqual('Gladys Martínez'),
    );

    expect(Arr::first(DB::getQueryLog())['query'])
        ->toContain('id')
        ->toContain('name');
});

it('can specify custom mass-update key', function () {
    $customKeyUser = new class extends User
    {
        protected $table = 'users';

        public function getMassUpdateKeyName(): string|array|null
        {
            return 'username';
        }
    };

    User::factory()->createMany([
        ['username' => 'iksaku', 'name' => 'Jorge Gonzalez'],
        ['username' => 'gm_mtz', 'name' => 'Gladys Martines'],
    ]);

    DB::enableQueryLog();

    $customKeyUser::query()->massUpdate(
        values: [
            ['username' => 'iksaku', 'name' => 'Jorge González'],
            ['username' => 'gm_mtz', 'name' => 'Gladys Martínez'],
        ]
    );

    expect(User::all())->sequence(
        fn ($user) => $user->name->toEqual('Jorge González'),
        fn ($user) => $user->name->toEqual('Gladys Martínez'),
    );

    expect(Arr::first(DB::getQueryLog())['query'])
        ->toContain('username')
        ->toContain('name')
        ->not->toContain('id');
});

it('can specify multiple custom mass-update keys', function () {
    $customKeyUser = new class extends User
    {
        protected $table = 'users';

        public function getMassUpdateKeyName(): string|array|null
        {
            return ['id', 'username'];
        }
    };

    User::factory()->createMany([
        ['username' => 'iksaku', 'name' => 'Jorge Gonzalez'],
        ['username' => 'gm_mtz', 'name' => 'Gladys Martines'],
    ]);

    DB::enableQueryLog();

    $customKeyUser::query()->massUpdate(
        values: [
            ['id' => 1, 'username' => 'iksaku', 'name' => 'Jorge González'],
            ['id' => 2, 'username' => 'gm_mtz', 'name' => 'Gladys Martínez'],
        ]
    );

    expect(User::all())->sequence(
        fn ($user) => $user->name->toEqual('Jorge González'),
        fn ($user) => $user->name->toEqual('Gladys Martínez'),
    );

    expect(Arr::first(DB::getQueryLog())['query'])
        ->toContain('id')
        ->toContain('username')
        ->toContain('name');
});
