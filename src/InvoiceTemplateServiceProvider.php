<?php

namespace Snawbar\InvoiceTemplate;

use Illuminate\Support\ServiceProvider;

class InvoiceTemplateServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'snawbar-invoice-template');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../migrations');

            $this->publishes([
                __DIR__ . '/../config/invoice-template.php' => config_path('snawbar-invoice-template.php'),
                __DIR__ . '/../migrations' => database_path('migrations'),
            ], 'snawbar-invoice-template-assets');
        }
    }
}
