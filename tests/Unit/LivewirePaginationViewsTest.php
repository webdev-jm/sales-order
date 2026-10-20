<?php

namespace Tests\Unit;

use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The app publishes Livewire's pagination views to resources/views/vendor/livewire. The Livewire 2 copies
 * read a $numberOfPaginatorsRendered component property that Livewire 4 removed, which broke every
 * page with more than one page of results. Test data rarely has a second page, so the views are rendered here.
 */
class LivewirePaginationViewsTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function paginationViews(): array
    {
        return [
            'bootstrap' => ['livewire::bootstrap'],
            'simple bootstrap' => ['livewire::simple-bootstrap'],
            'tailwind' => ['livewire::tailwind'],
            'simple tailwind' => ['livewire::simple-tailwind'],
        ];
    }

    #[DataProvider('paginationViews')]
    public function test_published_pagination_view_renders_several_pages(string $view): void
    {
        $paginator = new LengthAwarePaginator(range(1, 12), 60, 12, 2, ['pageName' => 'page']);

        $html = (string) $paginator->links($view);

        $this->assertStringContainsString("previousPage('page')", $html);
        $this->assertStringContainsString("nextPage('page')", $html);
    }

    public function test_page_buttons_keep_a_stable_wire_key(): void
    {
        $paginator = new LengthAwarePaginator(range(1, 12), 60, 12, 2, ['pageName' => 'page']);

        $html = (string) $paginator->links('livewire::bootstrap');

        $this->assertStringContainsString('wire:key="paginator-page-page-3"', $html);
        $this->assertStringContainsString("gotoPage(3, 'page')", $html);
    }
}
