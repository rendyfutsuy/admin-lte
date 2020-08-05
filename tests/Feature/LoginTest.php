<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registered_users_can_login_into_admin()
    {
        $user = factory(User::class)->create([
            'email' => 'aditia@holahalo.com',
            'password' => bcrypt('secret'),
            'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->post(route('login'), [
            'login' => 'aditia@holahalo.com',
            'password' => 'secret',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function registered_users_can_login_into_admin_with_user_name()
    {
        $user = factory(User::class)->create([
            'username' => 'aditsaja',
            'email' => 'aditia@holahalo.com',
            'password' => bcrypt('secret'),
            'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->post(route('login'), [
            'login' => 'aditsaja',
            'password' => 'secret',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function registered_users_can_login_into_admin_with_ajax()
    {
        $user = factory(User::class)->create([
            'email' => 'aditia@holahalo.com',
            'password' => bcrypt('secret'),
            'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->postJson(route('login'), [
            'login' => 'aditia@holahalo.com',
            'password' => 'secret',
        ])->assertStatus(204);

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function registered_users_can_login_into_admin_with_user_name_with_ajax()
    {
        $user = factory(User::class)->create([
            'username' => 'aditsaja',
            'email' => 'aditia@holahalo.com',
            'password' => bcrypt('secret'),
            'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->postJson(route('login'), [
            'login' => 'aditsaja',
            'password' => 'secret',
        ])->assertStatus(204);

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function logged_in_users_can_logout()
    {
        $this->signIn();

        $this->assertAuthenticated();

        $this->post(route('logout'));

        $this->assertGuest();
    }

    /** @test */
    public function ajax_un_verified_user_get_unauthorized_status()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create([
            'email' => 'aditia@holahalo.com',
            'password' => bcrypt('secret'),
            'email_verified_at' => null,
        ]);

        $this->postJson(route('login'), [
            'login' => 'aditia@holahalo.com',
            'password' => 'secret',
        ])->assertUnauthorized();

        $this->assertGuest();
    }

    /** @test */
    public function ajax_banned_user_get_unauthorized_status()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create([
            'email' => 'aditia@holahalo.com',
            'password' => bcrypt('secret'),
            'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'banned_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $this->postJson(route('login'), [
            'login' => 'aditia@holahalo.com',
            'password' => 'secret',
        ])->assertUnauthorized();

        $this->assertGuest();
    }

    /** @test */
    public function un_verified_user_get_redirect_to_activation_page()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create([
            'email' => 'aditia@holahalo.com',
            'password' => bcrypt('secret'),
            'email_verified_at' => null,
        ]);

        $this->post(route('login'), [
            'login' => 'aditia@holahalo.com',
            'password' => 'secret',
        ])->assertRedirect(route('account.activation', [
            'e' => base64url_encode($user->email)
        ]));

        $this->assertGuest();
    }

    /** @test */
    public function banned_user_get_redirect_to_login()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create([
            'email' => 'aditia@holahalo.com',
            'password' => bcrypt('secret'),
            'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'banned_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $this->post(route('login'), [
            'login' => 'aditia@holahalo.com',
            'password' => 'secret',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /** @test */
    public function un_verified_user_can_not_log_in()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create([
            'email' => 'aditia@holahalo.com',
            'password' => bcrypt('secret'),
            'email_verified_at' => null,
        ]);

        $this->postJson(route('login'), [
            'login' => 'aditia@holahalo.com',
            'password' => 'secret',
        ]);

        $this->assertGuest();
    }

    /** @test */
    public function banned_user_can_not_log_in()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create([
            'email' => 'aditia@holahalo.com',
            'password' => bcrypt('secret'),
            'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'banned_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $this->postJson(route('login'), [
            'login' => 'aditia@holahalo.com',
            'password' => 'secret',
        ]);

        $this->assertGuest();
    }
}