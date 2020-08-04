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
    public function logged_in_users_can_logout()
    {
        $this->signIn();

        $this->assertAuthenticated();

        $this->post(route('logout'));

        $this->assertGuest();
    }
}