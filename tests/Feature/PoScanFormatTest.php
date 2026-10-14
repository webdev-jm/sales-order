<?php

namespace Tests\Feature;

use App\Http\Livewire\SalesOrderTemplate\Index;
use App\Http\Livewire\SalesOrderTemplate\ScanFormats;
use App\Models\Account;
use App\Models\PoScanFormat;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PoScanFormatTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->account = Account::factory()->create(['account_code' => 'SM001', 'account_name' => 'SM SUPERMARKET']);
    }

    /**
     * @param array<int, string> $permissions
     */
    private function actingAsUser(array $permissions = ['so template access', 'po scan format manage']): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'], ['module' => 'Test', 'description' => $permission]);
        }

        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo($permissions);
        $this->actingAs($user);

        return $user;
    }

    public function test_formats_tab_needs_the_manage_permission(): void
    {
        $this->actingAsUser(['so template access']);

        Livewire::test(Index::class)
            ->assertDontSee('PO Formats')
            ->call('setTab', 'formats')
            ->assertSet('activeTab', 'upload');
    }

    public function test_formats_tab_opens_with_the_manage_permission(): void
    {
        $this->actingAsUser();

        Livewire::test(Index::class)
            ->call('setTab', 'formats')
            ->assertSet('activeTab', 'formats')
            ->assertSee('Notes for the AI');
    }

    public function test_a_format_can_be_added_for_an_account(): void
    {
        $user = $this->actingAsUser();

        Livewire::test(ScanFormats::class)
            ->set('account_id', (string) $this->account->id)
            ->set('name', '  Standard PO  ')
            ->set('default_uom', 'PCS')
            ->set('instructions', 'Quantities are in pieces.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('name', '')
            ->assertSet('editingId', null)
            ->assertSee('PO format saved.');

        $format = PoScanFormat::where('account_id', $this->account->id)->sole();
        $this->assertSame('Standard PO', $format->name);
        $this->assertSame('PCS', $format->default_uom);
        $this->assertSame('Quantities are in pieces.', $format->instructions);
        $this->assertTrue($format->is_active);
        $this->assertSame($user->id, $format->created_by);
    }

    public function test_a_format_needs_an_account_a_name_and_a_known_unit(): void
    {
        $this->actingAsUser();

        Livewire::test(ScanFormats::class)
            ->set('default_uom', 'BOX')
            ->call('save')
            ->assertHasErrors(['account_id' => 'required', 'name' => 'required', 'default_uom' => 'in']);
    }

    public function test_format_names_are_unique_per_account_only(): void
    {
        $this->actingAsUser();
        $otherAccount = Account::factory()->create();
        PoScanFormat::factory()->create(['account_id' => $this->account->id, 'name' => 'Standard PO']);

        Livewire::test(ScanFormats::class)
            ->set('account_id', (string) $this->account->id)
            ->set('name', 'Standard PO')
            ->call('save')
            ->assertHasErrors(['name' => 'unique']);

        Livewire::test(ScanFormats::class)
            ->set('account_id', (string) $otherAccount->id)
            ->set('name', 'Standard PO')
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_a_format_can_be_edited_and_switched_off(): void
    {
        $this->actingAsUser();
        $format = PoScanFormat::factory()->create(['account_id' => $this->account->id, 'name' => 'Old name']);

        Livewire::test(ScanFormats::class)
            ->call('edit', $format->id)
            ->assertSet('editingId', $format->id)
            ->assertSet('account_id', (string) $this->account->id)
            ->assertSet('name', 'Old name')
            ->assertSee('Edit PO Format')
            ->set('name', 'Old name')
            ->set('is_active', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse($format->fresh()->is_active);
    }

    public function test_a_format_can_be_deleted(): void
    {
        $this->actingAsUser();
        $format = PoScanFormat::factory()->create(['account_id' => $this->account->id]);

        Livewire::test(ScanFormats::class)->call('delete', $format->id);

        $this->assertNull(PoScanFormat::find($format->id));
    }

    public function test_formats_cannot_be_changed_without_the_manage_permission(): void
    {
        $this->actingAsUser(['so template access']);
        $format = PoScanFormat::factory()->create(['account_id' => $this->account->id]);

        Livewire::test(ScanFormats::class)->call('delete', $format->id)->assertForbidden();
        Livewire::test(ScanFormats::class)
            ->set('account_id', (string) $this->account->id)
            ->set('name', 'Sneaky')
            ->call('save')
            ->assertForbidden();

        $this->assertNotNull($format->fresh());
        $this->assertFalse(PoScanFormat::where('name', 'Sneaky')->exists());
    }

    public function test_the_list_can_be_searched_by_account(): void
    {
        $this->actingAsUser();
        $other = Account::factory()->create(['account_code' => 'ZZ999', 'account_name' => 'ROBINSONS']);
        PoScanFormat::factory()->create(['account_id' => $this->account->id, 'name' => 'SM layout']);
        PoScanFormat::factory()->create(['account_id' => $other->id, 'name' => 'Robinsons layout']);

        Livewire::test(ScanFormats::class)
            ->set('search', 'ROBINSONS')
            ->assertSee('Robinsons layout')
            ->assertDontSee('SM layout');
    }
}
