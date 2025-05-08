<?php

use Illuminate\Support\Facades\Route;
use Filament\Facades\Filament;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

// Filament authentication routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return redirect(Filament::getHomeUrl());
    })->name('dashboard');
});
