<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Paf;
use App\Models\PafDetail;

/**
 * Tests for PAF module fixes:
 * - pafs and paf_details tables have all required columns (via migration)
 * - Paf model has correct $fillable and all required relationships
 * - PafDetail model has correct $fillable and relationships
 * - Approval.php has required imports (Notification facade + PafApproved)
 * - Create.php setDetail() searches $this->branches (not $this->products) for branch lookup
 * - Edit.php uses $this->paf->paf_number, $this->paf->paf_activity_id, and pre_plan relationship
 */
class PafTest extends TestCase
{
    // ── Paf model ─────────────────────────────────────────────────────────────

    public function test_paf_model_fillable_has_correct_fields(): void
    {
        $model = new Paf();
        $fillable = $model->getFillable();

        foreach (['account_id', 'user_id', 'paf_expense_type_id', 'paf_support_type_id', 'paf_activity_id', 'paf_number', 'title', 'concept', 'start_date', 'end_date', 'status'] as $field) {
            $this->assertContains($field, $fillable, "Paf model must have '{$field}' in \$fillable.");
        }
    }

    public function test_paf_model_fillable_does_not_have_old_fields(): void
    {
        $model = new Paf();
        $fillable = $model->getFillable();

        foreach (['PAFNo', 'account_code', 'account_name', 'support_type'] as $field) {
            $this->assertNotContains($field, $fillable, "Paf model must NOT have legacy field '{$field}' in \$fillable.");
        }
    }

    public function test_paf_model_has_user_relationship(): void
    {
        $source = file_get_contents(app_path('Models/Paf.php'));
        $this->assertStringContainsString('function user()', $source, 'Paf model must define a user() relationship.');
        $this->assertStringContainsString('belongsTo(User::class)', $source, 'Paf::user() must use belongsTo(User::class).');
    }

    public function test_paf_model_has_account_relationship(): void
    {
        $source = file_get_contents(app_path('Models/Paf.php'));
        $this->assertStringContainsString('function account()', $source, 'Paf model must define an account() relationship.');
        $this->assertStringContainsString('belongsTo(Account::class)', $source, 'Paf::account() must use belongsTo(Account::class).');
    }

    public function test_paf_model_has_expense_type_relationship(): void
    {
        $source = file_get_contents(app_path('Models/Paf.php'));
        $this->assertStringContainsString('function expense_type()', $source, 'Paf model must define an expense_type() relationship.');
        $this->assertStringContainsString("'paf_expense_type_id'", $source, 'Paf::expense_type() must use paf_expense_type_id as the foreign key.');
    }

    public function test_paf_model_has_support_type_relationship(): void
    {
        $source = file_get_contents(app_path('Models/Paf.php'));
        $this->assertStringContainsString('function support_type()', $source, 'Paf model must define a support_type() relationship.');
        $this->assertStringContainsString("'paf_support_type_id'", $source, 'Paf::support_type() must use paf_support_type_id as the foreign key.');
    }

    public function test_paf_model_has_activity_relationship(): void
    {
        $source = file_get_contents(app_path('Models/Paf.php'));
        $this->assertStringContainsString('function activity()', $source, 'Paf model must define an activity() relationship.');
        $this->assertStringContainsString("'paf_activity_id'", $source, 'Paf::activity() must use paf_activity_id as the foreign key.');
    }

    public function test_paf_model_paf_details_uses_correct_fk(): void
    {
        $source = file_get_contents(app_path('Models/Paf.php'));
        $this->assertStringNotContainsString("'PAFNo', 'PAFNo'", $source, 'Paf::paf_details() must not use the old PAFNo foreign key.');
        $this->assertStringContainsString("'paf_id'", $source, 'Paf::paf_details() must use paf_id as the foreign key.');
    }

    public function test_paf_model_has_pre_plan_relationship(): void
    {
        $source = file_get_contents(app_path('Models/Paf.php'));
        $this->assertStringContainsString('function pre_plan()', $source, 'Paf model must define a pre_plan() relationship.');
        $this->assertStringContainsString('hasOne(PafPrePlan::class)', $source, 'Paf::pre_plan() must use hasOne(PafPrePlan::class).');
    }

    // ── PafDetail model ───────────────────────────────────────────────────────

    public function test_paf_detail_model_fillable_has_correct_fields(): void
    {
        $model = new PafDetail();
        $fillable = $model->getFillable();

        foreach (['paf_id', 'product_id', 'branch_id', 'branch', 'type', 'amount', 'expense', 'srp', 'percentage', 'quantity', 'status'] as $field) {
            $this->assertContains($field, $fillable, "PafDetail model must have '{$field}' in \$fillable.");
        }
    }

    public function test_paf_detail_model_fillable_does_not_have_old_fields(): void
    {
        $model = new PafDetail();
        $fillable = $model->getFillable();

        foreach (['PAFNo', 'sku_code', 'sku_description'] as $field) {
            $this->assertNotContains($field, $fillable, "PafDetail model must NOT have legacy field '{$field}' in \$fillable.");
        }
    }

    public function test_paf_detail_model_paf_relationship_uses_correct_fk(): void
    {
        $source = file_get_contents(app_path('Models/PafDetail.php'));
        $this->assertStringNotContainsString("'PAFNo', 'PAFNo'", $source, 'PafDetail::paf() must not use the old PAFNo foreign key.');
        $this->assertStringContainsString("'paf_id'", $source, 'PafDetail::paf() must use paf_id as the foreign key.');
    }

    public function test_paf_detail_model_has_product_relationship(): void
    {
        $source = file_get_contents(app_path('Models/PafDetail.php'));
        $this->assertStringContainsString('function product()', $source, 'PafDetail model must define a product() relationship.');
    }

    // ── Approval.php imports ──────────────────────────────────────────────────

    public function test_approval_component_has_notification_import(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Paf/Approval.php'));
        $this->assertStringContainsString(
            'use Illuminate\Support\Facades\Notification;',
            $source,
            'Approval.php must import the Notification facade.'
        );
    }

    public function test_approval_component_has_paf_approved_import(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Paf/Approval.php'));
        $this->assertStringContainsString(
            'use App\Notifications\PafApproved;',
            $source,
            'Approval.php must import PafApproved notification.'
        );
    }

    // ── Create.php setDetail() fix ────────────────────────────────────────────

    public function test_create_set_detail_searches_branches_not_products_for_branch(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Paf/Create.php'));
        $this->assertStringNotContainsString(
            "collect(\$this->products)->firstWhere('id', \$detail['branch_id'])",
            $source,
            'setDetail() must not search $this->products for the branch — it must search $this->branches.'
        );
        $this->assertStringContainsString(
            "collect(\$this->branches)->firstWhere('id', \$detail['branch_id'])",
            $source,
            'setDetail() must search $this->branches for the branch lookup.'
        );
    }

    public function test_create_set_detail_uses_reference_in_foreach(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Paf/Create.php'));
        $this->assertStringContainsString(
            'foreach ($paf_data[\'details\'] as &$detail)',
            $source,
            'setDetail() must iterate details by reference (&$detail) so mutations are kept.'
        );
    }

    // ── Edit.php fixes ────────────────────────────────────────────────────────

    public function test_edit_uses_this_paf_paf_number_not_local_variable(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Paf/Edit.php'));
        $this->assertStringNotContainsString(
            "\$paf->paf_number",
            $source,
            'Edit.php must not reference the undefined local $paf variable — use $this->paf->paf_number.'
        );
        $this->assertStringContainsString(
            "\$this->paf->paf_number",
            $source,
            'Edit.php must use $this->paf->paf_number in the flash message.'
        );
    }

    public function test_edit_uses_paf_activity_id_column(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Paf/Edit.php'));
        $this->assertStringNotContainsString(
            "\$this->paf->activity_id",
            $source,
            'Edit.php must not use activity_id — the correct column name is paf_activity_id.'
        );
        $this->assertStringContainsString(
            "\$this->paf->paf_activity_id",
            $source,
            'Edit.php must use $this->paf->paf_activity_id to load the activity foreign key.'
        );
    }

    public function test_edit_reads_pre_plan_number_via_relationship(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Paf/Edit.php'));
        $this->assertStringNotContainsString(
            "\$this->paf->pre_plan_number",
            $source,
            'Edit.php must not access pre_plan_number directly on $paf — it is on the pre_plan relationship.'
        );
        $this->assertStringContainsString(
            'pre_plan->pre_plan_number',
            $source,
            'Edit.php must read pre_plan_number via the pre_plan relationship.'
        );
    }
}
