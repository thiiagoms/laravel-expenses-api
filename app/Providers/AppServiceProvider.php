<?php

namespace App\Providers;

use App\Models\Expense;
use App\Policies\Expense\ExpensePolicy;
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

    private function expenseSecurity(): void
    {
        Gate::policy(Expense::class, ExpensePolicy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->expenseSecurity();
    }
}
