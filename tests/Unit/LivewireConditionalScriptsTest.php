<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Livewire 2 ran a <script> that an update added to the page; Livewire 4 does not. A script inside a Blade
 * @if or loop of a Livewire view therefore never runs when the condition turns true later, which left the
 * branch and account sign-in forms without a GPS position and the holiday calendar empty. Put such code in
 * x-init on the element that appears, or in a script outside the condition.
 */
class LivewireConditionalScriptsTest extends TestCase
{
    /**
     * Views whose conditions are settled when the component mounts, so the script is in the first page load.
     *
     * @var array<int, string>
     */
    private const SETTLED_AT_MOUNT = [
        'accounts/account-logged.blade.php',
    ];

    public function test_no_livewire_view_puts_a_script_inside_a_blade_condition(): void
    {
        $offenders = [];

        foreach (File::allFiles(resource_path('views/livewire')) as $file) {
            $name = str_replace('\\', '/', $file->getRelativePathname());
            if (in_array($name, self::SETTLED_AT_MOUNT, true)) {
                continue;
            }

            $depth = 0;
            foreach (preg_split('/\r\n|\n/', $file->getContents()) as $number => $line) {
                $line = preg_replace('/\{\{--.*?--\}\}/', '', $line);

                if ($depth > 0 && preg_match('/<script\b(?![^>]*\bsrc=)/', $line)) {
                    $offenders[] = $name . ':' . ($number + 1);
                }

                $depth += preg_match_all('/@(if|isset|unless|foreach|forelse|for|while|empty)\s*\(/', $line);
                $depth -= preg_match_all('/@(endif|endisset|endunless|endforeach|endforelse|endfor|endwhile|endempty)\b/', $line);
            }
        }

        $this->assertSame([], $offenders, 'Scripts Livewire 4 will not run after an update: ' . implode(', ', $offenders));
    }
}
