<?php

namespace Hobbii\SocialiteProvider;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;

class SocialiteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/hobbii-socialite.php', 'hobbii-socialite',
        );
    }

    public function boot()
    {
        $socialite = $this->app->make(Factory::class);
        $socialite->extend(
            'hobbii',
            fn ($app) => $socialite->buildProvider(HobbiiProvider::class, $app['config']['hobbii-socialite.settings'])
        );
        $this->publishes([
            __DIR__.'/../config/hobbii-socialite.php' => config_path('hobbii-socialite.php'),
        ]);
    }
}
