<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Livewire\Component;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Livewire's JavaScript puts its own methods on $wire, so wire:click="upload" or an event listener
 * mapped to "set" runs that JavaScript method and never reaches the PHP action. These pass
 * Livewire::test() and only break in a browser, so they are caught here.
 */
class LivewireReservedActionNamesTest extends TestCase
{
    /** @var array<int, string> */
    private const RESERVED = [
        'upload', 'uploadMultiple', 'removeUpload', 'cancelUpload',
        'set', 'get', 'call', 'commit', 'refresh', 'watch', 'entangle', 'dispatch', 'dispatchTo',
        'dispatchSelf', 'on', 'js', 'el', 'id', 'parent', 'toggle', 'intercept', 'errors', 'island',
    ];

    public function test_no_livewire_component_declares_an_action_named_like_a_wire_magic_method(): void
    {
        $offenders = [];

        foreach (File::allFiles(app_path('Http/Livewire')) as $file) {
            $class = 'App\\Http\\Livewire\\' . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());

            if (!class_exists($class) || !is_subclass_of($class, Component::class)) {
                continue;
            }

            foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() === $class && \in_array($method->getName(), self::RESERVED, true)) {
                    $offenders[] = $class . '::' . $method->getName();
                }
            }
        }

        $this->assertSame([], $offenders, 'Rename these actions; Livewire\'s JavaScript keeps the name for $wire.');
    }
}
