<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Pages\Welcome;

Route::get('/', function () {
    return view('welcome');
});

// Ou se preferir manter o Filament no admin
Route::get('/institucional', function () {
    return app(Welcome::class);
})->name('institutional');
