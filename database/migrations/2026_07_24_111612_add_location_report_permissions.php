<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $permissions = [
        'location report access' => 'access to store location records report',
        'report restricted'      => 'restricts reports and mcp reports to the user\'s subordinates only',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['module' => 'Report', 'description' => $description]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::whereIn('name', array_keys($this->permissions))->delete();
    }
};
