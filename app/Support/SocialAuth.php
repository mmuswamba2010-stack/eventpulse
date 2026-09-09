<?php

namespace App\Support;

class SocialAuth
{
    /** @return list<string> */
    public static function providers(): array
    {
        return ['google', 'facebook'];
    }

    public static function isConfigured(string $provider): bool
    {
        if (! in_array($provider, self::providers(), true)) {
            return false;
        }

        $config = config("services.{$provider}");

        return filled($config['client_id'] ?? null)
            && filled($config['client_secret'] ?? null);
    }

    /** @return list<string> */
    public static function configuredProviders(): array
    {
        return array_values(array_filter(
            self::providers(),
            fn (string $provider) => self::isConfigured($provider)
        ));
    }

    public static function hasAnyProvider(): bool
    {
        return self::configuredProviders() !== [];
    }

    /** @return list<string> */
    public static function allowedContexts(): array
    {
        return [
            'login',
            'participant-register',
            'organizer-register',
        ];
    }

    public static function providerColumn(string $provider): string
    {
        return match ($provider) {
            'google' => 'google_id',
            'facebook' => 'facebook_id',
            default => throw new \InvalidArgumentException("Unsupported provider [{$provider}]."),
        };
    }

    public static function providerLabel(string $provider): string
    {
        return match ($provider) {
            'google' => __('Continue with Google'),
            'facebook' => __('Continue with Facebook'),
            default => $provider,
        };
    }
}
