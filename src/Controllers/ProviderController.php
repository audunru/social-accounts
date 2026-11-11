<?php

namespace audunru\SocialAccounts\Controllers;

use audunru\SocialAccounts\Events\SocialUserLoggedIn;
use audunru\SocialAccounts\Exceptions\SocialAccountBelongsToAnotherUser;
use audunru\SocialAccounts\Facades\SocialAccounts;
use audunru\SocialAccounts\Interfaces\Strategy;
use audunru\SocialAccounts\Strategies\AddSocialAccount;
use audunru\SocialAccounts\Strategies\FindOrCreateUser;
use audunru\SocialAccounts\Strategies\FindUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProviderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(protected Request $request) {}

    /**
     * Redirect the user to the authentication page.
     */
    public function redirectToProvider(Socialite $socialite): RedirectResponse
    {
        $this->configureRedirectForProvider();
        $this->applySettingsToProvider($socialite);

        if ($this->request->has('remember')) {
            $this->request->session()->put('remember', true);
        }

        if ($this->request->has('redirect')) {
            $redirect = $this->request->get('redirect');

            // Only allow relative paths to avoid open redirect vulnerabilities (e.g. //evil.com or http://evil.com)
            if (is_string($redirect)
                && Str::startsWith($redirect, '/')
                && ! Str::startsWith($redirect, '//')
            ) {
                Redirect::setIntendedUrl($redirect);
            }
        }

        return $socialite::driver($this->request->provider)->redirect();
    }

    /**
     * Handle the returned info from the external partner, and then login or create a new user depending on the circumstances.
     */
    public function handleProviderCallback(Socialite $socialite): RedirectResponse
    {
        // The provider returns an error instead of a code when the user denies access
        if ($this->request->has('error')) {
            return redirect()->intended()->enforceSameOrigin(url('/'));
        }

        $this->providerUser = $socialite::driver($this->request->provider)->user();

        abort_if(Gate::has(config('social-accounts.gates.login-with-provider'))
            && Gate::denies('login-with-provider', $this->providerUser), 403);

        $strategy = $this->getUserStrategy();
        $user = $strategy->handle($this->request->provider, $this->providerUser);

        abort_if(is_null($user), Response::HTTP_UNAUTHORIZED);

        $remember = $this->request->session()->pull('remember', false);

        // A new link shows only that this session controls the new provider account, not a login with an account
        // that the user already had, so the user is not logged in again and only SocialAccountAdded is dispatched
        if (! $strategy instanceof AddSocialAccount) {
            Auth::login($user, $remember);

            event(new SocialUserLoggedIn($user, $this->request->provider, $this->providerUser));
        }

        return redirect()->intended()->enforceSameOrigin(url('/'));
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
        $foundUser = config('social-accounts.models.user')::findBySocialAccount($this->request->provider, $this->providerUser->getId());

        if ($foundUser) {
            throw_unless($foundUser->is(Auth::user()), SocialAccountBelongsToAnotherUser::class);

            return new FindUser;
        }

        abort_if(Auth::user()->hasProvider($this->request->provider), 409, "You already have a social login with this provider: {$this->request->provider}.");
        abort_if(Gate::has(config('social-accounts.gates.add-social-account'))
            && Gate::denies('add-social-account', $this->providerUser), 403, 'You are not allowed to add social logins.');

        return new AddSocialAccount;
    }

    /**
     * Determines what to do if the user is not logged in.
     */
    protected function getLoginStrategy(): Strategy
    {
        if (config('social-accounts.automatically_create_users')) {
            return new FindOrCreateUser;
        }

        return new FindUser;
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
            ->filter(function (array $settings) {
                return $settings['provider'] === $this->request->provider;
            })
            ->each(function (array $settings) use ($socialite) {
                extract($settings);

                call_user_func_array([$socialite::driver($provider), $methodName], [$parameters]);
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
