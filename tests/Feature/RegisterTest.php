<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_register_user_from_zero()
    {
        $response = $this->post(route('register'), [
            'email' => 'aditia@holahalo.com',
            'username' => 'aditia',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'aditia@holahalo.com',
            'username' => 'aditia',
        ]);
    }

    /** @test */
    public function can_register_user_from_guest_user()
    {
        $user = factory(User::class)->create([
            'email' => null,
            'username' => 'guest123',
            'level' => User::GUEST,
        ]);

        $response = $this->post(route('register'), [
            'user_id' => $user->id,
            'email' => 'aditia@holahalo.com',
            'username' => 'aditia',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'aditia@holahalo.com',
            'username' => 'aditia',
        ]);
    }

    /** @test */
    public function can_register_user_from_sign_in_guest_user()
    {
        $user = factory(User::class)->create([
            'email' => null,
            'username' => 'guest123',
            'level' => User::GUEST,
        ]);

        $response = $this->post(route('register'), [
            'user_id' => $user->id,
            'email' => 'aditia@holahalo.com',
            'username' => 'aditia',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'aditia@holahalo.com',
            'username' => 'aditia',
        ]);
    }
}