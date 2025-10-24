<?php

namespace EmmanuelSaleem\SocialAuth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use EmmanuelSaleem\SocialAuth\Support\UserDataMapper;

class SocialAuthController extends Controller
{
    /**
     * Determine if a URL string is absolute (has a scheme and host)
     */
    protected function isAbsoluteUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }
        $parts = \parse_url($url);
        return !empty($parts['scheme']) && !empty($parts['host']);
    }

    /**
     * Resolve absolute redirect URI for the given route name when needed
     */
    protected function resolveAbsoluteRedirect(string $configuredRedirect, string $fallbackRouteName): string
    {
        if ($this->isAbsoluteUrl($configuredRedirect)) {
            return $configuredRedirect;
        }

        // Build absolute URL to the package callback route as a safe fallback
        try {
            return \route($fallbackRouteName);
        } catch (\Throwable $e) {
            // Last resort, return configured value (may still error at provider)
            \Log::warning('Microsoft redirect not absolute and route generation failed', [
                'configured' => $configuredRedirect,
                'route' => $fallbackRouteName,
                'exception' => $e->getMessage(),
            ]);
            return $configuredRedirect;
        }
    }
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return $this->buildGoogleDriver()->stateless()->redirect();
    }

    /**
     * Validate and store required fields for Google then redirect
     */
    public function prepareGoogleLogin(Request $request)
    {
        $requiredFields = (array) \config('emmanuel-saleem-social-auth.required_fields', []);
        $rules = [];
        foreach ($requiredFields as $field) {
            $name = 'extra.' . ($field['name'] ?? '');
            $rules[$name] = !empty($field['required']) ? 'required' : 'nullable';
        }
        $validated = $request->validate($rules);
        $request->session()->put('social_auth.extra', $validated['extra'] ?? []);
        return $this->redirectToGoogle();
    }

    /**
     * Build Google Socialite driver with package configuration
     */
    protected function buildGoogleDriver()
    {
        $clientId = \config('emmanuel-saleem-social-auth.google.client_id');
        $clientSecret = \config('emmanuel-saleem-social-auth.google.client_secret');
        $redirect = \config('emmanuel-saleem-social-auth.google.redirect');

        // Debug: Log Google configuration (without exposing secrets)
        \Log::info('Google OAuth configuration', [
            'client_id' => $clientId ? substr($clientId, 0, 10) . '...' : 'NOT_SET',
            'client_secret' => $clientSecret ? 'SET' : 'NOT_SET',
            'redirect' => $redirect,
        ]);

        \config([
            'services.google' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect' => $redirect,
            ],
        ]);

        return Socialite::driver('google');
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Use stateless for web to avoid session state issues
            $googleUser = $this->buildGoogleDriver()->stateless()->user();
            
            $userModel = \config('emmanuel-saleem-social-auth.user_model', 'App\\Models\\User');
            $user = $userModel::where('google_id', $googleUser->id)
                       ->orWhere('email', $googleUser->email)
                       ->first();

            if ($user) {
                // Update existing user
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                ]);
            } else {
                // Create new user
                $extra = (array) $request->session()->pull('social_auth.extra', []);
                $payload = UserDataMapper::prepare($googleUser, 'google');
                $userDefaults = (array) \config('emmanuel-saleem-social-auth.user_defaults', []);
                
                // Debug: Log what's being merged
                \Log::info('Google OAuth user creation data', [
                    'extra_from_form' => $extra,
                    'payload_from_oauth' => $payload,
                    'user_defaults' => $userDefaults,
                ]);
                
                // Merge in order: payload -> user_defaults -> extra (extra should override defaults)
                $user = $userModel::create(array_merge($payload, $userDefaults, $extra));
            }

            Auth::login($user, true);

            return \redirect()->intended(\config('emmanuel-saleem-social-auth.redirect_after_login'));
            
        } catch (\Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return \redirect()->route('emmanuel-saleem.social-auth.login')
                ->with('error', 'Failed to login with Google: ' . $e->getMessage());
        }
    }

    /**
     * Handle Google OAuth callback for API
     */
    public function handleGoogleCallbackApi(Request $request)
    {
        try {
            $googleUser = $this->buildGoogleDriver()->stateless()->user();
            
            $userModel = \config('emmanuel-saleem-social-auth.user_model', 'App\\Models\\User');
            $user = $userModel::where('google_id', $googleUser->id)
                       ->orWhere('email', $googleUser->email)
                       ->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                ]);
            } else {
                $payload = UserDataMapper::prepare($googleUser, 'google');
                $user = $userModel::create(array_merge($payload, (array) \config('emmanuel-saleem-social-auth.user_defaults', [])));
            }

            $token = $user->createToken('google-auth')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to login with Google.',
            ], 401);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(config('emmanuel-saleem-social-auth.redirect_after_logout'));
    }

    /**
     * Show login page
     */
    public function showLoginPage()
    {
        return view('emmanuel-saleem-social-auth::login');
    }

    /**
     * Redirect to Microsoft OAuth
     */
    public function redirectToMicrosoft()
    {
        $driver = $this->buildMicrosoftDriver();
        $scopes = (array) \config('emmanuel-saleem-social-auth.microsoft.scopes', []);
        if (!empty($scopes)) {
            $driver->scopes($scopes);
        }
        $response = $driver->stateless()->redirect();
        try {
            $targetUrl = $response->getTargetUrl();
        } catch (\Throwable $e) {
            $targetUrl = null;
        }
        \Log::info('Microsoft OAuth redirect (web) generated', [
            'target_url' => $targetUrl,
            'scopes' => $scopes,
        ]);
        return $response;
    }

    /**
     * Validate and store required fields for Microsoft then redirect
     */
    public function prepareMicrosoftLogin(Request $request)
    {
        $requiredFields = (array) \config('emmanuel-saleem-social-auth.required_fields', []);
        $rules = [];
        foreach ($requiredFields as $field) {
            $name = 'extra.' . ($field['name'] ?? '');
            $rules[$name] = !empty($field['required']) ? 'required' : 'nullable';
        }
        $validated = $request->validate($rules);
        $extraData = $validated['extra'] ?? [];
        
        // Debug: Log what we're storing in session
        \Log::info('Microsoft prepare login - storing extra data', [
            'extra_data' => $extraData,
            'session_id' => $request->session()->getId(),
        ]);
        
        $request->session()->put('social_auth.extra', $extraData);
        
        // Verify it was stored
        $stored = $request->session()->get('social_auth.extra', []);
        \Log::info('Microsoft prepare login - verification', [
            'stored_data' => $stored,
        ]);
        
        return $this->redirectToMicrosoft();
    }

    /**
     * Build Microsoft Socialite driver with package configuration
     */
    protected function buildMicrosoftDriver()
    {
        $configuredRedirect = (string) \config('emmanuel-saleem-social-auth.microsoft.redirect');
        $redirect = $this->resolveAbsoluteRedirect(
            $configuredRedirect,
            'emmanuel-saleem.social-auth.microsoft.callback'
        );

        $tenant = \config('emmanuel-saleem-social-auth.microsoft.tenant');

        $serviceConfig = [
            'client_id' => \config('emmanuel-saleem-social-auth.microsoft.client_id'),
            'client_secret' => \config('emmanuel-saleem-social-auth.microsoft.client_secret'),
            'redirect' => $redirect,
        ];

        if (!empty($tenant)) {
            $serviceConfig['tenant'] = $tenant;
        }

        \Log::info('Microsoft OAuth driver configuration (web)', [
            'redirect' => $serviceConfig['redirect'] ?? null,
            'tenant' => $serviceConfig['tenant'] ?? null,
            'scopes' => (array) \config('emmanuel-saleem-social-auth.microsoft.scopes', []),
            'services_microsoft_config' => \config('services.microsoft', []),
        ]);

        \config(['services.microsoft' => $serviceConfig]);

        $driver = Socialite::driver('microsoft');
        $scopes = (array) \config('emmanuel-saleem-social-auth.microsoft.scopes', []);
        if (!empty($scopes)) {
            $driver->scopes($scopes);
        }
        return $driver;
    }

    /**
     * Handle Microsoft OAuth callback
     */
    public function handleMicrosoftCallback(Request $request)
    {
        try {
            \Log::info('Microsoft OAuth callback (web) received', [
                'query' => $request->query(),
                'has_code' => $request->has('code'),
                'has_state' => $request->has('state'),
                'error' => $request->get('error'),
                'error_description' => $request->get('error_description'),
            ]);
            if ($request->has('error')) {
                $error = $request->get('error');
                $desc = $request->get('error_description');
                \Log::error('Microsoft OAuth returned error', ['error' => $error, 'description' => $desc]);
                return \redirect()->route('emmanuel-saleem.social-auth.login')
                    ->with('error', 'Failed to login with Microsoft: ' . ($desc ?: $error));
            }
            if (!$request->has('code')) {
                \Log::error('Microsoft OAuth missing authorization code on callback', ['query' => $request->query()]);
                return \redirect()->route('emmanuel-saleem.social-auth.login')
                    ->with('error', 'Failed to login with Microsoft: missing authorization code.');
            }
            \Log::info('Microsoft OAuth token exchange starting (web)');
            // Use stateless for web to avoid session state issues
            $microsoftUser = $this->buildMicrosoftDriver()->stateless()->user();
            
            $userModel = \config('emmanuel-saleem-social-auth.user_model', 'App\\Models\\User');
            $user = $userModel::where('microsoft_id', $microsoftUser->id)
                       ->orWhere('email', $microsoftUser->email)
                       ->first();

            if ($user) {
                // Update existing user
                $user->update([
                    'microsoft_id' => $microsoftUser->id,
                    'avatar' => $microsoftUser->avatar,
                    'microsoft_token' => $microsoftUser->token,
                    'microsoft_refresh_token' => $microsoftUser->refreshToken,
                ]);
            } else {
                // Create new user
                $extra = (array) $request->session()->pull('social_auth.extra', []);
                $payload = UserDataMapper::prepare($microsoftUser, 'microsoft');
                $userDefaults = (array) \config('emmanuel-saleem-social-auth.user_defaults', []);
                
                // Debug: Log what's being merged
                \Log::info('Microsoft OAuth user creation data', [
                    'session_id' => $request->session()->getId(),
                    'extra_from_form' => $extra,
                    'payload_from_oauth' => $payload,
                    'user_defaults' => $userDefaults,
                    'final_merged_data' => array_merge($payload, $userDefaults, $extra),
                ]);
                
                // Merge in order: payload -> user_defaults -> extra (extra should override defaults)
                $user = $userModel::create(array_merge($payload, $userDefaults, $extra));
            }

            Auth::login($user, true);

            return \redirect()->intended(\config('emmanuel-saleem-social-auth.redirect_after_login'));
            
        } catch (\Exception $e) {
            \Log::error('Microsoft OAuth Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return \redirect()->route('emmanuel-saleem.social-auth.login')
                ->with('error', 'Failed to login with Microsoft: ' . $e->getMessage());
        }
    }

    /**
     * Separate Microsoft OAuth flow that also fetches Microsoft Graph profile using the access token
     */
    public function redirectToMicrosoftGraph()
    {
        return $this->buildMicrosoftDriver()->stateless()->redirect();
    }

    /**
     * Handle Microsoft OAuth callback and call Microsoft Graph /me
     */
    public function handleMicrosoftGraphCallback(Request $request)
    {
        try {
            $microsoftUser = $this->buildMicrosoftDriver()->stateless()->user();

            $userModel = \config('emmanuel-saleem-social-auth.user_model', 'App\\Models\\User');
            $user = $userModel::where('microsoft_id', $microsoftUser->id)
                       ->orWhere('email', $microsoftUser->email)
                       ->first();

            if ($user) {
                $user->update([
                    'microsoft_id' => $microsoftUser->id,
                    'avatar' => $microsoftUser->avatar,
                    'microsoft_token' => $microsoftUser->token,
                    'microsoft_refresh_token' => $microsoftUser->refreshToken,
                ]);
            } else {
                $extra = (array) $request->session()->pull('social_auth.extra', []);
                $payload = UserDataMapper::prepare($microsoftUser, 'microsoft');
                $user = $userModel::create(array_merge($payload, (array) \config('emmanuel-saleem-social-auth.user_defaults', []), $extra));
            }

            Auth::login($user, true);

            // Use the OAuth access token to call Microsoft Graph APIs
            $graphMeResponse = Http::withToken($microsoftUser->token)
                ->get('https://graph.microsoft.com/v1.0/me');

            $graphData = [];
            if ($graphMeResponse->ok()) {
                $graphData['profile'] = $graphMeResponse->json();
            }

            // Fetch contacts if Contacts.Read permission is available
            $contactsResponse = Http::withToken($microsoftUser->token)
                ->get('https://graph.microsoft.com/v1.0/me/contacts');

            if ($contactsResponse->ok()) {
                $graphData['contacts'] = $contactsResponse->json();
            }

            // Store all Graph data in session for downstream use
            if (!empty($graphData)) {
                $request->session()->put('microsoft.graph', $graphData);
            }

            return \redirect()->intended(\config('emmanuel-saleem-social-auth.redirect_after_login'));
        } catch (\Exception $e) {
            \Log::error('Microsoft OAuth Graph Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return \redirect()->route('emmanuel-saleem.social-auth.login')
                ->with('error', 'Failed to login with Microsoft Graph: ' . $e->getMessage());
        }
    }
}