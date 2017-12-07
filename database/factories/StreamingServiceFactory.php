<?php

use Faker\Generator as Faker;

$factory->define(App\StreamingService::class, function (Faker $faker) {
    return [
        'title' => $faker->title,
    ];
});
