<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_a_bearer_token_and_user_endpoint_requires_it(): void
    {
        $user = User::factory()->create([
            'password' => 'secret-password',
        ]);

        $this->getJson('/api/user')->assertUnauthorized();
        $this->get('/api/categories')->assertUnauthorized();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret-password',
            'device_name' => 'phpunit',
        ])->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.id', $user->id);

        $token = $response->json('access_token');

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->postJson('/api/login', [
            'email' => 'missing@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_authenticated_user_can_manage_categories(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('phpunit')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->postJson('/api/categories', [
            'name' => 'Action Movies',
            'description' => 'Action category',
        ], $headers)
            ->assertCreated()
            ->assertJsonPath('data.slug', 'action-movies');

        $category = Category::firstOrFail();

        $this->getJson('/api/categories', $headers)
            ->assertOk()
            ->assertJsonPath('data.0.id', $category->id);

        $this->putJson('/api/categories/'.$category->id, [
            'name' => 'Adventure Movies',
        ], $headers)
            ->assertOk()
            ->assertJsonPath('data.slug', 'adventure-movies');

        $this->deleteJson('/api/categories/'.$category->id, [], $headers)
            ->assertNoContent();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_logout_revokes_the_current_bearer_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('phpunit')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        Auth::forgetGuards();

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertUnauthorized();
    }
}
