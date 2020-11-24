<?php

use App\Http\Controllers\CustomersController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'customers');

Route::get('customers', [CustomersController::class, 'index'])->name('customers');
Route::get('customers/{customer}/edit', [CustomersController::class, 'edit'])->name('customers.edit');
