<?php

namespace App\Providers;

use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\UserEloquentRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserEloquentRepository::class
        );
    }
}
