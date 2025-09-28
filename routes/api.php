<?php

use App\Http\Controllers\Search\JobSearchController;
use App\Http\Controllers\Search\SemanticSearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function ($request) {
    return $request->user();
});

Route::get('/search/jobs', JobSearchController::class)->name('api.search.jobs');

Route::middleware('auth')
    ->get('/search/personalized', SemanticSearchController::class)
    ->name('api.search.personalized');
