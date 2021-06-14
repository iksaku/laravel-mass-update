<?php

use iksaku\Laravel\MassUpdate\Exceptions\MassUpdatingAndFilteringModelUsingTheSameColumn;
use iksaku\Laravel\MassUpdate\Tests\App\Models\User;

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

it('fails when model is trying to update and filter on the same column', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $user->fill([
        'id' => 2,
        'name' => 'Jorge',
    ]);

    $this->expectException(MassUpdatingAndFilteringModelUsingTheSameColumn::class);

    User::query()->massUpdate([$user]);
});
