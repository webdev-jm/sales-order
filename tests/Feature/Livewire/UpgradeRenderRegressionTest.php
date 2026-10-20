<?php

namespace Tests\Feature\Livewire;

use App\Exports\MCPDashboardExport;
use App\Http\Livewire\OrganizationalStructure\StructureForm;
use App\Http\Livewire\Reports\Mcp\Dashboard;
use App\Http\Livewire\Reports\Mcp\Report;
use App\Models\JobTitle;
use App\Models\OrganizationStructure;
use App\Models\User;
use App\Models\UserBranchSchedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pages found broken after the Livewire 4 upgrade, by the browser crawl or in the error log.
 */
class UpgradeRenderRegressionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_structure_form_lists_the_positions_a_structure_can_report_to(): void
    {
        $this->actingAs(User::factory()->create(['status' => 'active']));
        $jobTitle = JobTitle::create(['job_title' => 'Regression Area Manager']);
        $head = OrganizationStructure::create(['job_title_id' => $jobTitle->id, 'type' => 'regression-type']);
        $child = OrganizationStructure::create(['job_title_id' => $jobTitle->id, 'type' => 'regression-type', 'reports_to_id' => $head->id]);

        Livewire::test(StructureForm::class, ['type' => 'regression-type'])
            ->assertOk()
            ->assertSee('Regression Area Manager - ', false)
            ->call('setForm', $child->id)
            ->assertSet('reports_to_id', $head->id)
            ->assertSeeHtml('value="' . $head->id . '"')
            ->assertDontSeeHtml('value="' . $child->id . '"');
    }

    public function test_the_mcp_dashboard_counts_schedules_although_users_also_have_a_status_column(): void
    {
        $viewer = User::factory()->create(['status' => 'active']);
        $this->actingAs($viewer);
        $salesman = User::factory()->create(['status' => 'active', 'firstname' => 'Regression', 'lastname' => 'Salesman']);

        UserBranchSchedule::create(['user_id' => $salesman->id, 'date' => now()->format('Y-m-d'), 'source' => 'activity-plan']);
        UserBranchSchedule::create(['user_id' => $salesman->id, 'date' => now()->format('Y-m-d'), 'source' => 'deviation']);
        UserBranchSchedule::create(['user_id' => $salesman->id, 'date' => now()->format('Y-m-d'), 'source' => 'request', 'status' => 'for reschedule']);

        $component = Livewire::test(Dashboard::class)->assertOk()->assertSee('Regression Salesman');

        $row = collect($component->viewData('schedule_results')->items())->firstWhere('uid', $salesman->id);
        $this->assertSame(2, (int) $row->schedule_count);
        $this->assertSame(1, (int) $row->deviation_count);
        $this->assertSame(0, (int) $row->request_count);
    }

    public function test_the_mcp_dashboard_export_counts_schedules_although_users_also_have_a_status_column(): void
    {
        $salesman = User::factory()->create(['status' => 'active', 'firstname' => 'Regression', 'lastname' => 'Exporter']);
        UserBranchSchedule::create(['user_id' => $salesman->id, 'date' => now()->format('Y-m-d'), 'source' => 'deviation']);

        $rows = (new MCPDashboardExport(now()->format('Y'), now()->format('m'), null))->collection()->last();
        $row = collect($rows)->firstWhere(0, 'Regression Exporter');

        $this->assertSame(['Regression Exporter', '1', '0', '1', '0', '0.0%'], $row);
    }
    public function test_the_mcp_report_pages_through_its_dates(): void
    {
        $this->actingAs(User::factory()->create(['status' => 'active']));
        $salesman = User::factory()->create(['status' => 'active', 'firstname' => 'Paging', 'lastname' => 'Salesman']);
        foreach ([0, 1, 2, 3] as $day) {
            UserBranchSchedule::create(['user_id' => $salesman->id, 'date' => now()->subDays($day)->format('Y-m-d'), 'source' => 'activity-plan']);
        }

        Livewire::test(Report::class)
            ->set('user_id', $salesman->id)
            ->set('date_from', now()->subDays(3)->format('Y-m-d'))
            ->set('date_to', now()->format('Y-m-d'))
            ->assertOk()
            ->call('gotoPage', 2, 'report-page')
            ->assertOk()
            ->assertSet('paginators.report-page', 2);
    }

    public function test_no_livewire_component_reads_the_livewire_2_page_property(): void
    {
        $offenders = collect(\Illuminate\Support\Facades\File::allFiles(app_path('Http/Livewire')))
            ->filter(fn ($file) => preg_match('/\$this->page\b(?!\s*\()/', $file->getContents()))
            ->map(fn ($file) => $file->getRelativePathname())
            ->values()
            ->all();

        $this->assertSame([], $offenders, 'Use $this->getPage() instead of $this->page in: ' . implode(', ', $offenders));
    }
}
