<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/upload');

Route::get('/upload', [FileController::class, 'create'])->name('files.create');
Route::post('/upload', [FileController::class, 'store'])->name('files.store');

Route::get('/files', [FileController::class, 'index'])->name('files.index');
Route::delete('/files/{fileItem}', [FileController::class, 'destroy'])->name('files.destroy');
