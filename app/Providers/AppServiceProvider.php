<?php

namespace App\Providers;

use App\Application\Repository\AssuntoRepositoryInterface;
use App\Application\Repository\AutorRepositoryInterface;
use App\Application\Repository\LivroRepositoryInterface;
use App\Infrastructure\Repository\AssuntoRepository;
use App\Infrastructure\Repository\AutorRepository;
use App\Infrastructure\Repository\LivroRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registra os bindings das interfaces de repositório
        $this->app->bind(AssuntoRepositoryInterface::class, AssuntoRepository::class);
        $this->app->bind(AutorRepositoryInterface::class, AutorRepository::class);
        $this->app->bind(LivroRepositoryInterface::class, LivroRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
