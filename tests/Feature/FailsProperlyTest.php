<?php

use Iksaku\Laravel\MassUpdate\Exceptions\EmptyUniqueByException;
use Iksaku\Laravel\MassUpdate\Exceptions\MassUpdatingAndFilteringModelUsingTheSameColumn;
use Iksaku\Laravel\MassUpdate\Exceptions\MissingFilterableColumnsException;
use Iksaku\Laravel\MassUpdate\Exceptions\OrphanValueException;
use Iksaku\Laravel\MassUpdate\Exceptions\RecordWithoutFilterableColumnsException;
use Iksaku\Laravel\MassUpdate\Exceptions\RecordWithoutUpdatableValuesException;
use Iksaku\Laravel\MassUpdate\Exceptions\UnexpectedModelClassException;
use Iksaku\Laravel\MassUpdate\Tests\App\Models\CustomUser;
use Iksaku\Laravel\MassUpdate\Tests\App\Models\Expense;
use Iksaku\Laravel\MassUpdate\Tests\App\Models\User;

it('returns 0 when no updatable records are given', function () {
    $result = User::query()->massUpdate([]);

    expect($result)->toBe(0);
});

it('fails when no columns are given to filter record values', function () {
    $this->expectException(EmptyUniqueByException::class);

    User::query()->massUpdate([
        ['id' => 1, 'name' => 'Jorge'],
    ], []);
});

it('fails when no filterable columns are found in a record', function () {
    $this->expectException(RecordWithoutFilterableColumnsException::class);

    User::query()->massUpdate([
        ['name' => 'Jorge'],
    ]);
});

it('fails when filterable columns are missing in a record', function () {
    $this->expectException(MissingFilterableColumnsException::class);

    User::query()->massUpdate(
        values: [['id' => 1, 'name' => 'Jorge']],
        uniqueBy: ['id', 'username']
    );
});

it('fails when no updatable values are found in a record', function () {
    $this->expectException(RecordWithoutUpdatableValuesException::class);

    User::query()->massUpdate([
        ['id' => 1],
    ]);
});

it('fails when an orphan value is found in a record', function () {
    $this->expectException(OrphanValueException::class);

    User::query()->massUpdate([
        ['id' => 1, 'Jorge González'],
    ]);
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
        $impostor::factory()->create(),
    ]);
})->with([
    'Completely Different Model Class' => [Expense::class],
    'Extended Model Class' => [CustomUser::class],
]);
