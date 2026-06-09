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
    return response()->file(base_path('../Documentacao/procedimentos-operacionais.html'));
})->name('procedimentos.operacionais');

Route::view('/institucional', 'welcome')->name('institutional');
