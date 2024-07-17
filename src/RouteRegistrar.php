<?php

namespace audunru\SocialAccounts;

use Illuminate\Contracts\Routing\Registrar as Router;

class RouteRegistrar
{
    /**
     * The route prefix.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Create a new route registrar instance.
     */
    public function __construct(protected Router $router)
    {
        $this->router = $router;
        $this->prefix = config('social-accounts.route_prefix');
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
        /** @var string[] $providers */
        $providers = config('social-accounts.providers');
        collect($providers)->each(function ($provider) {
            $this->forProvider($provider);
        });
    }

    /**
     * Register web routes for a provider.
     */
    public function forProvider(string $provider): void
    {
        $this->router->group(['middleware' => ['web']], function ($router) use ($provider) {
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
        $this->router->group(['middleware' => ['api', 'auth:api']], function ($router) {
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
