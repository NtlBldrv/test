<?php

$factory->define(App\ViewerCountHistory::class, function () {
    return [
        'viewer_count' => random_int(0, 999999),
        'stream_id'    => 1,
    ];
});
