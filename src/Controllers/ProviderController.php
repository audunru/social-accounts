<?php

namespace audunru\SocialAccounts\Controllers;

use audunru\SocialAccounts\DTOs\ProviderSettingsDto;
use audunru\SocialAccounts\Facades\SocialAccounts;
use audunru\SocialAccounts\Interfaces\Strategy;
use audunru\SocialAccounts\Strategies\AddSocialAccount;
use audunru\SocialAccounts\Strategies\FindOrCreateUser;
use audunru\SocialAccounts\Strategies\FindUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProviderController extends Controller
{
    protected ?ProviderUser $providerUser = null;

    public function __construct(protected Request $request)
    {
    }

    /**
     * Redirect the user to the authentication page.
     */
    public function redirectToProvider(Socialite $socialite): \Symfony\Component\HttpFoundation\RedirectResponse|RedirectResponse
    {
        $this->configureRedirectForProvider();
        $this->applySettingsToProvider($socialite);

        if ($this->request->has('remember')) {
            $this->request->session()->put('remember', true);
        }

        return $socialite::driver($this->request->provider)->redirect();
    }

    /**
     * Handle the returned info from the external partner, and then login or create a new user depending on the circumstances.
     */
    public function handleProviderCallback(Socialite $socialite): RedirectResponse
    {
        $this->providerUser = $socialite::driver($this->request->provider)->user();

        abort_if(Gate::has(config('social-accounts.gates.login-with-provider'))
            && Gate::denies('login-with-provider', $this->providerUser), Response::HTTP_FORBIDDEN);

        $user = $this->getUserStrategy()->handle($this->request->provider, $this->providerUser);

        abort_if(is_null($user), Response::HTTP_UNAUTHORIZED);

        $remember = $this->request->session()->pull('remember', false);
        Auth::login($user, $remember);

        return Redirect::intended();
    }

    /**
     * Decide what to do if the user is logged in or not.
     */
    protected function getUserStrategy(): Strategy
    {
        if (Auth::check()) {
            return $this->getAccountStrategy();
        }

        return $this->getLoginStrategy();
    }

    /**
     * Determines what to do if the user is logged in.
     */
    protected function getAccountStrategy(): Strategy
    {
        if (config('social-accounts.models.user')::findBySocialAccount($this->request->provider, $this->providerUser->getId())) {
            return new FindUser();
        }

        $user = Auth::user();

        abort_if($user->hasProvider($this->request->provider), Response::HTTP_CONFLICT, "You already have a social login with this provider: {$this->request->provider}.");
        abort_if(Gate::has(config('social-accounts.gates.add-social-account'))
            && Gate::denies('add-social-account', $this->providerUser), Response::HTTP_FORBIDDEN, 'You are not allowed to add social logins.');

        return new AddSocialAccount();
    }

    /**
     * Determines what to do if the user is not logged in.
     */
    protected function getLoginStrategy(): Strategy
    {
        if (config('social-accounts.automatically_create_users')) {
            return new FindOrCreateUser();
        }

        return new FindUser();
    }

    /**
     * Get the configured settings for the current provider and apply them to the Socalite driver.
     *
     * @SuppressWarnings("unused")
     * @SuppressWarnings("undefined")
     */
    private function applySettingsToProvider(Socialite $socialite): void
    {
        collect(SocialAccounts::getProviderSettings())
            ->filter(function (ProviderSettingsDto $settings) {
                return $settings->provider === $this->request->provider;
            })
            ->each(function (ProviderSettingsDto $settings) use ($socialite) {
                call_user_func_array([$socialite::driver($settings->provider), $settings->methodName], [$settings->parameters]);
            });
    }

    /**
     * Automatically configure a redirect URL for the current provider.
     */
    private function configureRedirectForProvider(): void
    {
        $key = "services.{$this->request->provider}.redirect";
        if (! Config::has($key)) {
            Config::set($key, route("social-accounts.callback.{$this->request->provider}"));
        }
    }
}
