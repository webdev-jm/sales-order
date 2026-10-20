<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;

use App\Http\Livewire\SalesOrderCancelRequest\Create;
use App\Http\Livewire\SalesOrderCancelRequest\Index;
use App\Http\Livewire\SalesOrderCancelRequest\Show;
use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\Company;
use App\Models\SalesOrder;
use App\Models\SalesOrderCancelRequest;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\SoCancelRequestCompleted;
use App\Notifications\SoCancelRequestInProgress;
use App\Notifications\SoCancelRequestRejected;
use App\Notifications\SoCancelRequestSubmitted;
use App\Notifications\SoCancelRequestWithdrawn;
use App\Services\SalesOrderCancelRequestService;
use App\Support\SoCancelRequestMenuFilter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

/**
 * SO cancel requests: a salesman asks to cancel an order already in SYSPRO, SCM processes it,
 * and each side is notified.
 */
class SalesOrderCancelRequestTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsReferenceData;

    private Account $account;
    private AccountLogin $accountLogin;
    private User $salesman;
    private User $scm;
    private SalesOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedSettings();
        Config::set('activitylog.enabled', false);
        Notification::fake();
        Queue::fake();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->account = Account::factory()->create([
            'company_id'   => Company::factory()->create()->id,
            'account_code' => 'MET001',
            'short_name'   => 'METRO',
            'discount_id'  => null,
        ]);

        $this->salesman = $this->userWith(['so cancel request create', 'sales order access']);
        $this->salesman->accounts()->attach($this->account->id);
        $this->accountLogin = AccountLogin::factory()->create([
            'user_id'    => $this->salesman->id,
            'account_id' => $this->account->id,
            'time_out'   => now(),
        ]);

        $this->scm = $this->userWith(['so cancel request process']);

        $this->order = $this->orderInSyspro(['control_number' => 'SO-CR-0001', 'po_number' => 'PO-CR-0001', 'reference' => '245001']);
    }

    /**
     * @param array<int, string> $permissions
     */
    private function userWith(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'], ['module' => 'Test', 'description' => $permission]);
        }

        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo($permissions);

        return $user;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function orderInSyspro(array $attributes = [], ?AccountLogin $accountLogin = null): SalesOrder
    {
        return SalesOrder::factory()->create($attributes + [
            'account_login_id' => ($accountLogin ?? $this->accountLogin)->id,
            'status'           => 'finalized',
            'upload_status'    => 1,
            'reference'        => fake()->unique()->numerify('24####'),
        ]);
    }

    private function fileRequest(?SalesOrder $order = null): SalesOrderCancelRequest
    {
        return app(SalesOrderCancelRequestService::class)->submit($order ?? $this->order, $this->salesman, 'Customer cancelled the PO.');
    }

    private function inProgressRequest(): SalesOrderCancelRequest
    {
        $request = $this->fileRequest();
        app(SalesOrderCancelRequestService::class)->markInProgress($request, $this->scm);

        return $request->fresh();
    }

    // -------------------------------------------------------------------------
    // Filing a request
    // -------------------------------------------------------------------------

    public function test_salesman_files_a_request_from_the_order_page_and_scm_is_notified(): void
    {
        Carbon::setTestNow('2026-10-15 09:00:00');
        $this->actingAs($this->salesman);

        $component = Livewire::test(Create::class, ['salesOrderId' => $this->order->id])
            ->assertSee('Request Cancelation')
            ->call('open')
            ->set('reason', 'Customer cancelled the PO.')
            ->call('submit')
            ->assertHasNoErrors();

        $request = SalesOrderCancelRequest::where('sales_order_id', $this->order->id)->firstOrFail();
        $component->assertRedirect(route('so-cancel-request.show', $request->id));

        $this->assertSame('CR-202610-0001', $request->request_number);
        $this->assertSame(SalesOrderCancelRequest::STATUS_PENDING, $request->status);
        $this->assertSame($this->salesman->id, $request->user_id);
        $this->assertSame('Customer cancelled the PO.', $request->reason);
        $this->assertSame('finalized', $this->order->fresh()->status);

        Notification::assertSentTo($this->scm, SoCancelRequestSubmitted::class, fn (SoCancelRequestSubmitted $notification): bool => $notification->cancelRequest->is($request));
        Notification::assertNotSentTo($this->salesman, SoCancelRequestSubmitted::class);

        Carbon::setTestNow();
    }

    public function test_salesman_picks_one_of_their_orders_in_syspro_from_the_module_page(): void
    {
        $notInSyspro = SalesOrder::factory()->create([
            'account_login_id' => $this->accountLogin->id, 'control_number' => 'SO-CR-DRAFT', 'status' => 'finalized', 'upload_status' => null, 'reference' => null,
        ]);
        $cancelled = $this->orderInSyspro(['control_number' => 'SO-CR-GONE', 'status' => 'cancelled']);
        $otherLogin = AccountLogin::factory()->create([
            'user_id'    => User::factory()->create()->id,
            'account_id' => Account::factory()->create(['company_id' => $this->account->company_id, 'discount_id' => null])->id,
        ]);
        $otherAccount = $this->orderInSyspro(['control_number' => 'SO-CR-OTHER'], $otherLogin);
        $second = $this->orderInSyspro(['control_number' => 'SO-CR-0002', 'po_number' => 'PO-FIND-ME']);

        $this->actingAs($this->salesman);

        $component = Livewire::test(Create::class)
            ->assertSee('New Request')
            ->call('open')
            ->assertSee('SO-CR-0001')
            ->assertSee('SO-CR-0002')
            ->assertDontSee($notInSyspro->control_number)
            ->assertDontSee($cancelled->control_number)
            ->assertDontSee($otherAccount->control_number)
            ->set('search', 'PO-FIND-ME')
            ->assertSee('SO-CR-0002')
            ->assertDontSee('SO-CR-0001')
            ->call('selectOrder', $second->id)
            ->set('reason', 'Duplicate of SO-CR-0001.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sales_order_cancel_requests', ['sales_order_id' => $second->id, 'status' => 'pending']);
        $component->assertRedirect();
    }

    public function test_the_order_and_reason_are_required(): void
    {
        $this->actingAs($this->salesman);

        Livewire::test(Create::class)
            ->call('open')
            ->call('submit')
            ->assertHasErrors(['selectedOrderId' => 'required', 'reason' => 'required']);

        $this->assertSame(0, SalesOrderCancelRequest::count());
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function ineligibleOrders(): array
    {
        return [
            'not uploaded to SYSPRO'     => [['upload_status' => null, 'reference' => null], 'Only orders already in SYSPRO'],
            'upload error'               => [['upload_status' => 0, 'reference' => null], 'Only orders already in SYSPRO'],
            'uploaded without SYSPRO SO' => [['upload_status' => 1, 'reference' => ''], 'Only orders already in SYSPRO'],
            'already cancelled'          => [['status' => 'cancelled'], 'already cancelled'],
        ];
    }

    /**
     *
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('ineligibleOrders')]
    public function test_orders_that_are_not_in_syspro_or_already_cancelled_cannot_be_requested(array $attributes, string $error): void
    {
        $this->order->update($attributes);
        $this->actingAs($this->salesman);

        Livewire::test(Create::class, ['salesOrderId' => $this->order->id])
            ->assertDontSee('Request Cancelation');

        Livewire::test(Create::class)
            ->call('open')
            ->set('selectedOrderId', $this->order->id)
            ->set('reason', 'Please cancel.')
            ->call('submit')
            ->assertHasErrors('selectedOrderId');

        $this->assertStringContainsString($error, (string) app(SalesOrderCancelRequestService::class)->eligibilityError($this->order->fresh(), $this->salesman));
        $this->assertSame(0, SalesOrderCancelRequest::count());
    }

    public function test_an_order_in_an_account_not_assigned_to_the_user_cannot_be_requested(): void
    {
        $this->salesman->accounts()->detach();
        $this->actingAs($this->salesman);

        Livewire::test(Create::class)
            ->call('open')
            ->set('selectedOrderId', $this->order->id)
            ->set('reason', 'Please cancel.')
            ->call('submit')
            ->assertHasErrors('selectedOrderId');

        $this->assertSame(0, SalesOrderCancelRequest::count());
    }

    public function test_a_user_without_the_create_permission_cannot_request(): void
    {
        $this->scm->accounts()->attach($this->account->id);

        $this->expectExceptionMessage('You do not have access to request sales order cancelations.');

        app(SalesOrderCancelRequestService::class)->submit($this->order, $this->scm, 'Please cancel.');
    }

    public function test_an_order_with_an_open_request_cannot_be_requested_again(): void
    {
        $existing = $this->fileRequest();
        $this->actingAs($this->salesman);

        Livewire::test(Create::class, ['salesOrderId' => $this->order->id])
            ->assertSee($existing->request_number)
            ->assertDontSee('Request Cancelation');

        $this->expectExceptionMessage('Cancel request ' . $existing->request_number . ' is already open for this order.');
        $this->fileRequest();
    }

    public function test_a_rejected_order_can_be_requested_again(): void
    {
        $request = $this->inProgressRequest();
        app(SalesOrderCancelRequestService::class)->reject($request, $this->scm, 'Already delivered.');

        $again = $this->fileRequest();

        $this->assertSame(SalesOrderCancelRequest::STATUS_PENDING, $again->status);
        $this->assertNotSame($request->request_number, $again->request_number);
    }

    public function test_request_numbers_count_up_and_restart_each_month(): void
    {
        $service = app(SalesOrderCancelRequestService::class);

        Carbon::setTestNow('2026-09-30 17:00:00');
        $this->assertSame('CR-202609-0001', $this->fileRequest()->request_number);
        $this->assertSame('CR-202609-0002', $this->fileRequest($this->orderInSyspro())->request_number);

        Carbon::setTestNow('2026-10-01 08:00:00');
        $this->assertSame('CR-202610-0001', $service->nextRequestNumber());
        $this->assertSame('CR-202610-0001', $this->fileRequest($this->orderInSyspro())->request_number);
        $this->assertSame('CR-202610-0002', $service->nextRequestNumber());

        Carbon::setTestNow();
    }

    // -------------------------------------------------------------------------
    // Withdrawing
    // -------------------------------------------------------------------------

    public function test_the_salesman_withdraws_a_pending_request_and_scm_is_told(): void
    {
        $request = $this->fileRequest();
        $this->actingAs($this->salesman);

        Livewire::test(Show::class, ['cancelRequestId' => $request->id])
            ->assertSee('Withdraw Request')
            ->call('withdraw')
            ->assertHasNoErrors()
            ->assertRedirect(route('so-cancel-request.show', $request->id));

        $request->refresh();
        $this->assertSame(SalesOrderCancelRequest::STATUS_WITHDRAWN, $request->status);
        $this->assertNotNull($request->closed_at);
        $this->assertSame('finalized', $this->order->fresh()->status);
        Notification::assertSentTo($this->scm, SoCancelRequestWithdrawn::class);

        $this->assertNull(app(SalesOrderCancelRequestService::class)->eligibilityError($this->order->fresh(), $this->salesman));
    }

    public function test_a_request_cannot_be_withdrawn_once_in_progress(): void
    {
        $request = $this->inProgressRequest();
        $this->actingAs($this->salesman);

        Livewire::test(Show::class, ['cancelRequestId' => $request->id])
            ->assertDontSee('Withdraw Request')
            ->call('withdraw')
            ->assertHasErrors('action');

        $this->assertSame(SalesOrderCancelRequest::STATUS_IN_PROGRESS, $request->fresh()->status);
    }

    public function test_only_the_requestor_can_withdraw(): void
    {
        $request = $this->fileRequest();

        $this->expectExceptionMessage('Only the salesman who filed this request can withdraw it.');

        app(SalesOrderCancelRequestService::class)->withdraw($request, $this->scm);
    }

    // -------------------------------------------------------------------------
    // Processing by SCM
    // -------------------------------------------------------------------------

    public function test_scm_marks_in_progress_then_completes_and_the_order_is_cancelled(): void
    {
        $request = $this->fileRequest();
        $this->actingAs($this->scm);

        Livewire::test(Show::class, ['cancelRequestId' => $request->id])
            ->assertSee('Mark In Progress')
            ->assertDontSee('Withdraw Request')
            ->call('markInProgress')
            ->assertHasNoErrors();

        $request->refresh();
        $this->assertSame(SalesOrderCancelRequest::STATUS_IN_PROGRESS, $request->status);
        $this->assertSame($this->scm->id, $request->processed_by);
        $this->assertNotNull($request->in_progress_at);
        Notification::assertSentTo($this->salesman, SoCancelRequestInProgress::class);

        Livewire::test(Show::class, ['cancelRequestId' => $request->id])
            ->call('startComplete')
            ->set('remarks', 'Cancelled in SYSPRO.')
            ->call('complete')
            ->assertHasNoErrors();

        $request->refresh();
        $this->assertSame(SalesOrderCancelRequest::STATUS_COMPLETED, $request->status);
        $this->assertSame('Cancelled in SYSPRO.', $request->remarks);
        $this->assertNotNull($request->closed_at);
        $this->assertSame('cancelled', $this->order->fresh()->status);
        Notification::assertSentTo($this->salesman, SoCancelRequestCompleted::class);
        Notification::assertNotSentTo($this->scm, SoCancelRequestCompleted::class);
    }

    public function test_completion_remarks_are_optional(): void
    {
        $request = $this->inProgressRequest();
        $this->actingAs($this->scm);

        Livewire::test(Show::class, ['cancelRequestId' => $request->id])
            ->call('startComplete')
            ->call('complete')
            ->assertHasNoErrors();

        $this->assertSame(SalesOrderCancelRequest::STATUS_COMPLETED, $request->fresh()->status);
        $this->assertNull($request->fresh()->remarks);
    }

    public function test_scm_rejects_with_required_remarks_and_the_order_is_kept(): void
    {
        $request = $this->inProgressRequest();
        $this->actingAs($this->scm);

        $component = Livewire::test(Show::class, ['cancelRequestId' => $request->id])
            ->call('startReject')
            ->call('reject')
            ->assertHasErrors(['remarks' => 'required']);

        $this->assertSame(SalesOrderCancelRequest::STATUS_IN_PROGRESS, $request->fresh()->status);

        $component->set('remarks', 'Already delivered on Oct 14.')
            ->call('reject')
            ->assertHasNoErrors();

        $request->refresh();
        $this->assertSame(SalesOrderCancelRequest::STATUS_REJECTED, $request->status);
        $this->assertSame('Already delivered on Oct 14.', $request->remarks);
        $this->assertSame('finalized', $this->order->fresh()->status);
        Notification::assertSentTo($this->salesman, SoCancelRequestRejected::class);
    }

    public function test_a_pending_request_must_be_in_progress_before_it_is_closed(): void
    {
        $request = $this->fileRequest();
        $this->actingAs($this->scm);

        Livewire::test(Show::class, ['cancelRequestId' => $request->id])
            ->call('complete')
            ->assertHasErrors('action')
            ->set('remarks', 'No.')
            ->call('reject')
            ->assertHasErrors('action');

        $this->assertSame(SalesOrderCancelRequest::STATUS_PENDING, $request->fresh()->status);
        $this->assertSame('finalized', $this->order->fresh()->status);
    }

    public function test_a_salesman_cannot_process_requests(): void
    {
        $request = $this->fileRequest();
        $this->actingAs($this->salesman);

        Livewire::test(Show::class, ['cancelRequestId' => $request->id])
            ->assertDontSee('Mark In Progress')
            ->call('markInProgress')
            ->assertHasErrors('action');

        $this->assertSame(SalesOrderCancelRequest::STATUS_PENDING, $request->fresh()->status);
        Notification::assertNotSentTo($this->salesman, SoCancelRequestInProgress::class);
    }

    public function test_a_withdrawn_request_cannot_be_processed(): void
    {
        $request = $this->fileRequest();
        app(SalesOrderCancelRequestService::class)->withdraw($request, $this->salesman);

        $this->expectExceptionMessage('This request is Withdrawn.');

        app(SalesOrderCancelRequestService::class)->markInProgress($request->fresh(), $this->scm);
    }

    // -------------------------------------------------------------------------
    // Screens and access
    // -------------------------------------------------------------------------

    public function test_the_list_shows_a_salesman_their_own_requests_and_scm_all_of_them(): void
    {
        $mine = $this->fileRequest();

        $colleague = $this->userWith(['so cancel request create']);
        $colleague->accounts()->attach($this->account->id);
        $theirs = app(SalesOrderCancelRequestService::class)->submit($this->orderInSyspro(), $colleague, 'Wrong ship-to.');

        $this->actingAs($this->salesman);
        Livewire::test(Index::class)
            ->assertSee($mine->request_number)
            ->assertDontSee($theirs->request_number);

        $this->actingAs($this->scm);
        Livewire::test(Index::class)
            ->assertSee($mine->request_number)
            ->assertSee($theirs->request_number)
            ->call('setStatus', SalesOrderCancelRequest::STATUS_COMPLETED)
            ->assertDontSee($mine->request_number)
            ->call('setStatus', SalesOrderCancelRequest::STATUS_PENDING)
            ->set('search', 'SO-CR-0001')
            ->assertSee($mine->request_number)
            ->assertDontSee($theirs->request_number);
    }

    public function test_a_request_page_opens_for_its_requestor_and_scm_only(): void
    {
        $request = $this->fileRequest();

        $this->actingAs($this->salesman)->get(route('so-cancel-request.show', $request->id))
            ->assertOk()
            ->assertSee($request->request_number)
            ->assertSee('SO-CR-0001');

        $this->actingAs($this->scm)->get(route('so-cancel-request.show', $request->id))->assertOk();

        $colleague = $this->userWith(['so cancel request create']);
        $this->actingAs($colleague)->get(route('so-cancel-request.show', $request->id))->assertForbidden();

        $outsider = User::factory()->create(['status' => 'active']);
        $this->actingAs($outsider)->get(route('so-cancel-request.index'))->assertForbidden();
    }

    public function test_the_list_page_renders_for_salesman_and_scm(): void
    {
        $request = $this->fileRequest();

        $this->actingAs($this->salesman)->get(route('so-cancel-request.index'))
            ->assertOk()
            ->assertSee('New Request')
            ->assertSee($request->request_number);

        $this->actingAs($this->scm)->get(route('so-cancel-request.index'))
            ->assertOk()
            ->assertDontSee('New Request')
            ->assertSee($request->request_number);
    }

    public function test_the_sales_order_page_follows_the_request(): void
    {
        $this->actingAs($this->salesman);
        $page = route('sales-order.show', $this->order->id);

        $this->get($page)->assertOk()->assertSee('Request Cancelation');

        $request = $this->fileRequest();
        $this->get($page)
            ->assertOk()
            ->assertSee($request->request_number)
            ->assertDontSee('Request Cancelation');

        app(SalesOrderCancelRequestService::class)->markInProgress($request, $this->scm);
        app(SalesOrderCancelRequestService::class)->complete($request->fresh(), $this->scm, null);

        $this->get($page)
            ->assertOk()
            ->assertSee('Cancelled by ' . $this->scm->fullName(), false)
            ->assertSee($request->request_number)
            ->assertDontSee('Request Cancelation');
    }

    private function completeRequest(): SalesOrderCancelRequest
    {
        $request = $this->inProgressRequest();
        app(SalesOrderCancelRequestService::class)->complete($request, $this->scm, null);

        return $request->fresh();
    }

    public function test_the_sales_order_list_shows_a_completed_cancelation_as_cancelled(): void
    {
        $this->salesman->givePermissionTo(Permission::firstOrCreate(['name' => 'sales order list', 'guard_name' => 'web'], ['module' => 'Test', 'description' => 'sales order list']));
        $this->orderInSyspro(['control_number' => 'SO-CR-KEEP']);
        $this->completeRequest();
        $this->actingAs($this->salesman);

        $cancelledBadge = '<span class="badge badge-danger">cancelled</span>';

        $this->get(route('sales-order.list', ['search' => 'SO-CR-0001']))
            ->assertOk()
            ->assertSee($cancelledBadge, false)
            ->assertDontSee('Uploaded</span>', false);

        $this->get(route('sales-order.list', ['status' => 'uploaded', 'search' => 'SO-CR-']))
            ->assertOk()
            ->assertSee('<td>SO-CR-KEEP</td>', false)
            ->assertDontSee('<td>SO-CR-0001</td>', false);

        $this->get(route('sales-order.list', ['status' => 'cancelled', 'search' => 'SO-CR-']))
            ->assertOk()
            ->assertSee('<td>SO-CR-0001</td>', false)
            ->assertDontSee('<td>SO-CR-KEEP</td>', false);
    }

    public function test_the_sales_orders_page_shows_a_completed_cancelation_as_cancelled(): void
    {
        AccountLogin::factory()->create([
            'user_id'    => $this->salesman->id,
            'account_id' => $this->account->id,
            'time_out'   => null,
        ]);
        $this->completeRequest();

        $this->actingAs($this->salesman)
            ->get(route('sales-order.index', ['search' => 'SO-CR-0001']))
            ->assertOk()
            ->assertSee('SO-CR-0001')
            ->assertSee('<span class="badge badge-danger">cancelled</span>', false)
            ->assertDontSee('Uploaded</span>', false);
    }

    public function test_the_sales_order_page_offers_the_cancel_request_instead_of_resubmit(): void
    {
        $this->salesman->givePermissionTo(Permission::firstOrCreate(['name' => 'sales order create', 'guard_name' => 'web'], ['module' => 'Test', 'description' => 'sales order create']));
        $openLogin = AccountLogin::factory()->create([
            'user_id'    => $this->salesman->id,
            'account_id' => $this->account->id,
            'time_out'   => null,
        ]);
        $this->actingAs($this->salesman)->withSession(['logged_account' => $openLogin]);

        $this->get(route('sales-order.show', $this->order->id))
            ->assertOk()
            ->assertSee('Request Cancelation')
            ->assertDontSee('Resubmit Sales Order')
            ->assertDontSee(route('sales-order.resubmit', $this->order->id));
    }

    public function test_resubmit_is_refused_while_a_request_is_open(): void
    {
        $this->salesman->givePermissionTo(Permission::firstOrCreate(['name' => 'sales order create', 'guard_name' => 'web'], ['module' => 'Test', 'description' => 'sales order create']));
        AccountLogin::factory()->create([
            'user_id'    => $this->salesman->id,
            'account_id' => $this->account->id,
            'time_out'   => null,
        ]);
        $request = $this->fileRequest();

        $this->actingAs($this->salesman)
            ->get(route('sales-order.resubmit', $this->order->id))
            ->assertRedirect(route('sales-order.show', $this->order->id))
            ->assertSessionHas('message_error', 'Cancel request ' . $request->request_number . ' is open for this order, so it cannot be resubmitted.');

        $this->assertSame('finalized', $this->order->fresh()->status);
    }

    public function test_the_sidebar_shows_the_pending_count_to_scm_only(): void
    {
        $this->fileRequest();
        $this->fileRequest($this->orderInSyspro());
        $inProgress = $this->fileRequest($this->orderInSyspro());
        app(SalesOrderCancelRequestService::class)->markInProgress($inProgress, $this->scm);

        $filter = new SoCancelRequestMenuFilter();
        $item   = ['key' => SoCancelRequestMenuFilter::MENU_KEY, 'text' => 'SO Cancel Requests'];

        $this->actingAs($this->scm);
        $this->assertSame(2, $filter->transform($item)['label']);
        $this->assertArrayNotHasKey('label', $filter->transform(['key' => 'other', 'text' => 'Invoice']));

        $this->actingAs($this->salesman);
        $this->assertArrayNotHasKey('label', $filter->transform($item));
    }

    // -------------------------------------------------------------------------
    // Notifications
    // -------------------------------------------------------------------------

    public function test_notifications_link_to_the_request_and_follow_the_email_setting(): void
    {
        $request = $this->fileRequest();
        $notification = new SoCancelRequestSubmitted($request);

        $data = $notification->toArray($this->scm);
        $this->assertSame('SO Cancel Request', $data['module']);
        $this->assertSame(route('so-cancel-request.show', $request->id), $data['url']);
        $this->assertStringContainsString('SO-CR-0001', $data['message']);
        $this->assertStringContainsString('245001', $data['message']);
        $this->assertStringContainsString('Customer cancelled the PO.', $data['message']);

        Setting::query()->update(['email_sending' => 0]);
        Cache::forget('app_settings');
        $this->assertSame(['database'], $notification->via($this->scm));

        Setting::query()->update(['email_sending' => 1]);
        Cache::forget('app_settings');
        $this->assertSame(['mail', 'database'], $notification->via($this->scm));
    }

    public function test_rejection_notice_carries_the_scm_remarks(): void
    {
        $request = $this->inProgressRequest();
        app(SalesOrderCancelRequestService::class)->reject($request, $this->scm, 'Already delivered.');

        Notification::assertSentTo($this->salesman, SoCancelRequestRejected::class, function (SoCancelRequestRejected $notification): bool {
            return str_contains($notification->toArray($this->salesman)['message'], 'Already delivered.');
        });
    }
}
