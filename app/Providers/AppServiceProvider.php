<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\SubAdminPolicy;
use Illuminate\Support\Facades\Gate;
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
        // Doesn't follow the {Model}Policy auto-discovery convention (the
        // permission names don't match), so it's registered explicitly.
        Gate::policy(User::class, SubAdminPolicy::class);
    }
}
