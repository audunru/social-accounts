<?php

namespace audunru\SocialAccounts\Controllers;

use Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use audunru\SocialAccounts\Interfaces\Strategy;
use audunru\SocialAccounts\Strategies\FindUser;
use audunru\SocialAccounts\Strategies\AddSocialAccount;
use audunru\SocialAccounts\Strategies\FindOrCreateUser;

class ProviderController extends Controller
{
    /**
     * The provider name.
     *
     * @var string
     */
    private $provider;

    /**
     * The provided user.
     *
     * @var object
     */
    private $providerUser;

    public function __construct(Request $request)
    {
        $this->provider = $request->provider;
    }

    /**
     * Redirect the user to the authentication page.
     *
     * @return RedirectResponse
     */
    public function redirectToProvider(Socialite $socialite): RedirectResponse
    {
        return $socialite::driver($this->provider)->redirect();  // TODO: Can the callback be set here instead of in services?
    }

    /**
     * Handle the returned info from the external partner, and then login or create a new user depending on the circumstances.
     *
     * @return RedirectResponse
     */
    public function handleProviderCallback(Socialite $socialite): RedirectResponse
    {
        $this->providerUser = $socialite::driver($this->provider)->user();

        $user = $this->getUserStrategy()->handle($this->provider, $this->providerUser);

        if ($user) {
            Auth::login($user);
        } else {
            abort(401);
        }

        return redirect()->intended();
    }

    protected function getUserStrategy(): Strategy
    {
        if (Auth::check()) {
            return $this->getAccountStrategy();
        }

        return $this->getLoginStrategy();
    }

    protected function getAccountStrategy(): Strategy
    {
        if (config('social-accounts.models.user')::findBySocialAccount($this->provider, $this->providerUser->getId())) {
            return new FindUser();
        }

        abort_if(Auth::user()->hasProvider($this->provider), 409, "You already have a social login with this provider: {$this->provider}.");
        abort_unless(Auth::user()->can_add_social_account, 403, 'You are not allowed to add social logins.');

        return new AddSocialAccount();
    }

    protected function getLoginStrategy(): Strategy
    {
        if (config('social-accounts.automatically_create_users')) {
            return new FindOrCreateUser();
        }

        return new FindUser();
    }
}
