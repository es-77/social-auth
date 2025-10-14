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
        $fieldMapping = config("emmanuel-saleem-social-auth.user_fields.field_mapping.{$provider}", []);
        $defaults = config('emmanuel-saleem-social-auth.user_fields.defaults', []);
        $nameHandling = config('emmanuel-saleem-social-auth.user_fields.name_handling', []);
        $transformations = config('emmanuel-saleem-social-auth.user_fields.transformations', []);

        $data = [];

        // Map OAuth fields to user table fields
        foreach ($fieldMapping as $oauthField => $userField) {
            $value = self::getOAuthValue($oauthUser, $oauthField);
            
            // Handle special cases
            if ($oauthField === 'name') {
                $value = self::handleNameField($value, $nameHandling, $data);
            }
            
            if ($value !== null) {
                $data[$userField] = $value;
            }
        }

        // Apply defaults for any missing required fields
        foreach ($defaults as $field => $defaultValue) {
            if (!array_key_exists($field, $data)) {
                $data[$field] = self::processDefaultValue($defaultValue);
            }
        }

        // Apply custom transformations
        foreach ($transformations as $field => $transformation) {
            if (is_callable($transformation) && isset($data[$field])) {
                $data[$field] = $transformation($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Get value from OAuth user object
     */
    protected static function getOAuthValue($oauthUser, string $field)
    {
        switch ($field) {
            case 'name':
                return $oauthUser->name ?? null;
            case 'email':
                return $oauthUser->email ?? null;
            case 'avatar':
                return $oauthUser->avatar ?? null;
            case 'id':
                return $oauthUser->id ?? null;
            case 'token':
                return $oauthUser->token ?? null;
            case 'refresh_token':
                return $oauthUser->refreshToken ?? null;
            default:
                return $oauthUser->{$field} ?? null;
        }
    }

    /**
     * Handle name field based on configuration
     */
    protected static function handleNameField($name, array $nameHandling, array &$data): string
    {
        $mode = $nameHandling['mode'] ?? 'single';
        
        if ($mode === 'split') {
            $splitFields = $nameHandling['split_fields'] ?? [];
            $parts = preg_split('/\s+/', trim($name), 2) ?: [];
            
            $firstField = $splitFields['first_name'] ?? 'first_name';
            $lastField = $splitFields['last_name'] ?? 'last_name';
            
            $data[$firstField] = $parts[0] ?? '';
            $data[$lastField] = $parts[1] ?? '';
            
            return $parts[0] ?? ''; // Return first part as the main name
        }
        
        return $name;
    }

    /**
     * Process default values (handle special cases)
     */
    protected static function processDefaultValue($value)
    {
        if ($value === 'auto_generated') {
            return Hash::make(Str::random(24));
        }
        
        if ($value === 'now') {
            return now();
        }
        
        return $value;
    }
}


