<?php

namespace audunru\SocialAccounts;

use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Routing\Router;

class RouteRegistrar
{
    /**
     * The enabled providers.
     */
    private array $providers;

    /**
     * The route prefix.
     */
    private string $prefix;

    /**
     * Web middleware.
     */
    private array $webMiddleware;

    /**
     * API middleware.
     */
    private array $apiMiddleware;

    /**
     * Create a new route registrar instance.
     */
    public function __construct(protected Registrar $router)
    {
        $this->router = $router;
        $this->providers = config('social-accounts.providers');
        $this->prefix = config('social-accounts.route_prefix', 'social-accounts');
        $this->webMiddleware = config('social-accounts.web_middleware', ['web']);
        $this->apiMiddleware = config('social-accounts.api_middleware', ['api']);
    }

    /**
     * Register routes for logging in and retrieving and removing social accounts.
     */
    public function all(): void
    {
        $this->forWeb();
        $this->forApi();
    }

    /**
     * Register web routes for all enabled providers.
     */
    public function forWeb(): void
    {
        collect($this->providers)->each(function (string $provider) {
            $this->forProvider($provider);
        });
    }

    /**
     * Register web routes for a provider.
     */
    public function forProvider(string $provider): void
    {
        $this->router->group(['middleware' => $this->webMiddleware], function (Router $router) use ($provider) {
            $router
                ->prefix($this->prefix)
                ->get("login/{$provider}", [
                    'uses' => 'ProviderController@redirectToProvider',
                    'as'   => "social-accounts.login.{$provider}",
                ])
                ->defaults('provider', $provider);
            $router
                ->prefix($this->prefix)
                ->get("login/{$provider}/callback", [
                    'uses' => 'ProviderController@handleProviderCallback',
                    'as'   => "social-accounts.callback.{$provider}",
                ])
                ->defaults('provider', $provider);
        });
    }

    /**
     * Register API routes.
     */
    public function forApi(): void
    {
        $this->router->group(['middleware' => $this->apiMiddleware], function (Router $router) {
            $router->apiResource($this->prefix, 'ApiController')
                ->only([
                    'index', 'show', 'destroy',
                ])
                ->parameters([
                    $this->prefix => 'social_account',
                ]);
        });
    }
}
