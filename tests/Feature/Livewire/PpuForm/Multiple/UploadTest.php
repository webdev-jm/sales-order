<?php

namespace Tests\Feature\Livewire\PpuForm\Multiple;

use App\Http\Livewire\PpuForm\Multiple\Upload;
use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\PPUForm;
use App\Models\PPUFormItem;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    private function loggedAccount(): AccountLogin
    {
        $user = User::factory()->create([
            'email' => 'ppu-upload@admin',
            'password' => bcrypt('p4ssw0rd'),
            'status' => 'active',
        ]);
        $user->assignRole('superadmin');

        $account = Account::factory()->create();

        return AccountLogin::factory()->create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'time_out' => null,
        ]);
    }

    private function line(string $rtvNumber): array
    {
        return [
            'row_number' => 1,
            'rtv_number' => $rtvNumber,
            'rtv_date' => now()->format('Y-m-d'),
            'branch_name' => 'Branch A',
            'total_quantity' => 10,
            'total_amount' => 100.0,
            'remarks' => '',
        ];
    }

    private function ppuData(array $lines): array
    {
        return [
            'lines' => $lines,
            'date_submitted' => now()->format('Y-m-d'),
            'pickup_date' => now()->format('Y-m-d'),
        ];
    }

    public function test_duplicate_rtv_number_against_existing_db_record_is_flagged_on_its_own_row(): void
    {
        $accountLogin = $this->loggedAccount();
        $ppuForm = PPUForm::factory()->create();
        PPUFormItem::factory()->create([
            'ppuform_id' => $ppuForm->id,
            'rtv_number' => 'RTV-100',
        ]);

        $lines = [
            $this->line('RTV-100'),
            $this->line('RTV-200'),
        ];

        $component = Livewire::test(Upload::class, ['logged_account' => $accountLogin])
            ->set('ppu_data', $this->ppuData($lines))
            ->call('savePPUForm', 'draft');

        $errData = $component->get('err_data');

        $this->assertArrayHasKey(0, $errData['rows']);
        $this->assertArrayNotHasKey(1, $errData['rows']);
        $this->assertStringContainsString('already exists', $errData['rows'][0]['rtv_number']);
    }

    public function test_intra_batch_duplicate_rtv_numbers_are_flagged_on_both_rows(): void
    {
        $accountLogin = $this->loggedAccount();

        $lines = [
            $this->line('RTV-SAME'),
            $this->line('RTV-SAME'),
        ];

        $component = Livewire::test(Upload::class, ['logged_account' => $accountLogin])
            ->set('ppu_data', $this->ppuData($lines))
            ->call('savePPUForm', 'draft');

        $errData = $component->get('err_data');

        $this->assertArrayHasKey(0, $errData['rows']);
        $this->assertArrayHasKey(1, $errData['rows']);
        $this->assertStringContainsString('duplicated within this upload', $errData['rows'][0]['rtv_number']);
    }

    public function test_recheck_lines_revalidates_after_inline_edit_without_reupload(): void
    {
        $accountLogin = $this->loggedAccount();

        $lines = [
            $this->line('RTV-SAME'),
            $this->line('RTV-SAME'),
        ];

        $component = Livewire::test(Upload::class, ['logged_account' => $accountLogin])
            ->set('ppu_data', $this->ppuData($lines))
            ->call('recheckLines');

        $this->assertNotEmpty($component->get('err_data')['rows'] ?? []);

        $component->set('ppu_data.lines.1.rtv_number', 'RTV-UNIQUE')
            ->call('recheckLines');

        $this->assertEmpty($component->get('err_data'));
    }

    public function test_valid_batch_saves_successfully_and_creates_ppuform_and_items(): void
    {
        $accountLogin = $this->loggedAccount();

        $lines = [
            $this->line('RTV-A'),
            $this->line('RTV-B'),
        ];

        $component = Livewire::test(Upload::class, ['logged_account' => $accountLogin])
            ->set('ppu_data', $this->ppuData($lines))
            ->call('savePPUForm', 'finalized');

        $this->assertNotEmpty($component->get('success_data')['control_number'] ?? null);
        $this->assertDatabaseHas('ppuform_items', ['rtv_number' => 'RTV-A']);
        $this->assertDatabaseHas('ppuform_items', ['rtv_number' => 'RTV-B']);
    }
}
