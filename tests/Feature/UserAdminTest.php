<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_list_and_search_users(): void
    {
        $u1 = User::factory()->create(['name' => 'Alice Martin', 'email' => 'alice@test.com']);
        $u2 = User::factory()->create(['name' => 'Bob Dupont', 'email' => 'bob@test.com']);

        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertOk();
        $response->assertViewHas('users');

        // Search by name
        $searchResponse = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'Alice']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('Alice Martin');
    }

    public function test_admin_can_create_and_store_user(): void
    {
        $this->actingAs($this->admin)->get(route('admin.users.create'))->assertOk();

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name'                  => 'Nouveau Membre',
            'email'                 => 'membre@example.com',
            'password'              => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
            'role'                  => User::ROLE_USER,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'membre@example.com',
            'role'  => User::ROLE_USER,
        ]);
    }

    public function test_admin_can_show_and_edit_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)->get(route('admin.users.show', $user))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.users.edit', $user))->assertOk();
    }

    public function test_admin_can_update_user_without_changing_password(): void
    {
        $user = User::factory()->create(['name' => 'Ancien Nom', 'role' => User::ROLE_USER]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
            'name'  => 'Nom Modifie',
            'email' => $user->email,
            'role'  => User::ROLE_ADMIN,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'name' => 'Nom Modifie',
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_admin_can_update_user_with_new_password(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
            'name'                  => $user->name,
            'email'                 => $user->email,
            'password'              => 'NouveauPass99!',
            'password_confirmation' => 'NouveauPass99!',
            'role'                  => User::ROLE_USER,
        ]);

        $response->assertRedirect(route('admin.users.index'));
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
