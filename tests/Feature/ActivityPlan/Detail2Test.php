<?php

namespace Tests\Feature\ActivityPlan;

use App\Http\Livewire\ActivityPlan\Detail2;
use Tests\TestCase;

class Detail2Test extends TestCase
{
    // ── Structure ──────────────────────────────────────────────────────────────

    public function test_detail2_has_get_cached_branch_ids_method(): void
    {
        $this->assertTrue(
            method_exists(Detail2::class, 'getCachedBranchIds'),
            'Detail2 must have a getCachedBranchIds() method.'
        );
    }

    public function test_detail2_mount_calls_get_cached_branch_ids(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Detail2.php')
        );

        $this->assertStringContainsString(
            '$this->getCachedBranchIds()',
            $source,
            'Detail2::mount() must call getCachedBranchIds() to prime the cache on first load.'
        );
    }

    // ── Cache key & storage ────────────────────────────────────────────────────

    public function test_detail2_uses_user_scoped_cache_key(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Detail2.php')
        );

        $this->assertStringContainsString(
            "'user_branches_' . auth()->id()",
            $source,
            'Detail2::getCachedBranchIds() must scope the cache key to the authenticated user ID.'
        );
    }

    public function test_detail2_stores_branch_ids_in_cache(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Detail2.php')
        );

        $this->assertStringContainsString(
            'Cache::put(',
            $source,
            'Detail2::getCachedBranchIds() must persist branch IDs to the cache.'
        );

        $this->assertStringContainsString(
            'now()->addHour()',
            $source,
            'Detail2::getCachedBranchIds() must cache branch IDs with a 1-hour TTL.'
        );
    }

    // ── Query optimisation ─────────────────────────────────────────────────────

    public function test_detail2_render_uses_where_in_instead_of_nested_where_has_for_auth(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Detail2.php')
        );

        $this->assertStringContainsString(
            "->whereIn('id', \$this->getCachedBranchIds())",
            $source,
            'Detail2::render() must use whereIn(cached IDs) instead of nested whereHas for authorization.'
        );
    }

    public function test_detail2_branch_query_in_render_does_not_use_nested_where_has_users(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Detail2.php')
        );

        // Isolate just the branch query block (between searchBranchQuery check and getCachedBranchIds).
        $branchStart = strpos($source, 'if (!empty($this->searchBranchQuery))');
        $branchEnd   = strpos($source, '->limit(10)->get();', $branchStart);
        $branchBlock = substr($source, $branchStart, $branchEnd - $branchStart);

        $this->assertStringNotContainsString(
            "whereHas('users'",
            $branchBlock,
            'The branch query in Detail2::render() must not use whereHas("users") — authorization is via the cached IDs.'
        );

        $this->assertStringContainsString(
            'getCachedBranchIds',
            $branchBlock,
            'The branch query in Detail2::render() must use getCachedBranchIds() for authorization.'
        );
    }

    // ── Cache facade interaction ───────────────────────────────────────────────

    public function test_detail2_reads_from_cache_before_querying_database(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Detail2.php')
        );

        $this->assertStringContainsString(
            'Cache::get(',
            $source,
            'Detail2::getCachedBranchIds() must attempt a Cache::get before running a DB query.'
        );

        // Ensure the DB query only runs when the cache miss occurs (inside the null check).
        $this->assertMatchesRegularExpression(
            '/Cache::get\([^)]+\).*?if \(\$ids === null\)/s',
            $source,
            'Detail2 must only query the database when the cache returns null.'
        );
    }
}
