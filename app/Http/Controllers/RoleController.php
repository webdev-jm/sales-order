<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index() {

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $roles = Role::orderBy('id', 'DESC')->paginate(10);
        return view('roles.index')->with([
            'roles' => $roles
        ]);
    }

    public function create() {
        $permissions = Permission::all();
        return view('roles.create')->with([
            'permissions' => $permissions
        ]);
    }

    public function store(StoreRoleRequest $request) {
        $role = Role::create(['name' => $request->name])->givePermissionTo($request->permissions);

        // logs
        activity('create')
        ->performedOn($role)
        ->log(':causer.firstname :causer.lastname has created role :subject.name');

        return redirect()->route('role.index')->with([
            'message_success' => 'Role '.$role->name.' was created.'
        ]);
    }

    public function edit($id) {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();
        return view('roles.edit')->with([
            'role' => $role,
            'permissions' => $permissions
        ]);
    }

    public function update(UpdateRoleRequest $request, $id) {
        $role = Role::findOrFail($id);
        $role_name = $role->name;

        $changes_arr['old'] = $role->getOriginal();

        $role->update([
            'name' => $request->name
        ]);
        $role->syncPermissions($request->permissions);

        $changes_arr['changes'] = $role->getChanges();
        
        // logs
        activity('update')
        ->performedOn($role)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated role :subject.name .');

        return back()->with([
            'message_success' => $role_name
        ]);
    }
}
