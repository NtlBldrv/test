<?php

use Illuminate\Support\Facades\Route;

Route::middleware('check_ips')->group(function () {
    Route::get('streams/', 'StreamController@allStreams')
         ->name('streams')
         ->middleware('auth:api');
    Route::get('streams/viewer_count/', 'StreamController@viewerCount')
         ->name('streams_viewer_count')
         ->middleware('auth:api');
    Route::get('streams/viewer_count_history/', 'StreamController@viewerCountHistory')
         ->name('streams_viewer_count_history')
         ->middleware('auth:api');
    Route::post('register/', 'Auth\RegisterController@register');
});
