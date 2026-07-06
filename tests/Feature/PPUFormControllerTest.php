<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\PPUForm;
use App\Models\PPUFormItem;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PPUFormControllerTest extends TestCase
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
            'email' => 'ppu-admin@admin',
            'password' => bcrypt('p4ssw0rd'),
            'status' => 'active',
        ]);
        $user->assignRole('superadmin');
        return $user;
    }

    private function loggedAccount(User $user): AccountLogin
    {
        $account = Account::factory()->create();

        return AccountLogin::factory()->create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'time_out' => null,
        ]);
    }

    private function item(string $rtvNumber): array
    {
        return [
            'rs' => $rtvNumber,
            'rtv' => now()->format('Y-m-d'),
            'name' => 'Branch A',
            'qty' => 10,
            'amount' => 100,
            'remarks' => '',
        ];
    }

    private function formFields(string $controlNumber): array
    {
        return [
            'control_number' => $controlNumber,
            'status' => 'draft',
            'date_prepared' => now()->format('Y-m-d'),
            'date_submitted' => now()->format('Y-m-d'),
            'pickup_date' => now()->format('Y-m-d'),
        ];
    }

    public function test_store_flags_duplicate_rtv_numbers_within_batch_on_their_own_rows(): void
    {
        $user = $this->createSuperadmin();
        $accountLogin = $this->loggedAccount($user);

        $ppuItem = [
            'items' => [
                $this->item('RTV-SAME'),
                $this->item('RTV-SAME'),
            ],
            'total_qty' => 20,
            'total_amount' => 200,
        ];

        $response = $this->actingAs($user)
            ->withSession(['logged_account' => $accountLogin, 'ppu_item' => $ppuItem])
            ->post(route('ppu.store'), $this->formFields('PPU-TEST-1'));

        $response->assertSessionHasErrors(['items.0', 'items.1']);
        $this->assertDatabaseMissing('ppuforms', ['control_number' => 'PPU-TEST-1']);
    }

    public function test_store_flags_rtv_number_that_already_exists_in_another_ppu(): void
    {
        $user = $this->createSuperadmin();
        $accountLogin = $this->loggedAccount($user);

        $existingPpuForm = PPUForm::factory()->create();
        PPUFormItem::factory()->create([
            'ppuform_id' => $existingPpuForm->id,
            'rtv_number' => 'RTV-100',
        ]);

        $ppuItem = [
            'items' => [
                $this->item('RTV-100'),
                $this->item('RTV-200'),
            ],
            'total_qty' => 20,
            'total_amount' => 200,
        ];

        $response = $this->actingAs($user)
            ->withSession(['logged_account' => $accountLogin, 'ppu_item' => $ppuItem])
            ->post(route('ppu.store'), $this->formFields('PPU-TEST-2'));

        $response->assertSessionHasErrors(['items.0']);
        $response->assertSessionDoesntHaveErrors(['items.1']);
    }

    public function test_update_excludes_the_forms_own_existing_items_from_duplicate_check(): void
    {
        $user = $this->createSuperadmin();
        $accountLogin = $this->loggedAccount($user);

        $ppuForm = PPUForm::factory()->create([
            'account_login_id' => $accountLogin->id,
            'status' => 'draft',
        ]);
        PPUFormItem::factory()->create([
            'ppuform_id' => $ppuForm->id,
            'rtv_number' => 'RTV-100',
        ]);

        $ppuItem = [
            'items' => [
                $this->item('RTV-100'),
            ],
            'total_qty' => 10,
            'total_amount' => 100,
        ];

        $response = $this->actingAs($user)
            ->withSession(['logged_account' => $accountLogin, 'ppu_item' => $ppuItem])
            ->post(route('ppu.update', $ppuForm->id), $this->formFields($ppuForm->control_number));

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(route('ppu.index'));
    }

    public function test_update_flags_duplicate_rtv_numbers_within_batch_on_their_own_rows(): void
    {
        $user = $this->createSuperadmin();
        $accountLogin = $this->loggedAccount($user);

        $ppuForm = PPUForm::factory()->create([
            'account_login_id' => $accountLogin->id,
            'status' => 'draft',
        ]);
        PPUFormItem::factory()->create([
            'ppuform_id' => $ppuForm->id,
            'rtv_number' => 'RTV-OLD',
        ]);

        $ppuItem = [
            'items' => [
                $this->item('RTV-SAME'),
                $this->item('RTV-SAME'),
            ],
            'total_qty' => 20,
            'total_amount' => 200,
        ];

        $response = $this->actingAs($user)
            ->withSession(['logged_account' => $accountLogin, 'ppu_item' => $ppuItem])
            ->post(route('ppu.update', $ppuForm->id), $this->formFields($ppuForm->control_number));

        $response->assertSessionHasErrors(['items.0', 'items.1']);
    }
}
