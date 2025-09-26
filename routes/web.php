<?php

use App\Http\Controllers\CreativeDashboardController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\OpportunityOwnerDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Profile setup and management
    Route::get('profile/setup', [ProfileController::class, 'setup'])->name('profile.setup');
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('profile/creative', [ProfileController::class, 'updateCreative'])->name('profile.update.creative');
    Route::patch('profile/opportunity-owner', [ProfileController::class, 'updateOpportunityOwner'])->name('profile.update.opportunity-owner');

    // Creative routes
    Route::middleware('user.type:creative')->group(function () {
        Route::get('dashboard/creative', [CreativeDashboardController::class, 'index'])->name('dashboard.creative');
    });

    // Opportunity Owner routes
    Route::middleware('user.type:opportunity_owner')->group(function () {
        Route::get('dashboard/opportunity-owner', [OpportunityOwnerDashboardController::class, 'index'])->name('dashboard.opportunity-owner');

        Route::prefix('opportunity-owner')->name('opportunity-owner.')->group(function () {
            Route::patch('jobs/{job}/publish', [JobController::class, 'publish'])->name('jobs.publish');
            Route::patch('jobs/{job}/archive', [JobController::class, 'archive'])->name('jobs.archive');
            Route::resource('jobs', JobController::class)->except(['show']);
        });
    });

    // Redirect dashboard to appropriate user type dashboard
    Route::get('dashboard', function () {
        $user = auth()->user();

        if ($user->isCreative()) {
            return redirect()->route('dashboard.creative');
        } else {
            return redirect()->route('dashboard.opportunity-owner');
        }
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
