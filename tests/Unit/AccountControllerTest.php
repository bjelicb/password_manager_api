<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_all_accounts()
    {
        $user = User::factory()->create();
        Account::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/api/accounts');

        $response->assertStatus(200);
        $accounts = $response->json();
        $this->assertCount(3, $accounts);
    }

    /** @test */
    public function it_can_get_account_details()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/api/accounts/' . $account->id);

        $response->assertStatus(200);
        // Decrypt password
        $account->password = decrypt($account->password);
        $response->assertJson($account->toArray());
    }

    /** @test */
    public function it_can_add_an_account()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/accounts', [
            'name' => 'Test Account',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('accounts', ['name' => 'Test Account']);
    }

    /** @test */
    public function it_can_update_an_account()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $account = Account::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/accounts/' . $account->id, [
            'name' => 'Updated Account Name'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'name' => 'Updated Account Name']);
    }

    /** @test */
    public function it_can_reset_an_account_password_by_admin()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $account = Account::factory()->create();

        $newPassword = 'newpassword';
        $response = $this->actingAs($user)->putJson('/api/account/reset-password/' . $account->id, [
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertStatus(200);
        // Proveravamo dekriptovanu lozinku
        $this->assertEquals($newPassword, decrypt($account->fresh()->password));
    }

    /** @test */
    public function it_can_reset_an_account_password_by_owner()
    {
        $owner = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $owner->id]);
        $newPassword = 'newpassword';

        $response = $this->actingAs($owner)->putJson('/api/account/reset-password/' . $account->id, [
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertStatus(200);
        // check decrypt password
        $this->assertEquals($newPassword, decrypt($account->fresh()->password));
    }

    /** @test */
    public function it_cannot_reset_an_account_password_by_unauthorized_user()
    {
        $response = $this->putJson('/api/account/reset-password/1', [
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $response->assertStatus(403);
    }
}
