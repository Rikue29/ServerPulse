<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire; // ✅ Import Livewire
use App\Http\Livewire\LogsTable; // ✅ Import your component class

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
        Livewire::component('logs-table', LogsTable::class);
    }
}
