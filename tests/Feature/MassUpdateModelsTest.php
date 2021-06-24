<?php

use Iksaku\Laravel\MassUpdate\Exceptions\MassUpdatingAndFilteringModelUsingTheSameColumn;
use Iksaku\Laravel\MassUpdate\Exceptions\UnexpectedModelClassException;
use Iksaku\Laravel\MassUpdate\Tests\App\Models\CustomUser;
use Iksaku\Laravel\MassUpdate\Tests\App\Models\Expense;
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

it('fails when trying to trying to update an unexpected model class', function (string $impostor) {
    $this->expectException(UnexpectedModelClassException::class);

    User::query()->massUpdate([
        $impostor::factory()->create()
    ]);
})->with([
    'Completely Different Model Class' => [Expense::class],
    'Extended Model Class' => [CustomUser::class],
]);
