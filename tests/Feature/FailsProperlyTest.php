<?php

use iksaku\Laravel\MassUpdate\Exceptions\EmptyUniqueByException;
use iksaku\Laravel\MassUpdate\Exceptions\OrphanValueException;
use iksaku\Laravel\MassUpdate\Exceptions\RecordWithoutFilterableColumnsException;
use iksaku\Laravel\MassUpdate\Exceptions\RecordWithoutUpdatableValuesException;
use iksaku\Laravel\MassUpdate\Tests\App\Models\User;

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
