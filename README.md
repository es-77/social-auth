# Emmanuel Saleem Social Auth Package

A comprehensive Laravel package for OAuth social authentication with Google and Microsoft, supporting both traditional web applications and modern SPA/API-based frontends.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel](https://img.shields.io/badge/Laravel-9%20%7C%2010%20%7C%2011-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net)

## 📚 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Quick Start - Add Login to Your App](#-quick-start---add-login-to-your-app) ⭐
- [Configuration](#️-configuration)
- [Usage (Web & API)](#-usage)
- [Customization](#-customization)
- [Troubleshooting](#-troubleshooting)
- [Documentation](#-documentation)

---

## ✨ Features

- 🔐 **Google OAuth** authentication (Web & API)
- 🔐 **Microsoft OAuth** authentication (Web & API)
- 🌐 **Dual Mode Support**: Traditional web and SPA/API applications
- 🎨 **Pre-built Login UI** with beautiful, modern design
- 📱 **Mobile-friendly** responsive design
- 🔄 **Auto-migration** for adding social auth fields to users table
- 🛡️ **Flexible Authentication**: Support for both **Laravel Sanctum** and **Laravel Passport**
- 🔑 **Token Management**: Automatic token generation with expiration support
- 📦 **Easy Installation** with Laravel auto-discovery
- ⚙️ **Highly Configurable**: routes, middleware, redirects, button labels, and more
- 🚀 **Production Ready** with comprehensive error handling
- 📊 **Standardized API Responses** with consistent JSON format

---

## 📋 Requirements

- PHP 8.0 or higher
- Laravel 9.x, 10.x or 11.x
- Laravel Socialite 5.x
- Laravel Sanctum (for API authentication - default) **OR** Laravel Passport (optional)

---

## 📦 Installation

### Step 1: Install via Composer

```bash
composer require emmanuel-saleem/social-auth
```

### Step 2: Install Authentication System (for API routes)

**Option A: Laravel Sanctum (Recommended - Default)**

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

**Option B: Laravel Passport (For OAuth2 Server)**

```bash
composer require laravel/passport
php artisan migrate
php artisan passport:install
```

> 📖 **See [PASSPORT_SETUP.md](./PASSPORT_SETUP.md) for detailed Passport configuration**

### Step 3: Add HasApiTokens to User Model

**For Sanctum (Default):**
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

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
}
```

**For Passport:**
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens; // Use Passport instead

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
}
```

Then configure the driver in `.env`:
```env
# For Passport (optional - default is Sanctum)
SOCIAL_AUTH_API_DRIVER=passport
```

### Step 4: Publish Package Assets

```bash
# Publish configuration file
php artisan vendor:publish --tag=emmanuel-saleem-social-auth-config

# Publish migrations
php artisan vendor:publish --tag=emmanuel-saleem-social-auth-migrations

# Publish views (optional - if you want to customize)
php artisan vendor:publish --tag=emmanuel-saleem-social-auth-views

# Run migrations
php artisan migrate
```

---

## ⚙️ Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/emmanuel-saleem/social-auth/google/callback

# Microsoft OAuth
MICROSOFT_CLIENT_ID=your-microsoft-client-id
MICROSOFT_CLIENT_SECRET=your-microsoft-client-secret
MICROSOFT_REDIRECT_URI=http://localhost:8000/emmanuel-saleem/social-auth/microsoft/callback

# Frontend URL (for API OAuth)
FRONTEND_URL=http://localhost:3000
```

### Update `config/services.php`

Add OAuth provider configurations:

```php
return [
    // ... other services

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
    ],
];
```

### OAuth Provider Setup

#### Google Cloud Console

Follow these steps to create OAuth credentials:

**Step 1:** Go to [Google Cloud Console](https://console.cloud.google.com/)

**Step 2:** Create a new project or select an existing one

![Create Project](images/1.png)

**Step 3:** Navigate to "APIs & Services" → "Credentials"

![APIs & Services](images/2.png)

**Step 4:** Click "CREATE CREDENTIALS" → Select "OAuth client ID"

![Create Credentials](images/3.png)

**Step 5:** Configure the OAuth consent screen (if first time)

![OAuth Consent Screen](images/4.png)

**Step 6:** Select "Web application" as the application type

![Application Type](images/5.png)

**Step 7:** Add your application name and authorized redirect URIs:

![Configure OAuth Client](images/6.png)

**Authorized redirect URIs:**
- For Web: `http://localhost:8000/emmanuel-saleem/social-auth/google/callback`
- For API/SPA: `http://localhost:3000/auth/google/callback`
- For Production: `https://yourdomain.com/emmanuel-saleem/social-auth/google/callback`

**Step 8:** Click "CREATE" and you'll receive your credentials

![Client ID and Secret](images/7.png)

**Step 9:** Copy the **Client ID** and **Client Secret** to your `.env` file

![Copy Credentials](images/8.png)

**Step 10:** Enable required APIs (Google+ API or People API)

![Enable APIs](images/9.png)

✅ **Configuration Complete!** Now add the credentials to your `.env` file:

```env
GOOGLE_CLIENT_ID=your-client-id-from-step-9
GOOGLE_CLIENT_SECRET=your-client-secret-from-step-9
GOOGLE_REDIRECT_URI=http://localhost:8000/emmanuel-saleem/social-auth/google/callback
```

#### Microsoft Azure Portal

1. Go to [Azure Portal](https://portal.azure.com/)
2. Navigate to Azure Active Directory → App registrations
3. Click "New registration"
4. **Redirect URI:** 
   - Web: `http://localhost:8000/emmanuel-saleem/social-auth/microsoft/callback`
   - API: `http://localhost:3000/auth/microsoft/callback`
5. Create client secret under Certificates & secrets
6. **API permissions:** Microsoft Graph → Add permissions:
   - openid
   - profile
   - email
   - User.Read
7. Grant admin consent

---

## 🚀 Quick Start - Add Login to Your App

### How to Add Google & Microsoft Login

After installation, you have **3 easy ways** to add social login to your application:

#### **Method 1: Use the Pre-built Login Page (Easiest)**

Simply redirect users to the package's built-in login page:

```blade
{{-- In your welcome.blade.php or any view --}}
<a href="{{ route('emmanuel-saleem.social-auth.login') }}" class="btn btn-primary">
    Login with Social Account
</a>
```

Visit: `http://localhost:8000/emmanuel-saleem/social-auth/login`

The page will show beautiful Google and Microsoft login buttons automatically! ✨

---

#### **Method 2: Include the Component in Your Own Page**

Add the social auth buttons to any existing page:

```blade
{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Login - My App</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f5f5f5;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 450px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Welcome to My App</h1>
        <p>Sign in to continue</p>
        
        {{-- Include the social auth component --}}
        @include('emmanuel-saleem-social-auth::login')
    </div>
</body>
</html>
```

**That's it!** Both Google and Microsoft buttons will appear with icons and proper styling.

---

#### **Method 3: Create Your Own Custom Buttons**

Use the package routes directly for full customization:

```blade
{{-- Your custom login page --}}
<div class="custom-login">
    <h2>Sign In</h2>
    
    {{-- Google Login Button --}}
    <a href="{{ route('emmanuel-saleem.social-auth.google') }}" class="btn-google">
        <img src="/google-icon.svg" alt="Google">
        Sign in with Google
    </a>
    
    {{-- Microsoft Login Button --}}
    <a href="{{ route('emmanuel-saleem.social-auth.microsoft') }}" class="btn-microsoft">
        <img src="/microsoft-icon.svg" alt="Microsoft">
        Sign in with Microsoft
    </a>
</div>

<style>
    .btn-google {
        background: #4285f4;
        color: white;
        padding: 12px 24px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
        margin: 5px;
    }
    
    .btn-microsoft {
        background: #00a4ef;
        color: white;
        padding: 12px 24px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
        margin: 5px;
    }
</style>
```

---

#### **Logout Button**

Add a logout button anywhere in your app:

```blade
<form action="{{ route('emmanuel-saleem.social-auth.logout') }}" method="POST">
    @csrf
    <button type="submit">Logout</button>
</form>
```

---

### 🎯 What Happens After Login?

1. **User clicks** "Sign in with Google" or "Sign in with Microsoft"
2. **Redirects to** Google/Microsoft for authentication
3. **User approves** the login request
4. **Redirects back** to your app with user data
5. **Package automatically**:
   - Creates a new user (if doesn't exist)
   - Updates existing user info
   - Logs them in
   - Redirects to dashboard (configurable)

**User is now logged in!** 🎉

Access logged-in user anywhere:

```php
// In controllers
$user = Auth::user();
echo $user->name;
echo $user->email;
echo $user->avatar; // Profile picture URL
echo $user->google_id; // or microsoft_id
```

```blade
<!-- In Blade views -->
@auth
    <p>Welcome, {{ Auth::user()->name }}!</p>
    <img src="{{ Auth::user()->avatar }}" alt="Profile">
@endauth
```

---

## 🌐 Complete Web Integration Guide

### Step-by-Step: Add Social Login to Your Laravel Web App

#### **Step 1: Create Your Login Page**

Create or update your login blade file at `resources/views/auth/login.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 48px 40px;
            max-width: 450px;
            width: 100%;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .logo-section h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .logo-section p {
            color: #666;
            font-size: 15px;
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .divider span {
            padding: 0 12px;
            color: #999;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <h1>Welcome Back!</h1>
            <p>Sign in to continue to your account</p>
        </div>

        {{-- Social Auth Buttons Component --}}
        @include('emmanuel-saleem-social-auth::login')

        <div class="divider">
            <span>or continue with email</span>
        </div>

        {{-- Your traditional email/password form (optional) --}}
        <form method="POST" action="{{ route('login') }}" style="display: none;">
            @csrf
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign In</button>
        </form>

        <p style="text-align: center; margin-top: 24px; color: #999; font-size: 13px;">
            By signing in, you agree to our 
            <a href="/terms" style="color: #667eea;">Terms</a> and 
            <a href="/privacy" style="color: #667eea;">Privacy Policy</a>
        </p>
    </div>
</body>
</html>
```

#### **Step 2: Create a Route for Login Page**

Add to `routes/web.php`:

```php
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');
```

#### **Step 3: Update Your Welcome Page (Optional)**

Add a login button to `resources/views/welcome.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
</head>
<body>
    <nav>
        @guest
            <a href="{{ route('login') }}">Login</a>
        @else
            <span>Hello, {{ Auth::user()->name }}</span>
            <form action="{{ route('emmanuel-saleem.social-auth.logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit">Logout</button>
            </form>
        @endguest
    </nav>

    <h1>Welcome to My App</h1>
</body>
</html>
```

#### **Step 4: Create a Dashboard Page**

Create `resources/views/dashboard.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .user-card {
            background: #f5f5f5;
            padding: 24px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .user-card img {
            border-radius: 50%;
            width: 80px;
            height: 80px;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    @auth
        <h1>Dashboard</h1>
        
        <div class="user-card">
            <h2>Welcome, {{ Auth::user()->name }}!</h2>
            
            @if(Auth::user()->avatar)
                <img src="{{ Auth::user()->avatar }}" alt="Profile Picture">
            @endif
            
            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
            <p><strong>Login Method:</strong> 
                @if(Auth::user()->google_id)
                    Google OAuth
                @elseif(Auth::user()->microsoft_id)
                    Microsoft OAuth
                @else
                    Email/Password
                @endif
            </p>
            
            @if(Auth::user()->google_id)
                <p><strong>Google ID:</strong> {{ Auth::user()->google_id }}</p>
            @endif
            
            @if(Auth::user()->microsoft_id)
                <p><strong>Microsoft ID:</strong> {{ Auth::user()->microsoft_id }}</p>
            @endif
        </div>

        <form action="{{ route('emmanuel-saleem.social-auth.logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    @else
        <p>Please <a href="{{ route('login') }}">login</a> to continue.</p>
    @endauth
</body>
</html>
```

#### **Step 5: Add Dashboard Route**

Add to `routes/web.php`:

```php
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard')->middleware('auth');
```

---

### 🧪 Testing Your Social Login

#### **Test 1: Visit Login Page**

```
http://localhost:8000/login
```

**Expected Result:**
- ✅ See a beautiful login page
- ✅ Two buttons: "Continue with Google" and "Continue with Microsoft"
- ✅ Buttons have official brand colors and icons

#### **Test 2: Click "Continue with Google"**

**What Happens:**
1. Redirects to Google login page
2. Google asks for permission
3. You approve
4. Redirects back to your app
5. Package creates/updates user
6. Logs you in automatically
7. Redirects to `/dashboard` (configurable)

**Check Database:**
```bash
php artisan tinker
```

```php
// In tinker
User::latest()->first();
// Should show user with google_id, avatar, etc.
```

#### **Test 3: Check User Data in Dashboard**

Visit: `http://localhost:8000/dashboard`

**Expected Result:**
- ✅ See welcome message with your name
- ✅ See your Google profile picture
- ✅ See your email
- ✅ See "Google OAuth" as login method
- ✅ See Google ID

#### **Test 4: Test Logout**

Click the "Logout" button

**Expected Result:**
- ✅ Logged out successfully
- ✅ Redirected to home page
- ✅ Can't access `/dashboard` anymore

#### **Test 5: Test Microsoft Login**

Follow same steps but click "Continue with Microsoft"

**Expected Result:**
- ✅ Redirects to Microsoft login
- ✅ User created with microsoft_id
- ✅ Redirected to dashboard

---

### 🔍 Debugging & Verification

#### **Check Routes Are Loaded**

```bash
php artisan route:list | grep emmanuel-saleem
```

**Expected Output:**
```
GET|HEAD  emmanuel-saleem/social-auth/login
GET|HEAD  emmanuel-saleem/social-auth/google
GET|HEAD  emmanuel-saleem/social-auth/google/callback
GET|HEAD  emmanuel-saleem/social-auth/microsoft
GET|HEAD  emmanuel-saleem/social-auth/microsoft/callback
POST      emmanuel-saleem/social-auth/logout
```

#### **Check Database Table**

```bash
php artisan migrate:status
```

Should show the social auth migration as completed.

```sql
-- Check users table structure
DESCRIBE users;
```

Should show columns:
- `google_id`
- `microsoft_id`
- `avatar`
- `google_token`
- `google_refresh_token`
- `microsoft_token`
- `microsoft_refresh_token`

#### **Check Logged-in User**

In any controller or view:

```php
// Get current user
$user = Auth::user();

// Check if logged in
if (Auth::check()) {
    echo "User is logged in: " . Auth::user()->email;
}

// Check login method
if ($user->google_id) {
    echo "Logged in with Google";
}

if ($user->microsoft_id) {
    echo "Logged in with Microsoft";
}
```

---

### 📸 Quick Test Checklist

- [ ] Login page displays correctly
- [ ] Google button works and redirects
- [ ] Microsoft button works and redirects  
- [ ] User is created in database
- [ ] User data (name, email, avatar) is saved
- [ ] User is automatically logged in
- [ ] Dashboard shows user info
- [ ] Logout works correctly
- [ ] Can login again after logout
- [ ] Avatar/profile picture displays

---

### 🎨 Customize Redirect After Login

In `.env` file:

```env
# Redirect to custom page after login
SOCIAL_AUTH_REDIRECT_AFTER_LOGIN=/dashboard
SOCIAL_AUTH_REDIRECT_AFTER_LOGOUT=/
```

Or in `config/emmanuel-saleem-social-auth.php`:

```php
'redirect_after_login' => '/my-custom-page',
'redirect_after_logout' => '/welcome',
```

---

## 🚀 Usage

### Option 1: Traditional Web Application

#### Routes Available

The package automatically registers these web routes:

```php
// Login page
GET /emmanuel-saleem/social-auth/login

// Google OAuth
GET /emmanuel-saleem/social-auth/google
GET /emmanuel-saleem/social-auth/google/callback

// Microsoft OAuth (future)
GET /emmanuel-saleem/social-auth/microsoft
GET /emmanuel-saleem/social-auth/microsoft/callback

// Logout
POST /emmanuel-saleem/social-auth/logout
```

#### Implementation

**Option A: Use the built-in login page**

Simply redirect users to the login page:

```html
<!-- In your blade template -->
<a href="{{ route('emmanuel-saleem.social-auth.login') }}" class="btn btn-primary">
    Login with Social
</a>
```

**Option B: Include the component in your own page**

The package provides a reusable Blade component that you can include anywhere:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <div class="container">
        <h1>Welcome to Our App</h1>
        <p>Please sign in to continue</p>
        
        {{-- Include social auth buttons --}}
        @include('emmanuel-saleem-social-auth::login')
    </div>
</body>
</html>
```

**Option C: Use individual buttons**

```html
<!-- Google button -->
<a href="{{ route('emmanuel-saleem.social-auth.google') }}" class="btn btn-google">
    Sign in with Google
</a>

<!-- Microsoft button -->
<a href="{{ route('emmanuel-saleem.social-auth.microsoft') }}" class="btn btn-microsoft">
    Sign in with Microsoft
</a>
```

#### Logout

```html
<form action="{{ route('emmanuel-saleem.social-auth.logout') }}" method="POST">
    @csrf
    <button type="submit">Logout</button>
</form>
```

---

---

### Option 2: API / SPA Application (React, Vue, Next.js)

For modern frontend applications, use the API endpoints.

#### **Frontend Login Flow**

**Step 1:** User clicks "Sign in with Google" button

**Step 2:** Get the OAuth URL from backend:

```javascript
// React/Vue/Next.js
const handleGoogleLogin = async () => {
  const response = await fetch('/api/emmanuel-saleem/auth/google/url');
  const result = await response.json();
  
  if (result.status) {
    // Redirect user to Google
    window.location.href = result.data.url;
  }
};
```

**Step 3:** Handle the callback when user returns:

```javascript
// This runs on your callback page: /auth/google/callback
useEffect(() => {
  const code = new URLSearchParams(window.location.search).get('code');
  
  if (code) {
    fetch('/api/emmanuel-saleem/auth/google/callback', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ code }),
    })
    .then(res => res.json())
    .then(result => {
      if (result.status) {
        // Save token
        localStorage.setItem('token', result.data.token);
        localStorage.setItem('user', JSON.stringify(result.data.user));
        
        // Redirect to dashboard
        window.location.href = '/dashboard';
      }
    });
  }
}, []);
```

**Step 4:** Use the token for authenticated requests:

```javascript
fetch('/api/user/profile', {
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('token')}`,
    'Content-Type': 'application/json',
  },
});
```

For complete React/Vue examples, see [USAGE_EXAMPLES.md](./USAGE_EXAMPLES.md)

#### API Routes Available

```php
// Google OAuth
GET  /api/emmanuel-saleem/auth/google/url
POST /api/emmanuel-saleem/auth/google/callback

// Microsoft OAuth
GET  /api/emmanuel-saleem/auth/microsoft/url
POST /api/emmanuel-saleem/auth/microsoft/callback
```

#### Quick Start Example (React)

```jsx
import React from 'react';

const LoginPage = () => {
  const handleGoogleLogin = async () => {
    // Step 1: Get Google auth URL
    const response = await fetch('/api/emmanuel-saleem/auth/google/url');
    const result = await response.json();
    
    // Step 2: Redirect user to Google
    if (result.status) {
      window.location.href = result.data.url;
    }
  };

  return (
    <button onClick={handleGoogleLogin}>
      Sign in with Google
    </button>
  );
};
```

```jsx
// OAuth Callback Component
import { useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';

const GoogleCallback = () => {
  const navigate = useNavigate();
  const location = useLocation();

  useEffect(() => {
    const handleCallback = async () => {
      const params = new URLSearchParams(location.search);
      const code = params.get('code');

      const response = await fetch('/api/emmanuel-saleem/auth/google/callback', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code }),
      });

      const result = await response.json();

      if (result.status) {
        // Store token
        localStorage.setItem('token', result.data.token);
        navigate('/dashboard');
      }
    };

    handleCallback();
  }, []);

  return <div>Logging in...</div>;
};
```

**📖 For complete API integration guide, see [OAUTH_API_GUIDE.md](./OAUTH_API_GUIDE.md)**

---

## 📡 API Response Format

All API endpoints return standardized JSON responses:

### Success Response

```json
{
  "status": true,
  "success": "success",
  "code": 200,
  "message": "Successfully authenticated with Google",
  "errors": [],
  "data": {
    "token": "1|eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "avatar": "https://...",
      "oauth_provider": "google",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

### Error Response

```json
{
  "status": false,
  "success": "error",
  "code": 401,
  "message": "Failed to authenticate with Google",
  "errors": {
    "exception": "Invalid authorization code"
  },
  "data": null
}
```

---

## 🎨 Customization

### Customize Button Labels

You can customize the button text via `.env` file:

```env
# Button Labels
SOCIAL_AUTH_GOOGLE_LABEL="Sign in with Google"
SOCIAL_AUTH_MICROSOFT_LABEL="Sign in with Microsoft"
```

Or in the config file `config/emmanuel-saleem-social-auth.php`:

```php
'labels' => [
    'google_button' => 'Login with Google Account',
    'microsoft_button' => 'Login with Microsoft Account',
],
```

### Customize Footer

Hide the footer:

```env
SOCIAL_AUTH_SHOW_FOOTER=false
```

Or customize the footer text:

```env
SOCIAL_AUTH_FOOTER_TEXT="Secure Login Powered by OAuth"
```

In config file:

```php
'show_footer' => false, // Hide footer
'footer_text' => 'Your custom footer text',
```

### Customize Routes Prefix

Edit `config/emmanuel-saleem-social-auth.php`:

```php
'route_prefix' => 'auth', // Changes routes to /auth/...
```

### Customize Redirects

```php
'redirect_after_login' => '/dashboard',
'redirect_after_logout' => '/welcome',
```

### Customize Middleware

```php
'middleware' => [
    'web' => ['web', 'guest'],
    'api' => ['api'],
],
```

### Customize Views

After publishing views:

```bash
php artisan vendor:publish --tag=emmanuel-saleem-social-auth-views
```

Edit the views in `resources/views/vendor/emmanuel-saleem-social-auth/`

The component includes:
- ✅ Both Google and Microsoft buttons with official icons
- ✅ Responsive design (mobile-friendly)
- ✅ Error/success message display
- ✅ Modern, clean UI
- ✅ Inline CSS (no external dependencies)
- ✅ Customizable labels and footer
- ✅ Can be included in any existing page

---

## 🗄️ Database Schema

The package adds these fields to your `users` table:

| Column | Type | Description |
|--------|------|-------------|
| `google_id` | string | Google user ID |
| `microsoft_id` | string | Microsoft user ID |
| `avatar` | string | Profile picture URL |
| `google_token` | text | Google access token |
| `google_refresh_token` | text | Google refresh token |
| `microsoft_token` | text | Microsoft access token |
| `microsoft_refresh_token` | text | Microsoft refresh token |

The `password` column is also made nullable to support social-only users.

---

## 🔒 Security

- All OAuth tokens are stored securely in the database
- Passwords for OAuth users are randomly generated and hashed
- Email verification is automatically marked as verified for OAuth users
- CSRF protection on all web routes
- Stateless OAuth for API routes

---

## 🧪 Testing

### Test Web Routes

```bash
# Visit in browser
http://localhost:8000/emmanuel-saleem/social-auth/login
```

### Test API Routes

```bash
# Get Google auth URL
curl http://localhost:8000/api/emmanuel-saleem/auth/google/url

# Test callback (after getting code from Google)
curl -X POST http://localhost:8000/api/emmanuel-saleem/auth/google/callback \
  -H "Content-Type: application/json" \
  -d '{"code":"YOUR_AUTHORIZATION_CODE"}'
```

---

## 🐛 Troubleshooting

### Issue: Composer cache directory not writable (Docker)

**Problem:**
```
Cannot create cache directory /.cache/composer/, or directory is not writable
```

**Solution:** Run composer with proper permissions or disable cache:
```bash
# Option 1: Fix permissions (if using Docker)
docker exec -it your-container chown -R www-data:www-data /.cache

# Option 2: Run composer without cache
composer require emmanuel-saleem/social-auth --no-cache

# Option 3: Run as the correct user
docker exec -it -u www-data your-container composer require emmanuel-saleem/social-auth
```

### Issue: Laravel version conflict

**Problem:**
```
emmanuel-saleem/social-auth requires laravel/framework ^9.0|^10.0|^11.0
```

**Solution:** The package supports Laravel 9, 10, and 11. Check your Laravel version:
```bash
php artisan --version
```

If you're on Laravel 8 or below, you need to upgrade Laravel or use an older version of Socialite.

### Issue: "Class not found" error

**Solution:** Make sure to run `composer dump-autoload`

### Issue: Routes not working

**Solution:** Clear route cache:
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Issue: "redirect_uri_mismatch"

**Solution:** Ensure your redirect URIs in `.env` match exactly with those in Google/Microsoft console

### Issue: Token creation fails

**Solution:** Make sure:
1. Laravel Sanctum is installed
2. User model has `HasApiTokens` trait
3. Migrations have run

### Issue: CORS errors in API

**Solution:** Configure CORS in `config/cors.php`:
```php
'paths' => ['api/*'],
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],
```

---

## 📚 Documentation

- [API Integration Guide](./OAUTH_API_GUIDE.md) - Complete guide for SPA/API integration
- [Passport Setup Guide](./PASSPORT_SETUP.md) - Laravel Passport integration
- [Usage Examples](./USAGE_EXAMPLES.md) - Practical examples for React, Vue, etc.
- [Laravel Socialite Docs](https://laravel.com/docs/socialite)
- [Laravel Sanctum Docs](https://laravel.com/docs/sanctum)
- [Laravel Passport Docs](https://laravel.com/docs/passport)

---

## 🛣️ Roadmap

- [x] Google OAuth support
- [x] Microsoft OAuth support
- [x] API endpoints for SPA
- [x] Standardized response format
- [ ] GitHub OAuth support
- [ ] Facebook OAuth support
- [ ] LinkedIn OAuth support
- [ ] Twitter/X OAuth support
- [ ] Two-factor authentication
- [ ] Social account linking
- [ ] Admin panel for managing OAuth apps

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👨‍💻 Author

**Emmanuel Saleem**

- Email: emmanuelsaleem098765@gmail.com
- LinkedIn: [linkedin.com/in/es77](https://pk.linkedin.com/in/es77)
- GitHub: [@emmanuel-saleem](https://github.com/es-77)

---

## 🙏 Acknowledgments

- Laravel Socialite team
- Laravel Sanctum team
- All contributors

---

## 📞 Support

If you encounter any issues or have questions:

1. Check the [OAUTH_API_GUIDE.md](./OAUTH_API_GUIDE.md)
2. Review [Troubleshooting](#-troubleshooting) section
3. Check existing [GitHub Issues](https://github.com/es-77/social-auth/issues)
4. Create a new issue with detailed information

---

**Made with ❤️ for the Laravel community**

