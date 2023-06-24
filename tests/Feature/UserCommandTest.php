<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use function Pest\Faker\fake;
use function Pest\Laravel\artisan;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('has a user:create command that adds a new admin', function () {
    artisan('users:create')
        ->expectsQuestion('Name of the new user', 'Justin Byrne')
        ->expectsQuestion('Email of the new user', 'justin@justinbyrne.dev')
        ->expectsQuestion('Password of the new user', 'SuperSecretPassword1234!')
        ->expectsQuestion('Role of the new user', 'admin')
        ->expectsOutput('Admin user justin@justinbyrne.dev created successfully')
        ->assertSuccessful();
});

it('has a user:create command that adds a new editor', function () {
    artisan('users:create')
        ->expectsQuestion('Name of the new user', 'Justin Byrne')
        ->expectsQuestion('Email of the new user', 'justin@justinbyrne.dev')
        ->expectsQuestion('Password of the new user', 'SuperSecretPassword1234!')
        ->expectsQuestion('Role of the new user', 'editor')
        ->expectsOutput('Editor user justin@justinbyrne.dev created successfully')
        ->assertSuccessful();
});

it('returns validation errors when incorrect data is provided', function ($values, $errors) {
    User::factory()->create([
        'email' => 'justin@justinbyrne.dev',
    ]);

    $command = artisan('users:create')
        ->expectsQuestion('Name of the new user', $values['name'] ?? '')
        ->expectsQuestion('Email of the new user', $values['email'] ?? '')
        ->expectsQuestion('Password of the new user', $values['password'] ?? '')
        ->expectsQuestion('Role of the new user', $values['role'] ?? '');

    foreach ($errors as $error) {
        $command->expectsOutputToContain($error);
    }

    $command->assertFailed();
})->with([
    'all required fields empty' => [
        [],
        [
            'The name field is required.',
            'The email field is required.',
            'The password field is required.',
        ],
    ],
    'incorrect formats' => [
        [
            'name' => fake()->regexify('[A-Z]{260}'),
            'email' => 'not an email',
            'password' => '1234',
        ],
        [
            'The name field must not be greater than 255 characters.',
            'The email field must be a valid email address.',
            'The password field must be at least 8 characters.',
        ],
    ],
    'user already exists' => [
        [
            'email' => 'justin@justinbyrne.dev',
        ],
        [
            'The email has already been taken',
        ],
    ],
]);
