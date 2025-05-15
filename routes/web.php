<?php

use Illuminate\Support\Facades\Route;
use Filament\Facades\Filament;
use App\Http\Controllers\DocumentationPageController;
use Illuminate\Support\Str;
use App\Models\DocumentationPage;

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

Route::get('/documentation/{documentationPage}/preview', [DocumentationPageController::class, 'preview'])
    ->name('documentation.preview');

Route::get('/documentation-pages-list', function () {
    return DocumentationPage::where('is_active', true)
        ->orderBy('order')
        ->get(['id', 'title', 'category']);
});

Route::get('/documentation/{documentationPage}/json', function (DocumentationPage $documentationPage) {
    return [
        'id' => $documentationPage->id,
        'title' => $documentationPage->title,
        'category' => $documentationPage->category,
        'content_html' => Str::markdown($documentationPage->content),
    ];
});
