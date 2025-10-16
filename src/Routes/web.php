<?php

use EmmanuelSaleem\SocialAuth\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('emmanuel-saleem-social-auth.route_prefix', 'emmanuel-saleem'),
    'middleware' => config('emmanuel-saleem-social-auth.middleware.web', ['web']),
], function () {
    
    // Login page
    Route::get('/social-auth/login', [SocialAuthController::class, 'showLoginPage'])
        ->name('emmanuel-saleem.social-auth.login');
    // Capture required fields then redirect
    Route::post('/social-auth/login/google', [SocialAuthController::class, 'prepareGoogleLogin'])
        ->name('emmanuel-saleem.social-auth.login.google');
    Route::post('/social-auth/login/microsoft', [SocialAuthController::class, 'prepareMicrosoftLogin'])
        ->name('emmanuel-saleem.social-auth.login.microsoft');
    
    // Google OAuth routes
    Route::get('/social-auth/google', [SocialAuthController::class, 'redirectToGoogle'])
        ->name('emmanuel-saleem.social-auth.google');
    
    Route::get('/social-auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])
        ->name('emmanuel-saleem.social-auth.google.callback');
    
    // Microsoft OAuth routes
    Route::get('/social-auth/microsoft', [SocialAuthController::class, 'redirectToMicrosoft'])
        ->name('emmanuel-saleem.social-auth.microsoft');
    
    Route::get('/social-auth/microsoft/callback', [SocialAuthController::class, 'handleMicrosoftCallback'])
        ->name('emmanuel-saleem.social-auth.microsoft.callback');

    // Separate Microsoft Graph flow (keeps existing one intact)
    Route::get('/social-auth/microsoft-graph', [SocialAuthController::class, 'redirectToMicrosoftGraph'])
        ->name('emmanuel-saleem.social-auth.microsoft.graph');

    Route::get('/social-auth/microsoft-graph/callback', [SocialAuthController::class, 'handleMicrosoftGraphCallback'])
        ->name('emmanuel-saleem.social-auth.microsoft.graph.callback');
    
    // Logout
    Route::post('/social-auth/logout', [SocialAuthController::class, 'logout'])
        ->name('emmanuel-saleem.social-auth.logout');
});