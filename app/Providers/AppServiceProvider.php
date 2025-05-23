<?php

namespace App\Providers;

use App\Repository\AuthRepository;
use App\Repository\Interface\AuthRepositoryInterface;
use App\Repository\Interface\PropertyRepositoryInterface;
use App\Repository\Interface\VisitorRepositoryInterface;
use App\Repository\Interface\AdminRepositoryInterface;
use App\Repository\PropertyRepository;
use App\Repository\VisitorRepository;
use App\Repository\AdminRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            AuthRepositoryInterface::class, AuthRepository::class
        );
        $this->app->bind(
            PropertyRepositoryInterface::class, PropertyRepository::class
        );
        $this->app->bind(
            VisitorRepositoryInterface::class, VisitorRepository::class
        );
        $this->app->bind(
            AdminRepositoryInterface::class, AdminRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
