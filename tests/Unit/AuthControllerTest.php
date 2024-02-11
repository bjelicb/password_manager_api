<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function test_register_creates_a_new_user_and_returns_201()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Successful registration']);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /** @test */
    public function test_login_returns_a_token_for_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'token']);
    }

    /** @test */
    public function test_logout_destroys_token()
    {
        $user = User::factory()->create();

        $token = $user->createToken('authToken')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Successfully logged out']);
    }

    /** @test */
    public function test_change_password_changes_user_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $response = $this->actingAs($user)->postJson('/api/change-password', [
            'current_password' => 'oldpassword',
            'password' => $newPassword = 'newpassword',
            'password_confirmation' => $newPassword,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Password changed successfully']);
        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }
}
