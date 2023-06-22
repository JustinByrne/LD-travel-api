<?php

use App\Models\Travel;
use function Pest\Laravel\get;

it('returns a page of 15 travels', function () {
    Travel::factory(16)->create([
        'is_public' => true,
    ]);

    get(route('api.v1.travels'))
        ->assertOk()
        ->assertJsonCount(15, 'data')
        ->assertJsonPath('meta.last_page', 2);
});

it('only returns travels that are public', function () {
    $public = Travel::factory()->create([
        'is_public' => true,
    ]);

    $notPublic = Travel::factory()->create([
        'is_public' => false,
    ]);

    get(route('api.v1.travels'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment([
            'id' => $public->id,
        ])
        ->assertJsonMissing([
            'id' => $notPublic->id,
        ]);
});
