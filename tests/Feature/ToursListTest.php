<?php

use App\Models\Tour;
use App\Models\Travel;
use function Pest\Laravel\get;

beforeEach(function () {
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

    get(route('api.v1.tours', $this->travel))
        ->assertOk()
        ->assertJsonCount(15, 'data')
        ->assertJsonPath('meta.last_page', 2);
});

it('returns the list of tours in order by the starting_at field by default', function () {
    $currentTour = Tour::factory()->create([
        'travel_id' => $this->travel->id,
        'starting_at' => now(),
        'ending_at' => now()->addDay(),
    ]);

    $futureTour = Tour::factory()->create([
        'travel_id' => $this->travel->id,
        'starting_at' => now()->addDays(2),
        'ending_at' => now()->addDays(3),
    ]);

    get(route('api.v1.tours', $this->travel))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $currentTour->id)
        ->assertJsonPath('data.1.id', $futureTour->id);
});

it('returns tour in specified order when sort query strings in the url', function () {
    $expensiveTour = Tour::factory()->create([
        'travel_id' => $this->travel->id,
        'price' => 200,
    ]);

    $cheapCurrentTour = Tour::factory()->create([
        'travel_id' => $this->travel->id,
        'price' => 100,
        'starting_at' => now(),
        'ending_at' => now()->addDay(),
    ]);

    $cheapFutureTour = Tour::factory()->create([
        'travel_id' => $this->travel->id,
        'price' => 100,
        'starting_at' => now()->addDays(2),
        'ending_at' => now()->addDays(3),
    ]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'sortBy' => 'price',
        'sortOrder' => 'asc',
    ]))
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.id', $cheapCurrentTour->id)
        ->assertJsonPath('data.1.id', $cheapFutureTour->id)
        ->assertJsonPath('data.2.id', $expensiveTour->id);
});
