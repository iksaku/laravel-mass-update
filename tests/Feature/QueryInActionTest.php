<?php

use Iksaku\Laravel\MassUpdate\Tests\App\Models\User;

it('updates multiple records in a single query', function () {
    [$jorge, $gladys] = User::factory()->createMany([
        ['name' => 'Jorge Gonzales'],
        ['name' => 'Gladys Martines'],
    ]);

    User::query()->massUpdate([
        ['id' => $jorge->id, 'name' => 'Jorge González'],
        ['id' => $gladys->id, 'name' => 'Gladys Martínez'],
    ]);

    expect(User::all())->sequence(
        fn ($user) => $user->name->toEqual('Jorge González'),
        fn ($user) => $user->name->toEqual('Gladys Martínez'),
    );
});

it('can chain other query statements', function () {
    [$a, $b, $c, $d] = User::factory()
        ->count(4)
        ->sequence(
            ['can_code' => true],
            ['can_code' => false],
        )
        ->create();

    $this->travelTo(now()->addSecond());

    User::query()
        ->where('can_code', true)
        ->massUpdate([
            ['id' => $a->id, 'name' => 'Updated User'],
            ['id' => $b->id, 'name' => 'Ignored User'],
            ['id' => $c->id, 'name' => 'Another Updated User'],
            ['id' => $d->id, 'name' => 'Another Ignored User'],
        ]);

    expect(
        User::query()->where('updated_at', '>=', now())->count()
    )->toBe(2);
});

it('can update multiple value types', function (string $attribute, mixed $value) {
    $user = User::factory()->create();

    User::query()->massUpdate(
        values: [[
            'id' => $user->id,
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
