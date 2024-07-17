<?php

namespace audunru\SocialAccounts;

use audunru\SocialAccounts\DTOs\ProviderSettingsDto;
use Illuminate\Support\Facades\Route;

class SocialAccounts
{
    /**
     * Holds (optional) provider settings.
     *
     * @var ProviderSettingsDto[]
     */
    protected static array $providerSettings = [];

    /**
     * Binds the SocialAccounts routes into the controller.
     *
     * @param array<string, string> $options
     */
    public static function routes(?callable $callback = null, array $options = []): void
    {
        $callback = $callback ?: function ($router) {
            $router->all();
        };
        $defaultOptions = [
            'namespace' => '\audunru\SocialAccounts\Controllers',
        ];
        $options = array_merge($defaultOptions, $options);
        Route::group($options, function ($router) use ($callback) {
            $callback(new RouteRegistrar($router));
        });
    }

    /**
     * Register settings for a provider.
     *
     * @param array<string,string|int|bool|null>|null $parameters
     */
    public static function registerProviderSettings(string $provider, string $methodName, ?array $parameters = null): void
    {
        array_push(self::$providerSettings, new ProviderSettingsDto($provider, $methodName, $parameters));
    }

    /**
     * Return settings for all providers.
     *
     * @return ProviderSettingsDto[]
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
