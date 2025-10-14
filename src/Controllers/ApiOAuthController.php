<?php

namespace EmmanuelSaleem\SocialAuth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class ApiOAuthController extends Controller
{
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

        return Socialite::driver('microsoft');
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
            $request->validate([
                'code' => 'required|string'
            ]);

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
                $user = $userModel::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                ]);
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
            $url = $this->buildMicrosoftDriver()
                ->stateless()
                ->redirect()
                ->getTargetUrl();

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
            $request->validate([
                'code' => 'required|string'
            ]);

            // Exchange code for user info
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
                $user = $userModel::create([
                    'name' => $microsoftUser->name,
                    'email' => $microsoftUser->email,
                    'microsoft_id' => $microsoftUser->id,
                    'avatar' => $microsoftUser->avatar,
                    'microsoft_token' => $microsoftUser->token,
                    'microsoft_refresh_token' => $microsoftUser->refreshToken,
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                ]);
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
}

