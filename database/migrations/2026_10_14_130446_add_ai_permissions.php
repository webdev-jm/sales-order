<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $permissions = [
        'po scan access'        => 'access to scan account PO PDFs into a sales order template with AI',
        'po scan format manage' => 'access to manage the per-account PO scan formats',
        'ai usage access'       => 'access to the AI token usage monitor',
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
                ['module' => 'AI', 'description' => $description]
            );
        }

        Role::where('name', 'superadmin')->first()?->givePermissionTo(array_keys($this->permissions));
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
