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
        ->assertJsonFragment(['id' => $tour->id]);
});

it('returns a tour with the correct pricing', function () {
    Tour::factory()->create([
        'travel_id' => $this->travel->id,
        'price' => '123.45',
    ]);

    get(route('api.v1.tours', $this->travel))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['price' => '123.45']);
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

it('returns a list of tours that are filtered by price', function () {
    $cheapTour = Tour::factory()->create([
        'travel_id' => $this->travel->id,
        'price' => 100,
    ]);

    $expensiveTour = Tour::factory()->create([
        'travel_id' => $this->travel->id,
        'price' => 200,
    ]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'priceFrom' => 100,
    ]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $cheapTour->id])
        ->assertJsonFragment(['id' => $expensiveTour->id]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'priceFrom' => 150,
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonMissing(['id' => $cheapTour->id])
        ->assertJsonFragment(['id' => $expensiveTour->id]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'priceFrom' => 250,
    ]))
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonMissing(['id' => $cheapTour->id])
        ->assertJsonMissing(['id' => $expensiveTour->id]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'priceTo' => 200,
    ]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $cheapTour->id])
        ->assertJsonFragment(['id' => $expensiveTour->id]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'priceTo' => 150,
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['id' => $cheapTour->id])
        ->assertJsonMissing(['id' => $expensiveTour->id]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'priceTo' => 50,
    ]))
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonMissing(['id' => $cheapTour->id])
        ->assertJsonMissing(['id' => $expensiveTour->id]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'priceFrom' => 150,
        'priceTo' => 250,
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonMissing(['id' => $cheapTour->id])
        ->assertJsonFragment(['id' => $expensiveTour->id]);
});

it('returns a list of tours that are filtered by date', function () {
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

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'dateFrom' => now()->format('Y-m-d'),
    ]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $currentTour->id])
        ->assertJsonFragment(['id' => $futureTour->id]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'dateFrom' => now()->addDay()->format('Y-m-d'),
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonMissing(['id' => $currentTour->id])
        ->assertJsonFragment(['id' => $futureTour->id]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'dateFrom' => now()->addDays(5)->format('Y-m-d'),
    ]))
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonMissing(['id' => $currentTour->id])
        ->assertJsonMissing(['id' => $futureTour->id]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'dateTo' => now()->addDays(5)->format('Y-m-d'),
    ]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $currentTour->id])
        ->assertJsonFragment(['id' => $futureTour->id]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'dateTo' => now()->addDay()->format('Y-m-d'),
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['id' => $currentTour->id])
        ->assertJsonMissing(['id' => $futureTour->id]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'dateTo' => now()->subDay()->format('Y-m-d'),
    ]))
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonMissing(['id' => $currentTour->id])
        ->assertJsonMissing(['id' => $futureTour->id]);

    get(route('api.v1.tours', [
        'travel' => $this->travel,
        'dateFrom' => now()->addDay()->format('Y-m-d'),
        'dateTo' => now()->addDays(5)->format('Y-m-d'),
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonMissing(['id' => $currentTour->id])
        ->assertJsonFragment(['id' => $futureTour->id]);
});

it('returns a 422 error code when passing incorrect formats in the query string', function ($parameters) {
    $parameters['travel'] = $this->travel;

    get(route('api.v1.tours', $parameters))
        ->assertUnprocessable();
})->with([
    'incorrect date from format' => [
        [
            'dateFrom' => 'not a date format',
        ],
    ],
    'incorrect date to format' => [
        [
            'dateTo' => 'not a date format',
        ],
    ],
    'incorrect price from format' => [
        [
            'priceFrom' => 'not a integer',
        ],
    ],
    'incorrect price to format' => [
        [
            'priceTo' => 'not a integer',
        ],
    ],
]);
