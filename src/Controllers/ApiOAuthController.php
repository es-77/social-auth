<?php

namespace EmmanuelSaleem\SocialAuth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use EmmanuelSaleem\SocialAuth\Support\UserDataMapper;

class ApiOAuthController extends Controller
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
     * Get required fields configuration for clients
     */
    public function getRequiredFields(): JsonResponse
    {
        return $this->successResponse([
            'required_fields' => (array) \config('emmanuel-saleem-social-auth.required_fields', []),
            'defaults' => (array) \config('emmanuel-saleem-social-auth.user_defaults', []),
        ], 'Required fields fetched');
    }
    /**
     * Build Google Socialite driver with package configuration
     */
    protected function buildGoogleDriver()
    {
        $redirect = \config('emmanuel-saleem-social-auth.google.api_redirect')
            ?: \config('emmanuel-saleem-social-auth.google.redirect');

        \config([
            'services.google' => [
                'client_id' => \config('emmanuel-saleem-social-auth.google.client_id'),
                'client_secret' => \config('emmanuel-saleem-social-auth.google.client_secret'),
                'redirect' => $redirect,
            ],
        ]);

        return Socialite::driver('google');
    }

    /**
     * Build Microsoft Socialite driver with package configuration
     */
    protected function buildMicrosoftDriver()
    {
        $redirect = (string) \config('emmanuel-saleem-social-auth.microsoft.api_redirect')
            ?: (string) \config('emmanuel-saleem-social-auth.microsoft.redirect');

        if (!$this->isAbsoluteUrl($redirect)) {
            try {
                $redirect = \url($redirect);
            } catch (\Throwable $e) {
                // keep as-is if URL helper fails
            }
        }

        $tenant = \config('emmanuel-saleem-social-auth.microsoft.tenant');

        $serviceConfig = [
            'client_id' => \config('emmanuel-saleem-social-auth.microsoft.client_id'),
            'client_secret' => \config('emmanuel-saleem-social-auth.microsoft.client_secret'),
            'redirect' => $redirect,
        ];

        if (!empty($tenant)) {
            $serviceConfig['tenant'] = $tenant;
        }

        \Log::info('Microsoft OAuth driver configuration (api)', [
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
     * Get Google OAuth authorization URL
     *
     * @return JsonResponse
     */
    public function getGoogleAuthUrl(): JsonResponse
    {
        try {
            $url = $this->buildGoogleDriver()
                ->stateless()
                ->redirect()
                ->getTargetUrl();

            return $this->successResponse([
                'url' => $url,
                'provider' => 'google'
            ], 'Google authorization URL generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to generate Google authorization URL',
                500,
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Handle Google OAuth callback
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleGoogleCallback(Request $request): JsonResponse
    {
        try {
            // Validate incoming code and optional extra fields
            $requiredFields = (array) \config('emmanuel-saleem-social-auth.required_fields', []);
            $rules = [
                'code' => 'required|string',
                'extra' => 'nullable|array',
            ];
            foreach ($requiredFields as $field) {
                $name = 'extra.' . ($field['name'] ?? '');
                $rules[$name] = !empty($field['required']) ? 'required' : 'nullable';
            }
            $validated = $request->validate($rules);

            // Exchange code for user info
            $googleUser = $this->buildGoogleDriver()
                ->stateless()
                ->user();

            // Get configured user model
            $userModel = \config('emmanuel-saleem-social-auth.user_model', 'App\\Models\\User');

            // Find or create user
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
                $extra = (array) ($validated['extra'] ?? []);
                $payload = UserDataMapper::prepare($googleUser, 'google');
                $user = $userModel::create(array_merge($payload, (array) \config('emmanuel-saleem-social-auth.user_defaults', []), $extra));
            }

            // Generate access token (Sanctum or Passport)
            $tokenData = $this->generateAccessToken($user, 'google');

            return $this->successResponse([
                'token' => $tokenData['token'],
                'token_type' => $tokenData['token_type'],
                'expires_in' => $tokenData['expires_in'] ?? null,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'oauth_provider' => 'google',
                    'created_at' => $user->created_at,
                ]
            ], 'Successfully authenticated with Google');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                'Validation failed',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to authenticate with Google',
                401,
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Get Microsoft OAuth authorization URL
     *
     * @return JsonResponse
     */
    public function getMicrosoftAuthUrl(): JsonResponse
    {
        try {
            $driver = $this->buildMicrosoftDriver()->stateless();
            $response = $driver->redirect();
            $url = $response->getTargetUrl();
            \Log::info('Microsoft OAuth redirect (api) generated', [
                'target_url' => $url,
            ]);

            return $this->successResponse([
                'url' => $url,
                'provider' => 'microsoft'
            ], 'Microsoft authorization URL generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to generate Microsoft authorization URL',
                500,
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Handle Microsoft OAuth callback
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleMicrosoftCallback(Request $request): JsonResponse
    {
        try {
            \Log::info('Microsoft OAuth callback (api) received', [
                'input' => $request->all(),
                'has_code' => $request->has('code'),
                'has_state' => $request->has('state'),
            ]);
            // Validate incoming code and optional extra fields
            $requiredFields = (array) \config('emmanuel-saleem-social-auth.required_fields', []);
            $rules = [
                'code' => 'required|string',
                'extra' => 'nullable|array',
            ];
            foreach ($requiredFields as $field) {
                $name = 'extra.' . ($field['name'] ?? '');
                $rules[$name] = !empty($field['required']) ? 'required' : 'nullable';
            }
            $validated = $request->validate($rules);

            // Exchange code for user info
            \Log::info('Microsoft OAuth token exchange starting (api)');
            $microsoftUser = $this->buildMicrosoftDriver()
                ->stateless()
                ->user();

            // Get configured user model
            $userModel = \config('emmanuel-saleem-social-auth.user_model', 'App\\Models\\User');

            // Find or create user
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
                $extra = (array) ($validated['extra'] ?? []);
                $payload = UserDataMapper::prepare($microsoftUser, 'microsoft');
                $user = $userModel::create(array_merge($payload, (array) \config('emmanuel-saleem-social-auth.user_defaults', []), $extra));
            }

            // Generate access token (Sanctum or Passport)
            $tokenData = $this->generateAccessToken($user, 'microsoft');

            return $this->successResponse([
                'token' => $tokenData['token'],
                'token_type' => $tokenData['token_type'],
                'expires_in' => $tokenData['expires_in'] ?? null,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'oauth_provider' => 'microsoft',
                    'created_at' => $user->created_at,
                ]
            ], 'Successfully authenticated with Microsoft');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                'Validation failed',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to authenticate with Microsoft',
                401,
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Success response helper
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse(mixed $data = null, string $message = 'success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'success' => 'success',
            'code' => $code,
            'message' => $message,
            'errors' => [],
            'data' => $data,
        ], $code)->header('Content-Type', 'application/json');
    }

    /**
     * Error response helper
     *
     * @param string $message
     * @param int $code
     * @param array $errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $code = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'status' => false,
            'success' => 'error',
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
            'data' => null,
        ], $code)->header('Content-Type', 'application/json');
    }

    /**
     * Generate access token based on configured driver
     *
     * @param mixed $user
     * @param string $provider
     * @return array
     */
    protected function generateAccessToken($user, string $provider): array
    {
        $driver = config('emmanuel-saleem-social-auth.api_auth_driver', 'sanctum');

        if ($driver === 'passport') {
            // Laravel Passport
            $tokenResult = $user->createToken(
                config('emmanuel-saleem-social-auth.passport.token_name', 'oauth-token'),
                config('emmanuel-saleem-social-auth.passport.scopes', [])
            );

            return [
                'token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_in' => $tokenResult->token->expires_at 
                    ? $tokenResult->token->expires_at->diffInSeconds(now()) 
                    : null,
            ];
        }

        // Laravel Sanctum (default)
        $token = $user->createToken($provider . '-oauth-token')->plainTextToken;

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => null, // Sanctum tokens don't expire by default
        ];
    }

    /**
     * Prepare user data for creation based on configuration
     *
     * @param object $oauthUser
     * @param string $provider
     * @return array
     */
    protected function prepareUserData($oauthUser, string $provider): array
    {
        $nameField = config('emmanuel-saleem-social-auth.user_fields.name_field', 'name');
        $additionalFields = config('emmanuel-saleem-social-auth.user_fields.additional_fields', []);
        
        $userData = [];

        // Handle name field(s)
        if ($nameField === 'first_last') {
            // Split name into first_name and last_name
            $nameParts = explode(' ', $oauthUser->name, 2);
            $userData['first_name'] = $nameParts[0] ?? '';
            $userData['last_name'] = $nameParts[1] ?? '';
        } else {
            // Use single name field
            $userData['name'] = $oauthUser->name;
        }

        // Add common fields
        $userData['email'] = $oauthUser->email;
        $userData['avatar'] = $oauthUser->avatar;
        $userData['email_verified_at'] = now();
        $userData['password'] = Hash::make(Str::random(24));

        // Add provider-specific fields
        if ($provider === 'google') {
            $userData['google_id'] = $oauthUser->id;
            $userData['google_token'] = $oauthUser->token;
            $userData['google_refresh_token'] = $oauthUser->refreshToken;
        } elseif ($provider === 'microsoft') {
            $userData['microsoft_id'] = $oauthUser->id;
            $userData['microsoft_token'] = $oauthUser->token;
            $userData['microsoft_refresh_token'] = $oauthUser->refreshToken;
        }

        // Merge additional fields from config
        $userData = array_merge($userData, $additionalFields);

        return $userData;
    }
}

