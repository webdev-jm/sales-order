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
        'ai assistant access' => 'access to the AI sales assistant, which answers questions about the sales orders of the user\'s accounts',
        'ai insights access'  => 'access to the AI summaries of the SO Dashboard and the Sales Order report',
        'ai mapping help'     => 'access to AI suggestions for SKUs and store codes that an upload could not match',
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
