<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    /**
     * fullName() should combine first and last name in title case.
     * We use a new User() without saving so no DB query is involved —
     * confirming that the method reads directly from model attributes.
     */
    public function test_full_name_without_middle_name(): void
    {
        $user = new User([
            'firstname'  => 'JUAN',
            'middlename' => null,
            'lastname'   => 'DELA CRUZ',
        ]);

        $this->assertSame('Juan Dela Cruz', $user->fullName());
    }

    /**
     * fullName() should include the middle name when it is set.
     */
    public function test_full_name_with_middle_name(): void
    {
        $user = new User([
            'firstname'  => 'JUAN',
            'middlename' => 'PEDRO',
            'lastname'   => 'DELA CRUZ',
        ]);

        $this->assertSame('Juan Pedro Dela Cruz', $user->fullName());
    }

    /**
     * fullName() should treat an empty string middlename the same as null.
     */
    public function test_full_name_skips_empty_middle_name(): void
    {
        $user = new User([
            'firstname'  => 'maria',
            'middlename' => '',
            'lastname'   => 'SANTOS',
        ]);

        $this->assertSame('Maria Santos', $user->fullName());
    }
}
