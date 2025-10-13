# Usage Examples

This document provides practical examples of how to use the Emmanuel Saleem Social Auth package in different scenarios.

---

## 📖 Table of Contents

1. [Basic Web Application](#basic-web-application)
2. [Include Component in Existing Page](#include-component-in-existing-page)
3. [Customize Button Labels](#customize-button-labels)
4. [Custom Styling](#custom-styling)
5. [API Integration (React)](#api-integration-react)
6. [API Integration (Vue.js)](#api-integration-vuejs)
7. [Handling User After Login](#handling-user-after-login)

---

## Basic Web Application

### Simple Login Page

Create a route to the built-in login page:

```php
// routes/web.php
Route::get('/login', function () {
    return redirect()->route('emmanuel-saleem.social-auth.login');
});
```

---

## Include Component in Existing Page

### Laravel Blade Template

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - My App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Welcome to My App</h1>
        <p class="subtitle">Please sign in to continue</p>
        
        {{-- Include social auth component --}}
        @include('emmanuel-saleem-social-auth::login')
        
        <p style="text-align: center; margin-top: 20px; color: #999; font-size: 14px;">
            By signing in, you agree to our Terms of Service and Privacy Policy.
        </p>
    </div>
</body>
</html>
```

---

## Customize Button Labels

### Via .env File

```env
# .env
SOCIAL_AUTH_GOOGLE_LABEL="Sign in with my Google Account"
SOCIAL_AUTH_MICROSOFT_LABEL="Use Microsoft 365"
SOCIAL_AUTH_FOOTER_TEXT="Secure OAuth Authentication"
```

### Via Config File

```php
// config/emmanuel-saleem-social-auth.php
return [
    // ... other config
    
    'labels' => [
        'google_button' => 'Login with Google Workspace',
        'microsoft_button' => 'Login with Microsoft Teams',
    ],
    
    'show_footer' => true,
    'footer_text' => 'Enterprise Grade Security',
];
```

---

## Custom Styling

### Override Component Styles

After including the component, add your own styles:

```blade
@include('emmanuel-saleem-social-auth::login')

<style>
    /* Override default styles */
    .emmanuel-saleem-social-auth {
        max-width: 600px;
    }
    
    .es-social-btn {
        padding: 16px 32px;
        font-size: 16px;
        border-radius: 12px;
    }
    
    .es-google-btn {
        background: linear-gradient(135deg, #4285f4 0%, #34a853 100%);
        color: white;
        border: none;
    }
    
    .es-microsoft-btn {
        background: linear-gradient(135deg, #00a4ef 0%, #0078d4 100%);
        color: white;
        border: none;
    }
    
    .es-icon {
        width: 24px;
        height: 24px;
    }
</style>
```

---

## API Integration (React)

### Complete React Example with TypeScript

```typescript
// src/pages/Login.tsx
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';

interface AuthResponse {
  status: boolean;
  success: string;
  code: number;
  message: string;
  errors: any[];
  data: {
    token: string;
    user: {
      id: number;
      name: string;
      email: string;
      avatar: string;
      oauth_provider: string;
    };
  } | null;
}

const LoginPage: React.FC = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleSocialLogin = async (provider: 'google' | 'microsoft') => {
    try {
      setLoading(provider);
      setError(null);

      const response = await fetch(`/api/emmanuel-saleem/auth/${provider}/url`);
      const result: AuthResponse = await response.json();

      if (result.status && result.data) {
        // Redirect to OAuth provider
        window.location.href = result.data.url;
      } else {
        setError(result.message || 'Failed to initiate login');
        setLoading(null);
      }
    } catch (err) {
      setError('An error occurred. Please try again.');
      setLoading(null);
    }
  };

  return (
    <div className="login-container">
      <div className="login-card">
        <h1>Welcome Back</h1>
        <p>Sign in to continue to your account</p>

        {error && (
          <div className="alert alert-error">
            {error}
          </div>
        )}

        <button
          onClick={() => handleSocialLogin('google')}
          disabled={loading !== null}
          className="social-btn google-btn"
        >
          {loading === 'google' ? 'Loading...' : 'Continue with Google'}
        </button>

        <button
          onClick={() => handleSocialLogin('microsoft')}
          disabled={loading !== null}
          className="social-btn microsoft-btn"
        >
          {loading === 'microsoft' ? 'Loading...' : 'Continue with Microsoft'}
        </button>
      </div>
    </div>
  );
};

export default LoginPage;
```

### OAuth Callback Handler

```typescript
// src/pages/OAuthCallback.tsx
import React, { useEffect, useState } from 'react';
import { useNavigate, useLocation, useParams } from 'react-router-dom';

const OAuthCallback: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const { provider } = useParams<{ provider: string }>();
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const handleCallback = async () => {
      const params = new URLSearchParams(location.search);
      const code = params.get('code');
      const errorParam = params.get('error');

      if (errorParam) {
        setError('Authentication was cancelled or failed');
        setTimeout(() => navigate('/login'), 3000);
        return;
      }

      if (!code) {
        setError('No authorization code received');
        setTimeout(() => navigate('/login'), 3000);
        return;
      }

      try {
        const response = await fetch(
          `/api/emmanuel-saleem/auth/${provider}/callback`,
          {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ code }),
          }
        );

        const result = await response.json();

        if (result.status && result.data) {
          // Store token and user info
          localStorage.setItem('access_token', result.data.token);
          localStorage.setItem('user', JSON.stringify(result.data.user));

          // Redirect to dashboard
          navigate('/dashboard');
        } else {
          setError(result.message || 'Authentication failed');
          setTimeout(() => navigate('/login'), 3000);
        }
      } catch (err) {
        setError('An error occurred during authentication');
        setTimeout(() => navigate('/login'), 3000);
      }
    };

    handleCallback();
  }, [location, navigate, provider]);

  if (error) {
    return (
      <div className="callback-container">
        <div className="error-box">
          <h2>Authentication Error</h2>
          <p>{error}</p>
          <p>Redirecting to login...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="callback-container">
      <div className="loading-box">
        <div className="spinner"></div>
        <p>Completing authentication...</p>
      </div>
    </div>
  );
};

export default OAuthCallback;
```

---

## API Integration (Vue.js)

### Vue 3 Composition API Example

```vue
<!-- src/views/Login.vue -->
<template>
  <div class="login-container">
    <div class="login-card">
      <h1>Welcome Back</h1>
      <p>Sign in to continue to your account</p>

      <div v-if="error" class="alert alert-error">
        {{ error }}
      </div>

      <button
        @click="handleSocialLogin('google')"
        :disabled="loading !== null"
        class="social-btn google-btn"
      >
        {{ loading === 'google' ? 'Loading...' : 'Continue with Google' }}
      </button>

      <button
        @click="handleSocialLogin('microsoft')"
        :disabled="loading !== null"
        class="social-btn microsoft-btn"
      >
        {{ loading === 'microsoft' ? 'Loading...' : 'Continue with Microsoft' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const loading = ref<string | null>(null);
const error = ref<string | null>(null);

const handleSocialLogin = async (provider: 'google' | 'microsoft') => {
  try {
    loading.value = provider;
    error.value = null;

    const response = await fetch(`/api/emmanuel-saleem/auth/${provider}/url`);
    const result = await response.json();

    if (result.status && result.data) {
      window.location.href = result.data.url;
    } else {
      error.value = result.message || 'Failed to initiate login';
      loading.value = null;
    }
  } catch (err) {
    error.value = 'An error occurred. Please try again.';
    loading.value = null;
  }
};
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.login-card {
  background: white;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
  max-width: 400px;
  width: 100%;
}

.social-btn {
  width: 100%;
  padding: 12px 24px;
  margin: 8px 0;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
}

.google-btn {
  background: #4285f4;
  color: white;
}

.microsoft-btn {
  background: #00a4ef;
  color: white;
}

.social-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.social-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
```

---

## Handling User After Login

### Redirect Based on User Role

```php
// app/Http/Controllers/SocialAuthController.php (extend the package controller)

namespace App\Http\Controllers;

use EmmanuelSaleem\SocialAuth\Controllers\SocialAuthController as BaseSocialAuthController;
use Illuminate\Support\Facades\Auth;

class CustomSocialAuthController extends BaseSocialAuthController
{
    public function handleGoogleCallback()
    {
        $response = parent::handleGoogleCallback();
        
        $user = Auth::user();
        
        // Redirect based on user role
        if ($user->is_admin) {
            return redirect('/admin/dashboard');
        } elseif ($user->is_vendor) {
            return redirect('/vendor/products');
        } else {
            return redirect('/dashboard');
        }
    }
}
```

### Store Additional User Data

```php
// Listen to user creation/update
// app/Providers/EventServiceProvider.php

use Illuminate\Support\Facades\Event;
use App\Models\User;

Event::listen('eloquent.created: ' . User::class, function ($user) {
    if ($user->google_id || $user->microsoft_id) {
        // User logged in via OAuth
        // Send welcome email, create default preferences, etc.
        $user->preferences()->create([
            'notifications' => true,
            'theme' => 'light',
        ]);
    }
});
```

---

## Complete Example: Multi-tenant Application

```php
// config/emmanuel-saleem-social-auth.php
return [
    'redirect_after_login' => env('APP_URL') . '/oauth/callback',
    
    'labels' => [
        'google_button' => 'Sign in with Google Workspace',
        'microsoft_button' => 'Sign in with Microsoft 365',
    ],
];
```

```php
// routes/web.php
Route::get('/oauth/callback', [App\Http\Controllers\OAuthCallbackController::class, 'handle']);
```

```php
// app/Http/Controllers/OAuthCallbackController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OAuthCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $user = Auth::user();
        
        // Determine tenant from email domain
        $domain = substr(strrchr($user->email, "@"), 1);
        $tenant = \App\Models\Tenant::where('domain', $domain)->first();
        
        if (!$tenant) {
            // Create new tenant
            $tenant = \App\Models\Tenant::create([
                'domain' => $domain,
                'name' => ucfirst(str_replace('.com', '', $domain)),
            ]);
        }
        
        // Associate user with tenant
        $user->tenant_id = $tenant->id;
        $user->save();
        
        return redirect('/dashboard');
    }
}
```

---

## Testing Examples

### Feature Test for Web OAuth

```php
// tests/Feature/SocialAuthTest.php
namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;

class SocialAuthTest extends TestCase
{
    public function test_google_redirect()
    {
        $response = $this->get(route('emmanuel-saleem.social-auth.google'));
        
        $response->assertRedirect();
    }
    
    public function test_google_callback_creates_user()
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('123456');
        $abstractUser->shouldReceive('getName')->andReturn('John Doe');
        $abstractUser->shouldReceive('getEmail')->andReturn('john@example.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://avatar.url');
        
        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);
        
        $response = $this->get(route('emmanuel-saleem.social-auth.google.callback'));
        
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'google_id' => '123456',
        ]);
    }
}
```

---

For more examples, check the main [README.md](./README.md) and [OAUTH_API_GUIDE.md](./OAUTH_API_GUIDE.md).

