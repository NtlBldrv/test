<?php

use Faker\Generator as Faker;

$factory->define(App\Stream::class, function (Faker $faker) {
    return [
        'channel_id'   => $faker->channel_id->unique(),
        'stream_id'    => $faker->stream_id->unique(),
        'viewer_count' => $faker->viewer_count,
        'active'       => true,
        'game_id'      => function () {
            return factory(\App\Game::class)->create()->id;
        },
        'service_id'   => function () {
            return factory(\App\StreamingService::class)->create()->id;
        },
    ];
});
