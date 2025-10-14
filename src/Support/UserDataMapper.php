<?php

namespace EmmanuelSaleem\SocialAuth\Support;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserDataMapper
{
    /**
     * Prepare user payload to insert/update based on config mappings.
     *
     * @param object $oauthUser  Socialite user object
     * @param string $provider   'google' | 'microsoft'
     * @return array
     */
    public static function prepare(object $oauthUser, string $provider): array
    {
        $nameFieldMode = (string) config('emmanuel-saleem-social-auth.user_fields.name_field', 'name');
        $additionalFields = (array) config('emmanuel-saleem-social-auth.user_fields.additional_fields', []);
        $customFieldMap = (array) (config('emmanuel-saleem-social-auth.user_fields.custom_fields') ?? []);

        $data = [];

        // Name handling
        $fullName = (string) ($oauthUser->name ?? '');
        if ($nameFieldMode === 'first_last') {
            $parts = preg_split('/\s+/', trim($fullName), 2) ?: [];
            $data['first_name'] = $parts[0] ?? '';
            $data['last_name'] = $parts[1] ?? '';
        } else {
            $data['name'] = $fullName;
        }

        // Common fields
        $data['email'] = $oauthUser->email ?? null;
        $data['avatar'] = $oauthUser->avatar ?? null;
        $data['email_verified_at'] = now();
        $data['password'] = Hash::make(Str::random(24));

        // Provider-specific fields
        if ($provider === 'google') {
            $data['google_id'] = $oauthUser->id ?? null;
            $data['google_token'] = $oauthUser->token ?? null;
            $data['google_refresh_token'] = $oauthUser->refreshToken ?? null;
        } elseif ($provider === 'microsoft') {
            $data['microsoft_id'] = $oauthUser->id ?? null;
            $data['microsoft_token'] = $oauthUser->token ?? null;
            $data['microsoft_refresh_token'] = $oauthUser->refreshToken ?? null;
        }

        // Merge any additional fixed fields
        $data = array_merge($data, $additionalFields);

        // Apply custom field name mapping (rename keys)
        if (!empty($customFieldMap)) {
            $data = self::remapKeys($data, $customFieldMap);
        }

        return $data;
    }

    /**
     * Rename keys in the array based on a mapping [from => to].
     *
     * @param array $data
     * @param array $map
     * @return array
     */
    protected static function remapKeys(array $data, array $map): array
    {
        foreach ($map as $from => $to) {
            if ($from === $to) {
                continue;
            }
            if (array_key_exists($from, $data)) {
                $data[$to] = $data[$from];
                unset($data[$from]);
            }
        }
        return $data;
    }
}


