<?php

use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::post('/get-file', [App\Http\Controllers\PageController::class, 'getFile']);
Route::post('/delete-files', [App\Http\Controllers\PageController::class, 'deleteFiles']);
Route::post('/get-name', [App\Http\Controllers\PageController::class, 'getName']);
