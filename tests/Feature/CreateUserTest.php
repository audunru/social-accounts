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
        // RefreshDatabase opens a transaction, so the rollback to the savepoint also removes the row of the listener
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('social_accounts', 0);
        Event::assertNotDispatched(SocialUserCreated::class);
        Event::assertNotDispatched(Registered::class);
        Event::assertNotDispatched(SocialUserLoggedIn::class);
    }

    public function test_it_throws_the_original_exception_when_a_user_without_email_violates_another_unique_index()
    {
        $this->enableUserCreation();

        $existingUser = User::factory()->create();

        User::creating(function (User $user) use ($existingUser) {
            $user->id = $existingUser->id;
        });

        $this->mockSocialite(null, 'User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(500);
        $this->assertInstanceOf(UniqueConstraintViolationException::class, $response->exception);
        $this->assertDatabaseCount('users', 1);
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

    public function test_it_saves_the_email_in_lower_case()
    {
        $this->enableUserCreation();

        $this->mockSocialite('User@Example.com', 'User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['email' => 'user@example.com']);
    }

    public function test_it_saves_non_ascii_letters_in_lower_case()
    {
        $this->enableUserCreation();

        $this->mockSocialite('ÆØÅ@Example.com', 'User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['email' => 'æøå@example.com']);
    }

    public function test_it_keeps_the_email_case_when_lowercase_emails_is_false()
    {
        $this->enableUserCreation();
        config(['social-accounts.lowercase_emails' => false]);

        $this->mockSocialite('User@Example.com', 'User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['email' => 'User@Example.com']);
    }

    public function test_it_saves_the_email_in_lower_case_when_published_config_has_no_setting()
    {
        $this->enableUserCreation();
        config(['social-accounts' => array_diff_key(config('social-accounts'), ['lowercase_emails' => true])]);

        $this->mockSocialite('User@Example.com', 'User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['email' => 'user@example.com']);
    }

    public function test_it_creates_a_user_without_email_when_lowercase_emails_is_true()
    {
        $this->enableUserCreation();

        $this->mockSocialite(null, 'User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', ['email' => null]);
    }

    public function test_the_event_gets_the_original_provider_email()
    {
        Event::fake([SocialUserCreated::class]);

        $this->enableUserCreation();

        $this->mockSocialite('User@Example.com', 'User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        Event::assertDispatched(SocialUserCreated::class, fn (SocialUserCreated $event) => $event->providerUser->getEmail() === 'User@Example.com');
    }
}
