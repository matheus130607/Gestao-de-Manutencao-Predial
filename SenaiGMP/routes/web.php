<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/admin');
    }
    return view('welcome');
})->name('home');

Route::get('/procedimentos-operacionais', function () {
    $path = base_path('../home/procedimentos-operacionais.html');
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
})->name('procedimentos.operacionais');

Route::view('/institucional', 'welcome')->name('institutional');
