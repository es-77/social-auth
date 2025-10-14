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
    | Default User Attributes
    |--------------------------------------------------------------------------
    |
    | Attributes to set when creating a user from OAuth, useful for required
    | columns like role. Override via env or publish this config.
    |
    */
    'user_defaults' => [
        'role' => env('SOCIAL_AUTH_DEFAULT_ROLE', 'user'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Required Fields (Rendered on Login Page)
    |--------------------------------------------------------------------------
    |
    | Define extra fields to collect before redirecting to OAuth. These values
    | are stored in the session and merged when creating the user.
    |
    | Supported types in the default Blade: text, select.
    |
    */
    'required_fields' => [
        [
            'name' => 'role',
            'label' => 'Role',
            'type' => 'select',
            'required' => true,
            'options' => [
                'user' => 'User',
                'admin' => 'Admin',
            ],
            'default' => env('SOCIAL_AUTH_DEFAULT_ROLE', 'user'),
        ],
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

    /*
    |--------------------------------------------------------------------------
    | User Fields Mapping
    |--------------------------------------------------------------------------
    |
    | Map OAuth user data to your database fields
    | This allows the package to work with different database schemas
    |
    | Options for name_field:
    | - 'name' (default) - Store full name in single 'name' column
    | - 'first_name' - Store full name in 'first_name' column only
    | - 'first_last' - Split into 'first_name' and 'last_name' columns
    |
    */
    'user_fields' => [
        // How to handle the user's name from OAuth
        // 'name' = single name column, 'first_name' = first_name column only, 'first_last' = separate first_name/last_name columns
        'name_field' => env('SOCIAL_AUTH_NAME_FIELD', 'name'), // 'name', 'first_name', or 'first_last'
        
        // Fields that should be filled when creating a user
        // Add any additional required fields for your users table
        'additional_fields' => [
            // Example:
            // 'role' => 'user',
            // 'status' => 'active',
            // 'is_active' => true,
        ],

        // Optional: Map standard field names to your custom column names
        'custom_fields' => [
            // Example:
            // 'first_name' => 'given_name',
            // 'last_name' => 'surname',
            // 'email' => 'user_email',
        ],
    ],
];