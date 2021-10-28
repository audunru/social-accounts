<?php

namespace audunru\SocialAccounts\Tests\Feature;

use audunru\SocialAccounts\SocialAccounts;
use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class RouteTest extends TestCase
{
    use WithFaker;

    protected $provider = 'google';
    protected $prefix = 'social-accounts';

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        config(["services.{$this->provider}.client_id" => $this->faker->uuid]);
        config(["services.{$this->provider}.client_secret" => $this->faker->uuid]);
    }

    public function testDisabledProviderHasNoRoute()
    {
        $this->get("/{$this->prefix}/login/twitter")
          ->assertStatus(404);
    }

    public function testItRedirectsFromLoginToProvider()
    {
        $response = $this->get("/{$this->prefix}/login/{$this->provider}");

        $response->assertStatus(302);
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/auth', $response->getTargetUrl());
    }

    public function testItAddsOptionsToRedirect()
    {
        SocialAccounts::registerProviderSettings('google', 'with', ['hd' => 'seinfeld.com']);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}");

        $response->assertStatus(302);
        $this->assertStringContainsString('hd=seinfeld.com', $response->getTargetUrl());
    }

    public function testItAddsAScope()
    {
        SocialAccounts::registerProviderSettings('google', 'scopes', ['amazing-scope']);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}");

        $response->assertStatus(302);
        $this->assertStringContainsString('scope=openid+profile+email+amazing-scope', $response->getTargetUrl());
    }

    public function testItOverwritesScopes()
    {
        SocialAccounts::registerProviderSettings('google', 'setScopes', ['just-this-scope']);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}");

        $response->assertStatus(302);
        $this->assertStringContainsString('scope=just-this-scope', $response->getTargetUrl());
    }

    public function testRedirectUrlIsSetAutomatically()
    {
        $response = $this->get("/{$this->prefix}/login/{$this->provider}");

        $response->assertStatus(302);
        $this->assertTrue(false !== strpos($response->getTargetUrl(), urlencode("/login/{$this->provider}/callback")));
    }

    public function testRedirectUrlCanBeSetManually()
    {
        config(["services.{$this->provider}.redirect" => '/forbidden-city-redirect']);

        $response = $this->get("/$this->prefix/login/{$this->provider}");

        $response->assertStatus(302);
        $this->assertStringContainsString('forbidden-city-redirect', $response->getTargetUrl());
    }
}
