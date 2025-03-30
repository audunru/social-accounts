<?php

use audunru\SocialAccounts\Controllers\ProviderController;
use Illuminate\Support\Facades\Route;

if (! config('social-accounts.web_routes_enabled')) {
    return;
}

$providers = config('social-accounts.providers');
$prefix = config('social-accounts.route_prefix', 'social-accounts');
$middleware = config('social-accounts.web_middleware', ['web']);

foreach ($providers as $provider) {
    Route::middleware($middleware)->prefix($prefix)->group(function () use ($provider) {
        Route::get("login/{$provider}", [ProviderController::class, 'redirectToProvider'])
          ->name("social-accounts.login.{$provider}")
          ->defaults('provider', $provider);
        Route::get("login/{$provider}/callback", [ProviderController::class, 'handleProviderCallback'])
          ->name("social-accounts.callback.{$provider}")
          ->defaults('provider', $provider);
    });
}
