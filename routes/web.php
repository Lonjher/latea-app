<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return redirect('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
