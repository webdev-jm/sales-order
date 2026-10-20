<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Livewire\Component;
use ReflectionClass;
use Tests\TestCase;

/**
 * Livewire 2 handed every object property to the view, including ones a component created on the fly
 * with $this->name = .... Livewire 4 only hands over declared public properties, so such a view fails
 * with "Undefined variable". Declare the property or pass the value to view() instead.
 */
class LivewireDeclaredPropertiesTest extends TestCase
{
    public function test_no_livewire_component_assigns_an_undeclared_property(): void
    {
        $offenders = [];

        foreach (File::allFiles(app_path('Http/Livewire')) as $file) {
            $class = 'App\\Http\\Livewire\\' . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());

            if (!class_exists($class) || !is_subclass_of($class, Component::class)) {
                continue;
            }

            preg_match_all('/\$this->(\w+)\s*(?:\[[^\]]*\]\s*)?=(?!=)/', $file->getContents(), $matches);
            $reflection = new ReflectionClass($class);

            foreach (array_unique($matches[1]) as $property) {
                if (!$reflection->hasProperty($property)) {
                    $offenders[] = $class . '::$' . $property;
                }
            }
        }

        $this->assertSame([], $offenders, 'Undeclared Livewire properties: ' . implode(', ', $offenders));
    }
}
