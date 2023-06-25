<?php

use App\Models\User;
use function Pest\Faker\fake;
use function Pest\Laravel\postJson;

it('returns access token with valid user credentials', function () {
    $user = User::factory()->create();

    postJson(route('api.v1.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonStructure(['access_token']);
});

it('returns a 422 error when invalid user credentials are provided', function () {
    postJson(route('api.v1.login'), [
        'email' => fake()->email(),
        'password' => 'password',
    ])
        ->assertUnprocessable();
});
