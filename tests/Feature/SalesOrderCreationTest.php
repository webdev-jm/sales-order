<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\AccountLogin;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;

class SalesOrderCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    private function createSuperadmin(): User
    {
        $user = User::factory()->create([
            'email' => 'admin@admin',
            'password' => bcrypt('p4ssw0rd'),
            'status' => 'active',
        ]);
        $user->assignRole('superadmin');
        return $user;
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get('/home')->assertRedirect('/login');
        $this->get('/sales-order')->assertRedirect('/login');
        $this->get('/sales-order/create')->assertRedirect('/login');
    }

    public function test_login_page_is_accessible(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = $this->createSuperadmin();

        $this->post('/login', [
            'email' => 'admin@admin',
            'password' => 'p4ssw0rd',
        ])->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $this->createSuperadmin();

        $this->post('/login', [
            'email' => 'admin@admin',
            'password' => 'wrongpassword',
        ])->assertSessionHasErrors();

        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_home(): void
    {
        $user = $this->createSuperadmin();

        $this->actingAs($user)->get('/home')->assertStatus(200);
    }

    public function test_sales_order_index_requires_logged_account(): void
    {
        $user = $this->createSuperadmin();

        $this->actingAs($user)
            ->get('/sales-order')
            ->assertRedirect('/home');
    }

    public function test_sales_order_index_accessible_with_logged_account(): void
    {
        $user = $this->createSuperadmin();
        $account = Account::factory()->create();
        $accountLogin = AccountLogin::factory()->create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'time_out' => null,
        ]);

        $this->actingAs($user)
            ->withSession(['logged_account' => $accountLogin])
            ->get('/sales-order')
            ->assertStatus(200);
    }

    public function test_sales_order_create_redirects_without_logged_account(): void
    {
        $user = $this->createSuperadmin();

        $this->actingAs($user)
            ->get('/sales-order/create')
            ->assertRedirect('/home');
    }

    public function test_sales_order_create_accessible_with_logged_account(): void
    {
        $user = $this->createSuperadmin();
        $account = Account::factory()->create();
        $accountLogin = AccountLogin::factory()->create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'time_out' => null,
        ]);

        $this->actingAs($user)
            ->withSession(['logged_account' => $accountLogin])
            ->get('/sales-order/create')
            ->assertStatus(200);
    }

    public function test_full_flow_login_then_account_then_sales_order_create(): void
    {
        $this->withoutExceptionHandling();

        $user = $this->createSuperadmin();
        $account = Account::factory()->create();
        $accountLogin = AccountLogin::factory()->create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'time_out' => null,
        ]);
        $accountLogin->load('account');

        // Step 1: Login
        $this->post('/login', [
            'email' => 'admin@admin',
            'password' => 'p4ssw0rd',
        ])->assertRedirect('/home');

        // Step 2: Access home
        $this->actingAs($user)->get('/home')->assertStatus(200);

        // Step 3: Access sales order index with account session
        $this->actingAs($user)
            ->withSession(['logged_account' => $accountLogin])
            ->get('/sales-order')
            ->assertStatus(200);

        // Step 4: Access sales order create form
        $this->actingAs($user)
            ->withSession(['logged_account' => $accountLogin])
            ->get('/sales-order/create')
            ->assertStatus(200);
    }
}
