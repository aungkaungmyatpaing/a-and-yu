<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/orders/{order}/invoice', [\App\Http\Controllers\OrderInvoiceController::class, 'show'])->name('orders.invoice');

