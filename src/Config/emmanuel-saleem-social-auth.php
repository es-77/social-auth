<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | This value is the prefix for all authentication routes
    |
    */
    'route_prefix' => 'emmanuel-saleem',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Define middleware for web and api routes
    |
    */
    'middleware' => [
        'web' => ['web'],
        'api' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirect URLs
    |--------------------------------------------------------------------------
    |
    | Define where to redirect after login/logout (for web routes)
    |
    */
    'redirect_after_login' => '/dashboard',
    'redirect_after_logout' => '/',

    /*
    |--------------------------------------------------------------------------
    | Frontend URL
    |--------------------------------------------------------------------------
    |
    | The frontend application URL for API OAuth redirects
    | This is where OAuth providers will redirect users after authentication
    |
    */
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),

    /*
    |--------------------------------------------------------------------------
    | Google OAuth Settings
    |--------------------------------------------------------------------------
    |
    | Configure your Google OAuth credentials in your .env file
    | GOOGLE_CLIENT_ID=your-client-id
    | GOOGLE_CLIENT_SECRET=your-client-secret
    | GOOGLE_REDIRECT_URI=http://your-app.com/emmanuel-saleem/social-auth/google/callback
    |
    */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Microsoft OAuth Settings
    |--------------------------------------------------------------------------
    |
    | Configure your Microsoft OAuth credentials in your .env file
    | MICROSOFT_CLIENT_ID=your-client-id
    | MICROSOFT_CLIENT_SECRET=your-client-secret
    | MICROSOFT_REDIRECT_URI=http://your-app.com/emmanuel-saleem/social-auth/microsoft/callback
    |
    */
    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Customization
    |--------------------------------------------------------------------------
    |
    | Customize the social login buttons and footer text
    |
    */
    'labels' => [
        'google_button' => env('SOCIAL_AUTH_GOOGLE_LABEL', 'Continue with Google'),
        'microsoft_button' => env('SOCIAL_AUTH_MICROSOFT_LABEL', 'Continue with Microsoft'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Footer Settings
    |--------------------------------------------------------------------------
    |
    | Show/hide footer and customize footer text
    |
    */
    'show_footer' => env('SOCIAL_AUTH_SHOW_FOOTER', true),
    'footer_text' => env('SOCIAL_AUTH_FOOTER_TEXT', 'Powered by Emmanuel Saleem Social Auth'),

    /*
    |--------------------------------------------------------------------------
    | API Authentication Driver
    |--------------------------------------------------------------------------
    |
    | Choose which authentication system to use for API tokens
    | Options: 'sanctum' or 'passport'
    |
    | Sanctum: Lightweight token-based authentication (default)
    | Passport: Full OAuth2 server implementation
    |
    */
    'api_auth_driver' => env('SOCIAL_AUTH_API_DRIVER', 'sanctum'),

    /*
    |--------------------------------------------------------------------------
    | Passport Token Settings (if using Passport)
    |--------------------------------------------------------------------------
    |
    | Configure Passport token settings
    |
    */
    'passport' => [
        'token_name' => 'oauth-token',
        'scopes' => [], // Add scopes if needed
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | Specify your application's User model class
    | This allows the package to work with different User model locations
    |
    */
    'user_model' => App\Models\User::class,
];