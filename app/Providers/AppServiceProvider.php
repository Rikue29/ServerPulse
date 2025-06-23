<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Livewire\LogsTable;
use Illuminate\Support\Facades\View;
use App\Models\Alert;

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
        View::composer('layouts.app', function ($view) {
        $view->with('recentAlerts', Alert::latest()->take(5)->get());
    });
    }
}
