<?php

namespace Snawbar\InvoiceTemplate;

use Illuminate\Support\ServiceProvider;

class InvoiceTemplateServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/invoice-template.php',
            'snawbar-invoice-template'
        );
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/invoice-template.php' => config_path('snawbar-invoice-template.php'),
            ], 'config');
        }
    }
}
