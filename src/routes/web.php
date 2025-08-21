<?php

use Illuminate\Support\Facades\Route;
use Snawbar\InvoiceTemplate\Controllers\InvoiceTemplateController;

Route::prefix(config('snawbar-invoice-template.route-prefix'))
    ->middleware(config('snawbar-invoice-template.middleware'))
    ->name('invoice-templates.')
    ->group(function () {
        Route::get('/', [InvoiceTemplateController::class, 'index'])->name('index');
        Route::get('/get-data', [InvoiceTemplateController::class, 'getData'])->name('data');
        Route::post('/store', [InvoiceTemplateController::class, 'store'])->name('store');
        Route::put('/update/{id}', [InvoiceTemplateController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [InvoiceTemplateController::class, 'destroy'])->name('destroy');
    });
