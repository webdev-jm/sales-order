<?php

namespace Tests\Feature;

use App\Http\Livewire\ConfirmDelete;
use App\Models\Company;
use App\Models\Department;
use App\Models\Discount;
use App\Models\SalesOrderCutOff;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ConfirmDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param array<int, string> $permissions
     */
    private function actingAsUser(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'], ['module' => 'Test', 'description' => $permission]);
        }

        $user = User::factory()->create(['status' => 'active', 'password' => Hash::make('secret-123')]);
        $user->givePermissionTo($permissions);
        $this->actingAs($user);

        return $user;
    }

    private function makeDiscount(): Discount
    {
        $company = Company::create(['name' => 'TEST CO', 'order_limit' => 10]);

        return Discount::create([
            'company_id' => $company->id,
            'discount_code' => 'T'.random_int(100, 999),
            'description' => 'Test discount',
            'discount_1' => 5,
            'discount_2' => 0,
            'discount_3' => 0,
        ]);
    }

    public function test_deleting_a_department_deletes_the_department_and_not_the_discount_with_the_same_id(): void
    {
        $this->actingAsUser(['department delete']);
        $department = Department::create(['department_code' => 'TST', 'department_name' => 'Test Department']);
        $discount = $this->makeDiscount();

        Livewire::test(ConfirmDelete::class)
            ->assertSeeHtml('disabled')
            ->call('setModel', 'Department', $department->id)
            ->assertSee('You are about to delete:')
            ->assertSee('department [TST] Test Department')
            ->set('password', 'secret-123')
            ->call('submitForm')
            ->assertRedirect('/department');

        $this->assertSoftDeleted('departments', ['id' => $department->id]);
        $this->assertNotSoftDeleted('discounts', ['id' => $discount->id]);
    }

    public function test_removed_module_types_are_refused(): void
    {
        $this->actingAsUser(['territory delete', 'district delete']);

        foreach (['Territory', 'District'] as $type) {
            Livewire::test(ConfirmDelete::class)
                ->call('setModel', $type, 1)
                ->assertSet('model', null)
                ->assertSet('error_message', 'This record cannot be deleted here.');
        }
    }

    public function test_a_user_without_the_delete_permission_cannot_delete(): void
    {
        $this->actingAsUser(['discount access']);
        $discount = $this->makeDiscount();

        Livewire::test(ConfirmDelete::class)
            ->call('setModel', 'Discount', $discount->id)
            ->assertSet('model', null)
            ->assertSet('error_message', 'You are not allowed to delete this record.')
            ->set('password', 'secret-123')
            ->call('submitForm')
            ->assertNoRedirect();

        $this->assertNotSoftDeleted('discounts', ['id' => $discount->id]);
    }

    public function test_a_user_with_the_delete_permission_can_delete(): void
    {
        $this->actingAsUser(['discount delete']);
        $discount = $this->makeDiscount();

        Livewire::test(ConfirmDelete::class)
            ->call('setModel', 'Discount', $discount->id)
            ->set('password', 'secret-123')
            ->call('submitForm')
            ->assertRedirect('/discount');

        $this->assertSoftDeleted('discounts', ['id' => $discount->id]);
    }

    public function test_a_wrong_password_does_not_delete(): void
    {
        $this->actingAsUser(['discount delete']);
        $discount = $this->makeDiscount();

        Livewire::test(ConfirmDelete::class)
            ->call('setModel', 'Discount', $discount->id)
            ->set('password', 'not-the-password')
            ->call('submitForm')
            ->assertSet('error_message', 'incorrect password.')
            ->assertNoRedirect();

        $this->assertNotSoftDeleted('discounts', ['id' => $discount->id]);
    }

    public function test_an_unknown_type_is_refused(): void
    {
        $this->actingAsUser(['discount delete']);

        Livewire::test(ConfirmDelete::class)
            ->call('setModel', 'Setting', 1)
            ->assertSet('model', null)
            ->assertSet('error_message', 'This record cannot be deleted here.');
    }

    public function test_a_draft_record_can_still_be_deleted_without_the_delete_permission(): void
    {
        $this->actingAsUser([]);
        $order = new \App\Models\SalesOrder(['status' => 'draft']);
        $finalized = new \App\Models\SalesOrder(['status' => 'finalized']);

        $component = new ConfirmDelete();
        $canDelete = (new \ReflectionMethod($component, 'canDelete'))->getClosure($component);

        $this->assertTrue($canDelete('SalesOrder', $order));
        $this->assertFalse($canDelete('SalesOrder', $finalized));
        $this->assertFalse($canDelete('Discount', new Discount()));
    }

    public function test_a_cut_off_can_be_deleted_with_its_permission(): void
    {
        $user = $this->actingAsUser(['so cut-off delete']);
        $cut_off = SalesOrderCutOff::create([
            'user_id' => $user->id,
            'start_date' => strtotime('2026-10-01 08:00'),
            'end_date' => strtotime('2026-10-01 17:00'),
            'message' => 'Test cut-off',
        ]);

        Livewire::test(ConfirmDelete::class)
            ->call('setModel', 'SalesOrderCutOff', $cut_off->id)
            ->assertSet('name', 'cut-off 2026-10-01 08:00 am to 2026-10-01 05:00 pm')
            ->set('password', 'secret-123')
            ->call('submitForm')
            ->assertRedirect('/cut-off');

        $this->assertDatabaseMissing('sales_order_cut_offs', ['id' => $cut_off->id]);
    }
}
