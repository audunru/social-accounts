<?php

namespace audunru\SocialAccounts;

use Illuminate\Support\Facades\Route;

class SocialAccounts
{
    /**
     * Binds the SocialAccounts routes into the controller.
     *
     * @param callable|null $callback
     * @param array         $options
     */
    public static function routes($callback = null, array $options = [])
    {
        $callback = $callback ?: function ($router) {
            $router->all();
        };
        $defaultOptions = [
            'prefix' => 'social',
            'namespace' => '\audunru\SocialAccounts\Controllers',
        ];
        $options = array_merge($defaultOptions, $options);
        Route::group($options, function ($router) use ($callback) {
            $callback(new RouteRegistrar($router));
        });
    }
}
