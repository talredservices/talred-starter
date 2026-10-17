<?php

use Illuminate\Support\Facades\Route;

Route::get('/', static fn () => response()->json([
    'name' => config('app.name'),
    'status' => 'ok',
]));
