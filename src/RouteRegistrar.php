<?php

namespace audunru\SocialAccounts;

use Illuminate\Contracts\Routing\Registrar as Router;

class RouteRegistrar
{
    /**
     * The router implementation.
     *
     * @var \Illuminate\Contracts\Routing\Registrar
     */
    protected $router;

    /**
     * Create a new route registrar instance.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Register routes for logging in and retrieving and removing social accounts.
     */
    public function all()
    {
        $this->forWeb();
        $this->forApi();
    }

    /**
     * Register web routes for all enabled providers.
     */
    public function forWeb()
    {
        collect(config('social-accounts.providers'))->each(function ($provider) {
            $this->forProvider($provider);
        });
    }

    /**
     * Register web routes for a provider.
     *
     * @param string $provider
     */
    public function forProvider(string $provider)
    {
        $this->router->group(['middleware' => ['web']], function ($router) use ($provider) {
            $router
                ->get("login/{$provider}", [
                    'uses'=> 'ProviderController@redirectToProvider',
                    'as' => "social-accounts.login.{$provider}",
                ])
                ->defaults('provider', $provider);
            $router
                ->get("login/{$provider}/callback", 'ProviderController@handleProviderCallback')
                ->defaults('provider', $provider);
        });
    }

    /**
     * Register API routes.
     */
    public function forApi()
    {
        $this->router->group(['middleware' => ['api']], function ($router) {
            $router->apiResource('social-accounts', 'ApiController')->only([
                'index', 'show', 'destroy',
            ]);
        });
    }
}
