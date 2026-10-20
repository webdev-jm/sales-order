<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Livewire matches repeated elements by wire:key when it updates the page. Without one, a list that filters,
 * re-sorts or pages can carry an input's value or focus over to a different row.
 */
class LivewireLoopKeysTest extends TestCase
{
    public function test_every_livewire_loop_keys_the_element_it_repeats(): void
    {
        $offenders = [];

        foreach (File::allFiles(resource_path('views/livewire')) as $file) {
            $lines = preg_split('/\r\n|\n/', $file->getContents());

            foreach ($lines as $index => $line) {
                if (!preg_match('/@foreach\s*\(/', $line)) {
                    continue;
                }

                $element = $this->firstElementOfLoop($lines, $index);

                if ($element === null || preg_match('/^\s*<option\b/i', $element['tag'])) {
                    continue;
                }

                if (!str_contains($element['text'], 'wire:key')) {
                    $offenders[] = $file->getRelativePathname() . ':' . ($element['line'] + 1);
                }
            }
        }

        $this->assertSame([], $offenders, 'Loops without wire:key: ' . implode(', ', $offenders));
    }

    /**
     * The first tag inside the loop, with its attributes up to the closing ">", or null when the loop body
     * starts with a nested loop (that loop is checked on its own).
     *
     * @param array<int, string> $lines
     * @return array{tag: string, text: string, line: int}|null
     */
    private function firstElementOfLoop(array $lines, int $loopLine): ?array
    {
        for ($i = $loopLine + 1; $i < count($lines); $i++) {
            if (preg_match('/@(foreach|endforeach)\b/', $lines[$i])) {
                return null;
            }

            if (preg_match('/^\s*<[a-zA-Z]/', $lines[$i])) {
                $text = '';
                for ($j = $i; $j < count($lines); $j++) {
                    $text .= $lines[$j] . "\n";
                    if (preg_match('/>\s*$|>/', $lines[$j]) && substr_count($text, '<') <= substr_count($text, '>')) {
                        break;
                    }
                }

                return ['tag' => $lines[$i], 'text' => $text, 'line' => $i];
            }
        }

        return null;
    }
}
