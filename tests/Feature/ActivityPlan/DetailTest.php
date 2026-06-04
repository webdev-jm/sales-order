<?php

namespace Tests\Feature\ActivityPlan;

use App\Http\Livewire\ActivityPlan\Detail;
use Tests\TestCase;

class DetailTest extends TestCase
{
    // ── Structure ──────────────────────────────────────────────────────────────

    public function test_detail_has_get_cached_branch_ids_method(): void
    {
        $this->assertTrue(
            method_exists(Detail::class, 'getCachedBranchIds'),
            'Detail must have a getCachedBranchIds() method.'
        );
    }

    public function test_detail_mount_calls_get_cached_branch_ids(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Detail.php')
        );

        $this->assertStringContainsString(
            '$this->getCachedBranchIds()',
            $source,
            'Detail::mount() must call getCachedBranchIds() to prime the cache on first load.'
        );
    }

    // ── Cache key & storage ────────────────────────────────────────────────────

    public function test_detail_uses_user_scoped_cache_key(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Detail.php')
        );

        $this->assertStringContainsString(
            "'user_branches_' . auth()->id()",
            $source,
            'Detail::getCachedBranchIds() must scope the cache key to the authenticated user ID.'
        );
    }

    public function test_detail_stores_branch_ids_in_cache(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Detail.php')
        );

        $this->assertStringContainsString(
            'Cache::put(',
            $source,
            'Detail::getCachedBranchIds() must persist branch IDs to the cache.'
        );

        $this->assertStringContainsString(
            'now()->addHour()',
            $source,
            'Detail::getCachedBranchIds() must cache branch IDs with a 1-hour TTL.'
        );
    }

    // ── Query optimisation ─────────────────────────────────────────────────────

    public function test_detail_render_uses_where_in_for_branch_auth_when_no_account_filter(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Detail.php')
        );

        $this->assertStringContainsString(
            "->whereIn('id', \$this->getCachedBranchIds())",
            $source,
            'Detail::render() must use whereIn(cached IDs) for authorization when no account_id is set.'
        );
    }

    public function test_detail_branch_query_in_render_does_not_use_nested_where_has_users(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Detail.php')
        );

        // Isolate just the branch query block.
        $branchStart = strpos($source, 'if (!empty($this->searchQuery))');
        $branchEnd   = strpos($source, '->limit(10)->get();', $branchStart);
        $branchBlock = substr($source, $branchStart, $branchEnd - $branchStart);

        $this->assertStringNotContainsString(
            "whereHas('users'",
            $branchBlock,
            'The branch query in Detail::render() must not use whereHas("users") — authorization is via the cached IDs.'
        );

        $this->assertStringContainsString(
            'getCachedBranchIds',
            $branchBlock,
            'The branch query in Detail::render() must use getCachedBranchIds() for authorization.'
        );
    }

    // ── Cache facade interaction ───────────────────────────────────────────────

    public function test_detail_reads_from_cache_before_querying_database(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Detail.php')
        );

        $this->assertStringContainsString(
            'Cache::get(',
            $source,
            'Detail::getCachedBranchIds() must attempt a Cache::get before running a DB query.'
        );

        $this->assertMatchesRegularExpression(
            '/Cache::get\([^)]+\).*?if \(\$ids === null\)/s',
            $source,
            'Detail must only query the database when the cache returns null.'
        );
    }
}
