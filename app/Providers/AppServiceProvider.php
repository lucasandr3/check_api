<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\TenantSeedCommand;
use App\Console\Commands\TestCompanySystemCommand;
use App\Console\Commands\CheckTenantMigrationStatusCommand;

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
        if ($this->app->runningInConsole()) {
            $this->commands([
                TenantSeedCommand::class,
                TestCompanySystemCommand::class,
                CheckTenantMigrationStatusCommand::class,
            ]);
        }
    }
}
