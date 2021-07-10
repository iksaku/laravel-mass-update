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

it('can chain other query statements', function () {
    $this->travelTo(now()->startOfDay());

    User::factory()->count(10)->create();

    $this->travelBack();

    User::query()
        ->where('id', '<', 5)
        ->massUpdate([
            ['id' => 1, 'name' => 'Updated User'],
            ['id' => 4, 'name' => 'Another Updated User'],
            ['id' => 5, 'name' => 'Ignored User'],
            ['id' => 10, 'name' => 'Another Ignored User'],
        ]);

    expect(
        User::query()->where('updated_at', '>', now()->startOfDay())->count()
    )->toBe(2);
});

it('can update multiple value types', function (string $attribute, mixed $value) {
    User::factory()->create();

    User::query()->massUpdate(
        values: [[
            'id' => 1,
            $attribute => $value,
        ]]
    );

    expect(User::query()->first()->getAttribute($attribute))->toEqual($value);
})->with([
    'int' => ['rank', 1],
    'null' => ['name', null],
    'string' => ['name', 'Jorge González'],
    'bool (true)' => ['can_code', true],
    'bool (false)' => ['can_code', false],
]);
