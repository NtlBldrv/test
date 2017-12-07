<?php

use Faker\Generator as Faker;

$factory->define(App\Stream::class, function (Faker $faker) {

    return [
        'channel_id'   => 1,
        'stream_id'    => random_int(111111, 99999999),
        'viewer_count' => random_int(0, 999999),
        'live'         => true,
        'game_id'      => 1,
        'service_id'   => 1,
    ];
});
