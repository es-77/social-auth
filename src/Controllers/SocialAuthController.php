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
        \config([
            'services.google' => [
                'client_id' => \config('emmanuel-saleem-social-auth.google.client_id'),
                'client_secret' => \config('emmanuel-saleem-social-auth.google.client_secret'),
                'redirect' => \config('emmanuel-saleem-social-auth.google.redirect'),
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
                $user = $userModel::create(array_merge($payload, (array) \config('emmanuel-saleem-social-auth.user_defaults', []), $extra));
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
        return $driver->stateless()->redirect();
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
        $request->session()->put('social_auth.extra', $validated['extra'] ?? []);
        return $this->redirectToMicrosoft();
    }

    /**
     * Build Microsoft Socialite driver with package configuration
     */
    protected function buildMicrosoftDriver()
    {
        \config([
            'services.microsoft' => [
                'client_id' => \config('emmanuel-saleem-social-auth.microsoft.client_id'),
                'client_secret' => \config('emmanuel-saleem-social-auth.microsoft.client_secret'),
                'redirect' => \config('emmanuel-saleem-social-auth.microsoft.redirect'),
            ],
        ]);

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
    public function handleMicrosoftCallback()
    {
        try {
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
                $extra = (array) request()->session()->pull('social_auth.extra', []);
                $payload = UserDataMapper::prepare($microsoftUser, 'microsoft');
                $user = $userModel::create(array_merge($payload, (array) \config('emmanuel-saleem-social-auth.user_defaults', []), $extra));
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