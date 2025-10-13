<?php

namespace EmmanuelSaleem\SocialAuth;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;

class SocialAuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/Config/emmanuel-saleem-social-auth.php', 'emmanuel-saleem-social-auth');
    }

    public function boot()
    {
        // Register Microsoft Socialite provider
        $this->bootMicrosoftSocialite();

        // Publish config
        $this->publishes([
            __DIR__.'/Config/emmanuel-saleem-social-auth.php' => config_path('emmanuel-saleem-social-auth.php'),
        ], 'emmanuel-saleem-social-auth-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/Migrations/' => database_path('migrations'),
        ], 'emmanuel-saleem-social-auth-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/Views/' => resource_path('views/vendor/emmanuel-saleem-social-auth'),
        ], 'emmanuel-saleem-social-auth-views');

        // Load views
        $this->loadViewsFrom(__DIR__.'/Views', 'emmanuel-saleem-social-auth');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/Migrations');
    }

    /**
     * Boot Microsoft Socialite provider
     */
    protected function bootMicrosoftSocialite()
    {
        $socialite = $this->app->make(Factory::class);
        
        $socialite->extend('microsoft', function ($app) use ($socialite) {
            $config = $app['config']['services.microsoft'];
            return $socialite->buildProvider(
                \SocialiteProviders\Microsoft\Provider::class,
                $config
            );
        });
    }
}