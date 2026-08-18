<?php
use App\MCF\Storage\Providers\Laravel\LaravelStorageController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/mcf/storage/public/{reference}',
    [LaravelStorageController::class, 'public'],
)->name('mcf.storage.public');

Route::get(
    '/mcf/storage/temporary/{reference}',
    [LaravelStorageController::class, 'temporary'],
)->name('mcf.storage.temporary');
