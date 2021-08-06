<?php

namespace App\Providers;

use App\Repositories\Item\ItemRepository;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        $this->app->bind(\App\Repositories\Item\ItemRepository::class, function($app) {
            return new \App\Repositories\Item\ItemEloquentRepository();
        });

        $this->app->bind(\App\Repositories\Tag\TagRepository::class, function($app) {
            return new \App\Repositories\Tag\TagEloquentRepository();
        });

        $this->app->bind(\App\Repositories\ItemType\ItemTypeRepository::class, function($app) {
            return new \App\Repositories\ItemType\ItemTypeEloquentRepository();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // https強制
        if ($this->app->environment() == 'production') {
            \URL::forceScheme('https');
        }
    }
}
