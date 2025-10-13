# Laravel Passport Integration Guide

This package supports both **Laravel Sanctum** (default) and **Laravel Passport** for API authentication.

---

## 🔄 Choosing Between Sanctum and Passport

### Laravel Sanctum (Default)
- ✅ Lightweight and simple
- ✅ Perfect for SPA and mobile apps
- ✅ Token-based authentication
- ✅ No OAuth2 server complexity
- ✅ Recommended for most use cases

### Laravel Passport
- ✅ Full OAuth2 server implementation
- ✅ Support for OAuth2 scopes and permissions
- ✅ Token expiration and refresh tokens
- ✅ OAuth2 grant types (authorization code, password, etc.)
- ✅ Recommended for complex enterprise applications

---

## 📦 Using with Laravel Sanctum (Default)

The package works with Sanctum out of the box. No additional configuration needed!

### Step 1: Install Sanctum

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### Step 2: Add HasApiTokens to User Model

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    // ... rest of your model
}
```

### Step 3: Use the API

That's it! The package will automatically use Sanctum for token generation.

**API Response:**
```json
{
  "status": true,
  "success": "success",
  "code": 200,
  "message": "Successfully authenticated with Google",
  "errors": [],
  "data": {
    "token": "1|eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": null,
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "avatar": "https://...",
      "oauth_provider": "google"
    }
  }
}
```

---

## 🔐 Using with Laravel Passport

### Step 1: Install Passport

```bash
composer require laravel/passport
```

### Step 2: Run Migrations

```bash
php artisan migrate
```

### Step 3: Install Passport

```bash
php artisan passport:install
```

This will create encryption keys and create OAuth clients.

### Step 4: Add HasApiTokens to User Model

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    // ... rest of your model
}
```

**Important:** Use `Laravel\Passport\HasApiTokens` instead of `Laravel\Sanctum\HasApiTokens`

### Step 5: Configure AuthServiceProvider

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Passport routes
        Passport::routes();
        
        // Optional: Set token expiration
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}
```

### Step 6: Update config/auth.php

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver' => 'passport',  // Change from 'token' to 'passport'
        'provider' => 'users',
    ],
],
```

### Step 7: Configure Package to Use Passport

Add to your `.env` file:

```env
SOCIAL_AUTH_API_DRIVER=passport
```

Or in `config/emmanuel-saleem-social-auth.php`:

```php
'api_auth_driver' => 'passport',
```

### Step 8: Use the API

**API Response with Passport:**
```json
{
  "status": true,
  "success": "success",
  "code": 200,
  "message": "Successfully authenticated with Google",
  "errors": [],
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 1296000,
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "avatar": "https://...",
      "oauth_provider": "google"
    }
  }
}
```

---

## 🎯 Advanced Passport Configuration

### Using Scopes

Configure scopes in `config/emmanuel-saleem-social-auth.php`:

```php
'passport' => [
    'token_name' => 'oauth-token',
    'scopes' => ['read-user', 'write-user'],
],
```

Define scopes in `AuthServiceProvider`:

```php
use Laravel\Passport\Passport;

public function boot(): void
{
    $this->registerPolicies();

    Passport::routes();
    
    Passport::tokensCan([
        'read-user' => 'Read user information',
        'write-user' => 'Update user information',
        'admin' => 'Perform administrative tasks',
    ]);
    
    Passport::setDefaultScope([
        'read-user',
    ]);
}
```

### Custom Token Expiration

```php
// In AuthServiceProvider
Passport::tokensExpireIn(now()->addDays(15));
Passport::refreshTokensExpireIn(now()->addDays(30));
Passport::personalAccessTokensExpireIn(now()->addMonths(6));
```

### Revoking Tokens

```php
// In your controller
public function logout(Request $request)
{
    $request->user()->token()->revoke();
    
    return response()->json([
        'message' => 'Successfully logged out'
    ]);
}
```

---

## 🔄 Switching Between Sanctum and Passport

You can easily switch between the two:

### Via Environment Variable

```env
# Use Sanctum (default)
SOCIAL_AUTH_API_DRIVER=sanctum

# Use Passport
SOCIAL_AUTH_API_DRIVER=passport
```

### Via Config File

```php
// config/emmanuel-saleem-social-auth.php
'api_auth_driver' => 'passport', // or 'sanctum'
```

---

## 📝 Complete Example with Passport

### .env Configuration

```env
# API Auth Driver
SOCIAL_AUTH_API_DRIVER=passport

# Google OAuth
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:3000/auth/google/callback

# Microsoft OAuth
MICROSOFT_CLIENT_ID=your-microsoft-client-id
MICROSOFT_CLIENT_SECRET=your-microsoft-client-secret
MICROSOFT_REDIRECT_URI=http://localhost:3000/auth/microsoft/callback

# Frontend URL
FRONTEND_URL=http://localhost:3000
```

### User Model

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'microsoft_id',
        'avatar',
        'google_token',
        'google_refresh_token',
        'microsoft_token',
        'microsoft_refresh_token',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
        'google_refresh_token',
        'microsoft_token',
        'microsoft_refresh_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

### Frontend Usage (React)

```javascript
// Login
const response = await fetch('/api/emmanuel-saleem/auth/google/url');
const result = await response.json();
window.location.href = result.data.url;

// Callback
const code = new URLSearchParams(window.location.search).get('code');
const response = await fetch('/api/emmanuel-saleem/auth/google/callback', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ code }),
});

const result = await response.json();
if (result.status) {
  localStorage.setItem('access_token', result.data.token);
  localStorage.setItem('token_type', result.data.token_type);
  localStorage.setItem('expires_in', result.data.expires_in);
}

// Use token in API requests
const apiResponse = await fetch('/api/user', {
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('access_token')}`,
    'Content-Type': 'application/json',
  },
});
```

---

## 🧪 Testing

### Test Sanctum

```bash
# Set in .env
SOCIAL_AUTH_API_DRIVER=sanctum

# Test the endpoint
curl -X POST http://localhost/api/emmanuel-saleem/auth/google/callback \
  -H "Content-Type: application/json" \
  -d '{"code":"YOUR_GOOGLE_CODE"}'
```

### Test Passport

```bash
# Set in .env
SOCIAL_AUTH_API_DRIVER=passport

# Test the endpoint
curl -X POST http://localhost/api/emmanuel-saleem/auth/google/callback \
  -H "Content-Type: application/json" \
  -d '{"code":"YOUR_GOOGLE_CODE"}'
```

---

## 🆚 Comparison

| Feature | Sanctum | Passport |
|---------|---------|----------|
| **Setup Complexity** | Simple | Moderate |
| **Token Type** | Plain text | JWT |
| **Token Expiration** | No (by default) | Yes (configurable) |
| **Refresh Tokens** | No | Yes |
| **OAuth2 Scopes** | No | Yes |
| **OAuth2 Grants** | No | Yes (multiple) |
| **Performance** | Faster | Slightly slower |
| **Database Tables** | 1 | 5 |
| **Use Case** | SPAs, Mobile Apps | Enterprise APIs, OAuth2 Server |

---

## 🐛 Troubleshooting

### Passport: "Unauthenticated" Error

**Solution:** Make sure:
1. `config/auth.php` has `'driver' => 'passport'` for API guard
2. User model uses `Laravel\Passport\HasApiTokens`
3. `Passport::routes()` is called in `AuthServiceProvider`
4. Token is sent as `Authorization: Bearer {token}`

### Passport: Encryption Keys Missing

**Solution:**
```bash
php artisan passport:install --force
```

### Token Not Working

**Solution:** Check if the correct guard is being used:
```php
// Correct
auth('api')->user()

// Check middleware
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
```

---

## 📚 Resources

- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Laravel Passport Documentation](https://laravel.com/docs/passport)
- [OAuth 2.0 Documentation](https://oauth.net/2/)

---

## ✅ Recommendation

**For most applications:** Use **Sanctum** (default)
- Simpler setup
- Better performance
- Sufficient for SPA/mobile apps
- No token expiration management needed

**For enterprise applications:** Use **Passport**
- Need OAuth2 server features
- Require scopes and permissions
- Need token expiration/refresh
- Building API for third-party integrations

---

**Need help?** Check the main [README.md](./README.md) or create an issue on GitHub.

