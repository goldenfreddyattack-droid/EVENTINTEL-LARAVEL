<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::view('about', 'about')->name('about');

    Route::get('users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');

    Route::get('/student', [\App\Http\Controllers\studmngt_controller::class, 'index'])->name('student.index');
    Route::get('/addstudent', [\App\Http\Controllers\studmngt_controller::class, 'addstud'])->name('student.addstud');
    Route::post('/studentstore', [App\Http\Controllers\studmngt_controller::class, 'store'])->name('student.store');
    Route::get('student/{id}/edit', [App\Http\Controllers\studmngt_controller::class, 'edit'])->name('student.edit');
    Route::put('student/{id}', [App\Http\Controllers\studmngt_controller::class, 'update'])->name('student.update');
    Route::delete('student/{id}', [App\Http\Controllers\studmngt_controller::class, 'delete'])->name('student.delete');
    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});
