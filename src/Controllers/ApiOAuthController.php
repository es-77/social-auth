<?php

namespace EmmanuelSaleem\SocialAuth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class ApiOAuthController extends Controller
{
    /**
     * Get Google OAuth authorization URL
     *
     * @return JsonResponse
     */
    public function getGoogleAuthUrl(): JsonResponse
    {
        try {
            $url = Socialite::driver('google')
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
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            // Find or create user
            $user = User::where('google_id', $googleUser->id)
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
                $user = User::create([
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

            // Generate access token (Laravel Sanctum)
            $token = $user->createToken('google-oauth-token')->plainTextToken;

            return $this->successResponse([
                'token' => $token,
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
            $url = Socialite::driver('microsoft')
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
            $microsoftUser = Socialite::driver('microsoft')
                ->stateless()
                ->user();

            // Find or create user
            $user = User::where('microsoft_id', $microsoftUser->id)
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
                $user = User::create([
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

            // Generate access token (Laravel Sanctum)
            $token = $user->createToken('microsoft-oauth-token')->plainTextToken;

            return $this->successResponse([
                'token' => $token,
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
}

