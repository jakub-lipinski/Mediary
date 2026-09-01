<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('oauth', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));

        RateLimiter::for('medical-files', fn (Request $request): array => [
            Limit::perMinute(3)->by('minute:'.$request->user()->id),
            Limit::perDay(20)->by('day:'.$request->user()->id),
        ]);

        RateLimiter::for('ai-analysis', fn (Request $request): array => [
            Limit::perMinute(5)->by('minute:'.$request->user()->id),
            Limit::perDay(30)->by('day:'.$request->user()->id),
        ]);
    }
}
