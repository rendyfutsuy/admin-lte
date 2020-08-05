<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'username' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'last_online' => Carbon::now(),
        'remember_token' => random_str(10),
        'banned_at' => null,
    ];
});

$factory->state(User::class, 'needs_activation', function (Faker $faker) {
    return [
        'activation_code' => $faker->randomNumber(5),
        'email_verified_at' => null,
        'last_online' => null,
        'remember_token' => null,
    ];
});

$factory->state(User::class, 'activated', function (Faker $faker) {
    return [
        'activation_code' => null,
        'email_verified_at' => $faker->dateTime(),
        'last_online' => null,
    ];
});

$factory->state(User::class, 'banned', function (Faker $faker) {
    return [
        'banned_at' => $faker->dateTime(),
    ];
});

