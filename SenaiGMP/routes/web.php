<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/admin');
    }
    return view('welcome');
})->name('home');

Route::view('/institucional', 'welcome')->name('institutional');
