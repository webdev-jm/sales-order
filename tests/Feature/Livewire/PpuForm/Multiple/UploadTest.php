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

    /**
     * A spreadsheet row as PhpSpreadsheet hands it over: RTV number, submitted
     * date, pick-up date, RTV date, branch, quantity, amount, remarks.
     *
     * @param  mixed  $submitted
     * @param  mixed  $pickup
     * @param  mixed  $rtvDate
     */
    private function sheetRow(string $rtvNumber, $submitted, $pickup, $rtvDate): array
    {
        return [$rtvNumber, $submitted, $pickup, $rtvDate, 'Branch A', 10, 100.5, 'note'];
    }

    private function headerRow(): array
    {
        return ['RTV/RS No.', 'Date Submitted', 'Pick-up Date', 'RTV Date', 'Branch', 'Quantity', 'Amount', 'Remarks'];
    }

    /**
     * Excel hands date cells over as serial numbers - as a float whenever a
     * formula produced them - or as text in whatever format the branch typed.
     * Every one of these must reach the form as a `Y-m-d` value; the old
     * parser only understood integer serials and `m-d-Y`, so the rest showed
     * up as blank date fields.
     */
    public function test_dates_are_parsed_from_serials_and_common_text_formats(): void
    {
        $accountLogin = $this->loggedAccount();

        $component = Livewire::test(Upload::class, ['logged_account' => $accountLogin]);

        $rows = [
            $this->headerRow(),
            $this->sheetRow('RTV-1', 45678.0, '01/21/2025', 45678),
            $this->sheetRow('RTV-2', 45678.0, '01/21/2025', '01/22/2025'),
            $this->sheetRow('RTV-3', 45678.0, '01/21/2025', '2025-01-23'),
            $this->sheetRow('RTV-4', 45678.0, '01/21/2025', '24-Jan-2025'),
            $this->sheetRow('RTV-5', 45678.0, '01/21/2025', '45681'),
        ];

        $this->invokeProcessData($component->instance(), $rows);

        $ppuData = $component->instance()->ppu_data;

        $this->assertSame('2025-01-21', $ppuData['date_submitted']);
        $this->assertSame('2025-01-21', $ppuData['pickup_date']);
        $this->assertSame(
            ['2025-01-21', '2025-01-22', '2025-01-23', '2025-01-24', '2025-01-24'],
            array_column($ppuData['lines'], 'rtv_date')
        );
    }

    /**
     * A date the parser cannot resolve must be reported on its row rather than
     * saved as an empty date.
     */
    public function test_unreadable_dates_are_flagged_and_block_saving(): void
    {
        $accountLogin = $this->loggedAccount();

        $component = Livewire::test(Upload::class, ['logged_account' => $accountLogin]);

        $rows = [
            $this->headerRow(),
            $this->sheetRow('RTV-1', '01/21/2025', '01/21/2025', 'n/a'),
        ];

        $this->invokeProcessData($component->instance(), $rows);

        $errData = $component->instance()->err_data;

        $this->assertStringContainsString('could not be read', $errData['rows'][0]['rtv_date']);

        $component->set('ppu_data', $component->instance()->ppu_data)
            ->call('savePPUForm', 'draft');

        $this->assertNull($component->get('success_data'));
        $this->assertDatabaseMissing('ppuform_items', ['rtv_number' => 'RTV-1']);
    }

    /**
     * The header dates repeat on every line; a blank cell on a later line must
     * not wipe the value read from the first one.
     */
    public function test_header_dates_survive_blank_cells_on_later_rows(): void
    {
        $accountLogin = $this->loggedAccount();

        $component = Livewire::test(Upload::class, ['logged_account' => $accountLogin]);

        $rows = [
            $this->headerRow(),
            $this->sheetRow('RTV-1', '01/21/2025', '01/22/2025', '01/23/2025'),
            $this->sheetRow('RTV-2', null, null, '01/23/2025'),
        ];

        $this->invokeProcessData($component->instance(), $rows);

        $ppuData = $component->instance()->ppu_data;

        $this->assertSame('2025-01-21', $ppuData['date_submitted']);
        $this->assertSame('2025-01-22', $ppuData['pickup_date']);
    }

    /**
     * Missing header dates are reported instead of being written to the form.
     */
    public function test_missing_header_dates_are_reported(): void
    {
        $accountLogin = $this->loggedAccount();

        $component = Livewire::test(Upload::class, ['logged_account' => $accountLogin]);

        $rows = [
            $this->headerRow(),
            $this->sheetRow('RTV-1', '', '', '01/23/2025'),
        ];

        $this->invokeProcessData($component->instance(), $rows);

        $errData = $component->instance()->err_data;

        $this->assertStringContainsString('could not be read', $errData['date_submitted']);
        $this->assertStringContainsString('could not be read', $errData['pickup_date']);
    }

    private function invokeProcessData(Upload $component, array $rows): void
    {
        $method = new \ReflectionMethod($component, 'processData');
        $method->setAccessible(true);
        $method->invoke($component, $rows);
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
