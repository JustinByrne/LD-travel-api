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

it('returns a 401 error when an unauthenticated user attempt to create a new travel', function () {
    postJson(route('api.v1.admin.travels.store'))
        ->assertUnauthorized();
});

it('returns a 403 error when an unauthorised user attempt to create a new travel', function () {
    $this->user->roles()->attach($this->editor_id);

    actingAs($this->user)
        ->postJson(route('api.v1.admin.travels.store'))
        ->assertForbidden();
});

it('creates a new travel with valid data', function () {
    $this->user->roles()->attach($this->admin_id);

    $data = [
        'name' => fake()->word(),
        'is_public' => fake()->boolean(),
        'description' => fake()->paragraph(),
        'number_of_days' => fake()->randomDigitNotNull(),
    ];

    actingAs($this->user)
        ->postJson(route('api.v1.admin.travels.store'), $data)
        ->assertCreated()
        ->assertJsonFragment([
            'name' => $data['name'],
        ]);
});

it('returns a 422 error when trying to create a new travel with invalid data', function ($data) {
    $this->user->roles()->attach($this->admin_id);

    actingAs($this->user)
        ->postJson(route('api.v1.admin.travels.store'), $data)
        ->assertUnprocessable();
})->with([
    'all required fields are missing' => [
        [],
    ],
    'incorrectly formatted data' => [
        [
            'is_public' => 'this is not a boolean',
            'number_of_days' => 'this is not an integer',
        ],
    ],
]);

it('updates an existing travel with valid data when the user is an editor', function () {
    $this->user->roles()->attach($this->editor_id);

    $data = [
        'name' => fake()->word(),
        'is_public' => true,
        'description' => fake()->paragraph(),
        'number_of_days' => fake()->randomDigitNotNull(),
    ];

    actingAs($this->user)
        ->putJson(route('api.v1.admin.travels.update', $this->travel), $data)
        ->assertOk();

    get(route('api.v1.travels'))
        ->assertJsonFragment([
            'name' => $data['name'],
        ]);
});

it('returns a 422 error when trying to edit an existing travel with invalid data', function ($data) {
    $this->user->roles()->attach($this->editor_id);

    actingAs($this->user)
        ->putJson(route('api.v1.admin.travels.update', $this->travel), $data)
        ->assertUnprocessable();
})->with([
    'all required fields are missing' => [
        [],
    ],
    'incorrectly formatted data' => [
        [
            'is_public' => 'this is not a boolean',
            'number_of_days' => 'this is not an integer',
        ],
    ],
]);
