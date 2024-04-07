<?php

namespace audunru\SocialAccounts;

use Illuminate\Support\Facades\Route;

class SocialAccounts
{
    /**
     * Holds (optional) provider settings.
     *
     * @var array
     */
    protected static $providerSettings = [];

    /**
     * Binds the SocialAccounts routes into the controller.
     *
     * @param callable|null $callback
     */
    public static function routes($callback = null, array $options = [])
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
