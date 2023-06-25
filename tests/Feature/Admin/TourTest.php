<?php

use App\Models\Role;
use App\Models\Travel;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use function Pest\Faker\fake;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->user = User::factory()->create();
    $this->travel = Travel::factory()->create();

    $this->editor_id = Role::where('name', 'editor')->value('id');
    $this->admin_id = Role::where('name', 'admin')->value('id');
});

it('returns a 401 error when an unauthenticated user accesses the admin tours', function () {
    postJson(route('api.v1.admin.tours.store', $this->travel))
        ->assertUnauthorized();
});

it('returns a 403 error when an unauthorised user accessed the admin travels', function () {
    $this->user->roles()->attach($this->editor_id);

    actingAs($this->user)
        ->postJson(route('api.v1.admin.tours.store', $this->travel))
        ->assertForbidden();
});

it('creates a new tour with valid data', function () {
    $this->user->roles()->attach($this->admin_id);

    $data = [
        'name' => fake()->word(),
        'starting_at' => now()->toDateString(),
        'ending_at' => now()->addDay()->toDateString(),
        'price' => fake()->randomFloat(2, 1, 30),
    ];

    actingAs($this->user)
        ->postJson(route('api.v1.admin.tours.store', $this->travel), $data)
        ->assertCreated()
        ->assertJsonFragment([
            'name' => $data['name'],
        ]);

    get(route('api.v1.tours', $this->travel))
        ->assertJsonFragment([
            'name' => $data['name'],
        ]);
});

it('returns a 422 error when trying to create a new tour with invalid data', function ($data) {
    $this->user->roles()->attach($this->admin_id);

    actingAs($this->user)
        ->postJson(route('api.v1.admin.tours.store', $this->travel), $data)
        ->assertUnprocessable();
})->with([
    'all required fields are missing' => [
        [],
    ],
    'incorrectly formatted data' => [
        [
            'starting_at' => 'this is not a date',
            'ending_at' => 'this is not a date',
            'price' => 'this is not a integer',
        ],
    ],
]);
