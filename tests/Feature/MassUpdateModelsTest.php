<?php

use Iksaku\Laravel\MassUpdate\Tests\App\Models\User;

it('can process array of changed models', function () {
    /** @var User[] $users */
    $users = User::factory()->count(2)->create();

    $users[0]->name = 'Jorge';
    $users[1]->name = 'Elena';

    User::query()->massUpdate($users);

    expect(User::all())->sequence(
        fn ($user) => $user->name->toEqual('Jorge'),
        fn ($user) => $user->name->toEqual('Elena'),
    );
});

it('skips models that have not changed', function () {
    /** @var User[] $users */
    $users = User::factory()->count(2)->create();

    $users[0]->name = 'Jorge';

    expect(User::query()->massUpdate($users))->toBe(1);

    expect(User::query()->first()->name)->toBe('Jorge');
});
