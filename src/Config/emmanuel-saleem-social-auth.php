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
        // API-first flow: set a separate redirect URI that points to your frontend SPA
        // Example: https://frontend.example.com/oauth/google/callback
        'api_redirect' => env('GOOGLE_API_REDIRECT_URI'),
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
        // API-first flow: set a separate redirect URI that points to your frontend SPA
        // Example: https://frontend.example.com/oauth/microsoft/callback
        'api_redirect' => env('MICROSOFT_API_REDIRECT_URI'),
        // Scopes to request from Microsoft identity platform (Graph)
        // Defaults include basic profile, contacts, and email access
        'scopes' => explode(',', env('MICROSOFT_SCOPES', 'offline_access,User.Read,profile,email,Contacts.Read')),
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
    | Define how OAuth data maps to your user table fields
    | This allows complete flexibility for different database schemas
    |
    */
    'user_fields' => [
        // Field mapping from OAuth providers to your user table columns
        'field_mapping' => [
            // Google OAuth fields -> Your user table columns
            'google' => [
                'name' => 'first_name',           // Google name -> first_name column
                'email' => 'email',               // Google email -> email column  
                'avatar' => 'avatar',             // Google avatar -> avatar column
                'id' => 'google_id',              // Google ID -> google_id column
                'token' => 'google_token',        // Google token -> google_token column
                'refresh_token' => 'google_refresh_token', // Google refresh -> google_refresh_token column
            ],
            
            // Microsoft OAuth fields -> Your user table columns  
            'microsoft' => [
                'name' => 'first_name',           // Microsoft name -> first_name column
                'email' => 'email',               // Microsoft email -> email column
                'avatar' => 'avatar',             // Microsoft avatar -> avatar column  
                'id' => 'microsoft_id',          // Microsoft ID -> microsoft_id column
                'token' => 'microsoft_token',     // Microsoft token -> microsoft_token column
                'refresh_token' => 'microsoft_refresh_token', // Microsoft refresh -> microsoft_refresh_token column
            ],
        ],

        // Default values for required fields that OAuth doesn't provide
        'defaults' => [
            'last_name' => '',                    // Default for last_name (NOT NULL column)
            'password' => 'auto_generated',       // Will be auto-generated
            'email_verified_at' => 'now',         // Will be set to current timestamp
            'role' => env('SOCIAL_AUTH_DEFAULT_ROLE', 'user'),
            'status' => 'active',
            'is_active' => true,
        ],

        // Special handling for name field (if you want to split Google name)
        'name_handling' => [
            'mode' => env('SOCIAL_AUTH_NAME_MODE', 'single'), // 'single', 'split', 'custom'
            'split_fields' => [
                'first_name' => 'first_name',    // Where to put first part
                'last_name' => 'last_name',      // Where to put last part  
            ],
        ],

        // Custom field transformations
        'transformations' => [
            // Example: Convert email domain to a field
            // 'email_domain' => function($email) { return substr(strrchr($email, "@"), 1); },
        ],
    ],
];