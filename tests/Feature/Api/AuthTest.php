<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_gets_a_token(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Aman',
            'email' => 'aman@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ])
            ->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token'])
            ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('users', ['email' => 'aman@example.com']);
    }

    public function test_registration_is_validated(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/v1/auth/register', ['email' => 'taken@example.com', 'password' => 'short'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_login_returns_a_token_that_works(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass']);

        $token = $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'secret-pass'])
            ->assertOk()
            ->json('token');

        $this->withToken($token)->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_login_with_a_wrong_password_fails(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass']);

        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_protected_routes_need_a_token(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->getJson('/api/v1/projects')->assertUnauthorized();
    }

    public function test_the_login_route_is_rate_limited(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'wrong']);
        }

        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertTooManyRequests();
    }
}
