<?php

use Illuminate\Support\Facades\Route;
use Snawbar\InvoiceTemplate\Controllers\InvoiceTemplateController;

Route::prefix(config('snawbar-invoice-template.route-prefix'))
    ->middleware(config('snawbar-invoice-template.middleware'))
    ->controller(InvoiceTemplateController::class)
    ->name('invoice-templates.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-data', 'getData')->name('data');
        Route::post('/store', 'store')->name('store');
        Route::put('/update/{id}', 'update')->name('update');
        Route::delete('/delete/{id}', 'destroy')->name('destroy');
    });
