<?php

namespace audunru\SocialAccounts\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void  routes(callable|null $callback = null, array $options = [])
 * @method static void  registerProviderSettings(string $provider, string $methodName, ?array $parameters = null)
 * @method static array getProviderSettings()
 * @method static void  emptyProviderSettings()
 */
class SocialAccounts extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'social-accounts';
    }
}
