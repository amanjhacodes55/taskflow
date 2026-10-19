<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/projects')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Log in');
    }

    public function test_a_visitor_can_sign_up(): void
    {
        $this->post('/register', [
            'name' => 'Aman',
            'email' => 'aman@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ])->assertRedirect('/projects');

        $this->assertAuthenticated();
    }

    public function test_a_user_can_log_in_and_out(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass']);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret-pass'])->assertRedirect('/projects');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_a_wrong_password_shows_an_error(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass']);

        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
