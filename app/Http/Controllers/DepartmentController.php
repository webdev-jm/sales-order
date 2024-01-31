<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));

        $departments = Department::orderBy('id', 'DESC')
            ->when(!empty($search), function($query) use($search) {
                $query->where('department_code', 'like', '%'.$search.'%')
                    ->orWhere('department_name', 'like', '%'.$search.'%');
            })
            ->paginate(10)->onEachSide(1)->appends(request()->query());

        return view('departments.index')->with([
            'search' => $search,
            'departments' => $departments
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $department = Department::findOrFail($id);
        
        return view('departments.show')->with([
            'department' => $department
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $department = Department::findOrFail($id);

        return view('departments.edit')->with([
            'department' => $department
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDepartmentRequest $request, $id)
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
