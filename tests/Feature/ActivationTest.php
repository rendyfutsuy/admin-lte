<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use App\Emails\UserActivationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    /** @test */
    public function guests_can_access_valid_account_activation_page()
    {
        $user = factory(User::class)->states('needs_activation')->create();

        $queryString = [
            'e' => base64url_encode($user->email),
        ];

        $this->get(route('account.activation', $queryString))->assertOk();
    }

    /** @test */
    public function activation_code_page_cannot_be_accessed_if_email_not_registered()
    {
        $this->withExceptionHandling();

        $invalidEmail = [
            'e' => base64url_encode('unregistered@email.com'),
        ];

        $response = $this->get(route('account.activation', $invalidEmail));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function user_can_activate_their_account_using_activation_code()
    {
        $user = factory(User::class)->states('needs_activation')->create();

        $queryString = [
            'e' => base64url_encode($user->email),
        ];

        $response = $this->post(
            route('account.activation', $queryString),
            ['act_code' => $user->getActivationCode()]
        );

        $response->assertRedirect(route('account.activation.success'));

        tap($user->fresh(), function ($user) {
            $this->assertTrue($user->isActivated());
            $this->assertNull($user->getActivationCode());
        });
    }

    /** @test */
    public function user_can_activate_their_account_from_link_on_email()
    {
        $user = factory(User::class)->states('needs_activation')->create();

        $response = $this->get(
            route('account.activation.from.links', [
                'act' => base64url_encode($user->getActivationCode()),
                'e' => base64url_encode($user->email)
            ])
        );

        $response->assertRedirect(route('account.activation.success'));

        tap($user->fresh(), function ($user) {
            $this->assertTrue($user->isActivated());
            $this->assertNull($user->getActivationCode());
        });
    }

    /** @test */
    public function user_can_validate_their_activation_code_before_activation()
    {
        $user = factory(User::class)->states('needs_activation')->create();

        $this->postJson(
            route('account.validate.activation.code'),
            [
                'act_code' => $user->getActivationCode(),
                'e' => base64url_encode($user->email),
            ]
        )
        ->assertOk()
        ->assertJson(['success' => true]);
    }

    /** @test */
    public function when_user_validate_their_activation_code_if_the_act_code_is_wrong_it_will_return_errors()
    {
        $user = factory(User::class)->states('needs_activation')->create();

        $this->postJson(
            route('account.validate.activation.code'),
            [
                'act_code' => 99999,
                'e' => base64url_encode($user->email),
            ]
        )
        ->assertStatus(422)
        ->assertJson(['success' => false]);
    }

    /** @test */
    public function when_user_validate_their_activation_code_if_the_act_code_and_email_are_null_it_will_return_errors()
    {
        $this->withExceptionHandling();

        $this->postJson(route('account.validate.activation.code'))
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
    }

    /** @test */
    public function when_user_validate_their_activation_code_if_the_email_is_wrong_it_will_return_errors()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->states('needs_activation')->create();

        $this->postJson(
            route('account.validate.activation.code'),
            [
                'act_code' => $user->getActivationCode(),
                'e' => base64url_encode('wrongemail@gmail.com'),
            ]
        )
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
    }

    /** @test */
    public function when_user_validate_their_activation_code_if_the_email_is_null_it_will_return_errors()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->states('needs_activation')->create();

        $this->postJson(
            route('account.validate.activation.code'),
            [
                'act_code' => $user->getActivationCode(),
            ]
        )
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
    }

    /** @test */
    public function users_are_logged_in_after_activation_success()
    {
        $user = factory(User::class)->states('needs_activation')->create();

        $queryString = [
            'e' => base64url_encode($user->email),
        ];

        $this->post(
            route('account.activation', $queryString),
            ['act_code' => $user->getActivationCode()]
        );

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function last_online_is_recorded_after_successful_activation()
    {
        $user = factory(User::class)->states('needs_activation')->create();

        $queryString = [
            'e' => base64url_encode($user->email),
        ];

        $this->post(
            route('account.activation', $queryString),
            ['act_code' => $user->getActivationCode()]
        );

        $this->assertNotNull($user->fresh()->last_online);
    }

    /** @test */
    public function user_can_resend_their_activation_code()
    {
        $user = factory(User::class)->states('needs_activation')->create();

        $this->get(route('account.resend.activation.code', $user->id))
            ->assertSessionHasNoErrors();

        Mail::assertQueued(UserActivationEmail::class);
    }

    /** @test */
    public function users_cannot_activate_account_using_invalid_activation_code()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->states('needs_activation')->create([
            'activation_code' => '12345',
        ]);

        $queryString = [
            'e' => base64url_encode($user->email),
        ];

        $this->from(route('account.activation', $queryString));

        $response = $this->post(
            route('account.activation', $queryString),
            ['act_code' => '11111']
        );

        $response->assertRedirect(route('account.activation', $queryString));

        $response->assertSessionHasErrors('act_code');

        tap($user->fresh(), function ($user) {
            $this->assertFalse($user->isActivated());
            $this->assertNotNull($user->getActivationCode());
        });
    }

    /** @test */
    public function it_redirects_to_login_page_if_users_email_not_registered()
    {
        $this->withExceptionHandling();

        $queryString = [
            'e' => base64url_encode('unregistered@email.com'),
        ];

        $response = $this->post(
            route('account.activation', $queryString),
            ['act_code' => '1111']
        );

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_redirects_to_home_page_if_users_email_already_activated()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->states('activated')->create();

        $queryString = [
            'e' => base64url_encode($user->email),
        ];

        $response = $this->get(route('account.activation', $queryString));

        $response->assertRedirect('/');
    }

    /** @test */
    public function activation_code_is_required_to_activate_users_account()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->states('needs_activation')->create();

        $queryString = [
            'e' => base64url_encode($user->email),
        ];

        $this->from(route('account.activation', $queryString));

        $response = $this->post(route('account.activation', $queryString), []);

        $response->assertRedirect(route('account.activation', $queryString));

        $response->assertSessionHasErrors('act_code');
    }
}
