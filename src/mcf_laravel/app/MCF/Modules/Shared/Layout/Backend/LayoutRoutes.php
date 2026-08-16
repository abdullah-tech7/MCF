<?php

use Illuminate\Support\Facades\Route;
use App\MCF\Modules\Shared\Layout\Backend\LayoutController;

Route::post('/language', [LayoutController::class, 'switchLanguage'])
    ->name('shared.layout.switchLanguage');
