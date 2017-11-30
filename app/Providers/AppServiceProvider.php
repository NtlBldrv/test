<?php

namespace App\Providers;

use App\Services\TwitchAdapter;
use App\Registry\StreamingServiceRegistry;
use App\StreamingService;
use Illuminate\Support\ServiceProvider;
use TwitchApi\TwitchApi;

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
