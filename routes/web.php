<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return redirect('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('admin/manage-stores', 'admin.manage-stores')->name('admin.stores');
    Route::livewire('admin/manage-products', 'admin.manage-products')->name('admin.products');
    Route::livewire('admin/manage-cashiers', 'admin.manage-cashiers')->name('admin.cashiers');
    Route::livewire('admin/manage-sales', 'admin.manage-sales')->name('admin.sales');
    Route::livewire('admin/manage-margin', 'admin.manage-margin')->name('admin.margin');

    Route::livewire('admin/stores/{store}/products', 'admin.manage-store-products')->name('admin.stores.products');
});

require __DIR__.'/settings.php';
