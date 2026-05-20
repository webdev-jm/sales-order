<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Tests for on-leave awareness in the WarAreaDetail Livewire component
 * and its corresponding view.
 */
class WarAreaDetailOnLeaveTest extends TestCase
{
    // ── WarAreaDetail component ───────────────────────────────────────────────

    public function test_war_area_detail_imports_activity_plan_detail(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/War/WarAreaDetail.php')
        );

        $this->assertStringContainsString(
            'use App\Models\ActivityPlanDetail;',
            $source,
            'WarAreaDetail must import ActivityPlanDetail.'
        );
    }

    public function test_war_area_detail_render_queries_activity_plan_detail_for_on_leave(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/War/WarAreaDetail.php')
        );

        $this->assertStringContainsString(
            'is_on_leave',
            $source,
            'WarAreaDetail::render() must query ActivityPlanDetail using is_on_leave.'
        );

        $this->assertStringContainsString(
            "->where('status', 'approved')",
            $source,
            'WarAreaDetail::render() must filter on-leave entries from approved activity plans.'
        );

        $this->assertStringContainsString(
            '$this->user->id',
            $source,
            'WarAreaDetail::render() must scope the on-leave check to the current user.'
        );
    }

    public function test_war_area_detail_passes_is_on_leave_to_view(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/War/WarAreaDetail.php')
        );

        $this->assertStringContainsString(
            "'is_on_leave'",
            $source,
            'WarAreaDetail::render() must pass is_on_leave to the view.'
        );
    }

    // ── war-area-detail.blade.php ─────────────────────────────────────────────

    public function test_war_area_detail_view_shows_on_leave_banner(): void
    {
        $source = file_get_contents(
            resource_path('views/livewire/war/war-area-detail.blade.php')
        );

        $this->assertStringContainsString(
            '$is_on_leave',
            $source,
            'war-area-detail.blade.php must check $is_on_leave.'
        );

        $this->assertStringContainsString(
            'ON LEAVE',
            $source,
            'war-area-detail.blade.php must display an ON LEAVE banner.'
        );
    }
}
