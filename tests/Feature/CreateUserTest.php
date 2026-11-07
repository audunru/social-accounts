<?php

namespace Tests\Feature;

use audunru\SocialAccounts\Events\SocialUserCreated;
use audunru\SocialAccounts\Events\SocialUserLoggedIn;
use audunru\SocialAccounts\Exceptions\EmailAlreadyRegistered;
use audunru\SocialAccounts\Tests\Models\SoftDeletingUser;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

class CreateUserTest extends TestCase
{
    use WithFaker;

    protected $provider = 'google';

    protected $prefix = 'social-accounts';

    public function test_it_fails_to_create_a_user_when_email_is_already_registered()
    {
        Event::fake();

        $this->enableUserCreation();

        User::factory()->create(['email' => 'user@example.com']);

        $this->mockSocialite('user@example.com', 'Another User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(409);
        $this->assertInstanceOf(EmailAlreadyRegistered::class, $response->exception);
        $this->assertFalse(Auth::check());
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_accounts', 0);
        Event::assertNotDispatched(SocialUserCreated::class);
        Event::assertNotDispatched(Registered::class);
        Event::assertNotDispatched(SocialUserLoggedIn::class);
    }

    public function test_it_fails_to_create_a_user_when_email_is_already_registered_with_different_case()
    {
        $this->enableUserCreation();

        User::factory()->create(['email' => 'user@example.com']);

        $this->mockSocialite('User@Example.com', 'Another User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(409);
        $this->assertInstanceOf(EmailAlreadyRegistered::class, $response->exception);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_it_fails_to_create_a_user_when_email_belongs_to_a_soft_deleted_user()
    {
        $this->enableUserCreation();

        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
        config(['social-accounts.models.user' => SoftDeletingUser::class]);

        SoftDeletingUser::create(['name' => 'Deleted User', 'email' => 'user@example.com'])->delete();

        $this->mockSocialite('user@example.com', 'Another User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(409);
        $this->assertInstanceOf(EmailAlreadyRegistered::class, $response->exception);
        $this->assertNull($response->exception->getPrevious());
        $this->assertDatabaseCount('users', 1);
    }

    public function test_it_fails_to_create_a_user_when_email_is_registered_after_the_check()
    {
        Event::fake([SocialUserCreated::class, Registered::class, SocialUserLoggedIn::class]);

        $this->enableUserCreation();

        User::creating(function () {
            DB::table('users')->insert(['name' => 'Another User', 'email' => 'user@example.com']);
        });

        $this->mockSocialite('user@example.com', 'User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(409);
        $this->assertInstanceOf(EmailAlreadyRegistered::class, $response->exception);
        $this->assertInstanceOf(UniqueConstraintViolationException::class, $response->exception->getPrevious());
        $this->assertFalse(Auth::check());
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_accounts', 0);
        Event::assertNotDispatched(SocialUserCreated::class);
        Event::assertNotDispatched(Registered::class);
        Event::assertNotDispatched(SocialUserLoggedIn::class);
    }

    public function test_it_creates_a_user_without_email_when_another_user_has_no_email()
    {
        $this->enableUserCreation();

        User::factory()->create(['email' => null]);

        $this->mockSocialite(null, 'User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertTrue(Auth::check());
        $this->assertDatabaseCount('users', 2);
    }
}
