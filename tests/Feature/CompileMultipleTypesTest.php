<?php

use Iksaku\Laravel\MassUpdate\Tests\App\Models\User;
use Illuminate\Support\Facades\DB;

it('can compile NULL values', function () {
    $user = User::factory()->create();

    DB::enableQueryLog();

    User::query()->massUpdate([
        $user->fill(['name' => null]),
    ]);

    expect(DB::getQueryLog()[0]['query'])->toContain('THEN null');

    expect($user->refresh()->name)->toBeNull();
});

it('can compile boolean values', function (bool $value) {
    $user = User::factory()->create([
        'can_code' => ! $value,
    ]);

    expect($user->can_code)->toBe(! $value);

    DB::enableQueryLog();

    User::query()->massUpdate([
        $user->fill(['can_code' => $value]),
    ]);

    expect(DB::getQueryLog()[0]['query'])->toContain("THEN $value");

    expect($user->refresh()->can_code)->toBe($value);
})->with([
    true,
    false,
]);

it('can compile numeric values', function (int $rank, float $height) {
    $user = User::factory()->create();

    expect($user)
        ->rank->toBeNull()
        ->height->toBeNull();

    DB::enableQueryLog();

    User::query()->massUpdate([
        $user->fill(compact('rank', 'height')),
    ]);

    expect(DB::getQueryLog()[0]['query'])->toContain('THEN 10', 'THEN 1.7');

    expect($user->refresh())
        ->rank->toBe($rank)
        ->height->toBe($height);
})->with([
    [
        'rank' => 10,
        'height' => 1.7,
    ],
]);

it('can compile strings', function (string $name) {
    $user = User::factory()->create();

    expect($user->name)->not->toBe($name);

    DB::enableQueryLog();
    User::query()->massUpdate([
        $user->fill(compact('name')),
    ]);

    expect($user->refresh()->name)->toBe($name);
})->with([
    'simple string' => 'Jorge González',
    'single quoted string' => "Jorge 'iksaku' González",
    'double quoted string' => 'Jorge "iksaku" González',
    'complex string' => '"\'Jorge \"\\\'iksaku\\\'\" González\'"',
]);
