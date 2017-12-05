<?php

namespace App\Providers;

use App\API\TwitchApi;
use App\Services\TwitchAdapter;
use App\Registry\StreamingServiceRegistry;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->make(StreamingServiceRegistry::class)
                  ->addAdapter(
                      TwitchAdapter::TWITCH,
                      new TwitchAdapter(
                          new TwitchApi(
                              [
                                  'client_id'     => env('TWITCH_CLIENT_ID'),
                                  'client_secret' => env('TWITCH_CLIENT_SECRET'),
                              ]
                          )
                      )
                  );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StreamingServiceRegistry::class);
    }
}
