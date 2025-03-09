<?php

namespace App\Providers;

use App\Contracts\ChatGptServiceInterface;
use App\Contracts\DiffbotServiceInterface;
use App\Services\Api\ChatGptService;
use App\Services\Api\DiffbotService;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DiffbotServiceInterface::class, function ($app) {
            return new DiffbotService(new Client);
        });

        $this->app->bind(ChatGptServiceInterface::class, function ($app) {
            return new ChatGptService(new Client);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app['router']->aliasMiddleware('api.token', \App\Http\Middleware\ApiTokenMiddleware::class);
    }
}
