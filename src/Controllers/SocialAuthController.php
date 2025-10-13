<?php

namespace EmmanuelSaleem\SocialAuth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
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

            Auth::login($user, true);

            return redirect()->intended(config('emmanuel-saleem-social-auth.redirect_after_login'));
            
        } catch (\Exception $e) {
            return redirect()->route('emmanuel-saleem.social-auth.login')->with('error', 'Failed to login with Google. Please try again.');
        }
    }

    /**
     * Handle Google OAuth callback for API
     */
    public function handleGoogleCallbackApi(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            $user = User::where('google_id', $googleUser->id)
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
        return Socialite::driver('microsoft')->redirect();
    }

    /**
     * Handle Microsoft OAuth callback
     */
    public function handleMicrosoftCallback()
    {
        try {
            $microsoftUser = Socialite::driver('microsoft')->user();
            
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

            Auth::login($user, true);

            return redirect()->intended(config('emmanuel-saleem-social-auth.redirect_after_login'));
            
        } catch (\Exception $e) {
            return redirect()->route('emmanuel-saleem.social-auth.login')->with('error', 'Failed to login with Microsoft. Please try again.');
        }
    }
}