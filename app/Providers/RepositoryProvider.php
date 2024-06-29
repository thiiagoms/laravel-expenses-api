<?php

namespace App\Providers;

use App\Repositories\Contracts\User\UserContract;
use App\Repositories\ORM\User\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $repositories = [
            UserContract::class => UserRepository::class,
        ];

        array_map(
            fn ($contract, $repository) => $this->app->bind($contract, $repository),
            array_keys($repositories),
            array_values($repositories)
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
