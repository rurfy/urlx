<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedirectController;

Route::get('/{slug}', RedirectController::class)
    ->where('slug', '[A-Za-z0-9]{3,32}');
