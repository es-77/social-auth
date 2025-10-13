<?php

use EmmanuelSaleem\SocialAuth\Controllers\ApiOAuthController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('emmanuel-saleem-social-auth.route_prefix', 'emmanuel-saleem'),
    'middleware' => config('emmanuel-saleem-social-auth.middleware.api', ['api']),
], function () {
    
    // Google OAuth API routes
    Route::get('/auth/google/url', [ApiOAuthController::class, 'getGoogleAuthUrl'])
        ->name('api.oauth.google.url');
    
    Route::post('/auth/google/callback', [ApiOAuthController::class, 'handleGoogleCallback'])
        ->name('api.oauth.google.callback');
    
    // Microsoft OAuth API routes
    Route::get('/auth/microsoft/url', [ApiOAuthController::class, 'getMicrosoftAuthUrl'])
        ->name('api.oauth.microsoft.url');
    
    Route::post('/auth/microsoft/callback', [ApiOAuthController::class, 'handleMicrosoftCallback'])
        ->name('api.oauth.microsoft.callback');
});