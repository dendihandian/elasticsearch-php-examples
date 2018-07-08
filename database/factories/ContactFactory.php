<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\Contact::class, function (Faker\Generator $faker) {
    return [
        'owner_id' => function () { return factory(App\Models\User::class)->create()->id; },
        'first_name' => $faker->firstName,
        'middle_name' => null,
        'last_name' => $faker->lastName,
        'email' => $faker->safeEmail,
        'phone' => $faker->phoneNumber,
        'address' => $faker->address,
    ];
});
