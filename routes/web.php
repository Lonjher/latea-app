<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return redirect('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware(['can:isAdmin'])->group(function (){
        Route::livewire('admin/manage-stores', 'admin.manage-stores')->name('admin.stores');
        Route::livewire('admin/manage-products', 'admin.manage-products')->name('admin.products');
        Route::livewire('admin/manage-cashiers', 'admin.manage-cashiers')->name('admin.cashiers');
        Route::livewire('admin/manage-sales', 'admin.manage-sales')->name('admin.sales');
        Route::livewire('admin/manage-operationals', 'admin.manage-operationals')->name('admin.operationals');
        Route::livewire('admin/manage-margin', 'admin.manage-margin')->name('admin.margin');
        Route::livewire('admin/stores/{store}/products', 'admin.manage-store-products')->name('admin.stores.products');
        });

        Route::middleware('can:isCashier')->group(function (){
            Route::livewire('user/manage-operationals', 'user.manage-operationals')->name('user.operationals');
            Route::livewire('user/manage-sales', 'user.manage-sales')->name('user.sales');
        Route::livewire('user/manage-margin', 'user.manage-margin')->name('user.margin');
    });
});

require __DIR__.'/settings.php';
