<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user()->only([
        'id',
        'name',
        'email',
        'email_verified_at',
        'profile_photo_url',
    ]);
})->middleware(['auth:sanctum', 'abilities:read']);
