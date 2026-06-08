<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search'));

        $departments = Department::orderBy('id', 'DESC')
            ->when(!empty($search), function($query) use($search) {
                $query->where('department_code', 'like', '%'.$search.'%')
                    ->orWhere('department_name', 'like', '%'.$search.'%');
            })
            ->paginate(10)->onEachSide(1)->appends(request()->query());

        return view('pages.departments.index')->with([
            'search' => $search,
            'departments' => $departments
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('pages.departments.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreDepartmentRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreDepartmentRequest $request)
    {
        $department = new Department([
            'department_code' => $request->department_code,
            'department_name' => $request->department_name,
            'department_head_id' => $request->department_head_id,
            'department_admin_id' => $request->department_admin_id,
        ]);
        $department->save();

        // logs
        activity('create')
            ->performedOn($department)
            ->log(':causer.firstname :causer.lastname has created department :subject.department_code :subject.department_name');

        return redirect()->route('department.index')->with([
            'message_success' => 'Department '.$department->department_code.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int|string  $id
     * @return \Illuminate\View\View
     */
    public function show(int|string $id)
    {
        $department = Department::findOrFail($id);

        return view('pages.departments.show')->with([
            'department' => $department
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int|string  $id
     * @return \Illuminate\View\View
     */
    public function edit(int|string $id)
    {
        $department = Department::findOrFail($id);

        return view('pages.departments.edit')->with([
            'department' => $department
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateDepartmentRequest  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateDepartmentRequest $request, int|string $id)
    {
        $department = Department::findOrFail($id);
        $changes_arr['old'] = $department->getOriginal();

        $department->update([
            'department_code' => $request->department_code,
            'department_name' => $request->department_name,
            'department_head_id' => $request->department_head_id,
            'department_admin_id' => $request->department_admin_id,
        ]);

        $changes_arr['changes'] = $department->getchanges();

        // log
        activity('update')
            ->performedOn($department)
            ->withProperties($changes_arr)
            ->log(':causer.firstname :causer.lastname has updated department :subject.department_code :subject.department_name .');

        return redirect()->route('department.index')->with([
            'message_success' => 'Department '.$department->department_code.' has been updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int|string  $id
     * @return void
     */
    public function destroy(int|string $id): void
    {
        //
    }
}
