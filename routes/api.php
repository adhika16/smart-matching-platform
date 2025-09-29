<?php

use App\Http\Controllers\Search\JobSearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function ($request) {
    return $request->user();
});

Route::get('/search/jobs', JobSearchController::class)->name('api.search.jobs');
