<?php

namespace audunru\SocialAccounts;

class SocialAccounts
{
    /**
     * Holds (optional) provider settings.
     *
     * @var array
     */
    protected static $providerSettings = [];

    /**
     * Register settings for a provider.
     */
    public static function registerProviderSettings(string $provider, string $methodName, ?array $parameters = null): void
    {
        array_push(self::$providerSettings, compact('provider', 'methodName', 'parameters'));
    }

    /**
     * Return settings for all providers.
     */
    public static function getProviderSettings(): array
    {
        return self::$providerSettings;
    }

    /**
     * Empty provider settings.
     */
    public static function emptyProviderSettings(): void
    {
        self::$providerSettings = [];
    }
}
