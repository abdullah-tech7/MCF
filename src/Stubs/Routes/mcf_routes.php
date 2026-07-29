<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MCF Routes
|--------------------------------------------------------------------------
|
| Register your Modular Control Framework routes here.
|
| Set your application's home page by changing the route name below.
|
| Example:
| return redirect()->route('shared.test.index');
|
*/

Route::get('/', function () {
    return redirect()->route('shared.test.index');
});

