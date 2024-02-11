<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_all_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $users = User::factory()->count(3)->create();

        $response = $this->actingAs($admin)
                        ->getJson('/api/users');

        $response->assertStatus(200)
                ->assertJsonCount(4);
    }

    /** @test */
    public function it_can_get_user_details()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
                         ->getJson('/api/users/' . $user->id);

        $response->assertStatus(200)
                 ->assertJson($user->toArray());
    }

    /** @test */
    public function it_can_update_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
                         ->putJson('/api/users/' . $user->id, [
                             'name' => 'Updated Name'
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name']);
    }

    /** @test */
    public function it_can_delete_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(200)
                ->assertJson(['message' => 'User deleted successfully']);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /** @test */
    public function it_can_make_user_an_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->putJson('/api/users/' . $user->id . '/make-admin');

        $response->assertStatus(200);
        $this->assertEquals('admin', $user->fresh()->role);
    }

}
