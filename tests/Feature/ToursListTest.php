<?php

use App\Models\Tour;
use App\Models\Travel;
use function Pest\Laravel\get;

beforeAll(function () {
    $this->travel = Travel::factory()->create();
});

it('returns a list of tours from a travel based on slug', function () {
    $tour = Tour::factory()->create([
        'travel_id' => $this->travel->id,
    ]);

    get(route('api.v1.tours', $this->travel))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment([
            'id' => $tour->id,
        ]);
});

it('returns a tour with the correct pricing', function () {
    Tour::factory()->create([
        'travel_id' => $this->travel->id,
        'price' => '123.45',
    ]);

    get(route('api.v1.tours', $this->travel))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment([
            'price' => '123.45',
        ]);
});

it('returns tours in a paginated list', function () {
    Tour::factory(16)->create([
        'travel_id' => $this->travel->id,
    ]);

    get(route('api.va.tours', $this->travel))
        ->assertOk()
        ->assertJsonCount(15, 'data')
        ->assertJsonPath('meta.last_page', 2);
});
