<?php

return [
    'twitch' => [
        'model' => \TwitchApi\TwitchApi::class,
        'options' => [
            'client_id' => env('TWITCH_CLIENT_ID'),
            'client_secret' => env('TWITCH_CLIENT_SECRET'),
        ]
    ],
];
